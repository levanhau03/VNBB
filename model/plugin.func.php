<?php

//$plugin_srcfiles = array();
$plugin_paths = array();
$plugins = array();

function plugin_init() {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	/*$plugin_srcfiles = array_merge(
		glob(APP_PATH.'model/*.php'), 
		glob(APP_PATH.'route/*.php'), 
		glob(APP_PATH.'view/htm/*.*'), 
		glob(ADMIN_PATH.'route/*.php'), 
		glob(ADMIN_PATH.'view/htm/*.*'),
		glob(APP_PATH.'lang/en-us/*.*'),
		glob(APP_PATH.'lang/vi-vn/*.*'),
		array(APP_PATH.'model.inc.php')
	);
	foreach($plugin_srcfiles as $k=>$file) {
		$filename = file_name($file);
		if(is_backfile($filename)) {
			unset($plugin_srcfiles[$k]);
		}
	}*/
	
	$plugin_paths = glob(APP_PATH.'plugin/*', GLOB_ONLYDIR);
	if(is_array($plugin_paths)) {
		foreach($plugin_paths as $path) {
			$dir = file_name($path);
			$conffile = $path."/conf.json";
			if(!is_file($conffile)) continue;
			$arr = xn_json_decode(file_get_contents($conffile));
			if(empty($arr)) continue;
			$plugins[$dir] = $arr;
			
			$plugins[$dir]['hooks'] = array();
			$hookpaths = glob(APP_PATH."plugin/$dir/hook/*.*"); // path
			if(is_array($hookpaths)) {
				foreach($hookpaths as $hookpath) {
					$hookname = file_name($hookpath);
					$plugins[$dir]['hooks'][$hookname] = $hookpath;
				}
			}
			
			$plugins[$dir] = plugin_read_by_dir($dir);
		}
	}
}

function plugin_dependencies($dir) {
	global $plugin_srcfiles, $plugin_paths, $plugins;
	$plugin = $plugins[$dir];
	$dependencies = $plugin['dependencies'];
	
	$arr = array();
	foreach($dependencies as $_dir=>$version) {
		if(!isset($plugins[$_dir]) || !$plugins[$_dir]['enable']) {
			$arr[$_dir] = $version;
		}
	}
	return $arr;
}

function plugin_by_dependencies($dir) {
	global $plugins;
	
	$arr = array();
	foreach($plugins as $_dir=>$plugin) {
		if(isset($plugin['dependencies'][$dir]) && $plugin['enable']) {
			$arr[$_dir] = $plugin['version'];
		}
	}
	return $arr;
}

function plugin_enable($dir) {
	global $plugins;
	
	if(!isset($plugins[$dir])) {
		return FALSE;
	}
	
	$plugins[$dir]['enable'] = 1;
	
	//plugin_overwrite($dir, 'install');
	//plugin_hook($dir, 'install');
	
	file_replace_var(APP_PATH."plugin/$dir/conf.json", array('enable'=>1), TRUE);
	
	plugin_clear_tmp_dir();
	
	return TRUE;
}

function plugin_clear_tmp_dir() {
	global $conf;
	rmdir_recusive($conf['tmp_path'], TRUE);
	xn_unlink($conf['tmp_path'].'model.min.php');
}

function plugin_disable($dir) {
	global $plugins;
	
	if(!isset($plugins[$dir])) {
		return FALSE;
	}
	
	$plugins[$dir]['enable'] = 0;
	
	//plugin_overwrite($dir, 'unstall');
	//plugin_hook($dir, 'unstall');
	
	file_replace_var(APP_PATH."plugin/$dir/conf.json", array('enable'=>0), TRUE);
	
	plugin_clear_tmp_dir();
	
	return TRUE;
}

function plugin_install_all() {
	global $plugins;
	
	foreach ($plugins as $dir=>$plugin) {
		plugin_install($dir);
	}
}

function plugin_unstall_all() {
	global $plugins;
	
	foreach ($plugins as $dir=>$plugin) {
		plugin_unstall($dir);
	}
}

function plugin_install($dir) {
	global $plugins, $conf;
	
	if(!isset($plugins[$dir])) {
		return FALSE;
	}
	
	$plugins[$dir]['installed'] = 1;
	$plugins[$dir]['enable'] = 1;
	
	//plugin_overwrite($dir, 'install');
	
	//plugin_hook($dir, 'install');
	
	file_replace_var(APP_PATH."plugin/$dir/conf.json", array('installed'=>1, 'enable'=>1), TRUE);
	
	plugin_clear_tmp_dir();
	
	return TRUE;
}

// copy from plugin_install
function plugin_unstall($dir) {
	global $plugins;
	
	if(!isset($plugins[$dir])) {
		return TRUE;
	}
	
	$plugins[$dir]['installed'] = 0;
	$plugins[$dir]['enable'] = 0;
	
	//plugin_overwrite($dir, 'unstall');
	
	//plugin_hook($dir, 'unstall');
	
	file_replace_var(APP_PATH."plugin/$dir/conf.json", array('installed'=>0, 'enable'=>0), TRUE);
	
	plugin_clear_tmp_dir();
	
	return TRUE;
}

function plugin_paths_enabled() {
	static $return_paths;
	if(empty($return_paths)) {
		$return_paths = array();
		$plugin_paths = glob(APP_PATH.'plugin/*', GLOB_ONLYDIR);
		if(empty($plugin_paths)) return array();
		foreach($plugin_paths as $path) {
			$conffile = $path."/conf.json";
			if(!is_file($conffile)) continue;
			$pconf = xn_json_decode(file_get_contents($conffile));
			if(empty($pconf)) continue;
			if(empty($pconf['enable']) || empty($pconf['installed'])) continue;
			$return_paths[$path] = $pconf;
		}
	}
	return $return_paths;
}


function plugin_read_by_dir($dir, $local_first = TRUE) {
	global $plugins;

	$local = array_value($plugins, $dir, array());
	if(empty($local)) return array();
	if(empty($local)) $local_first = FALSE;
	
	//!isset($plugin['dir']) && $plugin['dir'] = '';
	!isset($local['name']) && $local['name'] = '';
	!isset($local['brief']) && $local['brief'] = '';
	!isset($local['version']) && $local['version'] = '1.0';
	!isset($local['bbs_version']) && $local['bbs_version'] = '1.0';
	!isset($local['installed']) && $local['installed'] = 0;
	!isset($local['enable']) && $local['enable'] = 0;
	!isset($local['hooks']) && $local['hooks'] = array();
	!isset($local['hooks_rank']) && $local['hooks_rank'] = array();
	!isset($local['dependencies']) && $local['dependencies'] = array();
	!isset($local['icon_url']) && $local['icon_url'] = '';
	!isset($local['have_setting']) && $local['have_setting'] = 0;
	!isset($local['setting_url']) && $local['setting_url'] = 0;
	
	if($local_first) {
		$plugin = $local;
	}
	$plugin['icon_url'] = "../plugin/$dir/icon.png";
	$plugin['setting_url'] = $plugin['installed'] && is_file("../plugin/$dir/setting.php") ? "plugin-setting-$dir.htm" : "";
	$plugin['downloaded'] = isset($plugins[$dir]);
	return $plugin;
}

function plugin_siteid() {
	global $conf;
	$auth_key = $conf['auth_key'];
	$siteip = _SERVER('SERVER_ADDR');
	$siteid = md5($auth_key.$siteip);
	return $siteid;
}
?>
