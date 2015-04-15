<?php
require_once '../../admin/functions.php';

$req = get_param();

$db_name = @$req['db_name'];
$table_name = @$req['table_name'];
$db_name || $db_name = 'default';
$table_name || $table_name = 'default';
$map_key = @$req['name'];
$direct = @$req['direct'];

list($map_val,$data_file,$data_url)=mapper_value_exit($db_name, $table_name, $map_key);

if ($direct === 'true') {
	jsonp_nocache_exit(object_read($data_file)); 
}

if ($direct === 'false') {
	jsonp_nocache_exit(array('status'=>'ok', 'ID'=>$map_val, 'data_file'=>$data_url)); 
}

header("Location: {$data_url}");

