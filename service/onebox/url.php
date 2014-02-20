<?php
require_once 'functions.php';
require_once 'nokogiri.php';

$req_url = @$_GET['url'];
$res_type = @$_GET['type'];

if (!empty($req_url)) {
	process_onebox_result($req_url, $res_type);
}

function process_onebox_result($req_url, $res_type) 
{
	if ($res_obj = get_onebox_url($req_url)) {
		if ($res_type === 'json') {
			$res_obj['status'] = 'ok';
			return jsonp_nocache_exit($res_obj);
		}
		return html_nocache_exit(onebox_output($res_obj));
	} else {
		if ($res_type === 'json') {
			return  jsonp_nocache_exit(['status'=>'error', 'error'=>'no handler']);
		} else {
			return  html_nocache_exit($req_url);
		}
	}
}
