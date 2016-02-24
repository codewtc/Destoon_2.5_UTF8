<?php 
defined('IN_DESTOON') or exit('Access Denied');
$COM = $db->get_one("SELECT * FROM {$table} c, {$table_member} m WHERE c.userid=m.userid AND c.username='$username' AND m.groupid>4");
if(!$COM) {//公司不存在
	$head_title = '公司不存在';
	include template('com-notfound', 'message');
	exit;
}
if(!$COM['edittime']) {//资料不完整
	$seo_title = $COM['company'];
	include template('com-opening', 'message');
	exit;
}

$domain = $COM['domain'];
if($domain) {
	//跳转到顶级域名
	if(strpos($DT_URL, $domain) === false) {
		$subdomain = userurl($username);
		if(strpos($DT_URL, $subdomain) === false) {
			dheader('http://'.$domain.'/');
		} else {
			dheader(str_replace($subdomain, 'http://'.$domain.'/', $DT_URL));
		}
	}
	$DT['rewrite'] = intval($CFG['com_rewrite']);//顶级域名可能无法Rewrite
}

$linkurl = userurl($username, '', $domain);
if($COM['linkurl'] != $linkurl) $COM['linkurl'] = $linkurl;

$userid = $COM['userid'];
$r = $db->get_one("SELECT content FROM {$DT_PRE}company_data WHERE userid=$userid");
$COM['content'] = $COM['intro'] = $r['content'];
$COM['thumb'] = $COM['thumb'] ? $COM['thumb'] : SKIN_PATH.'image/company.jpg';
$COM['year'] = vip_year($COM['fromtime']);

$COMGROUP = cache_read('group-'.$COM['groupid'].'.php');
if(!isset($COMGROUP['homepage']) || !$COMGROUP['homepage']) {
	$head_title = $COM['company'];
	$head_keywords = $COM['keyword'];
	$head_description = $COM['introduce'];
	$member = $COM;
	$content = $COM['content'];
	include template('show', $module);
	exit;
}
//Rewrite
isset($rewrite) or $rewrite = '';
if($rewrite) {
	if(substr($rewrite, -1) == '/') $rewrite = substr($rewrite, 0, -1);
	$r = explode('/', $rewrite);
	$rc = count($r);
	if(isset($file) && $file) {
		if($rc%2 == 0) {
			for($i = 0; $i < $rc; $i++) {
				$$r[$i] = $r[++$i];
			}
		}
	} else {
		$file = $r[0];
		if($rc%2 == 1) {
			for($i = 1; $i < $rc; $i++) {
				$$r[$i] = $r[++$i];
			}
		}
	}
	$page = isset($page) ? max(intval($page), 1) : 1;
	$catid = isset($catid) ? intval($catid) : 0;
	$itemid = isset($itemid) ? (is_array($itemid) ? $itemid : intval($itemid)) : 0;
	$kw = isset($kw) ? htmlspecialchars(str_replace(array("\'"), array(''), trim(urldecode($kw)))) : '';	
	$keyword = $kw ? str_replace(array(' ', '*'), array('%', '%'), $kw) : '';
}

isset($file) or $file = 'homepage';
$MFILE = array('introduce', 'sell', 'buy', 'news', 'credit', 'job', 'contact', 'link', 'homepage');
in_array($file, $MFILE) or dheader($MOD['linkurl']);

$HMENU = $DMENU = array('公司介绍', '供应产品', '采购清单', '新闻中心', '荣誉资质', '人才招聘', '联系方式', '友情链接');
$HSIDE = array('网站公告', '新闻中心', '产品分类', '联系方式', '站内搜索', '荣誉资质', '友情链接');
$SFILE = array('announce', 'news', 'type', 'contact', 'search', 'credit', 'link');
$HMAIN = array('推荐产品', '公司介绍', '最新供应', '公司新闻', '荣誉资质', '联系方式');
$IFILE = array('elite', 'introduce', 'sell', 'news', 'credit', 'contact');

$_menu_show = '1,1,1,1,1,1,1,1';
$_menu_order = '0,1,2,3,4,5,6,7';
$_menu_num = '1,16,30,30,10,30,1,40';
$_menu_file = implode(',' , $MFILE);
$_menu_name = implode(',' , $HMENU);

$_main_show = '1,1,1,0,0,0';
$_main_order = '0,1,2,3,4,5';
$_main_num = '10,1,10,5,3,1';
$_main_file= implode(',' , $IFILE);
$_main_name = implode(',' , $HMAIN);

$_side_show = '1,1,1,0,1,1,1';
$_side_order = '0,1,2,3,4,5,6';
$_side_num = '1,5,10,1,1,5,5';
$_side_file = implode(',' , $SFILE);
$_side_name = implode(',' , $HSIDE);

$HOME = get_company_setting($COM['userid']);

//MENU
$menu_show = explode(',', isset($HOME['menu_show']) ? $HOME['menu_show'] : $_menu_show);
$menu_order = explode(',', isset($HOME['menu_order']) ? $HOME['menu_order'] : $_menu_order);
$menu_num = explode(',', isset($HOME['menu_num']) ? $HOME['menu_num'] : $_menu_num);
$menu_file = explode(',', isset($HOME['menu_file']) ? $HOME['menu_file'] : $_menu_file);
$menu_name = explode(',', isset($HOME['menu_name']) ? $HOME['menu_name'] : $_menu_name);
$_HMENU = array();
asort($menu_order);
foreach($menu_order as $k=>$v) {
	$_HMENU[$k] = $HMENU[$k];
}
$HMENU = $_HMENU;

