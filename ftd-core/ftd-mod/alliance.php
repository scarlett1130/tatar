<?php

class AllianceModel extends ModelBase
{


//Chat Team
	public function SendToChat($username, $id, $text, $alliance_id){
		$this->provider->executeQuery( "INSERT INTO `g_chat_alliance` SET `username` = '%s', `userid` = '%s', `date` = '%s', `text` = '%s', `alliance_id` = '%s';", array($username,$id,time(),$text,$alliance_id));
	}

	public function GetFromChat($alliance_id){
		return $this->provider->fetchResultSet( "SELECT * FROM `g_chat_alliance` WHERE alliance_id='$alliance_id' ORDER BY `ID` DESC LIMIT 50;" );
	}

	public function DeleteOldChat($alliance_id){
		$count = $this->provider->fetchScalar( "SELECT COUNT(*) FROM `g_chat_alliance` WHERE alliance_id='$alliance_id';" );
		if(50 < $count){
			$limit = $count - 50;
			$this->provider->executeQuery( "DELETE FROM `g_chat_alliance` ORDER BY `ID` ASC LIMIT %s ;", array( $limit ) );
		}
	}

//End Chat Team


public function GetNamePlayer( $id ) {
return $this->provider->fetchScalar( "SELECT v.name FROM p_players v WHERE v.id=%s", array( $id ) );
}
public function GetNameAlliance( $id ){
return $this->provider->fetchScalar( "SELECT a.name FROM p_alliances a WHERE a.id=%s", array( $id ) );
}


    public function getAllianceData( $allianceId )
    {
        return $this->provider->fetchRow( "SELECT \r\n\t\t\t\ta.*,\r\n\t\t\t\t(a.rating*100+a.player_count) score\r\n\t\t\tFROM p_alliances a \r\n\t\t\tWHERE a.id=%s", array( $allianceId ) );
    }


public function allianceExists( $allianceName ){
return 0 < intval( $this->provider->fetchScalar( "SELECT a.id FROM p_alliances a WHERE a.name='%s'", array( $allianceName ) ) );
}
public function editalliancename( $name, $name2, $id ){
$this->provider->executeQuery( "UPDATE p_alliances a SET a.%s='%s' WHERE a.id=%s", array( $name, $name2, $id ) );
if($name == 'name'){
$this->provider->executeQuery( "UPDATE p_players p SET p.alliance_name='%s' WHERE p.alliance_id=%s", array($name2, $id ) );
$this->provider->executeQuery( "UPDATE p_villages p SET p.alliance_name='%s' WHERE p.alliance_id=%s", array($name2, $id ) );
}
}


///////////////////////////////////////////////////


