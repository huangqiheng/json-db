<?php


/***********************************************
	日志对象
************************************************/
/*
$schemas = array(
      array(
	    'name' => 'gateway',
	    'title' => '日志数据库',
	    'content' => '分级显示系统日志，轻量级Web显示',
	    'image' => 'http://www.doctorcom.com/statics/images/style2012/logo.jpg'),
      array(
	    'name' => 'proxy',
	    'title' => '代理网关日志',
	    'content' => '拦截消息',
	    'image' => 'http://mta.qq.com/mta/resource/imgcache/images/logo.png')
);

$log_db = new LogDB($schemas);
$log_db->info('asdfasdf');
print_r($log_db->commit());
*/

Class LogDB extends Jsondb 
{

function __construct($schemas, $table_name=null, $domain_name=null) 
{
	(count($schemas)<2) and trigger_error('input schemas error.');

	if ($schema_db = array_shift($schemas)) {
		$this->db_name = $schema_db['name'];
		$this->schema_db = array(); 
		$this->schema_db['caption'] = array(
		    'title' => $schema_db['title'],
		    'content' => $schema_db['content'],
		    'image' => $schema_db['image']
		);
	}

	$this->schema_tables = array();
	foreach($schemas as $item) {
		$name = $item['name'];
		$this->schema_tables[$name] = array(
			'caption' => array(
			    'title' => $item['title'],
			    'content' => $item['content'],
			    'image' => $item['image']),
			'fields' => array( 
			    'general' => array(
				'ID' => 'jqxInput-id',
				'TIME' => 'jqxInput-time',
				'CREATE' => 'jqxInput-time'),
			    'log' => array(
				'ident' => 'jqxInput',
				'facility' => 'jqxInput',
				'priority' => 'jqxInput',
				'title' => 'jqxInput',
				'data' => 'jqxInput-text-json')),
			'listview' => array('ID', 'title'));
	}

	parent::__construct($this->db_name, $table_name, $domain_name);
}

private $ident = 'identify';
private $facility = 'facility';

function set($table_name, $ident=null, $facility=null) 
{
	$this->table_name = $table_name;
	$ident and ($this->ident = $ident);
	$facility and ($this->facility = $facility);
}

function log($priority, $msg_title, $msg_data=null) 
{
	if (empty($this->table_name)) {
		$keys = array_keys($this->schema_tables);
		$this->table_name = array_shift($keys);
	}

	$data = array(
		'general' => array(
			'ID' => jsondb_id($this->db_name, $this->table_name),
			'TIME' => jsondb_date(),
			'CREATE' => jsondb_date()),
		'log' => array(
			'ident' => $this->ident,
			'facility' => $this->facility,
			'priority'=> $priority,
			'title' => $msg_title,
			'data' => $msg_data)
	);
	return $this->data(null, $data);
}

function error($caption,$data=null){return $this->log('error',$caption,$data);}
function warn($caption,$data=null){return $this->log('warn',$caption,$data);}
function info($caption,$data=null){return $this->log('info',$caption,$data);}
function debug($caption,$data=null){return $this->log('debug',$caption,$data);}
function trace($caption,$data=null){return $this->log('trace',$caption,$data);}
}

/***********************************************
	对象接口 
************************************************/

class Jsondb 
{

public $domain_name = null; 
public $db_name; 
public $table_name = null;
public $schema_db = null;
public $schema_tables = null;

function __construct($db_name=null, $table_name=null, $domain_name=null) 
{
	$db_name and ($this->db_name= $db_name);
	$table_name and ($this->table_name = $table_name);
	$domain_name and ($this->domain_name = $domain_name);

	if (empty($table_name)) {
		if (array_key_exists('default', $this->schema_tables)) {
			$this->table_name = 'default';
		}
	}

	empty($this->db_name) and trigger_error('db_name not set.');

	//主动设置域名,如果为空，则是当前域名。作为库调用时，要明确设置
	jsondb_root($this->domain_name, true);

	//如果发现子类设置了schema，对于新库则初始化数据库
	if ($this->schema_db) {
		if (empty(jsondb_schema($this->db_name))) {
			jsondb_schema($this->db_name, null, $this->schema_db);
		}
	}

	//初始化数据表schema
	if ($this->schema_tables) {
		foreach($this->schema_tables as $name=>$schema) {
			if (empty(jsondb_schema($this->db_name, $name))) {
				jsondb_schema($this->db_name, $name, $schema);
			}
		}
	}
}

function views($table_name=null, $force_update=false) 
{
	$table_name and ($this->table_name = $table_name);
	return jsondb_views($this->db_name, $this->table_name, $force_update);
}

function commit($table_name=null) 
{
	$table_name and ($this->table_name = $table_name);
	return jsondb_commit($this->db_name, $this->table_name);
}

function data($mapper_key, $data=null, $table_name=null) 
{
	$table_name and ($this->table_name = $table_name);
	return jsondb_data($this->db_name, $this->table_name, $mapper_key, $data);
}

function url($base_name, $table_name=null) 
{
	$table_name and ($this->table_name = $table_name);
	return jsondb_url($this->db_name, $this->table_name, $base_name);
}
}


