<?php

class VillageModel extends ModelBase
{

    public function getLatestReports( $playerId, $villageId )
    {
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tr.id,\r\n\t\t\t\tr.rpt_result,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%y/%%m/%%d') mdate,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%H:%%i') mtime,\r\n\t\t\t\tFALSE isAttack\r\n\t\t\tFROM p_rpts r\r\n\t\t\tWHERE\r\n\t\t\t\tr.to_player_id=%s\r\n\t\t\t\tAND r.to_village_id=%s\r\n\t\t\t\tAND (r.rpt_cat=3 OR (r.rpt_cat=4 AND r.rpt_result!=100))\r\n\t\t\tORDER BY r.creation_date DESC\r\n\t\t\tLIMIT 10", array(
            $playerId,
            $villageId
        ) );
    }


    public function getLatestReports2( $fromPlayerIds, $playerId, $villageId )
    {
        return $this->provider->fetchResultSet( "SELECT \r\n\t\t\t\tr.id,\r\n\t\t\t\tr.rpt_result,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%y/%%m/%%d') mdate,\r\n\t\t\t\tDATE_FORMAT(r.creation_date, '%%H:%%i') mtime,\r\n\t\t\t\tTRUE isAttack\r\n\t\t\tFROM p_rpts r\r\n\t\t\tWHERE\r\n\t\t\t\tr.to_player_id=%s\r\n\t\t\t\tAND r.to_village_id=%s\r\n\t\t\t\tAND r.from_player_id IN (%s)\r\n\t\t\t\tAND (r.rpt_cat=3 OR r.rpt_cat=4)\r\n\t\t\tORDER BY r.creation_date DESC\r\n\t\t\tLIMIT 10", array(
            $playerId,
            $villageId,
            $fromPlayerIds
        ) );
    }


    public function getLatestReports4( $fromPlayerIds, $villageId )
    {
        return $this->provider->fetchResultSet( "SELECT 
id,
rpt_result,
DATE_FORMAT(creation_date, '%%y/%%m/%%d') mdate,
DATE_FORMAT(creation_date, '%%H:%%i') mtime,
TRUE isAttack
FROM p_rpts
WHERE
to_village_id=%s
AND 
from_player_id IN (%s)
AND (rpt_cat=3 OR rpt_cat=4)
ORDER BY creation_date DESC
LIMIT 10", array(
            $villageId,
            $fromPlayerIds
        ) );
    }


    public function getLatestReports5( )
    {
        return $this->provider->fetchResultSet( "SELECT 
id,
rpt_result,
creation_date,
from_player_name,
from_player_id,
to_player_name,
to_player_id,
TRUE isAttack
FROM p_rpts
WHERE
(rpt_cat=3 OR rpt_cat=4)
ORDER BY creation_date DESC
LIMIT 20");
    }


    public function getAlliancePlayersId( $alliance_id )
    {
        return $this->provider->fetchScalar( "SELECT a.players_ids FROM p_alliances a WHERE a.id=%s", array(
            $alliance_id
        ) );
    }

    public function getPlayType( $player_id )
    {
        return $this->provider->fetchScalar( "SELECT p.player_type FROM p_players p WHERE p.id=%s", array(
            $player_id
        ) );
    }
    public function getPlayblocked( $player_id )
    {
        return $this->provider->fetchScalar( "SELECT blocked_time FROM p_players WHERE id=%s", array(
            $player_id
        ) );
    }

    public function getMapItemData( $villageId )
    {
        return $this->provider->fetchRow( "SELECT\r\n\t\t\t\tv.id,\r\n\t\t\t\tv.rel_x,is_artefacts, v.rel_y, v.field_maps_id, v.is_capital,\r\n\t\t\t\tv.image_num, v.tribe_id, v.player_id, v.alliance_id, v.parent_id, \r\n\t\t\t\tv.player_name, v.village_name, v.alliance_name,\r\n\t\t\t\tv.people_count, v.is_oasis, v.troops_num,\r\n\t\t\t\tv.allegiance_percent,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.creation_date)) elapsedTimeInSeconds\r\n\t\t\tFROM\r\n\t\t\t\tp_villages v \r\n\t\t\tWHERE\r\n\t\t\t\tv.id=%s", array(
            $villageId
        ) );
    }

    public function getVillageName( $villageId )
    {
        return $this->provider->fetchScalar( "SELECT v.village_name FROM p_villages v WHERE v.id=%s", array(
            $villageId
        ) );
    }

}

?>
