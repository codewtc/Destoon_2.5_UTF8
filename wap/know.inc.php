<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2010 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
$CATEGORY = cache_read('category-'.$moduleid.'.php');
$table = $DT_PRE.'know';
$table_data = $DT_PRE.'know_data';
$table_answer = $DT_PRE.'know_answer';
$PROCESS = array('已关闭', '待解决', '投票中', '已解决');
if($itemid) {
	$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid AND status=3");
	$item or wap_msg('信息不存在');
	extract($item);	
	$could_answer = check_group($_groupid, $MOD['group_answer']);
	if($item['process'] != 1 || ($_username && $_username == $item['username'])) $could_answer = false;
	if($could_answer) {
		if($_username) {
			$r = $db->get_one("SELECT itemid FROM {$table_answer} WHERE username='$_username' AND qid=$itemid");
		} else {
			$r = $db->get_one("SELECT itemid FROM {$table_answer} WHERE ip='$DT_IP' AND qid=$itemid AND addtime>$DT_TIME-86400");
		}
	}
	if($action == 'list') {
		$r = $db->get_one("SELECT COUNT(*) AS num FROM {$table_answer} WHERE qid=$itemid AND status=3");
		$pages = wap_pages($r['num'], $page, $pagesize);
		if($item['answer'] != $r['num']) $db->query("UPDATE {$table} SET answer=$r[num] WHERE itemid=$itemid");
		$lists = array();
		$result = $db->query("SELECT * FROM {$table_answer} WHERE qid=$itemid AND status=3 ORDER BY itemid DESC LIMIT $offset,$pagesize");
		while($r = $db->fetch_array($result)) {
			$lists[] = $r;
		}
		$head_title = $title.$DT['seo_delimiter'].'答案列表'.$DT['seo_delimiter'].$MOD['name'].$DT['seo_delimiter'].$head_title;
	} else if($action == 'answer') {
		$could_answer or wap_msg('无权回答此问题');
		if($submit) {
			$content = htmlspecialchars(trim($content));
			if(strtolower($CFG['charset'] != 'utf-8')) $content = convert($content, 'utf-8', $CFG['charset']);
			if(!$content) wap_msg('请填写答案');
			$need_check =  $MOD['check_add'] == 2 ? $MG['check'] : $MOD['check_answer'];
			$status = get_status(3, $need_check);
			$db->query("INSERT INTO {$table_answer} (qid,content,username,addtime,ip,status) VALUES ('$itemid', '$content', '$_username', '$DT_TIME', '$DT_IP', '$status')");			
			if($status == 3) $db->query("UPDATE {$table} SET answer=answer+1");
			if($MOD['credit_answer'] && $_username && $status == 3) {
				$could_credit = true;
				if($MOD['credit_maxanswer'] > 0) {					
					$r = $db->get_one("SELECT SUM(amount) AS total FROM {$DT_PRE}finance_credit WHERE username='$_username' AND addtime>$DT_TIME-86400  AND reason='回答问题'");
					if($r['total'] > $MOD['credit_maxanswer']) $could_credit = false;
				}
				if($could_credit) {
					credit_add($_username, $MOD['credit_answer']);
					credit_record($_username, $MOD['credit_answer'], 'system', '回答问题', 'ID:'.$itemid.'(WAP)');
				}
			}
			if($MOD['answer_message'] && $item['username']) {
				$linkurl = $MOD['linkurl'].$item['linkurl'];
				send_message($item['username'], '您的提问['.dsubstr($item['title'], 20, '...').']收到新的回答', '问:'.$item['title'].'<br/>答:'.nl2br($content).'<br/>详见:<a href="'.$linkurl.'" target="_blank">'.$linkurl.'</a><br/>如果回答没有显示出来，可能需要系统审核后显示');
			}
			wap_msg($status == 3 ? '回答成功' : '回答成功，请等待审核', "?moduleid=$moduleid&itemid=$itemid");
		}
	} else {
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
		$best = $aid ? $db->get_one("SELECT * FROM {$DT_PRE}know_answer WHERE itemid=$aid") : array();

		$editdate = timetodate($addtime, 5);
		if($page == 1) $db->query("UPDATE {$table} SET hits=hits+1 WHERE itemid=$itemid");
		$head_title = $title.$DT['seo_delimiter'].$MOD['name'].$DT['seo_delimiter'].$head_title;
	}
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
	$result = $db->query("SELECT itemid,catid,title,addtime,process FROM {$table} WHERE $condition ORDER BY $order LIMIT $offset,$pagesize");
	while($r = $db->fetch_array($result)) {
		$r['editdate'] = timetodate($r['addtime'], 2);
		$r['catname'] = $CATEGORY[$r['catid']]['catname'];
		$lists[] = $r;
	}
}
include template('know', 'wap');
?>