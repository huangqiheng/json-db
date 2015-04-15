<?php
require_once 'admin/functions.php';

$req = get_param();
$db_name = @$req['db'];
$db_name || $db_name = 'default';
$table_name = @$req['table'];
$opt = @$req['opt'];


//读取数据
if ($opt === 'read') {
	$file_name = @$req['file'];
	if (preg_match('#\.json$#i', $file_name)) {
		if (empty($table_name)) {
			$res_file = db_root($db_name)."/{$file_name}";
		} else {
			$res_file = table_root($db_name, $table_name)."/{$file_name}";
		}
		jsonp_nocache_exit(object_read($res_file));
	}
	jsonp_nocache_exit(array('status'=>'error', 'error'=>'input filename error'));
}


//写入数据
if ($opt === 'write') {
	$data = @$req['data'];
	$table_name || $table_name = 'default';

	//后面是写入数据
	if (empty($data)) {
		jsonp_nocache_exit(array('status'=>'error', 'error'=>'input data is empty'));
	}

	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	$mapper = object_read("{$table_root}/mapper.json");

	if (empty($schema)) {
		jsonp_nocache_exit(array('status'=>'error', 'error'=>'schema file error', 'data'=>$table_root));
	}

	if (!api_valid($db_name, $table_name, @$req['apikey'])) {
		jsonp_nocache_exit(array('status'=>'error', 'error'=>'api key error'));
	}

	$map_key = mapper_key(@$req['mapper']);
	if (empty($map_key)) {
		jsonp_nocache_exit(array('status'=>'error', 'error'=>'not mapper in parameter'));
	}

	$is_new_data = true;
	do {
		if (empty($mapper)) break;

		$map_val = @$mapper[$map_key];
		if(empty($map_val)) break;

		$map_file = "{$table_root}/{$map_val}.json";
		if (!file_exists($map_file)) break;

		$ori_data = object_read($map_file);
		if (empty($ori_data)) break;

		if (!set_data_id($data, $map_val)) {
			jsonp_nocache_exit(array('status'=>'error', 'error'=>'no id field'));
		}
		$is_new_data = false;
	} while(false);

	if ($is_new_data) {
		$output = create_new_data($db_name, $table_name, $data);
	} else {
		$output = update_current_data($db_name, $table_name, $data);
	}

	jsonp_nocache_exit($output); 
}
