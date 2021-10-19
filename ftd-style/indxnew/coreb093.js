// using
//  http://closure-compiler.appspot.com/home
// for compression

var ld;									// fetched date at page loading
var gt;									// global timer handler
var mreq 		= true;
var elems 		= new Array();
var felems 	= new Array();
var _tt = null;
var _frame;
var _flag = 1;
var _vflag = false;

function _(id) {
	return document.getElementById( id );
}
function _tcls (elem, cls) {
	var exists = false;
	var newClass = '';
	var arr = elem.getAttribute("class").split(" ");
	for (var i = 0, count = arr.length; i < count; i++) {
		if (arr[i] != cls ) {
			if (newClass != "") {
				newClass += " ";
			}
			newClass += arr[i];
		} else {
			exists = true;
		}
	}

	elem.setAttribute("class", newClass + (exists?'': " "+cls) );
}
function _rcls (elem, cls) {
	var newClass = '';
	var arr = elem.getAttribute("class").split(" ");
	for (var i = 0, count = arr.length; i < count; i++) {
		if (arr[i] != cls ) {
			if (newClass != "") {
				newClass += " ";
			}
			newClass += arr[i];
		}
	}

	elem.setAttribute("class", newClass);
}



function Allmsg(){
	for(var x=0;x<document.msg.elements.length;x++){
		var y=document.msg.elements[x];if(y.name!='s10')y.checked=document.msg.s10.checked;
	}
} 

