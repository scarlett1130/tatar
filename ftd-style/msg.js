var timer=new Object();var ab=new Object();var bb=new Object();var cb=db();var eb=0;var auto_reload=1;var fb=new Object();var	
is_opera=window.opera!==undefined;var	is_ie=document.all!==undefined&&window.opera===undefined;var is_ie6p=document.compatMode!==undefined&&document.all!==undefined&&window.opera===undefined;var is_ie7=document.documentElement!==undefined&&document.documentElement.style.maxHeight!==undefined;var is_ie6=is_ie6p&&!is_ie7;var is_ff2p=window.Iterator!==undefined;var is_ff3p=document.getElementsByClassName!==undefined;var is_ff2=is_ff2p&&!is_ff3p
function Allmsg(){for(var x=0;x<document.msg.elements.length;x++){var y=document.msg.elements[x];if(y.name!='s10')y.checked=document.msg.s10.checked;}
}
function xy(){yc=screen.width+":"+screen.height;document.snd.w.value=yc;}
function my_village(){var zc=Math.round(0);var $c;var e=document.snd.dname.value;for(var i=0;i<dorfnamen.length;i++){if(dorfnamen[i].indexOf(e)>-1){zc++;$c=dorfnamen[i];}
}
if(zc==1){document.snd.dname.value=$c;}
}
var _c=document.getElementById?1:0;var ad=document.all?1:0;var bd=(navigator.userAgent.indexOf("Mac")>-1)?1:0;var cd=(ad&&(!bd)&&(typeof(window.offscreenBuffering)!='undefined'))?1:0;var dd=cd;var ed=cd&&(window.navigator.userAgent.indexOf("SV1")!=-1);function changeOpacity(fd,opacity){if(cd){fd.style.filter='progid:DXImageTransform.Microsoft.Alpha(opacity='+(opacity*100)+')';}
else if(_c){fd.style.MozOpacity=opacity;}
}
function gd(url,hd,id,jd){if(id===undefined){id='GET';}
var kd;if(window.XMLHttpRequest){kd=new XMLHttpRequest();}
else if(window.ActiveXObject){try{kd=new ActiveXObject("Msxml2.XMLHTTP");}
catch(e){try{kd=new ActiveXObject("Microsoft.XMLHTTP");}
catch(e){}
}
}
else{throw'Can not create XMLHTTP-instance';}
kd.onreadystatechange=function(){if(kd.readyState==4){if(kd.status==200){var ld=kd.getResponseHeader('Content-Type');ld=ld.substr(0,ld.indexOf(';'));switch(ld){case'application/json':hd((kd.responseText==''?null:eval('('+kd.responseText+')')));break;case'text/plain':case'text/html':hd(kd.responseText);break;default:throw'Illegal content type';}
}
else{throw'An error has occurred during request';}
}
}
;kd.open(id,url,true);if(id=='POST'){kd.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=utf-8');var md=nd(jd);}
else{var md=null;}
kd.send(md);}
function nd(od){var pd='';var qd=true;for(var rd in od){pd+=(qd?'':'&')+rd+'='+window.encodeURI(od[rd]);if(qd){qd=false;}
}
return pd;}
function mreload(){param='reload=auto';url=window.location.href;if(url.indexOf(param)==-1){if(url.indexOf('?')==-1){url+='?'+param;}
else
{url+='&'+param;}
}

return false;}
function xe(){if(ge){window.close();}
else{mdim={'x':7,'y':7,'rad':3}
;var of=[];for(var i=0;i<mdim.x;i++){of[i]=[];for(var j=0;j<mdim.y;j++){of[i][j]=qe(i+3,j+3,'a').details;}
}
ce.removeChild(de);ce.appendChild(be);map_init();var pf;var area;for(var i=0;i<mdim.x;i++){for(var j=0;j<mdim.y;j++){area=qe(i,j,'a');pf=qe(i,j,'i');area.details=of[i][j];area.details.fresh={}
;pf.className=of[i][j].img;qf(area,pf);}
}
jf(m_c.z);lf(m_c.z);}
return false;}
function ke(){var rf=1*this.id.substring(4,5);var sf=1*(this.id.substring(5,7)=='p7'?mdim.x:1);map_scroll(rf,sf);return false;}
function tf(z){var x=z.x-mdim.rad;var y=z.y-mdim.rad;var uf=z.x+mdim.rad;var vf=z.y+mdim.rad;return{'x':x,'y':y,'xx':uf,'yy':vf}
;}
function wf(rf,sf,xf){if(xf==null){xf=0;}
if(m_c.size==null){throw'Globale Variable m_c.size muss auf den Wert von $travian[map_prefetch_rows]) gesetzt werden.';}
var yf,zf;if(null===sf||1===sf){zf=m_c.size-1;}
else if(mdim.x==sf){yf=mdim.x;zf=-(mdim.x-1);}
else{throw'Parameter steps muss 1 oder Breite der Karte in Feldern sein.';}
var x,y,uf,vf,z;var z=m_c.z;switch(rf){case 1:x=z.x+mdim.rad;y=z.y+mdim.rad+xf;uf=z.x-mdim.rad;vf=y+zf;break;case 2:x=z.x+mdim.rad+xf;y=z.y-mdim.rad;uf=x+zf;vf=z.y+mdim.rad;break;case 3:x=z.x+mdim.rad;y=z.y-mdim.rad-xf;uf=z.x-mdim.rad;vf=y-zf;break;case 4:x=z.x-mdim.rad-xf;y=z.y-mdim.rad;uf=x-zf;vf=z.y+mdim.rad;break;}
return{'x':x,'y':y,'xx':uf,'yy':vf}
;}
function $f(_f){if(_f>400){_f-=801;}
if(_f<-400){_f+=801;}
return _f;}
function ag(_f){if(_f>400){_f=400;}
if(_f<-400){_f=-400;}
return _f;}





function bg(rf,sf){var z={}
;z.x=m_c.z.x*1;z.y=m_c.z.y*1;switch(rf){case 1:z.y+=sf;break;case 2:z.x+=sf;break;case 3:z.y-=sf;break;case 4:z.x-=sf;break;}
m_c.z.x=$f(z.x);m_c.z.y=$f(z.y);}
function cg(dg){return'ajax.php?f=k7&x='+dg.x+'&y='+dg.y+'&xx='+dg.xx+'&yy='+dg.yy;}
function map_scroll(rf,sf,eg){var dg,fg;if(td){return false;}
if(gg()){if(ud){return false;}
td=true;hg();m_c.usealternate=false;m_c.cindex=0;if(eg!==undefined){m_c.z.x=ag(eg.x);m_c.z.y=ag(eg.y);dg=tf(m_c.z);}
else{bg(rf,sf);dg=wf(rf,sf);}
ig=cg(dg);gd(ig,jg);}
else{if(kg()){if(ud){return false;}
ud=true;bg(rf,sf);dg=wf(rf,sf,2);ig=cg(dg);gd(ig,jg);}
else if(lg()){bg(rf,sf);mg();hg();}
else{bg(rf,sf);}
ng(rf,sf);}
function jg(og){var pg;if(kg()){pg=qg(m_c.cindex);m_c.usealternate=false;ud=false;}
else{pg=m_c.cindex;}
m_c.fields[pg]=og;if(gg()){if(eg!==undefined){ng(0,0,m_c.z);rg('x');rg('y');}
else{ng(rf,sf);rg(rf);}
td=false;}
}
function kg(){return m_c.usealternate;}
function gg(){return(rf!=m_c.dir||sf==mdim.x||(sf==1&&sf!=m_c.steps)||eg!==undefined);}
function lg(){return(m_c.index==m_c.size);}
}
function sg(rf,sf){m_c.dir=rf;m_c.steps=sf;}
function hg(){m_c.index=0;}
function tg(){m_c.index++;if(m_c.index==m_c.size-2){m_c.usealternate=true;}
}
function mg(){m_c.cindex=qg(m_c.cindex);}
function ng(rf,sf,eg){var ug=document.getElementById('map_content');var vg=ug.parentNode;if(1==sf){wg(rf);xg(m_c.fields[m_c.cindex],rf,sf);rg(rf);tg();}
else if(mdim.x==sf||eg!==undefined){yg(m_c.fields[m_c.cindex]);}
if(xd==0){lf(m_c.z);}
jf(m_c.z);sg(rf,sf);}
function qg(pg){return(pg==0?1:0);}
function yg(og){for(var i=0;i<mdim.x;i++){for(var j=0;j<mdim.y;j++){zg(i,j,og[i][j]);}
}
}
function $g(_g,ah){ah.details.href=_g;}
function zg(bh,ch,af){var pf=qe(bh,ch,'i');var area=qe(bh,ch,'a');ue(af,area);pf.className=area.details.img;qf(area,pf);}
function qf(area,pf){if(area.details.atyp){if(!pf.firstChild){pf.appendChild(document.createElement('span'));}
pf.firstChild.className='m'+area.details.atyp;}
else{if(pf.firstChild){pf.removeChild(pf.firstChild);}
}
}