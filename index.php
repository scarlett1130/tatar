<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?
  function TimeAgo($diff_in_unix){
  if ($diff_in_unix > 3600){
  $diff .= intval($diff_in_unix/3600); 
  $diff_in_unix = $diff_in_unix%3600;
  }else{ $diff .= '00'; }
  if($diff_in_unix > 60 AND $diff_in_unix < 3600){
  $diff .= ":".intval($diff_in_unix / 60);
  $diff_in_unix = $diff_in_unix%60;
  }else{ $diff .= ':00'; }
  if ($diff_in_unix < 60 AND $diff_in_unix > 0){
  $diff .= ":".$diff_in_unix;
  }
  return $diff;
  }
$x = 0;
require_once( "ftd-core/smartservs-conf-ftd/s1.php" );
$link = mysql_connect($AppConfig['db']['host'],$AppConfig['db']['user'],$AppConfig['db']['password']) or die(mysql_error());
mysql_select_db($AppConfig['db']['database'],$link) or die(mysql_error());
$q = mysql_query ("SELECT * FROM g_summary");
$sessionTimeoutInSeconds = 9000 * 60;
$g = mysql_query ("SELECT COUNT(*) FROM p_players WHERE TIME_TO_SEC(TIMEDIFF(NOW(), last_login_date)) <= ".$sessionTimeoutInSeconds."");
$g = mysql_fetch_row ($g);
$r = mysql_fetch_assoc ($q);
$online = floor((TimeAgo(time() - strtotime(date($AppConfig['system']['server_start'] )))/24));
$online_before = floor((TimeAgo(strtotime($AppConfig['system']['server_start']) - time())/24));
$players_count1 = $r["players_count"];
$active_players_count1 = $r['active_players_count'];
$online_players_count1 = $g[0];    
$x +=1;

