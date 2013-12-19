<?php
require_once 'functions.php';
denies_with_json();

$req = get_param();

switch($req['cmd']) {
    case 'update': update_data_exit($req);
    case 'create': create_data_exit($req);
    case 'delete': delete_data_exit($req);
    default: jsonp_nocache_exit(['status'=>'error', 'error'=>'unknow command.']);
}

function delete_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'schema file error']);
	}

	$req_list = @$req['list'];
	if (empty($req_list)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'input list error']);
	}

	$listview_file = "{$table_root}/listview.json";
	$listview_data = object_read($listview_file);
	$id_index = array_search('ID', $schema['listview']);

	$listview_new = [];
	$rep_list = [];
	foreach($listview_data as $subitem) {
		$id_cmp = $subitem[$id_index];
		if (in_array($id_cmp, $req_list)) {
			$rep_list[] = $id_cmp;
		} else {
			$listview_new[] = $subitem;
		}
	}

	foreach($rep_list as $item_id) {
		$id_file = "{$table_root}/{$item_id}.json";
		if (file_exists($id_file)) {
			unlink($id_file);
		}
	}

	object_save($listview_file, $listview_new);
	jsonp_nocache_exit(['status'=>'ok', 'id_list'=>$rep_list]); 
}

function create_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'schema file error']);
	}

	$data = @$req['data'];
	if (empty($data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'req data error']);
	}

	$new_id = get_random_id();

	foreach($data as $group=>&$items){
		if (array_key_exists('ID', $items)) {
			$items['ID'] = $new_id;
			break;
		}
	}

	$new_id_file = "{$table_root}/{$new_id}.json";
	object_save($new_id_file, $data);
	refresh_data($db_name, $table_name, $new_id_file);

	$listview_item = make_listview_item($schema, $data);
	jsonp_nocache_exit(['status'=>'ok', 'listview'=>$listview_item]); 
}

function update_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'schema file error']);
	}

	$req_data = @$req['data'];
	if (empty($req_data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'req data error']);
	}

	$merged_data = merge_fields($req_data);
	$req_id = $merged_data['ID'];
	$data_file = "{$table_root}/{$req_id}.json";
	$ori_data = object_read($data_file);

	//将请求的数据，逐个压入现有数据中

	foreach($req_data as $req_group=>$req_items){
		foreach($req_items as $req_field=>$req_val){
			$handled = false;
			foreach($ori_data as $group=>&$items){
				foreach($items as $ori_field=>&$ori_val){
					if ($ori_field === $req_field) {
						$ori_val = $req_val;
						$handled = true;
						break 2;
					}
				}
			}
			if ($handled){continue;}

			$new_items = @$ori_data[$req_group];
			if (empty($new_items)) {
				$ori_data[$req_group] = [];
			}
			$ori_data[$req_group][$req_field] = $req_val;
		}
	}

	$new_data = $ori_data;

	object_save($data_file, $new_data);
	refresh_data($db_name, $table_name, $data_file);

	$listview_item = make_listview_item($schema, $new_data);
	jsonp_nocache_exit(['status'=>'ok', 'listview'=>$listview_item]); 
}

?>
