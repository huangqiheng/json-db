<?php
require_once 'login.php';
require_once 'config.php';

/*****************************************
	共享函数
*****************************************/

function require_all ($path) 
{
	foreach (glob($path.'*.php') as $filename){
		require_once $filename;
	}
}

function is_data()
{
	$var_arr= func_get_args();
	$data = array_shift($var_arr);

	if (empty($data)) {
		return false;
	}

	$merged_data = merge_fields($data);

	if (array_key_exists('ID', $merged_data)) {
		return true;
	}


	if (array_key_exists('TIME', $merged_data)) {
		return true;
	}

	return false;
}

function items_exit()
{
	$var_arr= func_get_args();
	$data = array_shift($var_arr);

	if (empty($data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'items_exit no data']);
	}

	$merged_data = merge_fields($data);

	if (!array_key_exists('ID', $merged_data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'no id field']);
	}

/*
	if (!array_key_exists('CREATE', $merged_data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'no create time field']);
	}
*/

	if (!array_key_exists('TIME', $merged_data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'no time field']);
	}

	$res = [];
	foreach($var_arr as $var) {
		if (!array_key_exists($var, $merged_data)) {
			jsonp_nocache_exit(['status'=>'error', 'error'=>'no field: '.$var]);
		}
		$res[] = $merged_data[$var];
	}

	return $res;
}

function null_exit()
{
	$res = [];
	$var_arr= func_get_args();
	$req = @$var_arr[0];

	if (is_array($req)) {
		for($i=1; $i<count($var_arr); $i++) {
			$key = $var_arr[$i];
			$var = @$req[$key];
			if (empty($var)) {
				jsonp_nocache_exit(['status'=>'error', 'error'=>'input parameters invalid']);
			}
			$res[] = $var;
		}
	} else {
		foreach($var_arr as $var) {
			if (empty($var)) {
				jsonp_nocache_exit(['status'=>'error', 'error'=>'input parameters invalid']);
			}
			$res[] = $var;
		}
	}
	return $res;
}

function api_exit($db_name, $table_name, $apikey)
{
	if (!api_valid($db_name, $table_name, $apikey)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'api key error']);
	}
}

function api_valid($db_name, $table_name, $apikey)
{
	if (empty($db_name) || empty($table_name)) {
		return false;
	}

	$db_root = db_root($db_name);
	$table_root = $db_root."/{$table_name}";
	$db_schema = object_read("{$db_root}/schema.json");
	$table_schema = object_read("{$table_root}/schema.json");

	if (empty($db_schema) || empty($table_schema)) {
		return false;
	}

	$table_key = @$table_schema['caption']['key'];
	$db_key = @$db_schema['caption']['key'];

	$valid_keys = [];
	empty($table_key) || array_push($valid_keys, $table_key);
	empty($db_key) || array_push($valid_keys, $db_key);

	if (empty($valid_keys) || empty($apikey)) {
		return true;
	}

	return in_array($apikey, $valid_keys);
}

function get_onebox_keys($schema)
{
	global $mapper_types;
	$onebox = @$schema['onebox'];
	$key_title = @$onebox['title'];
	$key_title || $key_title = 'ID';
	$key_desc = @$onebox['desc'];

	if (empty($key_desc)) {
		foreach(merge_fields($schema['fields']) as $key=>$type) {
			if (in_array($type, $mapper_types)) {
				$key_desc = $key;
				break;
			}
		}
	}

	return [$key_title, $key_desc, @$onebox['image']];
}

function get_onebox_data($schema, $data)
{
	list($key_title, $key_desc, $key_image) = get_onebox_keys($schema);

	$res_obj = merge_fields($data);

	$ob_title = $res_obj[$key_title];
	$ob_desc = 'no description.';
	if (!empty($key_desc)) {
		$ob_desc = $res_obj[$key_desc];
	}

	if (is_array($ob_desc)) {
		$ob_desc = implode(', ', $ob_desc);
	}

	$ob_image = $schema['caption']['image'];
	if (!empty($key_image)) {
		$ob_image = $res_obj[$key_image];
	}

	return [$ob_title, $ob_desc, $ob_image];
}

function onebox_object($schema, $data_file, $data=null)
{
	$data || $data = object_read($data_file);

	$http_prefix = 'http://'.$_SERVER['HTTP_HOST'];
	list($ob_title, $ob_desc, $ob_image) = get_onebox_data($schema, $data);


	if (strlen($ob_image) > 1) {
		if ($ob_image[0] === '/') {
			$ob_image = $http_prefix.$ob_image;
		}
	}

	$res_obj = merge_fields($data);
	$ob_time = @$res_obj['TIME'];
	if ($ob_time) {
		$ob_time = format_time($ob_time);
	}

	$ob_ctime = @$res_obj['CREATE'];
	if ($ob_ctime) {
		$ob_ctime= format_time($ob_ctime);
	}

	$file_uri = substr($data_file, strlen($_SERVER['DOCUMENT_ROOT']));
	$file_uri = preg_replace('/\/'.$_SERVER['HTTP_HOST'].'/i', '', $file_uri);
	$file_uri = $http_prefix.$file_uri;

	$result = [];
	$result['title'] = $ob_title;
	$result['desc'] = strip_tags($ob_desc);
	$result['image'] = $ob_image;
	$result['url'] = $file_uri;
	$result['id'] = intval($res_obj['ID']);;
	$result['time'] = $ob_time;
	$result['ctime'] = $ob_ctime;
	return $result;
}

