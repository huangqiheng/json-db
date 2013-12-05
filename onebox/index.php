<?php

require_once 'nokogiri.php';

$req_url = @$_GET['url'];
$res_type = @$_GET['type'];

if (!empty($req_url)) {
	process_onebox_result($req_url, $res_type);
}

function process_onebox_result($req_url, $res_type) 
{
	do {
		if ($res_obj = process_itunes_url($req_url)) { break;}
		if ($res_obj = process_appgame_url($req_url)) { break;}
		if ($res_obj = process_bbs_appgame_url($req_url)) { break;}
		if ($res_type === 'json') {
			return  jsonp_nocache_exit(['status'=>'error', 'error'=>'no handler']);
		} else {
			return  html_nocache_exit($req_url);
		}
	} while(false);

	if ($res_type === 'json') {
		$res_obj['status'] = 'ok';
		return jsonp_nocache_exit($res_obj);
	}
	return html_nocache_exit(onebox_output($res_obj));
}

function process_bbs_appgame_url($req_url)
{
	preg_match('#^http://bbs\.appgame\.com/thread-[\d]+-[\d]+-[\d]+\.html$#us', $req_url, $matches);

	if ($matches == null) {
		return false;
	}

	$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36';
	$html = curl_get_content($req_url, $user_agent);
	if (empty($html)) {
		return false;
	}

	$regex_match = "#<div id=\"post_(\d+)\">#s";
	if (!preg_match($regex_match, $html, $match)) {
		return false;
	}
	$pid = $match[1];

	preg_match("#<title>([^<]*?)</title>#s", $html, $match);
	$title = $match[1];
	$title = preg_replace("#_[^_]+_任玩堂.*$#u", '', $title);

	$html= mb_convert_encoding($html, 'HTML-ENTITIES', mb_detect_encoding($html));
	$saw = new nokogiri($html);
	$target = $saw->get('td#postmessage_'.$pid);
	$dom = $target->getDom();
	$node = $dom->firstChild->childNodes->item(0); 
	$content = strip_tags(dom_to_html($node));
	$content = preg_replace("#[\s]+#us", '', $content);

        $avatar = $saw->get('table#pid'.$pid.' a.xw1');
	$dom = $avatar->getDom();
	$node = $dom->firstChild->childNodes->item(0); 
	$user_url = 'http://bbs.appgame.com/'.$node->getAttribute('href');

	$html = curl_get_content($user_url, $user_agent);
	if (empty($html)) {
		return false;
	}

	$html= mb_convert_encoding($html, 'HTML-ENTITIES', mb_detect_encoding($html));
	$saw = new nokogiri($html);
	$target = $saw->get('div.hm img');
	$dom = $target->getDom();
	$node = $dom->firstChild->childNodes->item(0); 
	$detail_url = $node->getAttribute('src');


	$res = array();
	$res['provider_name'] = '任玩堂论坛';
	$res['provider_url'] = 'http://bbs.appgame.com/';
	$res['favicon_url'] = 'http://www.appgame.com/favicon.ico';
	$res['ori_url'] = $req_url;
	$res['title'] = $title;
	$res['image'] = $detail_url;
	$res['description'] = $content;
	return $res;
}

function pr($var) { print '<pre>'; print_r($var); print '</pre>'; }

function dom_to_html($node)
{
	$doc = new DOMDocument();
	$doc->appendChild($doc->importNode($node,true));
	return mb_convert_encoding($doc->saveHTML(),'UTF-8','HTML-ENTITIES');
}

function process_appgame_url($req_url)
{
	preg_match('#(^http://([a-zA-Z0-9\-]+\.)*appgame\.com/)([\S]+/)?[\d]+\.html$#i', $req_url, $matches);

	if ($matches == null) {
		return false;
	}

	$api_prefix = @$matches[1];

	if (empty($api_prefix)) {
		return false;
	}

	//任玩堂的oEmbed的api格式
	$api_regex = "%s?oembed=true&format=json&url=%s";
	$api_url = sprintf($api_regex, $api_prefix, $req_url);

	$res = curl_get_content($api_url);

	if (empty($res)) {
		return false;
	}

	preg_match("#{\".*\"}#ui", $res, $mm);
	$res_body = $mm[0];

	if (empty($res_body)) {
		return false;
	}

	$res_obj = json_decode($res_body, true);

	$res = array();
	$res['provider_name'] = '任玩堂';
	$res['provider_url'] = 'http://www.appgame.com/';
	$res['favicon_url'] = 'http://www.appgame.com/favicon.ico';
	$res['ori_url'] = $req_url;
	$res['title'] = $res_obj['title'];
	$res['image'] = $res_obj['thumbnail_url'];
	$res['description'] = $res_obj['html'];

	return $res;
}

