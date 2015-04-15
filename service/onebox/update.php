<?php
require_once 'functions.php';
header( 'Content-type: text/html; charset=utf-8' );

$req = get_param();
list($db_name,$table_name,$apikey) = null_exit($req,'db_name','table_name','apikey');
api_exit($db_name, $table_name, $apikey);

$table_path = table_root($db_name, $table_name);
$schema = object_read("{$table_path}/schema.json");
$fields = $schema['fields'];

$onebox_url_routes = array();
foreach($fields as $group_name=>$group_data) {
	foreach($group_data as $field_name=>$field_type) {
		if (preg_match('/^jqxListBox-onebox/i', $field_type)) {
			$onebox_url_routes[] = [$group_name, $field_name];
		}
	}
}

$counter_changed = 0;
$counter_constant = 0;
$counter_all = 0;

foreach(glob("{$table_path}/*.json") as $file) {
	if (is_dir($file)) {continue;}
	if (!preg_match('~/(\d+)\.json$~',$file, $matches)){continue;}
	$item_id = $matches[1];

	$data_obj = object_read($file);
	if (empty($data_obj)) {continue;}

	$counter_file_changed = 0;
	$counter_all++;

	echo '<h2>'.$file.'</h2>';

	foreach($onebox_url_routes as $route) {
		$oneboxs = @$data_obj[$route[0]][$route[1]];
		if (empty($oneboxs)) {
			continue;
		}
		$new_oneboxs = array();
		foreach($oneboxs as $onebox) {
			$url = $onebox['url'];
			if (empty($url)) continue;

			$res_obj = get_onebox_url($url);

			if (!isset($onebox['time'])) {
				$onebox['time'] = gm_date(time());
			}

			$ctime_str = @$onebox['ctime'];
			if (empty($ctime_str)) {
				$ctime_str = $onebox['time'];
			}

			$new_ctime = __strtotime($res_obj['create_time']);
			$ori_ctime = __strtotime($ctime_str);
			$ctime = gm_date(min($new_ctime, $ori_ctime));

			$new_onebox = array();
			$new_onebox['title'] = $res_obj['title'];
			$new_onebox['desc'] = $res_obj['description'];
			$new_onebox['image'] = $res_obj['image'];
			$new_onebox['url'] = $res_obj['ori_url'];
			$new_onebox['id'] = $res_obj['ID'];
			$new_onebox['time'] = $res_obj['update_time'];
			$new_onebox['ctime'] = $ctime;
			$new_oneboxs[] = $new_onebox;

			$same_onebox = true;
			foreach($new_onebox as $key=>$val) {
				if ($val !== @$onebox[$key]) {
					$same_onebox = false;
					break;
				}
			}

			if ($same_onebox) {
				$counter_constant++;
				echo '<span>'.$url.' ok.</span><br>';
			} else {
				$counter_changed++;
				$counter_file_changed++;
				echo '<span>'.$url.'  updated!</span><br>';
			}
		}

		$data_obj[$route[0]][$route[1]] = $new_oneboxs;
	}


	if ($counter_file_changed > 0) {
		echo '<span>save '.$file.'</span><br>';
		object_save($file, $data_obj);
	}
}

echo '<span>total: '.($counter_all).' (constant: '.$counter_constant.'/ changed: '.$counter_changed.')</span>';

