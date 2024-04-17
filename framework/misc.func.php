<?php

function xn_message($code, $message) {
	$ajax = $_SERVER['ajax'];
	echo $ajax ? xn_json_encode(array('code'=>$code, 'message'=>$message)) : $message;
	exit;
}

function xn_log_post_data() {
	$method = $_SERVER['method'];
	if($method != 'POST') return;
	$post = $_POST;
	isset($post['password']) AND $post['password'] = '******';
	isset($post['password_new']) AND $post['password_new'] = '******';
	isset($post['password_old']) AND $post['password_old'] = '******';

	xn_log(xn_json_encode($post), 'post_data');
}

function error_handle($errno, $errstr, $errfile, $errline) {
	
	// PHP internal default processing
	if(DEBUG == 0)  return FALSE;
	
	// if(strpos($s, 'error_log(') !== FALSE) return TRUE;
	$time = $_SERVER['time'];
	$ajax = $_SERVER['ajax'];
	IN_CMD AND $errstr = str_replace('<br>', "\n", $errstr);
	
	$subject = "Error[$errno]: $errstr, File: $errfile, Line: $errline";
	$message = array();
	xn_log($subject, 'php_error');

	$arr = debug_backtrace();
	array_shift($arr);
	foreach($arr as $v) {
		$args = '';
		if(!empty($v['args']) && is_array($v['args'])) foreach ($v['args'] as $v2) $args .= ($args ? ' , ' : '').(is_array($v2) ? 'array('.count($v2).')' : (is_object($v2) ? 'object' : $v2));
		!isset($v['file']) AND $v['file'] = '';
		!isset($v['line']) AND $v['line'] = '';
		$message [] = "File: $v[file], Line: $v[line], $v[function]($args) ";
	}
	$txt = $subject."\r\n".implode("\r\n", $message);
	$html = $s = "<fieldset class=\"fieldset small notice\">
			<b>$subject</b>
			<div>".implode("<br>\r\n", $message)."</div>
		</fieldset>";
	echo ($ajax || IN_CMD) ? $txt : $html;
	DEBUG == 2 AND xn_log($txt, 'debug_error');
	return TRUE;
}

// Use global variables to log error messages
function xn_error($no, $str, $return = FALSE) {
	global $errno, $errstr;
	$errno = $no;
	$errstr = $str;
	return $return;
}

function param($key, $defval = '', $htmlspecialchars = TRUE, $addslashes = FALSE) {
	if(!isset($_REQUEST[$key]) || ($key === 0 && empty($_REQUEST[$key]))) {
		if(is_array($defval)) {
			return array();
		} else {
			return $defval;
		}
	}
	$val = $_REQUEST[$key];
	$val = param_force($val, $defval, $htmlspecialchars, $addslashes);
	return $val;
}

function param_word($key, $len = 32) {
	$s = param($key);
	$s = xn_safe_word($s, $len);
	return $s;
}

function param_base64($key, $len = 0) {
	$s = param($key, '', FALSE);
	if(empty($s)) return '';
	$s = substr($s, strpos($s, ',') + 1);
	$s = base64_decode($s);
	$len AND $s = substr($s, 0, $len);
	return $s;
}

function param_json($key) {
	$s = param($key, '', FALSE);
	if(empty($s)) return '';
	$arr = xn_json_decode($s);
	return $arr;
}

function param_url($key) {
	$s = param($key, '', FALSE);
	$arr = xn_urldecode($s);
	return $arr;
}

function xn_safe_word($s, $len) {
	$s = preg_replace('#\W+#', '', $s);
	$s = substr($s, 0, $len);
	return $s;
}

function param_force($val, $defval, $htmlspecialchars = TRUE, $addslashes = FALSE) {
	$get_magic_quotes_gpc = _SERVER('get_magic_quotes_gpc');
	if(is_array($defval)) {
		$defval = empty($defval) ? '' : $defval[0];
		if(is_array($val)) {
			foreach($val as &$v) {
				if(is_array($v)) {
					$v = $defval;
				} else {
					if(is_string($defval)) {
						//$v = trim($v);
						$addslashes AND !$get_magic_quotes_gpc && $v = addslashes($v);
						!$addslashes AND $get_magic_quotes_gpc && $v = stripslashes($v);
						$htmlspecialchars AND $v = htmlspecialchars($v);
					} else {
						$v = intval($v);
					}
				}
			}
		} else {
			return array();
		}
	} else {
		if(is_array($val)) {
			$val = $defval;
		} else {
			if(is_string($defval)) {
				//$val = trim($val);
				$addslashes AND !$get_magic_quotes_gpc && $val = addslashes($val);
				!$addslashes AND $get_magic_quotes_gpc && $val = stripslashes($val);
				$htmlspecialchars AND $val = htmlspecialchars($val);
			} else {
				$val = intval($val);
			}
		}
	}
	return $val;
}

/*
	lang('mobile_length_error');
	lang('mobile_length_error', array('mobile'=>$mobile));
*/
function lang($key, $arr = array()) {
	$lang = $_SERVER['lang'];
	if(!isset($lang[$key])) return 'lang['.$key.']';
	$s = $lang[$key];
	if(!empty($arr)) {
		foreach($arr as $k=>$v) {
			$s = str_replace('{'.$k.'}', $v, $s);
		}
	}
	return $s;
}

function jump($message, $url = '', $delay = 3) {
	$ajax = $_SERVER['ajax'];
	if($ajax) return $message;
	if(!$url) return $message;
	$url == 'back' AND $url = 'javascript:history.back()';
	$htmladd = '<script>setTimeout(function() {window.location=\''.$url.'\'}, '.($delay * 1000).');</script>';
	return '<a href="'.$url.'">'.$message.'</a>'.$htmladd;
}

