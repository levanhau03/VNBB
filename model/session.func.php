<?php 

$sid = '';
$g_session = array();	
$g_session_invalid = FALSE; // 0: valid, 1: invalid

//$g_sess_db = $db;


function sess_open($save_path, $session_name) { 
	//echo "sess_open($save_path,$session_name) \r\n";
	return true;
}

function sess_close() {
	return true;
}

function sess_read($sid) { 
	global $g_session, $longip, $time;
	//echo "sess_read() sid: $sid <br>\r\n";
	if(empty($sid)) {
		$sid = session_id();
		sess_new($sid);
		return '';
	}
	$arr = db_find_one('session', array('sid'=>$sid));
	if(empty($arr)) {
		sess_new($sid);
		return '';
	}
	if($arr['bigdata'] == 1) {
		$arr2 = db_find_one('session_data', array('sid'=>$sid));
		$arr['data'] = $arr2['data'];
	}
	$g_session = $arr;
	//return $arr ? session_decode($arr['data']) : '';
	return $arr ? $arr['data'] : '';
}

function sess_new($sid) {
	global $time, $longip, $conf, $g_session, $g_session_invalid;
	
	$agent = _SERVER('HTTP_USER_AGENT');
	
	//db_delete('session', array('ip'=>$longip));
	
	$cookie_test = _COOKIE('cookie_test');
	if($cookie_test) {
		$cookie_test_decode = xn_decrypt($cookie_test, $conf['auth_key']);
		$g_session_invalid = ($cookie_test_decode != md5($agent.$longip));
		setcookie('cookie_test', '', $time - 86400, '');
	} else {
		$cookie_test = xn_encrypt(md5($agent.$longip), $conf['auth_key']);
		setcookie('cookie_test', $cookie_test, $time + 86400, '');
		$g_session_invalid = FALSE;
		return;
	}
	
	$url = _SERVER('REQUEST_URI_NO_PATH');
	
	$arr = array(
		'sid'=>$sid,
		'uid'=>0,
		'fid'=>0,
		'url'=>$url,
		'last_date'=>$time,
		'data'=> '',
		'ip'=> $longip,
		'useragent'=> $agent,
		'bigdata'=> 0,
	);
	$g_session = $arr;
	db_insert('session', $arr);
}

function sess_restart() {
	global $sid;
	$data = sess_read($sid);
	session_decode($data);
}

function sess_save() {
	global $sid;
	sess_write($sid, TRUE);
}

function sess_write($sid, $data) {
	global $g_session, $time, $longip, $g_session_invalid, $conf;
	
	//echo "sess_write($sid, $data)";
	//if($g_session_invalid) return TRUE;
	
	$uid = _SESSION('uid');
	$fid = _SESSION('fid');
	unset($_SESSION['uid']);
	unset($_SESSION['fid']);
	
	if($data) {
		//$arr = session_decode($data);
		//unset($_SESSION['uid']);
		//unset($_SESSION['fid']);
		$data = session_encode();
	}
	
	function_exists('chdir') AND chdir(APP_PATH);
	
	$url = _SERVER('REQUEST_URI_NO_PATH');
	$agent = _SERVER('HTTP_USER_AGENT');
	$arr = array(
		'uid'=>$uid,
		'fid'=>$fid,
		'url'=>$url,
		'last_date'=>$time,
		'data'=> $data,
		'ip'=> $longip,
		'useragent'=> $agent,
		'bigdata'=> 0,
	);
	
	$session_delay_update_on = !empty($conf['session_delay_update']) && $time - $g_session['last_date'] < $conf['session_delay_update'];
	if($session_delay_update_on) {
		unset($arr['fid']);
		unset($arr['url']);
		unset($arr['last_date']);
	}
	
	$len = strlen($data);
	if($len > 255 && $g_session['bigdata'] == 0) {
		db_insert('session_data', array('sid'=>$sid));
	}
	if($len <= 255) {
		$update = array_diff_value($arr, $g_session);
		db_update('session', array('sid'=>$sid), $update);
		if(!empty($g_session) && $g_session['bigdata'] == 1) {
			db_delete('session_data', array('sid'=>$sid));
		}
	} else {
		$arr['data'] = '';
		$arr['bigdata'] = 1;
		$update = array_diff_value($arr, $g_session);
		$update AND db_update('session', array('sid'=>$sid), $update);
		$arr2 = array('data'=>$data, 'last_date'=>$time);
		if($session_delay_update_on) unset($arr2['last_date']);
		$update2 = array_diff_value($arr2, $g_session);
		$update2 AND db_update('session_data', array('sid'=>$sid), $update2);
	}
	return TRUE;
}

function sess_destroy($sid) { 
	//echo "sess_destroy($sid) \r\n";
	db_delete('session', array('sid'=>$sid));
	db_delete('session_data', array('sid'=>$sid));
	return TRUE; 
}

function sess_gc($maxlifetime) {
	global $time;
	// echo "sess_gc($maxlifetime) \r\n";
	$expiry = $time - $maxlifetime;
	db_delete('session', array('last_date'=>array('<'=>$expiry)));
	db_delete('session_data', array('last_date'=>array('<'=>$expiry)));
	return TRUE; 
}

function sess_start() {
	global $conf, $sid, $g_session;
	ini_set('session.name', 'bbs_sid');
	
	ini_set('session.use_cookies', 'On');
	ini_set('session.use_only_cookies', 'On');
	ini_set('session.cookie_domain', '');
	ini_set('session.cookie_path', '');
	ini_set('session.cookie_secure', 'Off');
	ini_set('session.cookie_lifetime', 8640000);
	ini_set('session.cookie_httponly', 'On');
	
	ini_set('session.gc_maxlifetime', $conf['online_hold_time']);
	ini_set('session.gc_probability', 1);
	ini_set('session.gc_divisor', 500);
	
	session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy', 'sess_gc'); 
	
	register_shutdown_function('session_write_close');
	
	session_start();
	
	$sid = session_id();
	
	//$_SESSION['uid'] = $g_session['uid'];
	//$_SESSION['fid'] = $g_session['fid'];
	
	//echo "sess_start() sid: $sid <br>\r\n";
	//print_r(db_find('session'));
	return $sid;
}

function online_count() {
	return db_count('session');
}

function online_find_cache() {
	return db_find('session');
}

function online_list_cache() {
	$onlinelist = cache_get('online_list');
	if($onlinelist === NULL) {
		$onlinelist = db_find('session', array('uid'=>array('>'=>0)), array('last_date'=>-1), 1, 500);
		foreach($onlinelist as &$online) {
			$user = user_read_cache($online['uid']);
			$online['username'] = $user['username'];
			$online['gid'] = $user['gid'];
			$online['ip_fmt'] = long2ip($online['ip']);
			$online['last_date_fmt'] = date('Y-n-j H:i', $online['last_date']);
		}
		cache_set('online_list', $onlinelist, 300);
	}
	return $onlinelist;
}

?>