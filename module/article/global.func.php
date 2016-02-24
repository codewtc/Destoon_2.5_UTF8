<?php
defined('IN_DESTOON') or exit('Access Denied');
function article_pages($item, $total, $page = 1) {
	global $MOD;
	$pages = '';
	$demo_url = $MOD['linkurl'].itemurl($item, '{destoon_page}');
	$_page = $page <= 1 ? $total : ($page - 1);
	$url = str_replace('{destoon_page}', $_page, $demo_url);
	$pages .= '<input type="hidden" id="des'.'toon_previous" value="'.$url.'"/><a href="'.$url.'" title="上一页(支持左方向键)">&nbsp;&#171;&nbsp;</a> ';
	for($_page = 1; $_page <= $total; $_page++) {
		$url = str_replace('{destoon_page}', $_page, $demo_url);
		$pages .= $page == $_page ? '<strong>&nbsp;'.$_page.'&nbsp;</strong> ' : ' <a href="'.$url.'">&nbsp;'.$_page.'&nbsp;</a>  ';
	}
	$_page = $page >= $total ? 1 : $page + 1;
	$url = str_replace('{destoon_page}', $_page, $demo_url);
	$pages .= '<a href="'.$url.'" title="下一页(支持右方向键)">&nbsp;&#187;&nbsp;</a> <input type="hidden" id="des'.'toon_next" value="'.$url.'"/>';
	return $pages;
}
?>