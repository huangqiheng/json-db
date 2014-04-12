<?php
require_once '../../admin/functions.php';

function call_async($script_path, $data=null, $ua='ME_USERAGENT')
{
	$curl_opt = array(
		CURLOPT_URL => 'http://127.0.0.1:'.$_SERVER['SERVER_PORT'].$script_path,
		CURLOPT_HTTPHEADER => array(
			'Host: '.$_SERVER['SERVER_NAME'],
			'User-Agent: '.$ua,
		),
		CURLOPT_PORT => $_SERVER['SERVER_PORT'], 
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_NOSIGNAL => 1,
		CURLOPT_CONNECTTIMEOUT_MS => 3000,
		CURLOPT_TIMEOUT_MS =>  1,
	);

	if ($data) {
		$curl_opt[CURLOPT_POST] = 1;
		$curl_opt[CURLOPT_POSTFIELDS] = http_build_query($data);
	}

	$ch = curl_init();
	curl_setopt_array($ch, $curl_opt);
	curl_exec($ch);
	curl_close($ch);
}


function getdata_exit($db_name, $table_name, $name)
{
	$map_key = $name;
	$table_root = table_root($db_name, $table_name);
	$mapper = object_read("{$table_root}/mapper.json");
	if (empty($mapper)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'mapper file not found']);
	}

	$map_key= mapper_key($map_key);
	$map_val = @$mapper[$map_key];
	$map_file = "{$table_root}/{$map_val}.json";
	if (!file_exists($map_file)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'data file not found']);
	}

	$data = object_read($map_file);
	if (empty($data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'data file is empty']);
	}

	items_exit($data);

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
