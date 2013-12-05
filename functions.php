<?php

function db_path()
{
	return dirname(__FILE__).'/databases';
}

function prety_json($obj)
{
	return indent_json(json_encode($obj));
}

function indent_json($json) 
{
	$result      = '';
	$pos         = 0;
	$strLen      = strlen($json);
	$indentStr   = '  ';
	$newLine     = "\n";
	$prevChar    = '';
	$outOfQuotes = true;

	for ($i=0; $i<=$strLen; $i++) {

		// Grab the next character in the string.
		$char = substr($json, $i, 1);

		// Are we inside a quoted string?
		if ($char == '"' && $prevChar != '\\') {
			$outOfQuotes = !$outOfQuotes;

			// If this character is the end of an element,
			// output a new line and indent the next line.
		} else if(($char == '}' || $char == ']') && $outOfQuotes) {
			$result .= $newLine;
			$pos --;
			for ($j=0; $j<$pos; $j++) {
				$result .= $indentStr;
			}
		}

		// Add the character to the result string.
		$result .= $char;

		// If the last character was the beginning of an element,
		// output a new line and indent the next line.
		if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
			$result .= $newLine;
			if ($char == '{' || $char == '[') {
				$pos ++;
			}

			for ($j = 0; $j < $pos; $j++) {
				$result .= $indentStr;
			}
		}

		$prevChar = $char;
	}

	return $result;
}

function get_db_captions()
{
	$result = [];
	foreach (glob(db_path().'/*', GLOB_ONLYDIR) as $db_path) { 
		$db_name = basename($db_path);
		$filename = $db_path.'/schema.json';
		$schema_str = file_get_contents($filename);
		if ($schema_str) {
			$schema = json_decode($schema_str, true);
			$caption = $schema['caption'];
			$caption['name'] = $db_name;
			$result[] = $caption;
		}
	}
	return $result;
}

function get_table_captions($db_name=null)
{
	$result = [];

	if ($db_name === null) {
		$db_captions = get_db_captions();
		foreach ($db_captions as $caption) {
			$result[$caption['name']] = get_table_captions($caption['name']);
		}
		return $result;
	} else {
		foreach (glob(db_path()."/{$db_name}/*", GLOB_ONLYDIR) as $table_path) { 
			$table_name = basename($table_path);
			$filename = $table_path.'/schema.json';
			$schema_str = file_get_contents($filename);
			if ($schema_str) {
				$schema = json_decode($schema_str, true);
				$caption = $schema['caption'];
				$caption['name'] = $table_name;
				$result[] = $caption;
			}
		}
		return $result;

	}
}

function pp($obj)
{
	echo "<pre>";
	print_r($obj);
	echo "</pre>";
}

$g_union = null;

function get_param($name=null, $default='default')
{
	global $g_union;
	if ($g_union === null) {
		$g_union = array_merge($_GET, $_POST); 
	}

	if ($name === null) {
		return $g_union;
	}

	$value = @$g_union[$name];
	empty($value) && ($value=$default);

	return $value;
}

function get_basetime()
{
	return mktime(0,0,0,7,21,2012);
}

function get_selected_db()
{
	return [get_param('db', 'default'), get_param('table', 'default'), get_param('id', time()-get_basetime())];
}

function json_file($file_name)
{
	do {
		list($database,$table,$id) = get_selected_db();
		$full_name = db_path()."/{$database}/{$table}/{$file_name}";
		if (!file_exists($full_name)) {
			break;
		}

		$json_res = file_get_contents($full_name);
		if (empty($json_res)) {
			break;
		}
		return json_decode($json_res, true);
	} while (false);

	return [];
}

/*********************************************************
	jsonp
*********************************************************/

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
