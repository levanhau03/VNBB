<?php


$g_static_users = array();

// hook model_user_start.php

function user__create($arr) {
	// hook model_user__create_start.php
	$r = db_insert('user', $arr);
	// hook model_user__create_end.php
	return $r;
}

function user__update($uid, $update) {
	// hook model_user__update_start.php
	$r = db_update('user', array('uid'=>$uid), $update);
	// hook model_user__update_end.php
	return $r;
}

function user__read($uid) {
	// hook model_user__read_start.php
	$user = db_find_one('user', array('uid'=>$uid));
	// hook model_user__read_end.php
	return $user;
}

function user__delete($uid) {
	// hook model_user__delete_start.php
	$r = db_delete('user', array('uid'=>$uid));
	// hook model_user__delete_end.php
	return $r;
}

function user_create($arr) {
	// hook model_user_create_start.php
	global $conf;
	$r = user__create($arr);
	
	runtime_set('users+', 1);
	runtime_set('todayusers+', 1);
	
	// hook model_user_create_end.php
	return $r;
}

function user_update($uid, $arr) {
	// hook model_user_update_start.php
	global $conf, $g_static_users;
	$r = user__update($uid, $arr);
	!in_array($conf['cache']['type'], array('mysql', 'pdo_mysql')) AND cache_delete("user-$uid");
	isset($g_static_users[$uid]) AND $g_static_users[$uid] = array_merge($g_static_users[$uid], $arr);
	
	// hook model_user_update_end.php
	return $r;
}

function user_read($uid) {
	global $g_static_users;
	if(empty($uid)) return array();
	$uid = intval($uid);
	// hook model_user_read_start.php
	$user = user__read($uid);
	user_format($user);
	$g_static_users[$uid] = $user;
	// hook model_user_read_end.php
	return $user;
}


function user_read_cache($uid) {
	global $conf, $g_static_users;
	if(isset($g_static_users[$uid])) return $g_static_users[$uid];
	
	// hook model_user_read_cache_start.php
	
	if($uid == 0) return user_guest();
	
	if(!in_array($conf['cache']['type'], array('mysql', 'pdo_mysql'))) {
		$r = cache_get("user-$uid");
		if($r === NULL) {
			$r = user_read($uid);
			cache_set("user-$uid", $r);
		}
	} else {
		$r = user_read($uid);
	}
	
	$g_static_users[$uid] = $r ? $r : user_guest();
	
	// hook model_user_read_cache_end.php
	return $g_static_users[$uid];
}

function user_delete($uid) {
	global $conf, $g_static_users;
	// hook model_user_delete_start.php
	
	$user = user_read($uid);
	if(empty($user)) return NULL;
	
	$threadlist = mythread_find_by_uid($uid, 1, 1000);
	foreach($threadlist as $thread) {
		thread_delete($thread['tid']);
	}
	
	post_delete_by_uid($uid);
	
	attach_delete_by_uid($uid);
	
	$user['avatar_path'] AND xn_unlink($user['avatar_path']);
	
	$r = user__delete($uid);
	
	!in_array($conf['cache']['type'], array('mysql', 'pdo_mysql')) AND cache_delete("user-$uid");
	if(isset($g_static_users[$uid])) unset($g_static_users[$uid]);
	
	runtime_set('users-', 1);
	
	// hook model_user_delete_end.php
	return $r;
}

function user_find($cond = array(), $orderby = array(), $page = 1, $pagesize = 20) {
	global $g_static_users;
	// hook model_user_find_start.php
	$userlist = db_find('user', $cond, $orderby, $page, $pagesize);
	if($userlist) foreach ($userlist as &$user) {
		$g_static_users[$user['uid']] = $user;
		user_format($user);
	}
	// hook model_user_find_end.php
	return $userlist;
}


function user_read_by_email($email) {
	global $g_static_users;
	// hook model_user_read_by_email_start.php
	$user = db_find_one('user', array('email'=>$email));
    if ($user) {
	user_format($user);
	$g_static_users[$user['uid']] = $user;
	}
	// hook model_user_read_by_email_end.php
	return $user;
}

