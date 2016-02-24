<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
$MOD['ad_enable'] or dheader(DT_PATH);
require MD_ROOT.'/ad.class.php';
$do = new ad();
$TYPE = array('广告类型', '代码广告', '文字链接', '图片广告', 'Flash广告', '排名广告', '幻灯片广告');
$currency = $MOD['ad_currency'];
$unit = $currency == 'money' ? '元' : '积分';

$typeid = isset($typeid) ? intval($typeid) : 0;
$pid = isset($pid) ? intval($pid) : 0;
$item = $db->get_one("SELECT pid FROM {$DT_PRE}ad_place ORDER BY rand()");
$destoon_task = "moduleid=$moduleid&html=ad&itemid=$item[pid]";
if($action == 'buy' && $pid) {
	require DT_ROOT.'/include/post.func.php';
	$do->pid = $pid;
	$p = $do->get_one_place();
	$p or dalert('广告位不存在', './');
	$p['price'] or message('此广告位价格为面议，不可在线订购', './');
	if($p['typeid'] == 5) dheader(extendurl('spread'));
	$months = array(1, 2, 3, 6, 12, 24);
	$t = $db->get_one("SELECT MAX(totime) AS totime FROM {$DT_PRE}ad WHERE pid=$pid AND totime>$DT_TIME");
	$fromtime = $t['totime'] ? $t['totime'] : $DT_TIME + 86400;
	$fromdate = timetodate($fromtime, 3);
	$typeid = $p['typeid'];
	if($submit) {
		(is_date($post['fromtime']) && $post['fromtime'] >= $fromdate) or message('请选择开始投放日期');
		in_array($month, $months) or message('请选择购买时长');
		$amount = $p['price']*$month;
		if($currency == 'money') {
			if($amount > $_money) message('帐户余额不足，请充值', $MODULE[2]['linkurl'].'charge.php?action=pay&amount='.($amount-$_money));
		} else {
			if($amount > $_credit) message('会员积分不足，请购买', $MODULE[2]['linkurl'].'trade.php?action=credit');
		}
		is_payword($_username, $password) or message('您的支付密码不正确');
		$ad = array();
		$ad['image_src'] = $ad['flash_src'] = $ad['code'] = '';
		if($typeid == 1) {
			if(strlen($post['code']) < 10) message('请填写广告代码');
			$ad['code'] = $post['code'];
		} else if($typeid == 2) {
			if(strlen($post['text_name']) < 2) message('请填写链接文字');
			if(strlen($post['text_url']) < 10) message('请填写链接地址');
			$ad['text_name'] = $post['text_name'];
			$ad['text_url'] = fix_link($post['text_url']);
			$ad['text_title'] = $post['text_title'];
		} else if($typeid == 3) {
			if(strlen($post['image_src']) < 15) message('请填写图片地址或上传图片');
			$ad['image_src'] = $post['image_src'];
			$ad['image_url'] = fix_link($post['image_url']);
			$ad['image_alt'] = $post['image_alt'];
		} else if($typeid == 4) {
			if(strlen($post['flash_src']) < 15 || strpos($post['flash_src'], '.swf') === false) message('请填写Flash地址');
			$ad['flash_src'] = $post['flash_src'];
			$ad['flash_url'] = fix_link($post['flash_url']);
		} else if($typeid == 6) {
			if(strlen($post['image_src']) < 15) message('请填写图片地址或上传图片');
			if(strlen($post['image_url']) < 10) message('请填写链接地址');
			clear_upload($post['image_src']);
			$ad['code'] = '0|'.fix_link($post['image_url']).'|'.$post['image_src'];
		}
		if($currency == 'money') {
			money_add($_username, -$amount);
			money_record($_username, -$amount, '站内', 'system', '站内支付', $p['name'].'广告订购'.$month.'月');
		} else {
			credit_add($_username, -$amount);
			credit_record($_username, -$amount, 'system', $p['name'].'广告订购', $month.'月');
		}
		$ad['fromtime'] = $post['fromtime'];
		$ad['totime'] = timetodate(strtotime($post['fromtime']) + 86400*30*$month, 3);
		$ad['pid'] = $pid;
		$ad['typeid'] = $typeid;
		$ad['title'] = $post['fromtime'].'会员('.$_username.')订购';
		$ad['introduce'] = timetodate($DT_TIME, 5).' 已支付'.$amount.$unit;
		$ad['note'] = '会员在线订购 IP-'.$DT_IP;
		$ad['status'] = 2;
		$ad['username'] = $_username;
		$do->add($ad);
		message('广告订购成功，请等待工作人员处理', './', 5);
	} else {
		$head_title = '订购广告位 ['.$p['name'].']';
		include template('ad_buy', $module);
	}
} else {
	if($pid) {
		$MOD['ad_view'] or message('系统已关闭广告位预览功能，请直接与我们联系');
		$do->pid = $pid;
		$p = $do->get_one_place();
		$p or message('', $MOD['linkurl'].'ad.php');
		$ad = false;
		$filename = 'ad_'.$pid.'.htm';
		$typeid = $p['typeid'];
		if($typeid == 5) $filename = 'ad_m'.$p['moduleid'].'.htm';
		$head_title = '广告位 ['.$p['name'].'] 预览';
		$action = 'view';
		include template('ad', $module);
	} else {
		$head_title = $head_keywords = $head_description = '广告中心';
		$condition = '1';
		if($typeid) $condition .= " AND typeid=$typeid";
		$ads = $do->get_list_place($condition, 'listorder DESC,pid DESC');
		include template('ad', $module);
	}
}
?>