<?php
require_once 'functions.php';

$req = get_param();
list($db_name,$table_name,$apikey) = null_exit($req,'db_name','table_name','apikey');
api_exit($db_name, $table_name, $apikey);

$table_path = table_root($db_name, $table_name);
$schema = object_read("{$table_path}/schema.json");
$fields = $schema['fields'];

$onebox_url_routes = [];
foreach($fields as $group_name=>$group_data) {
	foreach($group_data as $field_name=>$field_type) {
		if (preg_match('/^jqxListBox-onebox-url/i', $field_type)) {
			$onebox_url_routes[] = [$group_name, $field_name];
		}
	}
}

$changed_urls = [];

foreach(glob("{$table_path}/*.json") as $file) {
	if (is_dir($file)) {continue;}
	if (!preg_match('~/(\d+)\.json$~',$file, $matches)){continue;}
	$item_id = $matches[1];

	$data_obj = object_read($file);
	if (empty($data_obj)) {continue;}

	foreach($onebox_url_routes as $route) {
		$oneboxs = @$data_obj[$route[0]][$route[1]];
		if (empty($oneboxs)) {
			continue;
		}
		$new_oneboxs = [];
		foreach($oneboxs as $onebox) {
			$url = $onebox['url'];
			$res_obj = get_onebox_url($url);
			$new_onebox = [];
			$new_onebox['title'] = $res_obj['title'];
			$new_onebox['desc'] = $res_obj['description'];
			$new_onebox['image'] = $res_obj['image'];
			$new_onebox['url'] = $res_obj['ori_url'];
			$new_onebox['id'] = $res_obj['ID'];
			$new_onebox['time'] = $res_obj['update_time'];
			$new_onebox['ctime'] = $res_obj['create_time'];
			$new_oneboxs[] = $new_onebox;

			if (!same_onebox($new_onebox, $onebox)) {
				$changed_urls[] = $url;
			}
		}

		$data_obj[$route[0]][$route[1]] = $new_oneboxs;
	}

	object_save($file, $data_obj);
}

jsonp_nocache_exit($changed_urls); 

function same_onebox($a_onebox, $b_onebox)
{
	foreach($a_onebox as $key=>$val) {
		if ($val !== @$b_onebox[$key]) {
			return false;
		}
	}
	return true;
}