function NF(x){return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");}

function init() {
    ld = (new Date).getTime();
    for (var a = document.getElementsByTagName("input"), b = 0, c = a.length; b < c; b++) {
        var d = a[b];
        if (d.getAttribute("type") == "image" && d.className == "dynamic_img") d.onmouseover = function () {
            this.className = "dynamic_img over"
        }, d.onmouseout = function () {
            this.className = "dynamic_img"
        }, d.onmousedown = function () {
            this.className = "dynamic_img clicked"
        }
    }
    a = document.getElementsByTagName("table");
    b = 0;
    for (c = a.length; b < c; b++) if (d = a[b], d.hasAttribute("class") && d.getAttribute("class").indexOf("row_table_data") > -1) {
        trs = d.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
        for (var d = 0, e = trs.length; d < e; d++) trs[d].onmouseover = function () {
            this.setAttribute("class", this.getAttribute("class") + " hlight")
        }, trs[d].onmouseout = function () {
            _rcls(this, "hlight")
        }, trs[d].onmousedown = function () {
            _tcls(this, "marked")
        }
    }
    felems = [];
    for (b = 1; b < 5; b++) d = _("l" + b), d != null && (felems.push({
        e: d,
        r: parseFloat(d.getAttribute("title")),
        cv: parseInt(d.innerHTML),
        v: parseInt(d.innerHTML),
        x: d.getAttribute("id") == "l1" ? parseInt(document.getElementById('granary').innerHTML) : parseInt(document.getElementById('warehouse').innerHTML)
    }));
    elems = [];
    a = document.getElementsByTagName("span");
    b = 0;
    for (c = a.length; b < c; b++) d = a[b], d.getAttribute("id") != "timer1" && d.getAttribute("id") != "timer2" || (e = d.innerHTML.split(":"), isNaN(e[2]) || (e = new Number(e[0]) * 3600 + new Number(e[1]) * 60 + new Number(e[2]), elems.push({
        e: d,
        s: e,
        f: d.getAttribute("id") == "timer1" ? -1 : 1
    })));
    gt = window.setInterval(render, 100)
}

function render() {
    for (var a = parseInt(((new Date).getTime() - ld))/100, b = 0, c = felems.length; b < c; b++) {
        var d = felems[b],
            e = Math.floor(d.v + parseFloat(a/10 / 3600 * d.r));
        e > d.x && (e = d.x);
        d.cv = e;
        d.e.innerHTML = NF (e)
    }
    b = 0;
    for (c = elems.length; b < c; b++) {
        d = elems[b];
        e = d.s + a/10 * d.f;
        if (e < 0) {
            window.clearInterval(gt);
            document.location.reload();
            delete elems[b];
            break
        }
        var f = Math.floor(e % 3600 / 60),
            i = Math.floor(e % 60);
        d.e.innerHTML = Math.floor(e / 3600) + ":" + (f < 10 ? "0" : "") + f + ":" + (i < 10 ? "0" : "") + i
    }
}


function setLang (lng) {
	document.cookie =  'lng=' + lng + '; expires=Wed, 1 Jan 2250 00:00:00 GMT';
}
function toggleLevels() {
	var e1 = _("lswitch");
	var e2 = _("levels");
	var isOn = ( e1.className == "on" );
	e1.className = e2.className = isOn? "" : "on";

	document.cookie = (isOn? 'lvl=0' : 'lvl=1') + '; expires=Wed, 1 Jan 2250 00:00:00 GMT';
}

function showManual(b, c) {
    p = document.getElementById("ce");
    if (p != null) p.innerHTML = '<div id="_pwin" class="popup3"><div id="drag" onmousedown="dragStart(event, \'_pwin\')"></div><a href="#" onClick="hideManual(); return false;"><img src="assets/default/img/un/x.gif" border="1" class="popup4" alt="Move"></a><iframe frameborder="0" id="Frame" src="help?c=' + b + "&id=" + c + '" width="412" height="440" border="0"></iframe></div>';
    return false
}
function hideManual() {
	p = document.getElementById("ce");
	if (p!=null) {
		p.innerHTML = '';
	}
}

function showInfo(x, y) {
	_("x").innerHTML = _mp["mtx"][x][0][1];
	_("y").innerHTML = _mp["mtx"][x][y][2];
	var e = _mp["mtx"][x][y], p = e[5], o = e[6];
	_("map_infobox").setAttribute ( "class", (p?"village":"oasis_empty") );
	_("mbx_11").innerHTML = "-";
	_("mbx_12").innerHTML = "-";
	_("mbx_13").innerHTML = "-";
	if( p ) {
		_("mbx_1").innerHTML = o? textb.t3 : '<span class="tribe tribe'+ e[8] +'">' + e[11] + '</span>';
		_("mbx_11").innerHTML = e[10];
		_("mbx_12").innerHTML = o? '-' : e[9];
		_("mbx_13").innerHTML = e[12]!=''?e[12]:'-';
	} else {
		_("mbx_1").innerHTML = o? textb.t4 : textb.t2 + " " + textb.f[e[8]];
	}
}
function hideInfo() {
	_("x").innerHTML = _mp["x"];
	_("y").innerHTML = _mp["y"];
	_("map_infobox").setAttribute ( "class", "default" );
	_("mbx_1").innerHTML = textb.t1;
	_("mbx_11").innerHTML = "-";
	_("mbx_12").innerHTML = "-";
	_("mbx_13").innerHTML = "-";
}

function createRequestObject() {
	var http=null;
	try {
		http = new XMLHttpRequest();
	} catch (e) {
		try {
			http = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			http = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}

    return http;
}

function renderMap(obj, largeMap) {
	if ( !mreq ) {
		return false;
	}

	var xmlHttp=createRequestObject();

	var id  = obj.getAttribute("vid");
	var url = "karte?id=" + id + (largeMap? '&l' : '');
	if( xmlHttp == null ) {
		window.location = url;
		mreq = true;
		return false;
	}

	mreq = false;
	url += "&_a1_";
	xmlHttp.onreadystatechange = function() {
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") {
			mreq = true;
			if (xmlHttp.responseText.length > 0) {
				eval( xmlHttp.responseText );
				_("x").innerHTML= _mp["x"];
				_("y").innerHTML= _mp["y"];
				_("mcx").setAttribute ( "value", _mp["x"] );
				_("mcy").setAttribute ( "value", _mp["y"] );
				_("ma_n1").setAttribute ( "vid", _mp["n1"] );
				_("ma_n2").setAttribute ( "vid", _mp["n2"] );
				_("ma_n3").setAttribute ( "vid", _mp["n3"] );
				_("ma_n4").setAttribute ( "vid", _mp["n4"] );
				_("ma_n1p7").setAttribute ( "vid", _mp["n1p7"] );
				_("ma_n2p7").setAttribute ( "vid", _mp["n2p7"] );
				_("ma_n3p7").setAttribute ( "vid", _mp["n3p7"] );
				_("ma_n4p7").setAttribute ( "vid", _mp["n4p7"] );
				
				for (var i = 0, count =_mp["mtx"].length; i < count; i++) {
					var _mpa = _mp["mtx"][i];
					for (var j = 0, count1 = _mpa.length; j < count1; j++) {
						var _mpb = _mpa[j];
						_("i_" + i + "_" + j).setAttribute ( "class", _mpb[3] );
						_("i_" + i + "_" + j).innerHTML = ( _mpb[7] );
						
						var ea = _("a_" + i + "_" + j);
						ea.setAttribute ( "title", _mpb[4] );
						ea.setAttribute ( "href", "dorf3?id=" + _mpb[0] );
						
						if( i == 0 ) {
							_("my" + j).innerHTML = _mpb[2];
						}
						if( j == 0 ) {
							_("mx" + i).innerHTML = _mpb[1];
						}
					}
				}
			}
		}
	};

	xmlHttp.open("GET", url, true);
	xmlHttp.send(null);
	return false;
}

function slm () {
var url = "karte?l&id=" + _mp["mtx"][3][3][0];
window.location = url;
return false;
}

function add_res (id) { 
	set_res (id, _("r" + id).value + carry);
}
function upd_res (id, max) {
	set_res (id, max? merchNum * carry : isNaN (_("r" + id).value)? 0 : _("r" + id).value);
}
function set_res (id, v) {
    if (id == 1) { 
	if ( v > Res1) {
		v = Res1;
	}
    }
    if (id == 2) { 
	if ( v > Res2) {
		v = Res2;
	}
    }
    if (id == 3) { 
	if ( v > Res3) {
		v = Res3;
	}
    }
    if (id == 4) { 
	if ( v > Res4) {
		v = Res4;
	}
    }
	if (v > merchNum * carry) {
		v = merchNum * carry;
	}
	
	if (v == 0) {
		v = "";
	}
	_("r" + id).value = v;
}

// Determine browser and version.
 
function Browser() {
  var ua, s, i;
 
  this.isIE    = false;
  this.isNS    = false;
  this.version = null;
 
  ua = navigator.userAgent;
 
  s = "MSIE";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isIE = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }
 
  s = "Netscape6/";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }
 
  // Treat any other "Gecko" browser as NS 6.1.
  s = "Gecko";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = 6.1;
    return;
  }
}
 