function user_read_by_username($username) {
	global $g_static_users;
	// hook model_user_read_by_username_start.php
	$user = db_find_one('user', array('username'=>$username));
	if ($user) {
	user_format($user);
	$g_static_users[$user['uid']] = $user;
	}
	// hook model_user_read_by_username_end.php
	return $user;
}

function user_count($cond = array()) {
	// hook model_user_count_start.php
	$n = db_count('user', $cond);
	// hook model_user_count_end.php
	return $n;
}

function user_maxid($cond = array()) {
	// hook model_user_maxid_start.php
	$n = db_maxid('user', 'uid');
	// hook model_user_maxid_end.php
	return $n;
}

function user_format(&$user) {
	global $conf, $grouplist;
	if(empty($user)) return;

	// hook model_user_format_start.php
	
	$user['create_ip_fmt']   = long2ip(intval($user['create_ip']));
	$user['create_date_fmt'] = empty($user['create_date']) ? '0000-00-00' : date('Y-m-d', $user['create_date']);
	$user['login_ip_fmt']    = long2ip(intval($user['login_ip']));
	$user['login_date_fmt'] = empty($user['login_date']) ? '0000-00-00' : date('Y-m-d', $user['login_date']);
	
	$user['groupname'] = group_name($user['gid']);
	
	$dir = substr(sprintf("%09d", $user['uid']), 0, 3);
	// hook model_user_format_avatar_url_before.php
	$user['avatar_url'] = $user['avatar'] ? $conf['upload_url']."avatar/$dir/$user[uid].png?".$user['avatar'] : 'view/img/avatar.png';
	$user['avatar_path'] = $user['avatar'] ? $conf['upload_path']."avatar/$dir/$user[uid].png?".$user['avatar'] : '';

	$user['online_status'] = 1;
	// hook model_user_format_end.php
}


function user_guest() {
	global $conf;
	static $guest = NULL;
	// hook model_user_guest_start.php
	
	if($guest) return $guest;
	$guest = array (
		'uid' => 0,
		'gid' => 0,
		'groupname' => lang('guest_group'),
		'username' => lang('guest'),
		'avatar_url' => 'view/img/avatar.png',
		'create_ip_fmt' => '',
		'create_date_fmt' => '',
		'login_date_fmt' => '',
		'email' => '',
		
		'threads' => 0,
		'posts' => 0,
	);
	
	// hook model_user_guest_end.php
	return $guest;
}

function user_update_group($uid) {
	global $conf, $grouplist;
	$user = user_read_cache($uid);
	if($user['gid'] < 100) return FALSE;
	
	// hook model_user_update_group_start.php
	
	foreach($grouplist as $group) {
		if($group['gid'] < 100) continue;
		$n = $user['posts'] + $user['threads'];
		// hook model_user_update_group_policy_start.php
		if($n > $group['creditsfrom'] && $n < $group['creditsto']) {
			if($user['gid'] != $group['gid']) {
				user_update($uid, array('gid'=>$group['gid']));
				return TRUE;
			}
		}
	}
	
	// hook model_user_update_group_end.php
	return FALSE;
}

// uids: 1,2,3,4 -> array()
function user_find_by_uids($uids) {
	// hook model_user_find_by_uids_start.php
	$uids = trim($uids);
	if(empty($uids)) return array();
	$arr = explode(',', $uids);
	$r = array();
	foreach($arr as $_uid) {
		$user = user_read_cache($_uid);
		if(empty($user)) continue;
		$r[$user['uid']] = $user;
	}
	// hook model_user_find_by_uids_end.php
	return $r;
}

function user_safe_info($user) {
	// hook model_user_safe_info_start.php
	unset($user['password']);
	unset($user['email']);
	unset($user['salt']);
	unset($user['password_sms']);
	unset($user['idnumber']);
	unset($user['realname']);
	unset($user['qq']);
	unset($user['mobile']);
	unset($user['create_ip']);
	unset($user['create_ip_fmt']);
	unset($user['create_date']);
	unset($user['create_date_fmt']);
	unset($user['login_ip']);
	unset($user['login_date']);
	unset($user['login_ip_fmt']);
	unset($user['login_date_fmt']);
	unset($user['logins']);
	// hook model_user_safe_info_end.php
	return $user;
}


