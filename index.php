<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2010 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
require 'common.inc.php';
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
	$moduleid = 4;
	$module = 'company';
	$MOD = cache_read('module-'.$moduleid.'.php');
	require DT_ROOT.'/module/'.$module.'/common.inc.php';
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

	if($DT['index_html']) {	
		$html_file = DT_ROOT.'/'.$DT['index'].'.'.$DT['file_ext'];
		if(!is_file($html_file)) tohtml('index');
		include($html_file);
		exit;
	}
	$destoon_task = '';
	$AREA = cache_read('area.php');
	$LETTER = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
	$seo_title = $DT['seo_title'];
	if($CFG['cache_page']) defined('TOHTML') or define('TOHTML', true);
	include template('index');
	if($CFG['cache_page']) include DT_ROOT.'/include/cache.html.php';
}
?>