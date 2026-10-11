# QuickFix Mobile Manager

WordPress administration and `/wp-json/quickfix/v1` backend for the 11 October 2026 API and Admin specification. The existing website recipe tables remain the source of recipe content; the plugin owns app publication metadata and shared community data.

## Install

Upload the distributable ZIP in **Plugins → Add New → Upload Plugin**, activate it, and open **Mobile App**. Requires WordPress 6.4+, PHP 8.1+, OpenSSL, mbstring, MySQL/MariaDB with InnoDB, and the existing recipe schema. The JWT dependency is bundled and isolated; Composer is not needed on the destination host.

Activation adds new prefixed tables and grants plugin capabilities to Administrators only. It does not publish any recipe, change legacy recipe content, migrate existing website discussion, send notifications, or delete consumer accounts. Deactivation and uninstall preserve data. A failed schema setup remains visible in diagnostics and API writes fail closed.

## Curate content

1. Open Mobile App → Recipes. Filter and search by title or source recipe ID.
2. Add eligible recipes to Draft, then explicitly Publish or Schedule. A public source, title, approved image, nonempty ingredient and instruction are required. Dates use the WordPress site timezone in forms and UTC in the API/database.
3. Create categories and assign them to recipes. Dietary tags require explicit curation. Configure the three fixed sections under Home Layout and add up to ten overlapping featured placements.
4. Configure support/privacy/terms/community/deletion links and public branding under Settings.

Excluded, withdrawn, private, expired and orphaned recipes do not pass the mobile availability predicate. Website discussion uses website availability independently. Source withdrawal retains app metadata; restoring a source does not automatically publish it. Revision checks protect concurrent edits and bulk actions return per-item failures.

## Authentication and notifications

Public browsing works without Firebase credentials. Protected consumer routes reject requests until a correctly configured project and outside-web-root service account exist. Firebase consumers never become WordPress users. See [configuration](docs/configuration.md) for the exact configuration, least-privilege permissions, App Check, encryption keys, Apple code exchange and scheduler setup.

Notifications start disabled. Drafting never sends. A confirmed campaign targets opted-in devices only, validates its destination again during delivery, and processes bounded durable jobs. Acceptance by FCM is distinct from device delivery. Firebase and Apple credentials are never editable through admin forms or returned by the API.

## Website integration

- `[quickfix_community]` on a recipe page resolves the current custom recipe ID.
- `[quickfix_community recipe_id="123"]` explicitly selects the same stable custom ID used by the app.
- `[quickfix_delete_account]` provides the authenticated public deletion flow. Create a public page with this shortcode and set its URL under Settings before release.

The discussion widget offers Firebase email/Google/Apple sign-in, verification, comments, ratings, reporting and blocking against shared data. SDK browser modules are pinned to Firebase 13.0.0 and loaded only when identity is configured. No analytics/Firestore SDK is loaded. Apple users require the protected provider authorization/revocation configuration described in the deployment guide; validate its complete web/native flow on staging before enabling that provider.

## Contracts and handover

- [OpenAPI](docs/openapi.json)
- [Deployment, rollback and release gates](docs/deployment.md)
- [Moderation, notification and deletion runbooks](docs/runbooks.md)
- [Acceptance results](docs/acceptance-results.md)
- [Initial additive migration SQL](docs/schema-1.0.0.sql); the plugin executes its versioned `dbDelta` definitions automatically.

Run local acceptance fixtures with `wp --skip-plugins --skip-themes eval-file wp-content/plugins/quickfix-mobile-manager/tests/acceptance.php`. The suite rejects nonlocal execution, intercepts Firebase/FCM requests, creates temporary fixtures, then removes them and restores settings. It advances the internal catalog revision to invalidate fixture caches. No real Firebase account or push is touched.

The load harness requires `QUICKFIX_TEST_WPCLI` to point to the installed `wp-cli.phar`, then runs `tests/load.php` with the same command. It creates 1,000 temporary recipes and starts 50 concurrent PHP processes. Reported handler timings exclude WordPress bootstrap, network and CDN latency; full staging HTTP and device tests remain necessary.
