<?php
require_once '../../admin/functions.php';

function curl_download($url, $local)
{
	set_time_limit(0);
	$fp = fopen ($local, 'w+');//This is the file where we save the    information
	$ch = curl_init(str_replace(" ","%20",$url));//Here is the file we are downloading, replace spaces with %20
	curl_setopt($ch, CURLOPT_TIMEOUT, 60*5);
	curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$success = curl_exec($ch); // get curl response
	curl_close($ch);
	fclose($fp);

	return $success;
}

/**
* Copy remote file over HTTP one small chunk at a time.
*
* @param $infile The full URL to the remote file
* @param $outfile The path where to save the file
*/
function copyfile_chunked($infile, $outfile) 
{
	$chunksize = 10 * (1024 * 1024); // 10 Megs

	/**
	 * parse_url breaks a part a URL into it's parts, i.e. host, path,
	 * query string, etc.
	 */
	$parts = parse_url($infile);
	$i_handle = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
	$o_handle = fopen($outfile, 'wb');

	if ($i_handle == false || $o_handle == false) {
		return false;
	}

	if (!empty($parts['query'])) {
		$parts['path'] .= '?' . $parts['query'];
	}

	/**
	 * Send the request to the server for the file
	 */
	$request = "GET {$parts['path']} HTTP/1.1\r\n";
	$request .= "Host: {$parts['host']}\r\n";
	$request .= "User-Agent: Mozilla/5.0\r\n";
	$request .= "Keep-Alive: 115\r\n";
	$request .= "Connection: keep-alive\r\n\r\n";
	fwrite($i_handle, $request);

	/**
	 * Now read the headers from the remote server. We'll need
	 * to get the content length.
	 */
	$headers = array();
	while(!feof($i_handle)) {
		$line = fgets($i_handle);
		if ($line == "\r\n") break;
		$headers[] = $line;
	}

	/**
	 * Look for the Content-Length header, and get the size
	 * of the remote file.
	 */
	$length = 0;
	foreach($headers as $header) {
		if (stripos($header, 'Content-Length:') === 0) {
			$length = (int)str_replace('Content-Length: ', '', $header);
			break;
		}
	}

	/**
	 * Start reading in the remote file, and writing it to the
	 * local file one chunk at a time.
	 */
	$cnt = 0;
	while(!feof($i_handle)) {
		$buf = '';
		$buf = fread($i_handle, $chunksize);
		$bytes = fwrite($o_handle, $buf);
		if ($bytes == false) {
			return false;
		}
		$cnt += $bytes;

		/**
		 * We're done reading when we've reached the conent length
		 */
		if ($cnt >= $length) break;
	}

	fclose($i_handle);
	fclose($o_handle);
	return $cnt;
}

function call_async($script_path, $data=null, $ua='ME_USERAGENT')
{
	$curl_opt = array(
		CURLOPT_URL => 'http://127.0.0.1:'.$_SERVER['SERVER_PORT'].$script_path,
		CURLOPT_HTTPHEADER => array(
			'Host: '.$_SERVER['HTTP_HOST'],
			'User-Agent: '.$ua,
		),
		CURLOPT_PORT => $_SERVER['SERVER_PORT'], 
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_NOSIGNAL => 1,
		CURLOPT_CONNECTTIMEOUT_MS => 3000,
		CURLOPT_TIMEOUT_MS =>  1,
	);

	if ($data) {
		$curl_opt[CURLOPT_POST] = 1;
		$curl_opt[CURLOPT_POSTFIELDS] = http_build_query($data);
	}

	$ch = curl_init();
	curl_setopt_array($ch, $curl_opt);
	curl_exec($ch);
	curl_close($ch);
}


function getdata_exit($db_name, $table_name, $name)
{
	$map_key = $name;
	$table_root = table_root($db_name, $table_name);
	$mapper = object_read("{$table_root}/mapper.json");
	if (empty($mapper)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'mapper file not found']);
	}

	$map_key= mapper_key($map_key);
	$map_val = @$mapper[$map_key];
	$map_file = "{$table_root}/{$map_val}.json";
	if (!file_exists($map_file)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'data file not found']);
	}

	$data = object_read($map_file);
	if (empty($data)) {
		jsonp_nocache_exit(['status'=>'error', 'error'=>'data file is empty']);
	}

	items_exit($data);

	return $data;
}

function get_listview_column($db_name, $table_name, $field_name)
{
	$table_root = table_root($db_name, $table_name);
	$schema = object_read("{$table_root}/schema.json");
	$listview_data = object_read("{$table_root}/listview.json");
	$id_index = array_search($field_name, $schema['listview']);

	$rep_list = array();
	foreach($listview_data as $subitem) {
		$id_cmp = $subitem[$id_index];
		if (empty($id_cmp)) {
			continue;
		}
		if (is_array($id_cmp)) {
			foreach($id_cmp as $item) {
				if (!in_array($item, $rep_list)) {
					$rep_list[] = $item;
				}
			}
		} else {
			if (!in_array($id_cmp, $rep_list)) {
				$rep_list[] = $id_cmp;
			}
		}
	}
	return $rep_list;
}

function get_img_data($url)
{
	if (empty($url)) {
		return false;
	}

	$md5 = md5($url);
	$cache_file = __DIR__.'/cache/'.$md5.'.img';
	if (file_exists($cache_file)) {
		return file_get_contents($cache_file);
	}

	$res = curl_get_content($url, null, 7, 30);
	if (empty($res)) {
		return false;
	}

	$img_data = 'data:image/'.image_type($url).';base64,'.base64_encode($res);
	file_put_contents($cache_file, $img_data);
	return $img_data;
}

function image_type($img_url)
{
	if (preg_match('~.+\.(jpg|png|gif)$~i', $img_url, $matchs)) {return $matchs[1];}
	return 'jpg';
}

