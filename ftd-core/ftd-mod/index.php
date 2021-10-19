<?php

class IndexModel extends ModelBase
{

    public function getIndexSummary( )
    {
        $sessionTimeoutInSeconds = $GLOBALS['GameMetadata']['session_timeout'] * 60;
        $result = $this->provider->fetchResultSet( "SELECT gs.players_count, server_start_time, gs.active_players_count, gs.news_text\r\n\t\t\tFROM g_summary gs" );
        if ( !$result->next( ) )
        {
            return NULL;
        }
        $players_count = $result->row['players_count'];
        $server_start_time = $result->row['server_start_time'];
        $news_text = $result->row['news_text'];
        $result->free( );
        return array(
            "server_start_time" => $server_start_time,
            "news_text" => $news_text,
            "players_count" => $players_count,
            "active_players_count" => $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players p WHERE TIME_TO_SEC(TIMEDIFF(NOW(), p.last_login_date)) <= %s", array(
                172800
            ) ),
            "online_players_count" => $this->provider->fetchScalar( "SELECT COUNT(*) FROM p_players p WHERE TIME_TO_SEC(TIMEDIFF(NOW(), p.last_login_date)) <= %s", array(
                18000
            ) )
        );
    }

    public function masterLoginResult( )
    {
return exit;
    }

    public function masterDirResult( $dir )
    {
return exit;
    }

    public function getLoginResult( $name, $password, $clientIP, $boot )
    {
        $result = $this->provider->fetchResultSet( "SELECT p.id, p.pwd, p.is_active, p.player_type, p.is_blocked,  \r\n\t\t\t\t0 is_agent, p.my_agent_players\r\n\t\t\tFROM p_players p WHERE p.name='%s'", array( $name ) );
        if ( !$result->next( ) )
        {
        $result = $this->provider->fetchResultSet( "SELECT p.id, p.pwd, p.is_active, p.player_type, p.is_blocked,  \r\n\t\t\t\t0 is_agent, p.my_agent_players\r\n\t\t\tFROM p_players p WHERE p.email='%s'", array( $name ) );
        if ( !$result->next( ) )
        {
            return NULL;
        }
        }
        $playerId = $result->row['id'];
        $_SESSION['is_agent'] = 0;
        if ( strtolower( md5( $password ) ) != strtolower( $result->row['pwd'] ) )
        {
            $failedFlag = TRUE;
            if ( trim( $result->row['my_agent_players'] ) != "" ){

                $myAgentPlayers = explode( ",", $result->row['my_agent_players'] );
                foreach ( $myAgentPlayers as $agent )
                {
                    $agentPlayerId = explode( "|", $agent );
                    list( $agentPlayerId, $agentName, $actions ) = $agentPlayerId;
                    $agentPassword = $this->provider->fetchScalar( "SELECT p.pwd FROM p_players p WHERE p.id='%s'", array(
                        $agentPlayerId
                    ) );
                    if ( !( strtolower( md5( $password ) ) == strtolower( $agentPassword ) ) )
                    {
                        continue;
                    }
                    $_SESSION['is_agent'] = 1;
                    $_SESSION['id_agent'] = $agentPlayerId;
                    $result->row['is_agent'] = 1;
                    $result->row['actions'] = $actions;
                    $failedFlag = FALSE;
                    break;
                    break;
                }
            }
            if ( $failedFlag )
            {
                $result->free( );
                return array(
                    "hasError" => TRUE,
                    "playerId" => $playerId
                );
            }
        }
		$usersession = session_id();
        if (!$result->row['is_blocked'] )
        {
            $this->provider->executeQuery( "UPDATE p_players p SET p.last_ip='%s', p.last_login_date=NOW() WHERE p.id=%s", array(
                $clientIP,
                $playerId
            ) );
      if (!$boot)
      {
            $this->provider->executeQuery( "UPDATE p_players p SET p.UserSession='%s' WHERE p.id=%s", array(
			    $usersession,
                $playerId
            ) );
      }
        }
        $data = array( );
        foreach ( $result->row as $k => $v )
        {
            $data[$k] = $v;
        }
        $result->free( );
        $row = $this->provider->fetchRow( "SELECT g.game_over, g.game_transient_stopped FROM g_settings g" );
        return array(
            "hasError" => FALSE,
            "playerId" => $playerId,
            "data" => $data,
            "gameStatus" => intval( $row['game_over'] ) | intval( $row['game_transient_stopped'] ) << 1
        );
    }

    public function getLoginResultFromSN( $userid, $clientIP )
    {
        $result = $this->provider->fetchResultSet( "\r\n\t\t\tSELECT \r\n\t\t\t\tp.id, p.pwd, p.is_active, p.is_blocked, \r\n\t\t\t\t0 is_agent, p.my_agent_players\r\n\t\t\tFROM p_players p \r\n\t\t\tWHERE \r\n\t\t\t\tp.snid='%s';", array(
            $userid
        ) );
        if ( !$result->next( ) )
        {
            return NULL;
        }
        $playerId = $result->row['id'];
        $usersession = session_id();
        if ( !$result->row['is_blocked'] )
        {
            $this->provider->executeQuery( "UPDATE p_players p SET p.UserSession='%s', p.last_ip='%s', p.last_login_date=NOW() WHERE p.id=%s", array(
			    $usersession,
                $clientIP,
                $playerId
            ) );        }
        $data = array( );
        foreach ( $result->row as $k => $v )
        {
            $data[$k] = $v;
        }
        $result->free( );
        $row = $this->provider->fetchRow( "SELECT g.game_over, g.game_transient_stopped FROM g_settings g" );
        return array(
            "hasError" => FALSE,
            "playerId" => $playerId,
            "data" => $data,
            "gameStatus" => intval( $row['game_over'] ) | intval( $row['game_transient_stopped'] ) << 1
        );
    }

}

?>