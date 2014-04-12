<?php
require_once 'functions.php';

define('QUEUE_CACHE', __DIR__.'/uploads/cache');

function wh_event($db, $table, $event, $data)
{
	$table_root = table_root($db, $table);
	$table_schema = object_read("{$table_root}/schema.json");
	$hooks = @$table_schema['caption']['hooks'];

	if (empty($hooks)) {
		return;
	}

	$data['db'] = $db;
	$data['table'] = $table;
	queue_in(QUEUE_CACHE, 'webhook', ['hooks'=>$hooks, 'event'=>$event, 'data'=>$data]);

	wh_checkpoint();
}

function wh_checkpoint()
{
	if (queue_empty(QUEUE_CACHE, 'webhook')) {
		return false;
	}
	return async_call('/admin/webhook.php');
}

function wh_remote_call()
{
	$items = queue_out(QUEUE_CACHE, 'webhook');
	if (empty($items)) return;

	$hooks_data = [];
	foreach($items as $item) {
		$hooks = $item['hooks'];
		$event = $item['event'];
		foreach($hooks as $hook) {
			if (!isset($hooks_data[$hook])) {
				$hooks_data[$hook] = [];
			}
			$hook_data = &$hooks_data[$hook];

			if (!isset($hook_data[$event])) {
				$hook_data[$event] = [];
			}
			$datas = &$hook_data[$event];
			$datas[] = $item['data'];
		}
	}

	foreach($hooks_data as $hook_url=>$data) {
		curl_post_content($hook_url, $data);
	}
}

if (is_direct_called(__FILE__) and async_call()) {
	wh_remote_call();
}
