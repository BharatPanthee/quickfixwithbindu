<?php
if (!defined('WP_CLI') || !WP_CLI || !in_array(wp_get_environment_type(), ['local','development'], true)) { throw new RuntimeException('Rating fixture worker requires local/development WP-CLI.'); }
require_once dirname(__DIR__) . '/quickfix-mobile-manager.php';
use QuickFix\{Store,Community,Fault};
if (!post_type_exists('recipes')) { register_post_type('recipes', ['public' => true, 'rewrite' => ['slug' => 'recipes']]); }
$user=Store::row('users',(string)$args[0]);if(!str_starts_with($user['firebase_uid'],'qf-'))throw new RuntimeException('Only an acceptance fixture user is permitted.');
try{Community::rating((string)$args[1],'app',$user,['stars'=>(int)$args[2],'revision'=>0]);echo 'saved';}catch(Fault $e){echo $e->slug;}
