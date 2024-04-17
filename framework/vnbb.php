<?php

!defined('DEBUG') AND define('DEBUG', 1); // 1: Development, 2: Online debugging: logging, 0: Close
!defined('APP_PATH') AND define('APP_PATH', './');
!defined('VNBB_PATH') AND define('VNBB_PATH', dirname(__FILE__).'/');

function_exists('ini_set') AND ini_set('display_errors', DEBUG ? '1' : '0');
error_reporting(DEBUG ? E_ALL : 0);
version_compare(PHP_VERSION, '5.3.0', '<') AND set_magic_quotes_runtime(0);
$get_magic_quotes_gpc = version_compare(PHP_VERSION, '5.4.0', '<') ? get_magic_quotes_gpc() : FALSE;
$starttime = microtime(1);
$time = time();

define('IN_CMD', !empty($_SERVER['SHELL']) || empty($_SERVER['REMOTE_ADDR']));
if(IN_CMD) {
	!isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] = '';
	!isset($_SERVER['REQUEST_URI']) AND $_SERVER['REQUEST_URI'] = '';
	!isset($_SERVER['REQUEST_METHOD']) AND $_SERVER['REQUEST_METHOD'] = 'GET';
} else {
	header("Content-type: text/html; charset=utf-8");
	//header("Cache-Control: max-age=0;");
	//header("Cache-Control: no-store;");
	//header("X-Powered-By: VNBB 1.0");
}

// hook vnbb_include_before.php

// ----------------------------------------------------------> db cache class

include VNBB_PATH.'db_mysql.class.php';
include VNBB_PATH.'db_pdo_mysql.class.php';
include VNBB_PATH.'db_pdo_sqlite.class.php';
include VNBB_PATH.'cache_apc.class.php';
include VNBB_PATH.'cache_memcached.class.php';
include VNBB_PATH.'cache_mysql.class.php';
include VNBB_PATH.'cache_redis.class.php';
include VNBB_PATH.'cache_xcache.class.php';
include VNBB_PATH.'cache_yac.class.php';

// ----------------------------------------------------------> global function

include VNBB_PATH.'db.func.php';
include VNBB_PATH.'cache.func.php';
include VNBB_PATH.'image.func.php';
include VNBB_PATH.'array.func.php';
include VNBB_PATH.'xn_encrypt.func.php';
include VNBB_PATH.'misc.func.php';

// hook vnbb_include_after.php

empty($conf) AND $conf = array('db'=>array(), 'cache'=>array(), 'tmp_path'=>'./', 'log_path'=>'./', 'timezone'=>'Asia/Shanghai');
empty($conf['tmp_path']) AND $conf['tmp_path'] = ini_get('upload_tmp_dir');
empty($conf['log_path']) AND $conf['log_path'] = './';

$ip = ip();
$longip = ip2long($ip);
$longip < 0 AND $longip = sprintf("%u", $longip);
$useragent = _SERVER('HTTP_USER_AGENT');

// Language pack variables
!isset($lang) AND $lang = array();

$errno = 0;
$errstr = '';

// error_handle
// register_shutdown_function('xn_shutdown_handle');
DEBUG AND set_error_handler('error_handle', -1);
empty($conf['timezone']) AND $conf['timezone'] = 'Asia/Shanghai';
date_default_timezone_set($conf['timezone']);

// super global variable
!empty($_SERVER['HTTP_X_REWRITE_URL']) AND $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
!isset($_SERVER['REQUEST_URI']) AND $_SERVER['REQUEST_URI'] = '';
$_SERVER['REQUEST_URI'] = str_replace('/index.php?', '/', $_SERVER['REQUEST_URI']); // Compatible with iis6
$_REQUEST = array_merge($_COOKIE, $_POST, $_GET, xn_url_parse($_SERVER['REQUEST_URI']));

// IP address
!isset($_SERVER['REMOTE_ADDR']) AND $_SERVER['REMOTE_ADDR'] = '';
!isset($_SERVER['SERVER_ADDR']) AND $_SERVER['SERVER_ADDR'] = '';

// $_SERVER['REQUEST_METHOD'] === 'PUT' ? @parse_str(file_get_contents('php://input', false , null, -1 , $_SERVER['CONTENT_LENGTH']), $_PUT) : $_PUT = array(); // No need to support PUT
$ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower(trim($_SERVER['HTTP_X_REQUESTED_WITH'])) == 'xmlhttprequest') || param('ajax');
$method = $_SERVER['REQUEST_METHOD'];



// Save to super global variables to prevent conflicts from being overwritten.
$_SERVER['starttime'] = $starttime;
$_SERVER['time'] = $time;
$_SERVER['ip'] = $ip;
$_SERVER['longip'] = $longip;
$_SERVER['useragent'] = $useragent;
$_SERVER['conf'] = $conf;
$_SERVER['lang'] = $lang;
$_SERVER['errno'] = $errno;
$_SERVER['errstr'] = $errstr;
$_SERVER['method'] = $method;
$_SERVER['ajax'] = $ajax;
$_SERVER['get_magic_quotes_gpc'] = $get_magic_quotes_gpc;




$db = !empty($conf['db']) ? db_new($conf['db']) : NULL;

$conf['cache']['mysql']['db'] = $db;
$cache = !empty($conf['cache']) ? cache_new($conf['cache']) : NULL;
unset($conf['cache']['mysql']['db']);

!empty($conf) AND (function_exists('xiuno_key') ? ($conf['auth_key'] = xiuno_key()) : NULL);

$_SERVER['db'] = $db;
$_SERVER['cache'] = $cache;

?>