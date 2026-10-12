"""Loopback-only bridge from Android 10.0.2.2:8765 to Local's site router.

Uses the real WordPress API and preserves Firebase/App Check verification.
Start with Python; stop with Ctrl+C. Never expose this server on a LAN.
"""
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from urllib.request import Request, build_opener, ProxyHandler, HTTPRedirectHandler
from urllib.error import HTTPError, URLError
from urllib.parse import urlsplit
import json
import argparse

ORIGIN = "http://quickfixwithbindu-local.local"
PREFIX = "/wp-json/quickfix/v1/"
MAX_BODY = 32768


class NoRedirect(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


class Bridge(BaseHTTPRequestHandler):
    def log_message(self, fmt, *args):
        pass  # Never record authorization headers, bodies, or query strings.

    def fail(self, status, code, message):
        payload = json.dumps({"error": {"code": code, "message": message}}).encode()
        self.send_response(status)
        self.send_header("Content-Type", "application/json")
        self.send_header("Cache-Control", "no-store")
        self.send_header("Content-Length", str(len(payload)))
        self.end_headers()
        if self.command != "HEAD":
            self.wfile.write(payload)

    def relay(self):
        self.connection.settimeout(65)
        parsed = urlsplit(self.path)
        if parsed.scheme or parsed.netloc or not parsed.path.startswith(PREFIX):
            return self.fail(404, "not_found", "Only the QuickFix API is available here.")
        if self.headers.get("Transfer-Encoding"):
            return self.fail(400, "invalid_body", "Send a fixed-length JSON request.")
        try:
            length = int(self.headers.get("Content-Length", "0"))
            if length < 0 or length > MAX_BODY:
                return self.fail(413, "body_too_large", "Request is too large.")
        except ValueError:
            return self.fail(400, "invalid_body", "Invalid request length.")
        body = self.rfile.read(length) if length else None
        keep = {"authorization", "content-type", "accept", "idempotency-key",
                "x-firebase-appcheck", "x-quickfix-device", "if-none-match"}
        headers = {name: value for name, value in self.headers.items() if name.lower() in keep}
        request = Request(ORIGIN + self.path, data=body, headers=headers, method=self.command)
        try:
            response = build_opener(ProxyHandler({}), NoRedirect()).open(request, timeout=60)
        except HTTPError as error:
            response = error  # Preserve actual backend error envelopes/statuses.
        except (URLError, OSError):
            return self.fail(503, "local_backend_unavailable", "Start the Local site and check its API.")
        with response:
            payload = response.read()
            self.send_response(response.code)
            keep_response = {"content-type", "cache-control", "etag", "vary",
                             "retry-after", "x-request-id"}
            for name, value in response.headers.items():
                if name.lower() in keep_response:
                    self.send_header(name, value)
            self.send_header("Content-Length", str(len(payload)))
            self.end_headers()
            if self.command != "HEAD":
                self.wfile.write(payload)

    do_GET = do_HEAD = do_POST = do_PUT = do_PATCH = do_DELETE = relay


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--port", type=int, default=8765)
    args = parser.parse_args()
    print(f"PC: http://127.0.0.1:{args.port}{PREFIX}", flush=True)
    print(f"Android emulator: http://10.0.2.2:{args.port}{PREFIX}", flush=True)
    server = ThreadingHTTPServer(("127.0.0.1", args.port), Bridge)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        pass
    finally:
        server.server_close()
