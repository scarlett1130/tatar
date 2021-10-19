function coach()
{
    document.getElementById('coach').innerHTML = "<table id='plusFunctions' cellpadding='0' cellspacing='0' style='border: 1px solid #000000;'><thead><tr><th id='text'>جاري تدريب القوات</th></tr></thead><tr><td id='loading1'></td></tr></table>";
    i = 1;
    tid = setTimeout(coach1, 500);
    document.getElementById('loading1').innerHTML = "<center>عملية التدريب لا تزال قائمة يرجى عدم اغلاق الصفحة.</center>";
}

function coach1()
{
    var troop = null;
    var e= document.xcoach.elements.length;
    var cnt=0;
    var not=true;
    var farmingdata = null;
    for(cnt=0;cnt<e;cnt++)
    {
        if(document.xcoach.elements[cnt].name=="list[]")
        {
            if(document.xcoach.elements[cnt].checked)
            {
                not = false;
                document.xcoach.elements[cnt].checked = false;
                farmingdata = document.xcoach.elements[cnt].value.split("|");
                post_to_url("build.php?bid="+ farmingdata[1] +"&vid="+ farmingdata[0] +"",
                    {
                      'tcoach' : farmingdata[2],
                      'coach'  : 1,
                    }
                  );
                i += 1;

                
                break;
            }
        }
    }
    
    if(not == true)
    {
        Finish();
    }

}


function Finish()
{
    clearTimeout(tid);
    document.getElementById('text').innerHTML     = "تم سحب جميع الموارد بنجاح";
    document.getElementById('loading1').innerHTML = "<center>تم الانتهاء من سحب جميع موارد القرى المحدده.</center>";
    document.getElementById('loading1').style.backgroundColor="#298A08";
}

function post_to_url(path, params)
{ 
    $.post(path, params);
    tid = setTimeout(coach1, 5000);
}