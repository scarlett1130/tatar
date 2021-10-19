<?php

class PaymentModel extends ModelBase
{
    public function getPlayerDataById( $playerId )
    {
        return $this->provider->fetchRow( "SELECT p.name FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
    }
    public function incrementPlayerGold( $playerId, $goldNumber )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.gold_num=gold_num+%s WHERE p.id=%s", array(
            $goldNumber,
            $playerId
        ) );
    }
        
	public function getMonaydata( $transID )
    {
        return $this->provider->fetchRow( "SELECT * FROM money_log WHERE transID='%s'", array(
            $transID
        ) );
    }
        public function InsertMoneyLog( $transID, $usernam, $goldNumber, $cost, $currency, $type )
    {
        $this->provider->executeQuery("INSERT INTO money_log (transID, usernam, golds, money, currency,type) VALUES ('$transID','$usernam', $goldNumber,$cost,'$currency','$type');");
    }
        
        public function updatetotalsms( $goldNumber, $cost )
    {
        $this->provider->executeQuery( "UPDATE money_total  SET total_gold=total_gold+%s, total_sms=total_sms+%s", array(
            $goldNumber,
            $cost
        ) );
    }
        
        public function updatetotalcashu( $goldNumber, $cost )
    {
        $this->provider->executeQuery( "UPDATE money_total SET total_gold=total_gold+%s, total_cashu=total_cashu+%s", array(
            $goldNumber,
            $cost
        ) );
    }
        
        public function updatetotalonecard( $goldNumber, $cost )
    {
        $this->provider->executeQuery( "UPDATE money_total SET total_gold=total_gold+%s, total_onecard=total_onecard+%s", array(
            $goldNumber,
            $cost
        ) );
    }

}

?>
