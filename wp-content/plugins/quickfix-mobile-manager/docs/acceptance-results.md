# Acceptance results — 11 October 2026

The plugin is installed and active in the local WordPress site. No production deploy, real Firebase identity management, real device push, store submission or paid service activation was performed. All source recipes remain excluded unless explicitly curated.

## Verified locally

**102 acceptance assertions passed** in the local WordPress/MySQL environment. The executable suite creates/removes fixtures and intercepts the fixed Firebase/FCM endpoints locally with ephemeral RSA keys; it verifies real JWT signatures rather than bypassing the verifier.

| Area | Evidence |
| --- | --- |
| Schema | 19 additive prefixed InnoDB tables; repeated activation preserves columns; complete star distribution fields. Legacy recipe tables are not altered. |
| Catalog | New recipes excluded; draft-before-publish; missing content rejected; website-only sources hidden from app detail/search; time boundaries evaluated without cron; private source unavailable; restoration does not auto-publish; stale revisions conflict. |
| Paging and categories | Opaque query/sort/revision-bound cursors; no repeated IDs; invalidated cursor restart; disabled categories disappear without withdrawing recipes. |
| Identity | Wrong project/issuer, expired, forged, revoked and disabled credentials rejected; unverified participation denied; consumer initialization does not create a WordPress user. |
| Comments and reports | Pending privacy, idempotent replay, conflicting body rejection, cross-user edit denial, approved edit returning to pending, deletion after withdrawal, blocked-author items/counts removed, one unresolved report per target. |
| Ratings | Unique ownership/revision conflicts, shared website/app aggregate, update/delete/hide recalculation, moderator score-edit rejection. |
| Devices/campaigns | AES-GCM token storage roundtrip; lost initial response replay; installation ID alone insufficient; explicit send confirmation; completed delivery not repeated; completed status represents transport acceptance. |
| Deletion | Recent-auth requirement, immediate write denial, third-party failure remains pending and scrubs local identity fields, successful retry completes, tombstone rejects restored identity. |
| Security | Admin capability and nonce independently required; prepared search SQL; unsafe HTML/links/schemes rejected; public payload excludes emails/UIDs/tokens; durable throttling. |

PHP syntax checks and JavaScript parse checks pass. OpenAPI publishes **47 paths, 71 operations and 37 schemas**; reference resolution, method declarations, required ID parameters and unique operation IDs are checked by `tests/validate-contract.py`.

The normal local HTTP stack returned valid JSON for config, home, categories and recipe listings, a safe 404 for an excluded recipe, and a 403 for anonymous admin access. Public cache headers declare a 60-second TTL. An existing Hostinger theme Windows path bug initially printed warnings before JSON; the separate theme fix removes those warnings while preserving the Linux path behavior.

## Load measurement

The isolated handler harness created **1,000 temporary recipes**, ran **50 concurrent PHP workers / 500 requests**, then removed the fixtures. Measured cached REST-dispatch p95 was **329 ms**; one uncached detail dispatch was **3.47 ms**. These results exclude WordPress bootstrap, network and CDN latency and do not prove production end-to-end targets.

The complete local WordPress HTTP stack took approximately **4.7–5.9 seconds** across the smoke requests. A temporary startup trace located most time in the existing WordPress/plugin/theme bootstrap before REST dispatch; the QuickFix plugin load occupied about 28 ms. The profiling file was removed. **The full-origin latency release target is not met in this local stack.** Benchmark and optimize the staging host/cache/runtime before launch; do not substitute the isolated handler result for an end-to-end performance pass.

## Required staging checks

- Actual Firebase credentials, Google/email/Apple sign-in, provider linking, verification/reset and revocation against a separate test project.
- Real native App Check and Android/iOS FCM receipt, token rotation, opt-out, sign-out association clearing and client campaign deduplication.
- Real Apple authorization-code exchange and provider revocation, including the website Apple deletion integration/adapter.
- External every-minute scheduler, alerts for deletion deadlines/backlog, interruption/recovery and provider outages.
- CDN cache exclusions and purge integration, emergency removal propagation, full HTTP load timings and image payload sizes.
- Keyboard/screen-reader/responsive browser QA. The available browser tool failed to initialize with a missing-runtime-path error, so screenshots and live UI interaction were not verified.
- Public policies/support/deletion page, final retention/backup commitments, independent tombstone restore replay, and a staging host migration/restore drill.

Scheduled queue execution, transient FCM failure, expired worker lease recovery, and Apple authorization-subject validation/revocation recovery passed against intercepted provider endpoints. Ten simultaneous rating writers produced one accepted revision and nine conflicts, preserving the unique row and accurate aggregate. Queue tests also verified the fix for the MySQL advisory-lock name limit. Real provider integration remains a staging requirement. No claim of production readiness or store-policy approval is made.