var browser = new Browser();
 
// Global object to hold drag information.
var dragObj = new Object();
dragObj.zIndex = 0;
 
function dragStart(event, id) {
  var el;
  var x, y;

  // If an element id was given, find it. Otherwise use the element being clicked on.
  if (id)
    dragObj.elNode = document.getElementById(id);
  else {
    if (browser.isIE)
      dragObj.elNode = window.event.srcElement;
    if (browser.isNS)
      dragObj.elNode = event.target;
 
    // If this is a text node, use its parent element.
    if (dragObj.elNode.nodeType == 3)
      dragObj.elNode = dragObj.elNode.parentNode;
  }
 
  // Get cursor position with respect to the page.
  if (browser.isIE) {
    x = window.event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop + document.body.scrollTop;
  }
  if (browser.isNS) {
    x = event.clientX + window.scrollX;
    y = event.clientY + window.scrollY;
  }

  // Save starting positions of cursor and element.
  dragObj.cursorStartX = x;
  dragObj.cursorStartY = y;
  dragObj.elStartLeft  = parseInt(dragObj.elNode.style.left, 10);
  dragObj.elStartLeft  = parseInt(dragObj.elNode.style.right, 10);
  dragObj.elStartTop   = parseInt(dragObj.elNode.style.top,  10);

  if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = d3l;
  if (isNaN(dragObj.elStartTop))  dragObj.elStartTop  = d4l;

  // Update element's z-index.
  dragObj.elNode.style.zIndex = ++dragObj.zIndex;

  // Capture mousemove and mouseup events on the page.
  if (browser.isIE) {
    document.attachEvent("onmousemove", dragGo);
    document.attachEvent("onmouseup",   dragStop);
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS) {
    document.addEventListener("mousemove", dragGo,   true);
    document.addEventListener("mouseup",   dragStop, true);
    event.preventDefault();
  }
}
 