function xn_strlen($s) {
	return mb_strlen($s, 'UTF-8');
}

function xn_substr($s, $start, $len) {
	return mb_substr($s, $start, $len, 'UTF-8');
}

function xn_txt_to_html($s) {
	$s = htmlspecialchars($s);
	$s = str_replace(" ", '&nbsp;', $s);
	$s = str_replace("\t", ' &nbsp; &nbsp; &nbsp; &nbsp;', $s);
	$s = str_replace("\r\n", "\n", $s);
	$s = str_replace("\n", '<br>', $s);
	return $s;
}

function xn_urlencode($s) {
    $s = urlencode($s);
    $s = str_replace('_', '_5f', $s);
    $s = str_replace('-', '_2d', $s);
    $s = str_replace('.', '_2e', $s);
    $s = str_replace('+', '_2b', $s);
    $s = str_replace('=', '_3d', $s);
    $s = str_replace('%', '_', $s);
    return $s; 
}

function xn_urldecode($s) {
    $s = str_replace('_', '%', $s);
    $s = urldecode($s);
    return $s; 
}

function xn_json_encode($data, $pretty = FALSE, $level = 0) {
	if(version_compare(PHP_VERSION, '5.4.0') >= 0) {
		return json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
	}
	
	$tab = $pretty ? str_repeat("\t", $level) : '';
	$tab2 = $pretty ? str_repeat("\t", $level + 1) : '';
	$br = $pretty ? "\r\n" : '';
	switch($type = gettype($data)) {
		case 'NULL':
			return 'null';
		case 'boolean':
			return ($data ? 'true' : 'false');
		case 'integer':
		case 'double':
		case 'float':
			return $data;
		case 'string':
			$data = '"'.str_replace(array('\\', '"'), array('\\\\', '\\"'), $data).'"';
			$data = str_replace("\r", '\\r', $data);
			$data = str_replace("\n", '\\n', $data);
			$data = str_replace("\t", '\\t', $data);
			return $data;
		case 'object':
			$data = get_object_vars($data);
		case 'array':
			$output_index_count = 0;
			$output_indexed = array();
			$output_associative = array();
			foreach($data as $key => $value) {
				$output_indexed[] = xn_json_encode($value, $pretty, $level + 1);
				$output_associative[] = $tab2.'"'.$key.'":' . xn_json_encode($value, $pretty, $level + 1);
				if ($output_index_count !== NULL && $output_index_count++ !== $key) {
					$output_index_count = NULL;
				}
			}
			if($output_index_count !== NULL) {
				return '[' . implode(",$br", $output_indexed) . ']';
			} else {
				return "{{$br}" . implode(",$br", $output_associative) . "{$br}{$tab}}";
			}
		default:
			return ''; // Not supported
	}
}

function xn_json_decode($json) {
	$json = trim($json, "\xEF\xBB\xBF");
	$json = trim($json, "\xFE\xFF");
	return json_decode($json, 1);
}

/*
function xn_json_encode($arg) {
	$r = '';
	switch (gettype($arg)) {
		case 'array':
			$r = is_number_array($arg) ? xn_json_number_array_to_string($arg) : xn_json_assoc_array_to_string($arg);
		break;
		case 'object':
			return xn_json_encode(get_object_vars($arg));
		break;
		case 'integer':
		case 'double':
			$r = is_numeric($arg) ? (string)$arg : 'null';
		break;
		case 'string':
		$r = '"' . strtr($arg, array(
			"\r"   => '\\r',    "\n"   => '\\n',    "\t"   => '\\t',     "\b"   => '\\b',
			"\f"   => '\\f',    '\\'   => '\\\\',   '"'    => '\"',
			"\x00" => '\u0000', "\x01" => '\u0001', "\x02" => '\u0002', "\x03" => '\u0003',
			"\x04" => '\u0004', "\x05" => '\u0005', "\x06" => '\u0006', "\x07" => '\u0007',
			"\x08" => '\b',     "\x0b" => '\u000b', "\x0c" => '\f',     "\x0e" => '\u000e',
			"\x0f" => '\u000f', "\x10" => '\u0010', "\x11" => '\u0011', "\x12" => '\u0012',
			"\x13" => '\u0013', "\x14" => '\u0014', "\x15" => '\u0015', "\x16" => '\u0016',
			"\x17" => '\u0017', "\x18" => '\u0018', "\x19" => '\u0019', "\x1a" => '\u001a',
			"\x1b" => '\u001b', "\x1c" => '\u001c', "\x1d" => '\u001d', "\x1e" => '\u001e',
			"\x1f" => '\u001f'
			)) . '"';
		break;
		case 'boolean':
			$r = $arg ? 1 : 0;
		break;
		default:
			$r = 'null';
	}
	return $r;
}

function xn_json_number_array_to_string($arr) {
	$s = '';
	foreach ($arr as $k=>$v) {
		$s .= ','.xn_json_encode($v);
	}
	$s = substr($s, 1);
	$r = '['.$s.']';
	return $r;
}

function xn_json_assoc_array_to_string($arr) {
	$s = '';
	foreach ($arr as $k=>$v) {
		$s .= ',"'.$k.'":'.xn_json_encode($v);
	}
	$s = substr($s, 1);
	$r = '{'.$s.'}';
	return $r;
}
function is_number_array($arr) {
	$i = 0;
	foreach ($arr as $k=>$v) {
		if(!is_numeric($k) || $k != $i++) return FALSE;
	}
	return TRUE;
}*/


