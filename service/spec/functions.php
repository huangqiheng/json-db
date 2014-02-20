<?php
require_once '../../admin/functions.php';

function getdata_exit($db_name, $table_name, $name)
{
	$map_key = $name;
	$table_root = table_root($db_name, $table_name);
	$mapper = object_read("{$table_root}/mapper.json");
	if (empty($mapper)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'mapper file not found']);
	}

	$map_key= mapper_key($map_key);
	$map_val = $mapper[$map_key];
	$map_file = "{$table_root}/{$map_val}.json";
	if (!file_exists($map_file)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'data file not found']);
	}

	$data = object_read($map_file);
	if (empty($data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'data file is empty']);
	}

	return $data;
}

function get_listview_column($db_name, $table_name, $field_name)
{
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	$listview_data = object_read("{$table_root}/listview.json");
	$id_index = array_search($field_name, $schema['listview']);

	$rep_list = [];
	foreach($listview_data as $subitem) {
		$id_cmp = $subitem[$id_index];
		if (empty($id_cmp)) {
			continue;
		}
		if (is_array($id_cmp)) {
			foreach($id_cmp as $item) {
				if (!in_array($item, $rep_list)) {
					$rep_list[] = $item;
				}
			}
		} else {
			if (!in_array($id_cmp, $rep_list)) {
				$rep_list[] = $id_cmp;
			}
		}
	}
	return $rep_list;
}
