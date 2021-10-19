var tid = null;
var i = null;
var ii = null;
var timer = 0;
function CheckAll(checked) {
  var e= document.farm.elements.length;
  var cnt=0;
  for(cnt=0;cnt<e;cnt++)
  {
    if(document.farm.elements[cnt].name=="list[]")
    {
     document.farm.elements[cnt].checked=checked;
    }
  }
}
function farming() {
document.getElementById('farming').innerHTML = '<table class=\"rate_details lang_rtl lang_ar\" cellpadding=\"3\" cellspacing=\"1\"><thead><tr><th colspan=\"2\" id=\"texting\" >الرجاء الانتظار بينما يتم الاضافه</th></tr></thead><tbody><tr><td class=\"pic\"><div id=\"loading\" style=\"width: 0%; height: 10px; background-color: rgb(255, 0, 0);\" ></div></td></tr></tbody></table>';
i = 1;
tid = setTimeout(farming1, 1000);
}
function farming1() {
  var e= document.farm.elements.length;
  var cnt=0;
  var not=true;
  var farmingdata = null;
  ii = 0;
  for(cnt=0;cnt<e;cnt++)
  {
    if(document.farm.elements[cnt].name=="list[]")
    {
     if(document.farm.elements[cnt].checked)
     {
        ii += 1
     }
    }
  }
farming2();
}
function farming2() {
  var troop = null;
  var e= document.farm.elements.length;
  var cnt=0;
  var not=true;
  var farmingdata = null;
  for(cnt=0;cnt<e;cnt++)
  {
    if(document.farm.elements[cnt].name=="list[]")
    {
     if(document.farm.elements[cnt].checked)
     {
        not = false;
        document.farm.elements[cnt].checked = false;
        document.getElementById('a'+cnt).style.backgroundColor="#FFFF5F";
        document.getElementById('b'+cnt).style.backgroundColor="#FFFF5F";
        document.getElementById('c'+cnt).style.backgroundColor="#FFFF5F";
        document.getElementById('d'+cnt).style.backgroundColor="#FFFF5F";
        document.getElementById('e'+cnt).style.backgroundColor="#FFFF5F";
		document.getElementById('loading').style.width=((i/ii)*100)+'%';
        farmingdata = document.farm.elements[cnt].value.split("|");
        if(document.getElementById("trooptype").value == 1) { get_to_url("farm.php",{'x':farmingdata[0],'y':farmingdata[1],'t1':'1','t2':document.getElementById("t2").value,'farm':'1'}); };
        if(document.getElementById("trooptype").value == 2) { get_to_url("farm.php",{'x':farmingdata[0],'y':farmingdata[1],'t1':'2','t2':document.getElementById("t2").value,'farm':'1'}); };
        if(document.getElementById("trooptype").value == 3) { get_to_url("farm.php",{'x':farmingdata[0],'y':farmingdata[1],'t1':'3','t2':document.getElementById("t2").value,'farm':'1'}); };
        if(document.getElementById("trooptype").value == 4) { get_to_url("farm.php",{'x':farmingdata[0],'y':farmingdata[1],'t1':'4','t2':document.getElementById("t2").value,'farm':'1'}); };
        if(document.getElementById("trooptype").value == 5) { get_to_url("farm.php",{'x':farmingdata[0],'y':farmingdata[1],'t1':'5','t2':document.getElementById("t2").value,'farm':'1'}); };
        if(document.getElementById("trooptype").value == 6) { get_to_url("farm.php",{'x':farmingdata[0],'y':farmingdata[1],'t1':'6','t2':document.getElementById("t2").value,'farm':'1'}); };
        i += 1;
        //tid = setTimeout(farming2, 500);
        break;
     }
    }
  }
  if(not==true) {
  farming3();
  }
}
function farming3() {
clearTimeout(tid);
var texting = "تم اضافة جميع المزارع بنجاح";
document.getElementById('texting').innerHTML = ""+texting+"";
}
function get_to_url(path, params) { 
$.get(path, params).complete(function() {tid = setTimeout(farming2, 1000);});
}