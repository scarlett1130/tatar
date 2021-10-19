<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."payment.php" );
class GPage extends WebService
{

    public function load( )
    {
        $AppConfig = $GLOBALS['AppConfig'];
        if ( $this->isPost( ) )
        {
            $usedPackage = NULL;
            foreach ( $AppConfig['plus']['packages'] as $package )
            {
                if ( $package['cost'] == $_POST['amount'] )
                {
                    $usedPackage = $package;
                }
            }
            if ( isset( $_POST['session_id'] ) )
            {
                $merchant_id = $AppConfig['plus']['payments']['cashu']['merchant_id'];
            }
            else
            {
                $merchant_id = $AppConfig['plus']['payments']['onecard']['merchant_id'];
                $p = new GPage( );
                $p->run( );
                return;
            }
            $usedPayment = NULL;
            foreach ( $AppConfig['plus']['payments'] as $payment )
            {
                if ( $payment['merchant_id'] == $merchant_id )
                {
                    $usedPayment = $payment;
                }
            }
            if ( !isset( $_GET[$usedPayment['returnKey']] ) )
            {
                return;
            }
                $m = new PaymentModel();
                $transID = $_POST['trn_id'];
                $this->payconfairm = $m->getMonaydata( $transID );

            if ( $usedPackage != NULL && $this->payconfairm == null && $usedPayment != NULL && $_POST['token'] == md5( sprintf( "%s:%s:%s:%s", $merchant_id, $_POST['amount'], strtolower( $_POST['currency'] ), $_POST['test_mode'] ? $usedPayment['testKey'] : $usedPayment['key'] ) ) )
            {
                $playerId = base64_decode( $_POST['session_id'] );
                $goldNumber = $usedPackage['gold'];
                $transID = $_POST['trn_id'];
                $m = new PaymentModel();
                $userid = $m->getPlayerDataById ($playerId);
                $usernam = $userid['name'];
                $cost = $_POST['amount'];
                $currency = $_POST['currency'];
                $type = 'cashu';
                $m = new PaymentModel( );
$gg = $goldNumber+($goldNumber*$usedPackage['plus']/100);
                $m->incrementPlayerGold( $playerId, $gg );
                $m->dispose( );
                $m = new PaymentModel();
                $m->InsertMoneyLog( $transID, $usernam, $goldNumber, $cost, $currency, $type );
                 $m->updatetotalcashu( $goldNumber, $cost );
//here ern gold
$tatarzx = new QueueModel();
//here 10% =>
$show = $tatarzx->provider->fetchRow( "SELECT `invite_by` FROM `p_players` WHERE id = '".$playerId."'" );
if ($show['invite_by']) {
$ng = $goldNumber*10/100;
$m = new PaymentModel( );
$m->incrementPlayerGold( $show['invite_by'], $ng );
//here 1% =>
$show1 = $tatarzx->provider->fetchRow( "SELECT `invite_by` FROM `p_players` WHERE id = '".$show['invite_by']."'" );
if ($show1['invite_by']) {
$ng = $goldNumber*1/100;
$m = new PaymentModel( );
$m->incrementPlayerGold( $show1['invite_by'], $ng );
}
}
//end ern gold
                $userid = $m->getPlayerDataById ($playerId);
                $usernam = $userid['name'];
                $name = "النضام";
                require_once( MODEL_PATH."msg.php" );
                $mm = new MessageModel( );
                        $subject = "القسم المالي cashu";
$message = 'عزيزي   '.$usernam.',

كان الشراء الناجحة. و قد تم إضافة '.$goldNumber.' الذهب إلى الحساب الخاص بك.

شكرا لشرائك

حرب التتار كلاسيك, القسم المالي cahsu';
                $messageId = $mm->sendMessage( 1, $name, $playerId, $usernam, $subject, $message );
                $quizArray[] = $messageId;
                echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8" /><h2> تمت عملية الشراء بنجاح,شكرا لك أغغلق الصفحه وأرجع أكمل لعبك </h2>';
            }
            else
            {
                echo '<meta http-equiv="content-type" content="text/html; charset=UTF-8" /><h2>لم تتم عملية الشراء بنجاح , يرجى مراسلة الدعم</h2>';
            }
        }
    }
}
$p = new GPage( );
$p->run( );
?>
