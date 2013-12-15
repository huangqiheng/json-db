<?php
require_once 'functions.php';

$req = get_param();

switch($req['cmd']) {
    case 'update': update_data_exit($req);
    case 'create': create_data_exit($req);
    default: jsonp_nocache_exit(['status'=>'error', 'error'=>'unknow command.']);
}

function create_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	if (empty($schema)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'schema file error']);
	}

	$data = @$req['data'];
	if (empty($data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'req data error']);
	}

	$new_id = get_random_id();

	foreach($data as $group=>&$items){
		if (array_key_exists('ID', $items)) {
			$items['ID'] = $new_id;
			break;
		}
	}

	$new_id_file = "{$table_root}/{$new_id}.json";
	object_save($new_id_file, $data);
	refresh_data($db_name, $table_name, $new_id_file);

	jsonp_nocache_exit(['status'=>'ok']); 
}

function update_data_exit($req)
{

}

?>