/*
function xn_json_encode($json) {
	if(version_compare(PHP_VERSION, '5.4.0') == 1) {
		return json_encode($json, JSON_UNESCAPED_UNICODE);
	} else {
		$json = json_encode($json);
		return ucs2_to_utf8($json);
	}
}
function ucs2_to_utf8($s) {
	$s = preg_replace("#\\\u([0-9a-f]+)#ie", "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))", $s);
	return $s;
}
*/


// ---------------------> encrypt function end

function pagination_tpl($url, $text, $active = '') {
	global $g_pagination_tpl;
	empty($g_pagination_tpl) AND $g_pagination_tpl = '<li class="page-item{active}"><a href="{url}" class="page-link">{text}</a></li>';
	return str_replace(array('{url}', '{text}', '{active}'), array($url, $text, $active), $g_pagination_tpl);
}

function pagination($url, $totalnum, $page, $pagesize = 20) {
	$totalpage = ceil($totalnum / $pagesize);
	if($totalpage < 2) return '';
	$page = min($totalpage, $page);
	$shownum = 5;

	$start = max(1, $page - $shownum);
	$end = min($totalpage, $page + $shownum);

	$right = $page + $shownum - $totalpage;
	$right > 0 && $start = max(1, $start -= $right);
	$left = $page - $shownum;
	$left < 0 && $end = min($totalpage, $end -= $left);

	$s = '';
	$page != 1 && $s .= pagination_tpl(str_replace('{page}', $page-1, $url), '◀', '');
	if($start > 1) $s .= pagination_tpl(str_replace('{page}', 1, $url),'1 '.($start > 2 ? '...' : ''));
	for($i=$start; $i<=$end; $i++) {
		$s .= pagination_tpl(str_replace('{page}', $i, $url), $i, $i == $page ? ' active' : '');
	}
	if($end != $totalpage) $s .= pagination_tpl(str_replace('{page}', $totalpage, $url), ($totalpage - $end > 1 ? '...' : '').$totalpage);
	$page != $totalpage && $s .= pagination_tpl(str_replace('{page}', $page+1, $url), '▶');
	return $s;
}

function pager($url, $totalnum, $page, $pagesize = 20) {
	$totalpage = ceil($totalnum / $pagesize);
	if($totalpage < 2) return '';
	$page = min($totalpage, $page);

	$s = '';
	$page > 1 AND $s .= '<li><a href="'.str_replace('{page}', $page-1, $url).'">Trang trước</a></li>';
	$s .= " $page / $totalpage ";
	$totalnum >= $pagesize AND $page != $totalpage AND $s .= '<li><a href="'.str_replace('{page}', $page+1, $url).'">Trang tiếp</a></li>';
	return $s;
}

/*
function pages($url, $totalnum, $page, $pagesize = 20) {
	$totalpage = ceil($totalnum / $pagesize);
	if($totalpage < 2) return '';
	$page = min($totalpage, $page);
	$shownum = 5;

	$start = max(1, $page - $shownum);
	$end = min($totalpage, $page + $shownum);

	$right = $page + $shownum - $totalpage;
	$right > 0 && $start = max(1, $start -= $right);
	$left = $page - $shownum;
	$left < 0 && $end = min($totalpage, $end -= $left);

	$s = '';
	$page != 1 && $s .= '<a href="'.str_replace('{page}', $page-1, $url).'">◀</a>';
	if($start > 1) $s .= '<a href="'.str_replace('{page}', 1, $url).'">1 '.($start > 2 ? '... ' : '').'</a>';
	for($i=$start; $i<=$end; $i++) {
		if($i == $page) {
			$s .= '<a href="'.str_replace('{page}', $i, $url).'" class="active">'.$i.'</a>';// active
		} else {
			$s .= '<a href="'.str_replace('{page}', $i, $url).'">'.$i.'</a>';
		}
	}
	if($end != $totalpage) $s .= '<a href="'.str_replace('{page}', $totalpage, $url).'">'.($totalpage - $end > 1 ? '... ' : '').$totalpage.'</a>';
	$page != $totalpage && $s .= '<a href="'.str_replace('{page}', $page+1, $url).'">▶</a>';
	return $s;
}

function simple_pages($url, $totalnum, $page, $pagesize = 20) {
	$totalpage = ceil($totalnum / $pagesize);
	if($totalpage < 2) return '';
	$page = min($totalpage, $page);

	$s = '';
	$page > 1 AND $s .= '<a href="'.str_replace('{page}', $page-1, $url).'">Trang trước</a>';
	$s .= " $page / $totalpage ";
	$totalnum >= $pagesize AND $page != $totalpage AND $s .= '<a href="'.str_replace('{page}', $page+1, $url).'">Trang tiếp</a>';
	return $s;
}
*/

/*function page($page, $n, $pagesize) {
	$total = ceil($n / $pagesize);
	$total < 1 AND $total = 1;
	return mid($page, 1, $total);
}*/

function mid($n, $min, $max) {
	if($n < $min) return $min;
	if($n > $max) return $max;
	return $n;
}

