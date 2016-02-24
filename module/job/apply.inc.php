<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
$itemid or message('', $MOD['linkurl']);
login();

if(!check_group($_groupid, $MOD['group_apply'])) {
	$head_title = '抱歉，您所在的会员组没有权限访问此页面';
	exit(include template('noright', 'message'));
}

$item = $db->get_one("SELECT * FROM {$DT_PRE}job WHERE itemid=$itemid AND status=3");
$item or message('职位不存在', $MOD['linkurl']);
$linkurl = linkurl($MOD['linkurl'].$item['linkurl'], 1);
if(!$item['username']) message('该企业未注册本站会员，无法收到简历', $linkurl);
if($item['totime'] && $item['totime'] < $DT_TIME) message('职位已经过期', $linkurl);
if($item['username'] == $_username) message('您不能向自己公司投递简历', $linkurl);

$app = $db->get_one("SELECT * FROM {$DT_PRE}job_apply WHERE jobid=$itemid AND apply_username='$_username'");
if($app) message('您已经向此职位投递过简历', $linkurl);
if($submit) {
	$resumeid = intval($resumeid);
	$resumeid or message('', $linkurl);
	$resume = $db->get_one("SELECT * FROM {$DT_PRE}resume WHERE itemid=$resumeid AND status=3 AND open=3 AND username='$_username'");
	$resume or message('无效的简历ID', $linkurl);
	$db->query("INSERT INTO {$DT_PRE}job_apply (jobid,resumeid,job_username,apply_username,applytime,status) VALUES ('$itemid','$resumeid','$item[username]','$_username','$DT_TIME','1')");
	$db->query("UPDATE {$DT_PRE}job SET apply=apply+1 WHERE itemid=$itemid");
	$resumeurl = linkurl($MOD['linkurl'].$resume['linkurl'], 1);
	send_message($item['username'], '您的招聘['.dsubstr($item['title'], 20, '...').']收到新的简历', '详见:<a href="'.$resumeurl.'" target="_blank">'.$resumeurl.'</a>');
	message('简历投递成功', $linkurl);
} else {
	$lists = array();
	$result = $db->query("SELECT * FROM {$DT_PRE}resume WHERE username='$_username' AND status=3 AND open=3 ORDER BY edittime DESC");
	while($r = $db->fetch_array($result)) {
		$r['linkurl'] = $MOD['linkurl'].$r['linkurl'];
		$lists[] = $r;
	}
	if($lists) {
		$head_title = '投递简历'.$DT['seo_delimiter'].$item['title'].$DT['seo_delimiter'].$MOD['name'];
		include template('apply', $module);
	} else {
		message('请先创建简历', $MODULE[2]['linkurl'].$DT['file_my'].'?resume=1&action=add&mid='.$moduleid);
	}
}
?>