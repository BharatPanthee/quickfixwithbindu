# Environment configuration

Keep production, staging and development projects and device destinations separate. Never put service credentials, private signing keys or encryption keys in Git, mobile assets, WordPress options, the public directory or API responses.

Set these environment variables, or define equivalent constants in protected server configuration:

| Name | Purpose |
| --- | --- |
| `QUICKFIX_FIREBASE_CREDENTIALS` | Absolute path to Firebase service-account JSON **outside the public web root**. The project must match the public project ID in Settings. The path is resolved before checking containment, including symlinks. |
| `QUICKFIX_DEVICE_KEY` | Random secret with at least 32 characters, used to derive an AES-256-GCM key for FCM and provider tokens and initial device registration credentials. |
| `QUICKFIX_IDENTITY_HASH_KEY` | Separate random stable secret of at least 32 characters for deletion tombstone HMACs. Preserve it across host moves and independently retained tombstone exports. |
| `QUICKFIX_APP_CHECK_APP_IDS` | Comma-separated allowlist of exact Firebase App IDs permitted to register devices. No production bypass is implemented. |
| `QUICKFIX_MEDIA_HOSTS` | Additional comma-separated approved public HTTPS image hosts. The current site host and `i.ytimg.com` are already allowed. No server fetch follows user media URLs. |
| `QUICKFIX_EXTERNAL_SCHEDULER` | Set to `1` only after a real every-minute server scheduler is installed and monitored. This is a diagnostic declaration, not a scheduler. |
| `QUICKFIX_APPLE_CREDENTIALS` | Absolute outside-web-root JSON path containing `team_id`, `key_id`, `client_id` and `private_key` for the reviewed Apple Services ID/bundle ID. |

Generate keys in your protected secret manager. Back them up encrypted. Key rotation needs a deliberate encrypted-token migration or device re-registration; changing the device key invalidates existing encrypted tokens. Changing the identity hash key without a tombstone migration defeats restore protection.

Configure **Mobile App → Settings** with Firebase project ID, numeric project number and public web configuration (`apiKey`, `authDomain`, `projectId`, `appId`, `messagingSenderId`). Public client configuration is not a service account. Enable the intended providers and review Firebase password/verification policy in the project. Authorize the actual web domain and native apps.

The server uses the bundled maintained JWT verifier with an explicit RS256 allowlist, cached Google signing keys, project issuer/audience, expiry, issue/authentication time and subject checks. Protected requests also use the supported Identity Toolkit Admin REST lookup to check disabled identities and `validSince` revocation. A signing-key or identity-service outage fails closed for protected routes and does not prevent independent public browsing.

Grant the service identity only the Firebase Authentication permissions required for lookup/deletion and FCM message creation. Review those permissions in the project; do not use an Owner role simply to make an error disappear. OAuth requests use the Identity Toolkit and Firebase Messaging scopes. Outbound HTTPS to Google OAuth, Identity Toolkit, the fixed signing-key endpoints and FCM is required.

## Device registration contract

Initial POST `/app/devices` requires `Idempotency-Key`, a current FCM token, an `X-Firebase-AppCheck` token, and `app_id`. App Check signature, issuer, project number, audience, expiry and exact allowed app ID are verified. The installation identifier alone is never ownership proof.

Store the returned `registration_credential` in platform secure storage. Later updates/DELETE use `X-QuickFix-Device` plus the same installation ID. A lost initial response can replay the same body/key after renewed attestation. Only the hash of the registration credential and encrypted FCM token are persisted. A duplicate token owned by another installation is rejected; rotate it in the client before re-registering. Logout submits `associate_user: false`; optional user association otherwise requires a verified Firebase session and an active local profile.

Test audiences use the server-controlled `is_test` flag. Set it only for reviewed test registrations through a protected database/deployment operation; it is not accepted from the consumer payload. Stage test devices belong in a separate Firebase project. Clients deduplicate notification `campaign_id` and revalidate recipe availability after opening a push.

## Apple authorization and deletion

The native client passes the fresh Apple authorization code to `/me/session` as `apple_authorization_code` after Firebase login, or with `/me/deletion`. The server exchanges the code using its protected Apple client secret, verifies Apple's ID token and subject against the Apple identity in the already verified Firebase claims, and retains only the encrypted refresh token needed for revocation. No email-based account merge occurs.

Deletion acceptance requires that Apple revocation material or a reviewed `quickfix_revoke_provider_tokens` adapter exists. The worker persists revocation proof before deleting the Firebase identity, so retries do not silently skip Apple revocation. Failed revocation never marks deletion complete. The adapter must return literal `true` only after successful supported revocation, and must store minimal durable proof through retries. Configure the correct client ID for the native/web flow and test real Apple authorization and deletion on staging. Firebase popup sign-in by itself does not expose a reusable Apple authorization code to this plugin; the website Apple deletion flow needs the reviewed Apple authorization integration/adapter before release.

## Scheduler and caches

Install an every-minute scheduler under a protected server account:

```sh
wp --path=/srv/quickfix/public quickfix jobs run
```

Use the host's existing WP-CLI installation and lock/monitor the scheduled process. The plugin also provides a minute WP-Cron fallback and a capability/nonce-protected manual queue pass; traffic-driven cron is not a delivery guarantee. Deactivation clears the fallback event but leaves queue/data intact.

Private routes and any request with Authorization must bypass CDN/page caching. Public responses have a 60-second TTL, ETags and catalog revision; listings also have a 60-second server cache keyed by query, cursor offset, publication boundary and revision. Integrate your CDN purge through `quickfix_purge_catalog`. The plugin invokes LiteSpeed's existing purge hook when installed. Revalidate details and bookmarked IDs at the origin for emergency removals.

Forward the Authorization header through the web server. Require HTTPS in production; local/development HTTP is permitted only by the explicit WordPress environment type. Do not enable public PHP error display in production. The local Hostinger theme path warning was fixed separately so it cannot precede the API JSON.

Primary integration references: [Firebase verification](https://firebase.google.com/docs/auth/admin/verify-id-tokens), [session revocation](https://firebase.google.com/docs/auth/admin/manage-sessions), [App Check](https://firebase.google.com/docs/app-check/custom-resource-backend), [FCM HTTP v1](https://firebase.google.com/docs/cloud-messaging/send/v1-api), [Apple token revocation](https://developer.apple.com/documentation/signinwithapplerestapi/revoke-tokens).
