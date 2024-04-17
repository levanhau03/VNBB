<?php

!defined('DEBUG') AND exit('Access Denied.');

// hook index_start.php

$page = param(1, 1);
$order = $conf['order_default'];
$order != 'tid' AND $order = 'lastpid';
$pagesize = $conf['pagesize'];
$active = 'default';

$thread_list_from_default = 1;

// hook index_thread_list_before.php
if($thread_list_from_default) {
	$fids = arrlist_values($forumlist_show, 'fid');
	$threads = arrlist_sum($forumlist_show, 'threads');
	$pagination = pagination(url("$route-{page}"), $threads, $page, $pagesize);
	
	// hook thread_find_by_fids_before.php
	$threadlist = thread_find_by_fids($fids, $page, $pagesize, $order, $threads);
}

if($order == $conf['order_default'] && $page == 1) {
	$toplist3 = thread_top_find(0);
	$threadlist = $toplist3 + $threadlist;
}

// filter no permission thread
thread_list_access_filter($threadlist, $gid);

// SEO
$header['title'] = $conf['sitename']; 				// site title
$header['keywords'] = ''; 					// site keyword
$header['description'] = $conf['sitebrief']; 			// site description
$_SESSION['fid'] = 0;

// hook index_end.php

include _include(APP_PATH.'view/htm/index.htm');

?>