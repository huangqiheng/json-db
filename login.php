<?php

define('DEFAULT_LANGUAGE', 'cn');
define('DEFAULT_REFERER_FIELD', 'referer');

class Login
{
	private $db_file = 'databases/user.db';
	private $user_is_logged_in = false;
	public $feedback = "";

	public function __construct()
	{
		define('USER_INDEX', 0); 
		define('PASS_INDEX', 1); 
		define('MAIL_INDEX', 2); 

		$this->password_init();

		if ($this->is_call_me()) {
			$this->run();
		}
	}

	//设置7天超期
	private function session_start_expired($lifetime=604800)
	{
		session_set_cookie_params($lifetime);
		session_start();
		setcookie(session_name(),session_id(),time()+$lifetime);
	}

	public function logined()
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			$this->session_start_expired();
		}
		$result = null;
		if (!empty($_SESSION['user_name']) && ($_SESSION['user_is_logged_in'])) {
			$result = [];
			$result['user_name'] = $_SESSION['user_name'];
			$result['user_email'] = $_SESSION['user_email'];
		}
		return $result;
	}

	private function is_call_me()
	{
		return ($_SERVER['SCRIPT_FILENAME'] === __FILE__);
	}

	private function run()
	{
		if (isset($_GET["action"]) && $_GET["action"] == "register") {
			if ($this->check_registration_data()) {
				$this->create_new_user();
			}
			$this->show_page_registration();
		} else {
			//会话开始
			$this->session_start_expired();

			if (isset($_GET["action"]) && $_GET["action"] == "logout") 
			{//注销处理流程
				$_SESSION = array();
				session_destroy();
				$this->user_is_logged_in = false;
				$this->feedback = T('You were just logged out.');
			} 
			elseif (!empty($_SESSION['user_name']) && ($_SESSION['user_is_logged_in'])) 
			{//检查登陆状态
				$this->user_is_logged_in = true; 
			} 
			elseif (isset($_POST["login"])) 
			{//登陆处理流程
				if ($this->check_login_form_data_not_empty()) {
					$this->check_password_and_login();
				}
			}

			//根据处理结果，显示下一步页面
			if ($this->user_is_logged_in) {
				$this->show_page_logged_in();
			} else {
				$this->show_page_login_form();
			}
		}
	}

	private function check_login_form_data_not_empty()
	{
		if (!empty($_POST['user_name']) && !empty($_POST['user_password'])) {
			return true;
		} elseif (empty($_POST['user_name'])) {
			$this->feedback = T('Username field was empty.');
		} elseif (empty($_POST['user_password'])) {
			$this->feedback = T('Password field was empty.');
		}
		return false;
	}

	//检查用户是否存在，如果存在则检查提供的密码是否和数据库中的匹配
	private function check_password_and_login()
	{
		$userdata = $this->get_userdata($_POST['user_name']);
		if ($userdata) {
			if ($this->password_verify($_POST['user_password'], $userdata[PASS_INDEX])) {
				$_SESSION['user_name'] = $userdata[USER_INDEX];
				$_SESSION['user_email'] = $userdata[MAIL_INDEX];
				$_SESSION['user_is_logged_in'] = true;
				$this->user_is_logged_in = true;
				return true;
			} else {
				$this->feedback = T('Wrong password.');
			}
		} else {
			$this->feedback = T('This user does not exist.');
		}
		return false;
	}

	//检查用户输入的注册信息是否合法
	private function check_registration_data()
	{
		if (!isset($_POST["register"])) {
			return false;
		}

		if (!empty($_POST['user_name'])
				&& strlen($_POST['user_name']) <= 64
				&& strlen($_POST['user_name']) >= 2
				&& preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])
				&& !empty($_POST['user_email'])
				&& strlen($_POST['user_email']) <= 64
				&& filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)
				&& !empty($_POST['user_password_new'])
				&& !empty($_POST['user_password_repeat'])
				&& ($_POST['user_password_new'] === $_POST['user_password_repeat'])
		   ) {
			return true;
		} elseif (empty($_POST['user_name'])) {
			$this->feedback = T('Empty Username');
		} elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
			$this->feedback = T('Empty Password');
		} elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
			$this->feedback = T('Password and password repeat are not the same');
		} elseif (strlen($_POST['user_password_new']) < 6) {
			$this->feedback = T('Password has a minimum length of 6 characters');
		} elseif (strlen($_POST['user_name']) > 64 || strlen($_POST['user_name']) < 2) {
			$this->feedback = T('Username cannot be shorter than 2 or longer than 64 characters');
		} elseif (!preg_match('/^[a-z\d]{2,64}$/i', $_POST['user_name'])) {
			$this->feedback = T('Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters');
		} elseif (empty($_POST['user_email'])) {
			$this->feedback = T('Email cannot be empty');
		} elseif (strlen($_POST['user_email']) > 64) {
			$this->feedback = T('Email cannot be longer than 64 characters');
		} elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
			$this->feedback = T('Your email address is not in a valid email format');
		} else {
			$this->feedback = T('An unknown error occurred');
		}
		return false;
	}

	private function create_new_user()
	{
		$user_name = htmlentities($_POST['user_name'], ENT_QUOTES);
		$user_email = htmlentities($_POST['user_email'], ENT_QUOTES);
		$user_password = $_POST['user_password_new'];
		$user_password_hash = $this->password_hash($user_password, PASSWORD_DEFAULT);
		$userdata = $this->get_userdata($user_name);

		if ($userdata) {
			$this->feedback = T('Sorry, that username is already taken. Please choose another one.');
		} else {
			$new_data = [USER_INDEX=>$user_name, PASS_INDEX=>$user_password_hash, MAIL_INDEX=>$user_email];
			$user_datas = $this->userdata();
			array_unshift($user_datas, $new_data);
			$this->userdata($user_datas);
			$this->feedback = T('Your account has been created successfully. You can now log in.');
			return true;
		}
		return false;
	}

	private function referer_jump()
	{
		$check_urls = [@$_SERVER['HTTP_REFERER'], @$_SERVER['REQUEST_URI']];

		foreach($check_urls as $url) {
			$query_str = parse_url($url, PHP_URL_QUERY);
			if ($query_str) {
				$querys = parse_str($query_str, $output);
				if ($referer = @$output[DEFAULT_REFERER_FIELD]) {
					header( 'Location: '.$referer) ;
					exit();
				}
			}
		}
	}

	private function show_page_logged_in()
	{
		$this->referer_jump();

		header('Content-Type: text/html; charset=utf-8');
		if ($this->feedback) {
			echo $this->feedback . "<br/><br/>";
		}

		echo T('Hello').' '.$_SESSION['user_name'].', '.T('you are logged in.').'<br/><br/>';
		echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=logout">'.T('Log out').'</a>';
	}

	private function show_page_login_form()
	{
		if ($this->feedback) {
			$this->referer_jump();
			echo $this->feedback . "<br/><br/>";
		}

		header('Content-Type: text/html; charset=utf-8');

		echo '<h2>'.T('Login').'</h2>';

		echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '" name="loginform">';
		echo '<table><tr><td>';
		echo T('Username');
		echo '</td><td>';
		echo '<input id="login_input_username" type="text" name="user_name" required /> ';
		echo '</td></tr><tr><td>';
		echo T('Password');
		echo '</td><td>';
		echo '<input id="login_input_password" type="password" name="user_password" required /> ';
		echo '</td></tr><tr><td>';
		echo '</td><td>';
		echo '<input type="submit"  name="login" value="'.T('Log in').'" />';
		echo '</td><td>';
		echo '</td></tr></table>';
		echo '</form>';

		echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=register">'.T('Register new account').'</a>';
	}

	private function show_page_registration()
	{
		header('Content-Type: text/html; charset=utf-8');

		if ($this->feedback) {
			echo $this->feedback . "<br/><br/>";
		}

		echo '<h2>'.T('Registration').'</h2>';

		echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?action=register" name="registerform">';
		echo '<table><tr><td>';
		echo T('Username');
		echo '</td><td>';
		echo '<input id="login_input_username" type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" required />';
		echo '</td><td>';
		echo T('only letters and numbers, 2 to 64 characters');
		echo '</td></tr><tr><td>';
		echo T('email');
		echo '</td><td>';
		echo '<input id="login_input_email" type="email" name="user_email" required />';
		echo '</td><td>';
		echo T('User\'s email');
		echo '</td></tr><tr><td>';
		echo T('Password');
		echo '</td><td>';
		echo '<input id="login_input_password_new" class="login_input" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />';
		echo '</td><td>';
		echo T('Private password stored as hash in database(min. 6 characters)');
		echo '</td></tr><tr><td>';
		echo T('Repeat password');
		echo '</td><td>';
		echo '<input id="login_input_password_repeat" class="login_input" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />';
		echo '</td><td>';
		echo T('Re enter password');
		echo '</td></tr><tr><td>';
		echo '<input type="submit" name="register" value="'.T('Register').'" />';
		echo '</td><td>';
		echo '</td><td>';
		echo '</td></tr></table>';
		echo '</form>';

		echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '">'.T('Homepage').'</a>';
	}

	/********************************/
	/*	辅助函数		*/
	/********************************/

	private function get_userdata($req_username)
	{
		$user_datas = $this->userdata();
		if (empty($user_datas)) {
			return null;
		}

		foreach($user_datas as $user_data) {
			$user_name = @$user_data[USER_INDEX];
			if ($req_username === $user_name) {
				return $user_data;
			}
		}
		return null;
	}


	private function userdata($data=null)
	{
		if ($data) {
			$this->object_save($this->db_file, $data);
		} else {
			return $this->object_read($this->db_file);
		}
	}

	public function object_save($filename, $data)
	{
		file_put_contents($filename, json_encode($data));
	}

	public function object_read($filename)
	{
		if (!file_exists($filename)) {
			return [];
		}

		$data_str = file_get_contents($filename);
		if ($data_str === null) {
			return [];
		}
		return json_decode($data_str, true);
	}

	private function password_init()
	{
		if (!defined('PASSWORD_DEFAULT')) {
			define('PASSWORD_BCRYPT', 1);
			define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
		}
	}

	private function password_verify($password, $hash) {
		if (!function_exists('crypt')) {
			trigger_error("Crypt must be loaded for password_verify to function", E_USER_WARNING);
			return false;
		}
		$ret = crypt($password, $hash);
		if (!is_string($ret) || strlen($ret) != strlen($hash) || strlen($ret) <= 13) {
			return false;
		}

		$status = 0;
		for ($i = 0; $i < strlen($ret); $i++) {
			$status |= (ord($ret[$i]) ^ ord($hash[$i]));
		}

		return $status === 0;
	}

	private function password_hash($password, $algo, array $options = array()) 
	{
		if (!function_exists('crypt')) {
			trigger_error("Crypt must be loaded for password_hash to function", E_USER_WARNING);
			return null;
		}
		if (!is_string($password)) {
			trigger_error("password_hash(): Password must be a string", E_USER_WARNING);
			return null;
		}
		if (!is_int($algo)) {
			trigger_error("password_hash() expects parameter 2 to be long, " . gettype($algo) . " given", E_USER_WARNING);
			return null;
		}
		switch ($algo) {
			case PASSWORD_BCRYPT:
				// Note that this is a C constant, but not exposed to PHP, so we don't define it here.
				$cost = 10;
				if (isset($options['cost'])) {
					$cost = $options['cost'];
					if ($cost < 4 || $cost > 31) {
						trigger_error(sprintf("password_hash(): Invalid bcrypt cost parameter specified: %d", $cost), E_USER_WARNING);
						return null;
					}
				}
				// The length of salt to generate
				$raw_salt_len = 16;
				// The length required in the final serialization
				$required_salt_len = 22;
				$hash_format = sprintf("$2y$%02d$", $cost);
				break;
			default:
				trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
				return null;
		}
		if (isset($options['salt'])) {
			switch (gettype($options['salt'])) {
				case 'NULL':
				case 'boolean':
				case 'integer':
				case 'double':
				case 'string':
					$salt = (string) $options['salt'];
					break;
				case 'object':
					if (method_exists($options['salt'], '__tostring')) {
						$salt = (string) $options['salt'];
						break;
					}
				case 'array':
				case 'resource':
				default:
					trigger_error('password_hash(): Non-string salt parameter supplied', E_USER_WARNING);
					return null;
			}
			if (strlen($salt) < $required_salt_len) {
				trigger_error(sprintf("password_hash(): Provided salt is too short: %d expecting %d", strlen($salt), $required_salt_len), E_USER_WARNING);
				return null;
			} elseif (0 == preg_match('#^[a-zA-Z0-9./]+$#D', $salt)) {
				$salt = str_replace('+', '.', base64_encode($salt));
			}
		} else {
			$buffer = '';
			$buffer_valid = false;
			if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
				$buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
				if ($buffer) {
					$buffer_valid = true;
				}
			}
			if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
				$buffer = openssl_random_pseudo_bytes($raw_salt_len);
				if ($buffer) {
					$buffer_valid = true;
				}
			}
			if (!$buffer_valid && is_readable('/dev/urandom')) {
				$f = fopen('/dev/urandom', 'r');
				$read = strlen($buffer);
				while ($read < $raw_salt_len) {
					$buffer .= fread($f, $raw_salt_len - $read);
					$read = strlen($buffer);
				}
				fclose($f);
				if ($read >= $raw_salt_len) {
					$buffer_valid = true;
				}
			}
			if (!$buffer_valid || strlen($buffer) < $raw_salt_len) {
				$bl = strlen($buffer);
				for ($i = 0; $i < $raw_salt_len; $i++) {
					if ($i < $bl) {
						$buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
					} else {
						$buffer .= chr(mt_rand(0, 255));
					}
				}
			}
			$salt = str_replace('+', '.', base64_encode($buffer));
		}
		$salt = substr($salt, 0, $required_salt_len);

		$hash = $hash_format . $salt;

		$ret = crypt($password, $hash);

		if (!is_string($ret) || strlen($ret) <= 13) {
			return false;
		}

		return $ret;
	}
}

