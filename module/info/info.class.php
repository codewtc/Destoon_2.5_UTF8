<?php 
defined('IN_DESTOON') or exit('Access Denied');
class info {
	var $moduleid;
	var $itemid;
	var $db;
	var $table;
	var $table_data;
	var $text_data;
	var $fields;
	var $errmsg = errmsg;

    function info($moduleid) {
		global $db, $table, $table_data, $MOD;
		$this->moduleid = $moduleid;
		$this->table = $table;
		$this->table_data = $table_data;
		$this->text_data = $MOD['text_data'];
		$this->db = &$db;
		$this->fields = array('catid','level','title','style','fee','introduce','totime','areaid','thumb','status','username','addtime','adddate','editor','edittime','editdate','ip','template','islink', 'linkurl','filepath','note','company','truename','telephone','mobile','address','email','msn','qq');
    }

	function pass($post) {
		global $DT_TIME, $MOD;
		if(!is_array($post)) return false;
		if(!$post['catid']) return $this->_('请选择分类');
		if(!$post['title']) return $this->_('请填写信息标题');
		if($post['totime']) {
			if(!is_date($post['totime'])) return $this->_('日期格式错误');
			if(strtotime($post['totime'].' 23:59:59') < $DT_TIME) return $this->_('信息过期时间必须在当前时间之后');
		}
		if(isset($post['islink'])) {
			if(!$post['linkurl']) return $this->_('请填写链接地址');
		} else {
			if(!$post['content']) return $this->_('请填写详细内容');
		}
		return true;
	}

	function set($post) {
		global $MOD, $DT_TIME, $DT_IP, $CATEGORY, $AREA, $_username, $_userid;
		$post['editor'] = $_username;
		$post['islink'] = isset($post['islink']) ? 1 : 0;
		$post['addtime'] = (isset($post['addtime']) && $post['addtime']) ? strtotime($post['addtime']) : $DT_TIME;
		$post['adddate'] = timetodate($post['addtime'], 3);
		$post['edittime'] = $DT_TIME;
		$post['editdate'] = timetodate($post['edittime'], 3);
		$post['ip'] = $DT_IP;
		$post['totime'] = $post['totime'] ? strtotime($post['totime'].' 23:59:59') : 0;
		$post['fee'] = dround($post['fee']);
		$post['content'] = stripslashes($post['content']);
		//clear link
		if($post['content'] && isset($post['clear_link'])) $post['content'] = clear_link($post['content']);
		//save pictures
		if($post['content'] && isset($post['save_remotepic'])) $post['content'] = save_remote($post['content']);
		//save thumb
		if($post['content'] && $post['thumb_no'] && !$post['thumb']) $post['thumb'] = save_thumb($post['content'], $post['thumb_no'], $MOD['thumb_width'], $MOD['thumb_height']);
		//get introduce
		if($MOD['introduce_length']) $post['introduce'] = addslashes(get_intro($post['content'], $MOD['introduce_length']));
		//clear uploads
		clear_upload($post['content'].$post['thumb']);
		if($this->itemid) {
			if(!$post['islink']) $post['linkurl'] = itemurl($this->itemid, $post['catid'], $post['addtime']);
			$new = $post['content'];
			if($post['thumb']) $new .= '<img src="'.$post['thumb'].'">';
			$r = $this->get_one();
			$old = $r['content'];
			if($r['thumb']) $old .= '<img src="'.$r['thumb'].'">';
			delete_diff($new, $old);
		}
		if(!defined('DT_ADMIN')) {
			$content = $post['content'];
			unset($post['content']);
			$post = dhtmlspecialchars($post);
			$post['content'] = dsafe($content);
		}
		$post['content'] = addslashes($post['content']);
		return $post;
	}

	function get_one() {
        return $this->db->get_one("SELECT * FROM {$this->table} a,{$this->table_data} c WHERE a.itemid=c.itemid and a.itemid='$this->itemid' limit 0,1");
	}

