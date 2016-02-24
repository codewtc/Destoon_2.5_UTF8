<?php
defined('IN_DESTOON') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
<div class="tt">记录搜索</div>
<form action="?">
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td>
&nbsp;
<?php echo $fields_select;?> 
<input type="text" size="8" name="kw" value="<?php echo $kw;?>"/> 
<select name="bank" style="width:80px;">
<option value="">收款方式</option>
<?php
foreach($BANKS as $k=>$v) {
	echo '<option value="'.$v.'" '.($bank == $v ? 'selected' : '').'>'.$v.'</option>';
}
?>
</select>  
<?php echo $status_select;?> 
<?php echo dcalendar('fromtime', $fromtime);?> 至 <?php echo dcalendar('totime', $totime);?> 
<?php echo $order_select;?> 
<input type="text" name="psize" value="<?php echo $pagesize;?>" size="2" class="t_c" title="条/页"/>
<input type="submit" value="搜 索" class="btn"/> 
<input type="button" value="重 置" class="btn" onclick="window.location='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>';"/>
</td>
</tr>
</table>
</form>
<form method="post">
<div class="tt">提现记录</div>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<th width="25"><input type="checkbox" onclick="checkall(this.form);"/></th>
<th>流水号</th>
<th>金额</th>
<th>手续费</th>
<th>会员名称</th>
<th>收款方式</th>
<th width="130">申请时间</th>
<th width="130">受理时间</th>
<th>受理人</th>
<th>状态</th>
<th>管理</th>
</tr>
<?php foreach($cashs as $k=>$v) {?>
<tr onmouseover="this.className='on';" onmouseout="this.className='';" align="center" title="<?php echo $v['note'];?>">
<td><input type="checkbox" name="itemid[]" value="<?php echo $v['itemid'];?>"/></td>
<td><?php echo $v['itemid'];?></td>
<td class="f_red"><?php echo $v['amount'];?></td>
<td class="f_blue"><?php echo $v['fee'];?></td>
<td><a href="javascript:_user('<?php echo $v['username'];?>');"><?php echo $v['username'];?></a></td>
<td><?php echo $v['bank'];?></td>
<td><?php echo $v['addtime'];?></td>
<td><?php echo $v['edittime'];?></td>
<td><?php echo $v['editor'];?></td>
<td><?php echo $v['dstatus'];?></td>
<td>
<?php if($v['editor']) {?>
<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=show&itemid=<?php echo $v['itemid'];?>">查看</a>
<?php } else { ?>
<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=edit&itemid=<?php echo $v['itemid'];?>">受理</a>
<?php } ?>
</td>
</tr>
<?php }?>
<tr align="center">
<td></td>
<td><strong>小计</strong></td>
<td class="f_red"><?php echo $amount;?></td>
<td class="f_blue"><?php echo $fee;?></td>
<td colspan="7">&nbsp;</td>
</tr>
</table>
<div class="btns">
<input type="submit" value=" 批量删除 " class="btn" onclick="if(confirm('确定要删除选中记录吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete'}else{return false;}"/>
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
<input type="text" size="10" name="kw" value="<?php echo $kw;?>"/> 
<select name="bank" style="width:80px;">
<option value="">收款方式</option>
<?php
foreach($BANKS as $k=>$v) {
	echo '<option value="'.$v.'" '.($bank == $v ? 'selected' : '').'>'.$v.'</option>';
}
?>
</select>  
<?php echo $status_select;?> 
<?php echo dcalendar('dfromtime', $dfromtime);?> 至 <?php echo dcalendar('dtotime', $dtotime);?> 
<?php echo $order_select;?> 
<input type="submit" value="导出CSV" class="btn"/>
<input type="button" value="备份数据" class="btn" onclick="$('backup').submit();"/>
</td>
</tr>
</table>
</form>
<form action="?" id="backup">
<input type="hidden" name="file" value="database"/>
<input type="hidden" name="tables[]" value="<?php echo $DT_PRE;?>finance_cash"/>
<input type="hidden" name="sqlcompat" value=""/>
<input type="hidden" name="sqlcharset" value=""/>
<input type="hidden" name="sizelimit" value="2048"/>
<input type="hidden" name="backup" value="1"/>
</form>
<script type="text/javascript">Menuon(2);</script>
<br/>
</body>
</html>