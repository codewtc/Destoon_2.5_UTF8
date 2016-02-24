<?php
defined('IN_DESTOON') or exit('Access Denied');
$menus = array (
    array('生成充值卡', '?moduleid='.$moduleid.'&file='.$file.'&action=add'),
    array('充值卡管理', '?moduleid='.$moduleid.'&file='.$file),
);
switch($action) {
	case 'add':
		if($submit) {
			$amount = dround($amount);
			if($amount <= 0) msg('面额格式错误');
			$prefix_length = strlen($prefix);
			$number_length = intval($number_length);
			if($number_length < 8) msg('卡号不能少于8位');
			$rand_length = $number_length - $prefix_length;
			if($rand_length < 4)  msg('卡号长度和前缀长度差不能少于4位');
			$password_length = intval($password_length);
			if($password_length < 6) msg('密码不能少于6位');
			$number_part = trim($number_part);
			if(!preg_match("/^[0-9a-zA-z]{6,}$/", $number_part)) msg('卡号只能由6位以上数字和字母组成');
			$totime = strtotime($totime);
			if($totime < $DT_TIME) msg('过期时间必须在当前时间之后');
			$total = intval($total);
			$total or $total = 100;
			$t = 0;
			for($i = 0; $i < $total; $i++) {
				$number = $prefix.random($rand_length, $number_part);
				if($db->get_one("SELECT itemid FROM {$DT_PRE}finance_card WHERE number='$number'")) {
					$i--;
				} else {
					$t++;
					$password = random($password_length, '0123456');
					$db->query("INSERT INTO {$DT_PRE}finance_card (number,password,amount,editor,addtime,totime) VALUES('$number','$password','$amount','$_username','$DT_TIME','$totime')");
				}
			}
			msg('成功生成 '.$t.' 个', '?moduleid='.$moduleid.'&file='.$file);
		} else {
			$prefix = mt_rand(1000, 9999);
			$totime = (timetodate($DT_TIME, "Y") + 3).timetodate($DT_TIME, '-m-d');
			include tpl('card_add', $module);
		}
	break;
	case 'delete':
		$itemid or msg('未选择记录');
		$itemids = is_array($itemid) ? implode(',', $itemid) : $itemid;
		$db->query("DELETE FROM {$DT_PRE}finance_card WHERE itemid IN ($itemids)");
		dmsg('删除成功', $forward);
	break;
	case 'export':
		$sfields = array('按条件', '卡号', '密码', '面额', '会员', 'IP', '操作人');
		$dfields = array('number', 'number', 'password', 'amount', 'username', 'ip', 'editor');
		$sorder  = array('排序方式', '面额降序', '面额升序', '充值时间降序', '充值时间升序', '到期时间降序', '到期时间升序', '制卡时间降序', '制卡时间升序');
		$dorder  = array('itemid DESC', 'amount DESC', 'amount ASC', 'updatetime DESC', 'updatetime ASC', 'totime DESC', 'totime ASC', 'addtime DESC', 'addtime ASC');
		isset($fields) && isset($dfields[$fields]) or $fields = 0;
		isset($dfromtime) or $dfromtime = '';
		isset($dtotime) or $dtotime = '';
		isset($type) or $type = 0;
		isset($status) or $status = 0;
		isset($order) && isset($dorder[$order]) or $order = 0;
		$fields_select = dselect($sfields, 'fields', '', $fields);
		$order_select = dselect($sorder, 'order', '', $order);
		$condition = '';
		if($keyword) $condition .= " AND $dfields[$fields]='$keyword'";
		if($status == 1) $condition .= " AND updatetime>0";
		if($status == 2) $condition .= " AND totime<$DT_TIME";
		$times = array('updatetime', 'updatetime', 'totime', 'addtime');
		$time = $times[$type];
		if($dfromtime) $condition .= " AND $time>".(strtotime($dfromtime.' 00:00:00'));
		if($dtotime) $condition .= " AND $time<".(strtotime($dtotime.' 23:59:59'));
		$data = '卡号,密码,面额,有效期至,充值会员,充值时间,充值IP,制卡人,制卡时间'."\n";
		$result = $db->query("SELECT * FROM {$DT_PRE}finance_card WHERE 1 $condition ORDER BY $dorder[$order]");
		$income = $expense = 0;
		while($r = $db->fetch_array($result)) {
			$r['addtime'] = timetodate($r['addtime'], 5);
			$r['totime'] = timetodate($r['totime'], 3);
			$r['updatetime'] = $r['updatetime'] ? timetodate($r['updatetime'], 5) : '未使用';
			$data .= $r['number'].','.$r['password'].','.$r['amount'].','.$r['totime'].','.$r['username'].','.$r['updatetime'].','.$r['ip'].','.$r['editor'].','.$r['addtime']."\n";
		}
		ob_start();
		header('Cache-control: max-age=31536000');
		header('Expires: '.gmdate('D, d M Y H:i:s', $DT_TIME + 31536000).' GMT');
		header('Content-Length: '.strlen($data));
		header('Content-Disposition: attachment; filename=充值卡-'.date('Y-m-d-H-i-s', $DT_TIME).'.csv');
		header('Content-Type:application/octet-stream');
		echo $data;
		exit;
	break;
	default:
		$print = isset($print) ? 1 : 0;
		$sfields = array('按条件', '卡号', '密码', '面额', '会员', 'IP', '操作人');
		$dfields = array('number', 'number', 'password', 'amount', 'username', 'ip', 'editor');
		$sorder  = array('排序方式', '面额降序', '面额升序', '充值时间降序', '充值时间升序', '到期时间降序', '到期时间升序', '制卡时间降序', '制卡时间升序');
		$dorder  = array('itemid DESC', 'amount DESC', 'amount ASC', 'updatetime DESC', 'updatetime ASC', 'totime DESC', 'totime ASC', 'addtime DESC', 'addtime ASC');
		isset($fields) && isset($dfields[$fields]) or $fields = 0;
		isset($fromtime) or $fromtime = '';
		isset($totime) or $totime = '';
		isset($dfromtime) or $dfromtime = '';
		isset($dtotime) or $dtotime = '';
		isset($type) or $type = 0;
		isset($status) or $status = 0;
		isset($order) && isset($dorder[$order]) or $order = 0;
		$fields_select = dselect($sfields, 'fields', '', $fields);
		$order_select = dselect($sorder, 'order', '', $order);
		$condition = '';
		if($keyword) $condition .= " AND $dfields[$fields]='$keyword'";
		if($print) $condition .= " AND updatetime=0  AND totime>$DT_TIME";
		if($status == 1) $condition .= " AND updatetime>0";
		if($status == 2) $condition .= " AND totime<$DT_TIME";
		$times = array('updatetime', 'updatetime', 'totime', 'addtime');
		$time = $times[$type];
		if($fromtime) $condition .= " AND $time>".(strtotime($fromtime.' 00:00:00'));
		if($totime) $condition .= " AND $time<".(strtotime($totime.' 23:59:59'));
		$r = $db->get_one("SELECT COUNT(*) AS num FROM {$DT_PRE}finance_card WHERE 1 $condition");
		$pages = pages($r['num'], $page, $pagesize);		
		$lists = array();
		$result = $db->query("SELECT * FROM {$DT_PRE}finance_card WHERE 1 $condition ORDER BY $dorder[$order] LIMIT $offset,$pagesize");
		$income = $expense = 0;
		while($r = $db->fetch_array($result)) {
			$r['addtime'] = timetodate($r['addtime'], 5);
			$r['totime'] = timetodate($r['totime'], 3);
			$r['updatetime'] = $r['updatetime'] ? timetodate($r['updatetime'], 5) : '未使用';			
			$lists[] = $r;
		}
		include tpl('card', $module);
	break;
}
?>