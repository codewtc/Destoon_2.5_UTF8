<?php
defined('IN_DESTOON') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
<?php if($print) { ?>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<th>卡号</th>
<th>密码</th>
<th>面额</th>
<th>有效期至</th>
</tr>
<?php foreach($lists as $k=>$v) {?>
<tr onmouseover="this.className='on';" onmouseout="this.className='';" align="center">
<td><?php echo $v['number'];?></td>
<td><?php echo $v['password'];?></td>
<td class="f_blue"><?php echo $v['amount'];?></td>
<td><?php echo $v['totime'];?></td>
</tr>
<?php }?>
</table>
<div class="pages"><?php echo $pages;?></div>
<script type="text/javascript">Dh('destoon_menu');</script>
<?php exit; } ?>
<div class="tt">充值卡搜索</div>
<form action="?">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td>
&nbsp;
<?php echo $fields_select;?>
&nbsp;
<input type="text" size="10" name="kw" value="<?php echo $kw;?>"/>
&nbsp;
<?php echo dcalendar('fromtime', $fromtime);?> 至 <?php echo dcalendar('totime', $totime);?>&nbsp;
<select name="type">
<option value="0">时间</option>
<option value="1" <?php if($type == 1) echo 'selected';?>>充值</option>
<option value="2" <?php if($type == 2) echo 'selected';?>>到期</option>
<option value="3" <?php if($type == 3) echo 'selected';?>>制卡</option>
</select>&nbsp;
<select name="status">
<option value="0">状态</option>
<option value="1" <?php if($status == 1) echo 'selected';?>>已使用</option>
<option value="2" <?php if($status == 2) echo 'selected';?>>已过期</option>
</select>
&nbsp;
<?php echo $order_select;?>
&nbsp;
<input type="text" name="psize" value="<?php echo $pagesize;?>" size="2" class="t_c" title="条/页"/>
<input type="submit" value="搜 索" class="btn"/>
<input type="button" value="重 置" class="btn" onclick="window.location='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>';"/>
</td>
</tr>
</table>
</form>
<div class="tt">充值卡管理</div>
<form method="post">
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<th width="25"><input type="checkbox" onclick="checkall(this.form);"/></th>
<th>卡号</th>
<th>密码</th>
<th>面额</th>
<th>有效期至</th>
<th>充值会员</th>
<th>充值时间</th>
<th>充值IP</th>
<th>制卡时间</th>
</tr>
<?php foreach($lists as $k=>$v) {?>
<tr onmouseover="this.className='on';" onmouseout="this.className='';" align="center">
<td><input type="checkbox" name="itemid[]" value="<?php echo $v['itemid'];?>"/></td>
<td><?php echo $v['number'];?></td>
<td><?php echo $v['password'];?></td>
<td class="f_blue"><?php echo $v['amount'];?></td>
<td><?php echo $v['totime'];?></td>
<td><a href="javascript:_user('<?php echo $v['username'];?>');"><?php echo $v['username'];?></a></td>
<td><?php echo $v['updatetime'];?></td>
<td><a href="javascript:_ip('<?php echo $v['ip'];?>');" title="显示IP所在地"><?php echo $v['ip'];?></a></td>
<td title="制卡人:<?php echo $v['editor'];?>"><?php echo $v['addtime'];?></td>
</tr>
<?php }?>
</table>
<div class="btns">
<input type="submit" value=" 批量删除 " class="btn" onclick="if(confirm('确定要删除选中充值卡吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>
<input type="button" value=" 打印卡号 " class="btn" onclick="window.open('?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&print=1');"/>
</div>
</form>
<div class="pages"><?php echo $pages;?></div>
<div class="tt">导出记录</div>
<form method="post" action="?">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<input type="hidden" name="action" value="export"/>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td>
&nbsp;
<?php echo $fields_select;?>
&nbsp;
<input type="text" size="10" name="kw" value="<?php echo $kw;?>"/>
&nbsp;
<?php echo dcalendar('dfromtime', $dfromtime);?> 至 <?php echo dcalendar('dtotime', $dtotime);?>&nbsp;
<select name="type">
<option value="0">时间类型</option>
<option value="1" <?php if($type == 1) echo 'selected';?>>充值</option>
<option value="2" <?php if($type == 2) echo 'selected';?>>到期</option>
<option value="3" <?php if($type == 3) echo 'selected';?>>制卡</option>
</select>&nbsp;
<select name="status">
<option value="0">状态</option>
<option value="1" <?php if($status == 1) echo 'selected';?>>已使用</option>
<option value="2" <?php if($status == 2) echo 'selected';?>>已过期</option>
</select>
&nbsp;
<?php echo $order_select;?>
&nbsp;
<input type="submit" value="导出CSV" class="btn"/>
<input type="button" value="备份数据" class="btn" onclick="$('backup').submit();"/>
</td>
</tr>
</table>
</form>
<form action="?" id="backup">
<input type="hidden" name="file" value="database"/>
<input type="hidden" name="tables[]" value="<?php echo $DT_PRE;?>finance_card"/>
<input type="hidden" name="sqlcompat" value=""/>
<input type="hidden" name="sqlcharset" value=""/>
<input type="hidden" name="sizelimit" value="2048"/>
<input type="hidden" name="backup" value="1"/>
</form>
<script type="text/javascript">Menuon(1);</script>
<br/>
</body>
</html>