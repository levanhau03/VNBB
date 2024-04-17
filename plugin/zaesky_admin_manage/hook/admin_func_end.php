<?php exit;
	$today0 = strtotime(date('Y-m-d',time()))-1;
	$new_threads_num = db_count('thread',array('create_date'=>array('>'=>$today0)));
	$new_posts_num = db_count('post',array('create_date'=>array('>'=>$today0)));
	$new_menber_num = db_count('user',array('create_date'=>array('>'=>$today0)));
?>