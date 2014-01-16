<?php
require_once 'functions.php';

$req = get_param();

$db_name = @$req['db_name'];
$table_name = @$req['table_name'];
$db_name || $db_name = 'default';
$table_name || $table_name = 'default';
$table_root = table_root($db_name, $table_name);
$http_prefix = 'http://'.$_SERVER['HTTP_HOST'];

$mapper = object_read("{$table_root}/mapper.json");
if (empty($mapper)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'not found mapper.json']);
}

$schema = object_read("{$table_root}/schema.json");
if (empty($schema)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'not found schema.json']);
}

$map_key = @$req['name'];
if (empty($map_key)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'map_key is empty.']);
}

$map_key= mapper_key($map_key);
$map_val = @$mapper[$map_key];

if (empty($map_val)) {
	$map_val = strval(intval($map_key));
}

$map_file = "{$table_root}/{$map_val}.json";
if (!file_exists($map_file)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'not found target file']);
}

$onebox = onebox_object($schema, $map_file);

$res = array();
$res['provider_name'] = '任玩堂游戏数据库';
$res['provider_url'] = 'http://db.appgame.com/';
$res['favicon_url'] = 'http://www.appgame.com/favicon.ico';
$res['ori_url'] = $onebox['url'];
$res['ID'] 	= $onebox['id'];
$res['title'] 	= $onebox['title'];
$res['image'] 	= $onebox['image'];
$res['update_time'] = $onebox['time'];
$res['description'] = $onebox['desc'];

$res_type = @$req['type'];
if ($res_type === 'json') {
	$res['status'] = 'ok';
	return jsonp_nocache_exit($res);
}
return html_nocache_exit(onebox_output($res));

?>