function humandate($timestamp, $lan = array()) {
	$time = time();
	$lang = $_SERVER['lang'] ?? '';

	static $custom_humandate = NULL;
	if($custom_humandate === NULL) $custom_humandate = function_exists('custom_humandate');
	if($custom_humandate) return custom_humandate($timestamp, $lan);

	$seconds = $time - $timestamp;
	$lan = empty($lang) ? $lan : $lang;
	if($seconds > 31536000) {
		return date('Y-n-j', $timestamp);
	} elseif($seconds > 2592000) {
		return floor($seconds / 2592000).$lan['month_ago'];
	} elseif($seconds > 86400) {
		return floor($seconds / 86400).$lan['day_ago'];
	} elseif($seconds > 3600) {
		return floor($seconds / 3600).$lan['hour_ago'];
	} elseif($seconds > 60) {
		return floor($seconds / 60).$lan['minute_ago'];
	} else {
		return $seconds.$lan['second_ago'];
	}
}

function humannumber($num) {
	
	static $custom_humannumber = NULL;
	if($custom_humannumber === NULL) $custom_humannumber = function_exists('custom_humannumber');
	if($custom_humannumber) return custom_humannumber($num);
	
	$num > 100000 && $num = ceil($num / 10000).'triệu';
	return $num;
}

function humansize($num) {
	
	static $custom_humansize = NULL;
	if($custom_humansize === NULL) $custom_humansize = function_exists('custom_humansize');
	if($custom_humansize) return custom_humansize($num);
	
	if($num > 1073741824) {
		return number_format($num / 1073741824, 2, '.', '').'G';
	} elseif($num > 1048576) {
		return number_format($num / 1048576, 2, '.', '').'M';
	} elseif($num > 1024) {
		return number_format($num / 1024, 2, '.', '').'K';
	} else {
		return $num.'B';
	}
}

function ip() {
	$conf = _SERVER('conf');
	$ip = '127.0.0.1';
	if(empty($conf['cdn_on'])) {
		$ip = _SERVER('REMOTE_ADDR');
	} else {
		if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
			$ip = $_SERVER['HTTP_CDN_SRC_IP'];
		} elseif(isset($_SERVER['HTTP_CLIENTIP'])) {
			$ip = $_SERVER['HTTP_CLIENTIP'];
		} elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			$arr = array_filter(explode(',', $ip));
			$ip = trim(end($arr));
		} else {
			$ip = _SERVER('REMOTE_ADDR');
		}
	}
	return long2ip(ip2long($ip));
}

/*
function ip() {
	global $conf;
	$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
	if(empty($conf['cdn_ip']) || empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return $ip;
	} else {
		// X-Forwarded-For: client1, proxy1, proxy2, ...
		foreach($conf['cdn_ip'] as $cdnip) {
			$pos1 = strrpos($cdnip, '.');
			$pos2 = strrpos($ip, '.');
			if($ip == $cdnip || ($pos1 == $pos2 && substr($cdnip, $pos1) == '.*' && substr($cdnip, 0, $pos1) == substr($ip, 0, $pos2))) {
				$userips = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['HTTP_X_REAL_IP'];
				if(empty($userips)) return $ip;
				$arr = array_values(array_filter(explode(',', $userips)));
				return long2ip(ip2long(end($arr)));
			}
		}
		return $ip;
	}
}
*/

function xn_log($s, $file = 'error') {
	if(DEBUG == 0 && strpos($file, 'error') === FALSE) return;
	$time = $_SERVER['time'];
	$ip = $_SERVER['ip'];
	$conf = _SERVER('conf');
	$uid = intval(G('uid'));
	$day = date('Ym', $time);
	$mtime = date('Y-m-d H:i:s');
	$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	$logpath = $conf['log_path'].$day;
	!is_dir($logpath) AND mkdir($logpath, 0777, true);

	$s = str_replace(array("\r\n", "\n", "\t"), ' ', $s);
	$s = "<?php exit;?>\t$mtime\t$ip\t$url\t$uid\t$s\r\n";
	
	@error_log($s, 3, $logpath."/$file.php");
}

