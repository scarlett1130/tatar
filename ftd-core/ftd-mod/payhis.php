<?php

class Payhis extends ModelBase{
    
        public function getPlayerDataByName( $playerName )
    {
        return $this->provider->fetchRow( "SELECT p.id FROM p_players p WHERE p.name='%s'", array(
            $playerName
        ) );
    }

        public function getPlayerDataByName1( $playerName1 )
    {
        return $this->provider->fetchRow( "SELECT p.name FROM p_players p WHERE p.id='%s'", array(
            $playerName1
        ) );
    }
        
        public function getTotalMoney()
    {
        return $this->provider->fetchRow( "SELECT total_gold, total_sms, total_cashu, total_onecard FROM money_total ");
    }
        
        public function PayhisByType(  )
        {
            return $this->provider->fetchResultSet("SELECT * FROM money_log");
        }
        public function PayhisByType2( $type )
        {
            return $this->provider->fetchResultSet("SELECT * FROM money_log WHERE usernam='%s'", array(
                $type
        ) );
    }
        
        
}
?>
