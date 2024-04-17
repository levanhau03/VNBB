<?php

class db_mysql {
	
	public $conf = array();
	public $rconf = array();
	public $wlink = NULL;
	public $rlink = NULL;
	public $link = NULL;
	public $errno = 0;
	public $errstr = '';
	public $sqls = array();
	public $tablepre = '';
	public $innodb_first = TRUE;
	
	public function __construct($conf) {
		$this->conf = $conf;
		$this->tablepre = $conf['master']['tablepre'];
	}
	
	// Connect according to configuration file
	public function connect() {
		$this->wlink = $this->connect_master();
		$this->rlink = $this->connect_slave();
		return $this->wlink && $this->rlink;
	}
	
	// Connect to write server
	public function connect_master() {
		if($this->wlink) return $this->wlink;
		$conf = $this->conf['master'];
		if(!$this->wlink) $this->wlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		return $this->wlink;
	}
	
	public function connect_slave() {
		if($this->rlink) return $this->rlink;
		if(empty($this->conf['slaves'])) {
			if($this->wlink === NULL) $this->wlink = $this->connect_master();
			$this->rlink = $this->wlink;
			$this->rconf = $this->conf['master'];
		} else {
			//$n = array_rand($this->conf['slaves']);
			$arr = array_rand($this->conf['slaves'], 1);
			$conf = $this->conf['slaves'][$arr[0]];
			$this->rconf = $conf;
			$this->rlink = $this->real_connect($conf['host'], $conf['user'], $conf['password'], $conf['name'], $conf['charset'], $conf['engine']);
		}
		return $this->rlink;
	}
	
	public function real_connect($host, $user, $password, $name, $charset = '', $engine = '') {
		$link = @mysql_connect($host, $user, $password);
		if(!$link) { $this->error(mysql_errno(), 'Không thể kết nối với máy chủ cơ sở dữ liệu:'.mysql_error()); return FALSE; }
		if(!mysql_select_db($name, $link)) { $this->error(mysql_errno(), 'Không thể chọn cơ sở dữ liệu:'.mysql_error()); return FALSE; }
		//strtolower($engine) == 'innodb' AND $this->query("SET innodb_flush_log_at_trx_commit=no", $link);
		$charset AND $this->query("SET names $charset, sql_mode=''", $link);
		return $link;
	}
	public function sql_find_one($sql) {
		$query = $this->query($sql);
		if(!$query) return $query;
		$r = mysql_fetch_assoc($query);
		if($r === FALSE) {
			// $this->error();
			return NULL;
		}
		return $r;
	}
	
	
	public function sql_find($sql, $key = NULL) {
		$query = $this->query($sql);
		if(!$query) return $query;
		$arrlist = array();
		while($arr = mysql_fetch_assoc($query)) {
			$key ? $arrlist[$arr[$key]] = $arr : $arrlist[] = $arr;
		}
		return $arrlist;
	}
	
	public function find($table, $cond = array(), $orderby = array(), $page = 1, $pagesize = 10, $key = '', $col = array()) {
		$page = max(1, $page);
		$cond = db_cond_to_sqladd($cond);
		$orderby = db_orderby_to_sqladd($orderby);
		$offset = ($page - 1) * $pagesize;
		$cols = $col ? implode(',', $col) : '*';
		return $this->sql_find("SELECT $cols FROM {$this->tablepre}$table $cond$orderby LIMIT $offset,$pagesize", $key);
		
	}
		
	public function find_one($table, $cond = array(), $orderby = array(), $col = array()) {
		$cond = db_cond_to_sqladd($cond);
		$orderby = db_orderby_to_sqladd($orderby);
		$cols = $col ? implode(',', $col) : '*';
		return $this->sql_find_one("SELECT $cols FROM {$this->tablepre}$table $cond$orderby LIMIT 1");
	}
	
