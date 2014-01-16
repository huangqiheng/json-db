<?php
require_once 'functions.php';
denies_with_json();

$req = get_param();

switch($req['cmd']) {
    case 'update': update_data_exit($req);
    case 'create': create_data_exit($req);
    case 'delete': delete_data_exit($req);
    case 'refresh_data': refresh_data_exit($req);
    default: jsonp_nocache_exit(['status'=>'error', 'error'=>'unknow command.']);
}

function refresh_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$counter = refresh_data($db_name, $table_name);
	jsonp_nocache_exit(['status'=>'ok', 'counter'=>$counter]); 
}

function sync_remove_sameid($db_name, $table_name, $item_ids)
{

}

function sync_share_fields($db_name, $table_name, $item_id, $ori_data=null)
{
	global $id_same_types, $id_share_types;
	$item_id = intval($item_id);
	$affected_files = [];

	//找出sameid的访问路径
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	$fields = @$schema['fields'];
	$sameid_group = null;
	$sameid_key = null;
	$shared_keys = [];
	foreach($fields as $group=>$items) {
		foreach($items as $key=>$value) {
			if ($sameid_key === null) {
				if(in_array($value, $id_same_types)) {
					$sameid_group = $group;
					$sameid_key = $key;
				}
			}
			if(in_array($value, $id_share_types)) {
				$shared_keys[] = $key;
			}
		}
	}
	//如果没有发现关联id的键，则退出
	if (empty($sameid_key)) {
		return $affected_files;
	}

	//枚举出所有的sameid组成员
	$sameid_objs = [];
	$onebox_list = [];
	$checked_ids = [];

	$check_ids = [$item_id];
	$data_file = "{$table_root}/{$item_id}.json";
	$new_data = object_read($data_file);
	$onebox_list[$item_id] = onebox_object($schema, $data_file, $new_data);
	$rm_ids = remove_ids($ori_data, $new_data, $sameid_group, $sameid_key);

	//枚举
	while (!empty($check_ids)) {
		$id = array_pop($check_ids);
		$data_file = "{$table_root}/{$id}.json";
		$data = object_read($data_file);

		$sameid_objs[$id] = $data;
		$checked_ids[] = $id;

		$oneboxes = @$data[$sameid_group][$sameid_key];
		if (empty($oneboxes)) {continue;}

		foreach($oneboxes as $onebox){
			$new_id = intval(@$onebox['id']);
			if ($new_id === 0) {continue;}
			if (in_array($new_id, $checked_ids)) {continue;}

			$check_ids[] = $new_id;
			if (!in_array($new_id, $rm_ids)) {
				$onebox_list[$new_id] = $onebox;
			}
		}
	}

	//更新字段内容
	foreach($sameid_objs as $id=>$data) {
		$need_save = false;

		//生成新的onebox清单
		$new_oneboxs = [];
		foreach($onebox_list as $box_id=>$onebox) {
			if ($box_id !== $id) {
				$new_oneboxs[] = $onebox;
			}
		}
		//对比新旧onebox
		$old_oneboxs = @$data[$sameid_group][$sameid_key];
		if (empty($old_oneboxs)) {
			$old_oneboxs = [];
		}

		if (!is_same_oneboxs($old_oneboxs, $new_oneboxs)) {
			//更新sameid字段
			$data[$sameid_group][$sameid_key] = $new_oneboxs;
			$need_save = true;
		}

		//覆盖所有的share字段
		foreach($data as $group=>&$items) {
			foreach($items as $name=>&$value) {
				if (in_array($name, $shared_keys)) {
					$value = $new_data[$group][$name];
					$need_save = true;
				}
			}
		}

		//保存
		if ($need_save) {
			$data_file = "{$table_root}/{$id}.json";
			object_save($data_file, $data);
			$affected_files[] = $data_file;
		}
	}

	//更新要删除的onebox字段内容
	foreach($rm_ids as $id) {
		$data_file = "{$table_root}/{$id}.json";
		$data = object_read($data_file);
		$data[$sameid_group][$sameid_key] = [];
		object_save($data_file, $data);
		$affected_files[] = $data_file;
	}

	if (count($affected_files)) {
		refresh_data($db_name, $table_name, $affected_files);
	}

	return $affected_files;
}

function remove_ids($old_data, $new_data, $sameid_group, $sameid_key)
{
	if (empty($old_data)) {
		return [];
	}
	$old_boxs = @$old_data[$sameid_group][$sameid_key];
	$new_boxs = @$new_data[$sameid_group][$sameid_key];

	if (empty($old_boxs)) {
		return [];
	}

	if (empty($new_boxs)) {
		$new_boxs = [];
	}

	$old_ids = [];
	foreach($old_boxs as $items) {
		@$items['id'] || $old_ids[] = $items['id'];
	}
	$new_ids = [];
	foreach($new_boxs as $items) {
		@$items['id'] || $new_ids[] = $items['id'];
	}

	$left_data = array_values(array_diff($old_ids, $new_ids));
	return $left_data;
}

function is_same_oneboxs($oneboxs_a, $oneboxs_b)
{
	while($onebox_a = array_pop($oneboxs_a)) {
		$is_found = false;
		$id_a = @$onebox_a['id'];
		if (empty($id_a)) {
			continue;
		}
		foreach($oneboxs_b as $index=>$onebox) {
			$id_b = @$onebox['id'];
			if (empty($id_b)) {
				continue;
			}
			if($id_a === $id_b) {
				$is_found = true;
				unset($oneboxs_b[$index]);
				break;
			}
		}
		if ($is_found === false) {
			return false;
		}
	}
	return (count($oneboxs_b) === 0);
}

function delete_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$req_list = @$req['list'];
	sync_remove_sameid($db_name, $table_name, $req_list);
	$res = delete_current_data($db_name, $table_name, $req_list);
	jsonp_nocache_exit($res); 
}

function create_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$data = @$req['data'];
	$res = create_new_data($db_name, $table_name, $data);
	if ($res['status'] === 'ok') {
		$item_id = $res['ID'];
		$affected = sync_share_fields($db_name, $table_name, $item_id);
		if (count($affected)) {
			$res['reload'] = true;
		}
	}
	jsonp_nocache_exit($res); 
}

function update_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$req_data = @$req['data'];
	list($res, $ori_data) = update_current_data($db_name, $table_name, $req_data);
	if ($res['status'] === 'ok') {
		$item_id = $res['ID'];
		$affected = sync_share_fields($db_name, $table_name, $item_id, $ori_data);
		if (count($affected)) {
			$res['reload'] = true;
		}
	}
	jsonp_nocache_exit($res); 
}


?>
