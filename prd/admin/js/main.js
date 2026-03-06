
// 文字の入れ替え
function str_swap(frm, id1, id2, id_name, max) {
	var elem_id1 = -1;
	var elem_id2 = -1;
	var cnt = 0;
	var int_id1 = parseInt(id1);
	var int_id2 = parseInt(id2);
	if (int_id2 < 0) {
		return false;
	}
	if (max != '' && int_id2 > max) {
		return false;
	}
	for (i = 0; i < frm.elements.length; i++) {
		if (frm.elements[i].name == id_name) {
			if (cnt == int_id1 && elem_id1 < 0) {
				elem_id1 = i;
			}
			else if(cnt == int_id2 && elem_id2 < 0) {
				elem_id2 = i;
			}
			if (elem_id1 >= 0 && elem_id2 >= 0) {
				break;
			}
			cnt++;
		}
	}
	if (elem_id1 < 0 || elem_id2 < 0) {
		return false;
	}
	swap2(frm, elem_id1, elem_id2);
	return true;
}


// 位置の入れ替え
function swap2(frm, id, id2) {
  var buf;
  buf = frm.elements[id].value;
  frm.elements[id].value = frm.elements[id2].value;
  frm.elements[id2].value = buf;
  return true;
}

// 項目変更
function change_sub(frm, id, mode) {
	frm.mode.value = mode;
	frm.sub_id.value = id;
	frm.submit();
}

// 表示／非表示切替
function disp_on(id_name) {
	id = document.getElementById(id_name);
	if (navigator.userAgent && navigator.userAgent.indexOf("Gecko/") != -1) {
		id.style.display = "table-row";
	}
	else {
		id.style.display = '';
	}
}

function disp_off(id_name) {
	id = document.getElementById(id_name);
	id.style.display = "none";
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