$MENU = array();
$menuid = 0;
foreach($HMENU as $k=>$v) {
	if($menu_show[$k] && in_array($menu_file[$k], $MFILE)) {
		$MENU[$k]['name'] = $menu_name[$k];
		$MENU[$k]['linkurl'] = userurl($username, 'file='.$menu_file[$k], $domain);
	}
	if($file == $menu_file[$k]) $menuid = $k;
	if($menu_num[$k] < 1 || $menu_num[$k] > 50) $menu_num[$k] = 10;
}

//SIDE	
$side_show = explode(',', isset($HOME['side_show']) ? $HOME['side_show'] : $_side_show);
$side_order = explode(',', isset($HOME['side_order']) ? $HOME['side_order'] : $_side_order);
$side_num = explode(',', isset($HOME['side_num']) ? $HOME['side_num'] : $_side_num);
$side_file = explode(',', isset($HOME['side_file']) ? $HOME['side_file'] : $_side_file);
$side_name = explode(',', isset($HOME['side_name']) ? $HOME['side_name'] : $_side_name);
$_HSIDE = array();
asort($side_order);
foreach($side_order as $k=>$v) {
	if($side_show[$k] && in_array($side_file[$k], $SFILE)) {
		$_HSIDE[$k] = $HSIDE[$k];
	}
	if($side_num[$k] < 1 || $side_num[$k] > 50) $side_num[$k] = 10;
}
$HSIDE = $_HSIDE;

$side_pos = isset($HOME['side_pos']) && $HOME['side_pos'] ? 1 : 0;
$side_width = isset($HOME['side_width']) && $HOME['side_width'] ? $HOME['side_width'] : 200;
$show_stats = isset($HOME['show_stats']) && $HOME['show_stats'] == 0 ? 0 : 1;
$intro_length = isset($HOME['intro_length']) && $HOME['intro_length'] ? intval($HOME['intro_length']) : 1000;

$COM['intro'] = nl2br(dsubstr(strip_tags($COM['intro']), $intro_length, '...'));

$skin = 'default';
$template = 'homepage';
if($COM['skin'] && $COM['template']) {
	$skin = $COM['skin'];
	$template = $COM['template'];
} else if($COMGROUP['styleid']) {
	$r = $db->get_one("SELECT * FROM {$DT_PRE}style WHERE itemid=$COMGROUP[styleid]");
	if($r) {
		$skin = $r['skin'];
		$template = $r['template'];
	}
}
if($COM['banner']) {
	$banner_ext = strtolower(file_ext($COM['banner']));
	if($banner_ext == 'swf') {
		//
	} else if(in_array($banner_ext, array('jpg', 'gif', 'png'))) {
		$HOME['banner'] = $COM['banner'];
		$COM['banner'] = '';
	} else {
		$COM['banner'] = '';
	}
}

$preview = isset($preview) ? intval($preview) : 0;
if($file == 'homepage') {
	if($preview) {
		$preview = $db->get_one("SELECT * FROM {$DT_PRE}style WHERE itemid={$preview}");
		if($preview) {
			$skin = $preview['skin'];
			$template = $preview['template'];
		}
	}
}
$could_comment = $MOD['comment'];
$homeurl = $MOD['homeurl'];
if($domain) $could_comment = false;
$could_contact = check_group($_groupid, $MOD['group_contact']);
if($username == $_username || $domain) $could_contact = true;

$HSPATH = SKIN_PATH.'homepage/'.$skin.'/';
$background = isset($HOME['background']) && $HOME['background'] ? $HOME['background'] : '';
$banner = isset($HOME['banner']) && $HOME['banner'] ? $HOME['banner'] : (is_file(DT_ROOT.'/skin/'.$CFG['skin'].'/homepage/'.$skin.'/banner.jpg') ? $HSPATH.'banner.jpg' : '');
$bgcolor = isset($HOME['bgcolor']) && $HOME['bgcolor'] ? $HOME['bgcolor'] : '';
$logo = isset($HOME['logo']) && $HOME['logo'] ? $HOME['logo'] : '';
$css = isset($HOME['css']) && $HOME['css'] ? $HOME['css'] : '';
$announce = isset($HOME['announce']) && $HOME['announce'] ? $HOME['announce'] : '';
$map = isset($HOME['map']) && $HOME['map'] ? $HOME['map'] : '';

$head_title = $MENU[$menuid]['name'];
$seo_keywords = isset($HOME['seo_keywords']) && $HOME['seo_keywords'] ? $HOME['seo_keywords'] : '';
$seo_description = isset($HOME['seo_description']) && $HOME['seo_description'] ? $HOME['seo_description'] : '';

$head_keywords = strip_tags($seo_keywords ? $seo_keywords : $COM['company'].','.$COM['business']);
$head_description = strip_tags($seo_description ? $seo_description : $COM['introduce']);
$db->query("UPDATE {$DT_PRE}company SET hits=hits+1 WHERE userid=$userid");
(@include DT_ROOT.'/module/'.$module.'/'.$file.'.inc.php') or dheader($MOD['linkurl']);
exit;
?>