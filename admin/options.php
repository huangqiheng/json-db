<?php
require_once 'functions.php';
denies_with_json();

$req = get_param();
list($check_what) = null_exit($req, 'what');

if ($check_what === 'logos') {
	$outpu_logos = array();
	$logo_dir = __DIR__.'/uploads/logo';
	foreach (glob("{$logo_dir}/*") as $file) {
		$file_uri = substr($file, strlen($_SERVER['DOCUMENT_ROOT']));
		$outpu_logos[] = $file_uri;
	}
	jsonp_nocache_exit(array('status'=>'ok', 'count'=>count($outpu_logos), 'items'=>$outpu_logos));
}

if ($check_what === 'values') {
	list($db, $table, $field) = null_exit($req, 'db', 'table', 'field');
	$excepts = isset($req['except'])? $req['except'] : array();
	$filters = isset($req['filters'])? $req['filters'] : array();
	$table_root = table_root($db, $table);

	$res = array();
	$push_val = function($value)use(&$res){
		if (!in_array($value, $res)) {
			$res[] = $value;
		}
	};

	foreach (glob("{$table_root}/*") as $file) {
		if (is_dir($file)) {continue;}
		if (!preg_match('~/(\d+)\.json$~',$file, $matches)){continue;}

		$data_obj = merge_fields(object_read($file));
		if (empty($data_obj)) {continue;}

		$values = isset($data_obj[$field])? $data_obj[$field] : null;
		if (empty($values)) {continue;}

		if (count(array_intersect($excepts, $values))) {
			continue;
		}

		if (count($filters)) {
			$filter_source = '';
			array_walk_recursive($data_obj, function($item, $key)use(&$filter_source){
				$filter_source .= ' | '.$item;
			});

			$is_matched_all = true;
			foreach($filters as $filter) {
				if (mb_strpos($filter_source, $filter) === false) {
					$is_matched_all = false;

					break;
				}
			}
			if (!$is_matched_all) {
				continue;
			}
		}

		if (is_array($values)) {
			foreach($values as $value) {
				call_user_func($push_val, $value);
			}
		} else {
			call_user_func($push_val, $value);
		}
	}

	jsonp_nocache_exit(array('status'=>'ok', 'count'=>count($res), 'items'=>$res));
}

jsonp_nocache_exit(array('status'=>'error', 'error'=>'command error'));
