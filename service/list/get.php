<?php
/*-----------------------------
将jsondb的一个数据表格，当做列表来存取数据
（1）支持分页获取
（2）支持条件过滤
-----------------------------*/

require_once '../../admin/functions.php';

$db_name = 'default';
$table_name = 'default';

jsonp_nocache_exit($_GET);