$Language_data = [
	'Hello' => 		['cn'=>'您好'],
	'you are logged in.' => ['cn'=>'您已经是登陆状态了。'],
	'Log out' => 		['cn'=>'登出'],
	'Login' =>		['cn'=>'登陆'],
	'Username' =>		['cn'=>'账户'],
	'Password' =>		['cn'=>'密码'],
	'email' =>		['cn'=>'电子邮件'],
	'Log in' =>						['cn'=>'登陆'],
	'Register new account'=>				['cn'=>'注册新账户'],
	'Registration'=>					['cn'=>'注册'],
	'Register' =>						['cn'=>'注册'],
	'only letters and numbers, 2 to 64 characters' =>	['cn'=>'只允许字符和数字，2到64个字节'],
	'User\'s email'=>					['cn'=>'用户的电子邮件'],
	'Repeat password' =>					['cn'=>'再输密码'],
	'Re enter password' =>					['cn'=>'请再次输入上面输过的密码'],
	'Homepage' => 						['cn'=>'返回主界面'],
	'You were just logged out.' =>				['cn'=>'您刚刚登出了。'],
	'Username field was empty.' =>				['cn'=>'用户名字段不能为空。'],
	'Password field was empty.' =>				['cn'=>'密码字段不能为空。'],
	'Wrong password.' =>					['cn'=>'密码错误。'],
	'This user does not exist.' =>				['cn'=>'这个用户名不存在。'],
	'Empty Username' =>					['cn'=>'用户名是空的'],
	'Empty Password' => 					['cn'=>'密码是空的'],
	'Password and password repeat are not the same'=>	['cn'=>'两次输入的密码并不相同'],
	'Password has a minimum length of 6 characters'=>	['cn'=>'密码长度少于6个字符'],
	'Email cannot be empty'	=>				['cn'=>'电子邮件地址不能为空'],
	'Email cannot be longer than 64 characters' =>		['cn'=>'电子邮件不能长于64个字符'],
	'Your email address is not in a valid email format' =>	['cn'=>'你的电子邮件地址不符合邮件格式'],
	'An unknown error occurred.' =>				['cn'=>'发生了未知错误'],
	'The setting error on Permission of  "databases" folder'=>
				['cn'=>'databases目录的访问权限设置不当。'],
	'Private password stored as hash in database(min. 6 characters)'=>
				['cn'=>'个人密码（最少6个字符），服务器不会保存明文密码'],
	'Username cannot be shorter than 2 or longer than 64 characters' => 				
				['cn'=>'用户名不能少于2个字符，或者大于64个字符'],
	'Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters'=> 
				['cn'=>'用户名不符合规则：只能a-Z字符和数字，2到64个字符数'],
	'Sorry, that username is already taken. Please choose another one.' =>				
				['cn'=>'非常抱歉，你输入的用户名已经被其他人拥有，请再选另外一个。'],
	'Your account has been created successfully. You can now log in.' =>				
				['cn'=>'您的账户已经成功创建了。您现在能够登陆了。'],
];

