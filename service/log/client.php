<?php

$log_db_name = null;
$log_table_name = null;
$log_apikey = null;
$log_ident = null;
$log_facility = null;

$log_domain = 'log.hqh.me';
$log_host = '120.31.130.152';
$log_port = '80';

function jsondb_logger_init($db_name, $table_name, $apikey, $ident, $facility)
{
	global $log_db_name, $log_table_name, $log_apikey, $log_ident, $log_facility;
	$log_db_name = $db_name;
	$log_table_name = $table_name;
	$log_apikey = $apikey;
	$log_ident = $ident;
	$log_facility = $facility;
}

function jsondb_logger_server($domain, $host, $port)
{
	global $log_host, $log_domain, $log_port;
	$log_host = $host;
	$log_domain = $domain;
	$log_port = $port;
}

function jsondb_logger($priority, $message)
{
	global $log_db_name, $log_table_name, $log_apikey, $log_ident, $log_facility;
	global $log_host, $log_domain, $log_port;

	$log_url = 'http://'.$log_host.':'.$log_port.'/service/log/write.php';
        $headers = ['Content-Type: multipart/form-data; charset=utf-8'];
	$headers[] = 'Host: '.$log_domain;

	$data = [
		'db_name' => $log_db_name,
		'table_name' => $log_table_name,
		'apikey' => $log_apikey,
		'data' => [
			'general' => [
				'ID' => '',
				'TIME' => gm_date(time())
			],
			'log' => [
				'ident' => $log_ident,
				'facility' => $log_facility,
				'priority'=> $priority,
				'message' => $message
			]
		]
	];

	http_build_query_for_curl($data, $post_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $log_url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);

        $res = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_errno($ch);
        curl_close($ch);

	return ($err==0)? (($httpcode==200)? $res : null) : null;
}

function gm_date($time)
{
        return gmdate('D, d M Y H:i:s \G\M\T', $time);
}

function http_build_query_for_curl( $arrays, &$new = array(), $prefix = null ) {

	if ( is_object( $arrays ) ) {
		$arrays = get_object_vars( $arrays );
	}

	foreach ( $arrays AS $key => $value ) {
		$k = isset( $prefix ) ? $prefix . '[' . $key . ']' : $key;
		if ( is_array( $value ) OR is_object( $value )  ) {
			http_build_query_for_curl( $value, $new, $k );
		} else {
			$new[$k] = $value;
		}
	}
}

