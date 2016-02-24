<?php 
defined('IN_DESTOON') or exit('Access Denied');
if(!$MOD['sitemaps']) {
	@unlink(DT_ROOT.'/sitemaps.xml');
	return false;
}
$today = timetodate($DT_TIME, 3);
$mods = explode(',', $MOD['sitemaps_module']);
$nums = intval($MOD['sitemaps_items']/count($mods));
$data = '<?xml version="1.0" encoding="UTF-8"?>';
$data .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$data .= '<url>';
$data .= '<loc>'.DT_URL.'</loc>';
$data .= '<lastmod>'.$today.'</lastmod>';
$data .= '<changefreq>always</changefreq>';
$data .= '<priority>1.0</priority>';
$data .= '</url>';
$item = '';
foreach($mods as $mid) {
	if(isset($MODULE[$mid]) && !$MODULE[$mid]['islink']) {
		$url = linkurl($MODULE[$mid]['linkurl']);
		$data .= '<url>';
		$data .= '<loc>'.$url.'</loc>';
		$data .= '<lastmod>'.$today.'</lastmod>';
		$data .= '<changefreq>hourly</changefreq>';
		$data .= '<priority>0.9</priority>';
		$data .= '</url>';
		if($nums) {
			$fields = $mid == 4 ? 'linkurl' : 'linkurl,edittime';
			$order = $mid == 4 ? 'userid' : 'addtime';
			$result = $db->query("SELECT $fields FROM ".get_table($mid)." ORDER BY $order DESC LIMIT $nums");
			while($r = $db->fetch_array($result)) {
				$item .= '<url>';
				$item .= '<loc>'.xml_linkurl($r['linkurl'], $url).'</loc>';
				$item .= '<lastmod>'.($mid == 4 ? $today : timetodate($r['edittime'], 3)).'</lastmod>';
				$item .= '<changefreq>'.$MOD['sitemaps_changefreq'].'</changefreq>';
				$item .= '<priority>'.$MOD['sitemaps_priority'].'</priority>';
				$item .= '</url>';
			}
		}
	}
}
$data .= $item;
$data .= '</urlset>';
$data = str_replace('><', ">\n<", $data);
$data = convert($data, $CFG['charset'], 'utf-8');
file_put(DT_ROOT.'/sitemaps.xml', $data);
return true;
?>