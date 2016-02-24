<?php 
defined('IN_DESTOON') or exit('Access Denied');
define('MD_ROOT', DT_ROOT.'/module/'.$module);
require DT_ROOT.'/include/module.func.php';
require MD_ROOT.'/global.func.php';
$CATEGORY = cache_read('category-'.$moduleid.'.php');
$ITEMS = cache_read('cateitem-'.$moduleid.'.php');
$TYPE = array('已解决', '待解决', '投票中', '零回答', '推荐', '高分');
$PROCESS = array('已关闭', '待解决', '投票中', '已解决');
$CREDITS = explode('|', trim($MOD['credits']));
if($MOD['seo_keywords']) $head_keywords = $MOD['seo_keywords'];
if($MOD['seo_description']) $head_description = $MOD['seo_description'];
$table = $DT_PRE.$module;
$table_data = $DT_PRE.$module.'_data';
?>