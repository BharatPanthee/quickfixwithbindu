<?php
if (!defined('WP_CLI') || !WP_CLI || !in_array(wp_get_environment_type(), ['local','development'], true)) { throw new RuntimeException('Load worker requires local/development WP-CLI.'); }
require_once dirname(__DIR__) . '/quickfix-mobile-manager.php';
use QuickFix\API;
if (!post_type_exists('recipes')) { register_post_type('recipes', ['public' => true, 'rewrite' => ['slug' => 'recipes']]); }
$_SERVER['REMOTE_ADDR']='198.51.100.'.((int)($args[0]??0)+1);
$server=rest_get_server();API::register();$times=[];
for($i=0;$i<10;$i++){$request=new WP_REST_Request('GET','/quickfix/v1/app/recipes');$request->set_query_params(['q'=>'load fixture']);$start=microtime(true);$response=$server->dispatch($request);$times[]=(microtime(true)-$start)*1000;if($response->get_status()!==200||count($response->get_data()['data']['items']??[])!==20)throw new RuntimeException('Unexpected catalog response.');}
echo wp_json_encode($times);
