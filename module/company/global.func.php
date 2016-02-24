<?php
defined('IN_DESTOON') or exit('Access Denied');
require DT_ROOT.'/module/member/global.func.php';
function home_pages($total, $pagesize, $demo_url, $page = 1) {
	global $MOD;
	$pages = '';
	$items = $total;
	$total = ceil($total/$pagesize);
	$page = intval($page);
	if($page < 1 || $page > $total) $page = 1;
	$demo_url = str_replace(array('%7B', '%7D'), array('{', '}'), $demo_url);

	$pages .= '<label title="共'.$items.'条">第<span>'.$page.'</span>页/共<strong>'.$total.'</strong>页</label>&nbsp;&nbsp;';

	$url = str_replace('{destoon_page}', 1, $demo_url);
	$pages .= '<a href="'.$url.'">&nbsp;首页&nbsp;</a> ';

	$_page = $page <= 1 ? $total : ($page - 1);
	$url = str_replace('{destoon_page}', $_page, $demo_url);
	$pages .= '<a href="'.$url.'">&nbsp;上一页&nbsp;</a> ';

	$_page = $page >= $total ? 1 : $page + 1;
	$url = str_replace('{destoon_page}', $_page, $demo_url);
	$pages .= '<a href="'.$url.'">&nbsp;下一页&nbsp;</a> ';

	$url = str_replace('{destoon_page}', $total, $demo_url);
	$pages .= '<a href="'.$url.'">&nbsp;末页&nbsp;</a> ';

	$pages .= '<input type="text" class="pages_inp" id="destoon_pageno" onkeydown="if(event.keyCode==13 && this.value) {window.location=\''.$demo_url.'\'.replace(/\\{destoon_page\\}/, this.value);}"> <input type="button" class="pages_btn" value="GO" onclick="if($(\'destoon_pageno\').value>0)window.location=\''.$demo_url.'\'.replace(/\\{destoon_page\\}/, $(\'destoon_pageno\').value);"/>';
	return $pages;
}
?>