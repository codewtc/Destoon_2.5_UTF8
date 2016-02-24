<?php
defined('IN_DESTOON') or exit('Access Denied');
defined('DT_ADMIN') or exit('Access Denied');
$setting = array ('group_color' => '1,7','group_search' => '1,3,5,6,7','group_show' => '1,3,5,6,7','group_list' => '1,3,5,6,7','group_index' => '1,3,5,6,7','php_item_urlid' => '0','htm_item_urlid' => '1','htm_item_prefix' => '','show_html' => '0','php_list_urlid' => '0','htm_list_urlid' => '0','list_html' => '0','htm_list_prefix' => '','index_html' => '0','seo_title_search' => '{关键词}{地区}{分类名称}{模块名称}搜索{分隔符}{页码}{网站名称}','seo_title_show' => '{内容标题}{分隔符}{分类名称}{模块名称}{分隔符}{网站名称}','seo_title_list' => '{分类SEO标题}{页码}{模块名称}{分隔符}{网站名称}','seo_description' => '','seo_keywords' => '','seo_title_index' => '{模块名称}{分隔符}{页码}{网站名称}','level' => '推荐文章|幻灯图片|推荐图文|头条相关|头条推荐','text_data' => '0','show_np' => '0','keylink' => '0','clear_link' => '0','save_remotepic' => '0','introduce_length' => '120','order' => 'addtime desc','max_width' => '550','pagesize' => '20','template_search' => '','thumb_width' => '120','thumb_height' => '90','template_index' => '','template_list' => '','template_show' => '','check_add' => '2','captcha_add' => '2','question_add' => '2','fee_add' => '0','fee_view' => '0','pre_view' => '200','credit_add' => '2','credit_del' => '5','credit_color' => '100','seo_index' => '{$seo_modulename}{$seo_delimiter}{$seo_page}{$seo_sitename}','seo_list' => '{$seo_cattitle}{$seo_page}{$seo_modulename}{$seo_delimiter}{$seo_sitename}','seo_show' => '{$seo_showtitle}{$seo_delimiter}{$seo_catname}{$seo_modulename}{$seo_delimiter}{$seo_sitename}','seo_search' => '{$seo_kw}{$seo_areaname}{$seo_catname}{$seo_modulename}搜索{$seo_delimiter}{$seo_page}{$seo_sitename}',);

update_setting($moduleid, $setting);
$db->query("UPDATE {$DT_PRE}module SET listorder=$moduleid WHERE moduleid=$moduleid");
install_file('index', $dir, 1);
install_file('list', $dir, 1);
install_file('show', $dir, 1);
install_file('search', $dir, 1);
file_copy(DT_ROOT.'/api/ajax.php', DT_ROOT.'/'.$dir.'/ajax.php');
if($db->version() > '4.1' && $CFG['db_charset']) {
	$this_sql = " ENGINE=MyISAM DEFAULT CHARSET=".$CFG['db_charset'];
} else {
	$this_sql = " TYPE=MyISAM";
}
$db->query("DROP TABLE IF EXISTS `".$DT_PRE."article_".$moduleid."`");
$db->query("CREATE TABLE `".$DT_PRE."article_".$moduleid."`(`itemid` bigint(20) unsigned NOT NULL auto_increment,`catid` int(10) unsigned NOT NULL default '0',`level` tinyint(1) unsigned NOT NULL default '0',`title` varchar(100) NOT NULL default '',`style` varchar(50) NOT NULL default '',`fee` float NOT NULL default '0',`introduce` varchar(255) NOT NULL default '',`tag` varchar(100) NOT NULL default '',`keyword` varchar(255) NOT NULL default '',`author` varchar(50) NOT NULL default '',`copyfrom` varchar(30) NOT NULL default '',`fromurl` varchar(255) NOT NULL default '',`voteid` varchar(100) NOT NULL default '',`hits` int(10) unsigned NOT NULL default '0',`thumb` varchar(255) NOT NULL default '',`username` varchar(30) NOT NULL default '',`addtime` int(10) unsigned NOT NULL default '0',`editor` varchar(30) NOT NULL default '',`edittime` int(10) unsigned NOT NULL default '0',`ip` varchar(15) NOT NULL default '',`template` varchar(30) NOT NULL default '0',`status` tinyint(1) NOT NULL default '0',`islink` tinyint(1) unsigned NOT NULL default '0',`linkurl` varchar(255) NOT NULL default '',`filepath` varchar(255) NOT NULL default '',`note` varchar(255) NOT NULL default '',PRIMARY KEY  (`itemid`),KEY `keyword` (`keyword`),KEY `addtime` (`addtime`))".$this_sql." COMMENT='".$modulename."'");
$db->query("DROP TABLE IF EXISTS `".$DT_PRE."article_data_".$moduleid."`");
$db->query("CREATE TABLE `".$DT_PRE."article_data_".$moduleid."` (`itemid` int(10) unsigned NOT NULL default '0',`content` mediumtext NOT NULL,UNIQUE KEY `itemid` (`itemid`))".$this_sql." COMMENT='".$modulename."内容'");
?>