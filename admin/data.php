<?php
require_once 'functions.php';
require_once 'webhook.php';
denies_with_json();

$req = get_param();

switch($req['cmd']) {
    case 'update': update_data_exit($req);
    case 'create': create_data_exit($req);
    case 'delete': delete_data_exit($req);
    case 'refresh_data': refresh_listview_exit($req);
    default: jsonp_nocache_exit(['status'=>'error', 'error'=>'unknow command.']);
}

function refresh_listview_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$counter = refresh_listview($db_name, $table_name);
	clean_unmapper_data($db_name, $table_name);
	jsonp_nocache_exit(['status'=>'ok', 'counter'=>$counter]); 
}

function delete_data_exit($req)
{
	$db_name = @$req['db_name'];
	$table_name = @$req['table_name'];
	$req_list = @$req['list'];
	sync_remove_sameid($db_name, $table_name, $req_list);
	jsonp_nocache_exit(delete_current_data($db_name, $table_name, $req_list)); 
}

function create_data_exit($req)
{
	jsonp_nocache_exit(create_new_data(@$req['db_name'], @$req['table_name'], @$req['data'])); 
}

function update_data_exit($req)
{
	$force_empty = @$req['force_empty'] === 'true';
	jsonp_nocache_exit(update_current_data(@$req['db_name'], @$req['table_name'], @$req['data'], $force_empty)); 
}


?>