?>
<html>
<head>
<script src="ftd-style/Index-TCC/default/lang/right/Jq.js" type="text/javascript"></script>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta name="description" content="حرب التتار السريع واحدة من أنجح ألعاب المتصفحات في العالم. في حرب التتار السريع الرسمي ، تؤسس امبراطوريتك، توسّعها وتدّرب جيشاً لحمايتها، في النهاية، تتساعد مع لاعبين آخرين لتنافسوا على بناء أعجوبة العالم." />
<meta name="keywords" lang="ar" content="حرب التتار السريع الرسمي واحدة من أنجح ألعاب المتصفحات في العالم. في حرب التتار السريع الرسمي ، تؤسس امبراطوريتك، توسّعها وتدّرب جيشاً لحمايتها، في النهاية، تتساعد مع لاعبين آخرين لتنافسوا على بناء أعجوبة العالم." />
<link rel="shortcut icon" href="ftd-style/Index-TCC/favicon.ico" type="image/x-icon" />
<script type="text/javascript">var d3l=180</script>
<title>حرب التتار</title>
<script type="text/javascript">eval(function(p,a,c,k,e,d){e=function(c){return c.toString(36)};if(!''.replace(/^/,String)){while(c--){d[c.toString(a)]=k[c]||c.toString(a)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('(3(){(3 a(){8{(3 b(2){7((\'\'+(2/2)).6!==1||2%5===0){(3(){}).9(\'4\')()}c{4}b(++2)})(0)}d(e){g(a,f)}})()})();',17,17,'||i|function|debugger|20|length|if|try|constructor|||else|catch||300|setTimeout'.split('|'),0,{}))</script>
<script type="text/javascript">var W=[],Q=[],A=[];</script>
<link rel="stylesheet" type="text/css" href="ftd-style/Index-TCC/default/lang/right/compact.css">
<link rel="stylesheet" type="text/css" href="ftd-style/Index-TCC/default/lang/right/lang.css">
<link href="ftd-style/Index-TCC/indx/css/main_arr.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ftd-style/Index-TCC/images/Cs.css?Ma">
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="cache-control" content="max-age=0">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">
<meta http-equiv="imagetoolbar" content="no">
<link rel="shortcut icon" href="ftd-style/Index-TCC/favicon.ico" type="image/x-icon">
<link rel="apple-touch-icon" href="ftd-style/Index-TCC/favicon.ico">
<meta property="og:url" content="">
<meta property="og:type" content="website">
<meta property="og:title" content="لعبة المتصفّح الإستراتيجيّة على الإنترنت">
<meta property="og:image" content="ftd-style/Index-TCC/mobile/card.jpeg">
<meta property="og:description" content="رائدة الألعاب الجماعية على الإنترنت بالفعل مع آلاف اللاعبين الحقيقيين.">
<meta property="og:locale" content="ar-SA"><meta property="og:site_name" content="Tatar War">
<meta name="format-detection" content="telephone=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="حرب التتار">
<meta name="apple-mobile-web-app-status-bar-style" content="white">
<meta name="theme-color" content="#ffffff">
<meta name="viewport" content="width=device-width">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="لعبة حرب التتار">
<meta name="twitter:description" content="حرب التتار هي لعبة على الانترنت في عالم مليء بالآلاف من اللاعبين الحقيقيين الذين يبدأون جميعهم كزعماء لقرى صغيرة">
<meta name="twitter:image" content="ftd-style/Index-TCC/mobile/card.jpeg">
<meta name="viewport" content="width=device-width, user-scalable=no" />
<script src="ftd-style/Index-TCC/core.js" type="text/javascript"></script>
</head>
<body dir="rtl">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0,viewport-fit=cover">
<div class="Main"></div>
<div class="Bar">
<a href="/">الرئيسية</a>
<a class="Join" href="#">الدخول</a>
<a class="Sign" href="#">التسجيل</a>
<a href="login.php?terms">المساعدة</a>
<a href="help.php">الدليل</a>
</div>
<div class="Body">
<img src="ftd-style/Index-TCC/x.gif" class="Pic">
<div class="HDF"><h1 class="HHF">مرحباً بكم في حرب التتار</h1>هى لعبة تصفح مجانية لا تحتاج الى تحميل ,لعبة حرب في عالم مليء باللاعبين الحقيقين الذين يبدأون جميعهم كزعماء لقرى صغيرة.</div><h3 class="HHT">عن اللعبة</h3><div class="HDS">● ستبدأ كرئيس قرية صغيرة.</br>● ستطور المباني والحقول والجيش.</br>● ستحارب مع أو ضد لاعبين حقيقين.</br></div><div class="HDT">● عدد اللاعبين :<b> <?php echo $players_count1 + $players_count2 + $players_count3 + $players_count4 + $players_count5; ?></b></br>● المتواجدين حاليا :<b> <?php echo $players_count1 + $players_count2 + $players_count3 + $players_count4 + $players_count5; ?></b></br>● عدد السيرفرات: <b>1</b></br></div><h3 class="HHL">لقطات</h3><div class="HDL" id="Img"><img id="myImg" src="ftd-style/Index-TCC/images/v1.png" alt="الحقول" class="HIm"><img id="myImg" src="ftd-style/Index-TCC/images/v2.png" alt="المباني" class="HIm"><img id="myImg" src="ftd-style/Index-TCC/images/v3.png" alt="الخريطة" class="HIm"><img id="myImg" src="ftd-style/Index-TCC/images/v4.png" alt="الذهب" class="HIm"></div></div>
<div id="login_layer" class="overlay">
<div class="mask closer"></div>
<div id="login_list" class="overlay_content">
<h2>اختار سيرفر</h2><a href="#" class="closer No"><img class="dynamic_img" alt="Close" src="ftd-style/Index-TCC/x.gif" /></a>
<ul class="world_list">
<li class="w_big c1">
<a href="login.php"><img class="w_button" src="ftd-style/Index-TCC/x.gif" /></a>
<span class="Inf">
<div class="label_players c1" style="color: #000; !important;">
<bdi>اللاعبين: <b><?php echo $players_count1; ?></b> </bdi>
</div>
<div class="label_online" style="color: #000; !important;">
<bdi>المتواجدون: <b><?php echo $players_count1; ?></b> </bdi>
</div>
<div class="players c1 b"></div>
<div class="online c1 b"></div>

</span>
</li>

<div class="footer"></div>
</div>
</div>
<div id="signup_layer" class="overlay">
<div class="mask closer"></div>
<div id="signup_list" class="overlay_content">
<h2>اختار سيرفر</h2>
<a href="#" class="closer"><img class="dynamic_img" alt="Close" src="ftd-style/Index-TCC/x.gif" /></a>
<ul class="world_list">
<li class="w_big c1">
<a href="register.php"><img class="w_button" src="ftd-style/Index-TCC/x.gif" /></a>
<div class="label_players" style="color: #000; !important;">
<bdi>اللاعبين: <b><?php echo $players_count1; ?></b> </bdi>
</div>
<div class="label_online" style="color: #000; !important;">
<bdi>المتواجدون: <b><?php echo $players_count1; ?></b> </bdi>
</div>
<div class="players b"></div>
<div class="online b"></div>
</li>

</ul>
</div>
</div>
<div id="Screen" class="overlay">
<div class="mask closer"></div>
<div id="signup_list" class="overlay_content">
<h2 id="caption"></h2>
<a class="closer No">
<img alt="" class="dynamic_img" src="ftd-style/Index-TCC/x.gif"></a>
<img class="Screen-content" id="img01">
</div>
</div>

<script src="ftd-style/Index-TCC/images/Js.js?M" type="text/javascript"></script>
<script>init();EyePwd();</script>

</body>
</html>