function delete_current_data($db_name, $table_name, $req_list)
{
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		return ['status'=>'error', 'error'=>'schema file error'];
	}

	if (empty($req_list)) {
		return ['status'=>'error', 'error'=>'input list error'];
	}

	$listview_file = "{$table_root}/listview.json";
	$listview_data = object_read($listview_file);
	$id_index = array_search('ID', $schema['listview']);

	$listview_new = [];
	$rep_list = [];
	foreach($listview_data as $subitem) {
		$id_cmp = intval($subitem[$id_index]);
		if (empty($id_cmp)) {
			continue;
		}

		if (in_array($id_cmp, $req_list)) {
			$rep_list[] = $id_cmp;
		} else {
			$listview_new[] = $subitem;
		}
	}

	foreach($req_list as $item_id) {
		$id_file = "{$table_root}/{$item_id}.json";
		if (file_exists($id_file)) {
			unlink($id_file);
		}
	}

	object_save($listview_file, $listview_new);
	return ['status'=>'ok', 'id_list'=>$rep_list]; 
}

function append_new_data($db_name, $table_name, $data)
{
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		return ['status'=>'error', 'error'=>'schema file error'];
	}

	if (empty($data)) {
		return ['status'=>'error', 'error'=>'req data error'];
	}

	$new_id = get_random_id($table_root);
	if (!set_data_id($data, $new_id)) {
		return ['status'=>'error', 'error'=>'no id field'];
	}

	$data = format_fields($schema, $data);

	$new_id_file = "{$table_root}/{$new_id}.json";
	object_save($new_id_file, $data);

	$listview_item = make_listview_item($table_root, $data);
	append_listview_json("{$table_root}/listview.json", $listview_item);

	return ['status'=>'ok', 'ID'=>$new_id, 'listview'=>$listview_item]; 
}

function append_listview_json($file_name, $item)
{
	if (file_exists($file_name)) {
		$item_str =','. json_encode($item) . ']';
	} else {
		touch($file_name);
		$item_str = '['.json_encode($item) . ']';
	}

	$mutex = sem_get(ftok($file_name, 'r'), 1);
	sem_acquire($mutex);

	$fh = fopen($file_name, 'r+');
	$read_len = 8;
	fseek($fh, -$read_len, SEEK_END);
	$read_str = fread($fh, $read_len);

	$seek_option = SEEK_END;
	$seek_posi = null;
	$cmp_str = preg_replace('/\s+/m', '', $read_str);

	if ($cmp_str === '[]') {
		$item_str = '['.json_encode($item) . ']';
		$seek_option = SEEK_SET;
		$seek_posi = 0;
	} else {
		for($i=strlen($read_str)-1; $i>=0; $i--) {
			if ($read_str[$i] === ']') {
				$seek_posi = $i - strlen($read_str);
				break;
			}
		}
	}

	if ($seek_posi === null) {
		fclose($fh);
		sem_release($mutex);
		return;
	}

	fseek($fh, $seek_posi, $seek_option);
	fwrite($fh, $item_str);
	fclose($fh);

	sem_release($mutex);
}


function format_fields($schema, $new_data, $ori_data=null)
{
	$fields = $schema['fields'];
	$formated_data = array_merge(array(), $new_data);
	$ori_data = merge_fields($ori_data);

	//以schema为模板
	foreach($fields as $group=>$items) {
		//填充和完善新数据
		$group_obj = @$formated_data[$group];
		if (empty($group_obj)) {
			$group_obj = [];
		}
		foreach($items as $field_name=>$field_type) {
			$field_value = @$group_obj[$field_name];
			$filed_exists = array_key_exists($field_name, $group_obj);

			//如果该字段没有被设置，
			if (!$filed_exists) {
				//如果是更新的，则需要保留原值
				if ($ori_data) {
					$field_value = @$ori_data[$field_name];
				//只有创建时，才强制设置默认值
				} else {
					$field_value = '';
					if (preg_match('/^jqxInput/i', $field_type)) {$field_value='';}
					if (preg_match('/^jqxInput-text-json/i', $field_type)) {$field_value=json_decode('{}');}
					if (preg_match('/^jqxComboBox/i', $field_type)) {$field_value='';}
					if (preg_match('/^jqxRadioButton/i', $field_type)) {$field_value='';}
					if (preg_match('/^jqxCheckBox/i', $field_type)) {$field_value=[];}
					if (preg_match('/^jqxListBox/i', $field_type)) {$field_value=[];}
					if (preg_match('/^jqxNumberInput/i', $field_type)) {$field_value='0';}
					if (preg_match('/^jqxDateTimeInput/i', $field_type)) {$field_value=gm_date(time());}

					if ($field_name === 'CREATE') {
						$field_value=gm_date(time());
					}
				}
			//如果字段有数据，则标准化一下
			} else {
				if ($field_name === 'ID') {
					$field_value = intval($field_value);
				}
				if (($field_name === 'CREATE') || ($field_name === 'TIME')) {
					$field_value = format_time($field_value);
				}
			}
			$group_obj[$field_name] = $field_value;
		}
		$formated_data[$group] = $group_obj;
	}
	return $formated_data;
}

