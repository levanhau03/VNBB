<?php

!defined('DEBUG') AND exit('Access Denied.');

include VNBB_PATH.'xn_zip.func.php';

$action = param(1);

// init plugin var
plugin_init();

empty($action) AND $action = 'local';

if($action == 'local') {
	
	// local plugin list
	$pluginlist = $plugins;
	
	$pagination = '';
	
	$header['title']    = lang('local_plugin');
	$header['mobile_title'] = lang('local_plugin');
	
	
	include _include(ADMIN_PATH."view/htm/plugin_list.htm");

} elseif($action == 'read') {

	$dir = param_word(2);
	$siteid = plugin_siteid();
	
	$plugin = plugin_read_by_dir($dir);
	empty($plugin) AND message(-1, lang('plugin_not_exists'));
	
	$islocal = plugin_is_local($dir);
	
	$tab = !$islocal;
	$header['title']    = lang('plugin_detail').'-'.$plugin['name'];
	$header['mobile_title'] = $plugin['name'];
	include _include(ADMIN_PATH."view/htm/plugin_read.htm");
	
} elseif($action == 'install') {
	
	plugin_lock_start();
	
	$dir = param_word(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	//check directory writable
	//plugin_check_dir_is_writable();
	
	//check plugin dependency
	plugin_check_dependency($dir, 'install');
	
	//install plugin
	plugin_install($dir);
	
	$installfile = APP_PATH."plugin/$dir/install.php";
	if(is_file($installfile)) {
		include _include($installfile);
	}
	
	plugin_lock_end();

	//automatically unstall other theme plugin.
	if(strpos($dir, '_theme_') !== FALSE) {
		foreach($plugins as $_dir => $_plugin) {
			if($dir == $_dir) continue;
			if(strpos($_dir, '_theme_') !== FALSE) {
				plugin_unstall($_dir);
			}
		}
	} else {
		$suffix = substr($dir, strpos($dir, '_'));
		foreach($plugins as $_dir => $_plugin) {
			if($dir == $_dir) continue;
			$_suffix = substr($_dir, strpos($_dir, '_'));
			if($suffix == $_suffix) {
				plugin_unstall($_dir);
			}
		}
	}
	
	$msg = lang('plugin_install_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 3));
	
} elseif($action == 'unstall') {
	
	plugin_lock_start();
	
	$dir = param_word(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	// plugin_check_dir_is_writable();
	
	plugin_check_dependency($dir, 'unstall');
	
	plugin_unstall($dir);
	
	$unstallfile = APP_PATH."plugin/$dir/unstall.php";
	if(is_file($unstallfile)) {
		include _include($unstallfile);
	}
	
	//!DEBUG && rmdir_recusive("../plugin/$dir");
	
	plugin_lock_end();
	
	$msg = lang('plugin_unstall_sucessfully', array('name'=>$name, 'dir'=>"plugin/$dir"));
	message(0, jump($msg, http_referer(), 5));
	
} elseif($action == 'enable') {
	
	plugin_lock_start();
	
	$dir = param_word(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	//plugin_check_dir_is_writable();
	
	plugin_check_dependency($dir, 'install');
	
	plugin_enable($dir);
	
	plugin_lock_end();
	
	$msg = lang('plugin_enable_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 1));
	
} elseif($action == 'disable') {
	
	plugin_lock_start();
	
	$dir = param_word(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	//plugin_check_dir_is_writable();
	
	plugin_check_dependency($dir, 'unstall');
	
	plugin_disable($dir);
	
	plugin_lock_end();
	
	$msg = lang('plugin_disable_sucessfully', array('name'=>$name));
	message(0, jump($msg, http_referer(), 3));
	
} elseif($action == 'setting') {
	
	$dir = param_word(2);
	plugin_check_exists($dir);
	$name = $plugins[$dir]['name'];
	
	include _include(APP_PATH."plugin/$dir/setting.php");
}


function plugin_check_dependency($dir, $action = 'install') {
	global $plugins;
	$name = $plugins[$dir]['name'];
	if($action == 'install') {
		$arr = plugin_dependencies($dir);
		if(!empty($arr)) {
			$s = plugin_dependency_arr_to_links($arr);
			$msg = lang('plugin_dependency_following', array('name'=>$name, 's'=>$s));
			message(-1, $msg);
		}
	} else {
		$arr = plugin_by_dependencies($dir);
		if(!empty($arr)) {
			$s = plugin_dependency_arr_to_links($arr);
			$msg = lang('plugin_being_dependent_cant_delete', array('name'=>$name, 's'=>$s));
			message(-1, $msg);
		}
	}
}

function plugin_dependency_arr_to_links($arr) {
	global $plugins;
	$s = '';
	foreach($arr as $dir=>$version) {
		//if(!isset($plugins[$dir])) continue;
		$name = isset($plugins[$dir]['name']) ? $plugins[$dir]['name'] : $dir;
		$url = url("plugin-read-$dir");
		$s .= " <a href=\"$url\">[{$name}]</a> ";
	}
	return $s;
}


function plugin_is_local($dir) {
	global $plugins;
	return isset($plugins[$dir]) ? TRUE : FALSE;
}

function plugin_check_exists($dir, $local = TRUE) {
	global $plugins;
	!is_word($dir) AND message(-1, lang('plugin_name_error'));
	if($local) {
		!isset($plugins[$dir]) AND message(-1, lang('plugin_not_exists'));
	}
}

function plugin_lock_start() {
	global $route, $action;
	!xn_lock_start($route.'_'.$action) AND message(-1, lang('plugin_task_locked'));
}

function plugin_lock_end() {
	global $route, $action;
	xn_lock_end($route.'_'.$action);
}

?>