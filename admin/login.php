<?php

defined('DISABLE_CREATE') or define('DISABLE_CREATE', false);
defined('DEFAULT_LANGUAGE') or define('DEFAULT_LANGUAGE', 'cn');
defined('DEFAULT_REFERER_FIELD') or define('DEFAULT_REFERER_FIELD', 'referer');
defined('COOKIE_LIFETIME') or define('COOKIE_LIFETIME', 3600*24*365);
defined('DEFAULT_DB_FILE') or define('DEFAULT_DB_FILE', dirname(dirname(__FILE__)).'/databases/user.db');
defined('LOGIN_MESCRIPT') or define('LOGIN_MESCRIPT', substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

class Login
{
	private $db_file = null;
	private $user_is_logged_in = false;
	public $feedback = "";

	public function __construct($db_file=null)
	{
		define('USER_INDEX', 0); 
		define('PASS_INDEX', 1); 
		define('MAIL_INDEX', 2); 

		$this->db_file = $db_file;

		if (!is_readable(dirname($this->db_file))) {
			$this->db_file = null;
		}

		if (empty($this->db_file)) {
			$this->db_file = DEFAULT_DB_FILE;
		}

		if ($this->is_call_me()) {
			$this->run();
		}
	}

	private function session_start_expired($lifetime=COOKIE_LIFETIME, $sec_name='jsondb_login')
	{
		session_set_cookie_params($lifetime);
		session_name($sec_name);
		session_start();

		ini_set('session.save_path','/jsondb/'); 
		ini_set('session.cookie_lifetime',$lifetime); 
		ini_set('session.gc_maxlifetime',$lifetime); 

		if (isset($_COOKIE[$sec_name])) {
			setcookie($sec_name, $_COOKIE[$sec_name], time()+$lifetime);
		}
	}

	public function logined()
	{
		if (is_session_started() === FALSE) {
			if (!$this->is_call_me()) {
				$this->session_start_expired();
			}
		}
		$result = null;
		if (!empty($_SESSION['user_name']) && ($_SESSION['user_is_logged_in'])) {
			$result = array();
			$result['user_name'] = $_SESSION['user_name'];
			$result['user_email'] = $_SESSION['user_email'];
		} else {
			return null;
		}

		$userdata = $this->get_userdata($result['user_name']);
		if (empty($userdata)) {
			if (is_session_started() === FALSE) {
				session_destroy();
			}
			$this->user_is_logged_in = false;
			$this->feedback = T('Your account not exists');
			return null;
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
			if (password_verify($_POST['user_password'], $userdata[PASS_INDEX])) {
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
		if (DISABLE_CREATE) {
			$this->feedback = T('Sorry, system had shutdown the registration of new user.');
			return false;
		}

		$user_name = htmlentities($_POST['user_name'], ENT_QUOTES);
		$user_email = htmlentities($_POST['user_email'], ENT_QUOTES);
		$user_password = $_POST['user_password_new'];
		$user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
		$userdata = $this->get_userdata($user_name);

		if ($userdata) {
			$this->feedback = T('Sorry, that username is already taken. Please choose another one.');
		} else {
			$new_data = array(USER_INDEX=>$user_name, PASS_INDEX=>$user_password_hash, MAIL_INDEX=>$user_email);
			$user_datas = $this->userdata();
			array_unshift($user_datas, $new_data);
			$this->userdata($user_datas);
			$this->feedback = T('Your account has been created successfully. You can log in now.');
			return true;
		}
		return false;
	}

	private function referer_jump()
	{
		$check_urls = array(@$_SERVER['HTTP_REFERER'], @$_SERVER['REQUEST_URI']);

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
		echo '<a href="' . LOGIN_MESCRIPT . '?action=logout">'.T('Log out').'</a>';
	}

	private function show_page_login_form()
	{
		header('Content-Type: text/html; charset=utf-8');

		if ($this->feedback) {
			$this->referer_jump();
			echo $this->feedback . "<br/><br/>";
		}

		echo '<h2>'.T('Login').'</h2>';

		echo '<form method="post" action="' . LOGIN_MESCRIPT . '" name="loginform">';
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

		echo '<a href="' . LOGIN_MESCRIPT . '?action=register">'.T('Register new account').'</a>';
	}

	private function show_page_registration()
	{
		header('Content-Type: text/html; charset=utf-8');

		if ($this->feedback) {
			echo $this->feedback . "<br/><br/>";
		}

		echo '<h2>'.T('Registration').'</h2>';

		echo '<form method="post" action="' . LOGIN_MESCRIPT . '?action=register" name="registerform">';
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

		echo '<a href="' . LOGIN_MESCRIPT . '">'.T('Homepage').'</a>';
	}

	/********************************/
	/*	辅助函数		*/
	/********************************/

	private function get_userdata($req_username)
	{
		if (empty($req_username)) {
			return null;
		}

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
		file_put_contents($filename, $this->json_encode_h($data));
	}

	public function json_encode_h($obj)
	{
		return $this->indent_json(json_encode($obj));
	}

	public function indent_json($json) 
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

	public function object_read($filename)
	{
		if (!file_exists($filename)) {
			return array();
		}

		$data_str = file_get_contents($filename);
		if (empty($data_str)) {
			return array();
		}
		return json_decode($data_str, true);
	}
}

	
function is_session_started()
{
	if ( php_sapi_name() !== 'cli' ) {
		if ( version_compare(phpversion(), '5.4.0', '>=') ) {
			return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
		} else {
			return session_id() === '' ? FALSE : TRUE;
		}
	}
	return FALSE;
}

$Language_data = array(
	'Hello' => 		array('cn'=>'您好'),
	'you are logged in.' => array('cn'=>'您已经是登陆状态了。'),
	'Log out' => 		array('cn'=>'登出'),
	'Login' =>		array('cn'=>'登陆'),
	'Username' =>		array('cn'=>'账户'),
	'Password' =>		array('cn'=>'密码'),
	'email' =>		array('cn'=>'电子邮件'),
	'Log in' =>						array('cn'=>'登陆'),
	'Register new account'=>				array('cn'=>'注册新账户'),
	'Registration'=>					array('cn'=>'注册'),
	'Register' =>						array('cn'=>'注册'),
	'only letters and numbers, 2 to 64 characters' =>	array('cn'=>'只允许字符和数字，2到64个字节'),
	'User\'s email'=>					array('cn'=>'用户的电子邮件'),
	'Repeat password' =>					array('cn'=>'再输密码'),
	'Re enter password' =>					array('cn'=>'请再次输入上面输过的密码'),
	'Homepage' => 						array('cn'=>'返回主界面'),
	'You were just logged out.' =>				array('cn'=>'您刚刚登出了。'),
	'Your account not exists' => 				array('cn'=>'你的账户不存在'),
	'Username field was empty.' =>				array('cn'=>'用户名字段不能为空。'),
	'Password field was empty.' =>				array('cn'=>'密码字段不能为空。'),
	'Wrong password.' =>					array('cn'=>'密码错误。'),
	'This user does not exist.' =>				array('cn'=>'这个用户名不存在。'),
	'Empty Username' =>					array('cn'=>'用户名是空的'),
	'Empty Password' => 					array('cn'=>'密码是空的'),
	'Password and password repeat are not the same'=>	array('cn'=>'两次输入的密码并不相同'),
	'Password has a minimum length of 6 characters'=>	array('cn'=>'密码长度少于6个字符'),
	'Email cannot be empty'	=>				array('cn'=>'电子邮件地址不能为空'),
	'Email cannot be longer than 64 characters' =>		array('cn'=>'电子邮件不能长于64个字符'),
	'Your email address is not in a valid email format' =>	array('cn'=>'你的电子邮件地址不符合邮件格式'),
	'An unknown error occurred.' =>				array('cn'=>'发生了未知错误'),
	'The setting error on Permission of  "databases" folder'=>
				array('cn'=>'databases目录的访问权限设置不当。'),
	'Private password stored as hash in database(min. 6 characters)'=>
				array('cn'=>'个人密码（最少6个字符），服务器不会保存明文密码'),
	'Username cannot be shorter than 2 or longer than 64 characters' => 				
				array('cn'=>'用户名不能少于2个字符，或者大于64个字符'),
	'Username does not fit the name scheme: only a-Z and numbers are allowed, 2 to 64 characters'=> 
				array('cn'=>'用户名不符合规则：只能a-Z字符和数字，2到64个字符数'),
	'Sorry, that username is already taken. Please choose another one.' =>				
				array('cn'=>'非常抱歉，你输入的用户名已经被其他人拥有，请再选另外一个。'),
	'Your account has been created successfully. You can log in now.' =>				
				array('cn'=>'您的账户已经成功创建了。您现在能够登陆了。'),
	'Sorry, system had shutdown the registration of new user.' =>				
				array('cn'=>'非常抱歉，系统已经关闭注册了。'),
);

function T($ori)
{
	global $Language_data;
	$output = @$Language_data[$ori][DEFAULT_LANGUAGE];
	return ($output)? $output : $ori;
}


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
	$referer = $_SERVER['REQUEST_URI'];

	if ($logined_info === null) {
		header( 'Location: ' . LOGIN_MESCRIPT . '?' . DEFAULT_REFERER_FIELD.'='.$referer) ;
		exit();
	}

	$logined_info['logout'] = LOGIN_MESCRIPT.'?action=logout';
	return $logined_info;
}

function denies_with_json()
{
	global $logined_info;
	if ($logined_info === null) {
		header('Content-Type: application/json; charset=utf-8');
		$data = array('status'=>'error', 'error'=>'you need to login first.');
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

		echo json_encode(array('status'=>'error', 'error'=>'callback function name error.'));
		exit();
	}

	$logined_info['logout'] = LOGIN_MESCRIPT.'?action=logout';
	return $logined_info;
}

/*--------------------------------------------------------------------
		passwordHash
---------------------------------------------------------------------*/

if (!function_exists('password_hash')){
    function password_hash($password, $algo=PASSWORD_DEFAULT, $options=array()){
        $crypt = NEW PhpPasswordLib();
        $crypt->setAlgorithm($algo);
        
        $debug  = isset($options['debug'])
                ? $options['debug']
                : NULL;
        
        $password = $crypt->generateCryptPassword($password, $options, $debug);
        
        return $password;
    }
}
if (!function_exists('password_verify')){
    function password_verify($password, $hash){
        return (crypt($password, $hash) === $hash);
    }
}
if (!function_exists('password_needs_rehash')){
    function password_needs_rehash($hash, $algo, $options=array()){
        $crypt = NEW PhpPasswordLib();
        return !$crypt->verifyCryptSetting($hash, $algo, $options);
    }
}
if (!function_exists('password_get_info')){
    function password_get_info($hash){
        $crypt = NEW PhpPasswordLib();
        return $crypt->getInfo($hash);
    }
}

if (!defined('PASSWORD_BCRYPT')) define('PASSWORD_BCRYPT', 1);
// Note that SHA hashes are not implemented in password_hash() or password_verify() in PHP 5.5
// and are not recommended for use. Recommend only the default BCrypt option
if (!defined('PASSWORD_SHA256')) define('PASSWORD_SHA256', -1);
if (!defined('PASSWORD_SHA512')) define('PASSWORD_SHA512', -2);
if (!defined('PASSWORD_DEFAULT')) define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);

class PhpPasswordLib{
    
    CONST BLOWFISH_CHAR_RANGE = './0123456789ABCDEFGHIJKLMONPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    CONST BLOWFISH_CRYPT_SETTING = '$2a$'; 
    CONST BLOWFISH_CRYPT_SETTING_ALT = '$2y$'; // Available from PHP 5.3.7
    CONST BLOWFISH_ROUNDS = 10;
    CONST BLOWFISH_NAME = 'bcrypt';
    
    // Note that SHA hashes are not implemented in password_hash() or password_verify() in PHP 5.5
    // and are not recommended for use. Recommend only the default BCrypt option
    CONST SHA256_CHAR_RANGE = './0123456789ABCDEFGHIJKLMONPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    CONST SHA256_CRYPT_SETTING = '$5$';
    CONST SHA256_ROUNDS = 5000;
    CONST SHA256_NAME = 'sha256';
    
    CONST SHA512_CHAR_RANGE = './0123456789ABCDEFGHIJKLMONPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    CONST SHA512_CRYPT_SETTING = '$6$';
    CONST SHA512_ROUNDS = 5000;
    CONST SHA512_NAME = 'sha512';
    
    
    /**
     * Default Crypt Algorithm
     * 
     * @var INT
     */
    private $algorithm = PASSWORD_BCRYPT;
    
    
    /**
     * Name of the current algorithm
     *
     * @var STRING
     */
    private $algoName;
    
    
    /**
     * Setting for PHP Crypt function, defines algorithm
     * 
     * Default setting is '$2a$' : BCrypt
     * 
     * @var STRING
     */
    protected $cryptSetting;
    
    
    /**
     * Setting for PHP Crypt function, defines processing cost
     * 
     * Default setting is '08$' for BCrypt rounds
     * 
     * @var INT
     */
    protected $rounds;
    
    
    /**
     * Salt Character Count for Crypt Functions
     * 
     * @var INT
     */
    protected $addSaltChars;
    
    
    /**
     * Salt Character Range for Crypt Functions
     * 
     * @var STRING 
     */
    protected $saltCharRange;
    
    
    /**
     * Class Constructor
     */
    public function __construct(){
        // Initialise default algorithm
        $this->setAlgorithm($this->algorithm);
    }
    
    
    /**
     * Generate Crypt Password
     * 
     * @param STRING $password The password to encode
     * @param ARRAY $options Cost value, and Salt if required
     * @param BOOL $debug If true will return time to calculate hash
     * @return STRING The encoded password
     */
    public function generateCryptPassword($password, $options = array(), $debug = FALSE){
        $startTime  = microtime(TRUE);
        if (isset($options['cost'])) $this->setCost($options['cost']);
        $salt       = $this->cryptSalt(@$options['salt']);
        $crypt      = crypt($password, $salt);
        $endTime    = microtime(TRUE);
        if ($debug){
            $calcTime = $endTime - $startTime;
            return $calcTime;
        }
        return $crypt;
    }
    
    
    /**
     * Generate Crypt Salt
     * 
     * Generates a salt suitable for Crypt using the defined crypt settings
     * 
     * @param STRING $salt Override random salt with predefined value
     * @return STRING
     */
    public function cryptSalt($salt=NULL){
        if (empty($salt)){
            for ($i = 0; $i<$this->addSaltChars; $i++){
                $salt .= $this->saltCharRange[rand(0,(strlen($this->saltCharRange)-1))];
            }
        }
        $salt = $this->cryptSetting.$this->rounds.$salt.'$';
        return $salt;
    }
    
    
    /**
     * Set Crypt Setting
     * 
     * @param type $setting
     * @return \Antnee\PhpPasswordLib\PhpPasswordLib
     */
    public function cryptSetting($setting){
        $this->cryptSetting = $setting;
        return $this;
    }
    
    
    /**
     * Salt Character Count
     * 
     * @param INT $count Number of characters to set
     * @return \Antnee\PhpPasswordLib\PhpPasswordLib|boolean
     */
    public function addSaltChars($count){
        if (is_int($count)){
            $this->addSaltChars = $count;
            return $this;
        } else {
            return FALSE;
        }
    }
    
    
    /**
     * Salt Character Range
     * 
     * @param STRING $chars
     * @return \Antnee\PhpPasswordLib\PhpPasswordLib|boolean
     */
    public function saltCharRange($chars){
        if (is_string($chars)){
            $this->saltCharRange = $chars;
            return $this;
        } else {
            return FALSE;
        }
    }
    
    
    /**
     * Set Crypt Algorithm
     * 
     * @param INT $algo
     * @return \Antnee\PhpPasswordLib\PhpPasswordLib
     */
    public function setAlgorithm($algo=NULL){
        switch ($algo){
            case PASSWORD_SHA256:
                $this->algorithm = PASSWORD_SHA256;
                $this->cryptSetting(self::SHA256_CRYPT_SETTING);
                $this->setCost(self::SHA256_ROUNDS);
                $this->addSaltChars(16);
                $this->saltCharRange(self::SHA256_CHAR_RANGE);
                $this->algoName = self::SHA256_NAME;
                break;
            case PASSWORD_SHA512:
                $this->algorithm = PASSWORD_SHA512;
                $this->cryptSetting(self::SHA512_CRYPT_SETTING);
                $this->setCost(self::SHA512_ROUNDS);
                $this->addSaltChars(16);
                $this->saltCharRange(self::SHA512_CHAR_RANGE);
                $this->algoName = self::SHA512_NAME;
                break;
            case PASSWORD_BCRYPT:
            default:
                $this->algorithm = PASSWORD_BCRYPT;
                if (version_compare(PHP_VERSION, '5.3.7') >= 1){
                    // Use improved Blowfish algorithm if supported
                    $this->cryptSetting(self::BLOWFISH_CRYPT_SETTING_ALT);
                } else {
                    $this->cryptSetting(self::BLOWFISH_CRYPT_SETTING);
                }
                $this->setCost(self::BLOWFISH_ROUNDS);
                $this->addSaltChars(22);
                $this->saltCharRange(self::BLOWFISH_CHAR_RANGE);
                $this->algoName = self::BLOWFISH_NAME;
                break;
        }
        return $this;
    }
    
    
    /**
     * Set Cost
     * 
     * @todo implement
     * 
     * @return \Antnee\PhpPasswordLib\PhpPasswordLib
     */
    public function setCost($rounds){
        switch ($this->algorithm){
            case PASSWORD_BCRYPT:
                $this->rounds = $this->setBlowfishCost($rounds);
                break;
            case PASSWORD_SHA256:
            case PASSWORD_SHA512:
                $this->rounds = $this->setShaCost($rounds);
                break;
        }
        return $this;
    }
    
    
    /**
     * Set Blowfish hash cost
     * 
     * Minimum 4, maximum 31. Value is base-2 log of actual number of rounds, so
     * 4 = 16, 8 = 256, 16 = 65,536 and 31 = 2,147,483,648
     * Defaults to 8 if value is out of range or incorrect type
     * 
     * @param int $rounds
     * @return STRING
     */
    private function setBlowfishCost($rounds){
        if (!is_int($rounds) || $rounds < 4 || $rounds > 31){
            $rounds = $rounds = self::BLOWFISH_ROUNDS;
        }
        return sprintf("%02d", $rounds)."$";
    }
    
    
    /**
     * Set SHA hash cost
     * 
     * Minimum 1000, maximum 999,999,999
     * Defaults to 5000 if value is out of range or incorrect type
     * 
     * @param INT $rounds
     * @return STRING
     */
    private function setShaCost($rounds){
        if (!is_int($rounds) || $rounds < 1000 || $rounds > 999999999){
            switch ($this->algorithm){
                case PASSWORD_SHA256:
                    $rounds = self::SHA256_ROUNDS;
                case PASSWORD_SHA512:
                default:
                    $rounds = self::SHA512_ROUNDS;
            }
        }
        return "rounds=" . $rounds ."$";
    }
    
    
    /**
     * Get hash info
     *
     * @param STRING $hash
     * @return ARRAY
     */
    public function getInfo($hash){
        $params = explode("$", $hash);
        if (count($params) < 4) return FALSE;
        
        switch ($params['1']){
            case '2a':
            case '2y':
            case '2x':
                $algo = PASSWORD_BCRYPT;
                $algoName = self::BLOWFISH_NAME;
                break;
            case '5':
                $algo = PASSWORD_SHA256;
                $algoName = self::SHA256_NAME;
                break;
            case '6':
                $algo = PASSWORD_SHA512;
                $algoName = self::SHA512_NAME;
                break;
            default:
                return FALSE;
        }
        
        $cost = preg_replace("/[^0-9,.]/", "", $params['2']);
        
        return array(
            'algo' => $algo,
            'algoName' => $algoName,
            'options' => array(
                'cost' => $cost
            ),
        );
    }
    
    
    /**
     * Verify Crypt Setting
     * 
     * Checks that the hash provided is encrypted at the current settings or not,
     * returning BOOL accordingly
     * 
     * @param STRING $hash
     * @return BOOL
     */
    public function verifyCryptSetting($hash, $algo, $options=array()){
        $this->setAlgorithm($algo);
        if (isset($options['cost'])) $this->setCost($options['cost']);
        
        $setting = $this->cryptSetting.$this->rounds;
        
        return (substr($hash, 0, strlen($setting)) === $setting);
    }
}

/*--------------------------
	入口
--------------------------*/

$login_obj = new Login();
$logined_info = $login_obj->logined();
