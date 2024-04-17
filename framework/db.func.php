<?php

function db_new($dbconf) {
	global $errno, $errstr;
	if($dbconf) {
		switch ($dbconf['type']) {
			case 'mysql':      $db = new db_mysql($dbconf['mysql']); 		break;
			case 'pdo_mysql':  $db = new db_pdo_mysql($dbconf['pdo_mysql']);	break;
			case 'pdo_sqlite': $db = new db_pdo_sqlite($dbconf['pdo_sqlite']);	break;
			case 'pdo_mongodb': $db = new db_pdo_mongodb($dbconf['pdo_mongodb']);	break;
			default: return xn_error(-1, 'Not suppported db type:'.$dbconf['type']);
		}
		if(!$db || ($db && $db->errstr)) {
			$errno = -1;
			$errstr = $db->errstr;
			return FALSE;
		}
		return $db;
	}
	return NULL;
}

// kiểm tra kết nối
function db_connect($d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	
	$r = $d->connect();
	
	db_errno_errstr($r, $d);
	
	return $r;
}

function db_close($d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	$r = $d->close();
	
	db_errno_errstr($r, $d);
	
	return $r;
}

function db_sql_find_one($sql, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	$arr = $d->sql_find_one($sql);
	
	db_errno_errstr($arr, $d, $sql);
	
	return $arr;
}

function db_sql_find($sql, $key = NULL, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	$arr = $d->sql_find($sql, $key);
	
	db_errno_errstr($arr, $d, $sql);
	
	return $arr;
}

// Nếu là INSERT hoặc REPLACE，hãy trả về mysql_insert_id();
// Nếu là UPDATE hoặc DELETE，hãy trả về mysql_affected_rows();
// Đối với các bảng không tự động tăng, 0 luôn được trả về sau INSERT
// Xác định xem việc thực hiện có thành công hay không: mysql_exec() === FALSE
function db_exec($sql, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	DEBUG AND xn_log($sql, 'db_exec');
	
	$n = $d->exec($sql);
	
	db_errno_errstr($n, $d, $sql);
	
	return $n;
}

function db_count($table, $cond = array(), $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	$r = $d->count($d->tablepre.$table, $cond);
	
	db_errno_errstr($r, $d);
	
	return $r;
}

function db_maxid($table, $field, $cond = array(), $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	$r = $d->maxid($d->tablepre.$table, $field, $cond);
	
	db_errno_errstr($r, $d);
	
	return $r;
}

// KHÔNG có gói SQL, có thể hỗ trợ MySQL Marial PG MongoDB
function db_create($table, $arr, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	return db_insert($table, $arr);
}

function db_insert($table, $arr, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	$sqladd = db_array_to_insert_sqladd($arr);
	if(!$sqladd) return FALSE;
	return db_exec("INSERT INTO {$d->tablepre}$table $sqladd", $d);
}

function db_replace($table, $arr, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	$sqladd = db_array_to_insert_sqladd($arr);
	if(!$sqladd) return FALSE;
	return db_exec("REPLACE INTO {$d->tablepre}$table $sqladd", $d);
}

function db_update($table, $cond, $update, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	$condadd = db_cond_to_sqladd($cond);
	$sqladd = db_array_to_update_sqladd($update);
	if(!$sqladd) return FALSE;
	return db_exec("UPDATE {$d->tablepre}$table SET $sqladd $condadd", $d);
}

function db_delete($table, $cond, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	$condadd = db_cond_to_sqladd($cond);
	return db_exec("DELETE FROM {$d->tablepre}$table $condadd", $d);
}

function db_truncate($table, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	return $d->truncate($d->tablepre.$table);
}

function db_read($table, $cond, $d = NULL) {
	$db = $_SERVER['db'];
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	$sqladd = db_cond_to_sqladd($cond);
	$sql = "SELECT * FROM {$d->tablepre}$table $sqladd";
	return db_sql_find_one($sql, $d);
}

function db_find($table, $cond = array(), $orderby = array(), $page = 1, $pagesize = 10, $key = '', $col = array(), $d = NULL) {
	$db = $_SERVER['db'];
	
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	return $d->find($table, $cond, $orderby, $page, $pagesize, $key, $col);
}

function db_find_one($table, $cond = array(), $orderby = array(), $col = array(), $d = NULL) {
	$db = $_SERVER['db'];
	
	$d = $d ? $d : $db;
	if(!$d) return FALSE;
	
	return $d->find_one($table, $cond, $orderby, $col);
}

