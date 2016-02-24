/*
	[Destoon B2B System] Copyright (c) 2008-2010 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
document.write('<div style="width:'+EDW+';color:#666666;">');
document.write('<div style="float:right;padding:2px 5px 0 0;cursor:pointer;">');
document.write('<img src="'+SKPath+'image/fck_zo.gif" title="缩小高度" onclick="fck_zo();"/> ');
document.write('<img src="'+SKPath+'image/fck_zi.gif" title="增加高度" onclick="fck_zi();"/> ');
document.write('</div>');
document.write('&nbsp;');
document.write('<a href="javascript:" onclick="fck_get_data();" class="t">数据恢复</a>');
document.write('&nbsp;|&nbsp;');
document.write('<a href="javascript:" onclick="fck_save_draft();" class="t">暂存草稿</a>');
document.write('&nbsp;|&nbsp;<span id="fck_switch"></span>&nbsp;&nbsp;<span id="fck_data_msg"></span>');
document.write('</div>');
function fck_zi() {
	var h = Number($('content___Frame').height.replace('px', ''));
	h = h + 150;
	$('content___Frame').height = h+'px';
}
function fck_zo() {
	var h = Number($('content___Frame').height.replace('px', ''));
	h = h - 150;
	if(h > 200) $('content___Frame').height = h+'px';
}
function fck_content() {
	return FCKeditorAPI.GetInstance('content').GetXHTML(true) ;
}
function fck_get_data() {
	makeRequest('action=get_data&mid='+ModuleID, AJPath, '_fck_get_data');
}
function _fck_get_data() {    
	if(xmlHttp.readyState==4 && xmlHttp.status==200) {
		if(xmlHttp.responseText) {
			if(confirm('发现 '+xmlHttp.responseText.substring(0, 19)+' 保存的数据，是否覆盖当前数据？')) {
				FCKeditorAPI.GetInstance('content').SetData(xmlHttp.responseText.substring(19, xmlHttp.responseText.length));
			}
		} else {
			alert('抱歉 未找到保存的数据');
		}
	}
}
var fck_c = '';
function fck_save_data() {
	var l = FCKLen('content');
	if(l < 10) return;
	var c = fck_content();
	if(fck_c == c) return;
	makeRequest('action=save_data&mid='+ModuleID+'&content='+encodeURIComponent(c), AJPath);
	fck_c = c;
	var today = new Date();
	$('fck_data_msg').innerHTML = '<img src="'+SKPath+'image/clock.gif"/>'+today.getHours()+'点'+today.getMinutes()+'分'+today.getSeconds()+'秒 系统自动保存了草稿';
}
function fck_save_draft() {
	var l = FCKLen('content');
	if(l < 10) {alert('请至少输入10个字，当前已输入'+l+'字');return;}
	if(confirm('系统会自动保存草稿，此操作将中止系统自动保存功能，确定要继续吗？')) {
		fck_stop();
		makeRequest('action=save_data&mid='+ModuleID+'&content='+encodeURIComponent(fck_content()), AJPath);
		$('fck_data_msg').innerHTML = '草稿已保存';
		window.setTimeout(function(){$('fck_data_msg').innerHTML = '';}, 3000);
	}
}
var fck_interval;
function fck_init() {
	fck_interval = setInterval('fck_save_data()', 10000);
	$('fck_data_msg').innerHTML = '';
	$('fck_switch').innerHTML = '<a href="javascript:" class="t" onclick="fck_stop();">关闭保存</a>';
}
function fck_stop() {
	clearInterval(fck_interval);
	$('fck_data_msg').innerHTML = '草稿保存已停止';
	$('fck_switch').innerHTML = '<a href="javascript:" class="t" onclick="fck_init();">开启保存</a>';
}
fck_init();