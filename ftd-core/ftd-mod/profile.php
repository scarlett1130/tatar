<?php

class ProfileModel extends ModelBase
{
function _getNewUpdateKey () {
		return substr ( md5 ( 'Sps'.mt_rand(1,8763).'Trd') , 2, 5 );
	}
 	public function Protection( $playerId, $GoldNum )
    {
$t = time();
$np = ($GLOBALS['AppConfig']['Game']['protection']/12)-1;
$x = date('Y-m-d H:i:s',($t)-(43200/$np));
        $this->provider->executeQuery( "UPDATE `p_players` SET `registration_date` = '%s', `gold_num` = `gold_num` - %s WHERE `id` = '%s';", array(
		    $x,
		    $GoldNum,
            $playerId
        ) );
    }


 	public function Protection2( $playerId )
    {
        $this->provider->executeQuery( "UPDATE `p_players` SET `registration_date` = NOW() ,protection='1' WHERE `id` = '%s';", array(
            $playerId
        ) );
    }


 	public function Protection3( $playerId )
    {
$t = time();
$x = date('Y-m-d H:i:s',($t)-(121400));
        $this->provider->executeQuery( "UPDATE `p_players` SET `registration_date` = '%s',gold_num=gold_num-100 WHERE `id` = '%s';", array(
            $x,
            $playerId
        ) );
    }




	function _changeUpdateKey ($village,$key) {
		$this->provider->executeQuery ('UPDATE p_villages v SET v.update_key=\'%s\' WHERE v.id=%s', 
			array ($key, $village) 
		);
	}
 public function addAllianceContracts( $myid, $youid)
 {
 $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT avatar FROM p_players WHERE id=%s", array(
 $myid
 ) );
 $contracts1 = $contracts_alliance_id1;
 if ( $contracts1 != "" )
 {
 $contracts1 .= ",";
 }
 $contracts1 .= $youid;
 $this->provider->executeQuery( "UPDATE p_players SET avatar='%s' WHERE id=%s", array(
 $contracts1,
 $myid
 ) );
 }
public function removeAlliancewar( $is,$allianceId2 )
 {
 $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT avatar FROM p_players WHERE id=%s", array(
 $is
 ) );
 $contracts1 = "";
 if ( trim( $contracts_alliance_id1 ) != "" )
 {
 $arr = explode( ",", $contracts_alliance_id1 );
 foreach ( $arr as $arrStr )
 {
 if ( $arrStr == $allianceId2 )
 {
 continue;
 }
 if ( $contracts1 != "" )
 {
 $contracts1 .= ",";
 }
 $contracts1 .= $arrStr;
 }
 }
 $this->provider->executeQuery( "UPDATE p_players SET avatar ='%s' WHERE id=%s", array(
 $contracts1,
 $is
 ) );
 }