function T($ori)
{
	global $Language_data;
	$output = @$Language_data[$ori][DEFAULT_LANGUAGE];
	return ($output)? $output : $ori;
}

$logined_info = (new Login())->logined();

function login_wrap_referer($url, $referer)
{
	$query_str = parse_url($url, PHP_URL_QUERY);
	if ($query_str) {
		return $url.'&'.DEFAULT_REFERER_FIELD.'='.$referer;
	} else {
		return $url.'?'.DEFAULT_REFERER_FIELD.'='.$referer;
	}
}

function denies_with_redirect()
{
	global $logined_info;
	$me_script = substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT']));
	$referer = $_SERVER['REQUEST_URI'];

	if ($logined_info === null) {
		header( 'Location: '.$me_script.'?'.DEFAULT_REFERER_FIELD.'='.$referer) ;
		exit();
	}

	$logined_info['logout'] = $me_script.'?action=logout';
	return $logined_info;
}

function denies_with_json()
{
	global $logined_info;
	if ($logined_info === null) {
		header('Content-Type: application/json; charset=utf-8');
		$data = ['status'=>'error', 'error'=>'you need to login first.'];
		$json = json_encode($data);

		if(!isset($_GET['callback'])) {
			echo $json;
			exit();
		}

		$identifier_syntax = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
		$reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
				'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 
				'for', 'switch', 'while', 'debugger', 'function', 'this', 'with', 
				'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 
				'extends', 'super', 'const', 'export', 'import', 'implements', 'let', 
				'private', 'public', 'yield', 'interface', 'package', 'protected', 
				'static', 'null', 'true', 'false');

		$subject = $_GET['callback'];
		if (preg_match($identifier_syntax, $subject) && !in_array(mb_strtolower($subject,'UTF-8'),$reserved_words)) {
			echo "{$_GET['callback']}($json)";
			exit();
		}

		echo json_encode(['status'=>'error', 'error'=>'callback function name error.']);
		exit();
	}

	$me_script = substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT']));
	$logined_info['logout'] = $me_script.'?action=logout';
	return $logined_info;
}


