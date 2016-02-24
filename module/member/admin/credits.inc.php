<?php
defined('IN_DESTOON') or exit('Access Denied');
$menus = array (
    array('积分奖惩', '?moduleid='.$moduleid.'&file='.$file.'&action=add'),
    array('积分记录', '?moduleid='.$moduleid.'&file='.$file),
);
$BANKS = explode('|', trim($MOD['pay_banks']));
switch($action) {
	case 'add':
		if($submit) {
			$username or msg('请填写会员名');
			$amount or msg('请填写分值');
			$reason or msg('请填写事由');
			$amount = intval($amount);
			if($amount <= 0) msg('分值格式错误');
			$username = trim($username);
			$r = $db->get_one("SELECT username,credit FROM {$DT_PRE}member WHERE username='$username'");
			if(!$r) msg('会员不存在');
			if(!$type) {
				if($r['credit'] < $amount) msg('会员积分不足，当前积分为:'.$r['credit']);
				$amount = -$amount;
			}
			$reason or $reason = '奖励';
			$note or $note = '手工';
			credit_add($username, $amount);
			credit_record($username, $amount, $_username, $reason, $note);
			dmsg('添加成功', '?moduleid='.$moduleid.'&file='.$file);
		} else {
			include tpl('credits_add', $module);
		}
	break;
	case 'delete':
		$itemid or msg('未选择记录');
		$itemids = is_array($itemid) ? implode(',', $itemid) : $itemid;
		$db->query("DELETE FROM {$DT_PRE}finance_credit WHERE itemid IN ($itemids)");
		dmsg('删除成功', $forward);
	break;
	case 'export':
		$dfields = array('username', 'username', 'amount', 'reason', 'note', 'editor');
		$dorder  = array('itemid DESC', 'amount DESC', 'amount ASC', 'addtime DESC', 'addtime ASC');
		isset($fields) && isset($dfields[$fields]) or $fields = 0;
		isset($type) or $type = 0;
		isset($order) && isset($dorder[$order]) or $order = 0;
		$condition = '1';
		if($keyword) $condition .= " AND $dfields[$fields] LIKE '%$keyword%'";
		if($dfromtime) $condition .= " AND addtime>".(strtotime($dfromtime.' 00:00:00'));
		if($dtotime) $condition .= " AND addtime<".(strtotime($dtotime.' 23:59:59'));
		if($type) $condition .= $type == 1 ? " AND amount>0" : " AND amount<0" ;
		$data = '流水号,收入,支出,会员名称,发生时间,操作人,事由,备注'."\n";
		$result = $db->query("SELECT * FROM {$DT_PRE}finance_credit WHERE $condition ORDER BY $dorder[$order]");
		$income = $expense = 0;
		while($r = $db->fetch_array($result)) {
			$r['addtime'] = date('Y-m-d H:i', $r['addtime']);
			$r['amount'] > 0 ? $income += $r['amount'] : $expense += $r['amount'];
			$data .= $r['itemid'].','.($r['amount'] > 0 ? $r['amount'] : '').','.($r['amount'] < 0 ? $r['amount'] : '').','.$r['username'].','.$r['addtime'].','.$r['editor'].','.trim($r['reason']).','.trim($r['note'])."\n";
		}
		$data .= '小计,'.$income.','.$expense.',,,,,,';
		ob_start();
		header('Cache-control: max-age=31536000');
		header('Expires: '.gmdate('D, d M Y H:i:s', $DT_TIME + 31536000).' GMT');
		header('Content-Length: '.strlen($data));
		header('Content-Disposition: attachment; filename=积分记录-'.date('Y-m-d-H-i-s', $DT_TIME).'.csv');
		header('Content-Type:application/octet-stream');
		echo $data;
		exit;
	break;
	default:
		$sfields = array('按条件', '会员名', '金额', '事由', '备注', '操作人');
		$dfields = array('username', 'username', 'amount', 'reason', 'note', 'editor');
		$sorder  = array('排序方式', '金额降序', '金额升序', '时间降序', '时间升序');
		$dorder  = array('itemid DESC', 'amount DESC', 'amount ASC', 'addtime DESC', 'addtime ASC');
		isset($fields) && isset($dfields[$fields]) or $fields = 0;
		isset($fromtime) or $fromtime = '';
		isset($totime) or $totime = '';
		isset($dfromtime) or $dfromtime = '';
		isset($dtotime) or $dtotime = '';
		isset($type) or $type = 0;
		isset($order) && isset($dorder[$order]) or $order = 0;
		$fields_select = dselect($sfields, 'fields', '', $fields);
		$order_select = dselect($sorder, 'order', '', $order);
		$condition = '';
		if($keyword) $condition .= " AND $dfields[$fields] LIKE '%$keyword%'";
		if($fromtime) $condition .= " AND addtime>".(strtotime($fromtime.' 00:00:00'));
		if($totime) $condition .= " AND addtime<".(strtotime($totime.' 23:59:59'));
		if($type) $condition .= $type == 1 ? " AND amount>0" : " AND amount<0" ;
		$r = $db->get_one("SELECT COUNT(*) AS num FROM {$DT_PRE}finance_credit WHERE 1 $condition");
		$pages = pages($r['num'], $page, $pagesize);		
		$records = array();
		$result = $db->query("SELECT * FROM {$DT_PRE}finance_credit WHERE 1 $condition ORDER BY $dorder[$order] LIMIT $offset,$pagesize");
		$income = $expense = 0;
		while($r = $db->fetch_array($result)) {
			$r['addtime'] = timetodate($r['addtime'], 5);
			$r['amount'] > 0 ? $income += $r['amount'] : $expense += $r['amount'];
			$records[] = $r;
		}
		include tpl('credits', $module);
	break;
}
?>