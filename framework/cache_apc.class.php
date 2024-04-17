<?php

class cache_apc {
	
	public $conf = array();
	public $link = NULL;
	public $cachepre = '';
	public $errno = 0;
	public $errstr = '';
	
        public function __construct($conf = array()) {
                if(!function_exists('apc_fetch')) {
			return $this->error(-1, 'Tiện ích mở rộng APC chưa được tải, vui lòng kiểm tra phiên bản PHP của bạn');
                }
                $this->conf = $conf;
		$this->cachepre = isset($conf['cachepre']) ? $conf['cachepre'] : 'pre_';
        }
        public function connect() {
        }
        public function set($k, $v, $life) {
                return apc_store($k, $v, $life);
        }
        public function get($k) {
                $r = apc_fetch($k);
                if($r === FALSE) $r = NULL;
                return $r;
        }
        public function delete($k) {
                return apc_delete($k);
        }
        public function truncate() {
                return apc_clear_cache('user');
        }
       	public function error($errno = 0, $errstr = '') {
		$this->errno = $errno;
		$this->errstr = $errstr;
		DEBUG AND trigger_error('Cache Error:'.$this->errstr);
	}
        public function __destruct() {

        }
}

?>