<?php

class SpyBattleModel extends BattleModel{
        function handleWarSpy ($taskRow, $toVillageRow, $fromVillageRow, $procInfo) {
                global $GameMetadata;
                
                if (intval ($toVillageRow['player_id']) == 0) {
                        $paramsArray = explode ('|', $taskRow['proc_params']);
                        $paramsArray[sizeof ($paramsArray) - 1] = 1;
                        $newParams = implode ('|', $paramsArray);
                        
                        // generate troops back task
                        $this->provider->executeQuery (
                                'UPDATE p_queue q 
                                SET 
                                        q.player_id=%s,
                                        q.village_id=%s,
                                        q.to_player_id=%s,
                                        q.to_village_id=%s,
                                        q.proc_type=%s,
                                        q.proc_params=\'%s\',
                                        q.end_date=(q.end_date + INTERVAL q.execution_time SECOND)
                                WHERE q.id=%s',
                                array (
                                        intval ($taskRow['to_player_id']), intval ($taskRow['to_village_id']),
                                        intval ($taskRow['player_id']), intval ($taskRow['village_id']),
                                        QS_WAR_REINFORCE, $newParams, intval( $taskRow['id'] )
                                )
                        );
                        return TRUE;
                }
                
                //TODO:
                // check for minus crop  production
                
                // get the attack troops
                $attackTroops = $this->_getAttackTroopsForVillage ($fromVillageRow['troops_training'], $procInfo['troopsArray']['troops'], 0, 0, TRUE);
                
                // get the defense troops                
                $defenseTroops = array ();
                $totalDefensePower = 0;
                $troops_num = trim ($toVillageRow['troops_num']);
                if ($troops_num != '') {
                        $vtroopsArr = explode ('|', $troops_num);
                        foreach ($vtroopsArr as $vtroopsStr) {
                                list ($tvid, $tvtroopsStr) = explode (':', $vtroopsStr);
                                $incFactor = ($toVillageRow['is_oasis'] && intval ($toVillageRow['player_id']) == 0 && $tvid==-1)? floor ($toVillageRow['oasisElapsedTimeInSeconds']/86400) : 0;        // increase the troop number for the empty oasis ( 1 troop per 1 day )

                                $_hasHero = FALSE;
                                $vtroops = array ();
                                $_arr = explode (',', $tvtroopsStr);
                                foreach ($_arr as $_arrStr) {
                                        list ($_tid, $_tnum) = explode (' ', $_arrStr);
                                        if($_tnum == -1) {
                                                $_hasHero = TRUE;
                                        } else {
                                                $vtroops[$_tid] = $_tnum + $incFactor;
                                        }
                                }

                                if ($tvid==-1) {
                                        $hero_in_village_id = intval ($this->provider->fetchScalar ('SELECT p.hero_in_village_id FROM p_players p WHERE p.id=%s', array (intval ($toVillageRow['player_id']))));
                                        if ($hero_in_village_id > 0 && $hero_in_village_id == $toVillageRow['id']) {
                                                $_hasHero = TRUE;
                                        }
                                }
                                
                                $defenseTroops[$tvid] = $this->_getDefenseTroopsForVillage (($tvid==-1? $toVillageRow['id'] : $tvid), $vtroops, $_hasHero, 0, TRUE);
                                $totalDefensePower += $defenseTroops[$tvid]['total_spy_power'];
                        }
                }
                
                $warResult = $this->_getSpyResult ($attackTroops, $defenseTroops, $totalDefensePower, $fromVillageRow);

                // update from village crop consumption
                $reduceConsumption = $warResult['attackTroops']['total_dead_consumption'];
                if ($reduceConsumption > 0) {
                        $this->_updateVillage ($fromVillageRow, $reduceConsumption, FALSE);
                }

                // update to village
                $defenseTroopsStr = '';
                $defenseReduceConsumption = 0;
                $reportTroopTable = array ();
                $tribeId = 0;
                foreach ($warResult['defenseTroops'] as $vid=>$troopsTable) {
                        $defenseReduceConsumption += $troopsTable['total_dead_consumption'];
                        
                        $newTroops = '';
                        $thisInforcementDied = TRUE;
                        foreach ($troopsTable['troops'] as $tid=>$tprop) {
                                if ($newTroops != '') { $newTroops .= ','; }
                                
                                $newTroops .= $tid . ' ' . $tprop['live_number'];
                                if ($tprop['live_number'] > 0) {
                                        $thisInforcementDied = FALSE;
                                }
                                
                                if ($tid != 99) {
                                        $tribeId = $GameMetadata['troops'][$tid]['for_tribe_id'];
                                        if (!isset ($reportTroopTable[$tribeId])) {
                                                $reportTroopTable[$tribeId] = array (
                                                        'troops' => array(),
                                                        'hero' => array (
                                                                'number' => 0,
                                                                'dead_number' => 0
                                                        )
                                                );
                                        }
                                        
                                        if (!isset ($reportTroopTable[$tribeId]['troops'][$tid])) {
                                                $reportTroopTable[$tribeId]['troops'][$tid] = array (
                                                        'number' => $tprop['number'],
                                                        'dead_number' => $tprop['number'] - $tprop['live_number']
                                                );
                                        } else {
                                                $reportTroopTable[$tribeId]['troops'][$tid]['number'] += $tprop['number'];
                                                $reportTroopTable[$tribeId]['troops'][$tid]['dead_number'] += ($tprop['number'] - $tprop['live_number']);
                                        }
                                }
                        }
                        if ($troopsTable['hasHero']) {
                                $reportTroopTable[$tribeId]['hero']['number']++;
                                if ( $vid != -1){
                                        if ($newTroops != '') { $newTroops .= ','; }
                                        $newTroops .= $troopsTable['heroTroopId'] . ' -1';
                                }
                                $thisInforcementDied = FALSE;
                        }
                        
                        $this->_updateVillageOutTroops ($vid, $toVillageRow['id'], $newTroops, ($troopsTable['hasHero'] && $troopsTable['total_live_number'] <= 0), $thisInforcementDied, intval ($toVillageRow['player_id']));
                        
                        if ($vid == -1 && $toVillageRow['is_oasis']) {
                                $this->provider->executeQuery ('UPDATE p_villages v SET v.creation_date=NOW() WHERE v.id=%s', array (intval($toVillageRow['id'])));
                        }
                        
                        if (!$thisInforcementDied || $vid == -1) {
                                if ($defenseTroopsStr != '') { $defenseTroopsStr .= '|'; }
                                $defenseTroopsStr .= $vid . ':' . $newTroops;
                        }
                }
                if ($toVillageRow['is_oasis'] && intval ($toVillageRow['player_id']) > 0 && isset ($reportTroopTable[4])) {
                        unset ($reportTroopTable[4]);
                }
                
                $this->provider->executeQuery ('UPDATE p_villages v SET v.troops_num=\'%s\' WHERE v.id=%s', array ($defenseTroopsStr, intval($toVillageRow['id'])));
                if (!($toVillageRow['is_oasis'] && intval ($toVillageRow['player_id']) == 0)) {
                        $_tovid = ($toVillageRow['is_oasis'])? intval ($toVillageRow['parent_id']) : $toVillageRow['id'];
                        // TODO : more accurate later
                        $this->provider->executeQuery ('UPDATE p_villages v SET v.crop_consumption=v.crop_consumption-%s WHERE v.id=%s', array ($defenseReduceConsumption, intval($_tovid)));
                }

                // -----------------------------------------------------------
                // generate report
                $newTroops = '';
                foreach ($warResult['attackTroops']['troops'] as $tid=>$tprop) {
                        if ($newTroops != '') { $newTroops .= ','; }
                        $newTroops .= $tid . ' ' . $tprop['number'] . ' ' . ($tprop['number']-$tprop['live_number']);
                }
                if ($procInfo['troopsArray']['hasHero']) {
                        if ($newTroops != '') { $newTroops .= ','; }
                        $newTroops .= -1 . ' ' . 1 . ' ' . ($warResult['all_attack_killed']? 1:0);
                }
                $attackReportTroops = $newTroops;
                
                $defenseReportTroops = '';
                foreach ($reportTroopTable as $tribeId=>$defTroops) {
                        $defenseReportTroops1 = '';
                        foreach ($defTroops['troops'] as $tid=>$tArr) {
                                if ($defenseReportTroops1 != '') { $defenseReportTroops1 .= ','; }
                                $defenseReportTroops1 .= $tid . ' ' . $tArr['number'] . ' ' . $tArr['dead_number'];
                        }
                        
                        if ($defTroops['hero']['number'] > 0) {
                                if ($defenseReportTroops1 != '') { $defenseReportTroops1 .= ','; }
                                $defenseReportTroops1 .= -1 . ' ' . $defTroops['hero']['number'] . ' ' . $defTroops['hero']['dead_number'];
                        }
                        
                        if ($defenseReportTroops1 != '') {
                                if ($defenseReportTroops != '') { $defenseReportTroops .= '#'; }
                                $defenseReportTroops .= $defenseReportTroops1;
                        }
                }

                // get the harvest info
                $harvestInfo = '';
                $harvestResources = '';
                $spyType = $procInfo['spyAction'];
                if (!$warResult['all_spy_killed']) {
                        // get the resources info
                        if ($spyType == 1) {
                                $harvestResources = '0 0 0 0';
                                if (!$toVillageRow['is_oasis']) {
                                        $resources_info = array ();
                                        $r_arr = explode (',', $toVillageRow['resources']);
                                        foreach( $r_arr as $r_str ) {
                                                $r2 = explode( ' ', $r_str );

                                                $prate                                 = floor( $r2[4] * ( 1 + $r2[5]/100 ) ) - (($r2[0]==4)? $toVillageRow['crop_consumption'] : 0);
                                                $current_value         = floor ($r2[1] + $toVillageRow['elapsedTimeInSeconds'] * ($prate/3600));
                                                if ($current_value > $r2[2]) {
                                                        $current_value = $r2[2];
                                                }

                                                $resources_info[] = $current_value;
                                        }
                                        
                                        $harvestResources = implode (' ', $resources_info);
                                }
                        }

                        // get the building spy info
                        if ($spyType == 2) {
                                if (!$toVillageRow['is_oasis']) {                                        
                                        $buildingsInfo = array ();
                                        $bStr = trim ($toVillageRow['buildings']);

                                                                $buildingsInfo .= ',000 ' . $toVillageRow['allegiance_percent'];
                                        if ($bStr != '') {
                                                $bStrArr = explode (',', $bStr);
                                                $_i = 0;
                                                foreach ($bStrArr as $b2Str) {
                                                        $_i++; if ($_i < 19) continue;
                                                        
                                                        list ($item_id, $level, $update_state) = explode (' ', $b2Str);
// || $item_id == 26 || $item_id == 27 || $item_id == 33 || $item_id == 32 || item_id == 31 
        if ($item_id == 25){ 
                                                        if ($level > 0) {
                                                                $buildingsInfo .= ','.$item_id . ' ' . $level;
                                                        }
                                                        }


        if ($item_id == 26){ 
                                                        if ($level > 0) {
                                                                $buildingsInfo .= ','.$item_id . ' ' . $level;
                                                        }
                                                        }



        if ($item_id == 27){ 
                                                        if ($level > 0) {
                                                                $buildingsInfo .= ','.$item_id . ' ' . $level;
                                                        }
                                                        }


        if ($item_id == 33){ 
                                                        if ($level > 0) {
                                                                $buildingsInfo .= ','.$item_id . ' ' . $level;
                                                        }
                                                        }

        if ($item_id == 32){ 
                                                        if ($level > 0) {
                                                                $buildingsInfo .= ','.$item_id . ' ' . $level;
                                                        }
                                                        }
        if ($item_id == 31){ 
                                                        if ($level > 0) {
                                                                $buildingsInfo .= ','.$item_id . ' ' . $level;
                                                        }
                                                        }


                                                }
                                        }
                                        
                                        if (sizeof ($buildingsInfo) > 0) {
                                                $_randIndex = mt_rand (0, sizeof ($buildingsInfo)-1);
                                                
                                                $harvestInfo = $buildingsInfo;
                                        }
                                }
                        }
                } else {
                        $spyType = 3;
                }
                
                $timeInSeconds = $taskRow['remainingTimeInSeconds'];
                if (!$warResult['defense_has_spytroops']) {
                        $reportResult = 100;
                } else {
                        $reportResult = ($warResult['all_spy_killed'])? 9 : 10;
                }
                $reportCategory = 4;
                $reportBody = $attackReportTroops . '|' . $defenseReportTroops . '|'  . $harvestResources . '|' . $harvestInfo . '|' . $spyType;        // attack troops | defense troops | harvest resources | harvest info | $spyType
                $r = new ReportModel ();
                $reportId = $r->createReport (intval ($fromVillageRow['player_id']), intval ($toVillageRow['player_id']), intval ($fromVillageRow['id']), intval ($toVillageRow['id']), $reportCategory, $reportResult, $reportBody, $timeInSeconds);
                
                // delete the spy report for villages that has no spy troops
                if (!$warResult['defense_has_spytroops'] && ($toVillageRow['player_id'] != $fromVillageRow['player_id']) ) {
                        $r->deleteReport (intval ($taskRow['to_player_id']), $reportId);
                }
                
                // return the remaining troops
                if (!$warResult['all_attack_killed']) {
                        $paramsArray = explode ('|', $taskRow['proc_params']);
                        $paramsArray[sizeof ($paramsArray) - 1] = 1;
                        
                        $newTroops = '';
                        foreach ($warResult['attackTroops']['troops'] as $tid=>$tprop) {
                                if ($newTroops != '') { $newTroops .= ','; }
                                
                                $newTroops .= $tid . ' ' . $tprop['live_number'];
                        }
                        if (!$warResult['all_attack_killed'] && $procInfo['troopsArray']['hasHero']) {
                                if ($newTroops != '') { $newTroops .= ','; }
                                $newTroops .= $procInfo['troopsArray']['heroTroopId'] . ' -1';
                        }
                        
                        $paramsArray[0] = $newTroops;
                        $newParams = implode ('|', $paramsArray);
                        
                        // generate troops back task
                        $this->provider->executeQuery (
                                'UPDATE p_queue q 
                                SET 
                                        q.player_id=%s,
                                        q.village_id=%s,
                                        q.to_player_id=%s,
                                        q.to_village_id=%s,
                                        q.proc_type=%s,
                                        q.proc_params=\'%s\',
                                        q.end_date=(q.end_date + INTERVAL q.execution_time SECOND)
                                WHERE q.id=%s',
                                array (
                                        intval ($taskRow['to_player_id']), intval ($taskRow['to_village_id']),
                                        intval ($taskRow['player_id']), intval ($taskRow['village_id']),
                                        QS_WAR_REINFORCE, $newParams, intval( $taskRow['id'] )
                                )
                        );
                        
                        return TRUE;
                }

                return FALSE;        // FALSE MEAN NO TROOP BACK
        }
        
