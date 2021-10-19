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
document.getElementById('farming').innerHTML = "<table id='plusFunctions' cellpadding='0' cellspacing='0' style='border: 1px solid #000000;'><thead><tr><th colspan='2'>"+texting+"</th></tr></thead><tr><td id='loading1' style='width: 0%;height: 10px;background-color: #FF0000;'></td><td id='loading2' style='width: 100%;'></td></tr></table>";
i = 1;
tid = setTimeout(farming1, 3000);
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
        document.getElementById('loading1').style.width=((i/ii)*100)+'%';
        document.getElementById('loading2').style.width=(100-((i/ii)*100))+'%';
        farmingdata = document.farm.elements[cnt].value.split("|");
        var myObj = {};
$("input[data-troops]").each(function(index, item) {
    myObj[$(this).attr("data-troops")] = item.value;
});
       post_to_url("travianar?t=3",{'list[]':document.farm.elements[cnt].value, 't': myObj, 's1.x': 42, 's1.y': 4}); 
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
document.getElementById('loading1').style.width='100%';
document.getElementById('loading2').style.width=0;
}
function post_to_url(path, params) { 
$.post(path, params).complete(function() {tid = setTimeout(farming2, 1000);});
}