function update_single_data($table_root, $data)
{
	if (empty($data)) {
		return [null, null, null];
	}

	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		return [null, null, null];
	}

	$req_id = get_data_id($data);
	if (empty($req_id)) {
		return [null, null, null];
	}

	$data_file = "{$table_root}/{$req_id}.json";
	$ori_data = object_read($data_file);
	$ori_data_output = array_merge(array(), $ori_data);

	//格式化输入结构，填充空白字段
	//有时候用户的输入并不完整
	$req_data = format_fields($schema, $data, $ori_data);

	//将请求的数据，逐个压入现有数据中
	//这样即使现有数据有"多余"字段，也不会被覆盖
	foreach($req_data as $req_group=>$req_items){
		foreach($req_items as $req_field=>$req_val){
			$handled = false;
			foreach($ori_data as $group=>&$items){
				if (!is_iterable($items)) continue;
				foreach($items as $ori_field=>&$ori_val){
					if ($ori_field === $req_field) {
						if ($ori_field === 'CREATE') {
							$ori_time = __strtotime($ori_val);
							$req_time = __strtotime($req_val);
							$ori_val = gm_date(min($ori_time, $req_time));
						} else {
							$ori_val = $req_val;
						}
						$handled = true;
						break 2;
					}
				}
			}
			if ($handled){continue;}

			//有时候现有的数据字段缺失,可以强制填入,一切以schema为准了
			$new_items = @$ori_data[$req_group];
			if (empty($new_items)) {
				$ori_data[$req_group] = [];
			}
			$ori_data[$req_group][$req_field] = $req_val;
		}
	}

	$new_data = $ori_data;

	return [$req_id, object_save($data_file, $new_data), $ori_data_output];
}

function is_iterable($var)
{
	return $var !== null 
		&& (is_array($var) 
				|| $var instanceof Traversable 
				|| $var instanceof Iterator 
				|| $var instanceof IteratorAggregate
		   );
}

function create_single_data($table_root, $data)
{
	if (empty($data)) {
		return [null, null];
	}

	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		return [null, null];
	}

	$new_id = get_random_id($table_root);
	if (!set_data_id($data, $new_id)) {
		return [null, null];
	}

	$new_data = format_fields($schema, $data);

	$new_id_file = "{$table_root}/{$new_id}.json";
	
	return [$new_id, object_save($new_id_file, $new_data)];
}

function update_current_data($db_name, $table_name, $req_data)
{
	$table_root = table_root($db_name, $table_name);
	list($req_id, $new_data, $ori_data) = update_single_data($table_root, $req_data);

	if (empty($new_data)) {
		return ['status'=>'error', 'error'=>'update single data error'];
	}

	$affected = sync_share_fields($table_root, $req_id, $ori_data);
	$affected[] = "{$table_root}/{$req_id}.json";
	refresh_listview($db_name, $table_name, $affected);

	$listview_item = make_listview_item($table_root, $new_data);
	return ['status'=>'ok', 'ID'=>$req_id, 'reload'=>(count($affected)>0), 'listview'=>$listview_item];
}

function create_new_data($db_name, $table_name, $req_data)
{
	$table_root = table_root($db_name, $table_name);
	list($req_id, $new_data) = create_single_data($table_root, $req_data);

	if (empty($new_data)) {
		return ['status'=>'error', 'error'=>'create single data error'];
	}

	$affected = sync_share_fields($table_root, $req_id);
	$affected[] = "{$table_root}/{$req_id}.json";
	refresh_listview($db_name, $table_name, $affected);

	$listview_item = make_listview_item($table_root, $new_data);
	return ['status'=>'ok', 'ID'=>$req_id, 'reload'=>(count($affected)>0), 'listview'=>$listview_item];
}

function join_new_data($db_name, $table_name, $data)
{
	$table_root = table_root($db_name, $table_name);

	//判断是单个数据，还是数组
	if (is_data($data)) {
		if (data_exists($db_name, $table_name, $data)) {
			return update_current_data($db_name, $table_name, $data);
		} else {
			return create_new_data($db_name, $table_name, $data);
		}
	} else {
		$schema = object_read("{$table_root}/schema.json");
		$mapper = object_read("{$table_root}/mapper.json");
		$refresh_files = [];
		$listviews = [];

		//批量处理各个数据
		foreach($data as $sub_data) {
			if ($req_id = __data_exists($table_root, $schema, $mapper, $sub_data)) {
				set_data_id($sub_data, $req_id);
				list($req_id, $new_data, $ori_data) = update_single_data($table_root, $sub_data);
				if ($new_data) {
					//共享字段的同步
					foreach(sync_share_fields($table_root, $req_id, $ori_data) as $file) {
						$refresh_files[] = $file;
					}
					//记录需要刷新listview的数据文件名
					$refresh_files[] = "{$table_root}/{$req_id}.json";
					$listviews[] = make_listview_item($table_root, $new_data);
				}
			} else {
				list($req_id, $new_data) = create_single_data($table_root, $sub_data);
				if ($new_data) {
					foreach(sync_share_fields($table_root, $req_id) as $file) {
						$refresh_files[] = $file;
					}
					$refresh_files[] = "{$table_root}/{$req_id}.json";
					$listviews[] = make_listview_item($table_root, $new_data);
				}
			}
		}

		//批量刷新listview
		refresh_listview($db_name, $table_name, $refresh_files);
		return ['status'=>'ok', 'listview'=>$listviews];
	}
}

function set_data_id(&$data, $new_id)
{
	foreach($data as $group=>&$items){
		if (array_key_exists('ID', $items)) {
			$items['ID'] = $new_id;
			return true;
		}
	}
	return false;
}

function get_data_id($data)
{
	$merged_data = merge_fields($data);
	return @$merged_data['ID'];
}

function sync_remove_sameid($db_name, $table_name, $item_ids)
{

}

