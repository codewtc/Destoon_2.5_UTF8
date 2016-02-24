<?php
defined('IN_DESTOON') or exit('Access Denied');
function update_company_setting($userid, $setting) {
	global $db, $DT_PRE;
	$S = get_company_setting($userid);
	foreach($setting as $k=>$v) {
		if(is_array($v)) {
			foreach($v as $i=>$j) {
				$v[$i] = str_replace(',', '', $j);
			}
			$v = implode(',', $v);
		}
		if(isset($S[$k])) {
			$db->query("UPDATE {$DT_PRE}company_setting SET item_value='$v' WHERE userid='$userid' AND item_key='$k'");
		} else {
			$db->query("INSERT INTO {$DT_PRE}company_setting (userid,item_key,item_value) VALUES ('$userid','$k','$v')");
		}
	}
	return true;
}
function get_company_setting($userid, $key = '') {
	global $db, $DT_PRE;
	if($key) {
		$r = $db->get_one("SELECT * FROM {$DT_PRE}company_setting WHERE userid='$userid' AND item_key='$key'");
		return $r ? $r['item_value'] : '';
	} else {
		$setting = array();
		$query = $db->query("SELECT * FROM {$DT_PRE}company_setting WHERE userid='$userid'");
		while($r = $db->fetch_array($query)) {
			$setting[$r['item_key']] = $r['item_value'];
		}
		return $setting;
	}
}
?>