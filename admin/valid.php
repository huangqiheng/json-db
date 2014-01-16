<?php
require_once 'functions.php';
denies_with_json();

$req = get_param();

switch($req['cmd']) {
    case 'name': ;
    default: jsonp_nocache_exit(['status'=>'error', 'error'=>'unknow command.']);
}

?>

