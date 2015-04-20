<?php
require_once '../../admin/functions.php';
require_once 'php-export-data.class.php';

$req = get_param();
list($db_name, $table_name) = null_exit($req, 'db', 'table');
$columns = objects_read($db_name, $table_name, true);

$exporter = new ExportDataExcel('browser', $db_name.'-'.$table_name.'.xls');
$exporter->initialize(); //开始发送表格流数据到浏览器 

$has_header = false;
foreach($columns as $column) {
	if (!$has_header) {
		$exporter->addRow(array_keys($column)); 
		$has_header = true;
	}

	$datas = array();
	foreach($column as $key=>$val) {
		if (is_array($val)) {
			$unicode_str = json_encode($val);
			$datas[] = decodeUnicode($unicode_str);
		} else {
			$datas[] = $val;
		}
	}

	$exporter->addRow($datas); 
}

$exporter->finalize(); //完成页脚，发送剩余数据到浏览器


function decodeUnicode($str)
{
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
        create_function(
            '$matches',
            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
        ),
        $str);
}