function sync_share_fields($table_root, $item_id, $ori_data=null)
{
	global $id_same_types, $id_share_types;
	$item_id = intval($item_id);
	$affected_files = [];

	//找出sameid的访问路径
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

function refresh_listview($db_name, $table_name, $append_data_file=null)
{
	if (is_array($append_data_file)) {
		if (empty($append_data_file)) {
			return false;
		}
	}

	$data_path = table_root($db_name, $table_name);
	$schema = object_read("{$data_path}/schema.json");
	$listview = $schema['listview'];
	$fields = $schema['fields'];
	$merged_fields = merge_fields($fields);
	$mapper_fields = get_mapper_fields($merged_fields);
	$options_fields = get_options_fields($merged_fields);
	$id_index = array_search('ID', $listview);

	$listview_data = [];
	$mapper_data = [];
	$options_data = [];

	do {
		if (empty($listview)) {break;}
		$glob_files = [];

		//更新单个文件
		if ($append_data_file) {
			$listview_data = object_read("{$data_path}/listview.json");
			$mapper_data = object_read("{$data_path}/mapper.json");
			$options_data = object_read("{$data_path}/options.json");

			if (is_array($append_data_file)) {
				$append_data_file = array_unique($append_data_file);
				foreach($append_data_file as $data_file) {
					$merge_items = merge_fields(object_read($data_file));
					$id_input = @$merge_items['ID'];
					if (empty($id_input)) {
						continue;
					}
					$id_input = intval($id_input);

					
					//找到就是替换并直接退出，找不到就是追加，并抛给下一个流程
					foreach($listview_data as &$subitem) {
						$id_list = intval($subitem[$id_index]);
						if ($id_list !== $id_input) {continue;}
						//替换listview，完成任务退出
						$subitem = new_listview_item($listview, $merge_items, $merged_fields);
						update_mappers($mapper_data, $mapper_fields, $merge_items);
						update_options($options_data, $options_fields, $merge_items);
						continue 2;
					}
					//需要后续处理的
					$glob_files[] = $data_file;
				}
			} else {
				$merge_items = merge_fields(object_read($append_data_file));
				$id_input = $merge_items['ID'];
				//找到就是替换并直接退出，找不到就是追加，并抛给下一个流程
				foreach($listview_data as &$subitem) {
					if ($subitem[$id_index] !== $id_input) {continue;}
					//替换listview，完成任务退出
					$subitem = new_listview_item($listview, $merge_items, $merged_fields);
					update_mappers($mapper_data, $mapper_fields, $merge_items);
					update_options($options_data, $options_fields, $merge_items);
					break 2;
				}
				//需要后续处理的
				$glob_files[] = $append_data_file;
			}
		} else {
			set_time_limit(0);
			$glob_files = glob("{$data_path}/*.json");
		}

		$listview_maker = function($file)use(&$listview, &$merged_fields, &$listview_data,
				&$mapper_data, &$mapper_fields, &$options_data, &$options_fields) {
			$data_obj = object_read($file);
			if (empty($data_obj)) {return;}

			$merge_items = merge_fields($data_obj);

			//生成listview
			$new_listview = new_listview_item($listview, $merge_items, $merged_fields);
			array_unshift($listview_data, $new_listview);
			//生成mapper
			update_mappers($mapper_data, $mapper_fields, $merge_items);
			//更新options
			update_options($options_data, $options_fields, $merge_items);
			unset($data_obj);
			unset($merge_items);
			unset($new_listview);
		};

		ini_set('memory_limit', '1024M');

		foreach($glob_files as $file) {
			if (is_dir($file)) {continue;}
			if (!preg_match('~/(\d+)\.json$~',$file, $matches)){continue;}
			$item_id = $matches[1];
			call_user_func($listview_maker, $file);
		}

	}while(false);

	object_save("{$data_path}/mapper.json", $mapper_data);
	object_save("{$data_path}/listview.json", $listview_data);
	object_save("{$data_path}/options.json", $options_data);
	return count($listview_data);
}

function get_unmapper_list($db_name, $table_name)
{
	$data_path = table_root($db_name, $table_name);
	$mapper_data = object_read("{$data_path}/mapper.json");
	$valid_ids = array_unique(array_values($mapper_data));

	$total_ids = [];
	foreach(glob("{$data_path}/*.json") as $file) {
		if (is_dir($file)) {continue;}
		if (!preg_match('~/(\d+)\.json$~',$file, $matches)){continue;}
		$item_id = $matches[1];
		$total_ids[] = intval($item_id);
	}

	return array_values(array_diff($total_ids, $valid_ids));
}

function clean_unmapper_data($db_name, $table_name)
{
	$rm_ids = get_unmapper_list($db_name, $table_name);
	return delete_current_data($db_name, $table_name, $rm_ids);
}

function update_options(&$options_data, &$options_fields, &$merged_item)
{
	foreach($options_fields as $opt_name) {
		$opt_val = @$merged_item[$opt_name];
		if (empty($opt_val)) {
			continue;
		}

		$ori_arr = @$options_data[$opt_name];
		if (empty($ori_arr)) {
			$ori_arr = [];
		}

		if (is_array($opt_val)) {
			$opt_vals = $opt_val;
			foreach($opt_vals as $opt_val) {
				if (!in_array($opt_val, $ori_arr)) {
					$ori_arr[] = $opt_val;
					$options_data[$opt_name] = $ori_arr;
				}
			}

		} else {
			if (!in_array($opt_val, $ori_arr)) {
				$ori_arr[] = $opt_val;
				$options_data[$opt_name] = $ori_arr;
			}
		}
	}
}


function make_listview_item($table_root, $data_obj)
{
	$schema = object_read("{$table_root}/schema.json");

	$fields = $schema['fields'];
	$merged_fields = merge_fields($fields);

	$listview = $schema['listview'];
	$merge_items= merge_fields($data_obj);
	return new_listview_item($listview, $merge_items, $merged_fields);
}

function new_listview_item($listview, $merge_items, $merged_fields)
{
	$output = [];
	foreach($listview as $view_item) {
		$value = @$merge_items[$view_item];
		if ($value) {
			$field_name = @$merged_fields[$view_item];
			if ($field_name) {
				if ($field_name === 'ID'){
					$value = intval($value);
				}

				if (($field_name==='jqxDateTimeInput') || ($field_name==='jqxInput-time')){
					try {
						$fixed_date = preg_replace('/\(.+\)$/', '', $value);
						$date = new DateTime($fixed_date);
						$value = $date->format('Y-m-d');
					} catch (Exception $e) {
					}
				}

				if (preg_match('/^jqxListBox-onebox/i', $field_name)) {
					$value_out = [];
					foreach($value as $onebox_item) {
						$value_out[] = $onebox_item['title'];
					}
					$value = implode(',',$value_out);
				}

				if (preg_match('/^jqxCheckBox/i', $field_name)) {
					$value = implode(',',$value);
				}
			}

			$output[] = $value;
		} else {
			$output[] = '';
		}
	}
	return $output;
}


function __data_exists($table_root, $schema, $mapper, $data)
{
	$merged_fields = merge_fields($schema['fields']);
	$mapper_fields = get_mapper_fields($merged_fields);
	$merged_data = merge_fields($data);

	$check_id_valid = function($item_val) use($mapper, $table_root) {
		$map_key= mapper_key($item_val);
		if ($map_val = @$mapper[md5($map_key)]) {
			$map_file = "{$table_root}/{$map_val}.json";
			if (file_exists($map_file)) {
				return $map_val;
			}
		}
		return false;
	};

	foreach($mapper_fields as $field) {
		$value = @$merged_data[$field];

		if (!is_array($value)) {
			$value = [$value];
		}

		foreach($value as $sub_val) {
			if ($item_id = call_user_func($check_id_valid, $sub_val)) {
				return $item_id;
			}
		}
	}
	return false;
}

function data_exists($table_root, $data)
{
	$schema = object_read("{$table_root}/schema.json");
	$mapper = object_read("{$table_root}/mapper.json");
	return __data_exists($table_root, $schema, $mapper, $data);
}

function update_mappers(&$mapper_data, &$mapper_fields, &$merged_item)
{
	foreach($mapper_fields as $map_name) {
		$map_key = @$merged_item[$map_name];
		$item_id = @$merged_item['ID'];
		if (empty($map_key)) {continue;}

		if (is_string($map_key)) {
			$map_key= mapper_key($map_key);
			$mapper_data[$map_key] = $item_id;
			$mapper_data[md5($map_key)] = $item_id;
			continue;
		}

		if (is_array($map_key)) {
			foreach($map_key as $key) {
				$key = mapper_key($key);
				$mapper_data[$key] = $item_id;
				$mapper_data[md5($key)] = $item_id;
			}
		}
	}
}

function mapper_value_exit($db_name, $table_name, $map_key)
{
	$result = get_mapper_value($db_name, $table_name, $map_key);
	if ($result === false) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'no valid mapped id']);
	}
	return $result;
}

