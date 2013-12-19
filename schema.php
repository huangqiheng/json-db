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
    case 'update_fields': update_fields_exit($req);
    case 'refresh_data': refresh_data_exit($req);
    default: jsonp_nocache_exit(['status'=>'error', 'error'=>'unknow command.']);
}

function backup_database_exit($req)
{
	$db_name = @$req['db_name'];
	if (empty($db_name)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'db_name empty.']);
	}

	$db_path = dbs_path();
	$backup_dir = 'backup';
	$bak_dir = "{$db_path}/{$backup_dir}";
	if (!file_exists($bak_dir)) {
		mkdir($bak_dir);
	}

	$bak_file = "{$backup_dir}/{$db_name}-".gmdate("YmdHis", time()).'.tar.gz';
	exec("cd {$db_path} && tar -czf {$bak_file} {$db_name}");

	$bak_url = substr($bak_file, strlen($_SERVER['DOCUMENT_ROOT']));
	jsonp_nocache_exit(['status'=>'ok', 'file'=>$bak_url]); 
}

function update_fields_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$table_root = table_root($db_name, $table_name);
	$schema_file = "{$table_root}/schema.json";
	$schema = object_read($schema_file);

	$fields_req = $req['fields'];
	$listview_req = $req['listview'];
	$listview = $schema['listview'];

	$diff1 = array_diff($listview_req, $listview);
	$diff2 = array_diff($listview, $listview_req);
	$is_same = (empty($diff1) && empty($diff2));

	$schema['fields'] = $fields_req;
	$schema['listview'] = $listview_req;
	object_save($schema_file, $schema);

	if (!$is_same) {
		refresh_data($db_name, $table_name);
	}

	jsonp_nocache_exit(['status'=>'ok']); 
}

function refresh_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$counter = refresh_data($db_name, $table_name);
	jsonp_nocache_exit(['status'=>'ok', 'counter'=>$counter]); 
}


function del_database_exit($req)
{
	$db_name = $req['db_name'];
	$db_root = db_root($db_name);
	rmdir_Rf($db_root);
	jsonp_nocache_exit(['status'=>'ok']); 
}

function del_table_exit($req)
{
	$db_name = $req['db_name'];
	$table_name = $req['table_name'];
	$table_root = table_root($db_name, $table_name);
	rmdir_Rf($table_root);
	jsonp_nocache_exit(['status'=>'ok']); 
}

function create_database_exit($req)
{
	$db_root = db_root($req['name']);

	if (file_exists($db_root)) {
		jsonp_nocache_exit(['status'=>'error', 
			'error'=>'The request database already exists.',
			'ori_cmd'=> $req]);
	} else {
		mkdir($db_root, 0744);
	}

	$db_schema = "{$db_root}/schema.json";
	if (file_exists($db_schema)) {
		unlink($db_schema);
	}

	$caption = [];
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['image'] = $req['image']; 

	$schema = [];
	$schema['caption'] = $caption;

	object_save($db_schema, $schema);
	jsonp_nocache_exit(['status'=>'ok']); 
}

function edit_database_exit($req)
{
	$old_name = dbs_path()."/{$req['ori_name']}";
	if (!file_exists($old_name)) {
		jsonp_nocache_exit(['status'=>'error', 
			'error'=>'The request database not found.',
			'ori_cmd'=> $req]);
	}

	$filename = dbs_path()."/{$req['name']}";
	if ($req['ori_name'] !== $req['name']) {
		rename($old_name, $filename);
	}

	$filename = "{$filename}/schema.json";
	$schema = object_read($filename);

	$caption = [];
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['image'] = $req['image']; 
	$schema['caption'] = $caption;

	object_save($filename, $schema);
	jsonp_nocache_exit(['status'=>'ok']); 
}

function create_table_exit($req)
{
	$filename = dbs_path()."/{$req['db_name']}";
	if (!file_exists($filename)) {
		jsonp_nocache_exit(['status'=>'error', 
			'error'=>'The request database not found.',
			'ori_cmd'=> $req]);
	}

	$filename = "{$filename}/{$req['name']}";

	if (file_exists($filename)) {
		jsonp_nocache_exit(['status'=>'error', 
			'error'=>'The request table already exists.',
			'ori_cmd'=> $req]);
	} else {
		mkdir($filename, 0744);
	}

	$caption = [];
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['image'] = $req['image']; 

	$listview = [];
	array_push($listview, 'ID', 'Name');

	$fields = [];
	$general = [];
	$general['ID'] = 'jqxInput-id';
	$general['Name'] = 'jqxInput-name';
	$fields['general'] = $general;

	$schema = [];
	$schema['caption'] = $caption;
	$schema['listview'] = $listview;
	$schema['fields'] = $fields;

	$filename = "{$filename}/schema.json";
	object_save($filename, $schema);
	jsonp_nocache_exit(['status'=>'ok']); 
}

function edit_table_exit($req)
{
	$filename = dbs_path()."/{$req['db_name']}";
	if (!file_exists($filename)) {
		jsonp_nocache_exit(['status'=>'error', 
			'error'=>'The request database not found.',
			'ori_cmd'=> $req]);
	}

	$old_name = "{$filename}/{$req['ori_name']}";
	if (!file_exists($old_name)) {
		jsonp_nocache_exit(['status'=>'error', 
			'error'=>'The request table not found.',
			'ori_cmd'=> $req]);
	}

	$filename = "{$filename}/{$req['name']}";
	if ($req['ori_name'] !== $req['name']) {
		rename($old_name, $filename);
	}

	$filename = "{$filename}/schema.json";
	$schema = object_read($filename);

	$caption = [];
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['image'] = $req['image']; 
	$schema['caption'] = $caption;

	object_save($filename, $schema);
	jsonp_nocache_exit(['status'=>'ok']); 
}

?>

