<?php

/******* 周期性cron调用 **********
编辑/etc/crontab，添加：
*  *    * * *	www-data /usr/bin/php -q /srv/http/json-db/admin/crontab.php > /dev/null 2>&2
这样就会每分钟调用本脚本一次，is_cron_calling函数是用来检查来自系统cron的调用
*/
if (is_cron_calling()) {
	$status_changed = false;
	$status = json_decode(file_get_contents(cron_status_file()), true);
	if (empty($status)) {
		$status = [];
		$status_changed = true;
	}

	$jobs = cron_jobs();
	foreach($jobs as $job) {
		if (!array_key_exists('key', $job)) {
			continue;
		}
		$key = $job['key'];
		$url = $job['url'];
		$minutes = $job['minutes'];

		if (!array_key_exists($key, $status)) {
			$new_status = [];
			$new_status['url'] = $url;
			$new_status['minutes'] = $minutes;
			$new_status['last_check_time'] = 0;
			$status[$key] = $new_status;
		}

		$status_item = &$status[$key];
		$last_time = $status_item['last_check_time'];
		$pass_minutes = intval((time() - $last_time)/60);

		if ($pass_minutes >= $minutes) {
			if (run_cron_job($job)) {
				$status_item['last_check_time'] = time();
				$status_changed = true;
			}
		}
	}

	if ($status_changed) {
		file_put_contents(cron_status_file(), json_encode($status));
	}
	exit();
}

/******** cron_jobs_update函数 *********
如果有表格schema变化，就应该调用一下cron_jobs_update函数，触发更新
*/
function cron_jobs_update()
{
	file_put_contents(cron_cached_file(), json_encode(cron_jobs_get()));
}

/************************************
	功能函数
************************************/

define('CRONTAB_USER_AGENT', 'CRONTAB_TRIGGER');

function run_cron_job($job)
{
	cron_async_curl($job['url']);
	return true;
}

function cron_async_curl($url)
{
	$curl_opt = array(
		CURLOPT_URL => $url,
		CURLOPT_HTTPHEADER => array(
			'User-Agent: '.CRONTAB_USER_AGENT,
		),
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_NOSIGNAL => 1,
		CURLOPT_CONNECTTIMEOUT_MS => 3000,
		CURLOPT_TIMEOUT_MS =>  1,
	);

	$ch = curl_init();
	curl_setopt_array($ch, $curl_opt);
	curl_exec($ch);
	curl_close($ch);
}

function is_cron_calling()
{
	return (getenv('DOCUMENT_ROOT')===false and getenv('SERVER_SOFTWARE')===false);
}

function cron_jobs()
{
	return json_decode(file_get_contents(cron_cached_file()), true);
}

function cron_jobs_get()
{
	$result = [];
	$db_root = realpath(__DIR__.'/../databases');
	$saved_urls = [];
	foreach(glob("{$db_root}/*/*/*/schema.json") as $file) {
		if (!preg_match('~/([^\/]+)/([^\/]+)/([^\/]+)/schema\.json$~',$file, $matches)){
			continue;
		}

		$data_str = file_get_contents($file);
		$schema = json_decode($data_str, true);
		$timers = @$schema['timers'];
		if (empty($timers)) {
			continue;
		}


		foreach($timers as $timer) {
			$url = $timer['url'];
			if (in_array($url, $saved_urls)) {
				continue;
			}
			$saved_urls[] = $url;
			$timer['key'] = md5($url);
			$timer['domain'] = $matches[1];
			$timer['db_name'] = $matches[2];
			$timer['table_name'] = $matches[3];
			$result[] = $timer;
		}
	}
	return $result;
}

function cron_status_file()
{
	return __DIR__.'/uploads/cache/crontab.status';
}

function cron_cached_file()
{
	return __DIR__.'/uploads/cache/crontab.cache';
}

function logger($object)
{
	file_put_contents(__DIR__.'/uploads/cache/crontab.log', json_encode($object)."\r\n", FILE_APPEND);
}


