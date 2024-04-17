<?php

$g_include_slot_kv = array();
function _include($srcfile) {
	global $conf;
	$len = strlen(APP_PATH);
	$tmpfile = $conf['tmp_path'].substr(str_replace('/', '_', $srcfile), $len);
	if(!is_file($tmpfile) || DEBUG > 1) {
		$s = compile_srcfile($srcfile);
		$g_include_slot_kv = array();
		for($i = 0; $i < 10; $i++) {
			$s = preg_replace_callback('#<template\sinclude="(.*?)">(.*?)</template>#is', '_include_callback_1', $s);
			if(strpos($s, '<template') === FALSE) break;
		}
		file_put_contents_try($tmpfile, $s);
		
		$s = compile_srcfile($tmpfile);
		file_put_contents_try($tmpfile, $s);
		
	}
	return $tmpfile;
}

function _include_callback_1($m) {
	global $g_include_slot_kv;
	$r = file_get_contents($m[1]);
	preg_match_all('#<slot\sname="(.*?)">(.*?)</slot>#is', $m[2], $m2);
	if(!empty($m2[1])) {
		$kv = array_combine($m2[1], $m2[2]);
		$g_include_slot_kv += $kv;
		foreach($g_include_slot_kv as $slot=>$content) {
			$r = preg_replace('#<slot\sname="'.$slot.'"\s*/>#is', $content, $r);
		}
	}
	return $r;
}

function compile_srcfile($srcfile) {
	global $conf;
	if(!empty($conf['disabled_plugin'])) {
		$s = file_get_contents($srcfile);
		return $s;
	}
	if(!empty($conf['disabled_theme'])) {
		$s = file_get_contents($srcfile);
		return $s;
	}
	$srcfile = find_overwrite($srcfile);
	$s = file_get_contents($srcfile);
	
	for($i = 0; $i < 10; $i++) {
		if(strpos($s, '<!--{hook') !== FALSE || strpos($s, '// hook') !== FALSE) {
			$s = preg_replace('#<!--{hook\s+(.*?)}-->#', '// hook \\1', $s);
			$s = preg_replace_callback('#//\s*hook\s+(\S+)#is', 'compile_srcfile_callback', $s);
		} else {
			break;
		}
	}
	return $s;
}

function find_overwrite($srcfile) {
	$plugin_paths = plugin_paths_enabled();
	$theme_paths = theme_paths_enabled();
	
	$len = strlen(APP_PATH);
	
	$returnfile = $srcfile;
	$maxrank = 0;
	foreach($plugin_paths as $path=>$pconf) {
		
		$dir = file_name($path);
		$filepath_half = substr($srcfile, $len);
		$overwrite_file = APP_PATH."plugin/$dir/overwrite/$filepath_half";
		if(is_file($overwrite_file)) {
			$rank = isset($pconf['overwrites_rank'][$filepath_half]) ? $pconf['overwrites_rank'][$filepath_half] : 0;
			if($rank >= $maxrank) {
				$returnfile = $overwrite_file;
				$maxrank = $rank;
			}
		}
	}
	foreach($theme_paths as $path=>$pconf) {
		
		$dir = file_name($path);
		$filepath_half = substr($srcfile, $len);
		$overwrite_file = APP_PATH."theme/$dir/overwrite/$filepath_half";
		if(is_file($overwrite_file)) {
			$rank = isset($pconf['overwrites_rank'][$filepath_half]) ? $pconf['overwrites_rank'][$filepath_half] : 0;
			if($rank >= $maxrank) {
				$returnfile = $overwrite_file;
				$maxrank = $rank;
			}
		}
	}
	return $returnfile;
}

function compile_srcfile_callback($m) {
	static $hooks;
	if(empty($hooks)) {
		$hooks = array();
		$plugin_paths = plugin_paths_enabled();
		$theme_paths = theme_paths_enabled();
		
		$all_paths = array_merge($plugin_paths, $theme_paths);
		foreach($all_paths as $path=>$pconf) {
			$dir = file_name($path);
			$type = strpos($path, 'plugin') !== false ? 'plugin' : 'theme';
			$hookpaths = glob(APP_PATH."$type/$dir/hook/*.*");
			if(is_array($hookpaths)) {
				foreach($hookpaths as $hookpath) {
					$hookname = file_name($hookpath);
					$rank = isset($pconf['hooks_rank']["$hookname"]) ? $pconf['hooks_rank']["$hookname"] : 0;
					$hooks[$hookname][] = array('hookpath'=>$hookpath, 'rank'=>$rank);
				}
			}
		}
		foreach ($hooks as $hookname=>$arrlist) {
			$arrlist = arrlist_multisort($arrlist, 'rank', FALSE);
			$hooks[$hookname] = arrlist_values($arrlist, 'hookpath');
		}
		
	}
	
	$s = '';
	$hookname = $m[1];
	if(!empty($hooks[$hookname])) {
		$fileext = file_ext($hookname);
		foreach($hooks[$hookname] as $path) {
			$t = file_get_contents($path);
			if($fileext == 'php' && preg_match('#^\s*<\?php\s+exit;#is', $t)) {
				$t = preg_replace('#^\s*<\?php\s*exit;(.*?)(?:\?>)?\s*$#is', '\\1', $t);			
			}
			$s .= $t;
		}
	}
	return $s;
}
?>