function get_mapper_value($db_name, $table_name, $map_key)
{
	$table_root = table_root($db_name, $table_name);
	$mapper = object_read("{$table_root}/mapper.json");
	if (empty($mapper)) {
		return false;
	}

	$map_key= mapper_key($map_key);
	$map_val = @$mapper[$map_key];

	if (empty($map_val)) {
		return false;
	}

	$data_file = "{$table_root}/{$map_val}.json";
	if (!file_exists($data_file)) {
		return false;
	}

	$data_uri = substr($data_file, strlen($_SERVER['DOCUMENT_ROOT']));
	$data_uri = preg_replace('/\/'.$_SERVER['HTTP_HOST'].'/i', '', $data_uri);
	$data_url = get_current_url(false).$data_uri;

	return [$map_val, $data_file, $data_url];
}

function get_current_url($full = true)
{
	$s = $_SERVER;
	$ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
	$sp = strtolower($s['SERVER_PROTOCOL']);
	$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
	$port = $s['SERVER_PORT'];
	$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
	$host = isset($s['HTTP_X_FORWARDED_HOST']) ? $s['HTTP_X_FORWARDED_HOST'] : isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : $s['SERVER_NAME'];
	return $protocol . '://' . $host . $port .(($full)? $s['REQUEST_URI'] : '');
}

function mapper_key($input)
{
	if (empty($input)) {return '';}
	return mb_strtolower($input, 'UTF-8');
}


function get_options_fields($merge_fields)
{
	$mappers = [];
	global $options_types;
	foreach($merge_fields as $field=>$value) {
		if (in_array($value, $options_types)) {
			$mappers[] = $field;
		}
	}
	return $mappers;
}

function get_mapper_fields($merge_fields)
{
	$mappers = [];
	global $mapper_types;
	foreach($merge_fields as $field=>$value) {
		if (in_array($value, $mapper_types)) {
			$mappers[] = $field;
		}
	}
	return $mappers;
}

function merge_fields($group_obj)
{
	$merge_items = [];
	if (!empty($group_obj)) {
		foreach($group_obj as $group=>$items) {
			if (!is_iterable($items)) continue;
			foreach($items as $name=>$value) {
				$merge_items[$name] = $value;
			}
		}
	}
	return $merge_items;
}

function rmdir_Rf($directory)
{
	foreach(glob("{$directory}/*") as $file)
	{
		if(is_dir($file)) { 
			rmdir_Rf($file);
		} else {
			unlink($file);
		}
	}
	rmdir($directory);
}

function table_root($db_name, $table_name)
{
	return db_root($db_name)."/{$table_name}";
}

function db_root($db_name)
{
	return dbs_path()."/{$db_name}";
}

function init_db_root($root_path)
{
	mkdir($root_path.'/'.BACKUP_DIR);
	mkdir($root_path.'/'.WWWROOT_DIR);
	$index_file = $root_path.'/'.WWWROOT_DIR.'/index.php';
	$index = '<h>welcome to json-db homepage.</h>';
	object_save($index_file, $index);
}

