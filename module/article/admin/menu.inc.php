<?php
defined('IN_DESTOON') or exit('Access Denied');
$menu = array(
	array("添加".$name, "?moduleid=$moduleid&action=add"),
	array($name."列表", "?moduleid=$moduleid"),
	array("审核".$name, "?moduleid=$moduleid&action=check"),
	array("分类管理", "?file=category&mid=$moduleid"),
	array("关联链接", "?file=keylink&item=$moduleid"),
	array("生成网页", "?moduleid=$moduleid&file=html"),
	array("模块设置", "?moduleid=$moduleid&file=setting"),
);
?>