function get__browser() {
	$browser = array(
		'device'=>'pc', // pc|mobile|pad
		'name'=>'chrome', // chrome|firefox|ie|opera
		'version'=>30,
	);
	$agent = _SERVER('HTTP_USER_AGENT');
	if(strpos($agent, 'msie') !== FALSE || stripos($agent, 'trident') !== FALSE) {
		$browser['name'] = 'ie';
		$browser['version'] = 8;
		preg_match('#msie\s*([\d\.]+)#is', $agent, $m);
		if(!empty($m[1])) {
			if(strpos($agent, 'compatible; msie 7.0;') !== FALSE) {
				$browser['version'] = 8;
			} else {
				$browser['version'] = intval($m[1]);
			}
		} else {
			preg_match('#Trident/([\d\.]+)#is', $agent, $m);
			if(!empty($m[1])) {
				$trident = intval($m[1]);
				$trident == 4 AND $browser['version'] = 8;
				$trident == 5 AND $browser['version'] = 9;
				$trident > 5 AND $browser['version'] = 10;
			}
		}
	}

	if(isset($_SERVER['HTTP_X_WAP_PROFILE']) || (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap") || stripos($agent, 'phone')  || stripos($agent, 'mobile') || strpos($agent, 'ipod'))) {
		$browser['device'] = 'mobile';
	} elseif(strpos($agent, 'pad') !== FALSE) {
		$browser['device'] = 'pad';
		$browser['name'] = '';
		$browser['version'] = '';
	/*
	} elseif(strpos($agent, 'miui') !== FALSE) {
		$browser['device'] = 'mobile';
		$browser['name'] = 'xiaomi';
		$browser['version'] = '';
	*/
	} else {
		$robots = array('bot', 'spider', 'slurp');
		foreach($robots as $robot) {
			if(strpos($agent, $robot) !== FALSE) {
				$browser['name'] = 'robot';
				return $browser;
			}
		}
	}
	return $browser;
}


function is_robot() {
	$agent = _SERVER('HTTP_USER_AGENT');
	$robots = array('bot', 'spider', 'slurp');
	foreach($robots as $robot) {
		if(strpos($agent, $robot) !== FALSE) {
			return TRUE;
		}
	}
	return FALSE;
}

function browser_lang() {
	$accept = _SERVER('HTTP_ACCEPT_LANGUAGE');
	$accept = substr($accept, 0, strpos($accept, ';'));
	if(strpos($accept, 'en') !== FALSE) {
		return 'en';
	} else {
		return 'vi-vn';
	}
}

// Safely request a URL
// ini_set('default_socket_timeout', 60);
function http_get($url, $cookie = '', $timeout = 30, $times = 3) {
	//return '';
//	$arr = array(
//			'ssl' => array (
//			'verify_peer'   => TRUE,
//			'cafile'        => './cacert.pem',
//			'verify_depth'  => 5,
//			'method'  	=> 'GET',
//			'timeout'  	=> $timeout,
//			'CN_match'      => 'secure.example.com'
//		)
//	);
	if(substr($url, 0, 8) == 'https://') {
		return https_get($url, $cookie, $timeout, $times);
	}
	$arr = array(
		'http' => array(
			'method'=> 'GET',
			'timeout' => $timeout
		)
	);
	$stream = stream_context_create($arr);
	while($times-- > 0) {
		$s = file_get_contents($url, NULL, $stream, 0, 4096000);
		if($s !== FALSE) return $s;
	}
	return FALSE;
}

function http_post($url, $post = '', $cookie='', $timeout = 30, $times = 3) {
	if(substr($url, 0, 8) == 'https://') {
		return https_post($url, $post, $cookie, $timeout, $times);
	}
	is_array($post) AND $post = http_build_query($post);
	is_array($cookie) AND $cookie = http_build_query($cookie);
	$stream = stream_context_create(array('http' => array('header' => "Content-type: application/x-www-form-urlencoded\r\nx-requested-with: XMLHttpRequest\r\nCookie: $cookie\r\n", 'method' => 'POST', 'content' => $post, 'timeout' => $timeout)));
	while($times-- > 0) {
		$s = file_get_contents($url, NULL, $stream, 0, 4096000);
		if($s !== FALSE) return $s;
	}
	return FALSE;
}

function https_get($url, $cookie = '', $timeout = 30, $times = 1) {
	if(substr($url, 0, 7) == 'http://') {
		return http_get($url, $cookie, $timeout, $times);
	}
	return https_post($url, '', $cookie, $timeout, $times, 'GET');
}

function https_post($url, $post = '', $cookie = '', $timeout = 30, $times = 1, $method = 'POST') {
	if(substr($url, 0, 7) == 'http://') {
		return http_post($url, $post, $cookie, $timeout, $times);
	}
	is_array($post) AND $post = http_build_query($post);
	is_array($cookie) AND $cookie = http_build_query($cookie);
	$w = stream_get_wrappers();
	$allow_url_fopen = strtolower(ini_get('allow_url_fopen'));
	$allow_url_fopen = (empty($allow_url_fopen) || $allow_url_fopen == 'off') ? 0 : 1;
	if(extension_loaded('openssl') && in_array('https', $w) && $allow_url_fopen) {
		$stream = stream_context_create(array('http' => array('header' => "Content-type: application/x-www-form-urlencoded\r\nx-requested-with: XMLHttpRequest\r\nCookie: $cookie\r\n", 'method' => $method, 'content' => $post, 'timeout' => $timeout)));
		$s = file_get_contents($url, NULL, $stream, 0, 4096000);
		return $s;
	} elseif (!function_exists('curl_init')) {
		return xn_error(-1, 'server not installed curl.');
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 2); // 1/2
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded', 'x-requested-with: XMLHttpRequest'));
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, _SERVER('HTTP_USER_AGENT'));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	if($method == 'POST') {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	$header = array('Content-type: application/x-www-form-urlencoded', 'X-Requested-With: XMLHttpRequest');
	if($cookie) {
		$header[] = "Cookie: $cookie";
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	
	(!ini_get('safe_mode') && !ini_get('open_basedir')) && curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	$data = curl_exec($ch);
	if(curl_errno($ch)) {
		return xn_error(-1, 'Errno'.curl_error($ch));
	}
	if(!$data) {
		curl_close($ch);
		return '';
	}

	list($header, $data) = explode("\r\n\r\n", $data);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if($http_code == 301 || $http_code == 302) {
		$matches = array();
		preg_match('/Location:(.*?)\n/', $header, $matches);
		$url = trim(array_pop($matches));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		$data = curl_exec($ch);
	}
	curl_close($ch);
	return $data;
}

function http_multi_get($urls) {
	// Nếu không được hỗ trợ, nó sẽ chuyển sang tìm nạp tuần tự đơn luồng
	$data = array();
	if(!function_exists('curl_multi_init')) {
		foreach($urls as $k=>$url) {
			$data[$k] = https_get($url);
		}
		return $data;
	}

	$multi_handle = curl_multi_init();
	foreach ($urls as $i => $url) {
		$conn[$i] = curl_init($url);
		curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
		$timeout = 3;
		curl_setopt($conn[$i], CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($conn[$i], CURLOPT_FOLLOWLOCATION, 1);
		//curl_easy_setopt(curl, CURLOPT_NOSIGNAL, 1);
		curl_multi_add_handle($multi_handle, $conn[$i]);
	}
	do {
		$mrc = curl_multi_exec($multi_handle, $active);
	} while ($mrc == CURLM_CALL_MULTI_PERFORM);
	
	while($active and $mrc == CURLM_OK) {
		if(curl_multi_select($multi_handle) != - 1) {
			do {
				$mrc = curl_multi_exec($multi_handle, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
	}
	foreach($urls as $i => $url) {
		$data[$i] = curl_multi_getcontent($conn[$i]);
		curl_multi_remove_handle($multi_handle, $conn[$i]);
		curl_close($conn[$i]);
	}
	return $data;
}


function file_replace_var($filepath, $replace = array(), $pretty = FALSE) {
	$ext = file_ext($filepath);
	if($ext == 'php') {
		$arr = include $filepath;
		$arr = array_merge($arr, $replace);
		$s = "<?php\r\nreturn ".var_export($arr, true).";\r\n?>";
		file_backup($filepath);
		$r = file_put_contents_try($filepath, $s);
		$r != strlen($s) ? file_backup_restore($filepath) : file_backup_unlink($filepath);
		return $r;
	} elseif($ext == 'js' || $ext == 'json') {
		$s = file_get_contents_try($filepath);
		$arr = xn_json_decode($s);
		if(empty($arr)) return FALSE;
		$arr = array_merge($arr, $replace);
		$s = xn_json_encode($arr, $pretty);
		file_backup($filepath);
		$r = file_put_contents_try($filepath, $s);
		$r != strlen($s) ? file_backup_restore($filepath) : file_backup_unlink($filepath);
		return $r;
	}
}

function file_backname($filepath) {
	
	$dirname = dirname($filepath);
	//$filename = file_name($filepath);
	$filepre = file_pre($filepath);
	$fileext = file_ext($filepath);
	$s = "$filepre.backup.$fileext";
	return $s;
}

function is_backfile($filepath) {
	return strpos($filepath, '.backup.') !== FALSE;
}

function file_backup($filepath) {
	$backfile = file_backname($filepath);
	if(is_file($backfile)) return TRUE;
	$r = xn_copy($filepath, $backfile);
	clearstatcache();
	return $r && filesize($backfile) == filesize($filepath);
}

function file_backup_restore($filepath) {
	$backfile = file_backname($filepath);
	$r = xn_copy($backfile, $filepath);
	clearstatcache();
	$r && filesize($backfile) == filesize($filepath) && xn_unlink($backfile);
	return $r;
}

function file_backup_unlink($filepath) {
	$backfile = file_backname($filepath);
	$r = xn_unlink($backfile);
	return $r;
}

function file_get_contents_try($file, $times = 3) {
	while($times-- > 0) {
		$fp = fopen($file, 'rb');
		if($fp) {
			$size = filesize($file);
			if($size == 0) return '';
			$s = fread($fp, $size);
			fclose($fp);
			return $s;
		} else {
			sleep(1);
		}
	}
	return FALSE;
}

function file_put_contents_try($file, $s, $times = 3) {
	while($times-- > 0) {
		$fp = fopen($file, 'wb');
		if($fp AND flock($fp, LOCK_EX)){
			$n = fwrite($fp, $s);
			version_compare(PHP_VERSION, '5.3.2', '>=') AND flock($fp, LOCK_UN);
			fclose($fp);
			clearstatcache();
			return $n;
		} else {
			sleep(1);
		}
	}
	return FALSE;
}

function in_string($s, $str) {
	if(!$s || !$str) return FALSE;
	$s = ",$s,";
	$str = ",$str,";
	return strpos($str, $s) !== FALSE;
}

function move_upload_file($srcfile, $destfile) {
	//$r = move_uploaded_file($srcfile, $destfile);
	$r = xn_copy($srcfile, $destfile);
	return $r;
}

function file_ext($filename, $max = 16) {
	$ext = strtolower(substr(strrchr($filename, '.'), 1));
	$ext = xn_urlencode($ext);
	strlen($ext) > $max AND $ext = substr($ext, 0, $max);
	if(!preg_match('#^\w+$#', $ext)) $ext = 'attach';
	return $ext;
}

function file_pre($filename, $max = 32) {
	return substr($filename, 0, strrpos($filename, '.'));
}

function file_name($path) {
	return substr($path, strrpos($path, '/') + 1);
}

/*function t($name = '') {
	global $starttime;
	header("Time $name:".substr(microtime(1) - $starttime, 0, 7));
}*/

function http_url_path() {
	$port = $_SERVER['SERVER_PORT'];
	$host = $_SERVER['HTTP_HOST'];
	$https = strtolower($_SERVER['HTTPS'] ?? 'off');
	$proto = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
	$path = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
	$http = (($port == 443) || $proto == 'https' || ($https && $https != 'off')) ? 'https' : 'http';
	return  "$http://$host$path/";
}

/**
 * URL format: http://www.domain.com/demo/?user-login.htm?a=b&c=d
 * URL format: http://www.domain.com/demo/?user-login.htm&a=b&c=d
 * URL format: http://www.domain.com/demo/user-login.htm?a=b&c=d
 * URL format: http://www.domain.com/demo/user-login.htm&a=b&c=d
 * array(
 *     0 => user,
 *     1 => login
 *     a => b
 *     c => d
 * )
 */
function xn_url_parse($request_url) {
	// ex: /demo/?user-login.htm?a=b&c=d
	// ex: /demo/user-login.htm?a=b&c=d
	$request_url = str_replace('/?', '/', $request_url);
	$arr = parse_url($request_url);
	
	$q = array_value($arr, 'path');
	$pos = strrpos($q, '/');
	$pos === FALSE && $pos = -1;
	$q = substr($q, $pos + 1);
	$sep = strpos($q, '?') === FALSE ? strpos($q, '&') : FALSE;
	if($sep !== FALSE) {
		$front = substr($q, 0, $sep);
		$behind = substr($q, $sep + 1);
	} else {
		$front = $q;
		$behind = '';
	}
	
	if(substr($front, -4) == '.htm') $front = substr($front, 0, -4);
	$r = $front ? (array)explode('-', $front) : array();
	
	$arr1 = $arr2 = $arr3 = array();
	$behind AND parse_str($behind, $arr1);
	
	if(!empty($arr['query'])) {
		parse_str($arr['query'], $arr2);
	} else {
		!empty($_GET) AND $_GET = array();
	}
	$arr3 = $arr1 + $arr2;
	if($arr3) {
		//array_diff_key($arr3, $_GET) || array_diff_key($_GET, $arr3);
		count($arr3) != count($_GET) AND $_GET = $arr3;
	} else {
		!empty($_GET) AND $_GET = array();
	}
	$r += $arr3;
	
	$_SERVER['REQUEST_URI_NO_PATH'] = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1);
	
	$conf = _SERVER('conf');
	if(!empty($conf['url_rewrite_on']) && $conf['url_rewrite_on'] == 3) {
		$r = xn_url_parse_path_format($_SERVER['REQUEST_URI']) + $r;
	}

	isset($r[0]) AND $r[0] == 'index.php' AND $r[0] = 'index';
	return $r;
}

function xn_url_add_arg($url, $k, $v) {
	$pos = strpos($url, '.htm');
	if($pos === FALSE) {
		return strpos($url, '?') === FALSE ? $url."&$k=$v" :  $url."?$k=$v";
	} else {
		return substr($url, 0, $pos).'-'.$v.substr($url, $pos);
	}
}

/**
 * URL format: http://www.domain.com/user/login?a=1&b=2
 * array(
 *     0 => user,
 *     1 => login,
 *     a => 1,
 *     b => 2
 * )
 */
function xn_url_parse_path_format($s) {
	$get = array();
	substr($s, 0, 1) == '/' AND $s = substr($s, 1);
	$arr = explode('/', $s);
	$get = $arr;
	$last = array_pop($arr);
	if(strpos($last, '?') !== FALSE) {
		$get = $arr;
		$arr1 = explode('?', $last);
		parse_str($arr1[1], $arr2);
		$get[] = $arr1[0];
		$get = array_merge($get, $arr2);
	}
	return $get;
}

function glob_recursive($pattern, $flags = 0) {
	$files = glob($pattern, $flags);
	foreach(glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
		 $files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
	}
	return $files;
}

function rmdir_recusive($dir, $keepdir = 0) {
	if($dir == '/' || $dir == './' || $dir == '../') return FALSE;
	if(!is_dir($dir)) return FALSE;
	
	substr($dir, -1) != '/' AND $dir .= '/';
	
	$files = glob($dir.'*'); // +glob($dir.'.*')
	foreach(glob($dir.'.*') as $v) {
		if(substr($v, -1) != '.' && substr($v, -2) != '..') $files[] = $v;
	}
	$filearr = $dirarr = array();
	if($files) {
		foreach($files as $file) {
			if(is_dir($file)) {
				$dirarr[] = $file;
			} else {
				$filearr[] = $file;
			}
		}
	}
	if($filearr) {
		foreach($filearr as $file) {
			xn_unlink($file);
		}
	}
	if($dirarr) {
		foreach($dirarr as $file) {
			rmdir_recusive($file);
		}
	}
	if(!$keepdir) xn_rmdir($dir);
	return TRUE;
}

function xn_copy($src, $dest) {
	$r = is_file($src) ? copy($src, $dest) : FALSE;
	return $r;
}

function xn_mkdir($dir, $mod = NULL, $recusive = NULL) {
	$r = !is_dir($dir) ? mkdir($dir, $mod, $recusive) : FALSE;
	return $r;
}

function xn_rmdir($dir) {
	$r = is_dir($dir) ? rmdir($dir) : FALSE;
	return $r;
}

function xn_unlink($file) {
	$r = is_file($file) ? unlink($file) : FALSE;
	return $r;
}

function xn_filemtime($file) {
	return is_file($file) ? filemtime($file) : 0;
}

/*
	xn_set_dir(123, APP_PATH.'upload');
	
	000/000/1.jpg
	000/000/100.jpg
	000/000/100.jpg
	000/000/999.jpg
	000/001/1000.jpg
	000/001/001.jpg
	000/002/001.jpg
*/
function xn_set_dir($id, $dir = './') {

	$id = sprintf("%09d", $id);
	$s1 = substr($id, 0, 3);
	$s2 = substr($id, 3, 3);
	$dir1 = $dir.$s1;
	$dir2 = $dir."$s1/$s2";

	!is_dir($dir1) && mkdir($dir1, 0777);
	!is_dir($dir2) && mkdir($dir2, 0777);
	return "$s1/$s2";
}

function xn_get_dir($id) {
	$id = sprintf("%09d", $id);
	$s1 = substr($id, 0, 3);
	$s2 = substr($id, 3, 3);
	return "$s1/$s2";
}

function copy_recusive($src, $dst) {
	substr($src, -1) == '/' AND $src = substr($src, 0, -1);
	substr($dst, -1) == '/' AND $dst = substr($dst, 0, -1);
	$dir = opendir($src);
	!is_dir($dst) AND mkdir($dst);
	while(FALSE !== ($file = readdir($dir))) {
		if(($file != '.') && ($file != '..')) {
			if(is_dir($src . '/' . $file)) {
				copy_recusive($src.'/'.$file,$dst.'/'.$file);
			}  else {
				xn_copy($src.'/'.$file, $dst.'/'.$file);
			}
		}
	}
	closedir($dir); 
}

function xn_rand($n = 16) {
	$str = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
	$len = strlen($str);
	$return = '';
	for($i=0; $i<$n; $i++) {
		$r = mt_rand(1, $len);
		$return .= $str[$r - 1];
	}
	return $return;
}

function xn_is_writable($file) {
	
	if(PHP_OS != 'WINNT') {
		return is_writable($file);
	} else {
		if(is_file($file)) {
			$fp = fopen($file, 'a+');
			if(!$fp) return FALSE;
			fclose($fp);
			return TRUE;
		} elseif(is_dir($file)) {
			$tmpfile = $file.uniqid().'.tmp';
			$r = touch($tmpfile);
			if(!$r) return FALSE;
			if(!is_file($tmpfile)) return FALSE;
			xn_unlink($tmpfile);
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

function xn_shutdown_handle() {
}

function xn_debug_info() {
	$db = $_SERVER['db'];
	$starttime = $_SERVER['starttime'];
	$s = '';
	if(DEBUG > 1) {
		$s .= '<fieldset class="fieldset small debug break-all">';
		$s .= '<p>Processed Time:'.(microtime(1) - $starttime).'</p>';
		if(IN_CMD) {
			foreach($db->sqls as $sql) {
				$s .= "$sql\r\n";
			}
		} else {
			$s .= "\r\n<ul>\r\n";
			foreach($db->sqls as $sql) {
				$s .= "<li>$sql</li>\r\n";
			}
			$s .= "</ul>\r\n";
			$s .= '_REQUEST:<br>';
			$s .= xn_txt_to_html(print_r($_REQUEST, 1));
			if(!empty($_SESSION)) {
				$s .= '_SESSION:<br>';
				$s .= xn_txt_to_html(print_r($_SESSION, 1));
			}
			$s .= '';
		}
		$s .= '</fieldset>';
	}
	return $s;
}

function base64_decode_file_data($data) {
	if(substr($data, 0, 5) == 'data:') {
		$data = substr($data, strpos($data, ',') + 1);
	}
	$data = base64_decode($data);
	return $data;
}

function http_404() {
	header('HTTP/1.1 404 Not Found'); 
	header('Status: 404 Not Found'); 
	echo '<h1>404 Not Found</h1>';
	exit;
}

function http_403() {
	header('HTTP/1.1 403 Forbidden'); 
	header('Status: 403 Forbidden'); 
	echo '<h1>403 Forbidden</h1>';
	exit;
}

function http_location($url) {
	header('Location:'.$url);
	exit;
}

function http_referer() {
	$len = strlen(http_url_path());
	$referer = param('referer');
	empty($referer) AND $referer = _SERVER('HTTP_REFERER');
	$referer2 = substr($referer, $len);
	if(strpos($referer, url('user-login')) !== FALSE || strpos($referer, url('user-logout')) !== FALSE || strpos($referer, url('user-create')) !== FALSE) {
		$referer = './';
	}
	// $referer = str_replace('\'', '', $referer);
	if(!preg_match('#^\\??[\w\-/]+\.htm$#', $referer2) && !preg_match('#^[\w\/]*$#', $referer2)) {
		$referer = './';
	}
	return $referer;
}

function str_push($str, $v, $sep = '_') {
	if(empty($str)) return $v;
	if(strpos($str, $v.$sep) === FALSE) {
		return $str.$sep.$v;
	}
	return $str;
}

function y2f($rmb) {
        $rmb = floor($rmb * 10 * 10);		
        return $rmb;
}

// $round: float round ceil floor
function f2y($rmb, $round = 'float') {
        $rmb = floor($rmb * 100) / 10000;
        if($round == 'float') {
                $rmb = number_format($rmb, 2, '.', '');
        } elseif($round == 'round') {
                $rmb = round($rmb);
        } elseif ($round == 'ceil') {
                $rmb = ceil($rmb);
        } elseif ($round == 'floor') {
                $rmb = floor($rmb);
        }
        return $rmb;
}

function _GET($k, $def = NULL) { return isset($_GET[$k]) ? $_GET[$k] : $def; }
function _POST($k, $def = NULL) { return isset($_POST[$k]) ? $_POST[$k] : $def; }
function _COOKIE($k, $def = NULL) { return isset($_COOKIE[$k]) ? $_COOKIE[$k] : $def; }
function _REQUEST($k, $def = NULL) { return isset($_REQUEST[$k]) ? $_REQUEST[$k] : $def; }
function _ENV($k, $def = NULL) { return isset($_ENV[$k]) ? $_ENV[$k] : $def; }
function _SERVER($k, $def = NULL) { return isset($_SERVER[$k]) ? $_SERVER[$k] : $def; }
function GLOBALS($k, $def = NULL) { return isset($GLOBALS[$k]) ? $GLOBALS[$k] : $def; }
function G($k, $def = NULL) { return isset($GLOBALS[$k]) ? $GLOBALS[$k] : $def; }
function _SESSION($k, $def = NULL) {
	global $g_session; 
	return isset($_SESSION[$k]) ? $_SESSION[$k] : (isset($g_session[$k]) ? $g_session[$k] : $def); 
}

?>
