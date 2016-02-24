<?php
/*
	[Destoon B2B System] Copyright (c) 2008-2010 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
defined('IN_DESTOON') or exit('Access Denied');
if($PCF) {
	$contents = ob_get_clean();
	if($PCF) file_put($PCF, '<?php defined("IN_DESTOON") or exit("Access Denied");?>'.$contents);
	echo $contents;
}
?>