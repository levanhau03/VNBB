<?php

define('DEBUG', 2);
define('APP_PATH', realpath(dirname(__FILE__).'/../').'/');
define('INSTALL_PATH', dirname(__FILE__).'/');

define('MESSAGE_HTM_PATH', INSTALL_PATH.'view/htm/message.htm');


$conf = (include APP_PATH.'conf/conf.default.php');
$lang = include APP_PATH."lang/$conf[lang]/bbs.php";
$lang += include APP_PATH."lang/$conf[lang]/bbs_install.php";
$conf['log_path'] = APP_PATH.$conf['log_path']; 
$conf['tmp_path'] = APP_PATH.$conf['tmp_path']; 

include APP_PATH.'framework/vnbb.php';
include APP_PATH.'model/misc.func.php';
include APP_PATH.'model/extension.func.php';
include APP_PATH.'model/plugin.func.php';
include APP_PATH.'model/theme.func.php';
include APP_PATH.'model/user.func.php';
include APP_PATH.'model/group.func.php';
include APP_PATH.'model/form.func.php';
include APP_PATH.'model/forum.func.php';
include INSTALL_PATH.'install.func.php';

$action = param('action');

// Install initialization detection
is_file(APP_PATH.'conf/conf.php') AND DEBUG != 2 AND message(0, jump(lang('installed_tips'), '../'));

// Get data from cookie
$_lang = param('lang', 'vi-vn');



