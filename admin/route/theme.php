<?php

!defined('DEBUG') AND exit('Access Denied.');

include VNBB_PATH.'xn_zip.func.php';

$action = param(1);

// init theme var
theme_init();

empty($action) AND $action = 'local';

if($action == 'local') {
	
	// local theme list
	$themelist = $themes;
	
	$pagination = '';
	
	$header['title']    = lang('local_theme');
	$header['mobile_title'] = lang('local_theme');
	
	
	include _include(ADMIN_PATH."view/htm/theme_list.htm");

} elseif($action == 'read') {

	$dir = param_word(2);
	$siteid = theme_siteid();
	
	$theme = theme_read_by_dir($dir);
	empty($theme) AND message(-1, lang('theme_not_exists'));
	
	$islocal = theme_is_local($dir);
	
	$tab = !$islocal;
	$header['title']    = lang('theme_detail').'-'.$theme['name'];
	$header['mobile_title'] = $theme['name'];
	include _include(ADMIN_PATH."view/htm/theme_read.htm");
	
} elseif($action == 'install') {
	
	theme_lock_start();
	
	$dir = param_word(2);
	theme_check_exists($dir);
	$name = $themes[$dir]['name'];
	
	// check directory writable
	//theme_check_dir_is_writable();
	
	// check theme dependency
	theme_check_dependency($dir, 'install');
	
	// install theme
	theme_install($dir);
	
	$installfile = APP_PATH."theme/$dir/install.php";
	if(is_file($installfile)) {
		include _include($installfile);
	}
	
	theme_lock_end();

	// automatically unstall other theme.
	if(strpos($dir, '_theme_') !== FALSE) {
		foreach($themes as $_dir => $_theme) {
			if($dir == $_dir) continue;
			if(strpos($_dir, '_theme_') !== FALSE) {
				theme_unstall($_dir);
			}
		}
	} else {
		$suffix = substr($dir, strpos($dir, '_'));
		foreach($themes as $_dir => $_theme) {
			if($dir == $_dir) continue;
			$_suffix = substr($_dir, strpos($_dir, '_'));
			if($suffix == $_suffix) {
				theme_unstall($_dir);
			}
		}
	}
	
	$msg = lang('theme_install_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 3));
	
} elseif($action == 'unstall') {
	
	theme_lock_start();
	
	$dir = param_word(2);
	theme_check_exists($dir);
	$name = $themes[$dir]['name'];
	
	// theme_check_dir_is_writable();
	
	theme_check_dependency($dir, 'unstall');
	
	theme_unstall($dir);
	
	$unstallfile = APP_PATH."theme/$dir/unstall.php";
	if(is_file($unstallfile)) {
		include _include($unstallfile);
	}
	
	//!DEBUG && rmdir_recusive("../theme/$dir");
	
	theme_lock_end();
	
	$msg = lang('theme_unstall_sucessfully', array('name'=>$name, 'dir'=>"theme/$dir"));
	message(0, jump($msg, http_referer(), 5));
	
} elseif($action == 'enable') {
	
	theme_lock_start();
	
	$dir = param_word(2);
	theme_check_exists($dir);
	$name = $themes[$dir]['name'];
	
	//theme_check_dir_is_writable();
	
	theme_check_dependency($dir, 'install');
	
	theme_enable($dir);
	
	theme_lock_end();
	
	$msg = lang('theme_enable_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 1));
	
} elseif($action == 'disable') {
	
	theme_lock_start();
	
	$dir = param_word(2);
	theme_check_exists($dir);
	$name = $themes[$dir]['name'];
	
	//theme_check_dir_is_writable();
	
	theme_check_dependency($dir, 'unstall');
	
	theme_disable($dir);
	
	theme_lock_end();
	
	$msg = lang('theme_disable_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 3));
	
} elseif($action == 'setting') {
	
	$dir = param_word(2);
	theme_check_exists($dir);
	$name = $themes[$dir]['name'];
	
	include _include(APP_PATH."theme/$dir/setting.php");
}


function theme_check_dependency($dir, $action = 'install') {
	global $themes;
	$name = $themes[$dir]['name'];
	if($action == 'install') {
		$arr = theme_dependencies($dir);
		if(!empty($arr)) {
			$s = theme_dependency_arr_to_links($arr);
			$msg = lang('theme_dependency_following', array('name'=>$name, 's'=>$s));
			message(-1, $msg);
		}
	} else {
		$arr = theme_by_dependencies($dir);
		if(!empty($arr)) {
			$s = theme_dependency_arr_to_links($arr);
			$msg = lang('theme_being_dependent_cant_delete', array('name'=>$name, 's'=>$s));
			message(-1, $msg);
		}
	}
}

function theme_dependency_arr_to_links($arr) {
	global $themes;
	$s = '';
	foreach($arr as $dir=>$version) {
		//if(!isset($themes[$dir])) continue;
		$name = isset($themes[$dir]['name']) ? $themes[$dir]['name'] : $dir;
		$url = url("theme-read-$dir");
		$s .= " <a href=\"$url\">[{$name}]</a> ";
	}
	return $s;
}


function theme_is_local($dir) {
	global $themes;
	return isset($themes[$dir]) ? TRUE : FALSE;
}

function theme_check_exists($dir, $local = TRUE) {
	global $themes;
	!is_word($dir) AND message(-1, lang('theme_name_error'));
	if($local) {
		!isset($themes[$dir]) AND message(-1, lang('theme_not_exists'));
	}
}

function theme_lock_start() {
	global $route, $action;
	!xn_lock_start($route.'_'.$action) AND message(-1, lang('theme_task_locked'));
}

function theme_lock_end() {
	global $route, $action;
	xn_lock_end($route.'_'.$action);
}

?>