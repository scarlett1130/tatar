<?php

class GiftModel extends ModelBase
{

    public function getLastGeft( $playerId )
    {
        $select =  $this->provider->fetchRow("SELECT pla_gift FROM p_players WHERE id=".$playerId." ");
        return $select['pla_gift'];
    }

    public function updateLastGeft( $playerId )
    {
        $this->provider->executeQuery("UPDATE p_players SET pla_gift='".time()."' WHERE id='".$playerId."'");
    }

    public function updateGold( $num, $playerId )
    {
        $this->provider->executeQuery("UPDATE p_players SET gold_num=gold_num+'".$num."' WHERE id='".$playerId."'");
    }


}

?>