function dbs_path()
{
	$root_path = $_SERVER['DOCUMENT_ROOT'].'/databases/'.$_SERVER['HTTP_HOST'];
	if (!file_exists($root_path)) {
		mkdir($root_path);
		init_db_root($root_path);
	}
	return $root_path;
}

function put_object($db_name, $table_name, $base_name, $data)
{
	$table_root = table_root($db_name, $table_name);
	return object_save("{$table_root}/{$base_name}.json", $data);
}

function get_object($db_name, $table_name, $base_name)
{
	$table_root = table_root($db_name, $table_name);
	return object_read("{$table_root}/{$base_name}.json");
}

function object_save($filename, $data)
{
	file_put_contents($filename, prety_json($data));
	return $data;
}

function object_read($filename)
{
	if (preg_match('#^https?://#i', $filename)) {
		return object_read_url($filename);
	}
	
	if (!file_exists($filename)) {
		return [];
	}

	$data_str = file_get_contents($filename);
	if ($data_str === null) {
		return [];
	}
	return json_decode($data_str, true);
}

function clean_html($json)
{
	$json = preg_replace( '/[[:cntrl:]]+/', ' ',$json);
	$json = preg_replace( '/[\s]+/', ' ',$json);
	return $json;
}

function clean_space($json)
{
	$json = preg_replace( '/[[:cntrl:]]+/', '',$json);
	$json = preg_replace( '/[\s]+/', '',$json);
	return $json;
}

function object_read_url($req_url, $conn_timeout=7, $timeout=5)
{
	$res = curl_get_content($req_url,null,$conn_timeout,$timeout);

	if (empty($res)) {
		return [];
	}

	$res = clean_html($res);

	preg_match("#{[\s]*\".*[\s]*}#ui", $res, $mm);

	$res_body = @$mm[0];

	if (empty($res_body)) {
		return [];
	}

	return json_decode($res_body, true);
}

function get_remote_jsonex($url,$conn_timeout=7, $timeout=5)
{
	if ($full_obj = get_remote_json($url.'?json=1', $conn_timeout, $timeout)) {
		$post = &$full_obj['post'];
		$content = $post['content'];
		if (($post_count = $post['page_count']) > 1) {
			for($iter_count = 2; $iter_count<=$post_count; $iter_count++) {
				$new_url = $url.'/'.$iter_count;
				if ($json_obj = get_remote_json($new_url.'?json=1')) {
					$content .= $json_obj['post']['content'];
				}
			}
		}
		$post['content'] = $content;
	}
	return $full_obj;
}


function get_remote_json($req_url,$conn_timeout=7, $timeout=5)
{
	$res_obj = object_read_url($req_url,$conn_timeout,$timeout);

	if (@$res_obj['status'] !== 'ok') {
		return false;
	}

	return $res_obj;
}



function prety_json($obj)
{
	return indent_json(json_encode($obj));
}

function indent_json($json) 
{
	$result      = '';
	$pos         = 0;
	$strLen      = strlen($json);
	$indentStr   = '  ';
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;

	for ($i=0; $i<=$strLen; $i++) {

		// Grab the next character in the string.
		$char = substr($json, $i, 1);

		// Are we inside a quoted string?
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;

			// If this character is the end of an element,
			// output a new line and indent the next line.
		} else if(($char == '}' || $char == ']') && $outOfQuotes) {
			$result .= $newLine;
			$pos --;
			for ($j=0; $j<$pos; $j++) {
				$result .= $indentStr;
			}
		}

		// Add the character to the result string.
		$result .= $char;

		// If the last character was the beginning of an element,
		// output a new line and indent the next line.
		if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
			$result .= $newLine;
			if ($char == '{' || $char == '[') {
				$pos ++;
			}

			for ($j = 0; $j < $pos; $j++) {
				$result .= $indentStr;
			}
		}

		$prevChar = $char;
	}

	return $result;
}

function get_db_captions()
{
	$result = [];
	$dirs = glob(dbs_path().'/*', GLOB_ONLYDIR);
	usort($dirs, function($a,$b){return filemtime($b) - filemtime($a);});

	foreach ($dirs as $db_path) { 
		$db_name = basename($db_path);
		$filename = $db_path.'/schema.json';
		if (!file_exists($filename)) {
			continue;
		}

		$schema_str = file_get_contents($filename);
		if ($schema_str) {
			$schema = json_decode($schema_str, true);
			$caption = $schema['caption'];
			$caption['name'] = $db_name;
			$result[] = $caption;
		}
	}
	return $result;
}

function get_table_captions($db_name=null)
{
	$result = [];

	if ($db_name === null) {
		$db_captions = get_db_captions();
		foreach ($db_captions as $caption) {
			$result[$caption['name']] = get_table_captions($caption['name']);
		}
		return $result;
	} else {
		$dirs = glob(dbs_path()."/{$db_name}/*", GLOB_ONLYDIR);
		usort($dirs, function($a,$b){return filemtime($b) - filemtime($a);});

		foreach ($dirs as $table_path) { 
			$table_name = basename($table_path);
			$filename = $table_path.'/schema.json';
			$schema_str = file_get_contents($filename);
			if ($schema_str) {
				$schema = json_decode($schema_str, true);
				$caption = $schema['caption'];
				$caption['name'] = $table_name;
				$result[] = $caption;
			}
		}
		return $result;

	}
}

function pp($obj)
{
	echo "<pre>";
	print_r($obj);
	echo "</pre>";
}

$g_union = null;

