# QuickFix With Bindu

WordPress site files for QuickFix With Bindu, including WordPress core, installed themes and plugins, custom assets, and uploaded media.

## Local setup

1. Serve this directory with a PHP and MySQL environment compatible with the installed WordPress version.
2. Copy `wp-config-sample.php` to `wp-config.php` and configure your local database credentials and unique authentication salts.
3. Import a separately supplied WordPress database, then update the site URLs for your environment.

The WordPress database is not included. Pages, posts, menus, plugin settings, and database-managed WPCode snippets require that database. Supply API credentials through private server configuration.

Local credentials, private configuration, logs, generated caches, and temporary backups are excluded from Git. See `SITE_ANALYSIS.md` for the site audit and known implementation issues.

The repository copy of WP Recipe Maker omits its embedded YouTube API key. Configure video metadata access with your own private credentials when setting up another environment.