function dragGo(event) {
  var x, y;

  // Get cursor position with respect to the page.
  if (browser.isIE) {
	x = window.event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop + document.body.scrollTop;
  }
  if (browser.isNS) {
    x = event.clientX + window.scrollX;
    y = event.clientY + window.scrollY;
  }

  // Move drag element by the same amount the cursor has moved.
  dragObj.elNode.style.left = (dragObj.elStartLeft + x - dragObj.cursorStartX) + "px";
  dragObj.elNode.style.right = (dragObj.elStartLeft - x + dragObj.cursorStartX) + "px";
  dragObj.elNode.style.top  = (dragObj.elStartTop  + y - dragObj.cursorStartY) + "px";

  if (browser.isIE) {
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  }
  if (browser.isNS)
    event.preventDefault();
}
 
function dragStop(event) {
  // Stop capturing mousemove and mouseup events.
  if (browser.isIE) {
    document.detachEvent("onmousemove", dragGo);
    document.detachEvent("onmouseup",   dragStop);
  }
  if (browser.isNS) {
    document.removeEventListener("mousemove", dragGo,   true);
    document.removeEventListener("mouseup",   dragStop, true);
  }
}

function showTask () {
	if (_tt != null) { return; }

	var obj = _("anm");
	obj.style.visibility = "visible";
	if (_flag == 1) {
		_frame = {
			'right':0,
			'top':25,
			'width':118,
			'height':142
		};
	} else {
		var p = _("ce");
		if (p!=null) {
			p.innerHTML = '';
		}				
	}
	
	_tt = window.setInterval(renderTask, browser.isIE?5:10, new Date);
}

