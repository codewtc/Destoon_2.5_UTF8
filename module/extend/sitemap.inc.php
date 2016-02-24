<?php 
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/'.$module.'/common.inc.php';
$head_title = '网站地图';
if($CFG['cache_page']) defined('TOHTML') or define('TOHTML', true);
include template('sitemap', $module);
if($CFG['cache_page']) include DT_ROOT.'/include/cache.html.php';
?>