<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."payment.php" );
require_once( LIB_PATH."paypal.class.php" );
class GPage extends WebService
{
public function load( )
{
$AppConfig = $GLOBALS['AppConfig'];
$p = new paypal_class( );
$m = new PaymentModel( );
if(isset($_GET['action'])) {
if($_GET['action'] == 'success') {
echo "Gold Added";
}
}
$ip = "";
if ( isset( $_SERVER['REMOTE_ADDR'] ) )
{
$ip = $_SERVER['REMOTE_ADDR'];
}
else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
{
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) )
{
$ip = $_SERVER['HTTP_CLIENT_IP'];
}

$m = new QueueModel();
$have_ip = $m->provider->fetchScalar("SELECT COUNT(*) FROM p_players WHERE last_ip='".$ip."'");
if ($have_ip >= 1) {
exit;
}

if (!isset ($_POST['ipn_track_id'])) {
exit;
}

if(isset($_GET['suc'])) {
if((base64_decode( $_GET['suc'] )/100000/5.3) >= 1) {
if(isset($_POST['payment_status'])) {
if($_POST['payment_status'] == 'Completed') {
$get_post = $ip."
";

$get_post .= $_SERVER['HTTP_REFERER']."
";
if( isset($_GET) ) {
foreach ($_GET as $key => $value) {
$get_post .= $key." = ".$value;
}
}
$get_post .= "
post
";
if( isset($_POST) ) {
foreach ($_POST as $key => $value) {
$get_post .= $key." = ".$value."
";
}
}
$p_test = "paypal__test.txt";
$fp__test = fopen($p_test, 'w');
fwrite($fp__test, $get_post);
fclose($fp__test);

$usedPackage = NULL;
foreach ( $AppConfig['plus']['packages'] as $package )
{
if ( $package['cost'] == $_POST['mc_gross'] )
{
$usedPackage = $package;
}
}
$Player = (base64_decode( $_GET['suc'] )/100000/5.3);
$m = new PaymentModel( );
$pg = $usedPackage['gold']+($usedPackage['gold']*$usedPackage['plus']/100);
$m->incrementPlayerGold( $Player, $pg );
$userid = $m->getPlayerDataById ($Player);
$usernam = $userid['name'];
$d = date('Y/m/d H:i:s');
$m->InsertMoneyLog( $d, $usernam, $pg, $usedPackage['cost'], "USD", "cashu" );
$m->dispose( );
}
}
}
}
}
}
$p = new GPage( );
$p->run( );
?>