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

	$user_agent = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36';
	$html = curl_get_content($req_url, $user_agent);
	if (empty($html)) {
		return false;
	}

	$regex_match = "#<div id=\"post_(\d+)\">#s";
	if (!preg_match($regex_match, $html, $match)) {
		return false;
	}
	$pid = $match[1];

	preg_match('#发表于[^<]*?<span title="([^"]*?)">.*?</span>#s', $html, $match);
	$time_str = $match[1];

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

	preg_match('#showauthor\(this, \'userinfo'.$pid.'\'.*?<img .*?src="([^"]+?)"#s', $html, $match);
	$user_pic = $match[1];
	$user_img = get_redirect_url($user_pic);

	$res = array();
	$res['provider_name'] = '任玩堂论坛';
	$res['provider_url'] = 'http://bbs.appgame.com/';
	$res['favicon_url'] = 'http://www.appgame.com/favicon.ico';
	$res['ori_url'] = $req_url;
	$res['title'] = $title;
	$res['image'] = $user_img;
	$res['ID'] = intval($pid);
	$res['description'] = $content;
	$res['update_time'] = format_time($time_str);
	return $res;
}

function get_redirect_url($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Must be set to true so that PHP follows any "Location:" header
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$a = curl_exec($ch); // $a will contain all headers
	$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // This is what you need, it will return you the last effective URL
	return $url;
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
	preg_match('#^http://([a-zA-Z0-9\-]+\.)*appgame\.com/([\S]+/)?[\d]+\.html$#i', $req_url, $matches);

	if ($matches == null) {
		return false;
	}

	$res = curl_get_content($req_url.'?json=1');

	if (empty($res)) {
		return false;
	}

	preg_match("#{\".*\"}#ui", $res, $mm);
	$res_body = $mm[0];

	if (empty($res_body)) {
		return false;
	}

	$res_obj = json_decode($res_body, true);


	if ($res_obj['status'] !== 'ok') {
		return false;
	}


	$res = array();
	$res['provider_name'] = '任玩堂';
	$res['provider_url'] = 'http://www.appgame.com/';
	$res['favicon_url'] = 'http://www.appgame.com/favicon.ico';
	$res['ori_url'] = $req_url;
	$res['title'] = $res_obj['post']['title'];
	$res['image'] = $res_obj['post']['thumbnail'];
	$res['ID'] = $res_obj['post']['id'];
	$res['update_time'] = format_time($res_obj['post']['modified']);
	$res['description'] = strip_tags($res_obj['post']['excerpt']);
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
	$res['update_time'] = format_time($res_obj['releaseDate']);
	$res['image'] = $app_logo;
	$res['ID'] = intval($appid);
	$res['description'] = $res_obj['description'];

	return $res;
}

?>
