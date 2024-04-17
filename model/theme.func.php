<?php

$theme_paths = array();
$themes = array();

function theme_init() {
	global $theme_srcfiles, $theme_paths, $themes;
	
	$theme_paths = glob(APP_PATH.'theme/*', GLOB_ONLYDIR);
	if(is_array($theme_paths)) {
		foreach($theme_paths as $path) {
			$dir = file_name($path);
			$conffile = $path."/conf.json";
			if(!is_file($conffile)) continue;
			$arr = xn_json_decode(file_get_contents($conffile));
			if(empty($arr)) continue;
			$themes[$dir] = $arr;
			
			$themes[$dir]['hooks'] = array();
			$hookpaths = glob(APP_PATH."theme/$dir/hook/*.*"); // path
			if(is_array($hookpaths)) {
				foreach($hookpaths as $hookpath) {
					$hookname = file_name($hookpath);
					$themes[$dir]['hooks'][$hookname] = $hookpath;
				}
			}
			
			$themes[$dir] = theme_read_by_dir($dir);
		}
	}
}

function theme_dependencies($dir) {
	global $theme_srcfiles, $theme_paths, $themes;
	$theme = $themes[$dir];
	$dependencies = $theme['dependencies'];
	
	$arr = array();
	foreach($dependencies as $_dir=>$version) {
		if(!isset($themes[$_dir]) || !$themes[$_dir]['enable']) {
			$arr[$_dir] = $version;
		}
	}
	return $arr;
}

function theme_by_dependencies($dir) {
	global $themes;
	
	$arr = array();
	foreach($themes as $_dir=>$theme) {
		if(isset($theme['dependencies'][$dir]) && $theme['enable']) {
			$arr[$_dir] = $theme['version'];
		}
	}
	return $arr;
}

function theme_enable($dir) {
	global $themes;
	
	if(!isset($themes[$dir])) {
		return FALSE;
	}
	
	$themes[$dir]['enable'] = 1;
	
	//theme_overwrite($dir, 'install');
	//theme_hook($dir, 'install');
	
	file_replace_var(APP_PATH."theme/$dir/conf.json", array('enable'=>1), TRUE);
	
	theme_clear_tmp_dir();
	
	return TRUE;
}

function theme_clear_tmp_dir() {
	global $conf;
	rmdir_recusive($conf['tmp_path'], TRUE);
	xn_unlink($conf['tmp_path'].'model.min.php');
}

function theme_disable($dir) {
	global $themes;
	
	if(!isset($themes[$dir])) {
		return FALSE;
	}
	
	$themes[$dir]['enable'] = 0;
	
	//theme_overwrite($dir, 'unstall');
	//theme_hook($dir, 'unstall');
	
	file_replace_var(APP_PATH."theme/$dir/conf.json", array('enable'=>0), TRUE);
	
	theme_clear_tmp_dir();
	
	return TRUE;
}

function theme_install_all() {
	global $themes;
	
	foreach ($themes as $dir=>$theme) {
		theme_install($dir);
	}
}

function theme_unstall_all() {
	global $themes;
	
	foreach ($themes as $dir=>$theme) {
		theme_unstall($dir);
	}
}

function theme_install($dir) {
	global $themes, $conf;
	
	if(!isset($themes[$dir])) {
		return FALSE;
	}
	
	$themes[$dir]['installed'] = 1;
	$themes[$dir]['enable'] = 1;
	
	//theme_overwrite($dir, 'install');
	
	//theme_hook($dir, 'install');
	
	file_replace_var(APP_PATH."theme/$dir/conf.json", array('installed'=>1, 'enable'=>1), TRUE);
	
	theme_clear_tmp_dir();
	
	return TRUE;
}

function theme_unstall($dir) {
	global $themes;
	
	if(!isset($themes[$dir])) {
		return TRUE;
	}
	
	$themes[$dir]['installed'] = 0;
	$themes[$dir]['enable'] = 0;
	
	//theme_overwrite($dir, 'unstall');
	
	//theme_hook($dir, 'unstall');
	
	file_replace_var(APP_PATH."theme/$dir/conf.json", array('installed'=>0, 'enable'=>0), TRUE);
	
	theme_clear_tmp_dir();
	
	return TRUE;
}

function theme_paths_enabled() {
	static $return_paths;
	if(empty($return_paths)) {
		$return_paths = array();
		$theme_paths = glob(APP_PATH.'theme/*', GLOB_ONLYDIR);
		if(empty($theme_paths)) return array();
		foreach($theme_paths as $path) {
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


function theme_read_by_dir($dir, $local_first = TRUE) {
	global $themes;

	$local = array_value($themes, $dir, array());
	if(empty($local)) return array();
	if(empty($local)) $local_first = FALSE;
	
	//!isset($theme['dir']) && $theme['dir'] = '';
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
		$theme = $local;
	}
	$theme['icon_url'] = "../theme/$dir/icon.png";
	$theme['setting_url'] = $theme['installed'] && is_file("../theme/$dir/setting.php") ? "theme-setting-$dir.htm" : "";
	$theme['downloaded'] = isset($themes[$dir]);
	return $theme;
}

function theme_siteid() {
	global $conf;
	$auth_key = $conf['auth_key'];
	$siteip = _SERVER('SERVER_ADDR');
	$siteid = md5($auth_key.$siteip);
	return $siteid;
}
?>
