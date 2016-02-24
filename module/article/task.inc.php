<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
if($html == 'show') {
	$itemid or exit;
	$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid AND status=3");
	$item or exit;
	extract($item);
	$fee = get_fee($item['fee'], $MOD['fee_view']);
	($MOD['show_html'] || $fee) or exit;
	if(check_group($_groupid, $MOD['group_show'])) {
		if($fee) {
			if($MG['fee_mode']) {
				$user_status = 3;
			} else {
				$pay_item = $moduleid.'-'.$itemid;
				if($_userid) {
					if(check_pay($pay_item, $_username)) {
						$user_status = 3;
					} else {
						$user_status = 2;						
						$linkurl = linkurl($MOD['linkurl'].$linkurl, 1);
						$pay_url = linkurl($MODULE[2]['linkurl'], 1).'pay.php?item='.$pay_item.'&fee='.$fee.'&sign='.crypt_sign($_username.$pay_item.$fee.$linkurl.$item['title']).'&title='.rawurlencode($item['title']).'&forward='.urlencode($linkurl);
					}
				} else {
					$user_status = 0;
				}
			}
		} else {
			$user_status = 3;
		}
	} else {
		$user_status = $_userid ? 1 : 0;
	}
	if($_username && $_username == $item['username']) $user_status = 3;
	if($user_status == 3 || $user_status == 2) {
		if($MOD['text_data']) {
			$content = text_read($itemid, $moduleid);
		} else {
			$content = $db->get_one("SELECT content FROM {$table_data} WHERE itemid=$itemid");
			$content = $content['content'];
		}
		if($user_status == 2) $description = get_description($content, $MOD['pre_view']);
		if(strpos($content, '[pagebreak]') !== false) {
			$content = explode('[pagebreak]', $content);
			$total = count($content);
			$pages = article_pages($itemid, $catid, $addtime, $total, $page);
			$content = $content[$page-1];
		}
		if($MOD['keylink']) $content = keylink($content, $moduleid);
	}
	$content = strip_nr(ob_template('content', 'chip'), true);
	echo 'Inner("content", \''.$content.'\');';	

	if($page == 1) $db->query("UPDATE {$table} SET hits=hits+1 WHERE itemid=$itemid");
	echo 'Inner("hits", \''.$item['hits'].'\');';

	if($MOD['show_html'] && $DT_TIME - @filemtime(DT_ROOT.'/'.$MOD['moduledir'].'/'.$item['linkurl']) > $task_item) tohtml('show', $module);

} else if($html == 'list') {
	$catid or exit;
	if($MOD['list_html'] && $DT_TIME - @filemtime(DT_ROOT.'/'.$MOD['moduledir'].'/'.listurl($moduleid, $catid, $page, $CATEGORY, $MOD)) > $task_list) {
		$fid = $page;
		$num = 3;
		tohtml('list', $module);
	}
} else if($html == 'index') {
	if($DT_TIME - @filemtime(CE_ROOT.'/cateitem-'.$moduleid.'.php') > $CFG['tag_expires']) cache_item($moduleid);
	$file = DT_ROOT.'/'.$MOD['moduledir'].'/'.$DT['index'].'.'.$DT['file_ext'];
	if($MOD['index_html']) {
		if($DT_TIME - @filemtime($file) > $task_index) tohtml('index', $module);
	} else {
		if(is_file($file)) @unlink($file);
	}
}
?>