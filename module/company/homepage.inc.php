<?php 
defined('IN_DESTOON') or exit('Access Denied');
//MAIN	
$main_show = explode(',', isset($HOME['main_show']) ? $HOME['main_show'] : $_main_show);
$main_order = explode(',', isset($HOME['main_order']) ? $HOME['main_order'] : $_main_order);
$main_num = explode(',', isset($HOME['main_num']) ? $HOME['main_num'] : $_main_num);
$main_file = explode(',', isset($HOME['main_file']) ? $HOME['main_file'] : $_main_file);
$main_name = explode(',', isset($HOME['main_name']) ? $HOME['main_name'] : $_main_name);
$_HMAIN = array();
asort($main_order);
foreach($main_order as $k=>$v) {
	if($main_show[$k] && in_array($main_file[$k], $IFILE)) {
		$_HMAIN[$k] = $HMAIN[$k];
	}
	if($main_num[$k] < 1 || $main_num[$k] > 50) $main_num[$k] = 10;
}
$HMAIN = $_HMAIN;
$seo_title = isset($HOME['seo_title']) && $HOME['seo_title'] ? $HOME['seo_title'] : '';
$head_title = '';
if($CFG['cache_page'] && !$preview) defined('TOHTML') or define('TOHTML', true);
include template('index', $template);
if($CFG['cache_page'] && !$preview) include DT_ROOT.'/include/cache.html.php';
?>