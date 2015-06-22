<?php

define('UPLOAD_DIR', 'imgUpload');

$target_dir = dirname(dirname(dirname(__FILE__))) . '/cache/' . UPLOAD_DIR;
$uploaed_tmpname = $_FILES["fileToUpload"]["tmp_name"];
$uploaed_name = $_FILES["fileToUpload"]["name"];
$upload_size = $_FILES["fileToUpload"]["size"];
$upload_type = pathinfo($uploaed_name,PATHINFO_EXTENSION);

$uni_name = md5_file($uploaed_tmpname);
$target_file = "{$target_dir}/{$uni_name}.{$upload_type}";

//文件名合法性检查
if($uni_name === false) {
	exit('9000');
} 

//文件大小限制
if ($upload_size > 5000000) {
	exit('9001');
}

//只允许特定的文件扩展名
if(!in_array($upload_type, array('jpg', 'png', 'jpeg', 'gif'))) {
	exit('9002');
}

//检查图片文件大小，间接确认是否图片文件
if(isset($_POST["submit"])) {
    $check = getimagesize($uploaed_tmpname);
    if($check === false) {
	exit('9003');
    }
}

//确保文件夹存在
if (!file_exists($target_dir)) {
	@mkdir($target_dir);
}

//检查目标文件是否已经存在，如果已经存在，直接返回结果
if (file_exists($target_file)) {
	exit('1001 '.$target_file);
}

//复制上传成功的文件，返回访问链接
if (move_uploaded_file($uploaed_tmpname, $target_file)) {
	exit('1002 '.$target_file);
}

//其他原因的失败
exit('9009');
