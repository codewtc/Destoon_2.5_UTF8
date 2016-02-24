<?php
defined('IN_DESTOON') or exit('Access Denied');
include tpl('header');
show_menu($menus);
?>
<form action="?">
<div class="tt">广告搜索</div>
<input type="hidden" name="moduleid" value="<?php echo $moduleid;?>"/>
<input type="hidden" name="file" value="<?php echo $file;?>"/>
<input type="hidden" name="action" value="<?php echo $action;?>"/>
<input type="hidden" name="job" value="<?php echo $job;?>"/>
<input type="hidden" name="pid" value="<?php echo $pid;?>"/>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<td>
&nbsp;<?php echo $fields_select;?>&nbsp;
<input type="text" size="40" name="kw" value="<?php echo $kw;?>" title="关键词"/>&nbsp;
<?php echo $order_select;?>&nbsp;
广告位ID： <input type="text" name="pid" value="<?php echo $pid;?>" size="2" class="t_c"/>&nbsp;
<input type="text" name="psize" value="<?php echo $pagesize;?>" size="2" class="t_c" title="条/页"/>
<input type="submit" value="搜 索" class="btn"/>&nbsp;
<input type="button" value="重 置" class="btn" onclick="window.location='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>&job=<?php echo $job;?>';"/>
</td>
</tr>
</table>
</form>
<form method="post">
<div class="tt">广告列表</div>
<table cellpadding="2" cellspacing="1" class="tb">
<tr>
<th width="25"><input type="checkbox" onclick="checkall(this.form);"/></th>
<th>ID</th>
<th>广告名称</th>
<th>广告类型</th>
<th>广告位</th>
<th>点击</th>
<th>开始时间<?php tips('如果两个广告时间设置冲突，系统将以开始时间为依据，优先显示开始时间晚的广告');?></th>
<th>结束时间</th>
<th>投放状态</th>
<th width="80">操作</th>
</tr>
<?php foreach($ads as $k=>$v) {?>
<tr onmouseover="this.className='on';" onmouseout="this.className='';" align="center">
<td><input type="checkbox" name="aids[]" value="<?php echo $v['aid'];?>"/></td>
<td><?php echo $v['aid'];?></td>
<td align="left" title="编辑:<?php echo $v['editor'];?>&#10;添加时间:<?php echo $v['adddate'];?>&#10;更新时间:<?php echo $v['editdate'];?>">&nbsp;<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=edit&aid=<?php echo $v['aid'];?>&pid=<?php echo $v['pid'];?>"><?php echo $v['title'];?></a></td>
<td><a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>&job=<?php echo $job;?>&typeid=<?php echo $v['typeid'];?>"><?php echo $TYPE[$v['typeid']];?></a></td>
<td><a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>&job=<?php echo $job;?>&pid=<?php echo $v['pid'];?>"><?php echo $P[$v['pid']]['name'];?></a></td>
<td><?php echo $v['hits'];?></td>
<td><?php echo $v['fromdate'];?></td>
<td><?php echo $v['todate'];?></td>
<td><?php echo $v['process'];?></td>
<td>
<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=view&aid=<?php echo $v['aid'];?>" target="_blank"/><img src="<?php echo IMG_PATH;?>view.png" width="16" height="16" title="预览此广告" alt=""></a>&nbsp;
<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=edit&aid=<?php echo $v['aid'];?>&pid=<?php echo $v['pid'];?>"><img src="<?php echo IMG_PATH;?>edit.png" width="16" height="16" title="修改" alt=""/></a>&nbsp;
<a href="?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete&aid=<?php echo $v['aid'];?>&pid=<?php echo $v['pid'];?>" onclick="return _delete();"><img src="<?php echo IMG_PATH;?>delete.png" width="16" height="16" title="删除" alt=""/></a>
</td>
</tr>
<?php }?>
</table>
<div class="btns">
<input type="submit" value=" 删 除 " class="btn" onclick="if(confirm('确定要删除选中广告吗？此操作将不可撤销')){this.form.action='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=delete&pid=<?php echo $pid;?>'}else{return false;}"/>&nbsp;
<?php if($pid) { ?>
<?php if($job == 'check') { ?>
<input type="button" value=" 广告列表 " class="btn" onclick="window.location='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>&job=&pid=<?php echo $pid;?>';"/>
<?php } else { ?>
<input type="button" value=" 审核广告 " class="btn" onclick="window.location='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=<?php echo $action;?>&job=check&pid=<?php echo $pid;?>';"/>
<?php } ?>
<input type="button" value=" 添加广告 " class="btn" onclick="window.location='?moduleid=<?php echo $moduleid;?>&file=<?php echo $file;?>&action=add&pid=<?php echo $pid;?>';"/>
<?php } ?>
</div>
</form>
<div class="pages"><?php echo $pages;?></div>
<br/>
<script type="text/javascript">Menuon(<?php echo $job == 'check' ? 3 : 2;?>);</script>
</body>
</html>