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

$onebox = @$schema['onebox'];
$key_title = @$onebox['title'];
$key_desc = @$onebox['desc'];
$key_image = @$onebox['image'];
$key_title || $key_title = 'ID';
if (empty($key_desc)) {
	foreach(merge_fields($schema['fields']) as $key=>$type) {
		if (in_array($type, $mapper_types)) {
			$key_title = $key;
			break;
		}
	}
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

$json_file_uri = substr($map_file, strlen($_SERVER['DOCUMENT_ROOT']));
$json_file_uri = preg_replace('/\/'.$_SERVER['HTTP_HOST'].'/i', '', $json_file_uri);
$json_file_uri = $http_prefix.$json_file_uri;

$res_obj = object_read($map_file);
$res_obj = merge_fields($res_obj);

$ob_title = $res_obj[$key_title];
$ob_desc = 'no description.';
if (!empty($key_desc)) {
	$ob_desc = $res_obj[$key_desc];
}
$ob_image = $http_prefix.$schema['caption']['image'];
if (!empty($key_image)) {
	$ob_image = $res_obj[$key_image];
}

$ob_time = @$res_obj['TIME'];
if ($ob_time) {
	$ob_time = format_time($ob_time);
}

$res = array();
$res['provider_name'] = '任玩堂游戏数据库';
$res['provider_url'] = 'http://db.appgame.com/';
$res['favicon_url'] = 'http://www.appgame.com/favicon.ico';
$res['ori_url'] = $json_file_uri;
$res['ID'] = intval($map_val);
$res['title'] = $ob_title;
$res['image'] = $ob_image;
$res['update_time'] = $ob_time;
$res['description'] = strip_tags($ob_desc);

$res_type = @$req['type'];
if ($res_type === 'json') {
	$res['status'] = 'ok';
	return jsonp_nocache_exit($res);
}
return html_nocache_exit(onebox_output($res));


?>
