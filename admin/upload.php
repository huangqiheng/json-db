<?php
require_once 'functions.php';

null_exit(@$_GET['type']);

defined('UPLOAD_PATH') or define('UPLOAD_PATH', db_root(WWWROOT_DIR) . '/uploads');
defined('CONFIG_PATH') or define('CONFIG_PATH', db_root(GLOBAL_DIR));

$settings = array(
	'icon' => array(
		'list' => 'icon_libs.json',
		'max_file_size' => 1024000,
		'page_size' => 16*16,
		'thumb_x' => 32,
		'thumb_y' => 32,
		'prev_x' => 64,
		'prev_y' => 64,
		'types' => array('jpg', 'png', 'jpeg', 'gif')),
	'logo' => array(
		'list' => 'logo_libs.json',
		'max_file_size' => 2048000,
		'page_size' => 8*8,
		'thumb_x' => 64,
		'thumb_y' => 64,
		'prev_x' => 256,
		'prev_y' => 256,
		'types' => array('jpg', 'png', 'jpeg', 'gif')),
	'image' => array(
		'list' => 'image_libs.json',
		'max_file_size' => 4096000,
		'page_size' => 8*8,
		'thumb_x' => 128,
		'thumb_y' => 128,
		'prev_x' => 512,
		'prev_y' => 512,
		'types' => array('jpg', 'png', 'jpeg', 'gif')),
	'video' => array(
		'list' => 'video_libs.json',
		'max_file_size' => 0,
		'page_size' => 8*8,
		'types' => array()),
	'music' => array(
		'list' => 'music_libs.json',
		'max_file_size' => 0,
		'page_size' => 8*8,
		'types' => array()),
	'file' => array(
		'list' => 'file_libs.json',
		'max_file_size' => 0,
		'page_size' => 32,
		'types' => array())
);
$setting = $settings[$_GET['type']];
$is_image = in_array($_GET['type'], array('icon','logo','image'));
$cmd = isset($_GET['cmd'])? $_GET['cmd'] : 'read';

//这是读取上传库的列表长度
if($cmd === 'count') {
	$list_file = CONFIG_PATH .'/'. $setting['list'];
	$datas = async_read($list_file);
	jsonp_nocache_exit(count($datas));
}

//这是读取上传库的列表，全部读取，仅仅ID
if($cmd === 'list') {
	$startindex = isset($_GET['startindex'])? intval($_GET['startindex']) : null;
	$endindex = isset($_GET['endindex'])? intval($_GET['endindex']) : null;
	$pagesize = isset($_GET['pagesize'])? intval($_GET['pagesize']) : null;

	$list_file = CONFIG_PATH .'/'. $setting['list'];
	$datas = async_read($list_file);

	if (empty($endindex) or empty($pagesize)) {
		jsonp_nocache_exit($datas);
	}

	if (empty($startindex)) {
		$startindex = 0;
	}

	$count = count($datas);
	$index = $startindex * $pagesize;

	if (($index < 0) or ($index >= $count)) {
		jsonp_nocache_exit($datas);
	}

	$output_index = $startindex;
	$scan_index = $index;
	$outputs = array();
	do {
		$md5vals = array_slice($datas, $scan_index, $pagesize);
		$details = array();
		foreach($md5vals as $md5) {
			$details[] = object_read(UPLOAD_PATH."/{$md5}.json");
		}

		$outputs[$output_index] = $details;
		$scan_index += $pagesize;
		$output_index++;
	} while(($scan_index < $count) and ($output_index<$endindex));

	jsonp_nocache_exit($outputs);
}

//这是读取上传库的列表
if($cmd === 'read') {
	$order = isset($_GET['order'])? $_GET['order'] : 'desc';
	$req_page = isset($_GET['page'])? intval($_GET['page']) : 1;
	$page_size = $setting['page_size'];

	$list_file = CONFIG_PATH .'/'. $setting['list'];
	$list_data = async_read($list_file);

	$total = count($list_data);
	$max_pages = $total / $page_size + 1;
	$req_page = max($req_page, 1);
	$req_page = min($req_page, $max_pages);

	if ($order !== 'desc') {
		$list_data = array_reverse($list_data);
		$order = 'asc';
	}

	$res_list = array_slice($list_data, ($req_page-1)*$page_size, $page_size);

	$results = array();
	$md5_src = '';
	foreach($res_list as $uni_name) {
		$results[] = object_read(UPLOAD_PATH."/{$uni_name}.json");
		$md5_src .= $uni_name;
	}

	jsonp_nocache_exit(array('status'=>'ok', 
		'total' => $total,
		'order' => $order, 
		'page'  => $req_page,
		'pages' => $max_pages,
		'count' => count($results), 
		'digest' => md5($md5_src),
		'results'=>$results
	));
}


if($cmd !== 'write') {
	jsonp_nocache_exit(array('status'=>'error', 'error'=>'not valid cmd'));
}

//接下来是处理上传文件
$origin_dir = UPLOAD_PATH."/ori";
$preview_dir = UPLOAD_PATH."/prev";
$thumbnail_dir = UPLOAD_PATH."/thumb";

$uploaed_tmpname = @$_FILES["fileToUpload"]["tmp_name"];
$uploaed_name = @$_FILES["fileToUpload"]["name"];
$upload_size = @$_FILES["fileToUpload"]["size"];
$upload_type = pathinfo($uploaed_name,PATHINFO_EXTENSION);
$uni_name = md5_file($uploaed_tmpname);
$save_name = "{$uni_name}.{$upload_type}";
$save_json = "{$uni_name}.json";

