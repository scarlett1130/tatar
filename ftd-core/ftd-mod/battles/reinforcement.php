<?php

class ReInforcementBattleModel extends BattleModel
{
    public function handleReInforcement( $taskRow, $toVillageRow, $fromVillageRow, $procInfo, $troopsArrStr )
    {
        if ( $procInfo['troopBack'] )
        {
            if ( 0 < intval( $toVillageRow['player_id'] ) && $taskRow['to_player_id'] == intval( $toVillageRow['player_id'] ) )
            {
                $paramsArray = explode( "|", $taskRow['proc_params'] );
                $res = array( 0, 0, 0, 0 );
                if ( trim( $paramsArray[4] ) != "" )
                {
                    $res = explode( " ", $paramsArray[4] );
                }
                $k = 0;
                $r_arr = explode( ",", $toVillageRow['resources'] );
                foreach ( $r_arr as $r_str )
                {
                    $r2 = explode( " ", $r_str );
                    $resources[$r2[0]] = array(
                        "current_value" => $r2[2] < $r2[1] + $res[$k] ? $r2[2] : $r2[1] + $res[$k],
                        "store_max_limit" => $r2[2],
                        "store_init_limit" => $r2[3],
                        "prod_rate" => $r2[4],
                        "prod_rate_percentage" => $r2[5]
                    );
                    ++$k;
                }
                $resourcesStr = "";
                foreach ( $resources as $k => $v )
                {
                    if ( $resourcesStr != "" )
                    {
                        $resourcesStr .= ",";
                    }
                    $resourcesStr .= sprintf( "%s %s %s %s %s %s", $k, $v['current_value'], $v['store_max_limit'], $v['store_init_limit'], $v['prod_rate'], $v['prod_rate_percentage'] );
                }
                $this->provider->executeQuery( "UPDATE p_villages v  SET v.troops_num='%s', v.resources='%s' WHERE v.id=%s", array(
                    $this->_getNewTroops( $toVillageRow['troops_num'], $procInfo['troopsArray'], 0 - 1, $toVillageRow['player_id'] == $fromVillageRow['player_id'] && !$toVillageRow['is_oasis'] ),
                    $resourcesStr,
                    intval( $toVillageRow['id'] )
                ) );
                if ( $procInfo['troopsArray']['hasHero'] )
                {
                    $this->provider->executeQuery( "UPDATE p_players p SET p.hero_in_village_id=%s WHERE p.id=%s", array(
                        intval( $toVillageRow['id'] ),
                        intval( $toVillageRow['player_id'] )
                    ) );
                }
            }
            else if ( $procInfo['troopsArray']['hasHero'] )
            {
                $this->provider->executeQuery( "UPDATE p_players p SET p.hero_in_village_id=NULL, hero_troop_id=NULL WHERE p.id=%s", array(
                    intval( $toVillageRow['player_id'] )
                ) );
            }
        }
        else
        {
            if ( 0 < intval( $toVillageRow['player_id'] )  AND  0 < intval( $fromVillageRow['player_id'] )  )
            {
                $allegiance_percent = intval( $toVillageRow['allegiance_percent'] );
                if ( $toVillageRow['is_oasis'] && $allegiance_percent < 100 )
                {
                    $allegiance_percent += 15;
                    if ( 100 < $allegiance_percent )
                    {
                        $allegiance_percent = 100;
                    }
                    $this->provider->executeQuery( "UPDATE p_villages v SET v.allegiance_percent=%s WHERE v.id=%s", array(
                        $allegiance_percent,
                        intval( $toVillageRow['id'] )
                    ) );
                }
                $affectCropConsumption = TRUE;
                if ( $toVillageRow['is_oasis'] && trim( $fromVillageRow['village_oases_id'] ) != "" )
                {
                    $oArr = explode( ",", trim( $fromVillageRow['village_oases_id'] ) );
                    foreach ( $oArr as $oid )
                    {
                        if ( !( $oid == $taskRow['to_village_id'] ) )
                        {
                            continue;
                        }
                        //$affectCropConsumption = FALSE;
                        break;
                        break;
                    }
                }
                $this->_addTroopsToVillage( $toVillageRow, $fromVillageRow, $procInfo['troopsArray'], $affectCropConsumption );
                if ( $procInfo['troopsArray']['hasHero'] && $toVillageRow['player_id'] == $fromVillageRow['player_id'] && !$toVillageRow['is_oasis'] )
                {
                    $this->provider->executeQuery( "UPDATE p_players p SET p.hero_in_village_id=%s WHERE p.id=%s", array(
                        intval( $toVillageRow['id'] ),
                        intval( $toVillageRow['player_id'] )
                    ) );
                }
                $timeInSeconds = $taskRow['remainingTimeInSeconds'];
                $reportResult = 8;
                $reportCategory = 2;
                $troopsCropConsumption = $procInfo['troopsArray']['cropConsumption'];
                $reportBody = $troopsArrStr."|".$troopsCropConsumption;
                $r = new ReportModel( );
                $r->createReport( intval( $taskRow['player_id'] ), intval( $taskRow['to_player_id'] ), intval( $taskRow['village_id'] ), intval( $taskRow['to_village_id'] ), $reportCategory, $reportResult, $reportBody, $timeInSeconds );
                return FALSE;
            }
            $paramsArray = explode( "|", $taskRow['proc_params'] );
            $paramsArray[sizeof( $paramsArray ) - 1] = 1;
            $newParams = implode( "|", $paramsArray );
            $this->provider->executeQuery( "UPDATE p_queue q \r\n\t\t\t\t\tSET \r\n\t\t\t\t\t\tq.player_id=%s,\r\n\t\t\t\t\t\tq.village_id=%s,\r\n\t\t\t\t\t\tq.to_player_id=%s,\r\n\t\t\t\t\t\tq.to_village_id=%s,\r\n\t\t\t\t\t\tq.proc_type=%s,\r\n\t\t\t\t\t\tq.proc_params='%s',\r\n\t\t\t\t\t\tq.end_date=(q.end_date + INTERVAL q.execution_time SECOND)\r\n\t\t\t\t\tWHERE q.id=%s", array(
                intval( $taskRow['to_player_id'] ),
                intval( $taskRow['to_village_id'] ),
                intval( $taskRow['player_id'] ),
                intval( $taskRow['village_id'] ),
                QS_WAR_REINFORCE,
                $newParams,
                intval( $taskRow['id'] )
            ) );
            return TRUE;
        }
        return FALSE;
    }
    public function _addTroopsToVillage( $toVillageRow, $fromVillageRow, $addTroopsArray, $affectCropConsumption )
    {
        $troopsCropConsume = $affectCropConsumption ? $addTroopsArray['cropConsumption'] : 0;
        $t = $toVillageRow['player_id'] == $fromVillageRow['player_id'] && !$toVillageRow['is_oasis'] && !$fromVillageRow['is_oasis'] && $addTroopsArray['onlyHero'] ? $toVillageRow['troops_num'] : $this->_getNewTroops( $toVillageRow['troops_num'], $addTroopsArray, $fromVillageRow['id'], $toVillageRow['player_id'] == $fromVillageRow['player_id'] && !$toVillageRow['is_oasis'] && !$fromVillageRow['is_oasis'] );
        if ( !$toVillageRow['is_oasis'] )
        {
            $this->provider->executeQuery( "UPDATE p_villages v SET v.troops_num='%s', v.crop_consumption=v.crop_consumption+%s WHERE v.id=%s", array(
                $t,
                $troopsCropConsume,
                intval( $toVillageRow['id'] )
            ) );
        }
        else if ($toVillageRow['is_oasis'] && $toVillageRow['player_id'] == $this->player->playerId) {
                  $this->provider->executeQuery( "UPDATE p_villages v SET v.troops_num='%s', v.crop_consumption=v.crop_consumption+%s WHERE v.id=%s", array(
                $t,
                $troopsCropConsume,
                intval( $toVillageRow['id'] )
            ) );
        }else
        {
            $this->provider->executeQuery( "UPDATE p_villages v SET v.crop_consumption=v.crop_consumption+%s WHERE v.id=%s", array(
                $troopsCropConsume,
                intval( $toVillageRow['parent_id'] )
            ) );
            $this->provider->executeQuery( "UPDATE p_villages v SET v.troops_num='%s' WHERE v.id=%s", array(
                $t,
                intval( $toVillageRow['id'] )
            ) );
        }
            foreach ($addTroopsArray['troops'] as $tid=>$tnum) {
            if ( $tid == 9 || $tid == 10 || $tid == 19 || $tid == 29 || $tid == 59 || $tid == 69 || $tid == 79 || $tid == 49|| $tid == 108)
                {
                    $kingIsLive = $tnum > 0;
                }
            }
$x = $toVillageRow['player_id'] == $fromVillageRow['player_id'] && !$toVillageRow['is_oasis'];
if (!$x) {
        $t = $toVillageRow['player_id'] == $fromVillageRow['player_id'] && !$toVillageRow['is_oasis'] && $addTroopsArray['onlyHero'] ? $fromVillageRow['troops_out_num'] : $this->_getNewTroops( $fromVillageRow['troops_out_num'], $addTroopsArray, $toVillageRow['id'], $toVillageRow['player_id'] == $fromVillageRow['player_id'] && !$toVillageRow['is_oasis'] );
        $this->provider->executeQuery( "UPDATE p_villages v  SET v.troops_out_num='%s', v.crop_consumption=v.crop_consumption-%s WHERE v.id=%s", array(
            $t,
            $troopsCropConsume,
            intval( $fromVillageRow['id'] )
        ) );
       }else {
        $this->provider->executeQuery( "UPDATE p_villages v  SET v.crop_consumption=v.crop_consumption-%s WHERE v.id=%s", array(
          
            $troopsCropConsume,
            intval( $fromVillageRow['id'] )
        ) );

}
                                                 $this->_updateVillage ($toVillageRow, 0, FALSE);
 							$this->_updateVillage ($fromVillageRow, 0, FALSE);
    }

}

?>