function get_param($name=null, $default='default')
{
	global $g_union;
	if ($g_union === null) {
		$g_union = array_merge($_GET, $_POST); 

		if (empty($g_union)) {
			if(stristr(@$_SERVER['HTTP_USER_AGENT'], ' MSIE')){
				$msie_post = file_get_contents("php://input");
				parse_str($msie_post, $MY_POST);
				$g_union = $MY_POST;
			}
		}
	}

	if ($name === null) {
		return $g_union;
	}

	$value = @$g_union[$name];
	empty($value) && ($value=$default);

	return $value;
}

function get_basetime()
{
	return mktime(0,0,0,7,21,2012);
}

function get_random_id($table_root)
{
	$maxid_file = "{$table_root}/maxid.json";
	$schema_file = "{$table_root}/schema.json";

        $mutex = sem_get(ftok($schema_file, 'r'), 1);
	sem_acquire($mutex);

	($max_id = @file_get_contents($maxid_file)) || ($max_id = 0);
	$ran_val = intval((microtime(true)-get_basetime()) * 1000);
	$res_val = ($ran_val > $max_id)? $ran_val : ++$max_id;
	file_put_contents($maxid_file, $res_val);

	sem_release($mutex);
	return $res_val;
}

function get_selected_db()
{
	return [get_param('db', 'default'), get_param('table', 'default')];
}

function json_file($file_name)
{
	do {
		list($database,$table) = get_selected_db();
		$full_name = dbs_path()."/{$database}/{$table}/{$file_name}";
		if (!file_exists($full_name)) {
			break;
		}

		$json_res = file_get_contents($full_name);
		if (empty($json_res)) {
			break;
		}
		return json_decode($json_res, true);
	} while (false);

	return [];
}

/*********************************************************
	jsonp
*********************************************************/

function html_nocache_exit($output)
{
	set_nocache();
	header('Access-Control-Allow-Origin: *');  
	header('Content-Type: text/html; charset=utf-8');
	echo $output;
	exit();
}

function html_cache_exit($output, $age_val=300)
{
	set_cache_age($age_val);
	header('Access-Control-Allow-Origin: *');  
	header('Content-Type: text/html; charset=utf-8');
	echo $output;
	exit();
}

function jsonp_nocache_exit($output)
{
	set_nocache();
	echo jsonp($output);
	exit();
}

function jsonp_cache_exit($output, $age_val=300)
{
	set_cache_age($age_val);
	echo jsonp($output);
	exit();
}

function set_nocache()
{
	header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
	header("Pragma: no-cache"); //HTTP 1.0
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
}

function set_cache_age($age_val = 300)
{
	header('Cache-Control: public, must-revalidate, proxy-revalidate, max-age='.$age_val);
	header('Pragma: public');
	header('Last-Modified: '.gm_date(last_mtime()));
	header('Expires: '.gm_date(time()+$age_val));
}

function gm_date($time)
{
        return gmdate('D, d M Y H:i:s \G\M\T', $time);
}

function __strtotime($time_str)
{
	$time_str = preg_replace('/中国标准时间/', 'CST', $time_str);
	return strtotime($time_str);
}

function format_time($time_str)
{
	return gm_date(__strtotime($time_str));
}

function jsonp($data)
{
	header('Access-Control-Allow-Origin: *');  
	header('Content-Type: application/json; charset=utf-8');
	$json = json_encode($data);

	if(!isset($_GET['callback']))
		return $json;

	if(is_valid_jsonp_callback($_GET['callback']))
		return "{$_GET['callback']}($json)";

	return false;
}

function is_valid_jsonp_callback($subject)
{
	$identifier_syntax = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
	$reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
			'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 
			'for', 'switch', 'while', 'debugger', 'function', 'this', 'with', 
			'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 
			'extends', 'super', 'const', 'export', 'import', 'implements', 'let', 
			'private', 'public', 'yield', 'interface', 'package', 'protected', 
			'static', 'null', 'true', 'false');
	return preg_match($identifier_syntax, $subject)
		&& ! in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
}

/****/
/***************  curl ********************/
/****/

function curl_get_content($url, $user_agent=null, $conn_timeout=7, $timeout=5)
{
	$headers = array(
		"Accept: application/json",
		"Accept-Encoding: deflate,sdch",
		"Accept-Charset: utf-8;q=1"
		);

	if ($user_agent === null) {
		$user_agent = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36';
	}
	$headers[] = $user_agent;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $conn_timeout);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

	$res = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$err = curl_errno($ch);
	curl_close($ch);

	if (($err) || ($httpcode !== 200)) {
		return null;
	}

	return $res;
}

function curl_post_content($url, $data, $user_agent=null, $conn_timeout=7, $timeout=5)
{
	$headers = array(
		'Accept: application/json',
		'Accept-Encoding: deflate',
		'Accept-Charset: utf-8;q=1'
		);

	if ($user_agent === null) {
		$user_agent = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36';
	}
	$headers[] = $user_agent;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $conn_timeout);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

	if ($data) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	}

	$res = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$err = curl_errno($ch);
	curl_close($ch);

	if (($err) || ($httpcode !== 200)) {
		return null;
	}

	return $res;
}

