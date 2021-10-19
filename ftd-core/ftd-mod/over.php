<?php

class OverGameModel extends ModelBase
{

public function getWinnerPlayer( )
    {
        $playerId = intval( $this->provider->fetchScalar( "SELECT gs.win_pid FROM g_settings gs" ) );
        return $this->getPlayerDataById( $playerId );
    }
    public function getTopsAttacker( )
    {
        $playerId = intval( $this->provider->fetchScalar( "SELECT  `id`,`attack_points`,`name` FROM  `p_players` WHERE name != 'التتار' ORDER BY  `attack_points` DESC LIMIT 1" ) );
        return $this->getPlayerDataById( $playerId );
    }
    public function getTopsDeffer( )
    {
        $playerId = intval( $this->provider->fetchScalar( "SELECT  `id`,`defense_points`,`name` FROM  `p_players` WHERE name != 'التتار' ORDER BY  `defense_points` DESC LIMIT 1" ) );
        return $this->getPlayerDataById( $playerId );
    }
    public function getTopsPop( )
    {
        $playerId = intval( $this->provider->fetchScalar( "SELECT  `id`,`total_people_count`,`name` FROM  `p_players` WHERE name != 'التتار' ORDER BY  `total_people_count` DESC LIMIT 1" ) );
        return $this->getPlayerDataById( $playerId );
    }
    public function getTopsHero( )
    {
        $playerId = intval( $this->provider->fetchScalar( "SELECT  `id`,`hero_points`,`name` FROM  `p_players` WHERE name != 'التتار' ORDER BY  `hero_points` DESC LIMIT 1" ) );
        return $this->getPlayerDataById( $playerId );
    }
public function getPlayerDataById( $playerId )
    {
        $protectionPeriod = intval( $GLOBALS['GameMetadata']['player_protection_period'] );
        return $this->provider->fetchRow( "SELECT\r\n\t\t\t\tp.id,\r\n\t\t\t\tp.tribe_id,\r\n\t\t\t\tp.alliance_id,\r\n\t\t\t\tp.alliance_name,\r\n\t\t\t\tp.house_name, \r\n\t\t\t\tp.is_blocked,\r\n\t\t\t\tp.birth_date,\r\n\t\t\t\tp.gender,\r\n\t\t\t\tp.description1, p.description2,\r\n\t\t\t\tp.medals,\r\n\t\t\t\tp.total_people_count,\r\n\t\t\t\tp.villages_count,\r\n\t\t\t\tp.name,\r\n\t\t\t\tp.avatar,\r\n\t\t\t\tp.villages_id,\r\n\t\t\t\tDATE_FORMAT(registration_date, '%%Y/%%m/%%d %%H:%%i') registration_date,\r\n\t\t\t\tTIMEDIFF(DATE_ADD(registration_date, INTERVAL %s SECOND), NOW()) protection_remain,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(DATE_ADD(registration_date, INTERVAL %s SECOND), NOW())) protection_remain_sec,\r\n\t\t\t\tDATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(birth_date)), '%%Y')+0 age\r\n\t\t\tFROM p_players p\r\n\t\t\tWHERE p.id=%s", array(
            $protectionPeriod,
            $protectionPeriod,
            $playerId
        ) );
    }

}

?>