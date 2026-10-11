<?php
// Community, catalog, audit and deletion records intentionally survive uninstall.
defined('WP_UNINSTALL_PLUGIN') || exit;
wp_clear_scheduled_hook('quickfix_worker');
