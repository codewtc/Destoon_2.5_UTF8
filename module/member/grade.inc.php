<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
if($action == crypt_action('promo')) {
	$code = htmlspecialchars(trim($code));
	if($code) {
		$p = $db->get_one("SELECT * FROM {$DT_PRE}finance_promo WHERE number='$code' AND totime>$DT_TIME AND username=''");
		if($p) {
			if($p['type']) {
				echo '可获有效期:'.$p['amount'].'天';
			} else {
				echo '可充抵金额:'.$p['amount'].'元';
			}
		} else {
			echo '无效的优惠码';
		}
	}
	exit;
}
require DT_ROOT.'/include/post.func.php';
$GROUP = cache_read('group.php');
$groupid = isset($groupid) ? intval($groupid) : 0;
isset($GROUP[$groupid ]) or $groupid = 0;
$UP = $UG = array();
if($_groupid > 2) {
	foreach($GROUP as $k=>$v) {
		if($v['listorder'] > $MG['listorder']) $UP[$k] = $v;
	}
}
array_key_exists($groupid, $UP) or $groupid = 0;
$fee = 0;
$need_fee = false;
$could_up = $groupid;
if($groupid) {
	$UG = cache_read('group-'.$groupid.'.php');
	$fee = $UG['fee'];
	if($_userid && $fee) $need_fee = true;
}
if($_userid) {
	$r = $db->get_one("SELECT status FROM {$DT_PRE}upgrade WHERE userid=$_userid ORDER BY itemid DESC");
	if($r && $r['status'] == 2) $could_up = false;
} else {
	$r = $db->get_one("SELECT addtime FROM {$DT_PRE}upgrade WHERE ip='$DT_IP' ORDER BY itemid DESC");
	if($r && $DT_TIME - $r['addtime'] < 86400) $could_up = false;
}
if($submit && $could_up) {
	if(strlen($company) < 4) message('请填写公司名');
	if(strlen($truename) < 2) message('请填写联系人');
	if(strlen($telephone) < 6) message('请填写电话号码');
	$amount = $promo_type = $promo_amount = 0;
	if($fee) {
		if($need_fee && !is_payword($_username, $password)) message('支付密码不正确');
		if($promo_code) {
			$p = $db->get_one("SELECT * FROM {$DT_PRE}finance_promo WHERE number='$promo_code' AND totime>$DT_TIME AND username=''");
			if($p) {
				$promo_type = $p['type'];
				$promo_amount = $p['amount'];
			} else {
				$promo_code = '';
			}
		}
		if($promo_code) {
			if($promo_type) {//赠送有效期
				//
			} else {//赠送金额
				if($fee > $promo_amount) {
					$amount = $fee - $promo_amount;
					if($_money > $amount) {
						money_add($_username, -$amount);
						money_record($_username, -$amount, '站内', 'system', '会员升级', $GROUP[$groupid]['groupname']);
					} else {
						$amount = 0;
					}
				} else {
					$amount = $fee;
				}
			}
			$db->query("UPDATE {$DT_PRE}finance_promo SET username='$_username',ip='$DT_IP',updatetime='$DT_TIME' WHERE number='$promo_code'");
		} else {
			if($_money > $fee) {
				$amount = $fee;
				money_add($_username, -$amount);
				money_record($_username, -$amount, '站内', 'system', '会员升级', $GROUP[$groupid]['groupname']);
			}
		}
	}
	$company = htmlspecialchars(trim($company));
	$truename = htmlspecialchars(trim($truename));
	$telephone = htmlspecialchars(trim($telephone));
	$mobile = htmlspecialchars(trim($mobile));
	$email = htmlspecialchars(trim($email));
	$msn = htmlspecialchars(trim($msn));
	$qq = htmlspecialchars(trim($qq));
	$content = htmlspecialchars(trim($content));
	$db->query("INSERT INTO {$DT_PRE}upgrade (userid,username,groupid,company,truename,telephone,mobile,email,msn,qq,content,addtime,ip,amount,promo_code,promo_type,promo_amount,status) VALUES ('$_userid','$_username', '$groupid','$company','$truename','$telephone','$mobile','$email','$msn','$qq','$content', '$DT_TIME', '$DT_IP','$amount','$promo_code','$promo_type','$promo_amount','2')");
	 message('您的申请已经成功提交，请等待工作人员处理', DT_PATH, 5);
} else {
	$GROUPS = array();
	foreach($GROUP as $k=>$v) {
		if($k > 4) {
			$G = cache_read('group-'.$k.'.php');
			$G['moduleids'] = isset($G['moduleids']) ? explode(',', $G['moduleids']) : array();
			$GROUPS[$k] = $G;
		}
	}
	
	$cols = count($GROUPS)+1;
	$percent = dround(100/$cols).'%';
	$company = $truename = $email = $mobile = $telephone = $msn = $qq = '';
	if($_userid) {
		$user = userinfo($_username);
		$company = $user['company'];
		$truename = $user['truename'];
		$email = $user['email'];
		$mobile = $user['mobile'];
		$telephone = $user['telephone'];
		$msn = $user['msn'];
		$qq = $user['qq'];
	}
	$DM = $MODULE;
	$DM[9]['name'] = '招聘';
	$DM[-9]['moduleid'] = -9;
	$DM[-9]['name'] = '简历';
	$DM[-9]['linkurl'] = $DM[9]['linkurl'];
	$head_title = '会员升级/服务范围';
	include template('grade', $module);
}
?>