//参数检查
if (one_null($uploaed_tmpname, $uploaed_name, $upload_size, $upload_type,$uni_name)) {
	exit('9000');
}

//文件大小限制
if ($setting['max_file_size'] > 0) {
	if ($upload_size > $setting['max_file_size']) {
		exit('9001');
	}
}

//只允许特定的文件扩展名
if (count($setting['types'])) {
	if(!in_array(strtolower($upload_type), $setting['types'])) {
		exit('9002');
	}
}

//检查图片文件大小，间接确认是否图片文件
if ($is_image) {
	if(getimagesize($uploaed_tmpname) === false) {
		exit('9003');
	}
}

//确保文件夹存在
force_directory(UPLOAD_PATH);
force_directory(CONFIG_PATH);
force_directory($origin_dir);
force_directory($thumbnail_dir);
force_directory($preview_dir);

//保存文件
$err_code = '9009';
do {
	$target_file = $origin_dir."/{$save_name}";
	if (file_exists($target_file)) {
		$err_code = '1001';
	} else {
		if (!move_uploaded_file($uploaed_tmpname, $target_file)) {
			$err_code = '9004';
			break;
		}
		$err_code = '1000';
	}

	if ($is_image) {
		$thumb_file = $thumbnail_dir."/{$save_name}";
		resize($target_file, $setting['thumb_x'], $setting['thumb_y'], $thumb_file);

		$preview_file = $preview_dir."/{$save_name}";
		resize($target_file, $setting['prev_x'], $setting['prev_y'], $preview_file);
	}

	$data = array(
		'md5' => $uni_name,
		'file_name' => $uploaed_name,
		'upload_size' => $upload_size,
		'ori' => base_path($target_file),
		'thumb' => base_path($thumb_file),
		'prev' => base_path($preview_file),
		'time' => time()
	);

	object_save(UPLOAD_PATH."/{$save_json}", $data);

	$list_file = CONFIG_PATH .'/'. $setting['list'];

	if (!file_exists($list_file)) {
		touch($list_file);
	}

	$fp = fopen($list_file, 'r+');    
	if(flock($fp , LOCK_EX)){    
		$stat = fstat($fp);
		$ori_size = $stat['size'];

		if ($ori_size === 0) {
			$lst_data = array();
		} else {
			fseek($fp, 0);
			$contents = fread($fp, $ori_size);
			$lst_data = json_decode($contents, true);
			if (empty($lst_data)) {
				$lst_data = array();
			}
		}

		//****************
		$pos = array_search($uni_name, $lst_data);

		if ($pos === FALSE) {
			array_unshift($lst_data, $uni_name);
		} else {
			unset($lst_data[$pos]);
			array_unshift($lst_data, $uni_name);
		}
		//****************

		$data_str = json_encode($lst_data);

		fseek($fp, 0);
		fwrite($fp, $data_str); 
		fflush($fp); 
		ftruncate($fp, ftell($fp)); 

		flock($fp , LOCK_UN);    
	}  
	fclose($fp);


	exit("{$err_code} {$data['ori']}");
} while(false);


//其他原因的失败
exit($err_code);


function async_read($list_file)
{
	$fp = @fopen($list_file, 'r');    
	if ($fp) {
		if(flock($fp , LOCK_SH)){    
			$stat = fstat($fp);
			if ($stat['size'] > 0) {
				$contents = fread($fp, $stat['size']);
				$list_data = json_decode($contents, true);
			} else {
				$list_data = array();
			}
			flock($fp , LOCK_UN);    
		} else {
			$list_data = array();
		}
		fclose($fp);
	} else {
		$list_data = array();
	}
	return $list_data;
}



/*------------------------

-------------------------*/

function resize($img, $w, $h, $newfilename) 
{
	//Check if GD extension is loaded
	if (!extension_loaded('gd') && !extension_loaded('gd2')) {
		trigger_error("GD is not loaded", E_USER_WARNING);
		return false;
	}

	//Get Image size info
	$imgInfo = getimagesize($img);
	switch ($imgInfo[2]) {
		case 1: $im = imagecreatefromgif($img); break;
		case 2: $im = imagecreatefromjpeg($img);  break;
		case 3: $im = imagecreatefrompng($img); break;
		default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
	}

	//If image dimension is smaller, do not resize
	if ($imgInfo[0] <= $w && $imgInfo[1] <= $h) {
		$nHeight = $imgInfo[1];
		$nWidth = $imgInfo[0];
	}else{
		//yeah, resize it, but keep it proportional
		if ($w/$imgInfo[0] > $h/$imgInfo[1]) {
			$nWidth = $w;
			$nHeight = $imgInfo[1]*($w/$imgInfo[0]);
		}else{
			$nWidth = $imgInfo[0]*($h/$imgInfo[1]);
			$nHeight = $h;
		}
	}
	$nWidth = round($nWidth);
	$nHeight = round($nHeight);

	$newImg = imagecreatetruecolor($nWidth, $nHeight);

	/* Check if this image is PNG or GIF, then set if Transparent*/  
	if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)){
		imagealphablending($newImg, false);
		imagesavealpha($newImg,true);
		$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
		imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
	}
	imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);

	//Generate the file, and rename it to $newfilename
	switch ($imgInfo[2]) {
		case 1: imagegif($newImg,$newfilename); break;
		case 2: imagejpeg($newImg,$newfilename);  break;
		case 3: imagepng($newImg,$newfilename); break;
		default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
	}

	return $newfilename;
}
