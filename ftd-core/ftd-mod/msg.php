<?php
class MessageModel extends ModelBase
{

    public $maxMailBoxSize = 10000;


    public function getMessageListSupport( $playerId )
    {
        return $this->provider->fetchResultSet( "SELECT id,creation_date,from_player_name,msg_title,msg_body FROM p_msgs WHERE to_player_id=%s AND from_player_id=%s ORDER BY creation_date DESC", array(
            1,
            $playerId
        ) );
    }
    public function getMessageListSupport2( $playerId )
    {
        return $this->provider->fetchResultSet( "SELECT id,creation_date,from_player_name,from_player_id,msg_title,msg_body FROM p_msgs WHERE to_player_id=%s ORDER BY creation_date DESC", array(
           
            $playerId
        ) );
    }


        public function getMessageSupport( $messageId )
    {
        return $this->provider->fetchRow( "SELECT * FROM p_msgs WHERE id=%s AND to_player_id=%s", array(
            $messageId,
            1
        ) );
    }

        public function up( $messaget, $messageId )
    {
        return $this->provider->fetchResultSet( "UPDATE p_msgs SET msg_title='%s' WHERE id=%s", array(
            $messaget,
            $messageId
        ) );
    }


        public function upp( $messaget,$messaget2, $messageId )
    {
        return $this->provider->fetchResultSet( "UPDATE p_msgs SET msg_body='%s',msg_title='%s' WHERE id=%s", array(
            $messaget,
            $messaget2,
            $messageId
        ) );
    }




    public function getPlayerIdByName( $playerName )
    {
        return $this->provider->fetchScalar( "SELECT p.id FROM p_players p WHERE p.name='%s'", array(
            $playerName
        ) );
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
    public function getPlayerNameById( $playerId )
    {
        return $this->provider->fetchScalar( "SELECT p.name FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
    }

    public function getMessageListCount( $playerId, $inbox )
    {
        return $this->provider->fetchScalar( $inbox ? "SELECT COUNT(*) FROM p_msgs m WHERE m.to_player_id=%s AND m.delete_status!=1 AND m.to_archiv=0" : "SELECT COUNT(*) FROM p_msgs m WHERE m.from_player_id=%s AND m.delete_status!=2 AND m.form_archiv=0", array(
            $playerId
        ) );
    }

 public function getMessageList( $playerId, $inbox, $pageIndex, $pageSize )
    {
        return $this->provider->fetchResultSet( $inbox ? "SELECT  m.id, m.from_player_id uid, m.from_player_name uname, m.msg_title, m.msg_body, m.is_readed, DATE_FORMAT(m.creation_date, '%%y/%%m/%%d %%H:%%i') mdate FROM p_msgs m WHERE  m.to_player_id=%s AND m.delete_status!=1 AND m.to_archiv=0 ORDER BY m.creation_date DESC LIMIT %s,%s" : "SELECT  m.id, m.to_player_id uid, m.to_player_name uname, m.msg_title, m.msg_body, m.is_readed, DATE_FORMAT(m.creation_date, '%%y/%%m/%%d %%H:%%i') mdate FROM p_msgs m  WHERE  m.from_player_id=%s AND m.delete_status!=2 AND m.form_archiv=0 ORDER BY m.creation_date DESC LIMIT %s,%s", array(
            $playerId,
            $pageIndex * $pageSize,
            $pageSize
        ) );
    }
public function getMessageListCountT4( $playerId)
    {
        return $this->provider->fetchScalar("SELECT COUNT(*) FROM p_msgs m WHERE m.to_player_id=%s AND m.delete_status!=1 AND m.to_archiv=1", array(
            $playerId
        ) );
    }
    public function getMessageListT4( $playerId, $pageIndex, $pageSize )
    {
        return $this->provider->fetchResultSet("SELECT  m.id, m.from_player_id uid, m.from_player_name uname, m.msg_title, m.msg_body, m.is_readed, DATE_FORMAT(m.creation_date, '%%y/%%m/%%d %%H:%%i') mdate FROM p_msgs m WHERE  m.to_player_id=%s AND m.delete_status!=1 AND m.to_archiv=1 ORDER BY m.creation_date DESC LIMIT %s,%s", array(
            $playerId,
            $pageIndex * $pageSize,
            $pageSize
        ) );
    }

	
    public function getMessage( $playerId, $messageId )
    {
        return $this->provider->fetchResultSet( "SELECT  m.from_player_id, m.to_player_id, m.from_player_name, m.to_player_name, m.msg_title, m.msg_body, m.is_readed, m.delete_status, DATE_FORMAT(m.creation_date, '%%y/%%m/%%d') mdate, DATE_FORMAT(m.creation_date, '%%H:%%i:%%s') mtime FROM p_msgs m  WHERE  m.id=%s AND (m.from_player_id=%s OR m.to_player_id=%s) AND IF(m.to_player_id=%s, m.delete_status!=1, m.delete_status!=2)", array(
            $messageId,
            $playerId,
            $playerId,
            $playerId
        ) );
    }
	        
        public function getMessageAdmin( $messageId )
    {
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tm.from_player_id,\r\n\t\t\t\tm.to_player_id,\r\n\t\t\t\tm.from_player_name,\r\n\t\t\t\tm.to_player_name,\r\n\t\t\t\tm.msg_title,\r\n\t\t\t\tm.msg_body,\r\n\t\t\t\tm.is_readed,\r\n\t\t\t\tm.delete_status,\r\n\t\t\t\tDATE_FORMAT(m.creation_date, '%%y/%%m/%%d') mdate,\r\n\t\t\t\tDATE_FORMAT(m.creation_date, '%%H:%%i:%%s') mtime\r\n\t\t\tFROM p_msgs m \r\n\t\t\tWHERE \r\n\t\t\t\tm.id=%s", array(
            $messageId
        ) );
    }

