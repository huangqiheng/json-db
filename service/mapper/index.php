<?php
require_once '../../admin/functions.php';

$req = get_param();

$db_name = @$req['db_name'];
$table_name = @$req['table_name'];
$db_name || $db_name = 'default';
$table_name || $table_name = 'default';
$table_root = table_root($db_name, $table_name);

$mapper = object_read("{$table_root}/mapper.json");
if (empty($mapper)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'not found mapper.json']);
}

$map_key = @$req['name'];
$direct = @$req['direct'];
$map_key= mapper_key($map_key);
$map_val = $mapper[$map_key];
$map_file = "{$table_root}/{$map_val}.json";
if (!file_exists($map_file)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'not found target file']);
}

$json_file_uri = substr($map_file, strlen($_SERVER['DOCUMENT_ROOT']));
$json_file_uri = preg_replace('/\/'.$_SERVER['HTTP_HOST'].'/i', '', $json_file_uri);

if ($direct === 'true') {
	jsonp_nocache_exit(object_read($map_file)); 
}

if ($direct === 'false') {
	jsonp_nocache_exit(['status'=>'ok', 'ID'=>$map_val, 'data_file'=>$json_file_uri]); 
}

header("Location: {$json_file_uri}");

