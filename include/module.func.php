<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2010 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
function get_fee($item_fee, $mod_fee) {
	if($item_fee < 0) {
		$fee = 0;
	} else if($item_fee == 0) {
		$fee = $mod_fee;
	} else {
		$fee = $item_fee;
	}
	return $fee;
}

function keyword($kw, $items, $moduleid) {
	global $db, $DT_PRE, $DT_TIME, $DT;
	if(!$DT['search_kw'] || strlen($kw) < 3 || strlen($kw) > 30 || $items < 1) return;
	$kw = addslashes($kw);
	$r = $db->get_one("SELECT * FROM {$DT_PRE}keyword WHERE moduleid=$moduleid AND word='$kw'");
	if($r) {
		if($r['status'] == 2) return;
		$items = $items > $r['items'] ? $items : $r['items'];
		$month_search = date('Y-m', $r['updatetime']) == date('Y-m', $DT_TIME) ? 'month_search+1' : '1';
		$week_search = date('W', $r['updatetime']) == date('W', $DT_TIME) ? 'week_search+1' : '1';
		$today_search = date('Y-m-d', $r['updatetime']) == date('Y-m-d', $DT_TIME) ? 'today_search+1' : '1';
		$db->query("UPDATE {$DT_PRE}keyword SET items='$items',updatetime='$DT_TIME',total_search=total_search+1,month_search=$month_search,week_search=$week_search,today_search=$today_search WHERE itemid=$r[itemid]");
	} else {
		$letter = gb2py($kw);
		$status = $DT['search_check_kw'] ? 2 : 3;
		$db->query("INSERT INTO {$DT_PRE}keyword (moduleid,word,keyword,letter,items,updatetime,total_search,month_search,week_search,today_search,status) VALUES ('$moduleid','$kw','$kw','$letter','$items','$DT_TIME','1','1','1','1','$status')");
	}
}

function money_add($username, $amount) {
	global $db, $DT_PRE;
	if($username && $amount) $db->query("UPDATE {$DT_PRE}member SET money=money+{$amount} WHERE username='$username'");
}

function money_lock($username, $amount) {
	global $db, $DT_PRE;
	if($username && $amount) $db->query("UPDATE {$DT_PRE}member SET money_lock=money_lock+{$amount} WHERE username='$username'");
}

function money_record($username, $amount, $bank, $editor, $reason, $note = '') {
	global $db, $DT_PRE, $DT_TIME;
	if($username && $amount) $db->query("INSERT INTO {$DT_PRE}finance_record (username,bank,amount,addtime,reason,note,editor) VALUES ('$username','$bank','$amount','$DT_TIME','$reason','$note','$editor')");
}

function credit_add($username, $amount) {
	global $db, $DT_PRE;
	if($username && $amount) $db->query("UPDATE {$DT_PRE}member SET credit=credit+{$amount} WHERE username='$username'");
}

function credit_record($username, $amount, $editor, $reason, $note = '') {
	global $db, $DT_PRE, $DT_TIME;
	if($username && $amount) $db->query("INSERT INTO {$DT_PRE}finance_credit (username,amount,addtime,reason,note,editor) VALUES ('$username','$amount','$DT_TIME','$reason','$note','$editor')");
}

function secondstodate($seconds) {
	$date = '';
	if($seconds > 0) {
		$t = floor($seconds/86400);
		if($t) {
			$date .= $t.'天';
			$seconds = $seconds%86400;
		}
		$t = floor($seconds/3600);
		if($t) {
			$date .= $t.'小时';
			$seconds = $seconds%3600;
		}
		$t = floor($seconds/60);
		if($t) {
			$date .= $t.'分';
			$seconds = $seconds%60;
		}
		if($seconds) {
			$date .= $seconds.'秒';
		}
	}
	return $date;
}

function get_status($status, $check) {
	if($status == 0) {//Recycle
		return 0;
	} else if($status == 1) {//Rejected
		return 2;
	} else if($status == 2) {//Checking
		return 2;
	} else if($status == 3) {//
		return $check ? 2 : 3;
	} else if($status == 4) {//Expired
		return $check ? 2 : 3;
	} else {
		return 2;
	}
}

function get_intro($content, $length = 200) {
	return $length ? dtrim(dsubstr(strip_tags($content), $length)) : '';
}

function get_description($content, $length) {
	if($length) {
		$content = str_replace(array(' ', '[pagebreak]'), array('', ''), $content);
		return nl2br(trim(dsubstr(strip_tags($content), $length, '...')));
	} else {
		return '';
	}
}

function get_module_setting($moduleid, $key = '') {
	$M = cache_read('module-'.$moduleid.'.php');
	return $key ? $M[$key] : $M;
}