function renderTask () {
	var obj = _("anm");
	_frame.right 		-= 22*_flag;		if (_frame.right < -700) { _frame.right = -700; }		
	if(d3l > 0) {
		obj.style.right 	= _frame.right + "px";
	} else {
		obj.style.left 	= _frame.right + "px";
	}
	_frame.top 		-= 3*_flag;			if (_frame.top < -70) { _frame.top = -70; }				obj.style.top 		= _frame.top + "px";
	_frame.width		+= 10*_flag;		if (_frame.width > 430) { _frame.width = 430; }		obj.style.width 	= _frame.width + "px";
	_frame.height	+= 7*_flag;		if (_frame.height > 456) { _frame.height = 456; }	obj.style.height	= _frame.height + "px";
	
	if ((_frame.right == -700 && _frame.top == -70 &&  _frame.width == 430 && _frame.height == 456) || _frame.right>=25) {
		window.clearInterval(_tt);
		_flag *= -1;
		obj.style.visibility = "hidden";
		
		if (_flag == -1) {
			goto_guide();
		} else {
			if(_vflag) {
				goto_guide('f');
			} else {
				_tt = null;
			}
		}
	}
}
function goto_guide (value) {
	var p = _("ce");
	if (p!=null) {
		if(!_vflag) {
			p.innerHTML = '<div id="_pwin" class="popup3 quest"><div id="drag" onmousedown="dragStart(event, \'_pwin\')"></div><a href="#" onClick="showTask();return false;"><img src="assets/x.gif" border="1" class="popup4" alt="Move"></a><img src="assets/default/plus/loading.gif" width="48" height="48" alt="loading"></div>';
		}

		var xmlHttp=createRequestObject();
		xmlHttp.open('get', 'guide' + (value==undefined? '' : '?v=' + value));
		xmlHttp.onreadystatechange = function () {
			if (xmlHttp.readyState == 4) {
				if (xmlHttp.status == 200 && _flag == -1 && !_vflag) {
					if (xmlHttp.responseText != '') {
						p.innerHTML = '<div id="_pwin" class="popup3 quest"><div id="drag" onmousedown="dragStart(event, \'_pwin\')"></div><a href="#" onClick="showTask();return false;"><img src="assets/x.gif" border="1" class="popup4" alt="Move"></a>' + xmlHttp.responseText + '</div>';
						init();
					}

					var gquiz = xmlHttp.getResponseHeader("gquiz");
					if (gquiz == 1 || gquiz == 0) {
						hightlight_guide(gquiz==1);
					} else if (gquiz == 2) {
						var clsName = _('n5').className;
						var n = clsName[clsName.length-1];
						if (n == 4) { n = 2; } else if (n == 3) { n = 1; }
						_('n5').className = 'i' + n;
						
						hightlight_guide(false);
					} else if (gquiz == 100) {
						var t = _('qge'); if(t != null) { t.style.display = 'none'; }
					}
				}
				_vflag = false;
				_tt = null;
			}
		};
		xmlHttp.send(null);
	}
}
function hightlight_guide (hightlight) {
	var clsName = _('qgei').className;
	var isOn = clsName[clsName.length-1] == 'g';
	if (hightlight) {
		if (!isOn) {
			_('qgei').className += 'g';
		}
	} else {
		if (isOn) {
			_('qgei').className = clsName.substring(0, clsName.length-1);
		}
	}
}
function free_guide () {
	_vflag = true;
	showTask();
}


function PopupMap(i){
	pb=document.getElementById("ce");
    if(pb!=null){
    	var iframeHeight = 575, iframeWidth  = 624;
	    var tc='<div class="popup_map">'+'<div id="drag2">'+'<a href="#" style="position: absolute;right: ' + Math.round(((documentWidth() - iframeWidth) / 2 )+iframeWidth-30) + 'px; top: ' + Math.round(((documentHeight() - iframeHeight) / 2 ) + 10) + 'px;z-index:1001;" id="map_popclose" onclick="Close(); return false;"><img src="assets/x.gif" border="0" width="20px" height="20px"></a><iframe frameborder="0" id="Frame" src="wingold.php" style="position: absolute; width: '+iframeWidth+'px; height: '+iframeHeight+'px; right: ' + Math.round((documentWidth() - iframeWidth) / 2) + 'px;  top: ' + Math.round((documentHeight() - iframeHeight) / 2) + 'px" border="0" scrolling="no"></iframe>'+'</div></div>';
		pb.innerHTML=tc;
	}
}
function Close(){pb=document.getElementById("ce");if(pb!=null){pb.innerHTML='';}}
function documentWidth() {
    return Math.max(
        document.documentElement.clientWidth,
        document.body.scrollWidth,
        document.documentElement.scrollWidth,
        document.body.offsetWidth,
        document.documentElement.offsetWidth
    );
}

function documentHeight() {
    return Math.max(
        document.documentElement.clientHeight,
        document.body.offsetHeight,
        document.documentElement.offsetHeight
    );
}

function GetPings(){var X = performance;
    var NTi = Date.now();
    var Time_S = X.timing.navigationStart;
    var dT = NTi-Time_S;
    var dt = NTi-TIMER_START;
    var dP = X.timing.responseStart  - X.timing.requestStart;
    var Stall = dT-dP-dt;
    return [dP,dt,dT,Stall];
}
