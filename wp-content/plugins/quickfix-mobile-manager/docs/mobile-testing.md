# Local Android emulator integration

Updated 12 October 2026. The backend is implemented; live Firebase authentication and device delivery still require configuration. No Flutter app project was found in this WordPress workspace. These steps integrate an existing Flutter project, or a separately created one.

## 1. Start the local API bridge

Start `quickfixwithbindu-local` in Local. The development bridge preserves the Local site hostname while forwarding Firebase and App Check headers unchanged. It listens only on PC loopback and forwards only `/wp-json/quickfix/v1/` requests. It does not bypass authentication or change the WordPress plugin stack.

From this WordPress public directory, run in PowerShell:

```powershell
& 'C:\Users\MaxEAM\AppData\Local\Programs\Python\Python313\python.exe' wp-content/plugins/quickfix-mobile-manager/tools/emulator-proxy.py
```

Leave that window running; Ctrl+C stops it. One bridge was started during preparation; do not launch a second copy on the same port. If it stops or you reboot, use the command above.

| Client | API base URL |
| --- | --- |
| PC browser/Postman | `http://127.0.0.1:8765/wp-json/quickfix/v1` |
| Android emulator on this PC | `http://10.0.2.2:8765/wp-json/quickfix/v1` |
| Normal Local site | `http://quickfixwithbindu-local.local/wp-json/quickfix/v1` |

Android's `10.0.2.2` reaches the host PC's loopback interface. The `.local` hostname may not resolve in the emulator; the bridge solves both that and Local's hostname-based routing. This bridge is for a native emulator app, not Flutter Web or physical devices. It has no browser CORS support and is not a production server.

Recipes `1`, `2` and `3` were explicitly published in the local app catalog. Only local app metadata changed. More recipes can be reviewed in Mobile App → Recipes; to undo the local test publication, Withdraw these recipes there.

Test `/app/config`, `/app/home`, `/app/recipes`, `/app/recipes/1` from the PC bridge first, then open the same paths in the emulator browser. A public listing should contain three recipe IDs. Normal private/admin errors remain real errors.

The full WordPress startup currently takes roughly 5–6 seconds in a fresh trace, with occasional 20-second request timeouts in earlier checks. Isolated catalog handlers took about 30 ms. Use a temporary 60-second client timeout for local integration and record slow requests; this does not meet the production latency targets. Optimize the wider staging WordPress runtime before release.

## 2. Create a separate Firebase test project

