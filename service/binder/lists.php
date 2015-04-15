<?php
require_once '../../admin/functions.php';

define('AUTO_FIELD_LIST', 'auto-field-list');
define('AUTO_FIELD_CAPVIEW', 'auto-field-capview');

$req = get_param();
$db_name = @$req['db_name'];
$db_name || $db_name = 'default';
$table_name = @$req['table_name'];
$table_name || $table_name = 'default';
$list_caption = @$req['caption'];
$list_item = @$req['caption'];
$map_key = mapper_key(@$req['mapper']);

if (!api_valid($db_name, $table_name, @$req['apikey'])) {
	jsonp_nocache_exit(array('status'=>'error', 'error'=>'api key error'));
}

if (empty($map_key)) {
	jsonp_nocache_exit(array('status'=>'error', 'error'=>'not mapper in parameter'));
}

$table_root = table_root($db_name, $table_name);
$schema = object_read("{$table_root}/schema.json");
$mapper = object_read("{$table_root}/mapper.json");