function user_token_get() {
	global $time;
	$_uid = user_token_get_do();
	// hook model_user_token_get_start.php

	if(!$_uid) {
		//setcookie('bbs_token', '', $time - 86400, '');
	}
	
	// hook model_user_token_get_end.php
	
	return $_uid;
}

function user_token_get_do() {
	global $time, $ip, $conf;
	$token = param('bbs_token');
	
	// hook model_user_token_get_do_start.php
	
	if(empty($token)) return FALSE;
	$tokenkey = md5(xn_key());
	$s = xn_decrypt($token, $tokenkey);
	if(empty($s)) return FALSE;
	$arr = explode("\t", $s);
	if(count($arr) != 4) return FALSE;
	list($_ip, $_time, $_uid, $_pwd) = $arr;
	//if($ip != $_ip) return FALSE;
	//if($time - $_time > 86400) return FALSE;
	if($time - $_time > 1800) {
		$user = user_read($_uid);
		if(empty($user)) return 0;
		if(md5($user['password']) != $_pwd) {
			return 0;
		}
	}
	
	
	
	// hook model_user_token_get_do_end.php
	
	return $_uid;	
}

function user_token_set($uid) {
	global $time, $conf;
	if(empty($uid)) return;
	$token = user_token_gen($uid);
	setcookie('bbs_token', $token, $time + 8640000, $conf['cookie_path']);
	
	// hook model_user_token_set_end.php
}

function user_token_clear() {
	global $time, $conf;
	setcookie('bbs_token', '', $time - 8640000, $conf['cookie_path']);
	
	// hook model_user_token_clear_end.php
}

function user_token_gen($uid) {
	global $ip, $time, $conf;
	
	// hook model_user_token_gen_start.php
	
	$user = user_read($uid);
	$pwd = md5($user['password']);
	$tokenkey = md5(xn_key());
	$token = xn_encrypt("$ip	$time	$uid	$pwd", $tokenkey);
	
	// hook model_user_token_gen_end.php
	
	return $token;
}


function user_login_check() {
	global $user;
	
	// hook model_user_login_check_start.php
	
	empty($user) AND http_location(url('user-login'));
	
	// hook model_user_login_check_end.php
}



function user_http_referer() {
	// hook user_http_referer_start.php
	$referer = param('referer');
	empty($referer) AND $referer = array_value($_SERVER, 'HTTP_REFERER', '');
	
	$referer = str_replace(array('\"', '"', '<', '>', ' ', '*', "\t", "\r", "\n"), '', $referer);
	
	if(
		!preg_match('#^(http|https)://[\w\-=/\.]+/[\w\-=.%\#?]*$#is', $referer) 
		|| strpos($referer, 'user-login.htm') !== FALSE 
		|| strpos($referer, 'user-logout.htm') !== FALSE 
		|| strpos($referer, 'user-create.htm') !== FALSE 
		|| strpos($referer, 'user-setpw.htm') !== FALSE 
		|| strpos($referer, 'user-resetpw_complete.htm') !== FALSE
	) {
		$referer = './';
	}
	// hook user_http_referer_end.php
	return $referer;
}

function user_auth_check($token) {
	// hook user_auth_check_start.php
	global $time;
	$auth = param(2);
	$s = decrypt($auth);
	empty($s) AND message(-1, lang('decrypt_failed'));
	$arr = explode('-', $s);
	count($arr) != 3 AND message(-1, lang('encrypt_failed'));
	list($_ip, $_time, $_uid) = $arr;
	$_user = user_read($_uid);
	empty($_user) AND message(-1, lang('user_not_exists'));
	$time - $_time > 3600 AND message(-1, lang('link_has_expired'));
	// hook user_auth_check_end.php
	return $_user;
}


// hook model_user_end.php

?>