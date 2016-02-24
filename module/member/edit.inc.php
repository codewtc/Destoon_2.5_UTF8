<?php 
defined('IN_DESTOON') or exit('Access Denied');
login();
require DT_ROOT.'/module/'.$module.'/common.inc.php';
require MD_ROOT.'/member.class.php';
require DT_ROOT.'/include/post.func.php';
$do = new member;
$do->userid = $_userid;
$user = $do->get_one();
$CATEGORY = cache_read('category-4.php');

$MFD = cache_read('fields-member.php');
$CFD = cache_read('fields-company.php');
isset($post_fields) or $post_fields = array();
if($MFD || $CFD) require DT_ROOT.'/include/fields.func.php';

$tab = isset($tab) ? intval($tab) : 0;
if($submit) {
	if($post['password'] && $user['password'] != md5(md5($post['oldpassword']))) message('现有密码错误');
	if($post['payword'] && $user['payword'] != md5(md5($post['oldpayword']))) message('现有支付密码错误');
	$post['groupid'] = $user['groupid'];
	$post['email'] = $user['email'];
	$post['passport'] = $user['passport'];
	$post['company'] = $user['company'];
	$post['domain'] = $user['domain'];
	$post['icp'] = $user['icp'];
	$post['banner'] = $user['banner'];
	$post['validated'] = $user['validated'];
	$post['validator'] = $user['validator'];
	$post['validtime'] = $user['validtime'];
	$post['skin'] = $user['skin'];
	$post['template'] = $user['template'];
	$post['edittime'] = $DT_TIME;
	if($MFD) fields_check($post_fields, $MFD);
	if($CFD) fields_check($post_fields, $CFD);
	if($do->edit($post)) {
		if($MFD) fields_update($post_fields, $do->tb_member, $do->userid, 'userid', $MFD);
		if($CFD) fields_update($post_fields, $do->tb_company, $do->userid, 'userid', $CFD);
		if($user['edittime'] == 0 && $user['inviter'] && $MOD['credit_user']) {
			$inviter = $user['inviter'];
			$r = $db->get_one("SELECT itemid FROM {$DT_PRE}finance_credit WHERE note='$_username' AND username='$inviter'");
			if(!$r) {
				credit_add($inviter, $MOD['credit_user']);
				credit_record($inviter, $MOD['credit_user'], 'system', '会员推广', $_username);
			}
		}
		if($user['edittime'] == 0 && $MOD['credit_edit']) {
			credit_add($_username, $MOD['credit_edit']);
			credit_record($_username, $MOD['credit_edit'], 'system', '完善资料', $DT_IP);
		}
		message('资料修改成功', '?tab='.$tab);//Not dmsg() For Change PW To LogOut
	} else {
		message($do->errmsg);
	}
} else {
	$COM_TYPE = explode('|', $MOD['com_type']);
	$COM_SIZE = explode('|', $MOD['com_size']);
	$COM_MODE = explode('|', $MOD['com_mode']);
	$MONEY_UNIT = explode('|', $MOD['money_unit']);
	$head_title = '修改资料';
	extract($user);
	$mode_check = dcheckbox($COM_MODE, 'post[mode][]', $mode, 'onclick="check_mode(this, '.$MOD['mode_max'].');"', 0);
	$d = $db->get_one("SELECT content FROM {$DT_PRE}company_data WHERE userid=$userid");
	$introduce = $d['content'];
	$cates = $catid ? explode(',', substr($catid, 1, -1)) : array();
	$tab = isset($tab) ? intval($tab) : -1;
	if($tab == 2 && $_groupid < 6) $tab = 0;
	include template('edit', $module);
}
?>