/***********************************************
	函数式接口 
************************************************/

$jsondb_root_dir = null;

function jsondb_root($domain_name=null, $force_update=false)
{
	global $jsondb_root_dir;

	if ($force_update === false) {
		if ($jsondb_root_dir) {
			return $jsondb_root_dir;
		}
	}

	$check_dirs = array(dirname(__DIR__));
	if (empty($domain_name)) {
		if (array_key_exists('HTTP_HOST', $_SERVER)) {
			$domain_name = $_SERVER['HTTP_HOST'];
		}
	}
	if ($domain_name) {
		array_push($check_dirs, __DIR__ . '/databases/' . $domain_name);
	}

	foreach($check_dirs as $check_dir) {
		$www_root = dirname(dirname($check_dir));
		if (file_exists($www_root.'/admin')) {
			if (file_exists($www_root.'/service')) {
				$jsondb_root_dir = $check_dir;
				return $jsondb_root_dir;
			}
		}
	}

	trigger_error('dbase.php are not in the right place.');
	return __DIR__ . '/databases/default.db';
}

function jsondb_schema($db_name, $table_name=null, $schema=null)
{
	return data_object($db_name, $table_name, 'schema.json', $schema);
}

function jsondb_data($db_name, $table_name, $mapper_key, $data=null)
{
	if ($data) {
		if ($data === 'remove') {
			return del_data($db_name, $table_name, $mapper_key);
		} else {
			return set_data($db_name, $table_name, $mapper_key, $data);
		}
	} else {
		return get_data($db_name, $table_name, $mapper_key);
	}
}

function jsondb_commit($db_name, $table_name)
{
	$json_files = glob(jsondb_file($db_name, $table_name, '*.json'));

	$ids = [];
	$views = [];
	$maps = [];
	foreach($json_files as $file) {
		if (is_dir($file)) {continue;}
		if (preg_match('~/(\d+)\.json$~',$file, $matches)){
			$ids[] = intval($matches[1]);
			continue;
		}
		if (preg_match('~/(\d+)\.view\.json$~',$file, $matches)){
			$views[] = intval($matches[1]);
			continue;
		}
		if (preg_match('~/(\d+)\.map\.json$~',$file, $matches)){
			$maps[] = intval($matches[1]);
		}
	}

	//处理列表显示
	$lack_views = array_diff($ids, $views);
	$lack_views_rm = array_diff($views, $ids);
	foreach($lack_views as $ID) {
		$data_view = data_view($db_name, $table_name, $ID);
		if (empty($data_view)) {
			$lack_views_rm[] = $ID;
			continue;
		}
		$data_str = json_encode($data_view) . ',';
		data_object($db_name, $table_name, $ID.'.view.json', $data_str, false);
	}
	foreach($lack_views_rm as $ID) {
		$file = jsondb_file($db_name, $table_name, $ID.'.view.json');
		unlink($file);
	}

	//处理映射表数据
	$lack_maps = array_diff($ids, $maps);
	$lack_maps_rm = array_diff($maps, $ids);
	foreach($lack_maps as $ID) {
		$data_mapper = data_mapper($db_name, $table_name, $ID);
		$data_str = json_encode($data_mapper) . ',';
		data_object($db_name, $table_name, $ID.'.map.json', $data_str, false);
	}
	foreach($lack_maps_rm as $ID) {
		$file = jsondb_file($db_name, $table_name, $ID.'.map.json');
		unlink($file);
	}

	//生成新的listview
	$items = update_aggregate_file($db_name, $table_name, '*.view.json', 'listview.json');

	//从大到小排序
	usort($items, function($a, $b){
		$a_val = intval($a[0]);
		$b_val = intval($b[0]);
		if ($a_val === $b_val) return 0;
		return ($a_val > $b_val)? -1 : 1;
	});

	$filename = jsondb_file($db_name, $table_name, 'listview.json');
	file_put_contents($filename, json_encode($items));

	return $items;
}


function jsondb_views($db_name, $table_name, $force_update=false)
{
	if (empty($table_name)) {
		return [];
	}

	if ($force_update) {
		return jsondb_commit($db_name, $table_name);
	} else {
		return data_object($db_name, $table_name, 'listview.json');
	}
}

function jsondb_url($db_name, $table_name, $base_name)
{
	$table_root = "http://{$_SERVER['HTTP_HOST']}/databases/{$db_name}/{$table_name}";
	return "{$table_root}/{$base_name}";
}