    public function _getSafeMessage( $playerId, $messageId )
    {
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tm.to_player_id,\r\n\t\t\t\tm.is_readed,\r\n\t\t\t\tm.delete_status\r\n\t\t\tFROM p_msgs m \r\n\t\t\tWHERE \r\n\t\t\t\tm.id=%s AND (m.from_player_id=%s OR m.to_player_id=%s)", array(
            $messageId,
            $playerId,
            $playerId
        ) );
    }

    public function deleteMessage( $playerId, $messageId )
    {
        $result = $this->_getSafeMessage( $playerId, $messageId );
        if ( !$result->next( ) )
        {
            return FALSE;
        }
        $deleteStatus = $result->row['delete_status'];
        $toPlayerId = $result->row['to_player_id'];
        $isReaded = $result->row['is_readed'];
        $result->free( );
        if ( $deleteStatus != 0 )
        {
            $this->provider->executeQuery( "DELETE FROM p_msgs\r\n\t\t\t\tWHERE\r\n\t\t\t\t\tid=%s AND (from_player_id=%s OR to_player_id=%s)", array(
                $messageId,
                $playerId,
                $playerId
            ) );
        }
        else
        {
            $this->provider->executeQuery( "UPDATE p_msgs m\r\n\t\t\t\tSET\r\n\t\t\t\t\tm.delete_status=%s\r\n\t\t\t\tWHERE\r\n\t\t\t\t\tid=%s AND (from_player_id=%s OR to_player_id=%s)", array(
                $toPlayerId == $playerId ? 1 : 2,
                $messageId,
                $playerId,
                $playerId
            ) );
        }
        if ( !$isReaded && $toPlayerId == $playerId )
        {
            $this->changeUnReadedMessages( $playerId, 0 - 1 );
            return TRUE;
        }
        return FALSE;
    }

    public function markMessageAsReaded( $playerId, $messageId )
    {
        $this->provider->executeQuery( "UPDATE p_msgs m SET m.is_readed=1 WHERE m.id=%s", array(
            $messageId
        ) );
        $this->changeUnReadedMessages( $playerId, 0 - 1 );
    }

    public function markzMessageAsReaded( $playerId, $messageId )
    {
        $this->provider->executeQuery( "UPDATE p_msgs m SET m.is_readed=0 WHERE m.id=%s", array(
            $messageId
        ) );
        $this->provider->executeQuery( "UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.new_mail_count=new_mail_count+1\r\n\t\t\tWHERE\r\n\t\t\t\tp.id=%s", array(
            $playerId
        ) );
    }


    public function saveFriendList( $playerId, $friends )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.friend_players='%s' WHERE p.id=%s", array(
            $friends,
            $playerId
        ) );
    }

    public function sendMessage( $fromPlayerId, $fromPlayerName, $toPlayerId, $toPlayerName, $subject, $body )
    {
$aa = date('y/m/d H:i:s');

        $this->provider->executeQuery( "INSERT p_msgs\r\n\t\t\tSET\r\n\t\t\t\tfrom_player_id=%s,\r\n\t\t\t\tto_player_id=%s,\r\n\t\t\t\tfrom_player_name='%s',\r\n\t\t\t\tto_player_name='%s',\r\n\t\t\t\tmsg_title='%s',\r\n\t\t\t\tmsg_body='%s',\r\n\t\t\t\tcreation_date='".$aa."',\r\n\t\t\t\tis_readed=0", array(
            $fromPlayerId,
            $toPlayerId,
            $fromPlayerName,
            $toPlayerName,
            $subject,
            $body
        ) );
        $messageId = $this->provider->fetchScalar( "SELECT LAST_INSERT_ID() FROM p_msgs" );
        $this->changeUnReadedMessages( $toPlayerId, 1 );
        while ( 0 < ( $mid = $this->provider->fetchScalar( "SELECT MIN(m.id) id FROM p_msgs m WHERE m.delete_status!=2 AND m.from_player_id=%s GROUP BY m.from_player_id HAVING COUNT(*)>%s", array(
            $fromPlayerId,
            $this->maxMailBoxSize
        ) ) ) )
        {
            $this->deleteMessage( $fromPlayerId, $mid );
        }
        return $messageId;
    }

    public function changeUnReadedMessages( $playerId, $offset )
    {
        $this->provider->executeQuery( "UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.new_mail_count=IF((p.new_mail_count+%s)<0, 0, p.new_mail_count+%s)\r\n\t\t\tWHERE\r\n\t\t\t\tp.id=%s", array(
            $offset,
            $offset,
            $playerId
        ) );
    }

    public function getAlliancePlayersId( $allianceId )
    {
        return $this->provider->fetchScalar( "SELECT a.players_ids FROM p_alliances a WHERE a.id=%s", array(
            $allianceId
        ) );
    }
    public function getIdall( )
    {
        return $this->provider->fetchScalar( "SELECT id FROM p_players");
    }
	public function deletearchiv($id, $player_id, $reportId, $archiv)
    {
        $this->provider->executeQuery( "UPDATE p_msgs m SET m.%s=1,m.is_readed=1 WHERE m.id=%s AND m.%s=%s", array( $archiv, $reportId, $player_id, $id  ) );
    }
    public function syncMessages( $playerId )
    {
        $newCount = intval( $this->provider->fetchScalar( "SELECT\r\n\t\t\t\tCOUNT(*)\r\n\t\t\tFROM p_msgs m \r\n\t\t\tWHERE \r\n\t\t\t\tm.to_player_id=%s\r\n\t\t\t\tAND m.is_readed=0\r\n\t\t\t\tAND m.delete_status!=1", array(
            $playerId
        ) ) );
        if ( $newCount < 0 )
        {
            $newCount = 0;
        }
        $this->provider->executeQuery( "UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.new_mail_count=%s\r\n\t\t\tWHERE\r\n\t\t\t\tp.id=%s", array(
            $newCount,
            $playerId
        ) );
        return $newCount;
    }

}

?>