public function getwarAllianceId( $allianceId )
    {
        return $this->provider->fetchScalar( "SELECT avatar FROM p_players WHERE name='%s'", array(
            $allianceId
        ) );
    }

    public function getPlayerIdByName( $playerName )
    {
        return $this->provider->fetchScalar( "SELECT p.id FROM p_players p WHERE p.name='%s'", array(
            $playerName
        ) );
    }
    	public function getGlobalSitevoting(){
	return $this->provider->fetchScalar('SELECT new_voting FROM g_summary');
	}

    public function getPlayerAgentForById( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.agent_for_players FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
    }

    public function getPlayerMyAgentById( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.my_agent_players FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
    }

    public function setMyAgents( $playerId, $playerName, $playerNameok, $actions, $newAgentId )
    {
        $agentStr = $newAgentId."|".$playerNameok."|".$actions;
        $this->provider->executeQuery( "UPDATE p_players p SET p.my_agent_players=IF(ISNULL(p.my_agent_players) OR p.my_agent_players='', '%s', CONCAT_WS(',', p.my_agent_players, '%s')) WHERE p.id=%s", array( $agentStr, $agentStr, $playerId ) );
        
		$agentFor = $playerId."|".$playerName."|".$actions;
        $this->provider->executeQuery( "UPDATE p_players p SET p.agent_for_players=IF(ISNULL(p.agent_for_players) OR p.agent_for_players='', '%s', CONCAT_WS(',', p.agent_for_players, '%s')) WHERE p.id=%s", array( $agentFor, $agentFor, $newAgentId ) );
    }

    public function removeMyAgents( $playerId, $agents, $aid )
    {
        $agentStr = "";
		foreach ( $agents as $agentId => $name_actions ) {
            if ( $agentStr != "" )
            {
                $agentStr .= ",";
            }
            $agentStr .= $agentId."|".$name_actions[0]."|".$name_actions[1];
        }
        $this->provider->executeQuery( "UPDATE p_players p SET p.my_agent_players='%s' WHERE p.id=%s", array(
            $agentStr,
            $playerId
        ) );
		
        $agentForStr = $this->getPlayerAgentForById( $aid );
        $agentForPlayers = trim( $agentForStr ) == "" ? array( ) : explode( ",", $agentForStr );
        $i = 0;
        $c = sizeof( $agentForPlayers );
        while ( $i < $c )
        {
            $agent = $agentForPlayers[$i];
            list( $agentId, $agentName, $agents ) = explode( "|", $agent );
            if ( $agentId == $playerId )
            {
                unset( $agentForPlayers[$i] );
            }
            ++$i;
        }
        $agentForStr = implode( ",", $agentForPlayers );
        $this->provider->executeQuery( "UPDATE p_players p SET p.agent_for_players='%s' WHERE p.id=%s", array(
            $agentForStr,
            $aid
        ) );
		
    }

    public function removeAgentsFor( $playerId, $agents, $aid )
    {
        $agentStr = "";
        foreach ( $agents as $agentId => $name_actions )
        {
            if ( $agentStr != "" )
            {
                $agentStr .= ",";
            }
            $agentStr .= $agentId."|".$name_actions[0]."|".$name_actions[1];
        }
        $this->provider->executeQuery( "UPDATE p_players p SET p.agent_for_players='%s' WHERE p.id=%s", array(
            $agentStr,
            $playerId
        ) );
        $agentForStr = $this->getPlayerMyAgentById( $aid );
        $agentForPlayers = trim( $agentForStr ) == "" ? array( ) : explode( ",", $agentForStr );
        $i = 0;
        $c = sizeof( $agentForPlayers );
        while ( $i < $c )
        {
            $agent = $agentForPlayers[$i];
            list( $agentId, $agentName, $actions ) = explode( " ", $agent );
            if ( $agentId == $playerId )
            {
                unset( $agentForPlayers[$i] );
            }
            ++$i;
        }
        $agentForStr = implode( ",", $agentForPlayers );
        $this->provider->executeQuery( "UPDATE p_players p SET p.my_agent_players='%s' WHERE p.id=%s", array(
            $agentForStr,
            $aid
        ) );
    }
