<?php
require_once 'functions.php';

if (is_direct_called(__FILE__)) {
	//特定的异步方式来触发
	if (async_call()) {
		wh_handler();
		exit;
	}

	//直接使用shell来触发：php -q webhook.php crontab
	if (isset($argv)) {
		if ($argv[1] === 'crontab') {
			wh_handler();
			exit;
		}
	}

	//使用外部url来处罚
	$req = get_param();
	switch($req['cmd']) {
		case 'crontab': wh_handler(); exit;
		case 'trigger': wh_trigger_exit(@$req['db'],@$req['table'],@$req['event'],@$req['data']); 
	}
}

function wh_trigger_exit($db, $table, $event, $data)
{
	denies_with_json();
	if (!in_array($event, ['update', 'refresh'])) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'invalid event type']);
	}
	if (wh_event($db, $table, $event, $data) === true) {
		jsonp_nocache_exit(['status'=>'ok']);
	} else {
		jsonp_nocache_exit(['status'=>'error']);
	}
}


