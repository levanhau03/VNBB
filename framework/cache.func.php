<?php

function cache_new($cacheconf) {
	if($cacheconf && !empty($cacheconf['enable'])) {
		switch ($cacheconf['type']) {
			case 'redis': 	  $cache = new cache_redis($cacheconf['redis']); 	     break;
			case 'memcached': $cache = new cache_memcached($cacheconf['memcached']); break;
			case 'pdo_mysql': 	  
			case 'mysql': 	  
					$cache = new cache_mysql($cacheconf['mysql']); break;
			case 'xcache': 	  $cache = new cache_xcache($cacheconf['xcache']); 	break;
			case 'apc': 	  $cache = new cache_apc($cacheconf['apc']); 	break;
			case 'yac': 	  $cache = new cache_yac($cacheconf['yac']); 	break;
			default: return xn_error(-1, 'Unsupported cache type:'.$cacheconf['type']);
		}
		return $cache;
	}
	return NULL;
}

function cache_get($k, $c = NULL) {
	$cache = $_SERVER['cache'];
	$c = $c ? $c : $cache;
	if(!$c) return FALSE;
	
	strlen($k) > 32 AND $k = md5($k);
	
	$k = $c->cachepre.$k;
	$r = $c->get($k);
	return $r;
}

function cache_set($k, $v, $life = 0, $c = NULL) {
	$cache = $_SERVER['cache'];
	$c = $c ? $c : $cache;
	if(!$c) return FALSE;
	
	strlen($k) > 32 AND $k = md5($k);
	
	$k = $c->cachepre.$k;
	$r = $c->set($k, $v, $life);
	return $r;
}

function cache_delete($k, $c = NULL) {
	$cache = $_SERVER['cache'];
	$c = $c ? $c : $cache;
	if(!$c) return FALSE;
	
	strlen($k) > 32 AND $k = md5($k);
	
	$k = $c->cachepre.$k;
	$r = $c->delete($k);
	return $r;
}

function cache_truncate($c = NULL) {
	$cache = $_SERVER['cache'];
	$c = $c ? $c : $cache;
	if(!$c) return FALSE;
	
	//$k = $c->cachepre.$k;
	$r = $c->truncate();
	return $r;
}

?>