function jsondb_file($db_name, $table_name, $base_name)
{
	if ($table_name) {
		return jsondb_root()."/{$db_name}/{$table_name}/{$base_name}";
	} else {
		return jsondb_root()."/{$db_name}/{$base_name}";
	}
}


function jsondb_id($db_name, $table_name)
{
	$maxid_file = jsondb_file($db_name, $table_name, 'maxid.json');
	$schema_file = jsondb_file($db_name, $table_name, 'schema.json');

        $mutex = sem_get(ftok($schema_file, 'r'), 1);
	sem_acquire($mutex);

	($max_id = @file_get_contents($maxid_file)) || ($max_id = 0);
	$ran_val = intval((microtime(true)-mktime(0,0,0,12,21,2012))*1000);
	$res_val = ($ran_val > $max_id)? $ran_val : ++$max_id;
	file_put_contents($maxid_file, $res_val);

	sem_release($mutex);
	return $res_val;
}

function jsondb_date($time=null)
{
	if (empty($time)) {
		$time = time();
	}
        return gmdate('D, d M Y H:i:s \G\M\T', $time);
}

/***********************************************
	功能接口函数分界线
************************************************/


function del_data($db_name, $table_name, $mapper_key)
{

}

function get_data($db_name, $table_name, $mapper_key)
{
	$mapper_data = data_object($db_name, $table_name, 'mapper.json');
	$mapper_val = @$mapper_data[$mapper_key];
	if (empty($mapper_val)) {
		return false;
	}

	$res_data = data_object($db_name, $table_name, $mapper_val.'.json');
	if (empty($res_data)) {
		return false;
	}

	return $res_data;
}

function set_data($db_name, $table_name, $mapper_key, $data)
{
	//检查是否合法
	$merged_data = flat_fields($data);
	$ID = @$merged_data['ID'];
	if (empty($ID)) {
		return false;
	}

	//(1)更新映射片段	
	$mappers = data_mapper($db_name, $table_name, $data);
	if ($mapper_key) {
		if (!array_key_exists($mapper_key, $mappers)) {
			$mappers[] = $mapper_key;
		}
	}
	$map_str = '';
	foreach($mappers as $mapper_name) {
		$map_str .= "\"{$mapper_name}\":{$ID},";
	}
	if (!empty($map_str)) {
		data_object($db_name, $table_name, $ID.'.map.json', $map_str, false);
	}

	//(2)更新新的视图片段
	$data_view = data_view($db_name, $table_name, $data);
	$data_str = json_encode($data_view) . ',';
	data_object($db_name, $table_name, $ID.'.view.json', $data_str, false);

	//(3)更新ID列表
	data_idlist($db_name, $table_name, $ID);

	return data_object($db_name, $table_name, $ID.'.json', $data);
}

function update_aggregate_file($db_name, $table_name, $glob_from, $target_file, $ret_result=true)
{
	$globs_file = jsondb_file($db_name, $table_name, $glob_from);
	$output_file = jsondb_file($db_name, $table_name, $target_file);
	$temp_name = $target_file.'.tmp';
	$temp_file = jsondb_file($db_name, $table_name, $temp_name);

	exec("truncate --size 0 {$temp_file}");
	exec("echo -n [ >> {$temp_file}");
	exec("cat {$globs_file} >> {$temp_file}");
	exec("truncate --size -1 {$temp_file}");
	exec("echo -n ] >> {$temp_file}");
	exec("mv {$temp_file} {$output_file}");

	if ($ret_result) {
		return data_object($db_name, $table_name, $target_file);
	}
	return true;
}


function jsondb_reset($db_name, $table_name)
{
	if (empty($db_name) || empty($table_name)) {
		return false;
	}

	//要先保存schema.json
	$schema = data_object($db_name, $table_name, 'schema.json');

	if (empty($schema)) {
		return false;
	}

	$rm_file = jsondb_file($db_name, $table_name, '*.json');
	exec("rm -f {$rm_file}");
	data_object($db_name, $table_name, 'schema.json', $schema);
	return true;
}


function data_idlist($db_name, $table_name, $ID, $is_add=true)
{
	$result = false;
	$idlist_file = jsondb_file($db_name, $table_name, 'idlist.json');
	if (!file_Exists($idlist_file)) {
		exec("echo -n \"[\nnull]\" > {$idlist_file}");
	}

	$output = [];
	$return_val = 0;

	if ($is_add === true) {
		exec("grep \"{$ID},\" {$idlist_file}", $output, $return_val);

		//如果没有发现，则添加
		if ($return_val === 1) {
			exec("sed -i '2s/^/{$ID},\\n/' {$idlist_file}", $output, $return_val);
			$result = ($return_val === 0);
		}

	} else {
		//直接执行删除，不管原来存在与否
		exec("sed -i '/^{$ID},$/d' {$idlist_file}", $output, $return_val);
		$result = ($return_val === 0);
	}

	return $result;
}

