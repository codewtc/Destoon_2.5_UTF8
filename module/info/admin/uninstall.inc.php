<?php
defined('IN_DESTOON') or exit('Access Denied');
defined('DT_ADMIN') or exit('Access Denied');
$_groupid == 1 or exit('Access Denied');
if($dir) dir_delete(DT_ROOT.'/'.$dir);
$db->query("DROP TABLE IF EXISTS `".$DT_PRE."info_".$moduleid."`");
$db->query("DROP TABLE IF EXISTS `".$DT_PRE."info_data_".$moduleid."`");
?>