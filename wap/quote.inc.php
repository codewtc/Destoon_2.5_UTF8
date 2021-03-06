<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2010 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
$CATEGORY = cache_read('category-'.$moduleid.'.php');
$table = $DT_PRE.'quote';
$table_data = $DT_PRE.'quote_data';
if($itemid) {
	$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid AND status=3");
	$item or wap_msg('信息不存在');
	extract($item);

	$CAT = get_cat($catid);
	if(!check_group($_groupid, $MOD['group_show']) || !check_group($_groupid, $CAT['group_show'])) wap_msg('无权浏览的页面');
	$description = '';
	$user_status = 3;
	$fee = get_fee($item['fee'], $MOD['fee_view']);
	require $action == 'pay' ? 'pay.inc.php' : 'content.inc.php';

	if($MOD['text_data']) {
		$content = text_read($itemid, $moduleid);
	} else {
		$content = $db->get_one("SELECT content FROM {$table_data} WHERE itemid=$itemid");
		$content = $content['content'];
	}
	$content = str_replace('[pagebreak]', '', $content);
	$content = strip_tags($content);
	$content = preg_replace("/\&([^;]+);/i", '', $content);
	if($user_status == 2) $description = get_description($content, $MOD['pre_view']);
	$contentlength = strlen($content);
	if($contentlength > $maxlength) {
		$start = ($page-1)*$maxlength;
		$content = dsubstr($content, $maxlength, '', $start);
		$pages = wap_pages($contentlength, $page, $maxlength);
	}
	$content = nl2br($content);
	$editdate = timetodate($addtime, 5);
	if($page == 1) $db->query("UPDATE {$table} SET hits=hits+1 WHERE itemid=$itemid");
	$head_title = $title.$DT['seo_delimiter'].$MOD['name'].$DT['seo_delimiter'].$head_title;
} else {
	if($kw) {
		check_group($_groupid, $MOD['group_search']) or wap_msg('无权搜索');
	} else if($catid) {
		isset($CATEGORY[$catid]) or wap_msg('分类不存在');
		$CAT = get_cat($catid);
		if(!check_group($_groupid, $MOD['group_list']) || !check_group($_groupid, $CAT['group_list'])) {
			wap_msg('无权浏览的页面');
		}
	} else {
		check_group($_groupid, $MOD['group_index']) or wap_msg('无权浏览的页面');
	}

	$head_title = $MOD['name'].$DT['seo_delimiter'].$head_title;
	if($kw) $head_title = $kw.$DT['seo_delimiter'].$head_title;
	$keyword = $kw ? str_replace(array(' ', '*'), array('%', '%'), $kw) : '';
	$condition = "status=3";
	if($keyword) $condition .= " AND keyword LIKE '%$keyword%'";
	if($catid) $condition .= ($CATEGORY[$catid]['child']) ? " AND catid IN (".$CATEGORY[$catid]['arrchildid'].")" : " AND catid=$catid";
	$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table} WHERE $condition");
	$pages = wap_pages($r['num'], $page, $pagesize);
	$lists = array();
	$order = $MOD['order'];
	$result = $db->query("SELECT itemid,catid,title,addtime FROM {$table} WHERE $condition ORDER BY $order LIMIT $offset,$pagesize");
	while($r = $db->fetch_array($result)) {
		$r['editdate'] = timetodate($r['addtime'], 2);
		$r['catname'] = $CATEGORY[$r['catid']]['catname'];
		$lists[] = $r;
	}
}
include template('quote', 'wap');
?>