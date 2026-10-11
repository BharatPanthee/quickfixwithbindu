-- QuickFix 1.0.0 additive schema. Replace {prefix} with the configured WordPress prefix.
-- Back up first. The plugin normally creates these tables via dbDelta and tracks its schema version.
-- No existing recipe/WordPress table is altered.

CREATE TABLE IF NOT EXISTS {prefix}quickfix_mobile_recipes (
  recipe_id bigint unsigned NOT NULL,
  state varchar(20) NOT NULL DEFAULT 'excluded',
  available_from datetime DEFAULT NULL,
  available_to datetime DEFAULT NULL,
  published_at datetime DEFAULT NULL,
  summary_override text NOT NULL,
  image_override varchar(1000) NOT NULL DEFAULT '',
  dietary_tags text NOT NULL,
  comments_enabled tinyint NOT NULL DEFAULT 1,
  ratings_enabled tinyint NOT NULL DEFAULT 1,
  revision bigint unsigned NOT NULL DEFAULT 1,
  updated_by bigint unsigned NOT NULL DEFAULT 0,
  updated_at datetime NOT NULL,
  orphaned tinyint NOT NULL DEFAULT 0,
  PRIMARY KEY (recipe_id),
  KEY publication (state,available_from,available_to)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_app_categories (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL,
  slug varchar(100) NOT NULL,
  description text NOT NULL,
  image_ref varchar(1000) NOT NULL DEFAULT '',
  enabled tinyint NOT NULL DEFAULT 1,
  sort_order int NOT NULL DEFAULT 0,
  revision bigint unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_recipe_categories (
  recipe_id bigint unsigned NOT NULL,
  category_id bigint unsigned NOT NULL,
  PRIMARY KEY (recipe_id,category_id),
  KEY category (category_id,recipe_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_home_placements (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  recipe_id bigint unsigned NOT NULL,
  section varchar(20) NOT NULL DEFAULT 'featured',
  position int NOT NULL DEFAULT 0,
  starts_at datetime DEFAULT NULL,
  ends_at datetime DEFAULT NULL,
  revision bigint unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY placement (recipe_id,section)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_users (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  firebase_uid varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  display_name varchar(100) NOT NULL,
  email varchar(254) DEFAULT NULL,
  providers text NOT NULL,
  verified tinyint NOT NULL DEFAULT 0,
  status varchar(30) NOT NULL DEFAULT 'active',
  first_seen datetime NOT NULL,
  last_seen datetime NOT NULL,
  deletion_requested_at datetime DEFAULT NULL,
  revision bigint unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY firebase_uid (firebase_uid),
  KEY status (status)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_comments (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  recipe_id bigint unsigned NOT NULL,
  user_id bigint unsigned DEFAULT NULL,
  parent_comment_id bigint unsigned DEFAULT NULL,
  author_type varchar(20) NOT NULL DEFAULT 'consumer',
  body text NOT NULL,
  status varchar(20) NOT NULL DEFAULT 'pending',
  source_channel varchar(20) NOT NULL,
  revision bigint unsigned NOT NULL DEFAULT 1,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  moderation_reason varchar(500) NOT NULL DEFAULT '',
  assigned_to bigint unsigned DEFAULT NULL,
  PRIMARY KEY (id),
  KEY discussion (recipe_id,status,created_at,id),
  KEY owner (user_id),
  UNIQUE KEY brand_reply (parent_comment_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_ratings (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  recipe_id bigint unsigned NOT NULL,
  user_id bigint unsigned NOT NULL,
  stars tinyint unsigned NOT NULL,
  status varchar(20) NOT NULL DEFAULT 'active',
  source_channel varchar(20) NOT NULL,
  revision bigint unsigned NOT NULL DEFAULT 1,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  moderation_reason varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY ownership (recipe_id,user_id),
  KEY aggregate (recipe_id,status)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_rating_stats (
  recipe_id bigint unsigned NOT NULL,
  total int unsigned NOT NULL DEFAULT 0,
  count int unsigned NOT NULL DEFAULT 0,
  star1 int unsigned NOT NULL DEFAULT 0,
  star2 int unsigned NOT NULL DEFAULT 0,
  star3 int unsigned NOT NULL DEFAULT 0,
  star4 int unsigned NOT NULL DEFAULT 0,
  star5 int unsigned NOT NULL DEFAULT 0,
  calculated_at datetime NOT NULL,
  PRIMARY KEY (recipe_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_reports (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  reporter_id bigint unsigned NOT NULL,
  target_type varchar(20) NOT NULL,
  target_id bigint unsigned NOT NULL,
  reason varchar(30) NOT NULL,
  details varchar(500) NOT NULL DEFAULT '',
  status varchar(20) NOT NULL DEFAULT 'open',
  unresolved_key varchar(100) DEFAULT NULL,
  assigned_to bigint unsigned DEFAULT NULL,
  resolution varchar(500) NOT NULL DEFAULT '',
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  revision bigint unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY unresolved (unresolved_key),
  KEY moderation (status,created_at)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_user_blocks (
  blocker_user_id bigint unsigned NOT NULL,
  blocked_user_id bigint unsigned NOT NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (blocker_user_id,blocked_user_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_devices (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  installation_id varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  credential_hash varchar(64) NOT NULL,
  user_id bigint unsigned DEFAULT NULL,
  token_cipher text NOT NULL,
  token_hash varchar(64) NOT NULL,
  platform varchar(20) NOT NULL,
  app_version varchar(30) NOT NULL,
  consent tinyint NOT NULL DEFAULT 0,
  is_test tinyint NOT NULL DEFAULT 0,
  last_seen datetime NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY installation (installation_id),
  UNIQUE KEY token (token_hash),
  KEY audience (consent,is_test,id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_campaigns (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  title varchar(80) NOT NULL,
  body varchar(180) NOT NULL,
  target varchar(30) NOT NULL DEFAULT 'home',
  audience varchar(20) NOT NULL DEFAULT 'all',
  scheduled_at datetime DEFAULT NULL,
  status varchar(30) NOT NULL DEFAULT 'draft',
  created_by bigint unsigned NOT NULL,
  eligible int NOT NULL DEFAULT 0,
  attempted int NOT NULL DEFAULT 0,
  accepted int NOT NULL DEFAULT 0,
  permanent_failures int NOT NULL DEFAULT 0,
  retries int NOT NULL DEFAULT 0,
  revision bigint unsigned NOT NULL DEFAULT 1,
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (id),
  KEY schedule (status,scheduled_at)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_jobs (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  kind varchar(30) NOT NULL,
  dedupe_key varchar(191) NOT NULL,
  payload text NOT NULL,
  attempts int NOT NULL DEFAULT 0,
  available_at datetime NOT NULL,
  lock_until datetime DEFAULT NULL,
  lock_owner varchar(36) DEFAULT NULL,
  status varchar(20) NOT NULL DEFAULT 'queued',
  safe_error varchar(100) NOT NULL DEFAULT '',
  created_at datetime NOT NULL,
  updated_at datetime NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY dedupe (dedupe_key),
  KEY queue (status,available_at)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_audit (
  id bigint unsigned NOT NULL AUTO_INCREMENT,
  actor_type varchar(20) NOT NULL,
  actor_id bigint unsigned NOT NULL,
  action varchar(60) NOT NULL,
  target varchar(100) NOT NULL,
  metadata text NOT NULL,
  request_id varchar(36) NOT NULL,
  created_at datetime NOT NULL,
  PRIMARY KEY (id),
  KEY retention (created_at)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_idempotency (
  dedupe_key varchar(64) NOT NULL,
  request_hash varchar(64) NOT NULL,
  response longtext DEFAULT NULL,
  expires_at datetime NOT NULL,
  PRIMARY KEY (dedupe_key),
  KEY expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_deletion_tombstones (
  identity_hash varchar(64) NOT NULL,
  deleted_at datetime NOT NULL,
  replay_state varchar(20) NOT NULL DEFAULT 'pending',
  PRIMARY KEY (identity_hash)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_rate_limits (
  bucket varchar(64) NOT NULL,
  hits int NOT NULL DEFAULT 0,
  expires_at datetime NOT NULL,
  PRIMARY KEY (bucket),
  KEY expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_campaign_deliveries (
  campaign_id bigint unsigned NOT NULL,
  device_id bigint unsigned NOT NULL,
  status varchar(20) NOT NULL DEFAULT 'pending',
  attempts int NOT NULL DEFAULT 0,
  PRIMARY KEY (campaign_id,device_id),
  KEY progress (campaign_id,status)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS {prefix}quickfix_provider_tokens (
  user_id bigint unsigned NOT NULL,
  provider varchar(30) NOT NULL,
  token_cipher text NOT NULL,
  revoked tinyint NOT NULL DEFAULT 0,
  updated_at datetime NOT NULL,
  PRIMARY KEY (user_id,provider)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
