<?php

function_exists('set_magic_quotes_runtime') AND set_magic_quotes_runtime(0);
$dir = '../';


$s = php_strip_whitespace($dir.'db_mysql.class.php');
$s .= php_strip_whitespace($dir.'db_pdo_mysql.class.php');
$s .= php_strip_whitespace($dir.'db_pdo_sqlite.class.php');
$s .= php_strip_whitespace($dir.'cache_apc.class.php');
$s .= php_strip_whitespace($dir.'cache_memcached.class.php');
$s .= php_strip_whitespace($dir.'cache_mysql.class.php');
$s .= php_strip_whitespace($dir.'cache_redis.class.php');
$s .= php_strip_whitespace($dir.'cache_xcache.class.php');
$s .= php_strip_whitespace($dir.'cache_yac.class.php');

$s .= php_strip_whitespace($dir.'db.func.php');
$s .= php_strip_whitespace($dir.'cache.func.php');
$s .= php_strip_whitespace($dir.'image.func.php');
$s .= php_strip_whitespace($dir.'array.func.php');
$s .= php_strip_whitespace($dir.'xn_encrypt.func.php');
$s .= php_strip_whitespace($dir.'misc.func.php');

$s = substr($s, 8, -2);

$vnbb = file_get_contents($dir.'vnbb.php');
$before = '// hook vnbb_include_before.php';
$after = '// hook vnbb_include_after.php';
$pre = substr($vnbb, 0, strpos($vnbb, $before) + 1 + strlen($before));
$suffix = substr($vnbb, strpos($vnbb, $after));
$vnbb_min = trim($pre)."\r\n\r\n".trim($s)."\r\n\r\n".trim($suffix);

file_put_contents($dir.'vnbb.min.php', $vnbb_min);

echo 'ok';