function curl_get_content($url, $user_agent=null)
{
	$headers = array(
		"Accept: application/json",
		"Accept-Encoding: deflate,sdch",
		"Accept-Charset: utf-8;q=1"
		);

	if ($user_agent) {
		$headers[] = $user_agent;
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
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

	if (($err) || ($httpcode !== 200)) {
		return null;
	}

	return $res;
}

function process_itunes_url($req_url) 
{
	//https://itunes.apple.com/cn/app/mysterious-cities-gold-secret/id739095583?mt=8&uo=4
	preg_match('|^https?://itunes.apple.com/(\S*)/app\S*/id(\d+)(\?mt\=\d+){0,1}.*$|i', $req_url, $matches);

	if ($matches == null || $matches[0] == null) {
		return false;
	}

	$country = @$matches[1];
	$appid = $matches[2];

	if ($country == null || $country == "") {
		$country = "us";
	} else {
		$country = substr($country, 0, 2);
	}

	$res_itunes = file_get_contents("http://ax.itunes.apple.com/WebObjects/MZStoreServices.woa/wa/wsLookup?id=" . $appid . "&country=" . $country);
	if (($res_itunes == null) || (trim($res_itunes) == '')) {
		return false;
	}

	$obj = json_decode($res_itunes, true);
	$res_obj = $obj['results'][0];

	$app_logo = substr($res_obj['artworkUrl512'],0,-4).'.175x175-75.jpg';
	$app_logo = str_replace('.512x512-75','',$app_logo);

	$res = array();
	$res['provider_name'] = 'Apple itunes';
	$res['provider_url'] = 'http://itunes.appgame.com/';
	$res['favicon_url'] = 'http://www.apple.com/favicon.ico';
	$res['ori_url'] = $res_obj['trackViewUrl'];
	$res['title'] = $res_obj['trackName'];
	$res['image'] = $app_logo;
	$res['description'] = $res_obj['description'];

	return $res;
}

function onebox_output($res)
{
	$html  = "<div class=\"onebox-result\">";
	$html .=   "<div class=\"source\">";
	$html .=     "<div class=\"info\">";
	$html .=       "<a href=\"{$res['provider_url']}\" target=\"_blank\">";
	$html .=         "<img class=\"favicon\" src={$res['favicon_url']}>{$res['provider_name']}";
	$html .=       "</a>";
	$html .=     "</div>";
	$html .=   "</div>";
	$html .=   "<div class=\"onebox-result-body\">"; if ($res['image']!='') {
	$html .=     "<a href={$res['ori_url']} target=\"_blank\"><img src={$res['image']} class=\"thumbnail\"></a>";}
	$html .=     "<a href={$res['ori_url']} target=\"_blank\" class=\"onebox-title\">{$res['title']}</a>";
	$html .=     "<div>{$res['description']}</div>";
	$html .=   "</div>";
	$html .=   "<div class=\"clearfix\"></div>";
	$html .= "</div>";
	return $html;
}

function html_nocache_exit($output)
{
	set_nocache();
	header('Access-Control-Allow-Origin: *');  
	header('Content-Type: text/html; charset=utf-8');
	echo $output;
	exit();
}

function html_cache_exit($output, $age_val=300)
{
	set_cache_age($age_val);
	header('Access-Control-Allow-Origin: *');  
	header('Content-Type: text/html; charset=utf-8');
	echo $output;
	exit();
}

function jsonp_nocache_exit($output)
{
	set_nocache();
	echo jsonp($output);
	exit();
}

function jsonp_cache_exit($output, $age_val=300)
{
	set_cache_age($age_val);
	echo jsonp($output);
	exit();
}

function set_nocache()
{
	header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
	header("Pragma: no-cache"); //HTTP 1.0
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
}

function set_cache_age($age_val = 300)
{
	header('Cache-Control: public, must-revalidate, proxy-revalidate, max-age='.$age_val);
	header('Pragma: public');
	header('Last-Modified: '.gm_date(last_mtime()));
	header('Expires: '.gm_date(time()+$age_val));
}

function jsonp($data)
{
	header('Access-Control-Allow-Origin: *');  
	header('Content-Type: application/json; charset=utf-8');
	$json = json_encode($data);

	if(!isset($_GET['callback']))
		return $json;

	if(is_valid_jsonp_callback($_GET['callback']))
		return "{$_GET['callback']}($json)";

	return false;
}

function is_valid_jsonp_callback($subject)
{
	$identifier_syntax = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
	$reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
			'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 
			'for', 'switch', 'while', 'debugger', 'function', 'this', 'with', 
			'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 
			'extends', 'super', 'const', 'export', 'import', 'implements', 'let', 
			'private', 'public', 'yield', 'interface', 'package', 'protected', 
			'static', 'null', 'true', 'false');
	return preg_match($identifier_syntax, $subject)
		&& ! in_array(mb_strtolower($subject, 'UTF-8'), $reserved_words);
}

?>