	public function query($sql, $link = NULL) {
		if(!$link) {
			if(!$this->rlink && !$this->connect_slave()) return FALSE;;
			$link = $this->link = $this->rlink;
		}
		$t1 = microtime(1);
		$query = mysql_query($sql, $link);
		$t2 = microtime(1);
		if($query === FALSE) $this->error();
		
		$t3 = substr($t2 - $t1, 0, 6);
		DEBUG AND xn_log("[$t3]".$sql, 'db_sql');
		if(count($this->sqls) < 1000) $this->sqls[] = "[$t3]".$sql;
		
		return $query;
	}
	
	public function exec($sql, $link = NULL) {
		if(!$link) {
			if(!$this->wlink && !$this->connect_master()) return FALSE;
			$link = $this->link = $this->wlink;
		}
		if(strtoupper(substr($sql, 0, 12) == 'CREATE TABLE')) {
			$fulltext = strpos($sql, 'FULLTEXT(') !== FALSE;
			$highversion = version_compare($this->version(), '5.6') >= 0;
			if(!$fulltext || ($fulltext && $highversion)) {
				$conf = $this->conf['master'];
				if(strtolower($conf['engine']) != 'myisam') {
					$this->innodb_first AND $this->is_support_innodb() AND $sql = str_ireplace('MyISAM', 'InnoDB', $sql);
				}
			}
		}
		$t1 = microtime(1);
		$query = mysql_query($sql, $this->wlink);
		$t2 = microtime(1);
		$t3 = substr($t2 - $t1, 0, 6);
		
		DEBUG AND xn_log("[$t3]".$sql, 'db_sql');
		if(count($this->sqls) < 1000) $this->sqls[] = "[$t3]".$sql;
		
		if($query !== FALSE) {
			$pre = strtoupper(substr(trim($sql), 0, 7));
			if($pre == 'INSERT ' || $pre == 'REPLACE') {
				return mysql_insert_id($this->wlink);
			} elseif($pre == 'UPDATE ' || $pre == 'DELETE ') {
				return mysql_affected_rows($this->wlink);
			}
		} else {
			$this->error();
		}
		
		return $query;
	}
	
	// information_schema
	public function count($table, $cond = array()) {
		$this->connect_slave();
		if(empty($cond) && $this->rconf['engine'] == 'innodb') {
			$dbname = $this->rconf['name'];
			$sql = "SELECT TABLE_ROWS as num FROM information_schema.tables WHERE TABLE_SCHEMA='$dbname' AND TABLE_NAME='$table'";
		} else {
			$cond = db_cond_to_sqladd($cond);
			$sql = "SELECT COUNT(*) AS num FROM `$table` $cond";
		}
		$arr = $this->sql_find_one($sql);
		return !empty($arr) ? intval($arr['num']) : $arr;
	}
	
	public function maxid($table, $field, $cond = array()) {
		$sqladd = db_cond_to_sqladd($cond);
		$sql = "SELECT MAX($field) AS maxid FROM `$table` $sqladd";
		$arr = $this->sql_find_one($sql);
		return !empty($arr) ? intval($arr['maxid']) : $arr;
	}
	
	public function truncate($table) {
		return $this->exec("TRUNCATE $table");
	}
	
	public function close() {
		$r = mysql_close($this->wlink);
		if($this->wlink != $this->rlink) {
			$r = mysql_close($this->rlink);
		}
		return $r;
	}
	
	public function version() {
		$r = $this->sql_find_one("SELECT VERSION() AS v");
		return $r['v'];
	}
	
	public function error($errno = 0, $errstr = '') {
		$this->errno = $errno ? $errno : ($this->link ? mysql_errno($this->link) : mysql_errno());
		$this->errstr = $errstr ? $errstr : ($this->link ? mysql_error($this->link) : mysql_error());
		DEBUG AND trigger_error('Database Error:'.$this->errstr);
	}
	
	public function is_support_innodb() {
		$arrlist = $this->sql_find('SHOW ENGINES');
		$arrlist2 = arrlist_key_values($arrlist, 'Engine', 'Support');
		return isset($arrlist2['InnoDB']) AND $arrlist2['InnoDB'] == 'YES';
	}

	public function __destruct() {
		if($this->wlink) $this->wlink = NULL;
		if($this->rlink) $this->rlink = NULL;
	}
}

?>