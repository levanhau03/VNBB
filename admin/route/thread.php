<?php

!defined('DEBUG') AND exit('Access Denied.');

$action = param(1);

// hook admin_thread_start.php

$pagesize = 100;

if(empty($action) || $action == 'list') {

	$header['title'] = lang('thread_admin');
	$header['mobile_title'] = lang('thread_admin');
		
	// hook admin_thread_list_start.php
	
	$threads = $runtime['threads'];
	$page = 1;
	$totalpage = ceil($threads / $pagesize);
	
	$queueid = _SESSION('thread_find_queueid');
	$queueid AND queue_destory($queueid);
	$queueid = $time;
	$_SESSION['thread_find_queueid'] = $queueid;

	$forumlist_simple = array();
	foreach($forumlist as $k=>$v) {
		$forumlist_simple[$k] = array(
			'name'=>$v['name'],
			'threads'=>$v['threads'],
		);
	}
	
	//$queue_count = queue_count($queueid);
	
	// hook admin_thread_list_end.php
	
	include _include(ADMIN_PATH."view/htm/thread_list.htm");
	
} elseif($action == 'scan') {
	
	$queueid = _SESSION('thread_find_queueid');
	empty($queueid) AND message(-1, lang('thread_queue_not_exists'));
	
	$_uid = param('uid');
	if(!is_numeric($_uid)) {
		$_user = user_read_by_username($_uid);
		$_uid = $_user ?  $_user['uid'] : 0;
	}
	$fid = param('fid', 0);
	
	$cond = array();
	$cond['fid'] = $fid;
	$cond['create_date_start'] = strtotime(param('create_date_start'));
	$cond['create_date_end'] = strtotime(param('create_date_end'));
	$cond['uid'] = $_uid;
	$userip = param('userip');
	$cond['userip'] = $userip ? sprintf('%u', ip2long($userip)) : 0;
	$cond['keyword'] = param('keyword');
	$cond['page'] = param('page', 1);
	
	$page = $cond['page'];
	$threads = $cond['fid'] ? $forumlist[$fid]['threads'] : $runtime['threads'];
	$totalpage = ceil($threads / $pagesize);
	
	// hook admin_thread_scan_start.php
	$threadlist = thread_find_by_fid($fid, $page, $pagesize);
	
	if($page == 1) $queueid AND queue_destory($queueid);
	
	$tids = array();

	foreach($threadlist as $thread) {
		if($cond['fid'] && $thread['fid'] != $cond['fid']) continue; 
		if($cond['create_date_start'] && $thread['create_date'] < $cond['create_date_start']) continue; 
		if($cond['create_date_end'] && $thread['create_date'] > $cond['create_date_end']) continue; 
		if($cond['uid'] && $thread['uid'] != $cond['uid']) continue; 
		if($cond['userip'] && $thread['userip'] != $cond['userip']) continue; 
		//if($cond['views_start'] && $thread['views'] > $cond['views_start']) continue; 
		//if($cond['views_end'] && $thread['views'] > $cond['views_end']) continue; 
		//if($cond['posts_start'] && $thread['posts'] > $cond['posts_start']) continue; 
		//if($cond['posts_end'] && $thread['posts'] > $cond['posts_end']) continue; 
		if($cond['keyword'] && stripos($thread['subject'], $cond['keyword']) === FALSE) continue; 
		
		// hook admin_thread_scan_for.php
		
		$tids[] = $thread['tid'];
		queue_push($queueid, $thread['tid'], 86400);
	}
	
	// hook admin_thread_scan_end.php
	message(0, $tids);
	
} elseif($action == 'operation') {
		
	$queueid = _SESSION('thread_find_queueid');
	empty($queueid) AND message(-1, lang('thread_queue_not_exists'));
	
	$op = param(2);
	$tids = array();
	// hook admin_thread_operation_start.php
	for($i = 0; $i <= $pagesize; $i++) {
		$tid = queue_pop($queueid);
		if(!$tid) {
			//queue_destory($queueid);
			//unset($_SESSION['thread_find_queueid']);
			break;
			//message(0, 'Xóa tất cả đã hoàn thành');
		}
		if($op == 'delete') {
			thread_delete($tid);
		} elseif($op == 'close') {
			thread_update($tid, array('closed'=>1));
		} elseif($op == 'open') {
			thread_update($tid, array('closed'=>0));
		}
		// hook admin_thread_operation_for.php
		$tids[] = $tid;
	}
	// hook admin_thread_operation_end.php
	message(0, $tids);
	
// operate
} elseif($action == 'found') {	

	$queueid = _SESSION('thread_find_queueid');
	empty($queueid) AND message(-1, lang('thread_queue_not_exists'));
	
	$page = param(2, 1);
	$total = queue_count($queueid);
	$pagination = pagination(url('thread-found-{page}'), $total, $page, $pagesize);
	// hook admin_thread_found_start.php
	$tids = queue_find($queueid, $page, $pagesize);
	$threadlist = thread_find_by_tids($tids);
	
	// hook admin_thread_found_end.php
	include _include(ADMIN_PATH."view/htm/thread_found.htm");
}

// hook admin_thread_start.php

?>