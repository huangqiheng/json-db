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

function sync_share_fields($db_name, $table_name, $item_id)
{

}

function delete_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$req_list = @$req['list'];
	$res = delete_current_data($db_name, $table_name, $req_list);
	if ($res['status'] === 'ok') {
		sync_remove_sameid($db_name, $table_name, $req_list);
	}
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
		sync_share_fields($db_name, $table_name, $item_id);
	}
	jsonp_nocache_exit($res); 
}

function update_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$req_data = @$req['data'];
	$res = update_current_data($db_name, $table_name, $req_data);
	if ($res['status'] === 'ok') {
		$item_id = $res['ID'];
		sync_share_fields($db_name, $table_name, $item_id);
	}
	jsonp_nocache_exit($res); 
}


?>
