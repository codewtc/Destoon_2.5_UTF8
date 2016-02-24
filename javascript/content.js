/*
	[Destoon B2B System] Copyright (c) 2008-2010 Destoon.COM
	This is NOT a freeware, use is subject to license.txt
*/
function fontZoom(z, id){
	var id = id ? id : 'content';
	var size = $(id).style.fontSize ? $(id).style.fontSize : '14px';
	var new_size = Number(size.replace('px', ''));
	new_size += z == '+' ? 1 : -1;
	if(new_size < 1) new_size = 1;
	$(id).style.fontSize=new_size+'px';
}
function ImgZoom(Id, m){
	var m = m ? m : 550;
	var w = Id.width;
	if(w < m){
		return;
	} else {
		var h = Id.height;
		Id.title = '点击打开原图';
		Id.onclick = function (e) {window.open(Id.src);}
		Id.height = parseInt(h*m/w);
		Id.width = m;
	}
}
window.onload = function() {
	var Imgs = $(content_id).getElementsByTagName("img");
	for(var i=0;i<Imgs.length;i++)	{
		ImgZoom(Imgs[i], img_max_width);
	}
	var Links = $(content_id).getElementsByTagName("a");
	for(var i=0;i<Links.length;i++)	{
		if(Links[i].target != '_blank') {
			if(document.domain && Links[i].href.indexOf(document.domain) == -1) Links[i].target = '_blank';
		}
	}
}