//
    public function editPlayerProfile( $playerId, $data )
    {
        $selected_village_id = $this->provider->fetchScalar( "SELECT p.selected_village_id FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
        $villages_data_arr = array( );
        $villages_id_arr = explode( "\n", $data['villages'] );
        $i = 0;
        $c = sizeof( $villages_id_arr );
        while ( $i < $c )
        {
            list( $vid, $x, $y, $vname ) = explode( " ", $villages_id_arr[$i], 4 );
           // if ( $vid == $selected_village_id )
           // {
                $vname = htmlspecialchars($data['village_name'][$i]);
                $villages_id_arr[$i] = $vid." ".$x." ".$y." ".$vname;
        $village_name = htmlspecialchars(trim( $data['village_name'][$i] ));
        if ( $village_name != "" )
        {
            $this->provider->executeQuery( "UPDATE p_villages v SET v.village_name='%s' WHERE v.id=%s", array(
                $village_name,
                $vid
            ) );
        }

           // }
            $villages_data_arr[$vname][] = $villages_id_arr[$i];
            ++$i;
        }
        ksort( $villages_data_arr );
        $villages_data = "";
        foreach ( $villages_data_arr as $k => $v )
        {
            foreach ( $villages_data_arr[$k] as $v2 )
            {
                if ( $villages_data != "" )
                {
                    $villages_data .= "\n";
                }
                $villages_data .= $v2;
            }
        }
        $this->provider->executeQuery( "UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.birth_date='%s',\r\n\t\t\t\tp.gender=%s,\r\n\t\t\t\tp.house_name='%s',\r\n\t\t\t\tp.description1='%s',\r\n\t\t\t\tp.description2='%s',\r\n\t\t\t\tp.villages_data='%s',\r\n\t\t\t\tp.used1='%s'\r\n\t\t\tWHERE p.id=%s", array(
            $data['birthData'],
            $data['gender'],
            $data['house_name'],
            $data['description1'],
            $data['description2'],
            $villages_data,
            $data['used1'],
            $playerId
        ) );
    }

    public function changePlayerPassword( $playerId, $newPassword )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.pwd='%s' WHERE p.id=%s", array(
            $newPassword,
            $playerId
        ) );
    }
	

    public function changePlayerEmail_ate_new( $playerId, $email_alt_o, $email_alt )
    {
        if ( 0 < intval( $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players p WHERE p.email='%s'", array(
            $email_alt_o
        ) ) ) )
        {
            return;
        }
        $this->provider->executeQuery( "UPDATE p_players p SET p.email_alt='%s' WHERE p.id=%s", array(
            $email_alt,
            $playerId
        ) );
    }
	
	public function changePlayerEmail( $playerId, $newEmail )
    {
        if ( 0 < intval( $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players p WHERE p.email='%s'", array(
            $newEmail
        ) ) ) )
        {
            return;
        }
        $this->provider->executeQuery( "UPDATE p_players p SET p.email='%s' WHERE p.id=%s", array(
            $newEmail,
            $playerId
        ) );
    }

	public function email_cancel($id)
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.email_alt=NULL WHERE p.id=%s", array( $id ) );
    }

    public function getPlayerRank( $playerId, $score )
    {
        return $this->provider->fetchScalar( "SELECT (\r\n\t\t\t\t(SELECT\r\n\t\t\t\t\tCOUNT(*)\r\n\t\t\t\tFROM p_players p\r\n\t\t\t\tWHERE p.player_type!=%s AND (p.total_people_count*10+p.villages_count)>%s) \r\n\t\t\t\t+\r\n\t\t\t\t(SELECT \r\n\t\t\t\t\tCOUNT(*)\r\n\t\t\t\tFROM p_players p\r\n\t\t\t\tWHERE p.player_type!=%s AND p.id<%s AND (p.total_people_count*10+p.villages_count)=%s)\r\n\t\t\t) + 1 rank", array(
            PLAYERTYPE_ADMIN,
            $score,
            PLAYERTYPE_ADMIN,
            $playerId,
            $score
        ) );
    }

    public function getWinnerPlayer( )
    {
        $playerId = intval( $this->provider->fetchScalar( "SELECT gs.win_pid FROM g_settings gs" ) );
        return $this->getPlayerDataById( $playerId );
    }
        public function getTop1Attacker( )
    {
        $playerId = intval( $this->provider->fetchScalar( "SELECT  `id`,`attack_points`,`name` FROM  `p_players` ORDER BY  `attack_points` DESC LIMIT 1" ) );
        return $this->getPlayerDataById( $playerId );
    }


    public function getPlayerDataById( $playerId )
    {
        $protectionPeriod = intval( $GLOBALS['GameMetadata']['player_protection_period'] / $GLOBALS['GameMetadata']['game_speed'] );
        return $this->provider->fetchRow( "SELECT\r\n\t\t\t\tp.id,player_type,\r\n\t\t\t\tp.tribe_id,\r\n\t\t\t\tp.alliance_id,\r\n\t\t\t\tp.alliance_name,\r\n\t\t\t\tp.house_name, \r\n\t\t\t\tp.is_blocked,club,color_name,used1,troops,last_ip,UserSession,blocked_time,\r\n\t\t\t\tp.birth_date,\r\n\t\t\t\tp.gender,\r\n\t\t\t\tp.description1,agent_for_players,my_agent_players, p.description2,\r\n\t\t\t\tp.medals,\r\n\t\t\t\tp.total_people_count,email,is_haat,\r\n\t\t\t\tp.villages_count,\r\n\t\t\t\tp.name,\r\n\t\t\t\tp.last_login_date,\r\n\t\t\t\tp.avatar,\r\n\t\t\t\tp.villages_id,\r\n\t\t\t\tDATE_FORMAT(registration_date, '%%Y/%%m/%%d %%H:%%i') registration_date,\r\n\t\t\t\tTIMEDIFF(DATE_ADD(registration_date, INTERVAL %s SECOND), NOW()) protection_remain,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(DATE_ADD(registration_date, INTERVAL %s SECOND), NOW())) protection_remain_sec,\r\n\t\t\t\tDATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(birth_date)), '%%Y')+0 age\r\n\t\t\tFROM p_players p\r\n\t\t\tWHERE p.id=%s", array(
            $protectionPeriod,
            $protectionPeriod,
            $playerId
        ) );
    }

    public function getVillagesSummary( $villages_id )
    {
        return $this->provider->fetchResultSet( "SELECT\r\n\t\t\t\tv.id,\r\n\t\t\t\tv.rel_x, v.rel_y,\r\n\t\t\t\tv.village_name,\r\n\t\t\t\tv.people_count,village_oases_id,\r\n\t\t\t\tv.is_capital\r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id IN (%s)\r\n\t\t\tORDER BY v.people_count DESC", array(
            $villages_id
        ) );
    }

    public function resetGNewsFlag( $playerId )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.new_gnews=0 WHERE p.id=%s", array(
            $playerId
        ) );
    }

	public function resetGvoFlag( $voting, $playerId )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.new_voting=0 WHERE p.id=%s", array( $playerId ) );
		$this->provider->executeQuery('UPDATE g_summary SET new_voting="%s"', array($voting));
    }


    public function resetproFlag( $p, $playerId )
    {
        $this->provider->executeQuery( "UPDATE p_players  SET registration_date='%s' WHERE id=%s", array(
            $p,
            $playerId
        ) );
    }

        	public function getGlobalSitesummary(){
	return $this->provider->fetchScalar('SELECT truce_time FROM g_summary');
	}


}

?>