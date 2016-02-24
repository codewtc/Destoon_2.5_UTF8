<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
require DT_ROOT.'/include/post.func.php';
$itemid = intval($itemid);
$itemid or dheader($MOD['linkurl']);
$item = $db->get_one("SELECT * FROM {$table} WHERE itemid=$itemid AND status>2");
if($action == 'best') {
	if(!$item) exit('0');
	$op = $op ? 1 : 0;
	$f = $op ? 'agree' : 'against';
	if(get_cookie('best_answer_'.$itemid)) exit('-1');
	$db->query("UPDATE {$table} SET `{$f}`=`{$f}`+1 WHERE itemid=$itemid");
	set_cookie('best_answer_'.$itemid, 1, $DT_TIME + 365*86400);
	exit('1');
}
$item or dalert('信息不存在或正在审核');
$linkurl = linkurl($MOD['linkurl'].$item['linkurl'], 1);
$table_answer = $DT_PRE.'know_answer';
$table_vote = $DT_PRE.'know_vote';

$aid = isset($aid) ? intval($aid) : 0;
$aser = $aid ? $db->get_one("SELECT * FROM {$table_answer} WHERE itemid=$aid AND status=3") : array();
if($aser && $aser['qid'] != $itemid) dalert('答案不存在或正在审核');
$could_admin = $could_addition = $could_close = $_username && $_username == $item['username'];
if($item['process'] > 1) $could_addition = $could_close = false;
switch($action) {
	case 'addition':
		if($could_addition) {
			$content = htmlspecialchars($content);
			$db->query("UPDATE {$table} SET addition='$content' WHERE itemid=$itemid");
			if($MOD['show_html']) tohtml('show', $module);
		}
		dalert('', $linkurl);
	break;
	case 'vote':
		$could_vote = $could_admin;
		if($item['process'] != 1) $could_vote = false;
		if($could_vote) {
			$items = $db->counter($table_answer, "qid=$itemid AND status=3", '');
			if($items < 2) $could_vote = false;
		}
		if($could_vote) {
			$totime = $DT_TIME + $MOD['votedays']*86400;
			$db->query("UPDATE {$table} SET process=2,totime=$totime WHERE itemid=$itemid");
			if($MOD['show_html']) tohtml('show', $module);
		}
		dalert('', $linkurl);
	break;
	case 'vote_del':
		if($item['process'] != 2) dalert('投票已经结束');
		$items = $db->counter($table_answer, "qid=$itemid AND status=3", '');
		if($items < 3) dalert('至少需要保留两个答案');
		if($aser['qid'] == $itemid) $db->query("DELETE FROM {$table_answer} WHERE itemid=$aid");
		dalert('', '', 'parent.window.location=parent.window.location;');
	break;
	case 'vote_add':
		$could_vote = check_group($_groupid, $MOD['group_vote']);
		if(get_cookie('answer_vote_'.$itemid)) $could_vote = false;
		if($could_vote) {
			if($_userid) {
				$v = $db->get_one("SELECT itemid FROM {$table_vote} WHERE qid=$itemid AND username='$_username'");
			} else {
				$v = $db->get_one("SELECT itemid FROM {$table_vote} WHERE qid=$itemid AND ip='$DT_IP' AND addtime>$DT_TIME-86400");
			}
		}
		if($v) $could_vote = false;
		set_cookie('answer_vote_'.$itemid, 1, $DT_TIME + 365*86400);
		if($could_vote) {
			$db->query("INSERT INTO {$table_vote} (qid,aid,username,addtime,ip) VALUES ('$itemid','$aid','$_username','$DT_TIME','$DT_IP')");
			$db->query("UPDATE {$table_answer} SET vote=vote+1 WHERE itemid=$aid");
			if($MOD['credit_vote'] && $_username) {
				$could_credit = true;
				if($MOD['credit_maxvote'] > 0) {					
					$r = $db->get_one("SELECT SUM(amount) AS total FROM {$DT_PRE}finance_credit WHERE username='$_username' AND addtime>$DT_TIME-86400  AND reason='问题投票'");
					if($r['total'] > $MOD['credit_maxvote']) $could_credit = false;
				}
				if($could_credit) {
					credit_add($_username, $MOD['credit_vote']);
					credit_record($_username, $MOD['credit_vote'], 'system', '问题投票', 'ID:'.$itemid);
				}
			}
			dalert('', '', 'parent.window.location=parent.window.location;');
		} else {
			dalert('您已经投过票或无权投票', '', 'parent.window.location=parent.window.location;');
		}
	break;
	case 'vote_show':
		if($item['process'] != 2) dalert('投票已经结束', 'goback');
		$votes = array();
		$result = $db->query("SELECT * FROM {$table_answer} WHERE qid=$itemid AND status=3 ORDER BY itemid ASC");
		$total = 0;
		while($r = $db->fetch_array($result)) {
			$total += $r['vote'];
			$votes[] = $r;
		}
		foreach($votes as $k=>$v) {
			$votes[$k]['precent'] = $total ? dround($v['vote']*100/$total, 2, true).'%' : '1%';
		}
	break;
	case 'close':
		if($could_close) {
			$db->query("UPDATE {$table} SET process=0 WHERE itemid=$itemid");
			if($MOD['show_html']) tohtml('show', $module);
		}
		dalert('', $linkurl);
	break;
	case 'choose':
		$could_choose = $could_admin;
		if($item['process'] != 1) $could_choose = false;
		$aid = intval($aid);
		if(!$aid) $could_choose = false;
		if($could_choose) {
			$a = $db->get_one("SELECT * FROM {$table_answer} WHERE itemid=$aid AND qid=$itemid");
			if($a) {
				$content = htmlspecialchars($content);
				$db->query("UPDATE {$table} SET process=3,aid=$aid,comment='$content',updatetime='$DT_TIME' WHERE itemid=$itemid");
				//奖励悬赏
				if($a['username']) {
					if($item['credit']) {
						credit_add($a['username'], $item['credit']);
						credit_record($a['username'], $item['credit'], 'system', '['.$MODULE[$moduleid]['name'].']最佳答案悬赏', 'ID:'.$itemid);
					}
					if($MOD['credit_best']) {
						credit_add($a['username'], $MOD['credit_best']);
						credit_record($a['username'], $MOD['credit_best'], 'system', '['.$MODULE[$moduleid]['name'].']最佳答案奖励', 'ID:'.$itemid);
					}
					if($credit > 1 && $credit <= $_credit) {
						credit_add($a['username'], $credit);
						credit_record($a['username'], $credit, 'system', '['.$MODULE[$moduleid]['name'].']最佳答案感谢', 'ID:'.$itemid);
					}
				}
				if($MOD['show_html']) tohtml('show', $module);
			}
		}
		dalert('', $linkurl);
	break;
	case 'raise':
		if($credit < 1) dalert('请选择积分', 'goback');
		if($credit > $_credit) dalert('积分不足', 'goback');
		$could_raise = $could_admin;
		if($item['process'] != 1) $could_raise = false;
		if($item['raise'] >= $MOD['maxraise'])  $could_raise = false;
		if($could_raise) {
			if($credit >= $MOD['raisecredit']) {
				$addtime = $DT_TIME;
				$totime = $DT_TIME + $MOD['overdays']*86400 + $MOD['raisedays']*86400;
			} else {
				$addtime = $item['addtime'];
				$totime = $item['totime'] + $MOD['raisedays']*86400;
			}
			$db->query("UPDATE {$table} SET credit=credit+$credit,raise=raise+1,addtime=$addtime,totime=$totime WHERE itemid=$itemid");
			//扣除悬赏
			credit_add($_username, -$credit);
			credit_record($_username, -$credit, 'system', '['.$MODULE[$moduleid]['name'].']追加悬赏', 'ID:'.$itemid);
			if($MOD['show_html']) tohtml('show', $module);
		}
		dalert('', $linkurl);
	break;
	default:
		$could_answer = check_group($_groupid, $MOD['group_answer']);
		if($item['process'] != 1 || $could_admin) $could_answer = false;
		if($MOD['answer_pagesize']) {
			$pagesize = $MOD['answer_pagesize'];
			$offset = ($page-1)*$pagesize;
		}
		$need_captcha = $MOD['captcha_answer'] == 2 ? $MG['captcha'] : $MOD['captcha_answer'];
		$need_question = $MOD['question_answer'] == 2 ? $MG['question'] : $MOD['question_answer'];

		if($could_answer) {
			if($_username) {
				$r = $db->get_one("SELECT itemid FROM {$table_answer} WHERE username='$_username' AND qid=$itemid");
			} else {
				$r = $db->get_one("SELECT itemid FROM {$table_answer} WHERE ip='$DT_IP' AND qid=$itemid AND addtime>$DT_TIME-86400");
			}
			if($r) $could_answer = false;
		}

		if($submit && $could_answer) {
			if($error) dalert($error);
			$msg = captcha($captcha, $need_captcha, true);
			if($msg) dalert($msg);
			$msg = question($answer, $need_question, true);
			if($msg) dalert($msg);
			$content = htmlspecialchars(trim($content));
			if(!$content) dalert('请填写答案');
			$url = htmlspecialchars(trim($url));	
			$need_check =  $MOD['check_add'] == 2 ? $MG['check'] : $MOD['check_answer'];
			$status = get_status(3, $need_check);
			$hidden = isset($hidden) ? 1 : 0;
			$db->query("INSERT INTO {$table_answer} (qid,linkurl,content,username,addtime,ip,status,hidden) VALUES ('$itemid', '$url', '$content', '$_username', '$DT_TIME', '$DT_IP', '$status','$hidden')");
			if($status == 3) $db->query("UPDATE {$table} SET answer=answer+1");
			if($MOD['credit_answer'] && $_username && $status == 3) {
				$could_credit = true;
				if($MOD['credit_maxanswer'] > 0) {					
					$r = $db->get_one("SELECT SUM(amount) AS total FROM {$DT_PRE}finance_credit WHERE username='$_username' AND addtime>$DT_TIME-86400  AND reason='回答问题'");
					if($r['total'] > $MOD['credit_maxanswer']) $could_credit = false;
				}
				if($could_credit) {
					credit_add($_username, $MOD['credit_answer']);
					credit_record($_username, $MOD['credit_answer'], 'system', '回答问题', 'ID:'.$itemid);
				}
			}
			if($MOD['answer_message'] && $item['username']) {
				send_message($item['username'], '您的提问['.dsubstr($item['title'], 20, '...').']收到新的回答', '问:'.$item['title'].'<br/>答:'.nl2br($content).'<br/>详见:<a href="'.$linkurl.'" target="_blank">'.$linkurl.'</a><br/>如果回答没有显示出来，可能需要系统审核后显示');
			}
			if($status == 3) {
				$items = isset($items) ? intval($items)+1 : 1;
				$page = ceil($items/$pagesize);
				$forward = $MOD['linkurl'].'answer.php?itemid='.$itemid.'&page='.$page.'&rand='.mt_rand(10, 99).'#last';
				dalert('', '', 'parent.window.location="'.$forward.'";');
			} else {
				dalert('回答成功，请等待审核', '', 'parent.window.location=parent.window.location;');
			}
		} else {
			$could_vote = check_group($_groupid, $MOD['group_vote']);
			if(get_cookie('answer_vote_'.$itemid)) $could_vote = false;
			$pages = '';
			$answers = array();
			$items = $db->counter("{$table_answer}", "qid=$itemid AND status=3 AND itemid!=$item[aid]", '');
			$a = $items;
			if($item['aid']) $a += 1;
			if($item['answer'] != $a) {
				$item['answer'] = $a;
				$db->query("UPDATE {$table} SET answer=$a WHERE itemid=$itemid");
			}
			if($item['process'] == 1 && $item['username'] && !$item['message'] && $MOD['messagedays']) {
				if($item['totime'] - $DT_TIME < $MOD['messagedays']*86400) {
					send_message($item['username'], '您的提问['.dsubstr($item['title'], 20, '...').']即将到期，请及时处理', '详见:<a href="'.$linkurl.'" target="_blank">'.$linkurl.'</a>');
					$db->query("UPDATE {$table} SET message=1 WHERE itemid=$itemid");
				}
			}
			if($DT_TIME > $item['totime']) {
				$reload = false;
				if($item['process'] == 1) {
					if($item['username'] && $MOD['credit_deal'] > 0) {
						credit_add($item['username'], -$MOD['credit_deal']);
						credit_record($item['username'], -$MOD['credit_deal'], 'system', '['.$MODULE[$moduleid]['name'].']问题过期', 'ID:'.$itemid);
					}
					if($item['answer'] > 1) {
						//转入投票
						$totime = $DT_TIME + $MOD['votedays']*86400;
						$db->query("UPDATE {$table} SET process=2,totime=$totime,updatetime='$DT_TIME' WHERE itemid=$itemid");
					} else {
						//关闭问题
						$db->query("UPDATE {$table} SET process=0,updatetime='$DT_TIME' WHERE itemid=$itemid");
					}
					$reload = true;
				} else if($item['process'] == 2) {
					$a = $db->get_one("SELECT * FROM {$table_answer} WHERE qid=$itemid ORDER BY vote DESC");
					if($a && $a['vote'] > $MOD['minvote']) {
						$aid = $a['aid'];//最佳答案
						$db->query("UPDATE {$table} SET process=3,aid=$aid,updatetime='$DT_TIME' WHERE itemid=$itemid");
						//奖励悬赏
						if($a['username']) {
							if($item['credit']) {
								credit_add($a['username'], $item['credit']);
								credit_record($a['username'], $item['credit'], 'system', '['.$MODULE[$moduleid]['name'].']最佳答案悬赏', 'ID:'.$itemid);
							}
							if($MOD['credit_best']) {
								credit_add($a['username'], $MOD['credit_best']);
								credit_record($a['username'], $MOD['credit_best'], 'system', '['.$MODULE[$moduleid]['name'].']最佳答案奖励', 'ID:'.$itemid);
							}
						}
					} else {
						//关闭问题
						$db->query("UPDATE {$table} SET process=0,updatetime='$DT_TIME' WHERE itemid=$itemid");
					}
					$reload = true;
				}
				if($reload) {
					if($MOD['show_html']) tohtml('show', $module);
					dalert('', '', 'top.window.location.reload();');
				}
			}
			$pages = pages($items, $page, $pagesize);
			$result = $db->query("SELECT * FROM {$table_answer} WHERE qid=$itemid AND status=3 AND  itemid!=$item[aid] ORDER BY itemid ASC LIMIT $offset,$pagesize");
			while($r = $db->fetch_array($result)) {
				$answers[] = $r;
			}
			$head_title = '问题回答'.$DT['seo_delimiter'].$item['title'].$DT['seo_delimiter'].$MOD['name'];
		}
	break;
}
include template('answer', $module);
?>