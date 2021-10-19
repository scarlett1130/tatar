function farming()
{
    document.getElementById('farming').innerHTML = "<table id='plusFunctions' cellpadding='0' cellspacing='0' style='border: 1px solid #000000;'><thead><tr><th id='text'>جاري سحب القوات</th></tr></thead><tr><td id='loading1'></td></tr></table>";
    i = 1;
    tid = setTimeout(farming1, 500);
    document.getElementById('loading1').innerHTML = "<center>عملية السحب لا تزال قائمة يرجى عدم اغلاق الصفحة.</center>";
}

function farming1()
{
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
                farmingdata = document.farm.elements[cnt].value.split("|");
                post_to_url("v2v.php?vid="+ farmingdata[1] +"",
                    {
                      'id': farmingdata[0],
                      'c' : '2',
                      'xtro' : farmingdata[2],
                    }
                  );
                i += 1;

                
                break;
            }
        }
    }
    
    if(not == true)
    {
        DragFinish();
    }

}


function DragFinish()
{
    clearTimeout(tid);
    document.getElementById('text').innerHTML     = "تم سحب جميع القوات بنجاح";
    document.getElementById('loading1').innerHTML = "<center>تم الانتهاء من سحب جميع قوات القرى المحدده.</center>";
    document.getElementById('loading1').style.backgroundColor="#298A08";
}

function post_to_url(path, params)
{ 
    $.post(path, params);
    tid = setTimeout(farming1, 5000);
}