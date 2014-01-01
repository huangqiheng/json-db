<?php
require_once 'login.php';
require_once 'config.php';

/*****************************************
	共享函数
*****************************************/

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

function set_data_id($data, $new_id)
{
	foreach($data as $group=>&$items){
		if (array_key_exists('ID', $items)) {
			$items['ID'] = $new_id;
			return true;
		}
	}
	return false;
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

	if (!set_data_id($data, get_random_id())) {
		return ['status'=>'error', 'error'=>'no id field'];
	}

	$new_id_file = "{$table_root}/{$new_id}.json";
	object_save($new_id_file, $data);
	refresh_data($db_name, $table_name, $new_id_file);

	$listview_item = make_listview_item($schema, $data);
	return ['status'=>'ok', 'listview'=>$listview_item]; 
}

function update_current_data($db_name, $table_name, $req_data)
{
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		return ['status'=>'error', 'error'=>'schema file error'];
	}

	if (empty($req_data)) {
		return ['status'=>'error', 'error'=>'req data error'];
	}

	$merged_data = merge_fields($req_data);
	$req_id = $merged_data['ID'];

	if (empty($req_id)) {
		return ['status'=>'error', 'error'=>'no id field'];
	}

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
	return ['status'=>'ok', 'listview'=>$listview_item]; 
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
			$merge_items = merge_fields(object_read($append_data_file));

			$id_input = $merge_items['ID'];
			$id_index = array_search('ID', $listview);
			if ($id_index === false) {
				//找不到ID，这是个不正常的记录
				jsonp_nocache_exit(['status'=>'error', 'error'=>'ID field not found, listview content error.']);
			}

			//找到就是替换并直接退出，找不到就是追加，并抛给下一个流程
			foreach($listview_data as &$subitem) {
				$id_cmp = $subitem[$id_index];
				if ($id_cmp !== $id_input) {
					continue;
				}

				//替换listview
				$subitem = new_listview_item($listview, $merge_items);
				//更新mapper
				update_mappers($mapper_data, $mapper_fields, $merge_items);
				//更新options
				update_options($options_data, $options_fields, $merge_items);

				break 2;
			}

			$glob_files[] = $append_data_file;
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
			array_unshift($listview_data, new_listview_item($listview, $merge_items));
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

		if (!in_array($opt_val, $ori_arr)) {
			$ori_arr[] = $opt_val;
			$options_data[$opt_name] = $ori_arr;
		}
	}
}


function make_listview_item($schema, $data_obj)
{
	$listview = $schema['listview'];
	$merge_items= merge_fields($data_obj);
	return new_listview_item($listview, $merge_items);
}

function new_listview_item($listview, $merge_items)
{
	$output = [];
	foreach($listview as $view_item) {
		$value = @$merge_items[$view_item];
		if ($value) {
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
			$mapper_data[$map_key] = $item_id;
			$mapper_data[md5($map_key)] = $item_id;
			continue;
		}

		if (is_array($map_key)) {
			foreach($map_key as $key) {
				$mapper_data[$key] = $item_id;
				$mapper_data[md5($key)] = $item_id;
			}
		}
	}
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

function object_save($filename, $data)
{
	file_put_contents($filename, prety_json($data));
}

function object_read($filename)
{
	if (!file_exists($filename)) {
		return [];
	}

	$data_str = file_get_contents($filename);
	if ($data_str === null) {
		return [];
	}
	return json_decode($data_str, true);
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

function get_random_id()
{
	$ran_val = time()-get_basetime();
	return strval($ran_val);
}

function get_selected_db()
{
	return [get_param('db', 'default'), get_param('table', 'default'), get_param('id', get_random_id())];
}

function json_file($file_name)
{
	do {
		list($database,$table,$id) = get_selected_db();
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
