<?php

!defined('DEBUG') AND exit('Access Denied.');

// hook index_inc_start.php

$sid = sess_start();

// Language
$_SERVER['lang'] = $lang = include _include(APP_PATH."lang/$conf[lang]/bbs.php");

// Group
$grouplist = group_list_cache();

// Support Token interface (token and session dual match, to facilitate the design of the REST interface, but also to facilitate the use of $_SESSION)
$uid = intval(_SESSION('uid'));
empty($uid) AND $uid = user_token_get() AND $_SESSION['uid'] = $uid;
$user = user_read($uid);

$gid = empty($user) ? 0 : intval($user['gid']);
$group = isset($grouplist[$gid]) ? $grouplist[$gid] : $grouplist[0];

// Forum
$fid = 0;
$forumlist = forum_list_cache();
$forumlist_show = forum_list_access_filter($forumlist, $gid);	// filter no permission forum
$forumarr = arrlist_key_values($forumlist_show, 'fid', 'name');

// header.inc.htm 
$header = array(
	'title'=>$conf['sitename'],
	'mobile_title'=>'',
	'mobile_link'=>'./',
	'keywords'=>'', // Search engine automatic analysis of key words, so keep it empty.
	'description'=>strip_tags($conf['sitebrief']),
	'navs'=>array(),
);

// runtime data
$runtime = runtime_init();

// restricted access
check_runlevel();

// $setting = kv_get('setting');

$route = param(0, 'index');

// hook index_inc_route_before.php

if(!defined('SKIP_ROUTE')) {
	
	// According to the frequency of the use of sorting, increase the hit rate, improve efficiency
	switch ($route) {
		// hook index_route_case_start.php
		case 'index': 	include _include(APP_PATH.'route/index.php'); 	break;
		case 'thread':	include _include(APP_PATH.'route/thread.php'); 	break;
		case 'forum': 	include _include(APP_PATH.'route/forum.php'); 	break;
		case 'user': 	include _include(APP_PATH.'route/user.php'); 	break;
		case 'my': 	include _include(APP_PATH.'route/my.php'); 	break;
		case 'attach': 	include _include(APP_PATH.'route/attach.php'); 	break;
		case 'post': 	include _include(APP_PATH.'route/post.php'); 	break;
		case 'mod': 	include _include(APP_PATH.'route/mod.php'); 	break;
		case 'browser': include _include(APP_PATH.'route/browser.php'); break;
		// hook index_route_case_end.php
		default: 
			// hook index_route_case_default.php
			include _include(APP_PATH.'route/index.php'); 	break;
			//http_404();
			/*
			!is_word($route) AND http_404();
			$routefile = _include(APP_PATH."route/$route.php");
			!is_file($routefile) AND http_404();
			include $routefile;
			*/
	}
}

// hook index_inc_end.php

?>