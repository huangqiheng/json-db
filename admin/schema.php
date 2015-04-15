<?php
require_once 'functions.php';
denies_with_json();

$req = get_param();

switch($req['cmd']) {
    case 'new_database': create_database_exit($req);
    case 'edit_database': edit_database_exit($req);
    case 'backup_database': backup_database_exit($req);
    case 'new_table': create_table_exit($req);
    case 'edit_table': edit_table_exit($req);
    case 'del_database': del_database_exit($req);
    case 'del_table': del_table_exit($req);
    case 'key_data': get_keydata_exit($req);
    default: jsonp_nocache_exit(array('status'=>'error', 'error'=>'unknow command.'));
}

function get_keydata_exit($req)
{
	list($key_file,$db_name) = null_exit($req, 'file', 'db_name');

	$table_name = @$req['table_name'];
	if (empty($table_name)) {
		$db_root = db_root($db_name);
		jsonp_nocache_exit(object_read("{$db_root}/{$key_file}.json"));
	}

	$table_root = table_root($db_name, $table_name);
	jsonp_nocache_exit(object_read("{$table_root}/{$key_file}.json"));
}

function backup_database_exit($req)
{
	$db_name = @$req['db_name'];
	if (empty($db_name)) {
		jsonp_nocache_exit(array('status'=>'error', 'error'=>'db_name empty.'));
	}

	$db_path = dbs_path();
	$backup_dir = BACKUP_DIR;
	$bak_dir = "{$db_path}/{$backup_dir}";
	if (!file_exists($bak_dir)) {
		mkdir($bak_dir);
	}

	$table_name = @$req['table_name'];
	if (empty($table_name)) {
		$cd_path = $db_path;
		$target_name = $db_name;
		$bak_file = "{$bak_dir}/{$db_name}-".gmdate("YmdHis", time()).'.tar.gz';
	} else {
		$cd_path = "{$db_path}/{$db_name}";
		$target_name = $table_name;
		if (!file_exists($cd_path)) {
			jsonp_nocache_exit(array('status'=>'error', 'error'=>'table_name error.'));
		}
		$bak_file = "{$bak_dir}/{$db_name}-{$table_name}-".gmdate("YmdHis", time()).'.tar.gz';
	}

	//执行命令行
	exec("cd {$cd_path} && tar -czf {$bak_file} {$target_name}");

	$bak_url = substr($bak_file, strlen($_SERVER['DOCUMENT_ROOT']));
	jsonp_nocache_exit(array('status'=>'ok', 'file'=>$bak_url, 'ori'=>$bak_file, 'db_path'=>$db_path)); 
}

function del_database_exit($req)
{
	$db_name = $req['db_name'];
	$db_root = db_root($db_name);
	rmdir_Rf($db_root);
	jsonp_nocache_exit(array('status'=>'ok')); 
}

function del_table_exit($req)
{
	$db_name = $req['db_name'];
	$table_name = $req['table_name'];
	$table_root = table_root($db_name, $table_name);
	rmdir_Rf($table_root);
	wh_event($req['db_name'], $req['table_name'], 'destroy');
	jsonp_nocache_exit(array('status'=>'ok')); 
}

function create_database_exit($req)
{
	$db_root = db_root($req['name']);

	if (file_exists($db_root)) {
		jsonp_nocache_exit(array('status'=>'error', 
			'error'=>'The request database already exists.',
			'ori_cmd'=> $req));
	} else {
		mkdir($db_root, 0744);
	}

	$db_schema = "{$db_root}/schema.json";
	if (file_exists($db_schema)) {
		unlink($db_schema);
	}

	$caption = array();
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['key'] = $req['key']; 
	$caption['image'] = $req['image']; 

	$schema = array();
	$schema['caption'] = $caption;

	object_save($db_schema, $schema);
	jsonp_nocache_exit(array('status'=>'ok')); 
}

function edit_database_exit($req)
{
	$old_name = dbs_path()."/{$req['ori_name']}";
	if (!file_exists($old_name)) {
		jsonp_nocache_exit(array('status'=>'error', 
			'error'=>'The request database not found.',
			'ori_cmd'=> $req));
	}

	$filename = dbs_path()."/{$req['name']}";
	if ($req['ori_name'] !== $req['name']) {
		rename($old_name, $filename);
	}

	touch($filename);

	$filename = "{$filename}/schema.json";
	$schema = object_read($filename);

	$caption = array();
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['image'] = $req['image']; 
	$caption['key'] = $req['key']; 
	$caption['hooks'] = isset($req['hooks'])? array_values($req['hooks']) : array();
	$schema['caption'] = $caption;

	object_save($filename, $schema);
	jsonp_nocache_exit(array('status'=>'ok')); 
}

function create_table_exit($req)
{
	$filename = dbs_path()."/{$req['db_name']}";
	if (!file_exists($filename)) {
		jsonp_nocache_exit(array('status'=>'error', 
			'error'=>'The request database not found.',
			'ori_cmd'=> $req));
	}

	$base_path = "{$filename}/{$req['name']}";

	if (file_exists($base_path)) {
		jsonp_nocache_exit(array('status'=>'error', 
			'error'=>'The request table already exists.',
			'ori_cmd'=> $req));
	} else {
		mkdir($base_path, 0744);
	}

	$caption = array();
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['image'] = $req['image']; 
	$caption['key'] = $req['key']; 

	$listview = array();
	array_push($listview, 'ID', 'Name');

	$fields = array();
	$general = array();
	$general['ID'] = 'jqxInput-id';
	$general['CREATE'] = 'jqxInput-time';
	$general['TIME'] = 'jqxInput-time';
        $general['Name'] = 'jqxInput-name';
	$fields['general'] = $general;

	$onebox = array();
	$onebox['title'] = 'ID';
	$onebox['desc'] = 'Name';
	$onebox['image'] = '';

	$schema = array();
	$schema['caption'] = $caption;
	$schema['listview'] = $listview;
	$schema['fields'] = $fields;
	$schema['onebox'] = $onebox;

	object_save("{$base_path}/schema.json", $schema);
	object_save("{$base_path}/listview.json", array());
	object_save("{$base_path}/options.json", array());

	wh_event($req['db_name'], $req['name'], 'create');
	jsonp_nocache_exit(array('status'=>'ok')); 
}

function edit_table_exit($req)
{
	$filename = dbs_path()."/{$req['db_name']}";
	if (!file_exists($filename)) {
		jsonp_nocache_exit(array('status'=>'error', 
			'error'=>'The request database not found.',
			'ori_cmd'=> $req));
	}

	$old_name = "{$filename}/{$req['ori_name']}";
	if (!file_exists($old_name)) {
		jsonp_nocache_exit(array('status'=>'error', 
			'error'=>'The request table not found.',
			'ori_cmd'=> $req));
	}

	$filename = "{$filename}/{$req['name']}";
	if ($req['ori_name'] !== $req['name']) {
		rename($old_name, $filename);
	}

	touch($filename);

	$filename = "{$filename}/schema.json";
	$schema = object_read($filename);

	$caption = array();
	$caption['title'] = @$req['title']; 
	$caption['content'] = @$req['content']; 
	$caption['image'] = @$req['image']; 
	$caption['key'] = @$req['key']; 
	$caption['hooks'] = isset($req['hooks'])? array_values($req['hooks']) : array();
	$schema['caption'] = $caption;

	object_save($filename, $schema);
	jsonp_nocache_exit(array('status'=>'ok')); 
}

?>

