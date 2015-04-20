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
	$exporter->addRow($column); 
}

$exporter->finalize(); //完成页脚，发送剩余数据到浏览器


