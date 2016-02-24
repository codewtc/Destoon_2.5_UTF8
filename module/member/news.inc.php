<?php 
defined('IN_DESTOON') or exit('Access Denied');
login();
require DT_ROOT.'/module/'.$module.'/common.inc.php';

$MG['homepage'] && $MG['news_limit'] > -1 or dalert('您所在的会员组没有权限使用此功能，请升级', 'goback');

require DT_ROOT.'/include/post.func.php';
$TYPE = get_type('news-'.$_userid);
require MD_ROOT.'/news.class.php';
$do = new news();

switch($action) {
	case 'add':
		if($MG['news_limit']) {
			$r = $db->get_one("SELECT COUNT(*) AS num FROM {$DT_PRE}news WHERE username='$_username' AND status>0");
			if($r['num'] >= $MG['news_limit']) dalert('最多可发布'.$MG['news_limit'].'条 当前已发布'.$r['num'].'条', 'goback');
		}
		if($submit) {
			if($do->pass($post)) {
				$post['username'] = $_username;
				$need_check =  $MOD['news_check'] == 2 ? $MG['check'] : $MOD['news_check'];
				$post['status'] = get_status(3, $need_check);
				$do->add($post);
				dmsg('添加成功', $MOD['linkurl'].'news.php?status='.$post['status']);
			} else {
				message($do->errmsg);
			}
		} else {		
			$addtime = timetodate($DT_TIME);
			$type_select = type_select('news-'.$_userid, 0, 'post[typeid]', '默认');
		}
	break;
	case 'edit':
		$itemid or message();
		$do->itemid = $itemid;
		$r = $do->get_one();
		if(!$r || $r['username'] != $_username) message();
		if($submit) {
			if($do->pass($post)) {
				$post['username'] = $_username;
				$need_check =  $MOD['news_check'] == 2 ? $MG['check'] : $MOD['news_check'];
				$post['status'] = get_status($r['status'], $need_check);
				$do->edit($post);
				dmsg('修改成功', $forward);
			} else {
				message($do->errmsg);
			}
		} else {
			extract($r);
			$addtime = timetodate($addtime);
			$type_select = type_select('news-'.$_userid, $typeid, 'post[typeid]', '默认');
		}
	break;
	case 'delete':
		$itemid or message();
		$do->itemid = $itemid;
		$r = $do->get_one();
		if(!$r || $r['username'] != $_username) message();
		$do->recycle($itemid);
		dmsg('删除成功', $forward);
	break;
	default:
		$status = isset($status) ? intval($status) : 3;
		in_array($status, array(1, 2, 3)) or $status = 3;
		$typeid = isset($typeid) ? ($typeid === '' ? -1 : intval($typeid)) : -1;
		$type_select = type_select('news-'.$_userid, 0, 'typeid', '默认', $typeid, '', '所有分类');
		$condition = "username='$_username'";
		$condition .= " AND status=$status";
		if($keyword) $condition .= " AND title LIKE '%$keyword%'";
		if($typeid > -1) $condition .= " AND typeid=$typeid";
		$lists = $do->get_list($condition);
		foreach($lists as $k=>$v) {
			$lists[$k]['type'] = $lists[$k]['typeid'] && isset($TYPE[$lists[$k]['typeid']]) ? set_style($TYPE[$lists[$k]['typeid']]['typename'], $TYPE[$lists[$k]['typeid']]['style']) : '默认';
		}
		$head_title = '公司新闻';
	break;
}
$nums = array();
$limit_used = 0;
for($i = 1; $i < 4; $i++) {
	$r = $db->get_one("SELECT COUNT(*) AS num FROM {$DT_PRE}news WHERE username='$_username' AND status=$i");
	$nums[$i] = $r['num'];
	$limit_used += $r['num'];
}
$limit_free = $MG['news_limit'] && $MG['news_limit'] > $limit_used ? $MG['news_limit'] - $limit_used : 0;
include template('news', $module);
?>