<?php

require( ".".DIRECTORY_SEPARATOR."tatarzx_privete".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."payment.php" );
class GPage extends WebService
{

    public function load()
    {
        $AppConfig = $GLOBALS['AppConfig'];
        if ( $this->isPost() )
        {
            foreach ( $AppConfig['plus']['packages'] as $package )
            {
                if ( $package['cost'] == $_POST['OneCard_Amount'] )
                {
                    $usedPackage = $package;
                }
            }
			$m = new PaymentModel();
			$transID = $_POST['OneCard_TransID'];
			$this->payconfairm = $m->getMonaydata( $transID );

				$merchant_id = $AppConfig['plus']['payments']['onecard']['merchant_id'];
				$Keyword = $AppConfig['plus']['payments']['onecard']['testKey'];
                                $dt = 'on';
				$hashcode = MD5($merchant_id.$_POST['OneCard_TransID'].$_POST['OneCard_Amount'].$_POST['OneCard_Currency'].$dt.$Keyword); 
                if ( $_POST['OneCard_Code'] == "00" && $usedPackage != NULL  && $this->payconfairm == null)
                { 
				    $playerId = base64_decode( $_POST['OneCard_Field2'] );
				    $transID = $_POST['OneCard_TransID'];
					$m = new PaymentModel();
                    $userid = $m->getPlayerDataById ($playerId);
			        $usernam = $userid['name'];
			        $cost = $usedPackage['cost'];
			        $currency = $_POST['OneCard_Currency'];
					$type = 'cashu';
                    $goldNumber = $usedPackage['gold'];
					$time = time();
					$m = new PaymentModel();
					$m->updatetotalonecard( $goldNumber, $cost );
                                           $pg = $goldNumber+($goldNumber*$usedPackage['plus']/100);
                    $m->incrementPlayerGold( $playerId, $pg );
					$m = new PaymentModel();
					$m->InsertMoneyLog( $transID, $usernam, $goldNumber, $cost, $currency, $time, $type );
//here ern gold
$tatarzx = new QueueModel();
//here 10% =>
$show = $tatarzx->provider->fetchRow( "SELECT `invite_by` FROM `p_players` WHERE id = '".$playerId."'" );
if ($show['invite_by']) {
$ng = $goldNumber*10/100;
$m->incrementPlayerGold( $show['invite_by'], $ng );
//here 1% =>
$show1 = $tatarzx->provider->fetchRow( "SELECT `invite_by` FROM `p_players` WHERE id = '".$show['invite_by']."'" );
if ($show1['invite_by']) {
$ng = $goldNumber*1/100;
$m->incrementPlayerGold( $show1['invite_by'], $ng );
}
}
//end ern gold
// ارسال رساله الى الاعب بأعلامه ان العمليه ناجحه
$name = "النضام";
require_once( MODEL_PATH."msg.php" );
$mm = new MessageModel( );
$subject = "القسم المالي onecard";
$message = 'تحيه طيبه

عزيزي '.$usernam.',

لقد تم شحن ذهب بقيمة '.$cost.' دولار مقابل '.$goldNumber.' من الذهب وتمت العمليه بنجاح.

ادارة اللعبة , القسم المالي';
$messageId = $mm->sendMessage( 1, $name, $playerId, $usernam, $subject, $message );
$quizArray[] = $messageId;
$m->dispose();
//نهايه
					
                    header("Location: plus.php?t=2");
                }
                else
                {
                    header("Location: plus.php");
                }
        }
		

    }

}

$p = new GPage();
$p->run();
?>