        function _getSpyResult  ($attackTroops, $defenseTroops, $totalDefensePower, $fromVillageRow) {
                $warResult = array (
                        'all_attack_killed'                                 => FALSE,
                        'all_spy_killed'                                         => FALSE,
                        'defense_has_spytroops'                => FALSE
                );

$powatt = 1;
$powdeff = 1;
///Start Artefect
$efect = 2;
$pid = $fromVillageRow['player_id'];
$vid = $fromVillageRow['id'];
$tatarzx = new QueueModel();
$this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" ); 
$this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
if ($this->BigArt) {
$powatt = 10;
}else if ($this->SeCArt) {
$powatt = 5;
}else if ($this->SmallArt) {
$powatt = 10;
}
///End Artefect
///Start Artefect
$efect = 2;
$pid = $toVillageRow['player_id'];
$vid = $toVillageRow['id'];
$tatarzx = new QueueModel();
$this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" ); 
$this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
if ($this->BigArt) {
$powdeff = 10;
}else if ($this->SeCArt) {
$powdeff = 5;
}else if ($this->SmallArt) {
$powdeff = 10;
}
///End Artefect
   
                $totalAttackPower = $attackTroops['total_spy_power'];


                        
                $totalAttackPower *= $powatt;
                $totalDefensePower *= $powdeff;
                $ap = (pow(($totalDefensePower/$totalAttackPower),1.5));

                foreach ($attackTroops['troops'] as $tid=>$tProp) {
                        if ($warResult['all_attack_killed']) { break; }
            
 if ( $tid == 99 || $tid != 103 && $tid != 54 && $tid != 4 && $tid != 14 && $tid != 23 && $tid != 64 && $tid != 74 )
 {
 continue;
 }

                                        
                        $deadNum = round($tProp['number']*$ap);

                        if ($deadNum > $tProp['live_number']) {
                                $deadNum = $tProp['live_number'];
                        }
                                        
                        $attackTroops['total_carry_load'] -= $deadNum * $tProp['single_carry_load'];
                        $attackTroops['total_dead_consumption'] += $deadNum * $tProp['single_consumption'];
                        $attackTroops['total_live_number'] -= $deadNum;
                    
                        if ($deadNum > 0) {
                                $warResult['defense_has_spytroops'] = TRUE;
                        }
                                        
                        if ($attackTroops['total_live_number'] <= 0) {
                                $warResult['all_attack_killed'] = TRUE;
                        }

                        if ($attackTroops['total_live_number'] <= 0) {
                                $warResult['all_spy_killed'] = TRUE;
                        }
                                        
                        $attackTroops['troops'][$tid]['live_number'] -= $deadNum;
        }
                
                foreach ($defenseTroops as $vid=>$troopsTable) {
                                
                        foreach ($troopsTable['troops'] as $tid=>$tProp) {
if ( $tid == 99 || $tid != 103 && $tid != 54 && $tid != 4 && $tid != 14 && $tid != 23 && $tid != 64 && $tid != 74 )
 {
 continue;
 }
                                        $deadNum = 0;

                                        $defenseTroops[$vid]['total_dead_consumption'] += $deadNum * $tProp['single_consumption'];
                                        $defenseTroops[$vid]['total_live_number'] -= $deadNum;
                                                
                                        $defenseTroops[$vid]['troops'][$tid]['live_number'] -= $deadNum;

                        }
                }

                $warResult['attackTroops']                 = $attackTroops;
                $warResult['defenseTroops']         = $defenseTroops;

                return $warResult;
        }
        
}
?>