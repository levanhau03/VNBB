<?php


!defined('DEBUG') AND exit('Forbidden');

// 添加插件配置
$admin_manage_setting_config = array(
	"nav_position" => 1,
);

setting_set('admin_manage_setting', $admin_manage_setting_config); 

?>