In the [Firebase console](https://console.firebase.google.com/), create a project such as `QuickFix Dev`; keep its actual unique project ID. Analytics is optional for this implementation.

1. Register the Android test app with its exact application ID, for example `com.quickfixwithbindu.app.dev`. Use an emulator image with Google Play services. Set the Android debug signing SHA fingerprints for Google sign-in as required by Firebase's provider instructions.
2. Register a Web app as well to obtain the public web configuration used by WordPress Settings and the website widget.
3. Enable Authentication → Email/Password. Enable Google when ready to test that provider. Apple needs its own developer/provider configuration and revocation material; Android email/Google tests do not validate the Apple flow.
4. Record the project ID, numeric project number, Android Firebase App ID and public Web configuration.
5. Settings → Service accounts → Generate New Private Key. Store that server JSON outside the web root, for example `C:\Users\MaxEAM\Local Sites\quickfixwithbindu-local\app\firebase-dev-service-account.json`. Do not place it in Flutter assets, `public`, Git or a chat message. The actual filename can differ.

The backend expects signed tokens from this real test project and performs server-side revocation lookup. The Firebase Auth Emulator's unsigned tokens are not accepted by this implementation; use the separate test project for end-to-end testing.

## 3. Connect WordPress to that project

Mobile App → Settings: save the Firebase project ID, project number, and public Web JSON containing `apiKey`, `authDomain`, `projectId`, `appId`, `messagingSenderId`. The Web `appId` belongs in public settings; the Android App ID belongs in the server App Check allowlist. Configure valid support/terms/privacy/community/deletion links before testing website registration.

Configure these server constants through protected configuration loaded by `wp-config.php` before `wp-settings.php`:

| Constant | Value |
| --- | --- |
| `QUICKFIX_FIREBASE_CREDENTIALS` | Absolute outside-web-root service-account JSON path |
| `QUICKFIX_DEVICE_KEY` | Separate generated random secret of at least 32 characters |
| `QUICKFIX_IDENTITY_HASH_KEY` | Another generated stable random secret of at least 32 characters |
| `QUICKFIX_APP_CHECK_APP_IDS` | Exact Android Firebase App ID, typically `1:PROJECT_NUMBER:android:...` |

Do not merely set PowerShell environment variables and assume Local's already-running PHP service will inherit them. Use protected server configuration or restart a correctly configured service. Avoid changing encryption/tombstone keys after test devices and accounts have been created. See [configuration](configuration.md) for permissions and key handling. The service account needs Firebase Authentication management and FCM sending permissions; use the test project only.

Under Mobile App → Dashboard diagnostics, confirm identity and encryption configuration. `/app/config` must show `identity_available: true`. Never upload the private JSON as the public Web configuration.

## 4. Configure Flutter

Install Flutter/Android tooling if needed; neither Flutter nor Firebase CLI was available on the current shell PATH during preparation. In the mobile project directory, use Firebase's FlutterFire workflow:

```sh
firebase login
dart pub global activate flutterfire_cli
flutter pub add firebase_core firebase_auth firebase_messaging firebase_app_check http uuid flutter_secure_storage
flutterfire configure --project=YOUR_ACTUAL_TEST_PROJECT_ID
```

Initialize Firebase with the generated `firebase_options.dart`. For the emulator debug build, activate `AndroidProvider.debug` immediately afterward. Register the printed App Check debug token in Firebase Console → App Check → Manage debug tokens. Send the resulting App Check JWT to the backend, not the printed debug secret. Keep the production build on its production attestation provider.

Enable HTTP only in the Android debug configuration. For a simple Flutter project without an existing network security config, add this to `android/app/src/debug/AndroidManifest.xml`:

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <uses-permission android:name="android.permission.INTERNET" />
    <application android:usesCleartextTraffic="true" />
</manifest>
```

If an existing `android:networkSecurityConfig` is present, use a debug-only resource override permitting cleartext to `10.0.2.2` instead. Keep release traffic on HTTPS.

Example authenticated request after Firebase sign-in:

```dart
import 'dart:convert';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:http/http.dart' as http;

const apiBase = String.fromEnvironment(
  'QUICKFIX_API_BASE',
  defaultValue: 'http://10.0.2.2:8765/wp-json/quickfix/v1',
);

Future<Map<String, dynamic>> initializeBackendSession() async {
  final user = FirebaseAuth.instance.currentUser;
  if (user == null) throw StateError('Sign in first');
  final token = await user.getIdToken();
  if (token == null) throw StateError('Firebase session unavailable');
  final response = await http.post(
    Uri.parse('$apiBase/me/session'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
    body: jsonEncode({}),
  ).timeout(const Duration(seconds: 60));
  final envelope = jsonDecode(response.body) as Map<String, dynamic>;
  if (response.statusCode < 200 || response.statusCode >= 300) {
    throw StateError(envelope['error']['message'] as String);
  }
  return envelope['data'] as Map<String, dynamic>;
}
```

Refresh an expired ID token once, then retry the same logical request. Do not send WordPress credentials, service-account keys, or a client-supplied UID as authorization. This Dart snippet is an integration example; no Flutter compiler or mobile project was available to compile it here.

## 5. Verify the complete flows

| Test | Request or action | Expected result |
| --- | --- | --- |
| Browse | `GET /app/recipes`, `GET /app/recipes/1` | Curated cards/detail |
| Session | Firebase email signup/login, then `POST /me/session` with `{}` and Bearer token | Backend consumer profile |
| Verification | Send email verification, follow it, reload Firebase user and refresh token | Verified participation allowed |
| Comment | `POST /app/recipes/1/comments`, `{ "text": "Test comment" }`, Bearer + Idempotency-Key | Pending; approve in WordPress to make public |
| Rating | `PUT /app/recipes/1/rating`, `{ "stars": 5, "revision": 0 }`, Bearer | Rating saved; use returned revision for subsequent edits |
| Device | `POST /app/devices`, App Check JWT + Idempotency-Key and payload below | Registration credential returned |
| Push | Allow device notifications; enable Notifications in test WordPress Settings; draft and confirm test-project campaign; run worker | FCM acceptance recorded; app receives/handles message |
| Deletion | Reauthenticate a disposable test account; `POST /me/deletion`, `{ "confirm": true }`, Bearer + Idempotency-Key; run worker | Pending, then local/Firebase deletion completed |

Device JSON example (replace tokens and IDs with actual values):

```json
{
  "installation_id": "PERSISTED_RANDOM_UUID",
  "fcm_token": "ACTUAL_FCM_TOKEN",
  "platform": "android",
  "app_version": "1.0.0",
  "consent": true,
  "app_id": "ACTUAL_ANDROID_FIREBASE_APP_ID",
  "associate_user": false
}
```

On first registration send `X-Firebase-AppCheck`; persist the returned `registration_credential` in secure storage and send it as `X-QuickFix-Device` on subsequent updates/opt-out. To associate the logged-in account, also send Bearer and `associate_user: true`; clear the association at logout. Handle FCM token rotation, notification permission, foreground display and campaign-ID deduplication in the app.

Use a UUID Idempotency-Key for each new logical comment, report, device change, campaign execution and deletion request. Persist/reuse that same key and body on retries; a changed body requires a new key. A 409 revision conflict requires a refresh/review. IDs are JSON strings. Follow `next_cursor`; on `cursor_restart`, restart pagination. Honor `Retry-After` on 429.

Local currently has `DISABLE_WP_CRON=true`. In Local's **Open site shell**, run `wp quickfix jobs run` after requesting deletion or sending a campaign. For continuous testing, configure a monitored every-minute worker; declaring `QUICKFIX_EXTERNAL_SCHEDULER=1` alone does not run it. The manual queue action also exists in Mobile App. Do not call an admin job endpoint from the consumer app.

Admin endpoints use the existing WordPress session, nonce and explicit capabilities. They are for WordPress administration; the app uses public and consumer endpoints. See [OpenAPI](openapi.json) for all 47 paths and exact schemas. Verify real Google sign-in, delivery and deletion before claiming those flows ready. Apple and iOS remain separate platform test gates.

References: [FlutterFire setup](https://firebase.google.com/docs/flutter/setup), [Firebase service accounts](https://firebase.google.com/docs/admin/setup), [App Check debug provider](https://firebase.google.com/docs/app-check/flutter/debug-provider), [Android emulator network](https://developer.android.com/studio/run/emulator-networking-address), [Android network security](https://developer.android.com/privacy-and-security/security-config), [FCM Flutter setup](https://firebase.google.com/docs/cloud-messaging/flutter/get-started).
