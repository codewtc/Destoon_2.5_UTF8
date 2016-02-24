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
<?php echo $fields_select;?> 
<input type="text" size="6" name="kw" value="<?php echo $kw;?>"/> 
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
<div class="tt">交易记录</div>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<th width="25"><input type="checkbox" onclick="checkall(this.form);"/></th>
<th>流水号</th>
<th>商品或服务</th>
<th>交易总额</th>
<th>卖家</th>
<th>买家</th>
<th width="75">下单时间</th>
<th width="75">更新时间</th>
<th>状态</th>
<th>操作</th>
</tr>
<?php foreach($trades as $k=>$v) {?>
<tr onmouseover="this.className='on';" onmouseout="this.className='';" align="center">
<td><input type="checkbox" name="itemid[]" value="<?php echo $v['itemid'];?>"/></td>
<td class="px11"><?php echo $v['itemid'];?></td>
<td align="left">
<?php if($v['linkurl']) {?>
<a href="<?php echo $v['linkurl'];?>" target="_blank" class="t">
<?php } ?>
<?php echo $v['title'];?>
<?php if($v['linkurl']) {?>
</a>
<?php } ?>
</td>
<td class="f_red px11"><?php echo $v['money'];?></td>
<td class="px11"><a href="javascript:_user('<?php echo $v['seller'];?>');"><?php echo $v['seller'];?></a></td>
<td class="px11"><a href="javascript:_user('<?php echo $v['buyer'];?>');"><?php echo $v['buyer'];?></a></td>
<td class="px11"><?php echo $v['addtime'];?></td>
<td class="px11"><?php echo $v['updatetime'];?></td>
<td><?php echo $v['dstatus'];?></td>
<td>
<?php if($v['status'] == 5) {?>
<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=refund&itemid=<?php echo $v['itemid'];?>">受理</a>
<?php } else { ?>
<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=show&itemid=<?php echo $v['itemid'];?>">查看</a>
<?php } ?>
</td>
</tr>
<?php }?>
<tr align="center">
<td></td>
<td><strong>小计</strong></td>
<td></td>
<td class="f_red f_b"><?php echo $money;?></td>
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
<?php echo $fields_select;?> 
<input type="text" size="10" name="kw" value="<?php echo $kw;?>"/> 
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
<input type="hidden" name="tables[]" value="<?php echo $DT_PRE;?>finance_trade"/>
<input type="hidden" name="sqlcompat" value=""/>
<input type="hidden" name="sqlcharset" value=""/>
<input type="hidden" name="sizelimit" value="2048"/>
<input type="hidden" name="backup" value="1"/>
</form>
<script type="text/javascript">Menuon(1);</script>
<br/>
</body>
</html>