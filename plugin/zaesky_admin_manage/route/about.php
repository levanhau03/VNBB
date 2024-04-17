<?php

!defined('DEBUG') AND exit('Access Denied.');

if($method == 'GET') {
  $header['title'] = lang('admin_header_about');
	$header['mobile_title'] =lang('admin_header_about');
include _include(APP_PATH."plugin/zaesky_admin_manage/view/about.htm");
}
?>