// The first step is to read
if(empty($action)) {
	
    if($method == 'GET') {
        $input = array();
        $languages = array('en-us' => 'English', 'vi-vn' => 'Tiếng Việt');
        $selectedLang = isset($conf['lang']) && in_array($conf['lang'], array_keys($languages)) ? $conf['lang'] : 'vi-vn';
        $input['lang'] = form_select('lang', $languages, $selectedLang);
		
        // Hiển thị trang web với tùy chọn ngôn ngữ
        include INSTALL_PATH . "view/htm/index.htm";
    } else {
        $_lang = param('lang');
        $allowedLanguages = array('en-us', 'vi-vn');
        $_lang = in_array($_lang, $allowedLanguages) ? $_lang : 'vi-vn';
		
        // Lưu ngôn ngữ vào cookie và cập nhật trong file conf.default.php
        setcookie('lang', $_lang);
        $conf['lang'] = $_lang;
        
        // Sao lưu tệp tin trước khi thay đổi
        xn_copy(APP_PATH . './conf/conf.default.php', APP_PATH . './conf/conf.backup.php');
        
        // Thay đổi giá trị lang trong tệp conf.default.php
        $result = file_replace_var(APP_PATH . 'conf/conf.default.php', array('lang' => $_lang));
        if ($result === false) {
            message(-1, jump(lang('please_set_conf_file_writable'), ''));
        }
        
        // Chuyển hướng người dùng sau khi đã thực hiện thay đổi ngôn ngữ
        http_location('index.php?action=license');
    }
	
} elseif($action == 'license') {
	
	
	// set to cookie
	
	include INSTALL_PATH."view/htm/license.htm";
	
} elseif($action == 'env') {
	
	if($method == 'GET') {
		$succeed = 1;
		$env = $write = array();
		get_env($env, $write);
		include INSTALL_PATH."view/htm/env.htm";
	} else {
	
	}
	
} elseif($action == 'db') {
	
	if($method == 'GET') {
		
		$succeed = 1;
		$mysql_support = function_exists('mysql_connect');
		$pdo_mysql_support = extension_loaded('pdo_mysql');
		$myisam_support = extension_loaded('pdo_mysql');
		$innodb_support = extension_loaded('pdo_mysql');
		
		(!$mysql_support && !$pdo_mysql_support) AND message(-1, lang('evn_not_support_php_mysql'));

		include INSTALL_PATH."view/htm/db.htm";
		
	} else {
		
		$type = param('type');	
		$engine = param('engine');	
		$host = param('host');	
		$name = param('name');	
		$user = param('user');
		$password = param('password', '', FALSE);
		$force = param('force');
		
		$adminemail = param('adminemail');
		$adminuser = param('adminuser');
		$adminpass = param('adminpass');
		
		empty($host) AND message('host', lang('dbhost_is_empty'));
		empty($name) AND message('name', lang('dbname_is_empty'));
		empty($user) AND message('user', lang('dbuser_is_empty'));
		empty($adminpass) AND message('adminpass', lang('adminuser_is_empty'));
		empty($adminemail) AND message('adminemail', lang('adminpass_is_empty'));
		
		
		
		//set_time_limit(60);
		ini_set('mysql.connect_timeout',  5);
		ini_set('default_socket_timeout', 5); 

		$conf['db']['type'] = $type;	
		$conf['db']['mysql']['master']['host'] = $host;
		$conf['db']['mysql']['master']['name'] = $name;
		$conf['db']['mysql']['master']['user'] = $user;
		$conf['db']['mysql']['master']['password'] = $password;
		$conf['db']['mysql']['master']['engine'] = $engine;
		$conf['db']['pdo_mysql']['master']['host'] = $host;
		$conf['db']['pdo_mysql']['master']['name'] = $name;
		$conf['db']['pdo_mysql']['master']['user'] = $user;
		$conf['db']['pdo_mysql']['master']['password'] = $password;
		$conf['db']['pdo_mysql']['master']['engine'] = $engine;
		
		$_SERVER['db'] = $db = db_new($conf['db']);
		// An error may be reported here
		$r = db_connect($db);
		if($r === FALSE) {
			if($errno == 1049 || $errno == 1045) {
				if($type == 'mysql') {
					mysql_query("CREATE DATABASE $name");
					$r = db_connect($db);
				} elseif($type == 'pdo_mysql') {
					if(strpos(':', $host) !== FALSE) {
						$arr = explode(':', $host);
						$host = $arr[0];
						$port = $arr[1];
					} else {
						//$host = $host;
						$port = 3306;
					}
					try {
						$attr = array(
							PDO::ATTR_TIMEOUT => 5,
							//PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
						);
						$link = new PDO("mysql:host=$host;port=$port", $user, $password, $attr);
						$r = $link->exec("CREATE DATABASE `$name`");
						if($r === FALSE) {
							$error = $link->errorInfo();
							$errno = $error[1];
							$errstr = $error[2];
						}
					} catch (PDOException $e) {
						$errno = $e->getCode();
						$errstr = $e->getMessage();
					}
				}
			}
			if($r === FALSE) {
				message(-1, "$errstr (errno: $errno)");
			}
		}
		
		$conf['cache']['mysql']['db'] = $db;
		$_SERVER['cache'] = $cache = !empty($conf['cache']) ? cache_new($conf['cache']) : NULL;
		
		// Set engine type
		if($engine == 'innodb') {
			$db->innodb_first = TRUE;
		} else {
			$db->innodb_first = FALSE;
		}
		
		// start creating tables and importing data
		
		install_sql_file(INSTALL_PATH.'install.sql');
		
		// initialization
		copy(APP_PATH.'conf/conf.default.php', APP_PATH.'conf/conf.php');
		
		// Administrator password
		$salt = xn_rand(16);
		$password = md5(md5($adminpass).$salt);
		$update = array('username'=>$adminuser, 'email'=>$adminemail, 'password'=>$password, 'salt'=>$salt, 'create_date'=>$time, 'create_ip'=>$longip);
		db_update('user', array('uid'=>1), $update);
		
		$replace = array();
		$replace['db'] = $conf['db'];
		$replace['auth_key'] = xn_rand(64);
		$replace['installed'] = 1;
		file_replace_var(APP_PATH.'conf/conf.php', $replace);
		
		// Processing language packs
		group_update(0, array('name'=>lang('group_0')));
		group_update(1, array('name'=>lang('group_1')));
		group_update(2, array('name'=>lang('group_2')));
		group_update(4, array('name'=>lang('group_4')));
		group_update(5, array('name'=>lang('group_5')));
		group_update(6, array('name'=>lang('group_6')));
		group_update(7, array('name'=>lang('group_7')));
		group_update(101, array('name'=>lang('group_101')));
		group_update(102, array('name'=>lang('group_102')));
		group_update(103, array('name'=>lang('group_103')));
		group_update(104, array('name'=>lang('group_104')));
		group_update(105, array('name'=>lang('group_105')));
		
		forum_update(1, array('name'=>lang('default_forum_name'), 'brief'=>lang('default_forum_brief')));
				
		xn_mkdir(APP_PATH.'upload/tmp', 0777);
		xn_mkdir(APP_PATH.'upload/attach', 0777);
		xn_mkdir(APP_PATH.'upload/avatar', 0777);
		xn_mkdir(APP_PATH.'upload/forum', 0777);
		
		message(0, jump(lang('conguralation_installed'), '../'));
	}
}


?>
