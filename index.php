<?php
// 0: Production mode; 1: Developer mode; 2: Plugin developement mode;
!defined('DEBUG') AND define('DEBUG', 0);
define('APP_PATH', dirname(__FILE__).'/'); // __DIR__
!defined('ADMIN_PATH') AND define('ADMIN_PATH', APP_PATH.'admin/');
!defined('VNBB_PATH') AND define('VNBB_PATH', APP_PATH.'framework/');

// !ini_get('zlib.output_compression') AND ob_start('ob_gzhandler');

//ob_start('ob_gzhandler');
$conf = (@include APP_PATH.'conf/conf.php') OR exit('<script>window.location="install/"</script>');

!isset($conf['user_create_on']) AND $conf['user_create_on'] = 1;
!isset($conf['logo_mobile_url']) AND $conf['logo_mobile_url'] = 'view/img/logo.png';
!isset($conf['logo_pc_url']) AND $conf['logo_pc_url'] = 'view/img/logo.png';
!isset($conf['logo_water_url']) AND $conf['logo_water_url'] = 'view/img/water-small.png';
$conf['version'] = '1.0';		// Avoid manually modifying conf/conf.php

substr($conf['log_path'], 0, 2) == './' AND $conf['log_path'] = APP_PATH.$conf['log_path']; 
substr($conf['tmp_path'], 0, 2) == './' AND $conf['tmp_path'] = APP_PATH.$conf['tmp_path']; 
substr($conf['upload_path'], 0, 2) == './' AND $conf['upload_path'] = APP_PATH.$conf['upload_path']; 

$_SERVER['conf'] = $conf;

if(DEBUG > 1) {
	include VNBB_PATH.'vnbb.php';
} else {
	include VNBB_PATH.'vnbb.min.php';
}

//try to connect database
//db_connect() OR exit($errstr);

include APP_PATH.'model/extension.func.php';
include APP_PATH.'model/plugin.func.php';
include APP_PATH.'model/theme.func.php';
include _include(APP_PATH.'model.inc.php');
include _include(APP_PATH.'index.inc.php');

//file_put_contents((ini_get('xhprof.output_dir') ? : '/tmp') . '/' . uniqid() . '.xhprof.xhprof', serialize(xhprof_disable()));

?>
