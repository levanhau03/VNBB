<?php

class cache_yac {
	public $yac = NULL;
	public $cachepre = '';
	public $errno = 0;
	public $errstr = '';
        public function __construct($conf = array()) {
                if(!class_exists('Yac')) {
                        return $this->error(1, 'Tiện ích mở rộng yac chưa được tải, vui lòng kiểm tra phiên bản PHP của bạn');
                }
		$this->cachepre = isset($conf['cachepre']) ? $conf['cachepre'] : 'pre_';
		$this->yac = new Yac($this->cachepre);
        }
        public function connect() {
        }
        public function set($k, $v, $life) {
                return $this->yac->set($k, $v, $life);
        }
        public function get($k) {
                $r = $this->yac->get($k);
                if($r === FALSE) $r = NULL;
                return $r;
        }
        public function delete($k) {
                return $this->yac->delete($k);
        }
        public function truncate() {
                $this->yac->flush();
                return TRUE;
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