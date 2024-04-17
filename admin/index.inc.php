<?php

!defined('DEBUG') AND exit('Access Denied.');

// hook admin_index_menu_after.php

// Only allow administrators to log in the background

// For unauthorized access, can default to the hacking attempt, without a friendly reminder.
if(DEBUG < 3) {
	// check admin group
	if($gid != 1) {
		setcookie('bbs_sid', '', $time - 86400);
		//http_403();
		http_location(url('../user-login'));
	}
	
	// check admin token
	admin_token_check();
}

$route = param(0, 'index');

switch ($route) {
	// hook admin_index_route_case_start.php
	case 'index':		include _include(ADMIN_PATH.'route/index.php');			break;
	case 'setting': 	include _include(ADMIN_PATH.'route/setting.php');		break;
	case 'forum': 		include _include(ADMIN_PATH.'route/forum.php'); 		break;
	case 'friendlink': 	include _include(ADMIN_PATH.'route/friendlink.php'); 	break;
	case 'group': 		include _include(ADMIN_PATH.'route/group.php'); 		break;
	case 'other':		include _include(ADMIN_PATH.'route/other.php'); 		break;
	case 'user':		include _include(ADMIN_PATH.'route/user.php'); 			break;
	case 'thread':		include _include(ADMIN_PATH.'route/thread.php'); 		break;
	case 'plugin':		include _include(ADMIN_PATH.'route/plugin.php'); 		break;
	case 'theme':		include _include(ADMIN_PATH.'route/theme.php'); 		break;
	// hook admin_index_route_case_end.php
	default: 
		// hook admin_index_route_case_default.php
		include _include(ADMIN_PATH.'route/index.php'); 	break;
		/*
		!is_word($route) AND http_404();
		$routefile = _include(ADMIN_PATH."route/$route.php");
		!is_file($routefile) AND  http_404();
		include $routefile;
		*/
}

?>
