# Deployment and rollback

## Discovered source mapping

All names use the actual WordPress prefix, not a hardcoded `wp_`. The local installation uses PHP 8.2.29 and MySQL. The source adapter reads:

- `recipes`: stable `id`, `wp_post_id`, `videoid`, `title`, `description`, timing, servings, notes, featured URL, legacy nutrition and update timestamp.
- `recipe_ingredients`: ordered by `id`; `ingredient`, `quantity`, `notes`.
- `recipe_steps`: ordered by `step_number`, then `id`; `section`, `instruction`, `timer_minutes`.
- `recipe_step_images`: ordered image URLs joined through step IDs.
- `cooking_videos`: only visible records with a valid 11-character YouTube ID are serialized.
- WordPress posts and attachment APIs establish public source visibility, permalink and thumbnail fallback.

Recipe IDs are strings in public JSON. Legacy source tables are never altered. Website discussion/rating history is not silently imported, replaced or counted twice. Inspect the diagnostics' legacy-comment count and the installed recipe-rating plugins before planning a reviewed legacy mapping.

## Staging deployment

1. Back up site files/database and verify the restore procedure. Preserve independent tombstones and their HMAC key outside that backup.
2. Install the ZIP on staging with a separate Firebase project. Activation applies additive, repeatable InnoDB migrations, records the schema version and adds six Administrator capabilities. Explicitly grant the necessary capabilities to other roles/users; Author/Editor access is not implicit.
3. Review Mobile App → Dashboard diagnostics. Validate all 19 new tables and source columns, no migration error, REST routing, supported extensions, outbound HTTPS, scheduler and cache rules.
4. Configure brand/legal/support links, public deletion page, moderation staffing and retention. Create a public page containing `[quickfix_delete_account]`, set its URL in Settings and confirm the identity-verified flow. Do not accept an email address alone as deletion authority.
5. Configure Firebase/App Check/encryption and the Apple provider flow. Review Google/Apple provider linking conflicts rather than merging by email. Use native secure storage for device registration credentials.
6. Curate reviewed launch recipes. Keep all other sources excluded. Check missing fields and unknown timings; no default nutrition/time values are invented.
7. Run acceptance, real sign-in, actual Android/iOS pushes, failure/retry tests, browser/keyboard checks, load and hosting migration tests. See the acceptance report for exactly what was locally verified.
8. Deploy compatible backend changes before mobile clients. Keep the production domain and `/wp-json/quickfix/v1` paths stable across host moves. Switch DNS only after destination-host checks.

## Rollback

Keep the prior plugin ZIP and schema-compatible backup. Deactivate this plugin to stop its fallback jobs; stop the external scheduler separately. Deactivation/uninstall leave catalog and community data intact. Do not automatically drop tables or reverse a migration just by reinstalling a previous version.

If a migration fails, preserve `quickfix_migration_error`, review database permissions/runtime, take a backup, then rerun activation. The plugin does not mark a failed migration successful. Recover with the compatible plugin/schema, or restore an reviewed backup and replay tombstones before accepting traffic.

## Backup and deletion restoration

Export minimized tombstones independently, to an access-restricted path outside the web root:

```sh
wp quickfix tombstones export /protected/quickfix-tombstones.json
```

After restoring a website/database backup, put the site in maintenance, restore the same protected keys, then run:

```sh
wp quickfix tombstones replay /protected/quickfix-tombstones.json
wp quickfix jobs run
```

Replay suppresses restored identities immediately, removes restored community contributions and enqueues external cleanup. It refuses malformed identity hashes. Keep traffic blocked until the deletion queue is reconciled. Schedule a fresh independent tombstone export after accepted requests; do not rely only on an old export from before the deletion. Tombstone retention requires the finalized privacy/backup policy and is not automatically expired by ordinary log pruning.

## Release gates

Live Firebase project credentials, real Google/email/Apple login and Apple revocation, real App Check/FCM on both platforms, external scheduler/alerts, CDN purge adapter, privacy/legal/support configuration, public deletion resource, browser/accessibility review and staging restore/migration drill are required before production release. No store submission, production deployment or paid service spend was performed by the implementation.
