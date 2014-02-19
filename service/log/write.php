<?php
require_once '../../admin/functions.php';

$req = get_param();
list($db_name,$table_name,$data,$apikey) = null_exit($req,'db_name','table_name','data','apikey');
api_exit($db_name, $table_name, $apikey);
items_exit($data,'ident','facility','priority','title');

$output = append_new_data($db_name, $table_name, $data);
unset($output['listview']);
jsonp_nocache_exit($output); 

