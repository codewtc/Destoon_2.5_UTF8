<?php 
defined('IN_DESTOON') or exit('Access Denied');
if(!$_userid) dheader($MODULE[2]['linkurl'].$DT['file_my']);
require DT_ROOT.'/module/'.$module.'/common.inc.php';
if($action == 'logout' && $admin_user) {
	set_cookie('admin_user', '');
	dmsg('注销成功', $MODULE[2]['linkurl']);
}
require MD_ROOT.'/member.class.php';
require DT_ROOT.'/include/post.func.php';
$do = new member;
if($submit) {
	$note = htmlspecialchars($note);
	if(char_count($note) > 1000) message('便笺限1000字');
	$db->query("UPDATE {$DT_PRE}company_data SET mynote='$note' WHERE userid=$_userid");
	dmsg('更新成功', $MODULE[2]['linkurl']);
} else {
	$head_title = '';
	$do->userid = $_userid;
	$user = $do->get_one();
	extract($user);
	$logintime = timetodate($logintime, 5);
	$regtime = timetodate($regtime, 5);
	$userurl = userurl($_username, '', $domain);
	$note = $db->get_one("SELECT mynote FROM {$DT_PRE}company_data WHERE userid=$_userid");
	$note = $note['mynote'];
	$trade = $db->counter("{$DT_PRE}finance_trade", "seller='$_username' AND status=0", '');
	$expired = $totime && $totime < $DT_TIME ? true : false;
	$havedays = $expired ? 0 : ceil(($totime-$DT_TIME)/86400);
	include template('index', $module);
}
?>