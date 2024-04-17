<?php

// hook model_table_day_start.php

function table_day_read($table, $year, $month, $day) {
	// hook model_table_day_read_start.php
	$arr = db_find_one('table_day', array('year'=>$year, 'month'=>$month, 'day'=>$day, 'table'=>$table));
	// hook model_table_day_read_end.php
	return $arr;
}

/*
	$maxtid = table_day_maxid('thread', '2014-9-1');
	$maxtid = table_day_maxid('thread', 1234567890);

*/
function table_day_maxid($table, $date) {
	// hook model_table_day_maxid_start.php

	$mintime = 1411516800; // strtotime('2014-9-24');
	!is_numeric($date) AND $date = strtotime($date);
	if($date < $mintime) return 0;

	list($year, $month, $day) = explode('-', date('Y-n-j', $date));
	$arr = table_day_read($table, $year, $month, $day);
	// hook model_table_day_maxid_end.php
	return $arr ? intval($arr['maxid']) : 0;
}

function table_day_cron($crontime = 0) {
	// hook model_table_day_cron_start.php
	global $time;
	$crontime = $crontime ? $crontime : $time;
	list($y, $m, $d) = explode('-', date('Y-n-j', $crontime));
	
	$table_map = array(
		'thread'=>'tid',
		'post'=>'pid',
		'user'=>'uid',
	);
	foreach ($table_map as $table=>$col) {
		$maxid = db_maxid($table, $col, array('create_date'=>array('<'=>$crontime)));
		$count = db_count($table, array('create_date'=>array('<'=>$crontime)));
		$arr = array(
			'year'=>$y,
			'month'=>$m, 
			'day'=>$d, 
			'create_date'=>$crontime, 
			'table'=>$table, 
			'maxid'=>$maxid, 
			'count'=>$count
		);
		db_replace('table_day', $arr);
	}
	// hook model_table_day_cron_end.php
}

function table_day_rebuild() {
	// hook model_table_day_rebuild_start.php
	global $time;
	$user = user__read(1);
	$crontime = $user['create_date'];
	while($crontime < $time) {
		table_day_cron($crontime);
		$crontime = $crontime + 86400;
	}
	// hook model_table_day_rebuild_end.php
}

// hook model_table_day_end.php

?>