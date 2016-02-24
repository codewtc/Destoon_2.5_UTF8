<?php
defined('IN_DESTOON') or exit('Access Denied');
require MD_ROOT.'/member.class.php';
$do = new member;
$menus = array (
    array('添加会员', '?moduleid='.$moduleid.'&action=add'),
    array('会员列表', '?moduleid='.$moduleid),
    array('审核会员', '?moduleid='.$moduleid.'&action=check'),
    array('会员升级', '?moduleid='.$moduleid.'&file=grade&action=check'),
    array('公司列表', '?moduleid=4'),
    array(VIP.'列表', '?moduleid=4&file=vip'),
);
isset($userid) or $userid = 0;
$CATEGORY = cache_read('category-4.php');

if(in_array($action, array('add', 'edit'))) {
	$MFD = cache_read('fields-member.php');
	$CFD = cache_read('fields-company.php');
	isset($post_fields) or $post_fields = array();
	if($MFD || $CFD) require DT_ROOT.'/include/fields.func.php';
}

if(in_array($action, array('', 'check'))) {
	$sfields = array('按条件', '公司名', '会员名', '通行证名','姓名', '手机号码', '部门', '职位', 'Email', 'MSN', 'QQ', '注册IP', '登录IP', '推荐人', '客户端');
	$dfields = array('username', 'company', 'username', 'passport', 'truename', 'mobile', 'department', 'career', 'email', 'msn', 'qq', 'regip', 'loginip', 'inviter', 'agent');
	$sorder  = array('结果排序方式', '注册时间降序', '注册时间升序', '登录时间降序', '登录时间升序', '登录次数降序', '登录次数升序', '账户资金降序', '账户资金升序', '会员积分降序', '会员积分升序');
	$dorder  = array('userid DESC', 'regtime DESC', 'regtime ASC', 'logintime DESC', 'logintime ASC', 'logintimes DESC', 'logintimes ASC', 'money ASC', 'money DESC', 'credit ASC', 'credit DESC');
	$sgender = array('性别', '先生' , '女士');
	$sprofile = array('资料', '完善' , '未完善');

	isset($fields) && isset($dfields[$fields]) or $fields = 0;
	isset($order) && isset($dorder[$order]) or $order = 0;
	$groupid = isset($groupid) ? intval($groupid) : 0;
	$gender = isset($gender) ? intval($gender) : 0;
	$profile = isset($profile) ? intval($profile) : 0;

	$fields_select = dselect($sfields, 'fields', '', $fields);
	$order_select  = dselect($sorder, 'order', '', $order);
	$gender_select = dselect($sgender, 'gender', '', $gender);
	$profile_select = dselect($sprofile, 'profile', '', $profile);
	$group_select = group_select('groupid', '会员组', $groupid);

	$condition = $action ? 'groupid=4' : '1';//
	if($keyword) $condition .= " AND $dfields[$fields] LIKE '%$keyword%'";
	if($gender) $condition .= " AND gender=$gender";
	if($groupid) $condition .= " AND groupid=$groupid";
	if($profile) $condition .= $profile == 1 ? " AND edittime>0" : " AND edittime=0";
}
if(in_array($action, array('add', 'edit'))) {
	$COM_TYPE = explode('|', $MOD['com_type']);
	$COM_SIZE = explode('|', $MOD['com_size']);
	$COM_MODE = explode('|', $MOD['com_mode']);
	$MONEY_UNIT = explode('|', $MOD['money_unit']);
}
switch($action) {
	case 'add':
		if($submit) {
			$member['banner'] = '';
			$member['groupid'] = $member['regid'];
			if($member['groupid'] == 5) $member['company'] = $member['truename'].'(个人)';
			$member['passport'] = $member['passport'] ? $member['passport'] : $member['username'];
			$member['edittime'] = $member['edittime'] ? $DT_TIME : 0;
			if($MFD) fields_check($post_fields, $MFD);
			if($CFD) fields_check($post_fields, $CFD);
			if($do->add($member)) {
				if($MFD) fields_update($post_fields, $do->tb_member, $do->userid, 'userid', $MFD);
				if($CFD) fields_update($post_fields, $do->tb_company, $do->userid, 'userid', $CFD);
				if($MOD['welcome'] > 0) {
					$post = $member;
					$username = $post['username'];
					$title = '欢迎加入'.$DT['sitename'];
					$content = ob_template('welcome', 'mail');
					if($MOD['welcome'] == 1 || $MOD['welcome'] == 3) send_message($username, $title, $content);
					if($MOD['welcome'] == 2 || $MOD['welcome'] == 3) send_mail($post['email'], $title, $content);
				}
				dmsg('添加成功', $forward);
			} else {
				msg($do->errmsg);
			}
		} else {
			include tpl('member_add', $module);
		}
	break;
	case 'edit':
		$userid or msg();
		$do->userid = $userid;
		if($submit) {
			$member['edittime'] = $member['edittime'] ? $DT_TIME : 0;
			$member['validtime'] = $member['validtime'] ? strtotime($member['validtime']) : 0;
			if($userid == 1 || $userid == $CFG['founderid']) $member['groupid'] = 1;
			if($MFD) fields_check($post_fields, $MFD);
			if($CFD) fields_check($post_fields, $CFD);
			if($do->edit($member)) {
				if($MFD) fields_update($post_fields, $do->tb_member, $do->userid, 'userid', $MFD);
				if($CFD) fields_update($post_fields, $do->tb_company, $do->userid, 'userid', $CFD);
				dmsg('修改成功', $forward);
			} else {
				msg($do->errmsg);
			}
		} else {
			$user = $do->get_one();
			extract($user);
			$d = $db->get_one("SELECT content FROM {$DT_PRE}company_data WHERE userid=$userid");
			$introduce = $d['content'];
			$cates = $catid ? explode(',', substr($catid, 1, -1)) : array();
			$validtime = $validtime ? timetodate($validtime, 3) : '';
			include tpl('member_edit', $module);
		}
	break;
	case 'show':
		$username = isset($username) ? $username : '';
		($userid || $username) or msg('会员不存在');
		if($userid) $do->userid = $userid;
		$user = $do->get_one($username);
		$user or msg('会员不存在');
		extract($user);
		include tpl('member_show', $module);
	break;
	case 'delete':
		$userid or msg('请选择会员');
		if($do->delete($userid)) {
			dmsg('删除成功', $forward);
		} else {
			msg($do->errmsg);
		}
	break;
	case 'move':
		$userid or msg('请选择会员');
		$gid = $groupids ? $groupids : $groupid;
		$do->move($userid, $gid);
		dmsg('移动成功', $forward);
	break;
	case 'check':
		if($userid) {
			$do->check($userid);
			dmsg('审核成功', $forward);
		} else {
			$members = $do->get_list($condition, $dorder[$order]);
			include tpl('member_check', $module);
		}
	break;
	case 'rename':
		$cusername or message('当前会员名不能为空');
		$nusername or message('会员名不能为空');
		if($do->rename($cusername, $nusername)) {
			dmsg('修改成功', $forward);
		} else {
			msg($do->errmsg);
		}
	break;
	case 'login':
		if($userid) {
			$auth = dcrypt($userid.'|'.$_username);
			set_cookie('admin_user', $auth);
			msg('授权成功，正在转入会员商务中心...', $MODULE[2]['linkurl']);
		} else {
			msg();
		}
	break;
	case 'unlock':
		$ip or msg('请填写需要解锁的IP');
		$ipfile = CE_ROOT.'/ban/'.$ip.'.php';
		if(is_file($ipfile)) {
			cache_delete($ip.'.php', 'ban');
			msg('IP:'.$ip.' 已经成功解除锁定', $forward);
		} else {
			msg('IP:'.$ip.' 未被系统锁定');
		}
	break;
	default:
		$members = $do->get_list($condition, $dorder[$order]);
		include tpl('member', $module);
	break;
}
?>