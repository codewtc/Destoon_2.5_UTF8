<?php
defined('IN_DESTOON') or exit('Access Denied');
if($action == 'sitemaps') {
	tohtml('sitemaps', $module);
	msg('SiteMaps 更新成功');
} else if($action == 'baidunews') {
	tohtml('baidunews', $module);
	msg('BaiduNews 更新成功');
}
?>