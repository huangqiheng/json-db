<?php
require_once '../../admin/functions.php';

define('CACHE_PATH', 'cache/msg');
define('TIME_INTERVAL', 30);
define('MAX_DEQUEUE_COUNT', 1000);
define('ITEMS_SUFFIX', 'bucket');

if (is_direct_call()) {
	$req = get_param();
	list($name) = null_exit($req,'name');
	$items = @$req['items'];

	if (!empty($items)) {
		if (enqueue_items($name, $items)) {
			jsonp_nocache_exit(['status'=>'ok']);
		} else {
			jsonp_nocache_exit(['status'=>'error', 'error'=>'enqueue to file error']);
		}
	} else {
		if ($items = dequeue_items($name, @$req['max'])) {
			jsonp_nocache_exit(['status'=>'ok', 'count'=>count($items), 'items'=>$items]);
		} else {
			if (file_exists(current_queuing_file($name))) {
				$time_to_wait = TIME_INTERVAL - intval(time()) % TIME_INTERVAL;
				jsonp_nocache_exit(['status'=>'error', 'error'=>'wait for queue item', 'wait_time'=>$time_to_wait]);
			} else {
				jsonp_nocache_exit(['status'=>'error', 'error'=>'empty queue']);
			}
		}
	}
}

function enqueue_items($name, $items)
{
	$file_to_write= current_queuing_file($name, true);
	$data_to_write = '';

	if (is_array($items)) {
		foreach ($items as $item) {
			$item_text = json_encode($item);
			$data_to_write .= $item_text.PHP_EOL;
		}
	} else {
		$data_to_write = json_encode($items).PHP_EOL;
	}
	return file_put_contents($file_to_write, $data_to_write, FILE_APPEND | LOCK_EX);
}

function dequeue_items($name, $max)
{
	if (empty($max)) {
		$max = MAX_DEQUEUE_COUNT;
	}
		
	$now_writing_id = now_name();

	$item_ids = [];
	foreach(glob(get_queue_file($name, '*')) as $file) {
		if (is_dir($file)) {continue;}
		if (!preg_match('~/(\d+)\.'.ITEMS_SUFFIX.'$~',$file, $matches)){continue;}
		$item_id = intval($matches[1]);
		if ($item_id < $now_writing_id) {
			$item_ids[] = $item_id;
		}
	}

	sort($item_ids, SORT_NUMERIC);

	$output_items = [];
	foreach ($item_ids as $id) {
		$file_name = get_queue_file($name, $id);
		$data_str = file_get_contents($file_name);
		$data_arr = explode(PHP_EOL, $data_str);
		while($item_str = array_shift($data_arr)) {
			if ($item_str === '') {
				continue;
			}

			$item = json_decode($item_str, true);
			if (empty($item)) {
				continue;
			}

			$output_items[] = $item;
			if (count($output_items) >= $max) {
				if (!empty($data_arr)) {
					$resave_str = implode(PHP_EOL, $data_arr);
					file_put_contents($file_name, $resave_str);
					break 2;
				}
			}
		}
		unlink($file_name);
	}

	$que_dir = get_queue_dir($name);
	if (is_dir_empty($que_dir)) {
		rmdir($que_dir);
	}

	return $output_items;
}

function is_dir_empty($dir) 
{
	if (!is_readable($dir)) return NULL; 
	$handle = opendir($dir);
	while (false !== ($entry = readdir($handle))) {
		if ($entry != "." && $entry != "..") {
			return FALSE;
		}
	}
	return TRUE;
}

function get_queue_file($que_name, $id)
{
	return get_queue_dir($que_name).'/'.$id.'.'.ITEMS_SUFFIX;
}

function get_queue_dir($que_name)
{
	return __DIR__.'/'.CACHE_PATH.'/'.md5($que_name);
}

function now_name()
{
	return intval(time() / TIME_INTERVAL);
}

function current_queuing_file($name, $force_file=false)
{
	$file = now_name().'.'.ITEMS_SUFFIX;
	$dir = get_queue_dir($name);
	$full_name = $dir.'/'.$file;

	if ($force_file) {
		if (!file_exists($full_name)) {
			if (!file_exists($dir)) {
				mkdir($dir);
			}
			touch($full_name);
		}
	}

	return $full_name;
}

function is_direct_call()
{
	return ($_SERVER['SCRIPT_FILENAME'] === __FILE__);
}


