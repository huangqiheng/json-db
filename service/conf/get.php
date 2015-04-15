<?php
/*-----------------------------
将jsondb的多个数据表格，当做配置数据的时候使用
可以一次获取多张数据表的数据作为一个配置列表
apikey就选用该数据库的密钥
-----------------------------*/

require_once '../../admin/functions.php';

$req = get_param();
list($db_name, $tables, $apikey) = null_exit($req, 'db', 'tables', 'apikey');

if (!is_array($tables)) {
	jsonp_nocache_exit(array('status'=>'error', 'error'=>'tables must be array'));
}

$results = [];
foreach ($tables as $table_name) {
	if (!api_valid($db_name, $table_name, $apikey)) {
		continue;
	}
	$results[$table_name] = objects_read($db_name, $table_name);
}

jsonp_nocache_exit(array('status'=>'ok', 'md5'=>md5(json_encode($results)), 'count'=>count($results), 'items'=>$results)); 

