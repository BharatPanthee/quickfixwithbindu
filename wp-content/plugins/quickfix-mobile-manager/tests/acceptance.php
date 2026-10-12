<?php
/** Run with wp --skip-plugins --skip-themes eval-file <this-file>, on a disposable/local database only. */
if (!defined('WP_CLI') || !WP_CLI || !in_array(wp_get_environment_type(), ['local','development'], true)) { throw new RuntimeException('Acceptance fixtures require a local/development WP-CLI environment.'); }
require_once dirname(__DIR__) . '/quickfix-mobile-manager.php';
use QuickFix\{API,Apple,Catalog,Community,Firebase,Identity,Jobs,Schema,Settings,Store,Fault};
use QuickFix\Vendor\Firebase\JWT\JWT;
global $wpdb;
Schema::activate();
if (!post_type_exists('recipes')) { register_post_type('recipes', ['public' => true, 'rewrite' => ['slug' => 'recipes']]); }
if (get_option('quickfix_migration_error')) { throw new RuntimeException('Migration failed: ' . get_option('quickfix_migration_error')); }
$saved_settings = get_option('quickfix_settings');
$saved_revision = get_option('quickfix_catalog_revision');
$saved_queue = get_option('quickfix_last_queue_run');
$saved_maintenance = get_option('quickfix_last_maintenance');
$saved_health = get_option('quickfix_api_health');
$run = wp_generate_uuid4(); $posts = []; $recipe_ids = []; $user_ids = []; $video_id = null;
$before = []; foreach (Schema::definitions() as $table => $_) { $before[$table] = $wpdb->get_col('SHOW COLUMNS FROM ' . Store::table($table)); }
$key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]); openssl_pkey_export($key, $private_key);
$public_key = openssl_pkey_get_details($key)['key'];
$credentials = tempnam(sys_get_temp_dir(), 'qf-acceptance-');
file_put_contents($credentials, wp_json_encode(['type' => 'service_account', 'project_id' => 'quickfix-test', 'private_key' => $private_key, 'private_key_id' => $run, 'client_email' => 'test@quickfix-test.iam.gserviceaccount.com']));
putenv('QUICKFIX_FIREBASE_CREDENTIALS=' . $credentials); putenv('QUICKFIX_DEVICE_KEY=' . bin2hex(random_bytes(32))); putenv('QUICKFIX_IDENTITY_HASH_KEY=' . bin2hex(random_bytes(32))); putenv('QUICKFIX_APP_CHECK_APP_IDS=1:123:android:test');
$revoked = false; $disabled = false; $firebase_down = false; $send_count = 0; $push_fail = false; $extra_campaigns=[]; $extra_hashes=[]; $apple_file=null; $apple_revoke_count=0; $apple_bad_subject=false;
$http = static function ($pre, $args, $url) use (&$revoked,&$disabled,&$firebase_down,&$send_count,&$push_fail,&$apple_revoke_count,&$apple_bad_subject,$public_key,$private_key,$run) {
    $response = static fn($body,$code=200) => ['headers' => ['cache-control' => 'max-age=300'], 'body' => wp_json_encode($body), 'response' => ['code' => $code, 'message' => 'Test fixture'], 'cookies' => [], 'filename' => null];
    if (str_contains($url, 'securetoken@')) { return $response(['test-key' => $public_key]); }
    if (str_contains($url, 'firebaseappcheck') || str_contains($url,'appleid.apple.com/auth/keys')) { $details=openssl_pkey_get_details(openssl_pkey_get_public($public_key));return $response(['keys'=>[['kty'=>'RSA','alg'=>'RS256','use'=>'sig','kid'=>'test-key','n'=>JWT::urlsafeB64Encode($details['rsa']['n']),'e'=>JWT::urlsafeB64Encode($details['rsa']['e'])]]]); }
    if (str_contains($url,'appleid.apple.com/auth/token')) { return $response(['id_token'=>JWT::encode(['iss'=>'https://appleid.apple.com','aud'=>'fixture.apple.client','sub'=>$apple_bad_subject?'other-subject':'apple-fixture-subject','iat'=>time()-1,'exp'=>time()+300],$private_key,'RS256','test-key'),'refresh_token'=>'apple-fixture-refresh-token']); }
    if (str_contains($url,'appleid.apple.com/auth/revoke')) { $apple_revoke_count++; return $response([]); }
    if (str_contains($url, 'oauth2.googleapis.com')) { return $response(['access_token' => 'fixture-access-token', 'expires_in' => 300]); }
    if (str_contains($url, 'accounts:lookup')) { if ($firebase_down) { return new WP_Error('test_outage','Fixture outage'); } $body=json_decode($args['body'],true); $uid=$body['localId'][0];return $response(['users'=>[['localId'=>$uid,'emailVerified'=>!str_contains($uid,'unverified'),'disabled'=>$disabled,'validSince'=>$revoked?time()+1:0]]]); }
    if (str_contains($url, 'accounts:delete')) { return $firebase_down ? new WP_Error('test_outage','Fixture outage') : $response([]); }
    if (str_contains($url, 'messages:send')) { $send_count++; if($push_fail)return $response(['error'=>['status'=>'UNAVAILABLE']],503);return $response(['name'=>'projects/quickfix-test/messages/fixture']); }
    throw new RuntimeException('Unexpected outbound request in acceptance tests.');
};
add_filter('pre_http_request', $http, 10, 3);
delete_transient('quickfix_firebase_certs'); delete_transient('quickfix_appcheck_jwks');
$passed = 0;
$check = static function (bool $condition,string $label) use (&$passed) { if(!$condition) throw new RuntimeException('FAIL: '.$label);$passed++;echo "PASS $label\n"; };
$expect = static function (callable $action,string $code,string $label) use ($check) { try {$action();} catch(Fault $e){$check($e->slug===$code,$label.' ['.$e->slug.']');return;}throw new RuntimeException('FAIL: '.$label.' (no rejection)'); };
$token = static function (string $uid,array $changes=[]) use ($private_key) { return JWT::encode(array_replace(['iss'=>'https://securetoken.google.com/quickfix-test','aud'=>'quickfix-test','sub'=>$uid,'iat'=>time()-1,'exp'=>time()+3600,'auth_time'=>time()-1,'email_verified'=>true,'email'=>'fixture@example.invalid','name'=>'Fixture cook','firebase'=>['identities'=>['google.com'=>[$uid]]]],$changes),$private_key,'RS256','test-key'); };
$identity = static function(string $uid,array $changes=[]) use ($token) { $request=new WP_REST_Request('POST','/quickfix/v1/me/session');$request->set_header('Authorization','Bearer '.$token($uid,$changes));return Identity::authenticate($request); };
try {
    $settings=Settings::get();$settings['firebase_project_id']='quickfix-test';$settings['firebase_project_number']='123';$settings['notifications_enabled']=true;$settings['public_rate']=365;$settings['write_rate']=365;update_option('quickfix_settings',$settings,false);
    $admin = get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']); wp_set_current_user($admin[0]);
    $check(count(Schema::definitions())===19,'MIG01 additive prefix-aware tables');
    Schema::activate(); foreach($before as $table=>$columns){$check($columns===$wpdb->get_col('SHOW COLUMNS FROM '.Store::table($table)),'MIG01 repeatable '.$table);}
    $check(in_array('star5',$before['rating_stats'],true),'RAT02 complete distribution schema');
    $video_id=$wpdb->insert(Catalog::source_table('cooking_videos'),['youtube_id'=>substr(str_replace('-','',$run),0,11),'video_url'=>'https://www.youtube.com/watch?v=abcdefghijk','title'=>'Acceptance fixture','visible'=>1]) ? $wpdb->insert_id : null;
    foreach(['published fixture','website-only fixture','incomplete fixture'] as $name){$post=wp_insert_post(['post_type'=>'recipes','post_status'=>'publish','post_title'=>'QuickFix '.$run.' '.$name]);$posts[]=$post;$wpdb->insert(Catalog::source_table('recipes'),['videoid'=>$video_id,'wp_post_id'=>$post,'title'=>$name,'description'=>'Fixture soup','featured_image_url'=>'https://www.quickfixwithbindu.com/fixture.jpg','total_time_minutes'=>15]);$id=(string)$wpdb->insert_id;$recipe_ids[]=$id;if($name!=='incomplete fixture'){$wpdb->insert(Catalog::source_table('recipe_ingredients'),['recipe_id'=>$id,'ingredient'=>'Carrot','quantity'=>'1']);$wpdb->insert(Catalog::source_table('recipe_steps'),['recipe_id'=>$id,'step_number'=>1,'instruction'=>'Cook the carrot.','timer_minutes'=>15]);}}
    [$recipe,$website,$incomplete]=$recipe_ids;
    $expect(fn()=>Catalog::get($recipe),'recipe_unavailable','CAT01 new recipes excluded');
    $expect(fn()=>Catalog::save($recipe,['revision'=>0,'state'=>'published']),'invalid_transition','CAT06 excluded must become draft');
    Catalog::save($recipe,['revision'=>0,'state'=>'draft']);Catalog::save($recipe,['revision'=>1,'state'=>'published']);
    $check(Catalog::get($recipe)['id']===$recipe,'CAT02 explicit publication');
    $expect(fn()=>Catalog::save($recipe,['revision'=>1,'summary_override'=>'stale']),'revision_conflict','CAT06 stale admin edit rejected');
    Catalog::save($incomplete,['revision'=>0,'state'=>'draft']);$expect(fn()=>Catalog::save($incomplete,['revision'=>1,'state'=>'published']),'recipe_incomplete','CAT02 incomplete recipe cannot publish');
    $check(Catalog::get($website,'website')['id']===$website,'WEB01 website-only source available to website');
    $expect(fn()=>Catalog::detail($website),'recipe_unavailable','CAT03 website-only direct app detail rejected');
    $listing=Catalog::listing(['q'=>'Carrot','limit'=>1]);$check(in_array($recipe,array_column($listing['items'],'id'),true),'CAT01 ingredient search sees only app recipes');
    Catalog::save($recipe,['revision'=>2,'available_from'=>gmdate('Y-m-d\TH:i:s\Z',time()+3600)]);$expect(fn()=>Catalog::get($recipe),'recipe_unavailable','CAT04 future start checked without cron');
    Catalog::save($recipe,['revision'=>3,'available_from'=>null,'available_to'=>gmdate('Y-m-d\TH:i:s\Z',time()-1)]);$expect(fn()=>Catalog::get($recipe),'recipe_unavailable','CAT04 expired end checked without cron');
    Catalog::save($recipe,['revision'=>4,'available_to'=>null]);wp_update_post(['ID'=>$posts[0],'post_status'=>'private']);Catalog::source_transition('private','publish',get_post($posts[0]));$expect(fn()=>Catalog::get($recipe),'recipe_unavailable','CAT05 source privacy enforced at origin');wp_update_post(['ID'=>$posts[0],'post_status'=>'publish']);Catalog::source_transition('publish','private',get_post($posts[0]));$expect(fn()=>Catalog::get($recipe),'recipe_unavailable','CAT05 restored source does not auto-republish');
    // Test environment skips hooks, so restore metadata explicitly as a curator would after a source withdrawal.
    $meta=Catalog::metadata($recipe);if($meta['state']==='withdrawn')Catalog::save($recipe,['revision'=>(int)$meta['revision'],'state'=>'published']);
    $wp_user_count=(int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
    $alice=$identity('qf-'.$run.'-alice');$bob=$identity('qf-'.$run.'-bob');$unverified=$identity('qf-'.$run.'-unverified');$user_ids=[(string)$alice['id'],(string)$bob['id'],(string)$unverified['id']];
    $check((int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}")===$wp_user_count,'AUTH02 consumer identity does not create WP administrator');
    $expect(fn()=>Firebase::verify($token('bad',['aud'=>'other-project'])),'invalid_session','AUTH01 wrong-project token rejected');
    $expect(fn()=>Firebase::verify($token('bad',['iss'=>'https://attacker.invalid'])),'invalid_session','AUTH01 wrong issuer rejected');
    $expect(fn()=>Firebase::verify($token('bad',['exp'=>time()-100])),'invalid_session','AUTH01 expired token rejected');
    $parts=explode('.',$token('bad'));$parts[2]=str_repeat('x',strlen($parts[2]));$expect(fn()=>Firebase::verify(implode('.',$parts)),'invalid_session','AUTH01 forged signature rejected');
    $revoked=true;$expect(fn()=>Firebase::verify($token('bad')),'invalid_session','AUTH01 revoked token rejected');$revoked=false;$disabled=true;$expect(fn()=>Firebase::verify($token('bad')),'invalid_session','AUTH01 disabled identity rejected');$disabled=false;
    $expect(fn()=>Community::create_comment($recipe,'app',$unverified,['text'=>'Hello'],'unverified-key'),'email_unverified','AUTH03 unverified commenter rejected');
    $expect(fn()=>Community::create_comment($recipe,'app',$alice,['text'=>'<script>alert(1)</script>'],'xss-test-key'),'invalid_text','SEC01 HTML rejected');
    $expect(fn()=>Community::create_comment($recipe,'app',$alice,['text'=>'https://spam.example.com'],'link-test-key'),'invalid_text','SEC01 links rejected');
    $comment=Community::create_comment($recipe,'app',$alice,['text'=>'Great soup.'],'comment-test-key');$again=Community::create_comment($recipe,'app',$alice,['text'=>'Great soup.'],'comment-test-key');$check($comment===$again,'COM04 same key replays one comment');
    $expect(fn()=>Community::create_comment($recipe,'app',$alice,['text'=>'Changed text.'],'comment-test-key'),'idempotency_conflict','COM04 different body with same key conflicts');
    $check(Community::comments($recipe,'app',[],null)['approved_count']===0,'COM01 pending comment is private');
    $expect(fn()=>Community::own_comment($comment['id'],$bob,['text'=>'Hijack','revision'=>1]),'not_found','COM03 cross-user comment edit rejected');
    Community::moderate('comments',$comment['id'],['revision'=>1,'status'=>'approved']);$check(Community::comments($recipe,'app',[],null)['approved_count']===1,'COM01 approved comment becomes public');
    Community::block((string)$alice['id'],$bob,'PUT');$check(Community::comments($recipe,'app',[],$bob)['approved_count']===0,'COM05 block filters comment counts and items');Community::block((string)$alice['id'],$bob,'DELETE');
    $report=Community::report($bob,['target_type'=>'comment','target_id'=>$comment['id'],'reason'=>'spam','channel'=>'app'],'report-test-key');$check($report['status']==='open','COM05 durable report');
    $expect(fn()=>Community::report($bob,['target_type'=>'comment','target_id'=>$comment['id'],'reason'=>'abuse','channel'=>'app'],'another-report-key'),'report_open','COM05 one unresolved report per target');
    Community::own_comment($comment['id'],$alice,['text'=>'Updated soup review.','revision'=>2]);$check(Community::comments($recipe,'app',[],null)['approved_count']===0,'COM02 edit returns approved text to pending');
    $rating=Community::rating($recipe,'app',$alice,['stars'=>5,'revision'=>0]);$check($rating['rating']['average']===5.0&&$rating['rating']['count']===1,'RAT01 one current rating');
    $expect(fn()=>Community::rating($recipe,'app',$alice,['stars'=>1,'revision'=>0]),'revision_conflict','RAT01 concurrent/stale rating conflicts');
    $rating=Community::rating($recipe,'website',$alice,['stars'=>4,'revision'=>1]);Community::rating($recipe,'app',$bob,['stars'=>2,'revision'=>0]);$stats=Community::stats($recipe);$check($stats['average']===3.0&&$stats['count']===2&&$stats['distribution'][4]===1,'WEB01 shared website/app rating totals');
    Community::moderate('ratings',$rating['my_rating']['id'],['revision'=>2,'status'=>'hidden','reason'=>'Fraudulent fixture']);$check(Community::stats($recipe)['average']===2.0,'RAT02 moderation excludes hidden rating');
    $expect(fn()=>Community::moderate('ratings',$rating['my_rating']['id'],['revision'=>3,'stars'=>1,'status'=>'active']),'unknown_fields','RAT02 moderators cannot change score');
    Community::own_rating($rating['my_rating']['id'],$alice);$check(Community::stats($recipe)['count']===1,'RAT02 own rating deletion recalculates');
    Catalog::save($recipe,['revision'=>(int)Catalog::metadata($recipe)['revision'],'state'=>'withdrawn']);$expect(fn()=>Community::comments($recipe,'app',[],null),'recipe_unavailable','CAT01 withdrawn mobile discussion inaccessible');Community::own_comment($comment['id'],$alice,null);$check(!$wpdb->get_var($wpdb->prepare('SELECT id FROM '.Store::table('comments').' WHERE id=%s',$comment['id'])),'COM02 own deletion after withdrawal removes text');
    Catalog::save($recipe,['revision'=>(int)Catalog::metadata($recipe)['revision'],'state'=>'published']);
    $cipher=Firebase::encrypt('fcm-fixture-token-0123456789');$check(Firebase::decrypt($cipher)==='fcm-fixture-token-0123456789'&&!str_contains($cipher,'fixture-token'),'PUSH01 tokens encrypted at rest');
    $attestation=JWT::encode(['iss'=>'https://firebaseappcheck.googleapis.com/123','aud'=>['projects/123'],'sub'=>'1:123:android:test','iat'=>time()-1,'exp'=>time()+300],$private_key,'RS256','test-key');
    $device_request=new WP_REST_Request('POST','/quickfix/v1/app/devices');$device_request->set_header('Idempotency-Key','device-test-key');$device_request->set_header('X-Firebase-AppCheck',$attestation);
    $device_data=['installation_id'=>str_replace('-','',$run),'fcm_token'=>'fcm-fixture-token-0123456789','platform'=>'android','app_version'=>'1.0.0','consent'=>true,'app_id'=>'1:123:android:test'];
    $device=Jobs::devices($device_request,$device_data,null);$replay=Jobs::devices($device_request,$device_data,null);$check($device['id']===$replay['id']&&$device['registration_credential']===$replay['registration_credential'],'PUSH01 lost registration response securely replays');
    $bad_request=new WP_REST_Request('DELETE','/quickfix/v1/app/devices');$bad_request->set_header('Idempotency-Key','device-delete-key');$bad_request->set_header('X-Quickfix-Device','wrong');$expect(fn()=>Jobs::devices($bad_request,['installation_id'=>$device_data['installation_id']],null),'device_ownership_required','PUSH01 installation ID alone cannot opt out device');
    $campaign=Jobs::campaign(null,['title'=>'Fixture','body'=>'Fixture notification','target'=>$recipe,'audience'=>'all']);$check($campaign['status']==='draft','PUSH01 draft does not send');
    $expect(fn()=>Jobs::send((string)$campaign['id'],['revision'=>1,'confirm'=>false],'campaign-test-key'),'confirmation_required','PUSH01 sending requires explicit confirmation');
    Jobs::send((string)$campaign['id'],['revision'=>1,'confirm'=>true],'campaign-test-key');Jobs::plan(['campaign_id'=>(string)$campaign['id'],'after'=>0]);Jobs::deliver(['campaign_id'=>(string)$campaign['id'],'device_id'=>$device['id']]);Jobs::deliver(['campaign_id'=>(string)$campaign['id'],'device_id'=>$device['id']]);$check($send_count===1,'PUSH01 completed device chunk is not sent twice');
    Jobs::finalize(['campaign_id'=>(string)$campaign['id']]);$check(Store::row('campaigns',(string)$campaign['id'])['status']==='completed','PUSH01 completion means transport acceptance');
    $foreign_jobs=(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.Store::table('jobs')." WHERE status IN ('queued','running') AND dedupe_key NOT LIKE %s",'campaign-%:'.$campaign['id'].'%'));$check($foreign_jobs===0,'PUSH01 fixture queue isolated from other work');
    $scheduled=Jobs::campaign(null,['title'=>'Scheduled fixture','body'=>'Retry fixture','target'=>'home','scheduled_at'=>gmdate('Y-m-d\TH:i:s\Z',time()+3600)]);$extra_campaigns[]=(string)$scheduled['id'];Jobs::send((string)$scheduled['id'],['revision'=>1,'confirm'=>true],'scheduled-fixture-key');$check(Store::row('campaigns',(string)$scheduled['id'])['status']==='scheduled','PUSH01 confirmed future campaign waits for schedule');
    Store::update('campaigns',['scheduled_at'=>gmdate('Y-m-d H:i:s',time()-1)],['id'=>$scheduled['id']]);$push_fail=true;Jobs::run();
    $retry_job=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.Store::table('jobs')." WHERE dedupe_key=%s",'campaign-device:'.$scheduled['id'].':'.$device['id']),ARRAY_A);$check($retry_job&&$retry_job['status']==='queued'&&$retry_job['safe_error']==='firebase_unavailable','PUSH01 transient send failure retains retry and safe code');
    $push_fail=false;Store::update('jobs',['status'=>'running','lock_until'=>gmdate('Y-m-d H:i:s',time()-1),'available_at'=>Store::now()],['id'=>$retry_job['id']]);Jobs::run();$check(Store::row('campaigns',(string)$scheduled['id'])['accepted']==1,'PUSH01 interrupted lease reclaimed and send accepted');
    $wpdb->query($wpdb->prepare('UPDATE '.Store::table('jobs').' SET available_at=%s WHERE dedupe_key=%s',Store::now(),'campaign-finalize:'.$scheduled['id']));Jobs::run();$check(Store::row('campaigns',(string)$scheduled['id'])['status']==='completed','PUSH01 scheduled retry campaign finalizes');$sends_after=$send_count;Jobs::run();$check($send_count===$sends_after,'PUSH01 completed queued campaign does not repeat delivery');
    $apple_key=openssl_pkey_new(['private_key_type'=>OPENSSL_KEYTYPE_EC,'curve_name'=>'prime256v1']);openssl_pkey_export($apple_key,$apple_private);
    $apple_file=tempnam(sys_get_temp_dir(),'qf-apple-');file_put_contents($apple_file,wp_json_encode(['team_id'=>'fixture-team','key_id'=>'fixture-key','client_id'=>'fixture.apple.client','private_key'=>$apple_private]));putenv('QUICKFIX_APPLE_CREDENTIALS='.$apple_file);delete_transient('quickfix_apple_jwks');
    $apple_user=$identity('qf-'.$run.'-apple',['firebase'=>['identities'=>['apple.com'=>['apple-fixture-subject']]]]);$user_ids[]=(string)$apple_user['id'];
    $expect(fn()=>Jobs::deletion($apple_user,['confirm'=>true],'apple-deletion-key'),'apple_authorization_required','DELETE01 Apple deletion requires revocation material');
    $apple_bad_subject=true;$expect(fn()=>Apple::associate($apple_user,'fixture-code'),'apple_identity_mismatch','AUTH04 Apple authorization subject cannot change account');$apple_bad_subject=false;Apple::associate($apple_user,'fixture-code');
    $apple_cipher=$wpdb->get_var($wpdb->prepare('SELECT token_cipher FROM '.Store::table('provider_tokens').' WHERE user_id=%s',$apple_user['id']));$check($apple_cipher&&!str_contains($apple_cipher,'apple-fixture-refresh-token'),'SEC02 provider revocation token encrypted');
    Jobs::deletion($apple_user,['confirm'=>true],'apple-deletion-key');$apple_hash=Firebase::identity_hash($apple_user['firebase_uid']);$extra_hashes[]=$apple_hash;$firebase_down=true;
    $expect(fn()=>Jobs::finish_deletion(['user_id'=>(string)$apple_user['id'],'identity_hash'=>$apple_hash,'apple_revocation'=>true]),'firebase_unavailable','DELETE01 Apple revocation proof survives Firebase outage');
    $firebase_down=false;Jobs::finish_deletion(['user_id'=>(string)$apple_user['id'],'identity_hash'=>$apple_hash,'apple_revocation'=>true]);$check($apple_revoke_count===1&&Store::row('users',(string)$apple_user['id'])['status']==='deleted','DELETE01 retry does not revoke Apple twice or skip completion');
    $old=$alice;$old['_claims']['auth_time']=time()-600;$expect(fn()=>Jobs::deletion($old,['confirm'=>true],'deletion-test-key'),'reauthentication_required','DELETE01 recent reauthentication required');
    $result=Jobs::deletion($alice,['confirm'=>true],'deletion-test-key');$check($result['status']==='deletion_pending','DELETE01 deletion immediately disables participation');
    $expect(fn()=>Community::rating($recipe,'app',$alice,['stars'=>5,'revision'=>0]),'community_unavailable','DELETE01 pending deletion prevents writes');
    $hash=Firebase::identity_hash($alice['firebase_uid']);$firebase_down=true;$expect(fn()=>Jobs::finish_deletion(['user_id'=>(string)$alice['id'],'identity_hash'=>$hash]),'firebase_unavailable','DELETE01 third-party outage remains retryable');$check(Store::row('users',(string)$alice['id'])['status']==='deletion_pending'&&Store::row('users',(string)$alice['id'])['email']===null,'DELETE01 partial failure scrubs identifiable profile');
    $firebase_down=false;Jobs::finish_deletion(['user_id'=>(string)$alice['id'],'identity_hash'=>$hash]);$check(Store::row('users',(string)$alice['id'])['status']==='deleted','DELETE01 identity and data deletion complete');
    $expect(fn()=>$identity('qf-'.$run.'-alice'),'account_deleted','DELETE02 tombstone prevents restored identity access');
    $server=rest_get_server();API::register();$nonce=wp_create_nonce('wp_rest');$request=new WP_REST_Request('GET','/quickfix/v1/admin/settings');$response=$server->dispatch($request);$check($response->get_status()===403,'SEC01 administrator without nonce rejected');
    $request->set_header('X-WP-Nonce',$nonce);$check($server->dispatch($request)->get_status()===200,'SEC01 authorized nonce permits administrator');
    wp_set_current_user(0);$check($server->dispatch($request)->get_status()===403,'SEC01 nonce alone does not grant capability');
    $request=new WP_REST_Request('GET','/quickfix/v1/app/recipes');$request->set_query_params(['q'=>"x' OR 1=1 --"]);$response=$server->dispatch($request);$check($response->get_status()===200,'SEC01 search SQL input safely prepared');
    $payload=wp_json_encode($response->get_data());$check(!str_contains($payload,'firebase_uid')&&!str_contains($payload,'token_cipher')&&!str_contains($payload,'fixture@example.invalid'),'SEC02 public payload omits private identity data');
    wp_set_current_user($admin[0]);
    $bulk=static function($body)use($server){$r=new WP_REST_Request('POST','/quickfix/v1/admin/recipes/bulk');$r->set_header('Content-Type','application/json');$r->set_header('X-WP-Nonce',wp_create_nonce('wp_rest'));$r->set_body(wp_json_encode($body));return $server->dispatch($r);};
    $unconfirmed=$bulk(['items'=>[['id'=>$website,'revision'=>0]],'action'=>'publish','confirm'=>false]);$check($unconfirmed->get_status()===422&&$unconfirmed->get_data()['error']['code']==='confirmation_required','CAT06 bulk publication requires explicit review');
    $check(Catalog::metadata($website)['state']==='excluded','CAT06 unconfirmed bulk action preserves exclusion');
    $stale=$bulk(['items'=>[['id'=>$website,'revision'=>1]],'action'=>'publish','confirm'=>true]);$check($stale->get_data()['data']['results'][0]['error']['code']==='revision_conflict'&&Catalog::metadata($website)['state']==='excluded','CAT06 stale bulk publication preserves exclusion');
    Catalog::save($incomplete,['revision'=>1,'state'=>'excluded']);
    $published=$bulk(['items'=>[['id'=>$website,'revision'=>0],['id'=>$incomplete,'revision'=>2]],'action'=>'publish','confirm'=>true]);$results=$published->get_data()['data']['results'];
    $check($published->get_status()===200&&$results[0]['ok']&&!$results[1]['ok'],'CAT06 mixed bulk publication returns per-item results');
    $check(Catalog::metadata($website)['state']==='published'&&(int)Catalog::metadata($website)['revision']===2,'CAT06 confirmed bulk publication prepares draft and publishes');
    $check($results[1]['error']['code']==='recipe_incomplete'&&Catalog::metadata($incomplete)['state']==='excluded'&&(int)Catalog::metadata($incomplete)['revision']===2,'CAT06 incomplete bulk publication rolls back draft preparation');
    $check(Catalog::detail($website)['id']===$website,'CAT06 bulk-published recipe available through mobile detail');
    $first=Catalog::listing(['limit'=>1]);$check((bool)$first['next_cursor'],'CAT01 listing returns opaque cursor');$second=Catalog::listing(['limit'=>1,'cursor'=>$first['next_cursor']]);$check($first['items'][0]['id']!==$second['items'][0]['id'],'CAT01 stable pagination does not repeat IDs');
    $expect(fn()=>Catalog::listing(['limit'=>1,'sort'=>'cooking_time','cursor'=>$first['next_cursor']]),'cursor_restart','CAT01 cursor bound to query and sort');
    Store::bump();$expect(fn()=>Catalog::listing(['limit'=>1,'cursor'=>$first['next_cursor']]),'cursor_restart','CAT02 catalog change restarts pagination');
    Store::rate('acceptance-test',$run,1,60);$expect(fn()=>Store::rate('acceptance-test',$run,1,60),'rate_limited','SEC01 durable rate counter returns throttling');
    wp_set_current_user($admin[0]);$category=API::curation('app_categories',null,['name'=>'Fixture category','slug'=>'fixture-'.$run],'POST');Catalog::save($website,['revision'=>2,'categories'=>[(string)$category['id']]]);$check(in_array((string)$category['id'],array_column(Catalog::categories(),'id'),true),'CAT01 enabled nonempty category appears');
    API::curation('app_categories',(string)$category['id'],['revision'=>1,'enabled'=>false],'PATCH');$check(!in_array((string)$category['id'],array_column(Catalog::categories(),'id'),true),'CAT01 disabled category hidden without removing recipe');
    $check(Catalog::get($website)['id']===$website,'CAT01 disabled category does not withdraw recipe');
    $expect(fn()=>Settings::save(['revision'=>Settings::get()['revision'],'privacy_url'=>'javascript:alert(1)']),'invalid_url','SEC01 settings reject unsafe URL schemes');
    $race=$identity('qf-'.$run.'-race');$user_ids[]=(string)$race['id'];$wpcli=getenv('QUICKFIX_TEST_WPCLI');
    if($wpcli&&is_file($wpcli)){
        $processes=[];for($i=0;$i<10;$i++){$pipes=[];$proc=proc_open([PHP_BINARY,'-c',php_ini_loaded_file(),$wpcli,'--skip-plugins','--skip-themes','eval-file',__DIR__.'/rating-worker.php',(string)$race['id'],$recipe,(string)($i%5+1)],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,ABSPATH,null,['bypass_shell'=>true]);if(!is_resource($proc))throw new RuntimeException('Concurrency worker failed.');fclose($pipes[0]);$processes[]=[$proc,$pipes];}
        $results=[];foreach($processes as[$proc,$pipes]){$results[]=trim(stream_get_contents($pipes[1]));$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);if(proc_close($proc)!==0)throw new RuntimeException('Concurrency worker failed: '.$err);}
        $check(count(array_filter($results,fn($v)=>$v==='saved'))===1&&count(array_filter($results,fn($v)=>$v==='revision_conflict'))===9,'RAT01 ten concurrent writers leave one accepted revision');
        $check((int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.Store::table('ratings').' WHERE recipe_id=%s AND user_id=%s',$recipe,$race['id']))===1,'RAT01 unique rating persists under real concurrency');
        $sum=(int)$wpdb->get_var($wpdb->prepare('SELECT SUM(stars) FROM '.Store::table('ratings')." WHERE recipe_id=%s AND status='active'",$recipe));$stats_row=$wpdb->get_row($wpdb->prepare('SELECT total,count FROM '.Store::table('rating_stats').' WHERE recipe_id=%s',$recipe),ARRAY_A);$check((int)$stats_row['total']===$sum&&$stats_row['count']==2,'RAT02 aggregate remains accurate under concurrent requests');
    }else{echo "SKIP real concurrency: set QUICKFIX_TEST_WPCLI to the local WP-CLI PHAR.\n";}
    echo "\n$passed acceptance assertions passed. Firebase/FCM were intercepted locally; no real messages or identities were touched.\n";
} finally {
    remove_filter('pre_http_request',$http,10);unlink($credentials);if($apple_file)unlink($apple_file);
    $old_suppress=$wpdb->suppress_errors(true);
    foreach($user_ids as $id){foreach(['comments','ratings','provider_tokens'] as $table)$wpdb->delete(Store::table($table),['user_id'=>$id]);$wpdb->delete(Store::table('reports'),['reporter_id'=>$id]);$wpdb->query($wpdb->prepare('DELETE FROM '.Store::table('user_blocks').' WHERE blocker_user_id=%s OR blocked_user_id=%s',$id,$id));$wpdb->delete(Store::table('users'),['id'=>$id]);}
    foreach($recipe_ids as $id){foreach(['mobile_recipes','recipe_categories','home_placements','comments','ratings','rating_stats'] as $table)$wpdb->delete(Store::table($table),['recipe_id'=>$id]);$wpdb->delete(Catalog::source_table('recipes'),['id'=>$id]);}
    if(isset($device))$wpdb->delete(Store::table('devices'),['id'=>$device['id']]);
    if(isset($campaign)){$wpdb->delete(Store::table('campaign_deliveries'),['campaign_id'=>$campaign['id']]);$wpdb->delete(Store::table('campaigns'),['id'=>$campaign['id']]);$wpdb->query($wpdb->prepare('DELETE FROM '.Store::table('jobs').' WHERE dedupe_key LIKE %s','campaign-%:'.$campaign['id'].'%'));}
    foreach($extra_campaigns as$id){$wpdb->delete(Store::table('campaign_deliveries'),['campaign_id'=>$id]);$wpdb->delete(Store::table('campaigns'),['id'=>$id]);$wpdb->query($wpdb->prepare('DELETE FROM '.Store::table('jobs').' WHERE dedupe_key LIKE %s','campaign-%:'.$id.'%'));}
    foreach($extra_hashes as$extra_hash){$wpdb->delete(Store::table('deletion_tombstones'),['identity_hash'=>$extra_hash]);$wpdb->delete(Store::table('jobs'),['dedupe_key'=>'deletion:'.$extra_hash]);}
    if(isset($hash)){$wpdb->delete(Store::table('deletion_tombstones'),['identity_hash'=>$hash]);$wpdb->delete(Store::table('jobs'),['dedupe_key'=>'deletion:'.$hash]);}
    if(isset($category))$wpdb->delete(Store::table('app_categories'),['id'=>$category['id']]);
    $wpdb->delete(Store::table('audit'),['request_id'=>Store::request_id()]);
    foreach($posts as $post)wp_delete_post($post,true);if($video_id)$wpdb->delete(Catalog::source_table('cooking_videos'),['id'=>$video_id]);
    update_option('quickfix_settings',$saved_settings,false);Store::bump();
    foreach(['quickfix_last_queue_run'=>$saved_queue,'quickfix_last_maintenance'=>$saved_maintenance,'quickfix_api_health'=>$saved_health] as $name=>$value){if($value===false)delete_option($name);else update_option($name,$value,false);}
    $wpdb->suppress_errors($old_suppress);
}
