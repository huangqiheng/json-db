<?php
require_once '../../admin/functions.php';

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

function get_onebox_url($req_url)
{
	if ($res_obj = process_itunes_url($req_url)) {return $res_obj;}
	if ($res_obj = process_appgame_url($req_url)) {return $res_obj;}
	if ($res_obj = process_bbs_appgame_url($req_url)) {return $res_obj;}
	if ($res_obj = process_jsondb_url($req_url)) {return $res_obj;}
	return false;
}

function process_jsondb_url($req_url)
{
	//http://db.appgame.com/databases/db_name/table_name/12345678.json
	preg_match('#^((https?://[\S]+)/databases/[\S]+/[\S]+/)[\d]+\.json$#i', $req_url, $matches);

	if ($matches == null) {
		return false;
	}

	$data_url = $matches[0];
	$schema_url = $matches[1].'schema.json';
	$http_prefix = $matches[2];

	$schema = object_read($schema_url);
	$data = object_read($data_url);

	if (empty($schema) || empty($data)) {
		return false;
	}

	list($ob_title, $ob_desc, $ob_image) = get_onebox_data($schema, $data);

	if (@$ob_image[0] === '/') {
		$ob_image = $http_prefix.$ob_image;
	}

	$res_obj = merge_fields($data);
	$utime = format_time(@$res_obj['TIME']);
	$ctime = @$res_obj['CREATE'];
	if (empty($ctime)) {
		$ctime = $utime;
	} else {
		$ctime = format_time($ctime);
	}

	$res = array();
	$res['onebox'] = 'jsondb';
	$res['provider_name'] = '任玩堂';
	$res['provider_url'] = 'http://www.appgame.com/';
	$res['favicon_url'] = 'http://www.appgame.com/favicon.ico';
	$res['ori_url'] = $data_url;
	$res['title'] = $ob_title;
	$res['image'] = $ob_image;
	$res['ID'] = intval($res_obj['ID']);
	$res['update_time'] = $utime;
	$res['create_time'] = $ctime;
	$res['description'] = strip_tags(trim($ob_desc));
	return $res;
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
	$res['onebox'] = 'appgame-bbs';
	$res['provider_name'] = '任玩堂论坛';
	$res['provider_url'] = 'http://bbs.appgame.com/';
	$res['favicon_url'] = 'http://www.appgame.com/favicon.ico';
	$res['ori_url'] = $req_url;
	$res['title'] = $title;
	$res['image'] = $user_img;
	$res['ID'] = intval($pid);
	$res['description'] = trim($content);
	$res['update_time'] = format_time($time_str);
	$res['create_time'] = $res['update_time'];
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

	$res_obj = get_remote_json($req_url.'?json=1');
	if ($res_obj === false) {
		return false;
	}

	$res = array();
	$res['onebox'] = 'appgame-cms';
	$res['provider_name'] = '任玩堂';
	$res['provider_url'] = 'http://www.appgame.com/';
	$res['favicon_url'] = 'http://www.appgame.com/favicon.ico';
	$res['ori_url'] = $req_url;
	$res['title'] = $res_obj['post']['title'];
	$res['image'] = $res_obj['post']['thumbnail'];
	$res['ID'] = $res_obj['post']['id'];
	$res['update_time'] = format_time($res_obj['post']['modified']);
	$res['create_time'] = format_time($res_obj['post']['date']);
	$res['description'] = strip_tags(trim($res_obj['post']['excerpt']));
	return $res;
}

function get_itunes($appid, $country)
{
	$itunes_cache = 'http://www.appgame.com/itunes_js.php?id='.$appid.'&country='.$country.'&cache=true';
	$itunes_url = 'http://ax.itunes.apple.com/WebObjects/MZStoreServices.woa/wa/wsLookup?id=' . $appid . '&country=' . $country;

	$res_itunes = object_read_url($itunes_cache, 10, 10);
	if (empty($res_itunes)) {
		$res_itunes = object_read_url($itunes_cache, 10, 10);
		if (empty($res_itunes)) {
			return false;
		}
	}

	if ($res_itunes['resultCount'] === 0){
		return false;
	}

	$res_obj = @$res_itunes['results'][0];
	if (empty($res_obj)) {
		return false;
	}

	$app_logo = substr($res_obj['artworkUrl512'],0,-4).'.175x175-75.jpg';
	$app_logo = str_replace('.512x512-75','',$app_logo);

	$res = array();
	$res['onebox'] = 'itunes';
	$res['provider_name'] = 'Apple itunes';
	$res['provider_url'] = 'http://itunes.apple.com/';
	$res['favicon_url'] = 'http://www.apple.com/favicon.ico';
	$res['ori_url'] = $res_obj['trackViewUrl'];
	$res['title'] = $res_obj['trackName'];
	$res['update_time'] = format_time($res_obj['releaseDate']);
	$res['create_time'] = $res['update_time'];
	$res['image'] = $app_logo;
	$res['ID'] = intval($appid);
	$res['description'] = trim($res_obj['description']);
	$res['data'] = array(
		'id' => $appid,
		'country' => $country,
		'src_url' => $itunes_url,
		'cache_url' => $itunes_cache,
		'kind' => $res_obj['kind'],
		'trackName' => $res_obj['trackName'],
		'artworkUrl512' => @$res_obj['artworkUrl512'],
		'trackViewUrl' => $res_obj['trackViewUrl'],
		'fileSizeBytes' => intval($res_obj['fileSizeBytes']),
		'currency' => $res_obj['currency'],
		'formattedPrice' => @$res_obj['formattedPrice'],
		'screenshotUrls' => @$res_obj['screenshotUrls'],
		'ipadScreenshotUrls' => @$res_obj['ipadScreenshotUrls'],
		'bundleId' => @$res_obj['bundleId'],
		'releaseDate' => format_time($res_obj['releaseDate']),
		'supportedDevices' =>$res_obj['supportedDevices'],
	);

	return $res;
}

function process_itunes_url($req_url) 
{
	//https://itunes.apple.com/cn/app/mysterious-cities-gold-secret/id739095583?mt=8&uo=4
	//http://ax.itunes.apple.com/WebObjects/MZStoreServices.woa/wa/wsLookup?id=739095583&country=cn
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

	return get_itunes($appid, $country);
}
