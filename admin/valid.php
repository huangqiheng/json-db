<?php
require_once 'functions.php';
denies_with_json();

$req = get_param();
list($db_name, $table_name, $check_what, $check_is) = null_exit($req, 'db_name', 'table_name', 'what', 'is');

if ($check_what === 'mapper') {
	list($mapped_id,$data_file,$data_url) = mapper_value_exit($db_name, $table_name, $check_is);
	jsonp_nocache_exit(['status'=>'ok', 'id'=>$mapped_id, 'url'=>$data_url]);
}

jsonp_nocache_exit(['status'=>'error', 'error'=>'unknow command.']);
