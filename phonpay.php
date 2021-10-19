<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."payment.php" );
class GPage extends WebService{
public function load( ){

// check that the request comes from PayGol server
/*
if(!in_array($_SERVER['REMOTE_ADDR'],
  array('109.70.3.48', '109.70.3.146', '109.70.3.210'))) {
  header("HTTP/1.0 403 Forbidden");
  die("Error: Unknown IP");
}
*/
$fix_ip = explode ("." , $_SERVER['REMOTE_ADDR']);
if ($fix_ip[0] == "109" AND $fix_ip[1] == "70") {
$m = new PaymentModel();
$AppConfig = $GLOBALS['AppConfig'];
$message_id = $_GET['message_id'];
$service_id = $_GET['service_id'];
$shortcode = $_GET['shortcode'];
$keyword = $_GET['keyword'];
$message = $_GET['message'];
$sender = $_GET['sender'];
$operator = $_GET['operator'];
$country = $_GET['country'];
$custom = $_GET['custom'];
$points = $_GET['points'];
$price = $_GET['price'];
$currency = $_GET['currency'];
$this->payconfairm = $m->getMonaydata( $sender );
if ($this->payconfairm == null) {
$userid = $m->getPlayerDataById($custom);
$usernam = $userid['name'];
$m->incrementPlayerGold($custom,$points);
$m->InsertMoneyLog( $sender, $usernam, $points, $price, 'SAR', 'cashu' );
$m->updatetotalcashu($points,$price);
$m->dispose( );
require_once MODEL_PATH . 'msg.php';
$mm = new MessageModel ();
$msg = "تحيه طيبه ".$usernam."

نود اعلامكم بانه تمت عمليه شراء عبر الرسائل النصيه وكانت العمليه ناجحه 
تمت العمليه عبر رقم الجوال : ".$sender."
ومقابل ".$points." قطعة ذهبيه

شكرا لك ... القسم المالي";
$mm->sendMessage (1, "النظام", $custom, $usernam, "القسم المالي sms", $msg);
}
}
}
}
$p = new GPage( );
$p->run( );
?>
