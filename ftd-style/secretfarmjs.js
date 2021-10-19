var tid = null;
var i = null;
var ii = null;
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
document.getElementById('farming').innerHTML = '<table class=\"rate_details lang_rtl lang_ar\" cellpadding=\"3\" cellspacing=\"1\"><thead><tr><th colspan=\"2\" id=\"texting\" >يتم الان الهجوم علي المزارع</th></tr></thead><tbody><tr><td class=\"pic\"><div id=\"loading\" style=\"width: 0%; height: 10px; background-color: rgb(255, 0, 0);\" ></div></td></tr></tbody></table>';
i = 1;
tid = setTimeout(farming1, 1);
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

        if(farmingdata[3] == 1) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[1]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 2) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[2]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 3) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[3]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 4) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[4]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 5) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[5]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 6) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[6]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 11) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[11]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 12) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[12]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 13) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[13]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 14) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[14]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 15) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[15]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 16) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[16]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 21) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[21]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 22) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[22]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 23) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[23]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 24) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[24]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 25) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[25]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 26) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[26]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 51) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[51]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 52) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[52]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 53) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[53]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 54) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[54]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 55) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[55]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 56) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[56]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 61) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[61]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 62) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[62]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 63) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[63]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 64) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[64]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 65) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[65]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 66) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[66]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 71) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[71]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 72) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[72]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 73) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[73]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 74) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[74]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 75) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[75]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 76) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[76]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 100) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[100]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 101) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[101]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 102) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[102]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 103) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[103]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 104) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[104]':farmingdata[4],'farm':'1'}); };
        if(farmingdata[3] == 105) { post_to_url("v2v.php",{'id':farmingdata[0],'c':'4','t[105]':farmingdata[4],'farm':'1'}); };
                i += 1;
        // tid = setTimeout(farming2, 1000);
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

var loading = "تم الهجوم";
var texting = "تم الهجوم";
document.getElementById('texting').innerHTML = ""+texting+"";

}
function post_to_url(path, params) {
$.post(path, params).complete(function() {tid = setTimeout(farming2, 1000);});
}