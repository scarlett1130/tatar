<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
require_once( MODEL_PATH."msg.php" );
class GPage extends SecureGamePage
{
        function GPage(){
                parent::securegamepage();
                $this->viewFile = "support.phtml";
                $this->contentCssClass = "plus";
        }
        function load()
                {
           parent::load();
$this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 0 <= intval($_GET['t'])) && intval($_GET['t']) <= 3) ? intval($_GET['t']) : 0);
$c = $_GET['close'];
$id = $_GET['id'];
$m = new MessageModel( );
if ($this->player->playerId == 1) {
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."admin.php");
$name = $_SESSION['nm_admin'];
$pwd = $_SESSION['pwd_admin'];
if ($name == $a && $pwd == $p) {

}else {
            exit( 0 );

}
}
if (isset($c) && is_numeric($c)) {
$s = $m->getMessageSupport($c);
list($title, $type, $isnew) = explode('|', $s['msg_title']);
if ($isnew != 1) {
$tit = "".$title."|".$type."|1";
$aa = $m->up($tit,$c);
$m->markMessageAsReaded(1, intval($c));
$this->redirect('support?id='.$c.'');
}
}

if (isset($id) && is_numeric($id)) {
$isread = $m->getMessageSupport(intval($id));
if ($this->player->playerId == 1) {
if (!$isread['is_readed'] && !$this->player->isSpy)
{
$m->markMessageAsReaded($this->player->playerId, intval($id));
--$this->data['new_mail_count'];
}
}
if($this->isPost()){
    session_start();
if ($_SESSION['num_request'] >= 5 && $_SESSION['cliprz_request'] <= time() - 60) {
$_SESSION['num_request'] = 0;
}
    if($_SESSION['cliprz_request'] > time() - 60 && $_SESSION['num_request'] >= 5 && $this->player->playerId != 1) {
exit ("<center><h1>Error !!!</h1></center>");
        }
if (htmlspecialchars($_POST['reply']) != '') {
$msg = $m->getMessageSupport($id);
list($titileold, $typeold, $isnew) = explode('|', $msg['msg_title']);
$tatarzx = new QueueModel();
$time = date('Y/m/d H:i:s');
$tatarzx->provider->executeQuery( "UPDATE p_msgs SET creation_date='".$time."' WHERE id='".$id."'");
if ($this->player->playerId == 1) {
$isnew = 2;
$adminname = "الادارة";
$a = "رد على طلبك للدعم";
$domain = WebHelper::getdomain();
$l = "http://".$domain."support?id=".$id;
$msgs = 'تحيه طيبة 

نحيطك علما بأن تم الرد من قبل الدعم على رسالتك بعنوان : '.$titileold.'


ويرجى اغلاق الرساله عند الانتهاء منها 

شكرا لك ادارة حرب التتار ';
$messageId = $m->sendMessage( 1, $adminname, $msg['from_player_id'], $msg['from_player_name'], $a, $msgs );
$quizArray[] = $messageId;
}else {
$isnew = 3;
}
$newtitle="".$titileold."|".$typeold."|".$isnew;
$time = date('Y/m/d H:i:s');
if ($this->player->playerId == 1) {
$isadmin = 1;
}else {
$isadmin = 0;
}
$reply = htmlspecialchars($_POST['reply']);
$newmsg = "".$msg['msg_body']."____".$isadmin."|".$time."|".$reply."";
$uppp = $m->upp($newmsg,$newtitle,$id);
if ($this->player->playerId != 1) {
$m->markzMessageAsReaded(1, intval($id));
}
session_start();  
$_SESSION['cliprz_request'] = time();
$_SESSION['num_request'] = ($_SESSION['num_request']+1);
}
}
}else 
if ($this->selectedTabIndex == 0) {
}else if ($this->selectedTabIndex == 1) {
}else if ($this->selectedTabIndex == 2) {
if($this->isPost()){
session_start();
if ($_SESSION['num_request'] >= 5 && $_SESSION['cliprz_request'] <= time() - 60) {
$_SESSION['num_request'] = 0;
}
if($_SESSION['cliprz_request'] > time() - 60 && $_SESSION['num_request'] >= 5) {
exit ("<center><h1>Error !!!</h1></center>");
}
$title = htmlspecialchars($_POST['title']);
$type = htmlspecialchars($_POST['type']);
$msg = htmlspecialchars($_POST['content']);
$tep = 0;
if ($type < 5) {
$tep = 1;
}
if (((($msg != '' )&& $title != '') && $tep)) {
$m = new MessageModel( );
$adminname = $this->appConfig['system']['adminName'];
$a = "".$title."|".$type."|0";
$messageId = $m->sendMessage( $this->player->playerId, $this->data['name'], 1, $adminname, $a, $msg );
$quizArray[] = $messageId;
//send To Mail
$to = "tr4v4n_war@yahoo.com"; 
$you = "tr4v4n_war@yahoo.com"; 
$tn = $title." ~ ".$this->data['name'];
mail ( "$to" , "$tn" , "$msg" , "Form:$you" );
//EnD
session_start();  
$_SESSION['cliprz_request'] = time();
$_SESSION['num_request'] = ($_SESSION['num_request']+1);
$this->redirect('support');
}
}
}
}
}
$p = new GPage();
$p->run();
?>