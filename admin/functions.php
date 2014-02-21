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

	foreach($var_arr as $var) {
		if (!array_key_exists($var, $merged_data)) {
			jsonp_nocache_exit(['status'=>'error', 'error'=>'no field: '.$var]);
		}
	}
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

	if ($ob_image[0] === '/') {
		$ob_image = $http_prefix.$ob_image;
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
	return ['status'=>'ok', 'id_list'=>$rep_list]; 
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

	$new_id = get_random_id($db_name, $table_name);
	if (!set_data_id($data, $new_id)) {
		return ['status'=>'error', 'error'=>'no id field'];
	}

	$data = format_fields($schema, $data);

	$new_id_file = "{$table_root}/{$new_id}.json";
	object_save($new_id_file, $data);

	$listview_item = make_listview_item($schema, $data);
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

function create_new_data($db_name, $table_name, $data)
{
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		return ['status'=>'error', 'error'=>'schema file error'];
	}

	if (empty($data)) {
		return ['status'=>'error', 'error'=>'req data error'];
	}

	$new_id = get_random_id($db_name, $table_name);
	if (!set_data_id($data, $new_id)) {
		return ['status'=>'error', 'error'=>'no id field'];
	}

	$data = format_fields($schema, $data);

	$new_id_file = "{$table_root}/{$new_id}.json";
	object_save($new_id_file, $data);
	refresh_data($db_name, $table_name, $new_id_file);

	$listview_item = make_listview_item($schema, $data);
	return ['status'=>'ok', 'ID'=>$new_id, 'listview'=>$listview_item]; 
}

function format_fields($schema, $req_data)
{
	$fields = $schema['fields'];
	$fix_data = array_merge(array(), $req_data);

	foreach($fields as $group=>$items) {
		$group_obj = @$fix_data[$group];
		if (empty($group_obj)) {
			$group_obj = [];
		}
		foreach($items as $field_name=>$field_type) {
			$field_value = @$group_obj[$field_name];
			if (empty($field_value)) {
				$field_value = '';
				if (preg_match('/^jqxInput/i', $field_type)) {$field_value='';}
				if (preg_match('/^jqxInput-text-json/i', $field_type)) {$field_value=json_decode('{}');}
				if (preg_match('/^jqxComboBox/i', $field_type)) {$field_value='';}
				if (preg_match('/^jqxRadioButton/i', $field_type)) {$field_value='';}
				if (preg_match('/^jqxCheckBox/i', $field_type)) {$field_value=[];}
				if (preg_match('/^jqxListBox/i', $field_type)) {$field_value=[];}
				if (preg_match('/^jqxNumberInput/i', $field_type)) {$field_value='0';}
				if (preg_match('/^jqxDateTimeInput/i', $field_type)) {$field_value=gm_date(time());}
			}
			$group_obj[$field_name] = $field_value;
		}
		$fix_data[$group] = $group_obj;
	}
	return $fix_data;
}

function update_current_data($db_name, $table_name, $req_data)
{
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		return [['status'=>'error', 'error'=>'schema file error'], []];
	}

	if (empty($req_data)) {
		return [['status'=>'error', 'error'=>'req data error'],[]];
	}

	$merged_data = merge_fields($req_data);
	$req_id = $merged_data['ID'];

	if (empty($req_id)) {
		return [['status'=>'error', 'error'=>'no id field'],[]];
	}

	$data_file = "{$table_root}/{$req_id}.json";
	$ori_data = object_read($data_file);
	$ori_output = array_merge(array(), $ori_data);

	//格式化输入结构，填充空白字段
	$req_data = format_fields($schema, $req_data);

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
	$result = ['status'=>'ok', 'ID'=>$req_id, 'listview'=>$listview_item];
	return [$result, $ori_output]; 
}

function refresh_data($db_name, $table_name, $append_data_file=null)
{
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
				foreach($append_data_file as $data_file) {
					$merge_items = merge_fields(object_read($data_file));
					$id_input = @$merge_items['ID'];
					if (empty($id_input)) {
						continue;
					}
					
					//找到就是替换并直接退出，找不到就是追加，并抛给下一个流程
					foreach($listview_data as &$subitem) {
						if ($subitem[$id_index] !== $id_input) {continue;}
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
			$glob_files = glob("{$data_path}/*.json");
		}

		foreach($glob_files as $file) {
			if (is_dir($file)) {continue;}
			if (!preg_match('~/(\d+)\.json$~',$file, $matches)){continue;}
			$item_id = $matches[1];

			$data_obj = object_read($file);
			if (empty($data_obj)) {continue;}

			$merge_items = merge_fields($data_obj);

			//生成listview
			array_unshift($listview_data, new_listview_item($listview, $merge_items, $merged_fields));
			//生成mapper
			update_mappers($mapper_data, $mapper_fields, $merge_items);
			//更新options
			update_options($options_data, $options_fields, $merge_items);
		}

	}while(false);

	object_save("{$data_path}/mapper.json", $mapper_data);
	object_save("{$data_path}/listview.json", $listview_data);
	object_save("{$data_path}/options.json", $options_data);
	return count($listview_data);
}

function update_options(&$options_data, $options_fields, $merged_item)
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


function make_listview_item($schema, $data_obj)
{
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
				if ($field_name === 'jqxDateTimeInput'){
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
					$value = implode(',<br>',$value_out);
				}

				if (preg_match('/^jqxCheckBox/i', $field_name)) {
					$value = implode(',<br>',$value);
				}
			}

			$output[] = $value;
		} else {
			$output[] = '';
		}
	}
	return $output;
}

function update_mappers(&$mapper_data, $mapper_fields, $merged_item)
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
	foreach($group_obj as $group=>$items) {
		foreach($items as $name=>$value) {
			$merge_items[$name] = $value;
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

function object_read_url($req_url)
{
	$res = curl_get_content($req_url);

	if (empty($res)) {
		return [];
	}

	$res = preg_replace( '/[^[:print:]]/', '',$res);
	$res = preg_replace( '/[\s]+/', '',$res);
	preg_match("#{[\s]*\".*[\s]*}#ui", $res, $mm);

	$res_body = @$mm[0];

	if (empty($res_body)) {
		return [];
	}

	return json_decode($res_body, true);
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
	foreach (glob(dbs_path().'/*', GLOB_ONLYDIR) as $db_path) { 
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
		foreach (glob(dbs_path()."/{$db_name}/*", GLOB_ONLYDIR) as $table_path) { 
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

function get_random_id($db_name, $table_name)
{
	$table_root = table_root($db_name, $table_name);
	$maxid_file = "{$table_root}/maxid.json";
	$schema_file = "{$table_root}/schema.json";

        $mutex = sem_get(ftok($schema_file, 'r'), 1);
	sem_acquire($mutex);

	($max_id = file_get_contents($maxid_file)) || ($max_id = 0);
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

function curl_get_content($url, $user_agent=null)
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
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 3);

	$res = curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$err = curl_errno($ch);
	curl_close($ch);

	if (($err) || ($httpcode !== 200)) {
		return null;
	}

	return $res;
}


?>
