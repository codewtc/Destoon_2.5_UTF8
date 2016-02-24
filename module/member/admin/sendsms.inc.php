<?php
defined('IN_DESTOON') or exit('Access Denied');
$menus = array (
    array('发送短信', '?moduleid='.$moduleid.'&file='.$file),
    array('号码列表', '?moduleid='.$moduleid.'&file='.$file.'&action=list'),
    array('获取列表', '?moduleid='.$moduleid.'&file='.$file.'&action=make'),
);
function _userinfo($mobile) {
	global $db, $DT_PRE;
	return $db->get_one("SELECT * FROM {$DT_PRE}member m,{$DT_PRE}company c WHERE m.userid=c.userid AND m.mobile='$mobile'");
}
switch($action) {
	case 'list':		 
		$others = array();
		$mailfiles = glob(DT_ROOT.'/file/mobile/*.txt');
		$mail = $mails = array();
		if(is_array($mailfiles)) {
			$class = 1;
			foreach($mailfiles as $id=>$mailfile)	{
				$tmp = basename($mailfile);
					$mail['filename'] = $tmp;
					$mail['filesize'] = round(filesize($mailfile)/(1024), 2);
					$mail['mtime'] = timetodate(filemtime($mailfile), 5);
					$mail['count'] = substr_count(file_get($mailfile), "\n") + 1;	
					$mails[] = $mail;
			}
		}
		include tpl('sendsms_list', $module);
	break;
	case 'make':
		if(isset($make)) {
			$tb or $tb = $DT_PRE.'member';
			$field or $field = 'mobile';
			$sql or $sql = 'groupid>4';
			$sql = stripslashes($sql);
			$num or $num = 1000;
			$pagesize = $num;
			$offset = ($page-1)*$pagesize;
			if($page == 1) $random = $title ? $title : mt_rand(1000, 9999);
			$result = $db->query("SELECT $field FROM $tb WHERE $sql LIMIT $offset,$pagesize");
			$mail = '';
			while($r = $db->fetch_array($result)) {
				if($r[$field]) $mail .= $r[$field]."\n";
			}
			if($mail) {
				$filename = date('Ymd').'_'.$random.'_'.$page.'.txt';
				file_put(DT_ROOT.'/file/mobile/'.$filename, trim($mail));
				$page++;
				msg('文件'.$filename.'获取成功。<br/>请稍候，程序将自动继续...', '?moduleid='.$moduleid.'&file='.$file.'&action='.$action.'&tb='.urlencode($tb).'&field='.urlencode($field).'&sql='.urlencode($sql).'&num='.$num.'&page='.$page.'&random='.urlencode($random).'&make=1');
			} else {
				msg('列表获取成功', '?moduleid='.$moduleid.'&file='.$file.'&action=list');
			}
		} else {
			include tpl('sendsms_make', $module);
		}
	break;
	case 'download':
		$file_ext = file_ext($filename);
		if($file_ext != 'txt') msg('只能下载TxT文件');
		file_down(DT_ROOT.'/file/mobile/'.$filename);
	break;
	case 'upload':
		require DT_ROOT.'/include/upload.class.php';
		$upload = new upload($_FILES, 'file/mobile/', $uploadfile_name, 'txt');	
		$upload->adduserid = false;
		if($upload->uploadfile()) dmsg('上传成功', '?moduleid='.$moduleid.'&file='.$file.'&action=list');
		msg($upload->errmsg);
	break;
	case 'delete':
		 if(is_array($filenames)) {
			 foreach($filenames as $filename) {
				 if(file_ext($filename) == 'txt') @unlink(DT_ROOT.'/file/mobile/'.$filename);
			 }
		 } else {
			 if(file_ext($filenames) == 'txt') @unlink(DT_ROOT.'/file/mobile/'.$filenames);
		 }
		 dmsg('删除成功', '?moduleid='.$moduleid.'&file='.$file.'&action=list');
	break;
	default:
		if(isset($send)) {
			if(isset($preview) && $preview) {
				if($sendtype == 2) {
					$mobiles = explode("\n", $mobiles);
					$mobile = trim($mobiles[0]);
				} else if($sendtype == 3) {
					$mobiles = explode("\n", file_get(DT_ROOT.'/file/mobile/'.$mail));
					$mobile = trim($mobiles[0]);
				}
				$user = _userinfo($mobile);
				if($user) eval("\$content = \"$content\";");
				echo $content;
				exit;
			}
			if($sendtype == 1) {
				$content or msg('请填写短信内容');
				$mobile or msg('请填写接收号码');
				$mobile = trim($mobile);
				if(is_mobile($mobile)) {
					$user = _userinfo($mobile);
					if($user) eval("\$content = \"$content\";");
					send_sms($mobile, $content);
				}
			} else if($sendtype == 2) {
				$content or msg('请填写短信内容');
				$mobiles or msg('请填写接收号码');
				$mobiles = explode("\n", $mobiles);
				$DT['mail_name'] = $name;
				foreach($mobiles as $mobile) {
					$mobile = trim($mobile);
					if(is_mobile($mobile)) {
						$user = _userinfo($mobile);
						if($user) eval("\$content = \"$content\";");
						send_sms($mobile, $content);
					}
				}
			} else if($sendtype == 3) {
				if(isset($id)) {
					$data = cache_read($_username.'_sendsms.php');
					$content = $data['content'];
					$mobilelist = $data['mobilelist'];
				} else {
					$id = 0;
					$content or msg('请填写短信内容');
					$mobilelist or msg('请选择号码列表');
					$data = array();
					$data['mobilelist'] = $mobilelist;
					$data['content'] = $content;
					cache_write($_username.'_sendsms.php', $data);
				}
				$pernum = intval($pernum);
				if(!$pernum) $pernum = 10;
				$mobiles = file_get(DT_ROOT.'/file/mobile/'.$mobilelist);
				$mobiles = explode("\n", $mobiles);
				for($i = 1; $i <= $pernum; $i++) {
					$mobile = trim($mobiles[$id++]);
					if(is_mobile($mobile)) {
						$user = _userinfo($mobile);
						if($user) eval("\$content = \"$content\";");
						send_sms($mobile, $content);
					}
				}
				if($id < count($mobiles)) {
					msg('已发送 '.$id.' 条短信，系统将自动继续，请稍候...', '?moduleid='.$moduleid.'&file='.$file.'&sendtype=3&id='.$id.'&pernum='.$pernum.'&send=1', 3);
				}
				cache_delete($_username.'_sendsms.php');
				$forward = '?moduleid='.$moduleid.'&file='.$file;
			}
			dmsg('短信发送成功', $forward);
		} else {
			isset($mobile) or $mobile = '';
			include tpl('sendsms', $module);
		}
	break;
}
?>