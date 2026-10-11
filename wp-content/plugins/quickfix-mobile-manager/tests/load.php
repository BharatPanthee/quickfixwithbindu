<?php
/** Local-only fixture load runner. wp --skip-plugins --skip-themes eval-file tests/load.php */
if (!defined('WP_CLI') || !WP_CLI || !in_array(wp_get_environment_type(), ['local','development'], true)) { throw new RuntimeException('Load fixtures require local/development WP-CLI.'); }
require_once dirname(__DIR__) . '/quickfix-mobile-manager.php';
use QuickFix\{API,Catalog,Store};
global $wpdb;
if (!post_type_exists('recipes')) { register_post_type('recipes', ['public' => true, 'rewrite' => ['slug' => 'recipes']]); }
$run=wp_generate_uuid4();$ids=[];$posts=[];$processes=[];$workers=50;$video=null;$started=microtime(true);
$ini=php_ini_loaded_file();
// WP_CLI_ROOT is inside the PHAR. The launcher supplied by the developer is an explicit argument.
$wpcli=getenv('QUICKFIX_TEST_WPCLI');if(!$wpcli||!is_file($wpcli))throw new RuntimeException('Set QUICKFIX_TEST_WPCLI to the installed wp-cli.phar path.');
$settings=get_option('quickfix_settings');
try{
    putenv('QUICKFIX_MEDIA_HOSTS=www.quickfixwithbindu.com');
    $wpdb->insert(Catalog::source_table('cooking_videos'),['youtube_id'=>substr(str_replace('-','',$run),0,11),'video_url'=>'https://www.youtube.com/watch?v=abcdefghijk','title'=>'Load fixture','visible'=>1]);$video=$wpdb->insert_id;
    $now=Store::now();
    for($i=0;$i<1000;$i++){
        $wpdb->insert($wpdb->posts,['post_author'=>0,'post_date'=>$now,'post_date_gmt'=>$now,'post_content'=>'','post_title'=>'QuickFix load '.$run.' '.$i,'post_excerpt'=>'','post_status'=>'publish','comment_status'=>'closed','ping_status'=>'closed','post_password'=>'','post_name'=>'qf-load-'.$run.'-'.$i,'to_ping'=>'','pinged'=>'','post_modified'=>$now,'post_modified_gmt'=>$now,'post_content_filtered'=>'','post_parent'=>0,'guid'=>'','menu_order'=>0,'post_type'=>'recipes','post_mime_type'=>'','comment_count'=>0]);$post=$wpdb->insert_id;$posts[]=$post;
        $wpdb->insert(Catalog::source_table('recipes'),['videoid'=>$video,'wp_post_id'=>$post,'title'=>'Load fixture '.$i,'description'=>'Carrot soup load fixture','featured_image_url'=>'https://www.quickfixwithbindu.com/fixture.jpg','total_time_minutes'=>15]);$id=(string)$wpdb->insert_id;$ids[]=$id;
        $wpdb->insert(Catalog::source_table('recipe_ingredients'),['recipe_id'=>$id,'ingredient'=>'Carrot']);$wpdb->insert(Catalog::source_table('recipe_steps'),['recipe_id'=>$id,'step_number'=>1,'instruction'=>'Cook carrot.']);
        Store::insert('mobile_recipes',['recipe_id'=>$id,'state'=>'published','published_at'=>$now,'summary_override'=>'','dietary_tags'=>'[]','updated_at'=>$now]);
    }
    Store::bump();Catalog::listing(['q'=>'load fixture']);
    $server=rest_get_server();API::register();$detailStart=microtime(true);Catalog::detail($ids[0]);$detail=(microtime(true)-$detailStart)*1000;
    for($i=0;$i<$workers;$i++){
        $pipes=[];$command=[PHP_BINARY,'-c',$ini,$wpcli,'--skip-plugins','--skip-themes','eval-file',__DIR__.'/load-worker.php',(string)$i];
        $process=proc_open($command,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,ABSPATH,null,['bypass_shell'=>true]);
        if(!is_resource($process))throw new RuntimeException('Could not start load worker.');fclose($pipes[0]);$processes[]=[$process,$pipes];
    }
    $times=[];
    foreach($processes as [$process,$pipes]){$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$code=proc_close($process);if($code!==0)throw new RuntimeException('Load worker failed: '.$err);$result=json_decode(trim($out),true);if(!is_array($result))throw new RuntimeException('Invalid load result.');$times=array_merge($times,$result);}
    $processes=[];sort($times);$p95=$times[(int)ceil(count($times)*.95)-1];
    $report=['fixture_recipes'=>1000,'concurrent_workers'=>$workers,'requests'=>count($times),'cached_dispatch_p95_ms'=>round($p95,2),'uncached_detail_ms'=>round($detail,2),'elapsed_seconds'=>round(microtime(true)-$started,2),'scope'=>'Local in-process REST handler dispatch, excluding WordPress bootstrap, network and CDN latency.'];
    echo wp_json_encode($report,JSON_PRETTY_PRINT).PHP_EOL;
    if($p95>500||$detail>1000)throw new RuntimeException('Performance budget exceeded in isolated handler load.');
}finally{
    foreach($processes as [$process,$pipes]){if(is_resource($process))proc_terminate($process);foreach($pipes as $pipe)if(is_resource($pipe))fclose($pipe);}
    foreach($ids as $id){$wpdb->delete(Store::table('mobile_recipes'),['recipe_id'=>$id]);$wpdb->delete(Catalog::source_table('recipes'),['id'=>$id]);}
    foreach($posts as $post){$wpdb->delete($wpdb->posts,['ID'=>$post]);clean_post_cache($post);}
    if($video)$wpdb->delete(Catalog::source_table('cooking_videos'),['id'=>$video]);Store::bump();
}