	function get_list($condition = 'status=3', $order = 'edittime DESC', $cache = '') {
		global $MOD, $pages, $page, $pagesize, $offset, $items;
		$r = $this->db->get_one("SELECT COUNT(*) AS num FROM {$this->table} WHERE $condition", $cache);
		$items = $r['num'];
		$pages = defined('CATID') ? listpages(1, CATID, $items, $page, $pagesize, 10, $MOD['linkurl']) : pages($items, $page, $pagesize);
		$lists = array();
		$result = $this->db->query("SELECT * FROM {$this->table} WHERE $condition ORDER BY $order LIMIT $offset,$pagesize", $cache);
		while($r = $this->db->fetch_array($result)) {
			$r['adddate'] = timetodate($r['addtime'], 5);
			$r['editdate'] = timetodate($r['edittime'], 5);
			$r['todate'] = timetodate($r['totime'], 3);
			$r['alt'] = $r['title'];
			$r['title'] = set_style($r['title'], $r['style']);
			if(!$r['islink']) $r['linkurl'] = $MOD['linkurl'].$r['linkurl'];
			$lists[] = $r;
		}
		return $lists;
	}

	function add($post) {
		global $MOD;
		$post = $this->set($post);
		$sqlk = $sqlv = '';
		foreach($post as $k=>$v) {
			if(in_array($k, $this->fields)) { $sqlk .= ','.$k; $sqlv .= ",'$v'"; }
		}
        $sqlk = substr($sqlk, 1);
        $sqlv = substr($sqlv, 1);
		$this->db->query("INSERT INTO {$this->table} ($sqlk) VALUES ($sqlv)");
		$this->itemid = $this->db->insert_id();
		$this->db->query("INSERT INTO {$this->table_data} (itemid,content) VALUES ('$this->itemid', '$post[content]')");
		$this->update($this->itemid, $post, $post['content']);
		if($post['status'] > 2) $this->tohtml($this->itemid, $post['catid']);
		if($post['status'] == 3 && $post['username'] && $MOD['credit_add']) {
			credit_add($post['username'], $MOD['credit_add']);
			credit_record($post['username'], $MOD['credit_add'], 'system', $MOD['name'].'发布', 'ID:'.$this->itemid);
		}
		return $this->itemid;
	}

	function edit($post) {
		$this->delete($this->itemid, false);
		$post = $this->set($post);
		$sql = '';
		foreach($post as $k=>$v) {
			if(in_array($k, $this->fields)) $sql .= ",$k='$v'";
		}
        $sql = substr($sql, 1);
	    $this->db->query("UPDATE {$this->table} SET $sql WHERE itemid=$this->itemid");
	    $this->db->query("UPDATE {$this->table_data} SET content='$post[content]' WHERE itemid=$this->itemid");
		$this->update($this->itemid, $post, $post['content']);
		if($post['status'] > 2) $this->tohtml($this->itemid, $post['catid']);
		return true;
	}

	function tohtml($itemid = 0, $catid = 0) {
		global $module, $MOD;
		if($MOD['show_html'] && $itemid) tohtml('show', $module, "itemid=$itemid");
		if($MOD['list_html'] && $catid) tohtml('list', $module, "catid=$catid&fid=1&num=3");
		if($MOD['index_html']) tohtml('index', $module);
	}

	function update($itemid, $item = array(), $content = '') {
		global $DT_PRE;
		$item or $item = $this->db->get_one("SELECT * FROM {$this->table} WHERE itemid=$itemid");
		$keyword = $item['title'].','.strip_tags(cat_pos($item['catid'], ',')).strip_tags(area_pos($item['areaid'], ','));
		$keyword = str_replace("//", '', addslashes($keyword));
		$item['itemid'] = $itemid;
		$linkurl = $item['islink'] ? $item['linkurl'] : itemurl($item);
		$sql = "keyword='$keyword',linkurl='$linkurl'";
		if($item['username']) {
			$m = daddslashes(userinfo($item['username']));
			if($m) $sql .= ",groupid='$m[groupid]',company='$m[company]',vip='$m[vip]',validated='$m[validated]',areaid='$m[areaid]',truename='$m[truename]',telephone='$m[telephone]',mobile='$m[mobile]',address='$m[address]',email='$m[mail]',msn='$m[msn]',qq='$m[qq]'";
		}
		$this->db->query("UPDATE {$this->table} SET $sql WHERE itemid=$itemid");

		if($this->text_data) {
			if(!$content) {
				$content = $this->db->get_one("SELECT content FROM {$this->table_data} WHERE itemid=$itemid");
				$content = $content['content'];
			}
			text_write($itemid, $this->moduleid, $content);
		} else {
			text_delete($itemid, $this->moduleid);
		}
	}