function anti_spam($string) {
	global $MODULE;
	if(preg_match("/^[a-z0-9\.\-_@]+$/i", $string)) {
		return '<img src="'.$MODULE[3]['linkurl'].'image.php?auth='.urlencode(dcrypt($string)).'" align="absmddle"/>';
	} else {
		return $string;
	}
}

function hide_ip($ip, $sep = '*') {
	if(!preg_match("/[\d\.]{7,15}/", $ip)) return $ip;
	$tmp = explode('.', $ip);
	return $tmp[0].'.'.$tmp[1].'.'.$sep.'.'.$sep;
}

function check_pay($item, $username) {
	global $db, $DT_PRE;
	return $db->get_one("SELECT itemid FROM {$DT_PRE}finance_pay WHERE item='$item' AND username='$username'");
}

function check_sign($string, $sign) {
	return $sign == crypt_sign($string);
}

function crypt_sign($string) {
	global $CFG, $DT_IP;
	return strtoupper(md5(md5($DT_IP.$string.$CFG['authkey'])));
}

function text_write($itemid, $item, $content) {
	if(!$itemid || !$item || !$content) return;
	$text_dir = DT_ROOT.'/file/text/'.$item.'/'.dalloc($itemid).'/';
	if(!is_dir($text_dir)) {
		dir_create($text_dir);
		copy(DT_ROOT.'/file/index.html', $text_dir.'index.html');
	}
	file_put($text_dir.$itemid.'.php', '<?php exit; ?>'.stripslashes($content));
}

function text_delete($itemid, $item) {
	if(!$itemid || !$item) return;
	$text_file = DT_ROOT.'/file/text/'.$item.'/'.dalloc($itemid).'/'.$itemid.'.php';
	if(is_file($text_file)) unlink($text_file);
}

function text_read($itemid, $item) {
	if(!$itemid || !$item) return '';
	return substr(file_get(DT_ROOT.'/file/text/'.$item.'/'.dalloc($itemid).'/'.$itemid.'.php'), 14);
}

function cache_item($moduleid) {
	global $db, $DT_PRE;
	$data = array();
	$query = $db->query("SELECT catid,item FROM {$DT_PRE}category WHERE moduleid='$moduleid' ORDER BY listorder,catid");
	while($r = $db->fetch_array($query)) {
		$data[$r['catid']] = $r['item'];
	}
	cache_write('cateitem-'.$moduleid.'.php', $data);
}

function update_item($catid, $item) {
	global $db, $DT_PRE;
	$item = intval($item);
	$db->query("UPDATE {$DT_PRE}category SET item='$item' WHERE catid=$catid");
}

function keylink($content, $item) {
	global $KEYLINK;
	$KEYLINK or $KEYLINK = cache_read('keylink-'.$item.'.php');
	if(!$KEYLINK) return $content;
	foreach($KEYLINK as $v) {
		$p = strpos($content, $v['title']);
		if($p !== false) {
			$tmp = substr($content, 0, $p);
			$content = $tmp.'<a href="'.$v['url'].'" target="_blank"><strong class="keylink">'.$v['title'].'</strong></a>'.str_replace($tmp.$v['title'], '', $content);
		}
	}
	return $content;
}

function gender($gender, $type = 0) {
	if($type) return $gender == 1 ? '男' : '女';
	return $gender == 1 ? '先生' : '女士';
}

function fix_link($url) {
	if(strlen($url) < 10) return '';
	return strpos($url, '://') === false  ? 'http://'.$url : $url;
}

function vip_year($fromtime) {
	global $DT_TIME;
	return $fromtime ? intval(date('Y', $DT_TIME) - date('Y', $fromtime)) + 1  : 1;
}

function get_albums($item, $type = 0) {
	$imgs = array();
	if($type == 0) {
		$nopic = SKIN_PATH.'image/nopic60.gif';
		$imgs[] = $item['thumb'] ? $item['thumb'] : $nopic;
		$imgs[] = $item['thumb1'] ? $item['thumb1'] : $nopic;
		$imgs[] = $item['thumb2'] ? $item['thumb2'] : $nopic;
	} else if($type == 1) {
		$nopic = SKIN_PATH.'image/nopic240.gif';
		$imgs[] = $item['thumb'] ? str_replace('.thumb.', '.middle.', $item['thumb']) : $nopic;
		$imgs[] = $item['thumb1'] ? str_replace('.thumb.', '.middle.', $item['thumb1']) : $nopic;
		$imgs[] = $item['thumb2'] ? str_replace('.thumb.', '.middle.', $item['thumb2']) : $nopic;
	}
	return $imgs;
}

function xml_linkurl($linkurl, $modurl = '') {
	if(strpos($linkurl, '://') === false) $linkurl = linkurl($modurl).$linkurl;
	return str_replace('&', '&amp;', $linkurl);
}
?>