function async_call($script_path=null, $data=null)
{
	defined('ASYNC_USERAGENT') or define('ASYNC_USERAGENT', 'async_call_client');

	if ($script_path === null) {
		return ($_SERVER['HTTP_USER_AGENT'] === ASYNC_USERAGENT);
	}

	$headers = array(
		'Host: '.$_SERVER['SERVER_NAME'],
		'User-Agent: '.ASYNC_USERAGENT,
	);
	
	$url = 'http://127.0.0.1:'.$_SERVER['SERVER_PORT'].$script_path;

	$curl_opt = array(
		CURLOPT_URL => $url,
		CURLOPT_HTTPHEADER => $headers,
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
	return true;
}

function is_dir_empty($dir) 
{
	if (!is_readable($dir)) return NULL; 
	$handle = opendir($dir);
	while (false !== ($entry = readdir($handle))) {
		if ($entry != "." && $entry != "..") {
			return FALSE;
		}
	}
	return TRUE;
}

function is_direct_called($full_name)
{
        return ($_SERVER['SCRIPT_FILENAME'] === $full_name);
}

/*--------------------------------
	lock
*------------------------------*/

function lock_do($callback, $tok_file=null, $ident='g')
{
        $res = false;

        if (empty($tok_file)) {
                $tok_file = __FILE__;
        }

        if (!file_exists($tok_file)) {
                $tok_file = __FILE__;
        }

        $key = ftok($tok_file, $ident);
        if ($key === -1) {
                return call_user_func($callback);
        }

        $mutex = sem_get($key, 1);

        if ($mutex === false) {
                return call_user_func($callback);
        }

        if (sem_acquire($mutex) === false) {
                return call_user_func($callback);
        }

        try {
                $res = call_user_func($callback);
        } catch (Exception $e) {

        //} finally {
        }
	sem_release($mutex);

        return $res;
}


/*------------------------------------
 -		enqueue 	    --
-------------------------------------*/

defined('QUEUE_TIME_INTERVAL') or (define('QUEUE_TIME_INTERVAL', 60));
defined('QUEUE_ITEMS_SUFFIX') or (define('QUEUE_ITEMS_SUFFIX', 'bucket'));
defined('QUEUE_MAX_DEQUEUE') or (define('QUEUE_MAX_DEQUEUE', 1000));

function queue_id()
{
	return intval(time() / QUEUE_TIME_INTERVAL);
}

function queue_file($cache_dir, $name, $id=null)
{
	$dir = $cache_dir.'/'.md5($name);
	if ($id === null) {
		return $dir;
	}
	return $dir.'/'.$id.'.'.QUEUE_ITEMS_SUFFIX;
}

function queue_info($cache_dir, $name)
{
	$file_to_write = queue_file($cache_dir, $name, queue_id());
	if (file_exists($file_to_write)) {
		$time_to_wait = QUEUE_TIME_INTERVAL - intval(time()) % QUEUE_TIME_INTERVAL;
	} else {
		$time_to_wait = 0;
	}

	$buckets = glob(queue_file($cache_dir, $name, '*'));

	return [
		'file_to_enqueue' => $file_to_write,
		'is_queueing' => file_exists($file_to_write),
		'time_to_wait' => $time_to_wait,
		'is_empty' => is_dir_empty(queue_file($cache_dir, $name)) !== false,
		'bucket_interval' => QUEUE_TIME_INTERVAL,
		'max_dequeue' => QUEUE_MAX_DEQUEUE,
		'bucket_count' => count($buckets),
		'buckets' => $buckets
	];
}

function queue_empty($cache_dir, $name)
{
	$buckets = glob(queue_file($cache_dir, $name, '*'));
	$bucket_count = count($buckets);

	$file_to_write = queue_file($cache_dir, $name, queue_id());
	if (!file_exists($file_to_write)) {
		return ($bucket_count===0)? true : false;
	}

	return ($bucket_count>1)? false : true;
}

function queue_in($cache_dir, $name, $items)
{
	$file_to_write = queue_file($cache_dir, $name, queue_id());

	if (!file_exists($file_to_write)) {
		if (!file_exists($cache_dir)) {
			return false;
		}

		$dir = queue_file($cache_dir, $name);
		if (!file_exists($dir)) {
			mkdir($dir);
		}
		touch($file_to_write);
	}

	$data_to_write = '';

	if (is_array($items)) {
		foreach ($items as $item) {
			$item_text = json_encode($item);
			$data_to_write .= $item_text.PHP_EOL;
		}
	} else {
		$data_to_write = json_encode($items).PHP_EOL;
	}
	return file_put_contents($file_to_write, $data_to_write, FILE_APPEND | LOCK_EX);

}

function queue_out($cache_dir, $name, $max=QUEUE_MAX_DEQUEUE)
{
	if (empty($max)) {
		$max = QUEUE_MAX_DEQUEUE;
	}

	$now_writing_id = queue_id();

	$item_ids = [];
	foreach(glob(queue_file($cache_dir, $name, '*')) as $file) {
		if (is_dir($file)) {continue;}
		if (!preg_match('~/(\d+)\.'.QUEUE_ITEMS_SUFFIX.'$~',$file, $matches)){continue;}
		$item_id = intval($matches[1]);
		if ($item_id < $now_writing_id) {
			$item_ids[] = $item_id;
		}
	}

	if (count($item_ids)) {
		sort($item_ids, SORT_NUMERIC);
	}

	$output_items = [];
	foreach ($item_ids as $id) {
		$file_name = queue_file($cache_dir, $name, $id);
		$data_str = file_get_contents($file_name);
		$data_arr = explode(PHP_EOL, $data_str);
		while($item_str = array_shift($data_arr)) {
			if ($item_str === '') {
				continue;
			}

			$item = json_decode($item_str, true);
			if (empty($item)) {
				continue;
			}

			$output_items[] = $item;
			if (count($output_items) >= $max) {
				if (!empty($data_arr)) {
					$resave_str = implode(PHP_EOL, $data_arr);
					file_put_contents($file_name, $resave_str);
					break 2;
				}
			}
		}
		unlink($file_name);
	}

	$que_dir = queue_file($cache_dir, $name);
	if (is_dir_empty($que_dir)) {
		rmdir($que_dir);
	}

	return $output_items;
}



?>
