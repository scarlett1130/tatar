<?php

class PasswordModel extends ModelBase
{

    public function isPlayerIdExists( $playerId )
    {
        return 0 < $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players p WHERE p.id=%s", array( $playerId ) );
    }

    public function isPlayerIdHasEmail( $playerId, $email )
    {
        return 0 < $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players p WHERE p.id=%s AND p.email='%s'", array( $playerId, $email ) );
    }

    public function getPlayerName( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.name FROM p_players p WHERE p.id=%s", array( $playerId ) );
    }
         public function getPlayerEmail( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.email FROM p_players p WHERE p.id=%s", array( $playerId ) );
    }
	     public function getPasswordCode( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.password_code FROM p_players p WHERE p.id=%s", array( $playerId ) );
    }




    public function setPlayerPassword( $playerId, $password )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.pwd='%s' WHERE p.id=%s AND p.id != 1", array( md5( $password ), $playerId ) );
    }
	public function setPlayerPasswordCode( $playerId, $password_code )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.password_code='%s' WHERE p.id=%s AND p.id != 1", array( $password_code, $playerId ) );
    }

}

?>