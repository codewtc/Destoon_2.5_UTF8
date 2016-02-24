<?php 
defined('IN_DESTOON') or exit('Access Denied');
$admin_user = false;
if($_groupid == 1) {
	$admin_user = dcrypt(get_cookie('admin_user'), true);
	if($admin_user) {
		$user = explode('|', $admin_user);
		if($_username = $user[1]) {
			$userid = $user[0];
			$user = $db->get_one("SELECT username,company,truename,password,groupid,email,message,credit,money,loginip,level,edittime FROM {$DT_PRE}member WHERE userid=$userid LIMIT 0,1");
			if($user) {
				$_userid = $userid;
				extract($user, EXTR_PREFIX_ALL, '');
				$MG = cache_read('group-'.$_groupid.'.php');
				$admin_user = true;
			}
		}
	}
}
?>