function data_mapper($db_name, $table_name, $data)
{
	if (is_int($data)) {
		$data = data_object($db_name, $table_name, $data.'.json');
	}

	$schema = data_object($db_name, $table_name, 'schema.json');
	$fields = $schema['fields'];
	$merged_fields = flat_fields($fields);
	$merged_data = flat_fields($data);

	$mapper_types = [
		'jqxInput-name',
		'jqxListBox-name'
	];
	$results = [];

	foreach($merged_fields as $field=>$value) {
		if (in_array($value, $mapper_types)) {
			$mapper_value = @$merged_data[$field];
			if (empty($mapper_value)) {
				continue;
			}

			if (is_array($mapper_value)) {
				foreach($mapper_value as $sub_val) {
					$results[] = $sub_val;
				}
			} else {
				$results[] = $value;
			}
		}
	}

	return $results;
}

function data_view($db_name, $table_name, $data)
{
	if (is_int($data)) {
		$data = data_object($db_name, $table_name, $data.'.json');
	}

	$schema = data_object($db_name, $table_name, 'schema.json');
	$listview = $schema['listview'];
	$fields = $schema['fields'];
	$merged_fields = flat_fields($fields);
	$merged_data = flat_fields($data);

	$output = [];
	foreach($listview as $view_item) {
		$value = @$merged_data[$view_item];
		if (empty($value)) {
			$output[] = '';
		} else {
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
		}
	}
	return $output;
}

function data_object($db_name, $table_name, $base_name, $data=null, $encode=true)
{
	$filename = jsondb_file($db_name, $table_name, $base_name);

	//写入操作
	if ($data) {
		if ($encode) {
			$data = prety_json($data);
		}

		$base_dir = dirname($filename);
		if (!file_exists($base_dir)) {
			mkdir($base_dir, 0755, true);
		}
		
		file_put_contents($filename, $data);
		return $data;
	} else {
	//读出操作
		if (!file_exists($filename)) {
			return [];
		}

		$data_str = file_get_contents($filename);
		if ($data_str === null) {
			return [];
		}

		$res = json_decode($data_str, true);
		if (empty($res)) {
			return [];
		}

		return $res;
	}
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

function get_confs($db_name=null, $table_name=null, $key_field=null)
{
	//生成json数据文件列表
	$db_root = jsondb_root();
	$glob_str = $db_root;
	if (!empty($db_name)) {
		$glob_str .= '/'.$db_name;
	} else {
		$glob_str .= '/*';
	}
	if (!empty($table_name)) {
		$glob_str .= '/'.$table_name;
	} else {
		$glob_str .= '/*';
	}
	$glob_str .= '/*.json';

	$json_files = glob($glob_str);

	$items = [];
	foreach($json_files as $file) {
		if (is_dir($file)) {continue;}
		if (!preg_match('~/.+/(.+)/(.+)/(\d+)\.json$~',$file, $matches)){continue;}

		$db_name  = $matches[1];
		$table_name  = $matches[2];
		$data = $matches[3];

		if (!array_key_exists($db_name, $items)) {
			$items[$db_name] = [];
		}
		$db_item = &$items[$db_name];

		if (!array_key_exists($table_name, $db_item)) {
			$db_item[$table_name] = [];
		}
		$table_item = &$db_item[$table_name];

		$data_str = file_get_contents($file);
		if ($data_str === null) {continue;}

		$data_obj = json_decode($data_str, true);
		if (empty($data_obj)) {continue;}

		$merge_items = flat_fields($data_obj);
		unset($merge_items['ID']);
		unset($merge_items['CREATE']);
		unset($merge_items['TIME']);

		if (empty($key_field)) {
			$table_item[] = $merge_items;
		} else {
			if (array_key_exists($key_field, $merge_items)) {
				$table_item[$merge_items[$key_field]] = $merge_items;
			} else {
				$table_item[] = $merge_items;
			}
		}
	}
	return $items;
}

function flat_fields($group_obj)
{
	$is_iterable = function ($var){
		return ($var !== null )
			&& (is_array($var) 
				|| $var instanceof Traversable 
				|| $var instanceof Iterator 
				|| $var instanceof IteratorAggregate
			   );
	};

	$merge_items = [];
	if (!empty($group_obj)) {
		foreach($group_obj as $group=>$items) {
			if (!call_user_func($is_iterable, $items)) continue;
			foreach($items as $name=>$value) {
				$merge_items[$name] = $value;
			}
		}
	}
	return $merge_items;
}

