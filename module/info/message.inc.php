<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
$itemid = intval($itemid);
$itemid or dheader($MOD['linkurl']);
$item = $db->get_one("SELECT title,islink,linkurl,totime,username,company,vip FROM {$table} WHERE itemid=$itemid AND status>2");
$error = '';
$item or dalert('信息不存在或正在审核', $MOD['linkurl']);
$linkurl = $item['islink'] ? $item['linkurl'] : linkurl($MOD['linkurl'].$item['linkurl'], 1);
require DT_ROOT.'/include/post.func.php';
if($_userid) {
	$user = userinfo($_username);
	$company = $user['company'];
	$truename = $user['truename'];
	$telephone = $user['telephone'] ? $user['telephone'] : $user['mobile'];
	$email = $user['mail'] ? $user['mail'] : $user['email'];
	$qq = $user['qq'];
	$msn = $user['msn'];
}
$MG['message_limit'] > -1 or $error = '您所在的会员组没有权限使用此功能';
$limit_used = $limit_free = 0;
if($MG['message_limit']) {
	$today = strtotime(timetodate($DT_TIME, 3).' 00:00:00');
	$sql = $_userid ? "fromuser='$_username'" : "ip='$DT_IP'";
	$r = $db->get_one("SELECT COUNT(*) AS num FROM {$DT_PRE}message WHERE $sql AND addtime>$today AND status=3");
	$limit_used = $r['num'];
	$limit_used < $MG['message_limit'] or $error = '今日可发送'.$MG['message_limit'].'次 当前已发送'.$limit_used.'次';
	$limit_free = $MG['message_limit'] > $limit_used ? $MG['message_limit'] - $limit_used : 0;
}
if($item['totime'] && $DT_TIME > $item['totime']) $error = '此信息已过期';
if(!$item['username']) $error = '该企业未注册本站会员，无法收到留言';
$need_captcha = $MOD['captcha_message'] == 2 ? $MG['captcha'] : $MOD['captcha_message'];
$need_question = $MOD['question_message'] == 2 ? $MG['question'] : $MOD['question_message'];
if($submit) {
	if($error) dalert($error);
	if($_username && $_username == $item['username']) dalert('请不要给自己的信息留言');
	$msg = captcha($captcha, $need_captcha, true);
	if($msg) dalert($msg);
	$msg = question($answer, $need_question, true);
	if($msg) dalert($msg);
	$title = htmlspecialchars(trim($title));
	if(!$title) dalert('请填写主题');
	$content = htmlspecialchars(trim($content));
	if(!$content) dalert('请填写内容');
	if(!$_userid) {
		$truename = htmlspecialchars(trim($truename));
		if(!$truename) dalert('请填写联系人');
		if(!is_email($email)) dalert('电子邮件格式错误');
		$telephone = htmlspecialchars(trim($telephone));
		$company = htmlspecialchars(trim($company));
		$qq = htmlspecialchars(trim($qq));
		$msn = htmlspecialchars(trim($msn));
	}
	$content = nl2br($content);
	$content .= '<hr size="1"/>联系人：'.$truename;
	$content .= '<br/>电子邮件：'.$email;
	if($company) $content .= '<br/>公司名：'.$company;
	if($telephone) $content .= '<br/>联系电话：'.$telephone;
	if(is_numeric($qq)) $content .= '<br/>联系QQ：'.$qq;
	if(is_email($msn)) $content .= '<br/>联系MSN：'.$msn;
	if(is_date($date)) $content .= '<hr size="1"/>我希望在 '.$date.' 之前回复';	
	$message = '产品信息：<a href="'.$linkurl.'"><strong>'.$item['title'].'</strong></a><br/>'.$content;
	if(send_message($item['username'], $title, $message, 3, $_username)) {
		dalert('留言发送成功', '', 'parent.window.location=parent.window.location;');
	} else {
		dalert($_userid ? '留言发送失败，对方可能拒绝您的留言' : '留言发送失败，对方可能拒绝非会员的留言');
	}
} else {	
	$iask = explode('|', trim($MOD['message_ask']));
	isset($content) or $content = '';
	$date = timetodate($DT_TIME + 5*86400, 3);
	$title = '我对您发布的“'.$item['title'].'”很感兴趣';
	$head_title = '留言信息'.$DT['seo_delimiter'].$item['title'].$DT['seo_delimiter'].$MOD['name'];
	include template('message', $module);
}
?>