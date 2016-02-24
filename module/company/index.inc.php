<?php
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
$username = $domain = '';
if(isset($homepage) && preg_match("/^[a-z0-9]{2,}$/", $homepage)) {
	$username = $homepage;
} else if($CFG['com_domain']) {
	$host = $_SERVER['HTTP_HOST'];
	if(strpos(DT_URL, $host) === false && strpos($MODULE[4]['linkurl'], $host) === false) {
		$www = str_replace($CFG['com_domain'], '', $host);
		if(preg_match("/^[a-z0-9]{2,}$/", $www)) {
			$username = $homepage = $www;
		} else {
			$c = $db->get_one("SELECT username FROM {$DT_PRE}company WHERE domain='$host'");
			if($c) {
				$username = $homepage = $c['username'];
				$domain = $host;
			}
		}
	}
}
if($username) {
	require DT_ROOT.'/module/'.$module.'/init.inc.php';
} else {
	if($DT['safe_domain']) {
		$safe_domain = explode('|', $DT['safe_domain']);
		$pass_domain = false;
		foreach($safe_domain as $v) {
			if(strpos($DT_URL, $v) !== false) { $pass_domain = true; break; }
		}
		$pass_domain or exit(header("HTTP/1.1 404 Not Found"));
	}

	if(!check_group($_groupid, $MOD['group_index'])) {
		$head_title = '抱歉，您所在的会员组没有权限访问此页面';
		include template('noright', 'message');
		exit;
	}

	include DT_ROOT.'/include/seo.inc.php';
	if($MOD['seo_index']) {
		eval("\$seo_title = \"$MOD[seo_index]\";");
	} else {
		$seo_title = $seo_modulename.$seo_delimiter.$seo_sitename;
	}
	
	$destoon_task = "moduleid=$moduleid&html=index";
	if($CFG['cache_page']) defined('TOHTML') or define('TOHTML', true);
	include template('index', $module);
	if($CFG['cache_page']) include DT_ROOT.'/include/cache.html.php';
}
?>