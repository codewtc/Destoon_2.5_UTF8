<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2010 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
define('DT_NONUSER', true);
require 'common.inc.php';
require DT_ROOT.'/include/post.func.php';
switch($action) {
	case 'area':
		if(strtolower($CFG['charset']) != 'utf-8') $area_title = convert($area_title, 'utf-8', $CFG['charset']);
		$area_extend = isset($area_extend) ? stripslashes($area_extend) : '';
		$areaid = isset($areaid) ? intval($areaid) : 0;
		$area_deep = isset($area_deep) ? intval($area_deep) : 0;
		$area_id= isset($area_id) ? intval($area_id) : 1;
		echo get_area_select($area_title, $areaid, $area_extend, $area_deep, $area_id);
	break;
	case 'category':
		if(strtolower($CFG['charset']) != 'utf-8') $category_title = convert($category_title, 'utf-8', $CFG['charset']);
		$category_extend = isset($category_extend) ? stripslashes($category_extend) : '';
		$category_moduleid = isset($category_moduleid) ? intval($category_moduleid) : 1;
		if(!$category_moduleid) exit;
		$category_deep = isset($category_deep) ? intval($category_deep) : 0;
		$cat_id= isset($cat_id) ? intval($cat_id) : 1;
		if($_groupid == 1) {
			$R = cache_read('right-'.$_userid.'.php');
			if(isset($R[$category_moduleid]['index']['catid'])) {
				$_catids = $R[$category_moduleid]['index']['catid'];
				if($_catids) {
					$CATEGORY = cache_read('category-'.$category_moduleid.'.php');
					foreach($CATEGORY as $k=>$c) {
						if($c['parentid'] > 0) continue;
						if(!in_array($k, $_catids)) unset($CATEGORY[$k]);
					}
				}
			}
		}
		echo get_category_select($category_title, $catid, $category_moduleid, $category_extend, $category_deep, $cat_id);
	break;
	case 'product':
		require DT_ROOT.'/module/sell/product.func.php';
		if(strtolower($CFG['charset']) != 'utf-8') $product_title = convert($product_title, 'utf-8', $CFG['charset']);
		$product_extend = isset($product_extend) ? stripslashes($product_extend) : '';
		echo get_product_select($product_title, $product_catid, $product_pid, $product_extend);
	break;
	case 'product_option':
		require DT_ROOT.'/module/sell/product.func.php';
		$pid = isset($pid) ? intval($pid) : 0;
		$options = cache_read('option-'.$pid.'.php');
		$options or exit;
		$values = array();
		if($product_itemid) {
			$result = $db->query("SELECT * FROM {$DT_PRE}sell_value WHERE itemid=$product_itemid");
			while($r = $db->fetch_array($result)) {
				$values[$r['oid']] = $r['value'];
			}
		}
		$select = '<select id="product_require" style="display:none;">';
		$table = '<table cellpadding="2" cellspacing="2" class="product_option_t">';
		foreach($options as $k=>$v) {
			isset($values[$v['oid']]) or $values[$v['oid']] = '';
			$table .=  $v['type'] ? '<tr><td class="product_option_l">'.$v['name'].' '.($v['required'] ? '<span class="f_red">*</span>' : '').'</td><td class="product_option_r">'.product_html($values[$v['oid']], $v['oid'], $v['type'], $v['value'], $v['extend']).' <span class="f_red" id="dproduct-'.$v['oid'].'"></span></td></tr>' : '<tr><td class="product_option_h" colspan="2">'.$v['name'].'</td></tr>';
			$select .= $v['required'] ? '<option value="'.$v['oid'].'">'.$v['name'].'</option>' : '';
		}
		$table .= '</table>';
		$select .= '</select>';
		echo $table.$select;
	break;
	case 'clear':
		@ignore_user_abort(true);
		$session = new dsession();
		if($_SESSION['uploads']) {
			foreach($_SESSION['uploads'] as $file) {
				delete_upload($file, $_userid);
			}
			$_SESSION['uploads'] = array();
		}
	break;
	case 'ipage':
		isset($job) or exit;
		if($job == 'sell') {
			$moduleid = 5;
		} else if($job == 'buy') {
			$moduleid = 6;
		} else {
			exit;
		}	tag("moduleid=$moduleid&length=40&condition=status=3&pagesize=".$DT['page_trade']."&page=$page&datetype=2&order=addtime desc&template=list-trade");
	break;
	case 'keyword':
		$mid = isset($mid) ? intval($mid) : 0;
		$mid or exit;
		isset($MODULE[$mid]) or exit;
		tag("moduleid=$mid&table=keyword&condition=moduleid=$mid and status=3&pagesize=10&order=total_search desc&template=list-search_kw", -2);
	break;
	case 'tipword':
		if(!$DT['search_tips']) exit;
		$mid = isset($mid) ? intval($mid) : 0;
		$mid or exit;
		isset($MODULE[$mid]) or exit;
		if(!$word || strlen($word) < 2 || strlen($word) > 30) exit;
		if(strtolower($CFG['charset']) != 'utf-8') $word = convert($word, 'utf-8', $CFG['charset']);	
		$word = str_replace(array(' ','*', "\'"), array('%', '%', ''), $word);
		if(preg_match("/^[a-z0-9A-Z]+$/", $word)) {			
			tag("moduleid=$mid&table=keyword&condition=moduleid=$mid and letter like '%$word%'&pagesize=10&order=total_search desc&template=list-search_tip", -2);
		} else {
			tag("moduleid=$mid&table=keyword&condition=moduleid=$mid and keyword like '%$word%'&pagesize=10&order=total_search desc&template=list-search_tip", -2);
		}
	break;
	case 'letter':
		preg_match("/[a-z]{1}/", $letter) or exit;
		$cols = isset($cols) ? intval($cols) : 5;
		$precent = ceil(100/$cols);
		$CATEGORY = cache_read('category-'.$moduleid.'.php');
		$CATALOG = array();
		foreach($CATEGORY as $k=>$v) {
			if($v['letter'] == $letter) $CATALOG[] = $v;
		}
		include template('letter', 'chip');
	break;
	case 'catalog':
		$mid = isset($mid) ? intval($mid) : 0;
		$mid or exit;
		isset($MODULE[$mid]) or exit;
		include template('catalog', 'chip');
	break;
	case 'schcate':
		isset($name) or $name == '';
		if(strtolower($CFG['charset']) != 'utf-8' && $name) $name = convert($name);
		$CATEGORY = cache_read('category-'.$moduleid.'.php');
		$limit = $DT['schcate_limit'] ? intval($DT['schcate_limit']) : 10;
		$html = '';
		$result = $db->query("SELECT catid FROM {$DT_PRE}category WHERE moduleid=$moduleid AND catname LIKE '%$name%' ORDER BY item DESC,catid DESC LIMIT $limit");
		while($r = $db->fetch_array($result)) {
			$html .= '<input type="radio" name="dtcate" value="'.$r['catid'].'" onclick="load_category('.$r['catid'].', 1);" id="dtcate_'.$r['catid'].'"/> <label for="dtcate_'.$r['catid'].'">'.strip_tags(cat_pos($r['catid'])).'</label><br/>';
		}
		echo $html;
	break;
	case 'reccate':
		isset($name) or $name == '';
		if(strtolower($CFG['charset']) != 'utf-8' && $name) $name = convert($name);
		$CATEGORY = cache_read('category-'.$moduleid.'.php');
		$limit = $DT['schcate_limit'] ? intval($DT['schcate_limit']) : 10;
		$table = get_table($moduleid);
		$key = in_array($moduleid, array(5, 6)) ? 'tag' : 'keyword';
		$html = '';
		$result = $db->query("SELECT distinct catid FROM {$table} WHERE `{$key}` LIKE '%$name%' ORDER BY addtime DESC LIMIT $limit");
		while($r = $db->fetch_array($result)) {
			$html .= '<input type="radio" name="dtcate" value="'.$r['catid'].'" onclick="load_category('.$r['catid'].', 1);" id="dtcate_'.$r['catid'].'"/> <label for="dtcate_'.$r['catid'].'">'.strip_tags(cat_pos($r['catid'])).'</label><br/>';
		}
		echo $html;
	break;
	case 'ckpath':
		if($_groupid != 1) exit('权限不足');
		if($moduleid < 5) exit('模型错误');
		if(strlen($path) < 5) exit('路径错误');
		if(strtolower($CFG['charset']) != 'utf-8') $path = convert($path);
		$table = get_table($moduleid);
		if($table) {
			$sql = "filepath='$path'";
			if($itemid) $sql .= " AND itemid!=$itemid";
			if($db->get_one("SELECT itemid FROM {$table} WHERE $sql")) {
				exit('文件路径重复，请更换');
			} else {
				exit('文件路径没有重复');
			}
		}
	break;
	case 'member':
		isset($job) or $job = '';
		require DT_ROOT.'/module/'.$module.'/common.inc.php';
		isset($value) or $value == '';
		if(strtolower($CFG['charset']) != 'utf-8' && $value) $value = convert($value);
		require MD_ROOT.'/member.class.php';
		$do = new member;
		if(isset($userid) && $userid) $do->userid = $userid;
		switch($job) {
			case 'username':
				if(!$value) exit('会员名不能为空');
				if(!$do->is_username($value)) echo $do->errmsg;
			break;
			case 'passport':
				if(!$value) exit();
				if(!$do->is_passport($value)) echo $do->errmsg;
			break;
			case 'password':
				if(!$do->is_password($value, $value)) echo $do->errmsg;
			break;
			case 'payword':
				if(!$do->is_payword($value, $value)) echo $do->errmsg;
			break;
			case 'email':
				if(!is_email($value)) exit('邮件格式不正确');
				if($do->email_exists($value)) echo '邮件地址已经存在';
			break;
			case 'company':
				if(!$value) exit('公司名称不能为空');
				if($do->company_exists($value)) echo '公司名称已经存在';
			break;
			case 'get_company':
				$user = $do->get_one($value);
				if($user) {
					echo '<a href="'.$user['linkurl'].'" target="_blank" class="t">'.$user['company'].'</a>'.( $user['vip'] ? ' <img src="'.SKIN_PATH.'image/vip.gif" align="absmiddle"/> <img src="'.SKIN_PATH.'image/vip_'.$user['vip'].'.gif" align="absmiddle"/>' : '');
				} else {
					echo '1';
				}
			break;
		}
	break;
	case 'get_data':
		if(!$_userid) exit;
		$mid = isset($mid) ? intval($mid) : 0;
		$mid or exit;
		isset($MODULE[$mid]) or exit;
		$file = DT_ROOT.'/file/user/'.dalloc($_userid).'/'.$_userid.'/editor.data.'.$mid.'.php';
		if(!is_file($file)) exit('');
		$content = file_get($file);
		if($content) {
			echo substr($content, 13);
		} else {
			echo '';
		}
	break;
	case 'save_data':
		if(!$_userid) exit;
		$mid = isset($mid) ? intval($mid) : 0;
		$mid or exit;
		isset($MODULE[$mid]) or exit;
		if(!$content) exit;
		$content = stripslashes($content);
		if(strtolower($CFG['charset']) != 'utf-8') $content = convert($content, 'utf-8', $CFG['charset']);
		$content = '<?php exit;?>'.timetodate($DT_TIME).$content;
		file_put(DT_ROOT.'/file/user/'.dalloc($_userid).'/'.$_userid.'/editor.data.'.$mid.'.php', $content);
	break;
	case crypt_action('captcha'):
		if(strlen($captcha) < 4) exit('1');
		$session = new dsession();
		if(!isset($_SESSION['captchastr'])) exit('2');
		if($_SESSION['captchastr'] != md5(md5(strtoupper($captcha).$CFG['authkey'].$DT_IP))) exit('3');
		exit('0');
	break;
	case crypt_action('question'):
		if(strlen($answer) < 1) exit('1');
		$answer = stripslashes($answer);
		if(strtolower($CFG['charset']) != 'utf-8') $answer = convert($answer, 'utf-8', $CFG['charset']);
		$session = new dsession();
		if(!isset($_SESSION['answerstr'])) exit('2');
		if($_SESSION['answerstr'] != md5(md5($answer.$CFG['authkey'].$DT_IP))) exit('3');
		exit('0');
	break;
}
?>