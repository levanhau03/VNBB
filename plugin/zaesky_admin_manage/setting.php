<?php

!defined('DEBUG') and exit('Access Denied.');

$header['title'] = lang('setting') . ' ' . strtolower(lang('admin_manage_name'));

if ($method == 'GET') {
	
	$manage_config = setting_get('admin_manage_setting');
	
	include _include(APP_PATH.'plugin/zaesky_admin_manage/view/setting.htm');
	
} else if ($method == 'POST'){
	
	$manage_config = array();
	
	$manage_config['nav_position'] = param('nav_position', 1);

	setting_set('admin_manage_setting', $manage_config); 

	message(0, jump(lang('admin_setting_config_success'), url('plugin-setting-zaesky_admin_manage')));
}

?>