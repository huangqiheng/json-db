<?php
require_once '../../admin/functions.php';

$req = get_param();
list($db_name, $tables, $apikey) = null_exit($req, 'db', 'tables', 'apikey');

if (!is_array($tables)) {
	jsonp_nocache_exit(['status'=>'error', 'error'=>'tables must be array']);
}

$results = [];

foreach($tables as $table_name) {
	if (!api_valid($db_name, $table_name, $apikey)) {
		continue;
	}

	$table_root = table_root($db_name, $table_name);
	$items = [];
	foreach(glob("{$table_root}/*.json") as $file) {
		if (is_dir($file)) {continue;}
		if (!preg_match('~/(\d+)\.json$~',$file, $matches)){continue;}
		$item_id = $matches[1];

		$data_obj = object_read($file);
		if (empty($data_obj)) {return;}

		$merge_items = merge_fields($data_obj);
		unset($merge_items['ID']);
		unset($merge_items['CREATE']);
		unset($merge_items['TIME']);
		$items[] = $merge_items;
	}

	$results[$table_name] = $items;
}

jsonp_nocache_exit(['status'=>'ok', 'md5'=>md5(json_encode($results)), 'count'=>count($results), 'items'=>$results]); 