 public function getAllianceDataFor( $playerId )
 {
 return $this->provider->fetchRow( "SELECT 
 p.alliance_id,
 p.alliance_name
 FROM p_players p 
 WHERE p.id=%s", array(
 $playerId
 ) );
 }
 public function makeCat( $name,$allianceId )
 {
 return $this->provider->executeQuery( "INSERT INTO g_fcat(id,cata,catn,cato) VALUES (NULL,'".$allianceId."','".$name."',1)" );
 }
 public function catorder( $type,$cid )
 {
 if($type == 1)
 {
 $this->provider->executeQuery( "UPDATE g_fcat SET cato=(cato-1) WHERE id='".$cid."';" );
 }
 if($type == 2)
 {
 $this->provider->executeQuery( "UPDATE g_fcat SET cato=(cato+1) WHERE id='".$cid."';" );
 }
 if($type == 3)
 {
 $this->provider->executeQuery( "UPDATE g_fcat SET cata='-1' WHERE id='".$cid."';" );
 }
 }
 public function DeleteTID( $allianceId,$tid )
 {
 $this->provider->executeQuery( "DELETE FROM g_fposts WHERE id='%s' AND aid='%s'", array(
 $tid,
 $allianceId
 ) );
 }
 public function fpost( $type,$allianceId,$cid,$pid,$pname,$topic,$subject )
 {
 if($type == 2)
 {
 $this->provider->executeQuery( "UPDATE g_fposts SET timestamp='".time()."' WHERE id='".$cid."';" );
 }
 return $this->provider->executeQuery( "INSERT INTO g_fposts(id,type,aid,cid,pid,pname,Topic,Subject,date,timestamp) VALUES (NULL,'".$type."','".$allianceId."','".$cid."','".$pid."','".$pname."','".$topic."','".$subject."',NOW(),".time().")" );
 }
 public function getLatestReports( $playerIds, $type )
 {
 $expr = "";
 if ( $type == 1 )
 {
 $expr = sprintf( "r.from_player_id IN (%s) AND r.to_player_id IS NOT NULL", $playerIds );
 }
 else if ( $type == 2 )
 {
 $expr = sprintf( "(r.to_player_id IN (%s) AND IF(r.rpt_cat=4, r.rpt_result!=100,1)) AND r.from_player_id IS NOT NULL", $playerIds );
 }
 else
 {
 $expr = sprintf( "(r.from_player_id IN (%s) OR (r.to_player_id IN (%s) AND IF(r.rpt_cat=4, r.rpt_result!=100,1)))", $playerIds, $playerIds );
 }
 return $this->provider->fetchResultSet( "SELECT 
 r.id,
 r.from_player_id,
 r.to_player_id,
 r.from_player_name,
 r.to_player_name,
 r.rpt_result,
 r.rpt_cat,
 DATE_FORMAT(r.creation_date, '%%y/%%m/%%d %%H:%%i') mdate,
 (r.from_player_id IN(%s)) isAttack
 FROM p_rpts r
 WHERE
 (r.rpt_cat=3 OR r.rpt_cat=4)
 AND %s
 ORDER BY r.creation_date DESC
 LIMIT 50", array(
 $playerIds,
 $expr
 ) );
 }
 public function getCat( $allianceId )
 {
 return $this->provider->fetchResultSet( "SELECT *
 FROM g_fcat g 
 WHERE g.cata = '%s'
 ORDER BY g.cato ASC", array(
 $allianceId
 ) );
 }
 public function getPosts( $allianceId,$tid )
 {
 return $this->provider->fetchResultSet( "SELECT *
 FROM g_fposts g 
 WHERE g.type = '%s' AND g.aid = '%s' AND g.cid = '%s'
 ORDER BY g.id ASC", array(
 2,
 $allianceId,
 $tid
 ) );
 }
 public function getTopic( $allianceId,$tid )
 {
 return $this->provider->fetchResultSet( "SELECT *
 FROM g_fposts g 
 WHERE g.type = '%s' AND g.aid = '%s' AND g.id = '%s'
 ORDER BY g.id DESC", array(
 1,
 $allianceId,
 $tid
 ) );
 }
 public function getTopics( $catid,$allianceId )
 {
 return $this->provider->fetchResultSet( "SELECT *
 FROM g_fposts g 
 WHERE g.type = '%s' AND g.cid = '%s' AND g.aid = '%s'
 ORDER BY g.timestamp DESC", array(
 1,
 $catid,
 $allianceId
 ) );
 }
 public function getAlliancePlayers( $players_ids )
 {
 if ( trim( $players_ids ) == "" )
 {
 return NULL;
 }
 return $this->provider->fetchResultSet( "SELECT 
 p.id,
 p.name,
 p.total_people_count,
 p.color_name,
 p.alliance_roles,
 p.villages_count,
 floor(TIME_TO_SEC(TIMEDIFF(NOW(), p.last_login_date))/3600) lastLoginFromHours
 FROM p_players p 
 WHERE p.id IN (%s)
 ORDER BY p.total_people_count DESC, p.villages_count DESC", array(
 $players_ids
 ) );
 }
 public function getPlayerName( $playerId )
 {
 return $this->provider->fetchRow( "SELECT name FROM p_players WHERE id=%s", array(
 $playerId
 ) );
 }
 public function getAllianceRank( $allianceId, $score )
 {
 return $this->provider->fetchScalar( "SELECT (
 (SELECT
 COUNT(*)
 FROM p_alliances a
 WHERE 
 (a.rating*100+a.player_count)>%s)
 +
 (SELECT 
 COUNT(*)
 FROM p_alliances a
 WHERE 
 (a.rating*100+a.player_count)=%s
 AND a.id<%s)
 ) + 1 rank", array(
 $score,
 $score,
 $allianceId
 ) );
 }
 public function editAllianceData( $allianceId, $data, $playersIds )
 {
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.name='%s', a.name2='%s', a.description1='%s', a.description2='%s' WHERE a.id=%s", array(
 $data['name'],
 $data['name2'],
 $data['description1'],
 $data['description2'],
 $allianceId
 ) );
 $this->provider->executeQuery( "UPDATE p_players p SET p.alliance_name='%s' WHERE p.id IN(%s)", array(
 $data['name'],
 $playersIds
 ) );
 $this->provider->executeQuery( "UPDATE p_villages v SET v.alliance_name='%s' WHERE v.player_id IN(%s)", array(
 $data['name'],
 $playersIds
 ) );
 }
 public function removeFromAlliance( $playerId, $allianceId, $playersIds, $playersCount )
 {
 $this->provider->executeQuery( "UPDATE p_players p SET p.alliance_id=NULL, p.alliance_name=NULL, p.alliance_roles=NULL WHERE p.id=%s", array(
 $playerId
 ) );
 $this->provider->executeQuery( "UPDATE p_villages v SET v.alliance_id=NULL, v.alliance_name=NULL WHERE v.player_id=%s", array(
 $playerId
 ) );
 if ( trim( $playersIds ) != "" )
 {
 $playersIdsArr = explode( ",", $playersIds );
 $playersIds = "";
 $i = 0;
 $c = sizeof( $playersIdsArr ) - 1;
 for ($i = 0; $i <= $c; $i++)
 {
 if ( $playersIdsArr[$i] == $playerId )
 {
 continue;
 }
 if ( $playersIds != "" )
 {
 $playersIds .= ",";
 }
 $playersIds .= $playersIdsArr[$i];
 }
 }
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.player_count=a.player_count-1, a.players_ids='%s' WHERE a.id=%s", array(
 $playersIds,
 $allianceId
 ) );
 if ( $playersCount == 1 )
 {
 $this->provider->executeQuery( "DELETE FROM p_alliances WHERE id=%s", array(
 $allianceId
 ) );
 }
 return $playersIds;
 }
 public function getPlayerAllianceRole( $playerId )
 {
 return $this->provider->fetchRow( "SELECT p.name, p.alliance_roles FROM p_players p WHERE p.id=%s", array(
 $playerId
 ) );
 }
 public function setPlayerAllianceRole( $playerId, $roleName, $roleNumber )
 {
 return $this->provider->executeQuery( "UPDATE p_players p SET p.alliance_roles='%s' WHERE p.id=%s", array(
 $roleNumber." ".$roleName,
 $playerId
 ) );
 }
 public function getPlayerId( $playerName )
 {
 return $this->provider->fetchScalar( "SELECT p.id FROM p_players p WHERE p.name='%s'", array(
 $playerName
 ) );
 }
 public function _getNewInvite( $invitesString, $removeId )
 {
 if ( $invitesString == "" )
 {
 return "";
 }
 $result = "";
 $arr = explode( "\n", $invitesString );
 foreach ( $arr as $invite )
 {
 list( $id, $name ) = explode( " ", $invite, 2 );
 if ( $id == $removeId )
 {
 continue;
 }
 if ( $result != "" )
 {
 $result .= "\n";
 }
 $result .= $id." ".$name;
 }
 return $result;
 }
 public function removeAllianceInvites( $playerId, $allianceId )
 {
 $pRow = $this->provider->fetchRow( "SELECT p.name, p.invites_alliance_ids FROM p_players p WHERE p.id=%s", array(
 $playerId
 ) );
 $aRow = $this->provider->fetchRow( "SELECT a.name, a.invites_player_ids FROM p_alliances a WHERE a.id=%s", array(
 $allianceId
 ) );
 $pInvitesStr = $this->_getNewInvite( trim( $pRow['invites_alliance_ids'] ), $allianceId );
 $aInvitesStr = $this->_getNewInvite( trim( $aRow['invites_player_ids'] ), $playerId );
 $this->provider->executeQuery( "UPDATE p_players p SET p.invites_alliance_ids='%s' WHERE p.id=%s", array(
 $pInvitesStr,
 $playerId
 ) );
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.invites_player_ids='%s' WHERE a.id=%s", array(
 $aInvitesStr,
 $allianceId
 ) );
 }
 public function addAllianceInvites( $playerId, $allianceId )
 {
 $pRow = $this->provider->fetchRow( "SELECT p.name, p.invites_alliance_ids FROM p_players p WHERE p.id=%s", array(
 $playerId
 ) );
 $aRow = $this->provider->fetchRow( "SELECT a.name, a.invites_player_ids FROM p_alliances a WHERE a.id=%s", array(
 $allianceId
 ) );
 $pInvitesStr = $pRow['invites_alliance_ids'];
 if ( $pInvitesStr != "" )
 {
 $pInvitesStr .= "\n";
 }
 $pInvitesStr .= $allianceId." ".$aRow['name'];
 $aInvitesStr = $aRow['invites_player_ids'];
 if ( $aInvitesStr != "" )
 {
 $aInvitesStr .= "\n";
 }
 $aInvitesStr .= $playerId." ".$pRow['name'];
 $this->provider->executeQuery( "UPDATE p_players p SET p.invites_alliance_ids='%s' WHERE p.id=%s", array(
 $pInvitesStr,
 $playerId
 ) );
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.invites_player_ids='%s' WHERE a.id=%s", array(
 $aInvitesStr,
 $allianceId
 ) );
 }
 public function removeAllianceContracts( $allianceId1, $allianceId2 )
 {
 $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId1
 ) );
 $contracts_alliance_id2 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId2
 ) );
 $contracts1 = "";
 if ( trim( $contracts_alliance_id1 ) != "" )
 {
 $arr = explode( ",", $contracts_alliance_id1 );
 foreach ( $arr as $arrStr )
 {
 list( $aid, $aStatus ) = explode( " ", $arrStr );
 if ( $aid == $allianceId2 )
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
 $contracts2 = "";
 if ( trim( $contracts_alliance_id2 ) != "" )
 {
 $arr = explode( ",", $contracts_alliance_id2 );
 foreach ( $arr as $arrStr )
 {
 list( $aid, $aStatus ) = explode( " ", $arrStr );
 if ( $aid == $allianceId1 )
 {
 continue;
 }
 if ( $contracts2 != "" )
 {
 $contracts2 .= ",";
 }
 $contracts2 .= $arrStr;
 }
 }
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array(
 $contracts1,
 $allianceId1
 ) );
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array(
 $contracts2,
 $allianceId2
 ) );
 }







 public function removeAlliancewar( $allianceId1, $allianceId2 )
 {
 $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT a.war_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId1
 ) );
 $contracts1 = "";
 if ( trim( $contracts_alliance_id1 ) != "" )
 {
 $arr = explode( ",", $contracts_alliance_id1 );
 foreach ( $arr as $arrStr )
 {
 list( $aid, $aStatus ) = explode( " ", $arrStr );
 if ( $aid == $allianceId2 )
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
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.war_alliance_id='%s' WHERE a.id=%s", array(
 $contracts1,
 $allianceId1
 ) );
 }









 public function acceptAllianceContracts( $allianceId1, $allianceId2 )
 {
 $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId1
 ) );
 $contracts_alliance_id2 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId2
 ) );
 $contracts1 = "";
 if ( trim( $contracts_alliance_id1 ) != "" )
 {
 $arr = explode( ",", $contracts_alliance_id1 );
 foreach ( $arr as $arrStr )
 {
 list( $aid, $aStatus ) = explode( " ", $arrStr );
 if ( $aid == $allianceId2 )
 {
 $aStatus = 0;
 }
 if ( $contracts1 != "" )
 {
 $contracts1 .= ",";
 }
 $contracts1 .= $aid." ".$aStatus;
 }
 }
 $contracts2 = "";
 if ( trim( $contracts_alliance_id2 ) != "" )
 {
 $arr = explode( ",", $contracts_alliance_id2 );
 foreach ( $arr as $arrStr )
 {
 list( $aid, $aStatus ) = explode( " ", $arrStr );
 if ( $aid == $allianceId1 )
 {
 $aStatus = 0;
 }
 if ( $contracts2 != "" )
 {
 $contracts2 .= ",";
 }
 $contracts2 .= $aid." ".$aStatus;
 }
 }
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array(
 $contracts1,
 $allianceId1
 ) );
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array(
 $contracts2,
 $allianceId2
 ) );
 }
 public function addAllianceContracts( $allianceId1, $allianceId2 )
 {
 $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId1
 ) );
 $contracts_alliance_id2 = $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId2
 ) );
 $contracts1 = $contracts_alliance_id1;
 if ( $contracts1 != "" )
 {
 $contracts1 .= ",";
 }
 $contracts1 .= $allianceId2." 1";
 $contracts2 = $contracts_alliance_id2;
 if ( $contracts2 != "" )
 {
 $contracts2 .= ",";
 }
 $contracts2 .= $allianceId1." 2";
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array(
 $contracts1,
 $allianceId1
 ) );
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.contracts_alliance_id='%s' WHERE a.id=%s", array(
 $contracts2,
 $allianceId2
 ) );
 }









 public function addAllianceWars( $allianceId1, $allianceId2 )
 {
 $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT a.war_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId1
 ) );
 $contracts_alliance_id2 = $this->provider->fetchScalar( "SELECT a.war_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId2
 ) );
 $contracts1 = $contracts_alliance_id1;
 if ( $contracts1 != "" )
 {
 $contracts1 .= ",";
 }
 $contracts1 .= $allianceId2."";
 $contracts2 = $contracts_alliance_id2;
 if ( $contracts2 != "" )
 {
 $contracts2 .= ",";
 }
 $contracts2 .= $allianceId1."";
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.war_alliance_id='%s' WHERE a.id=%s", array(
 $contracts1,
 $allianceId1
 ) );
 }




 public function addAllianceWars2( $allianceId1, $allianceId2 )
 {
 $contracts_alliance_id1 = $this->provider->fetchScalar( "SELECT a.war_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId1
 ) );
 $contracts_alliance_id2 = $this->provider->fetchScalar( "SELECT a.war_alliance_id FROM p_alliances a WHERE a.id=%s", array(
 $allianceId2
 ) );
 $contracts1 = $contracts_alliance_id1;
 if ( $contracts1 != "" )
 {
 $contracts1 .= ",";
 }
 $contracts1 .= $allianceId2."";
 $contracts2 = $contracts_alliance_id2;
 if ( $contracts2 != "" )
 {
 $contracts2 .= ",";
 }
 $contracts2 .= $allianceId1."";
 $this->provider->executeQuery( "UPDATE p_alliances a SET a.war_alliance_id='%s' WHERE a.id=%s", array(
 $contracts1,
 $allianceId1
 ) );
 }












 public function getAllianceId( $allianceName )
 {
 return $this->provider->fetchScalar( "SELECT a.id FROM p_alliances a WHERE a.name='%s'", array(
 $allianceName
 ) );
 }
 public function getAllianceName( $allianceId )
 {
 return $this->provider->fetchScalar( "SELECT a.name FROM p_alliances a WHERE a.id=%s", array(
 $allianceId
 ) );
 }
}
?>