function db_errno_errstr($r, $d = NULL, $sql = '') {
	global $errno, $errstr;
	if($r === FALSE) { //  && $d->errno != 0
		$errno = $d->errno;
		$errstr = db_errstr_safe($errno, $d->errstr);
		$s = 'SQL:'.$sql."\r\nerrno: ".$errno.", errstr: ".$errstr;
		xn_log($s, 'db_error');
	}
}

function db_errstr_safe($errno, $errstr) {
	if(DEBUG) return $errstr;
	if($errno == 1049) {
		return 'Tên cơ sở dữ liệu không tồn tại, vui lòng tạo thủ công';
	} elseif($errno == 2003 ) {
		return 'Không thể kết nối với máy chủ cơ sở dữ liệu, vui lòng kiểm tra xem IP có đúng hay cài đặt tường lửa không';
	} elseif($errno == 1024) {
		return 'Không thể kết nối với cơ sở dữ liệu';
	} elseif($errno == 1045) {
		return 'Mật khẩu tài khoản cơ sở dữ liệu sai';
	}
	return $errstr;
}


//----------------------------------->
/*
$cond = array('id'=>123, 'groupid'=>array('>'=>100, 'LIKE'=>'\'jack'));
$s = db_cond_to_sqladd($cond);
echo $s;

WHERE id=123 AND groupid>100 AND groupid LIKE '%\'jack%' 

// Định dạng:
array('id'=>123, 'groupid'=>123)
array('id'=>array(1,2,3,4,5))
array('id'=>array('>' => 100, '<' => 200))
array('username'=>array('LIKE' => 'jack'))
*/

function db_cond_to_sqladd($cond) {
	$s = '';
	if(!empty($cond)) {
		$s = ' WHERE ';
		foreach($cond as $k=>$v) {
			if(!is_array($v)) {
				$v = (is_int($v) || is_float($v)) ? $v : "'".addslashes($v)."'";
				$s .= "`$k`=$v AND ";
			} elseif(isset($v[0])) {
				// OR hiệu quả hơn IN
				$s .= '(';
				//$v = array_reverse($v);
				foreach ($v as $v1) {
					$v1 = (is_int($v1) || is_float($v1)) ? $v1 : "'".addslashes($v1)."'";
					$s .= "`$k`=$v1 OR ";
				}
				$s = substr($s, 0, -4);
				$s .= ') AND ';
				
				/*
				$ids = implode(',', $v);
				$s .= "$k IN ($ids) AND ";
				*/
			} else {
				foreach($v as $k1=>$v1) {
					if($k1 == 'LIKE') {
						$k1 = ' LIKE ';
						$v1="%$v1%";
					}
					$v1 = (is_int($v1) || is_float($v1)) ? $v1 : "'".addslashes($v1)."'";
					$s .= "`$k`$k1$v1 AND ";
				}
			}
		}
		$s = substr($s, 0, -4);
	}
	return $s;
}

function db_orderby_to_sqladd($orderby) {
	$s = '';
	if(!empty($orderby)) {
		$s .= ' ORDER BY ';
		$comma = '';
		foreach($orderby as $k=>$v) {
			$s .= $comma."`$k` ".($v == 1 ? ' ASC ' : ' DESC ');
			$comma = ',';
		}
	}
	return $s;
}


/*
	$arr = array(
		'name'=>'abc',
		'stocks+'=>1,
		'date'=>12345678900,
	)
	db_array_to_update_sqladd($arr);
*/
function db_array_to_update_sqladd($arr) {
	$s = '';
	foreach($arr as $k=>$v) {
		$v = ($v !== null) ? addslashes($v) : null;
		$op = substr($k, -1);
		if($op == '+' || $op == '-') {
			$k = substr($k, 0, -1);
			$v = (is_int($v) || is_float($v)) ? $v : "'$v'";
			$s .= "`$k`=$k$op$v,";
		} else {
			$v = (is_int($v) || is_float($v)) ? $v : "'$v'";
			$s .= "`$k`=$v,";
		}
	}
	return substr($s, 0, -1);
}

/*
	$arr = array(
		'name'=>'abc',
		'date'=>12345678900,
	)
	db_array_to_insert_sqladd($arr);
*/
function db_array_to_insert_sqladd($arr) {
	$s = '';
	$keys = array();
	$values = array();
	foreach($arr as $k=>$v) {
		$k = addslashes($k);
		$v = addslashes($v);
		$keys[] = '`'.$k.'`';
		$v = (is_int($v) || is_float($v)) ? $v : "'$v'";
		$values[] = $v;
	}
	$keystr = implode(',', $keys);
	$valstr = implode(',', $values);
	$sqladd = "($keystr) VALUES ($valstr)";
	return $sqladd;
}

?>