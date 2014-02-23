<?php
require_once 'functions.php';
denies_with_json();

$req = get_param();
list($check_what) = null_exit($req, 'what');

if ($check_what === 'logos') {
	$outpu_logos = [];
	$logo_dir = __DIR__.'/uploads/logo';
	foreach (glob("{$logo_dir}/*") as $file) {
		$file_uri = substr($file, strlen($_SERVER['DOCUMENT_ROOT']));
		$outpu_logos[] = $file_uri;
	}
	jsonp_nocache_exit(['status'=>'ok', 'count'=>count($outpu_logos), 'items'=>$outpu_logos]);
}

jsonp_nocache_exit(['status'=>'error', 'error'=>'command error']);
