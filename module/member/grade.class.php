<?php 
defined('IN_DESTOON') or exit('Access Denied');
class grade {
	var $itemid;
	var $db;
	var $table;
	var $errmsg = errmsg;

    function grade() {
		global $db, $DT_PRE;
		$this->table = $DT_PRE.'upgrade';
		$this->db = &$db;
    }

	function get_one($condition = '') {
        return $this->db->get_one("SELECT * FROM {$this->table} WHERE itemid='$this->itemid' $condition limit 0,1");
	}

	function get_list($condition = 'status=3', $order = 'addtime DESC') {
		global $MOD, $pages, $page, $pagesize, $offset;
		$r = $this->db->get_one("SELECT COUNT(*) AS num FROM {$this->table} WHERE $condition");
		$pages = pages($r['num'], $page, $pagesize);		
		$lists = array();
		$result = $this->db->query("SELECT * FROM {$this->table} WHERE $condition ORDER BY $order LIMIT $offset,$pagesize");
		while($r = $this->db->fetch_array($result)) {
			$r['adddate'] = timetodate($r['addtime'], 5);
			$r['editdate'] = timetodate($r['edittime'], 5);
			$lists[] = $r;
		}
		return $lists;
	}

	function edit($post) {
		global $DT_PRE, $_username, $DT_TIME, $GROUP;
		$item = $this->get_one();
		$user = $item['username'] ? userinfo($item['username']) : array();
		$gsql = $msql = $csql = '';
		$gsql = "edittime=$DT_TIME,editor='$_username',status=$post[status],note='$post[note]'";
		if($post['status'] == 1) {
			//拒绝申请
			if($user) {
				if($post['message'] && $post['content']) {
					send_message($user['username'], '您的会员组升级('.$GROUP[$item['groupid']]['groupname'].')失败', nl2br($post['content']));
					$gsql .= ",message=1";
				}
				if($item['amount']) {
					money_add($item['username'], $item['amount']);
					money_record($item['username'], $item['amount'], '站内', 'system', '会员升级', '升级失败返款');
				}
			}
		} else if($post['status'] == 2) {
			//
		} else if($post['status'] == 3) {
			if($user) {
				if(isset($post['pay']) && $post['pay']) {
					if($user['money'] < $post['pay']) {
						return $this->_('会员余额不足');
					} else {
						money_add($item['username'], -$post['pay']);
						money_record($item['username'], -$post['pay'], '站内', 'system', '会员升级', '升级为'.$GROUP[$item['groupid']]['groupname']);
					}
				}
				$msql = $csql = "groupid=$item[groupid]";
				$vip = $GROUP[$item['groupid']]['vip'];
				$csql .= ",vip=$vip,vipt=$vip";
				if(isset($post['pay'])) {
					$csql .= ",fromtime=".strtotime($post['fromtime']).",totime=".strtotime($post['totime']).",validtime=".strtotime($post['validtime']).",validator='$post[validator]',validated=$post[validated]";
				}
				if($post['message'] && $post['content']) {
					send_message($user['username'], '您的会员组升级('.$GROUP[$item['groupid']]['groupname'].')成功', nl2br($post['content']));
					$gsql .= ",message=1";
				}
			}
		}
		$this->db->query("UPDATE {$this->table} SET $gsql WHERE itemid=$this->itemid");
		if($msql) $this->db->query("UPDATE {$DT_PRE}member SET $msql WHERE userid=$item[userid]");
		if($csql) $this->db->query("UPDATE {$DT_PRE}company SET $csql WHERE userid=$item[userid]");
		return true;
	}

	function delete($itemid, $all = true) {
		global $CFG, $MOD;
		if(is_array($itemid)) {
			foreach($itemid as $v) { $this->delete($v); }
		} else {
			$this->db->query("DELETE FROM {$this->table} WHERE itemid=$itemid");
		}
	}

	function _($e) {
		$this->errmsg = $e;
		return false;
	}
}
?>