<?php
require_once 'functions.php';

$req = get_param();

switch($req['cmd']) {
    case 'new_database': create_database_exit($req);
    case 'edit_database': edit_database_exit($req);
    case 'new_table': create_table_exit($req);
    case 'edit_table': edit_table_exit($req);
    case 'del_database': del_database_exit($req);
    case 'del_table': del_table_exit($req);
    default: jsonp_nocache_exit(['status'=>'error', 'error'=>'unknow command.']);
}

function del_database_exit($req)
{
	$db_name = $req['db_name'];
	$filename = db_path()."/{$db_name}";
	rmdir_Rf($filename);
	jsonp_nocache_exit(['status'=>'ok']); 
}

function del_table_exit($req)
{
	$db_name = $req['db_name'];
	$table_name = $req['table_name'];
	$filename = db_path()."/{$db_name}/{$table_name}";
	rmdir_Rf($filename);
	jsonp_nocache_exit(['status'=>'ok']); 
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

function create_database_exit($req)
{
	$filename = db_path()."/{$req['name']}";

	if (file_exists($filename)) {
		jsonp_nocache_exit(['status'=>'error', 
			'error'=>'The request database already exists.',
			'ori_cmd'=> $req]);
	} else {
		mkdir($filename, 0744);
	}

	$filename = "{$filename}/schema.json";
	if (file_exists($filename)) {
		unlink($filename);
	}

	$caption = [];
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['image'] = $req['image']; 

	$schema = [];
	$schema['caption'] = $caption;

	file_put_contents($filename, prety_json($schema));
	jsonp_nocache_exit(['status'=>'ok']); 
}

function edit_database_exit($req)
{
	$old_name = db_path()."/{$req['ori_name']}";
	if (!file_exists($old_name)) {
		jsonp_nocache_exit(['status'=>'error', 
			'error'=>'The request database not found.',
			'ori_cmd'=> $req]);
	}

	$filename = db_path()."/{$req['name']}";
	if ($req['ori_name'] !== $req['name']) {
		rename($old_name, $filename);
	}

	$filename = "{$filename}/schema.json";
	$schema = file_get_contents($filename);
	$schema = json_decode($schema, true);

	$caption = [];
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['image'] = $req['image']; 
	$schema['caption'] = $caption;

	file_put_contents($filename, prety_json($schema));
	jsonp_nocache_exit(['status'=>'ok']); 
}

function create_table_exit($req)
{
	$filename = db_path()."/{$req['db_name']}";
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
	$fields['ID'] = 'jqxInput-id';
	$fields['Name'] = 'jqxInput-name';

	$schema = [];
	$schema['caption'] = $caption;
	$schema['listview'] = $listview;
	$schema['fields'] = $fields;

	$filename = "{$filename}/schema.json";
	file_put_contents($filename, prety_json($schema));
	jsonp_nocache_exit(['status'=>'ok']); 
}

function edit_table_exit($req)
{
	$filename = db_path()."/{$req['db_name']}";
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
	$schema = file_get_contents($filename);
	$schema = json_decode($schema, true);

	$caption = [];
	$caption['title'] = $req['title']; 
	$caption['content'] = $req['content']; 
	$caption['image'] = $req['image']; 
	$schema['caption'] = $caption;

	file_put_contents($filename, prety_json($schema));
	jsonp_nocache_exit(['status'=>'ok']); 
}

?>