	function recycle($itemid) {
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->recycle($v); }
		} else {
			$this->db->query("UPDATE {$this->table} SET status=0 WHERE itemid=$itemid");
			$this->delete($itemid, false);
			return true;
		}		
	}

	function restore($itemid) {
		global $module, $MOD;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->restore($v); }
		} else {
			$this->db->query("UPDATE {$this->table} SET status=3 WHERE itemid=$itemid");
			if($MOD['show_html']) tohtml('show', $module, "itemid=$itemid");
			return true;
		}		
	}

	function delete($itemid, $all = true) {
		global $CFG, $MOD;
		if(is_array($itemid)) {
			foreach($itemid as $v) { 
				$this->delete($v, $all);
			}
		} else {
			$this->itemid = $itemid;
			$r = $this->get_one();
			if($MOD['show_html'] && !$r['islink']) {
				$_file = DT_ROOT.'/'.$MOD['moduledir'].'/'.$r['linkurl'];
				if(is_file($_file)) unlink($_file);
			}
			if($all) {
				$userid = get_user($r['username']);
				if($r['thumb']) delete_upload($r['thumb'], $userid);
				if($r['content']) delete_local($r['content'], $userid);
				$this->db->query("DELETE FROM {$this->table} WHERE itemid=$itemid");
				$this->db->query("DELETE FROM {$this->table_data} WHERE itemid=$itemid");
				if($this->text_data) text_delete($this->itemid, $this->moduleid);
				if($r['username'] && $MOD['credit_del']) {
					credit_add($r['username'], -$MOD['credit_del']);
					credit_record($r['username'], -$MOD['credit_del'], 'system', $MOD['name'].'删除', 'ID:'.$this->itemid);
				}
			}
		}
	}

	function check($itemid) {
		global $_username, $DT_TIME, $MOD;;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->check($v); }
		} else {
			$this->itemid = $itemid;
			$item = $this->get_one();
			if($MOD['credit_add'] && $item['username'] && $item['addtime'] == $item['edittime']) {
				credit_add($item['username'], $MOD['credit_add']);
				credit_record($item['username'], $MOD['credit_add'], 'system', $MOD['name'].'发布', 'ID:'.$this->itemid);
			}
			$editdate = timetodate($DT_TIME, 3);
			$this->db->query("UPDATE {$this->table} SET status=3,editor='$_username',edittime=$DT_TIME,editdate='$editdate' WHERE itemid=$itemid");
			$this->tohtml($itemid);
			return true;
		}
	}

	function reject($itemid) {
		global $_username, $DT_TIME;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->reject($v); }
		} else {
			$this->db->query("UPDATE {$this->table} SET status=1,editor='$_username' WHERE itemid=$itemid");
			return true;
		}
	}

	function expire($condition = '') {
		global $DT_TIME;
		$this->db->query("UPDATE {$this->table} SET status=4 WHERE status=3 AND totime>0 AND totime<$DT_TIME $condition");
	}

	function clear($condition = 'status=0') {		
		$result = $this->db->query("SELECT itemid FROM {$this->table} WHERE $condition ");
		while($r = $this->db->fetch_array($result)) {
			$this->delete($r['itemid']);
		}
	}

	function level($itemid, $level) {
		$itemids = is_array($itemid) ? implode(',', $itemid) : $itemid;
		$this->db->query("UPDATE {$this->table} SET level=$level WHERE itemid IN ($itemids)");
	}

	function refresh($itemid) {
		global $DT_TIME;
		$editdate = timetodate($DT_TIME, 3);
		$this->db->query("UPDATE {$this->table} SET edittime='$DT_TIME',editdate='$editdate' WHERE itemid='$itemid'");
	}

	function _update($username) {
		global $DT_TIME;
		$this->db->query("UPDATE {$this->table} SET status=4 WHERE status=3 AND totime>0 AND totime<$DT_TIME AND username='$username'");
	}

	function _($e) {
		$this->errmsg = $e;
		return false;
	}
}
?>