# Operational runbooks

## Moderation

New comments are pending. Moderators approve/reject/hide/restore with a reason and revision; they cannot rewrite consumer text. A brand reply is a separately attributed record and only one reply can reference a top-level comment. Author edits remove approved text from public view until reviewed. Deletion removes stored comment text and its brand reply instead of preserving indefinite text copies in audit.

Review Reports for open/investigating items, assign an authorized moderator, and record resolution/dismissal reasons. Report totals never automatically delete content. Personal blocking changes the reader's private overlay and pagination counts immediately. Suspension prevents engagement while public browsing remains independent.

Only user managers can inspect app-user emails; public payloads never contain them. Firebase UID is stable and email is mutable. Password recovery uses Firebase's supported flow via its dedicated confirmed action. WordPress account passwords are unrelated.

Ratings have one unique current row per internal user/recipe across the app and website. Moderators can hold/hide a fraudulent rating with a documented reason, or restore it. They cannot set the user's stars. Every mutation locks the aggregate row, checks ownership/revision and reconciles totals in the same InnoDB transaction. The maintenance pass also reconciles old aggregates in bounded batches.

Establish the proposed 48-hour ordinary moderation and 24-hour urgent-report response commitments with actual staffing and reachable support before launch.

## Notification campaigns

1. Draft title/body/destination/audience and review Android/iOS preview.
2. Confirm the send or schedule action separately. Draft creation/publication does not send automatically.
3. Monitor eligible/attempted/accepted/permanent-failure/retry totals. Completed means FCM acceptance, not receipt or reading.
4. Cancel a draft/scheduled/queued campaign before sending begins. Already sending campaigns cannot be edited/cancelled through this action.
5. Inspect safe queue error codes, service configuration and scheduling if retries accumulate. Workers process at most ten jobs or approximately 45 seconds per pass, with a database worker lock, leases, exponential backoff and unique campaign/device records.

Permanent invalid registrations are opted out and their encrypted tokens cleared. Completed deliveries are not sent again. A crash after transport acceptance can still result in a repeated FCM message, so clients must deduplicate campaign IDs. A recipe destination is validated again at each delivery; withdrawn destinations never become arbitrary external URLs.

## Account deletion

Recent Firebase reauthentication (within five minutes), explicit confirmation, a durable idempotency key and configured stable tombstone key are required. Apple-associated identities also need revocation material before acceptance. Knowing an email/UID alone is not sufficient.

At acceptance the user becomes deletion_pending, a minimal identity HMAC tombstone is stored, and engagement is denied. The durable worker removes comments/replies, ratings/recalculated aggregates, reports, blocks and device associations, scrubs profile data, revokes Apple credentials if applicable, deletes the Firebase identity, then marks completion. Only minimal retry state/UID survives while external deletion is incomplete. It is replaced by an opaque deleted marker at completion.

Transient failures retain a queued retry with a safe error code. Deletion/finalization jobs are not abandoned after a fixed number of attempts. Diagnostics show deletion retries; monitor requests approaching the proposed seven-day target and investigate service outages/provider revocation. Never mark a job complete manually just to clear the dashboard. Missing Apple revocation proof must be resolved through the reviewed authenticated flow/adapter.

Export tombstones independently after acceptance and before backup cutovers. On restore, replay them before reopening traffic. Device-local bookmarks are separate and must not be described as cloud data removed by the backend.

## Diagnostics and retention

Use Dashboard, `/admin/diagnostics` or `wp quickfix diagnostics`. These report safe booleans/counts, queue state, migration version and timestamps; they never expose credentials or tokens. Audit records carry actor, action, target and request ID with safe metadata; they omit comment bodies and redact obvious tokens/emails from moderation reasons.

Hourly maintenance removes expired ordinary idempotency/rate counters, prunes audit records by configured retention, prunes completed non-campaign operational jobs and reconciles ratings. Proposed defaults are 30-day operational and 180-day audit retention. Confirm those policies, bounded backups, monitoring, restore tests and actual recovery targets with the site owner before release. Retain deletion/campaign deduplication through completion and the approved policy window.
