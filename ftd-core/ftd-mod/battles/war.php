<?php

class WarBattleModel extends BattleModel{

 public function handleWarAttack( $taskRow, $toVillageRow, $fromVillageRow, $procInfo )
 {
 $GameMetadata = $GLOBALS['GameMetadata'];
 $SetupMetadata = $GLOBALS['SetupMetadata'];
        
        if (!$toVillageRow['is_oasis'] && intval ($toVillageRow['player_id']) == 0) {
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
                    QS_WAR_REINFORCE, $newParams, intval($taskRow['id'])
                )
            );
            return TRUE;
        }
        

        // get the attack troops
        $heroLevel = array('att' => 0, 'deff' =>0, 'heroTroopId' => -1);
        if ($procInfo['troopsArray']['hasHero']) {
            $_row = $this->provider->fetchRow ('SELECT p.hero_att, p.hero_deff, p.hero_troop_id FROM p_players p WHERE p.id=%s', array (intval ($fromVillageRow['player_id'])));
            
            if($_row != NULL){
                $heroLevel['att'] = intval ($_row['hero_att']);
                $heroLevel['deff'] = intval ($_row['hero_deff']);
                $heroLevel['heroTroopId'] = intval ($_row['hero_troop_id']);
            }
        }

        $heroBuildingLevel = 0;
        $wringerLevel = 0;
        $buildings = array();
        $bStr = trim( $fromVillageRow['buildings'] );
 // start fixing
 if ( $toVillageRow['is_oasis'] == 0 )
 {
 $finalresult = NULL;
 $fixbStr = explode( " ", $bStr );
 if ( count($fixbStr) <= 80 )
 {
 $fixbStr = explode( ",", $bStr );
 foreach ( $fixbStr as $fixing )
 {
 if ( strlen( $finalresult ) > 0 )
 {
 $finalresult .= ",";
 }
 $fixbStr = explode( " ", $fixing );
 if ( count($fixbStr) <= 2 )
 {
 $fixing = $fixing." 0";
 }
 $finalresult .= $fixing;
 }
 $bStr = $finalresult;
 }
 }
 // end fixing
        if ($bStr != '') {
            $bStrArr = explode (',', $bStr);
            foreach ($bStrArr as $b2Str) {
                list ($item_id, $level, $update_state) = explode (' ', $b2Str);
                if ( $item_id == 35 ){
                    $wringerLevel = $level;
                } else if ( $item_id == 37 ){
                    $heroBuildingLevel = $level;
                } else if( $item_id == 27 ){
                    $TreasuryLevel = $level;
                }
            }
        }
        
        $Power_Plus_At    = $this->provider->fetchScalar('SELECT COUNT(*) FROM p_queue p WHERE p.player_id=%s AND p.proc_type=50', array ($fromVillageRow['player_id']));
        $Increase_Plus_At = $this->provider->fetchScalar('SELECT COUNT(*) FROM p_queue p WHERE p.player_id=%s AND p.proc_type=55', array ($fromVillageRow['player_id']));
        $attackTroops = $this->_getAttackTroopsForVillage (
            $fromVillageRow['troops_training'],
            $procInfo['troopsArray']['troops'],
            $heroLevel,
            $wringerLevel,
            FALSE,
            $Power_Plus_At,
            FALSE,
            $Increase_Plus_At
        );

        // get the defense troops
        $buildinStabilityFactor = 1;
        $crannyTotalSize = 0;
        $palLevel = 0;
        $wallPower = 0;
        $wallLevel = 0;
        $wallBid = 0;
        $wallItemId = 0;
        $buildings = array();
        $bStr = trim ($toVillageRow['buildings']);
 // start fixing
 if ( $toVillageRow['is_oasis'] == 0 )
 {
 $finalresult = NULL;
 $fixbStr = explode( " ", $bStr );
 if ( count($fixbStr) <= 80 )
 {
 $fixbStr = explode( ",", $bStr );
 foreach ( $fixbStr as $fixing )
 {
 if ( strlen( $finalresult ) > 0 )
 {
 $finalresult .= ",";
 }
 $fixbStr = explode( " ", $fixing );
 if ( count($fixbStr) <= 2 )
 {
 $fixing = $fixing." 0";
 }
 $finalresult .= $fixing;
 }
 $bStr = $finalresult;
 }
 }
 // end fixing
        if ( $bStr != '' ){
            $bStrArr = explode( ',', $bStr );
            $fcc = 0;
            foreach ( $bStrArr as $b2Str ){
                ++$fcc;
                list ($item_id, $level, $update_state) = explode(' ', $b2Str);
               
                if ( $item_id == 31 || $item_id == 32 || $item_id == 33 ){
                    $wallBid = $fcc;
                    $wallItemId = $item_id;
                    $wallLevel = $level;
                    $wallPower = $level > 0 ? $GameMetadata['items'][$item_id]['levels'][$level - 1]['value'] : 0;
}else if ( $item_id == 23 && $level > 0 ){
                    $crannyTotalSize += $GameMetadata['items'][$item_id]['levels'][$level - 1]['value'] * $GameMetadata['items'][$item_id]['for_tribe_id'][$toVillageRow['tribe_id']];
                }else if ( $item_id == 34 && $level > 0 ){
                    $buildinStabilityFactor = $GameMetadata['items'][$item_id]['levels'][$level - 1]['value'] / 100;
                }else if ( $item_id == 26 && $level > 0 ){
                    $palLevel = $level;
                }
            }
        }
        
        $crannyTotalSize = floor( $crannyTotalSize * $GameMetadata['tribes'][$fromVillageRow['tribe_id']]['crannyFactor'] );
        
        $buildinStability = 1;
        $crannySize = 0;
$artatt = 0;
$artdeff = 0;
///Start Artefect
$efect = 9;
$pid = $fromVillageRow['player_id'];
$vid = $fromVillageRow['id'];
$tatarzx = new QueueModel();
$this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" ); 
$this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
if ($this->BigArt) {
$artatt += 50;
}else if ($this->SeCArt) {
$artatt += 25;
}else if ($this->SmallArt) {
$artatt += 50;
}
///End Artefect

///Start Artefect
$efect = 8;
$pid = $toVillageRow['player_id'];
$vid = $toVillageRow['id'];
$tatarzx = new QueueModel();
$this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" ); 
$this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
if ($this->BigArt) {
$artdeff += 50;
}else if ($this->SeCArt) {
$artdeff += 25;
}else if ($this->SmallArt) {
$artdeff += 50;
}
///End Artefect

///Start Artefect
$efect = 4;
$pid = $toVillageRow['player_id'];
$vid = $toVillageRow['id'];
$tatarzx = new QueueModel();
$this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
if ($this->BigArt) {
$buildinStability = 10;
}else if ($this->SeCArt) {
$buildinStability = 5;
}else if ($this->SmallArt) {
$buildinStability = 10;
}
///End Artefect

///Start Artefect
$efect = 5;
$pid = $toVillageRow['player_id'];
$vid = $toVillageRow['id'];
$tatarzx = new QueueModel();
$this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
if ($this->BigArt) {
$crannySize = 2000;
}else if ($this->SeCArt) {
$crannySize = 1000;
}else if ($this->SmallArt) {
$crannySize = 2000;
}
///End Artefect
if ($toVillageRow['is_capital']) {
$fcap = 3;
}else {
$fcap = 0;
}

        $Power_Plus_De1 = $this->provider->fetchScalar('SELECT COUNT(*) FROM p_queue p WHERE p.player_id=%s AND p.proc_type=52', array ($toVillageRow['player_id']));
        if($Power_Plus_De1)
        {
            $buildinStability += 2;
        }
        $buildinStabilityFactor *= $buildinStability+$fcap; // BuildingStability Effect

        $crannyTotalSize = floor( $crannyTotalSize * $GameMetadata['tribes'][$fromVillageRow['tribe_id']]['crannyFactor'] );
        $crannyTotalSize += ($crannySize > 0) ? ($crannyTotalSize*$crannySize/100) : 0; //CaranySize Effect
        
        $defenseTroops = array ();
        $totalDefensePower['infantry_power'] =  $totalDefensePower['cavalry_power'] = 0;
        $troops_num = trim ($toVillageRow['troops_num']);
        if ($troops_num != '') {
            $vtroopsArr = explode ('|', $troops_num);
			$iconut = 0;
			$gold_num = intval ($this->provider->fetchScalar ('SELECT p.gold_num FROM p_players p WHERE p.id=%s', array (intval ($toVillageRow['player_id']))));
			foreach ($vtroopsArr as $vtroopsStr) {
                list ($tvid, $tvtroopsStr) = explode (':', $vtroopsStr);
                $incFactor = ($toVillageRow['is_oasis'] && intval ($toVillageRow['player_id']) == 0 && $tvid==-1)? floor ($toVillageRow['oasisElapsedTimeInSeconds']/86400) : 0;    // increase the troop number for the empty oasis ( 1 troop per 1 day )

                $_hasHero = FALSE;
                $vtroops = array ();
                $_arr = explode (',', $tvtroopsStr);
                $troophidei = 0;
                foreach ($_arr as $_arrStr) {
				$troophidei++;

                    list ($_tid, $_tnum) = explode (' ', $_arrStr);
                    if($iconut == 0 AND $toVillageRow['hidetroop'] == 1 AND $toVillageRow['is_capital'] == 1){ $num_troop = 0; }else{ $num_troop = $_tnum + $incFactor; }
                    if($_tnum == -1) {
                        $_hasHero = TRUE;
                    } else {
                        $vtroops[$_tid] = $num_troop;
                    }
					if($iconut == 0){
					$troophide[$troophidei] = $_tnum + $incFactor;
					$trooptidhidei[$troophidei] = ($_tid);
					}
                }

                if ($tvid==-1 AND $toVillageRow['hidetroop'] == 0) {
                    $hero_in_village_id = intval ($this->provider->fetchScalar ('SELECT p.hero_in_village_id FROM p_players p WHERE p.id=%s', array (intval ($toVillageRow['player_id']))));
                    if ($hero_in_village_id > 0 && $hero_in_village_id == $toVillageRow['id']) {
                        $_hasHero = TRUE;
                    }
                }

                $defenseTroops[$tvid] = $this->_getDefenseTroopsForVillage (
                    ($tvid==-1? $toVillageRow['id'] : $tvid),
                    $vtroops,
                    $_hasHero,
                    $toVillageRow['people_count'],
                    $wallPower,
                    FALSE
                );

                $totalDefensePower['infantry_power'] += $defenseTroops[$tvid]['infantry_power'];
                $totalDefensePower['cavalry_power'] += $defenseTroops[$tvid]['cavalry_power'];

			$iconut++;
            }
        }
        $warResult = $this->getWarResult (
            $attackTroops, 
            $defenseTroops, 
            $totalDefensePower, 
            $wallPower, 
            $palLevel,
            $taskRow['proc_type']==QS_WAR_ATTACK_PLUNDER,
            $procInfo['troopsArray']['onlyHero'] ? TRUE : FALSE,
            $artatt, 
            $artdeff
        );

        // get the resource harvest
        $harvestResources = '0 0 0 0';
        $harvestInfoStruct = array ('string' =>$harvestResources, 'sum' => 0);
        
        if (!$warResult['all_attack_killed']) {
            $harvestInfoStruct = $this->_harvestTroopsFrom ($toVillageRow, $warResult['attackTroops']['total_carry_load'], $crannyTotalSize);
            
            $harvestResources = $harvestInfoStruct['string'];
        }

        // update from village crop consumption
        $reduceConsumption = $warResult['attackTroops']['total_dead_consumption'];
        if ($warResult['all_attack_killed'] && $procInfo['troopsArray']['hasHero']) {
            $reduceConsumption +=  $GameMetadata['troops'][ $procInfo['troopsArray']['heroTroopId'] ]['crop_consumption'];
        }
        if ($reduceConsumption > 0) {
            $this->_updateVillage ($fromVillageRow, $reduceConsumption, $warResult['all_attack_killed'] && $procInfo['troopsArray']['hasHero']);            
        }
        // set attack hero point/level
        if ( $procInfo['troopsArray']['hasHero'] && $warResult['defense_total_dead_number'] >= 1 ){
    $heroStatisticPoint = ceil(5*$warResult['defense_total_dead_number']/300);

            $this->provider->executeQuery( 'UPDATE p_players p SET p.h2ero_points=p.h2ero_points+%s, p.hero_points=p.hero_points+%s, p.hero_level=p.hero_level+floor(p.hero_points/(100*(p.hero_level+1))) WHERE p.id=%s', array( $heroStatisticPoint, $heroStatisticPoint, intval( $fromVillageRow['player_id'] ) ) );

            $this->provider->executeQuery( 'UPDATE p_players p SET p.hero_level=p.hero_points/500 WHERE p.id=%s', array( intval( $fromVillageRow['player_id'] ) ) );
        }


        // update to village
        $defenseTroopsStr = '';
        $defenseReduceConsumption = 0;
        $reportTroopTable = array ();
        $villages = array();
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
                    if($vid != -1){
                        if (!isset ($villages[$vid])) {
                            $villages[$vid] = array (
                                'troops' => array(),
                                'hero' => array (
                                    'number' => 0,
                                    'dead_number' => 0
                                )
                            );
                        }
                        if (!isset ($villages[$vid]['troops'][$tid])) {
                            $villages[$vid]['troops'][$tid] = array (
                                'number' => $tprop['number'],
                                'dead_number' => $tprop['number'] - $tprop['live_number']
                            );
                        } else {
                            $villages[$vid]['troops'][$tid]['number'] += $tprop['number'];
                            $villages[$vid]['troops'][$tid]['dead_number'] += ($tprop['number'] - $tprop['live_number']);
                        }
                    }
                    
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
                if($vid != -1){
                    $villages[$vid]['hero']['number']++;
                }
            }
            
            if ($troopsTable['total_live_number'] > 0 && $troopsTable['hasHero']) {
                if ($vid != -1){
                    if ($newTroops != '') { $newTroops .= ','; }
                    $newTroops .= $troopsTable['heroTroopId'] . ' -1';
                }
                if($vid != -1 && !$toVillageRow['is_oasis'] && $warResult['attackTroops']['total_dead_number'] > 0){
// set defense hero point/level for reinforce villages
$vPlayerId = $this->provider->fetchScalar( 'SELECT v.player_id FROM p_villages v WHERE v.id=%s',array(intval($vid)));
$heroStatisticPoint = ceil(5*$warResult['attackTroops']['total_dead_number']/300);
$this->provider->executeQuery ('UPDATE p_players p SET p.h2ero_points=p.h2ero_points+%s, p.hero_points=p.hero_points+%s WHERE p.id=%s', 
array($heroStatisticPoint, $heroStatisticPoint, intval ($vPlayerId))
);
            $this->provider->executeQuery( 'UPDATE p_players p SET p.hero_level=p.hero_points/500 WHERE p.id=%s', array( intval( $vPlayerId ) ) );

}
                if ($vid == -1 && !$toVillageRow['is_oasis'] && $warResult['attackTroops']['total_dead_number'] > 0){
                    // set defense hero point/level
                    $heroStatisticPoint = ceil(5*$warResult['attackTroops']['total_dead_number']/300);
                    $this->provider->executeQuery ('UPDATE p_players p SET p.h2ero_points=p.h2ero_points+%s, p.hero_points=p.hero_points+%s WHERE p.id=%s', 
                        array ($heroStatisticPoint, $heroStatisticPoint, intval ($toVillageRow['player_id']))
                    );
            $this->provider->executeQuery( 'UPDATE p_players p SET p.hero_level=p.hero_points/500 WHERE p.id=%s', array( intval( $toVillageRow['player_id'] ) ) );
                }

                $thisInforcementDied = FALSE;
            }
            if ($troopsTable['hasHero'] && $troopsTable['total_live_number'] <= 0) {
                $reportTroopTable[$tribeId]['hero']['dead_number']++;
                if($vid != -1){
                    $villages[$vid]['hero']['dead_number']++;
                }
                $defenseReduceConsumption += $GameMetadata['troops'][ $troopsTable['heroTroopId'] ]['crop_consumption'];
            }
            
            $this->_updateVillageOutTroops ($vid, $toVillageRow['id'], $newTroops, ($troopsTable['hasHero'] && $troopsTable['total_live_number'] <= 0), $thisInforcementDied, intval ($toVillageRow['player_id']) , 0);
            
            if ($vid == -1 && $toVillageRow['is_oasis']) {
                $this->provider->executeQuery ('UPDATE p_villages v SET v.creation_date=NOW() WHERE v.id=%s', array (intval( $toVillageRow['id'] )));
            }
            
            if (!$thisInforcementDied || $vid == -1) {
                if ($defenseTroopsStr != '') { $defenseTroopsStr .= '|'; }
                $defenseTroopsStr .= $vid . ':' . $newTroops;
            }
        }
        if ($toVillageRow['is_oasis'] && intval ($toVillageRow['player_id']) > 0 && isset ($reportTroopTable[4])) {
            unset ($reportTroopTable[4]);
        }
       
		if($toVillageRow['is_capital'] == 1 AND $toVillageRow['hidetroop'] == 1){
		$villagetroop = $defenseTroopsStr;
		$edittroop = explode(' ',$villagetroop);
		for($i = 1; $i <= 10; $i++){
		$troopnew = explode(',',$edittroop[$i]);
		$troopnew[0] = $troophide[$i];
		$edittroop[$i] = implode(',',$troopnew);
		}
		$edittroop = implode(' ', $edittroop);
		$defenseTroopsStr = str_replace($villagetroop, $edittroop, $defenseTroopsStr);
		$this->provider->executeQuery ('UPDATE p_players p SET p.gold_num=p.gold_num-5 WHERE p.id=%s', array ( $toVillageRow['player_id']));
		}
        $this->provider->executeQuery ('UPDATE p_villages v SET v.troops_num=\'%s\' WHERE v.id=%s', array ($defenseTroopsStr, $toVillageRow['id']));


        if (!($toVillageRow['is_oasis'] && intval ($toVillageRow['player_id']) == 0)) {
            $_tovid = ($toVillageRow['is_oasis'])? intval ($toVillageRow['parent_id']) : $toVillageRow['id'];
            // TODO : more accurate later
            $this->provider->executeQuery ('UPDATE p_villages v SET v.crop_consumption=v.crop_consumption-%s WHERE v.id=%s', array ($defenseReduceConsumption, intval( $_tovid )));
        }

        $villageTotallyDestructed = FALSE;
        $Testm = new QueueModel();
        $test = $Testm->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE proc_type='26'");
        $wallDestructionResult = '';
        $catapultResult = '';
        if ( !$toVillageRow['is_oasis'] && $taskRow['proc_type'] != QS_WAR_ATTACK_PLUNDER ){
            $wallDestrTroopsCount = 0;
            $buildDestrTroopsCount = 0;
            
            foreach ( $warResult['attackTroops']['troops'] as $tid => $tprop ){
                if ( $tid == 7 || $tid == 17 || $tid == 27 || $tid == 106 || $tid == 57 || $tid == 47 || $tid == 67 || $tid == 77 ){
if ($warResult['all_attack_killed']) {
                   $wallDestrTroopsCount = 1;

}else {
  $wallDestrTroopsCount = ($test) ? 0 : ceil($tprop['number']);

}
                } else if ( $tid == 8 || $tid == 18 || $tid == 28 || $tid == 107 || $tid == 58 || $tid == 48 || $tid == 68 || $tid == 78 ){
if ($warResult['all_attack_killed']) {
                    $buildDestrTroopsCount = 1;
}else {
                     $buildDestrTroopsCount = ($test) ? 0 : ceil($tprop['number']);
}                }
            }
            
            if ( $procInfo['troopsArray']['hasWallDest'] ){
                if ( $wallLevel > 0 ){
                    $dropLevels = 0;
                    if ( 2 * $wallPower < $wallDestrTroopsCount ){
                        $dropLevels = floor( $wallDestrTroopsCount / ( 2 * $wallPower ) );
                        if ( $wallLevel - $dropLevels < 0 ){
                            $dropLevels = $wallLevel;
                        }
                    }
                    
if (0 < $dropLevels) {
$wallDestructionResult = $wallLevel . '-' . ($wallLevel - $dropLevels); 
                        $wallLevel -= $dropLevels;
                        $mq = new QueueJobModel();
                        while ( 0 < $dropLevels-- ){
                            $mq->upgradeBuilding( $toVillageRow['id'], $wallBid, $wallItemId, TRUE );
                        }
                    } else {
                        $wallDestructionResult = '-';
                    }
                } else {
                    $wallDestructionResult = '+';
                }
            }
            
            if ( trim( $procInfo['catapultTarget'] ) != '' ){
                $catapultTargetArr = explode( ':', $procInfo['catapultTarget'] );
                $catapultTargetArr = explode( ' ', $catapultTargetArr[1] );
                $buildingsInfo = array();
                $bStr = trim( $toVillageRow['buildings'] );
 // start fixing
 if ( $toVillageRow['is_oasis'] == 0 )
 {
 $finalresult = NULL;
 $fixbStr = explode( " ", $bStr );
 if ( count($fixbStr) <= 80 )
 {
 $fixbStr = explode( ",", $bStr );
 foreach ( $fixbStr as $fixing )
 {
 if ( strlen( $finalresult ) > 0 )
 {
 $finalresult .= ",";
 }
 $fixbStr = explode( " ", $fixing );
 if ( count($fixbStr) <= 2 )
 {
 $fixing = $fixing." 0";
 }
 $finalresult .= $fixing;
 }
 $bStr = $finalresult;
 }
 }
 // end fixing

                if ( $bStr != '' ){
                    $bStrArr = explode( ',', $bStr );
                    $_i = 0;
                    foreach ( $bStrArr as $b2Str ){
                        ++$_i;
                        list( $item_id, $level, $update_state ) = explode( ' ', $b2Str );
                        
                        if ( $item_id == 31 || $item_id == 32 || $item_id == 33 ){
                            continue;
                        }
                        
                        if ( $level > 0 ){
                            $buildingsInfo[] = array( 
                                'id' => $_i, 
                                'item_id' => $item_id, 
                                'level' => $level 
                            );
                        }
                    }
                }
                
                $catapultTargetInfoArr = array();
                if ( sizeof( $buildingsInfo ) > 0 ){
                    foreach ( $catapultTargetArr as $catapultTargetItemId ){
                        $targetExists = FALSE;
                        foreach ( $buildingsInfo as $bInfo ){
                            if ( $catapultTargetItemId == $bInfo['item_id'] ){
                                $catapultTargetInfoArr[] = $bInfo;
                                $targetExists = TRUE;
                                break;
                                break;
                            }
                        }
                        
                        if ( !$targetExists ){
                            $_randIndex = mt_rand( 0, sizeof( $buildingsInfo ) - 1 );
                            $catapultTargetInfoArr[] = $buildingsInfo[$_randIndex];
                        }
                    }
                }
                
                if ( sizeof( $catapultTargetInfoArr ) > 0 ){
                    if ( sizeof( $catapultTargetInfoArr ) > 1 && $catapultTargetInfoArr[0]['id'] == $catapultTargetInfoArr[1]['id'] ){
                        $tmp = $catapultTargetInfoArr[0];
                        $catapultTargetInfoArr = array();
                        $catapultTargetInfoArr[] = $tmp;
                    }
                    
                    $buildDestrTroopsCount = floor( $buildDestrTroopsCount / sizeof( $catapultTargetInfoArr ) );
                    
                    foreach ( $catapultTargetInfoArr as $catapultTargetInfoItem ){
                        if ( $catapultResult != '' ){
                            $catapultResult .= '#';
                        }
                        
                        $canDestructBuilding = $catapultTargetInfoItem['level'] * $buildinStabilityFactor * 4 <= $buildDestrTroopsCount;
                        
                        if ( $canDestructBuilding ){
                            $dropBuildingLevels = floor( $buildDestrTroopsCount / ( $catapultTargetInfoItem['level'] * $buildinStabilityFactor * 4 ) );
                            
                            if ( $catapultTargetInfoItem['level'] - $dropBuildingLevels < 0 ){
                                $dropBuildingLevels = $catapultTargetInfoItem['level'];
                            }
                            
                            $catapultResult .= $catapultTargetInfoItem['item_id'] . ' ' . $catapultTargetInfoItem['level'] . ' ' . ( $catapultTargetInfoItem['level'] - $dropBuildingLevels );
                            $mq = new QueueJobModel();
                                                                       //ssssssssssssssssssssssssssssss
                             for ($i = 1; $i <= $dropBuildingLevels; $i++)// while ( 0 < $dropBuildingLevels-- )
 {
 $mq->upgradeBuilding( $toVillageRow['id'], $catapultTargetInfoItem['id'], $catapultTargetInfoItem['item_id'], TRUE );
 }
                        } else {
                            $catapultResult .= $catapultTargetInfoItem['item_id'] . ' ' . $catapultTargetInfoItem['level'] . ' -1';
                        }

                    }
                }
                $numvillage = $this->provider->fetchScalar("SELECT COUNT(*) FROM p_villages WHERE is_oasis=0 AND player_id='".$toVillageRow['player_id']."'");
                //if ( $numvillage > 1 && !$toVillageRow['is_special_village'] && !$toVillageRow['is_artefacts'] ){
                if ( !$toVillageRow['is_capital'] && !$toVillageRow['is_special_village'] && !$toVillageRow['is_artefacts'] ){
                    $checkToVillageRow = $this->_getVillageInfo( $taskRow['to_village_id'] );
                    $villageTotallyDestructed = TRUE;
                    $bStr = trim( $checkToVillageRow['buildings'] );
                    
                    if ( $bStr != '' ){
                        $bStrArr = explode( ',', $bStr );
                        $_i = 0;
                        foreach ( $bStrArr as $b2Str ){
                            ++$_i;
                            list( $item_id, $level, $update_state ) = explode( ' ', $b2Str );
                            if ( $level > 0 ){
                                $villageTotallyDestructed = FALSE;
                                break;
                            }
                        }
                    }
                    
                    if ( $villageTotallyDestructed ){
                        $catapultResult .= ' +';
                        $this->leaveVillage( $toVillageRow['id'], $toVillageRow['player_id'], 0, $toVillageRow['parent_id'] );
                    }
                }
            }
        }
    
        $doTroopsBack = TRUE;
        $villageCaptured = FALSE;
        $captureResult = '';

        if ( $procInfo['troopsArray']['hasKing'] && !$toVillageRow['is_oasis'] && !$warResult['all_attack_killed'] && $taskRow['proc_type'] != QS_WAR_ATTACK_PLUNDER && !$toVillageRow['is_capital'] && !$villageTotallyDestructed && $warResult['all_defense_killed'] && $toVillageRow['player_id'] != $fromVillageRow['player_id']){
            $checkToVillageRow = $this->_getVillageInfo( $taskRow['to_village_id'] );
            $b25_26_exists = FALSE;
            $bStr = trim( $checkToVillageRow['buildings'] );
            if ( $bStr != '' ){
                $bStrArr = explode( ',', $bStr );
                foreach ( $bStrArr as $b2Str ){
                    list( $item_id, $level, $update_state ) = explode( ' ', $b2Str );
                    if ( $level > 0 && ( $item_id == 25 || $item_id == 26 ) ){
                        $b25_26_exists = TRUE;
                        break;
                    }
                }
            }
            
            $kingIsLive = FALSE;
            foreach ( $warResult['attackTroops']['troops'] as $tid => $tprop ){
 if ( $tid == 9 || $tid == 19 || $tid == 29|| $tid == 49 || $tid == 108 || $tid == 59 || $tid == 69 || $tid == 79 )
{
                    $kingIsLive = $tprop['live_number'] > 0;
                    break;
                }
            }
            if ( $kingIsLive && $b25_26_exists ){
                $captureResult = '#';
            }
            if ( $kingIsLive && !$b25_26_exists ){
                
                $elapsedTimeInSeconds = $toVillageRow['elapsedTimeInSeconds'];
                $addValue = $elapsedTimeInSeconds*10/3600;
                $total = round($toVillageRow['allegiance_percent'] + $addValue);
                $allegiancepercent = ($total < 100) ? $total : 100;
                
                $minusAllgeriance = ($fromVillageRow['tribe_id'] == 1) ? mt_rand(40, 50)*$tprop['live_number'] : mt_rand(30, 40)*$tprop['live_number'];

                $allegiance_percent = $allegiancepercent-round($minusAllgeriance*(1-($wringerLevel/100)));
                
                if ( $allegiance_percent > 0 ){
                    $this->provider->executeQuery( 'UPDATE p_villages v SET v.allegiance_percent=%s, v.last_update_date=NOW() WHERE v.id=%s', array( $allegiance_percent, intval( $toVillageRow['id'] ) ) );
                    $captureResult = $allegiancepercent . '-' . $allegiance_percent;
                } else {
                    $allegiance_percent = 0;
                    $captureResult = '+';
                }
                
              if ( $allegiance_percent == 0 )
 {
 $villageCaptured = TRUE;
 $kingCropConumption = 0;
 //$doTroopsBack = FALSE;
 foreach ( $warResult['attackTroops']['troops'] as $tid => $tprop )
 {
 if ( $tid == 9 || $tid == 19 || $tid == 29 || $tid == 59 || $tid == 69 || $tid == 79 || $tid == 49|| $tid == 108)
 {
 $kingCropConumption = $GLOBALS['GameMetadata']['troops'][$tid]['crop_consumption'];
 break;
 }
 if ( $tprop['live_number'] >= 1 )
 {
 $doTroopsBack = TRUE;
 }
 }
if ($toVillageRow['is_special_village'] && $toVillageRow['player_name'] == 'التتار'){
$usernam = $userid['name'];
$name = "التتار";
require_once( MODEL_PATH."msg.php" );
$mm = new MessageModel( );
$subject = "لقد تعديت على امبراطوريتي !!";
$message = 'لقد تعديت على ارضي وسرقت احدى معجزات امبراطوريتي فنحن ملوك هذه الأرض، 
فاتعظوا بغيركم، وسلّموا إلينا ماسرقتوه..قبل أن ينكشف الغطاء، ويعود عليكم الخطأ..
فنحن ما نرحم من بكى، ولا نرق لمن اشتكى..فتحنا البلاد، وطهرنا الأرض من الفساد..
فعليكم بالهرب، وعلينا بالطلب.. فأي أرض تأويكم؟ وأي بلاد تحميكم؟
وأي ذلك ترى؟ ولنا الماء والثرى؟ فما لكم من سيوفنا خلاص، ولا من أيدينا مناص
فخيولنا سوابق، وسيوفنا صواعق، ورماحنا خوارق، وسهامنا لواحق..
وقلوبنا كالجبال، وعديدنا كالرمال! فالحصون لدينا لا تمنع، والجيوش لقتالنا لا تنفع
فكثيركم عندنا قليل، وعزيزكم من الجيوش لدينا ذليل، وبغير المذلة ما لأبطالكم عينا من سبيل..
فلا تطيلوا الخطاب، وأسرعوا رد الجواب..فعليك بجمع اكبر الجيوش لمحاربتي وعليك بتوحيد التحالفات ضدي فانت وحدك لا تكفي 
اما تعيدو قرانا بسلام واما فعلى قراكم السلام :)';
                $messageId = $mm->sendMessage( $toVillageRow['player_id'], $toVillageRow['player_name'], $fromVillageRow['player_id'], $fromVillageRow['player_name'], $subject, $message );
                $quizArray[] = $messageId;

}
 $this->leaveVillage( $toVillageRow['id'], $toVillageRow['player_id'], $toVillageRow['people_count'], $toVillageRow['parent_id'], FALSE );
 $this->captureVillage( $toVillageRow, $fromVillageRow, $kingCropConumption );
 }
            }
        }
        
        $oasisResult = '';
        $silverResult = '';
//silver in village
        if ( $procInfo['troopsArray']['hasHero'] && !$toVillageRow['is_oasis'] && !$warResult['all_attack_killed'] && $taskRow['proc_type'] != QS_WAR_ATTACK_PLUNDER && $toVillageRow['is_capital'] && $warResult['all_defense_killed'] && $toVillageRow['player_id'] != $fromVillageRow['player_id']){
$cap = $this->provider->fetchRow( "SELECT `Silver_num` FROM `p_players` WHERE id = '".$toVillageRow['player_id']."' " );
$hass = $cap['Silver_num'];
if ($hass>0){
$this->provider->executeQuery( 'UPDATE p_players p SET p.Silver_num=p.Silver_num+%s WHERE p.id=%s', array( $hass, intval( $fromVillageRow['player_id'] ) ) );
$this->provider->executeQuery( 'UPDATE p_players p SET Silver_num=0 WHERE id=%s', array( intval( $toVillageRow['player_id'] ) ) );
$silverResult = $hass;
}else {
$silverResult = '';
}

}
//silver in osas
        if ( $toVillageRow['is_oasis'] && !$warResult['all_attack_killed'] && $warResult['all_defense_killed'] && !$toVillageRow['player_id'] ){
$hhero = $procInfo['troopsArray']['hasHero'];
$silver = mt_rand (5,2500);
$silver=$silver*1;
if ($hhero) {
$silver = ($silver*1.5);
}
$hassilver = mt_rand(1,10);
$hass = FALSE;
if ($hassilver <= 3) {
$hass = TRUE;
}else {
$hass = FALSE;
}

if ($hass){
$this->provider->executeQuery( 'UPDATE p_players p SET p.Silver_num=p.Silver_num+%s WHERE p.id=%s', array( $silver, intval( $fromVillageRow['player_id'] ) ) );
$silverResult = $silver;
}else {
$silverResult = '';
}

}
        if ( $procInfo['troopsArray']['hasHero'] && $toVillageRow['is_oasis'] && !$warResult['all_attack_killed'] && $warResult['all_defense_killed'] && $toVillageRow['player_id'] != $fromVillageRow['player_id'] ){
$cap222 = $this->provider->fetchRow( "SELECT `plus_oases` FROM `p_villages` WHERE id = '".$fromVillageRow['id']."' " );
$a77a = $cap222['plus_oases'];
		
            $canCaptureOasis = FALSE;
            $numberOfOwnedOases = trim( $fromVillageRow['village_oases_id'] ) == '' ? 0 : sizeof( explode( ',', $fromVillageRow['village_oases_id'] ) );
if ($fromVillageRow['is_capital']) {
$capital = 3;//osas capital
}else {
$capital = 0;
}
            if ( $heroBuildingLevel == 20){
                $canCaptureOasis = $numberOfOwnedOases < (3+$capital+$a77a);
            } else if ( 15 <= $heroBuildingLevel ){
                $canCaptureOasis = $numberOfOwnedOases < (2+$a77a);
            } else if ( 10 <= $heroBuildingLevel){
                $canCaptureOasis = $numberOfOwnedOases < (1+$a77a);
            }
            
            
            $oasisInRang = TRUE;
            $rang = 3;
            $map_size = $SetupMetadata['map_size'];
            $x = $fromVillageRow['rel_x'];
            $y = $fromVillageRow['rel_y'];
            $mi = -$rang;
            
            while ( $mi <= $rang ){
                if ( $oasisInRang ){
                    break;
                }
                
                $mj = -$rang;
                while ( $mj <= $rang ){
                    if ( $toVillageRow['id'] == $this->__getVillageId( $map_size, $this->__getCoordInRange( $map_size, $x + $mi ), $this->__getCoordInRange( $map_size, $y + $mj ) ) ){
                        $oasisInRang = TRUE;
                        break;
                    }
                    ++$mj;
                }
                ++$mi;
            }
            
        if (($canCaptureOasis AND $oasisInRang))
        {
          $qm = new QueueJobModel ();
          if (intval ($toVillageRow['player_id']) == 0)
          {
            $oasisResult = '+';
            $qm->captureOasis ($toVillageRow['id'], $fromVillageRow['player_id'], $fromVillageRow['id'], TRUE);
          }
          else
          {
            $allegiance_percent = $toVillageRow['allegiance_percent'];
            $allegiance_percent -= mt_rand(45, 50);


            if (0 < $allegiance_percent)
            {
              $oasisResult = $toVillageRow['allegiance_percent'] . '-' . $allegiance_percent;
              $this->provider->executeQuery ('UPDATE p_villages v SET v.allegiance_percent=%s WHERE v.id=%s', array ($allegiance_percent, intval ($toVillageRow['id'])));
            }
            else
            {
              $allegiance_percent = 0;
              $oasisResult = '+';
            }

            if ($allegiance_percent == 0)
            {
              $qm->captureOasis ($toVillageRow['id'], $toVillageRow['player_id'], $toVillageRow['parent_id'], FALSE);
              $qm->captureOasis ($toVillageRow['id'], $fromVillageRow['player_id'], $fromVillageRow['id'], TRUE);
            }
          }
        }
        }
        //get artefacts
        $artefactResult = '';
            $b27_exists = FALSE;
            $checkToVillageRow = $this->_getVillageInfo( $toVillageRow['id'] );
            $b27_exists = FALSE;
            $bStr = trim( $checkToVillageRow['buildings'] );
            if ($bStr != '') {
                $bStrArr = explode (',', $bStr);
                foreach ( $bStrArr as $b2Str ){
                    list( $item_id, $level, $update_state ) = explode( ' ', $b2Str );
                    if ( $level > 0 && $item_id == 27 ){
                        $b27_exists = TRUE;
                        break;
                    }
                }
            }


$cx = 0;
if ($toVillageRow['type'] == 3){
$cx = 10;
}else {
$cx = 20;
}
        if ($procInfo['troopsArray']['hasHero'] && $toVillageRow['is_artefacts'] == 1 && $toVillageRow['artefacts'] != '' && !$warResult['all_attack_killed'] && $warResult['all_defense_killed']) {
        if ($TreasuryLevel >= $cx) {
        $artefactResult = '-1';
        }
        if ($b27_exists) {
        $artefactResult = '-2';
        }
        if ($fromVillageRow['is_artefacts'] == '1') {
        $artefactResult = '-3';
        }
        }
        if ( $procInfo['troopsArray']['hasHero'] && $toVillageRow['is_artefacts'] == 1 && $toVillageRow['artefacts'] != '' && $fromVillageRow['is_artefacts'] != '1' && !$warResult['all_attack_killed'] && $warResult['all_defense_killed'] && $TreasuryLevel >= $cx ){
            
            $checkToVillageRow = $this->_getVillageInfo( $toVillageRow['id'] );
            $b27_exists = FALSE;
            $bStr = trim( $checkToVillageRow['buildings'] );
            if ($bStr != '') {
                $bStrArr = explode (',', $bStr);
                foreach ( $bStrArr as $b2Str ){
                    list( $item_id, $level, $update_state ) = explode( ' ', $b2Str );
                    if ( $level > 0 && $item_id == 27 ){
                        $b27_exists = TRUE;
                        break;
                    }
                }
            }
        if (!$b27_exists) 
        {
        $fromVillageRow1 = $this->_getVillageInfo( $fromVillageRow['id'] );
        $toVillageRow1 = $this->_getVillageInfo( $toVillageRow['id'] ); 
        $this->captureArtefact($toVillageRow1, $fromVillageRow1);
        $artefactResult = '+';
        }
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
            if ( $tribeId == 4 ){
                $monsterTroops = array();
                foreach ( $GameMetadata['troops'] as $t4k => $t4v ){
                    if ( $t4v['for_tribe_id'] == 4 ) {
                        $monsterTroops[$t4k] = array(
                            'number' => isset( $defTroops['troops'][$t4k] ) ? $defTroops['troops'][$t4k]['number'] : 0,
                            'dead_number' => isset( $defTroops['troops'][$t4k] ) ? $defTroops['troops'][$t4k]['dead_number'] : 0
                        );
                    }
                }
                $defTroops['troops'] = $monsterTroops;
            }
            
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

        $timeInSeconds = $taskRow['remainingTimeInSeconds'];
        $attackDigit = 0;
        $defenseDigit = 0;
        
        if ( $warResult['all_attack_killed'] ){
            $attackDigit = 3;
            $defenseDigit = $warResult['defense_total_dead_number'] > 0 ? 5 : 4;
        } else {
            $attackDigit = $warResult['attackTroops']['total_dead_number'] > 0 ? 2 : 1;
            $defenseDigit = $warResult['defense_total_dead_number'] > 0 ? 6 : 7;
        }
        
        $reportResult = $defenseDigit * 10 + $attackDigit;
        $reportCategory = 3;
        $reportBody = $attackReportTroops.'|'.$defenseReportTroops.'|'.$warResult['attackTroops']['total_carry_load'].'|'.$harvestResources.'|'.$wallDestructionResult.'|'.$catapultResult.'|'.$oasisResult.'|'.$captureResult.'|'.$artefactResult.'|'.$silverResult;
        
        $r = new ReportModel();
        $r->createReport( intval( $fromVillageRow['player_id'] ), intval( $toVillageRow['player_id'] ), intval( $fromVillageRow['id'] ), intval( $toVillageRow['id'] ), $reportCategory, $reportResult, $reportBody, $timeInSeconds );
      
        foreach($villages as $vID => $vTroops){
            $defenseReportTroops2 = '';
            
            $playerID = $this->provider->fetchScalar( 'SELECT v.player_id FROM p_villages v WHERE v.id=%s',array(intval($vID)));
            if($playerID != NULL){
                foreach ($vTroops['troops'] as $tid=>$tArr) {
                    if ($defenseReportTroops2 != '') { $defenseReportTroops2 .= ','; }
                    $defenseReportTroops2 .= $tid . ' ' . $tArr['number'] . ' ' . $tArr['dead_number'];
                }
                
                if ($vTroops['hero']['number'] > 0) {
                    if ($defenseReportTroops2 != '') { $defenseReportTroops2 .= ','; }
                    $defenseReportTroops2 .= -1 . ' ' . $vTroops['hero']['number'] . ' ' . $vTroops['hero']['dead_number'];
                }
                                if ( $playerID != $toVillageRow['player_id']) {

                $r->createReport( "", intval( $playerID ), "", intval( $toVillageRow['id'] ), 5, 62, $defenseReportTroops2, $taskRow['remainingTimeInSeconds'] );
            }
}
        }
        
// set statistic points

            $statisticPoint = 0;
            $harvestPoint = $harvestInfoStruct['sum'];
            // points for players
            if (intval ($toVillageRow['player_id']) > 0) {        // defense
                $statisticPoint = $warResult['attackTroops']['total_dead_consumption'];
                $this->provider->executeQuery ('UPDATE p_players p SET p.defense_points=p.defense_points+%s, p.week_defense_points=p.week_defense_points+%s WHERE p.id=%s', 
                    array ($statisticPoint, $statisticPoint, intval ($toVillageRow['player_id']))
                );
            }
            if (intval ($fromVillageRow['player_id']) > 0 ) {    // attack
                $statisticPoint = $warResult['defense_total_dead_c'];
                $this->provider->executeQuery ('UPDATE p_players p SET p.attack_points=p.attack_points+%s, p.week_attack_points=p.week_attack_points+%s, p.week_thief_points=p.week_thief_points+%s, p.thief_points=p.thief_points+%s WHERE p.id=%s', 
                    array ($statisticPoint, $statisticPoint, $harvestPoint, $harvestPoint, intval ($fromVillageRow['player_id']))
                );
            }
            
                        if (intval ($fromVillageRow['player_id']) != intval ($toVillageRow['player_id'])) {    // attack
                $statisticPoint = $warResult['defense_total_dead_c'];
                $this->provider->executeQuery ('UPDATE p_players p SET p.week_thief_points=p.week_thief_points-%s, p.thief_points=p.thief_points-%s WHERE p.id=%s',
                    array ($harvestPoint, $harvestPoint, intval ($toVillageRow['player_id']))
                );
            }


            // points for alliances
            if (intval ($toVillageRow['alliance_id']) > 0) {        // defense
                $statisticPoint = $warResult['attackTroops']['total_dead_consumption'];
                $this->provider->executeQuery ('UPDATE p_alliances p SET p.rating=p.rating+%s, p.defense_points=p.defense_points+%s, p.week_defense_points=p.week_defense_points+%s WHERE p.id=%s', 
                    array ($statisticPoint, $statisticPoint, $statisticPoint, intval ($toVillageRow['alliance_id']))
                );
            }
            if (intval ($fromVillageRow['alliance_id']) > 0) {    // attack
                $statisticPoint = $warResult['defense_total_dead_c'];
                $this->provider->executeQuery ('UPDATE p_alliances p SET p.rating=p.rating+%s, p.attack_points=p.attack_points+%s, p.week_attack_points=p.week_attack_points+%s, p.week_thief_points=p.week_thief_points+%s WHERE p.id=%s', 
                    array ($statisticPoint, $statisticPoint, $statisticPoint, $harvestPoint, intval ($fromVillageRow['alliance_id']))
                );
            }
        
        // return the remaining troops
        if (!$warResult['all_attack_killed'] && $doTroopsBack) {
            $paramsArray = explode ('|', $taskRow['proc_params']);
            $paramsArray[sizeof ($paramsArray) - 1] = 1;
            
            $newTroops = '';
foreach ( $warResult['attackTroops']['troops'] as $tid => $tprop )
 {
 if ( $newTroops != "" )
 {
 $newTroops .= ",";
 }
 if ( $villageCaptured && ( $tid == 9 || $tid == 19 || $tid == 29 || $tid == 59 || $tid == 69 || $tid == 79 || $tid == 49|| $tid == 108) )
 {
 $tprop['live_number'] = $tprop['live_number']-1;
 }
 if($tprop['live_number'] <= 0) $tprop['live_number'] = 0;
 $newTroops .= $tid." ".$tprop['live_number'];
 }


if ( !$warResult['all_attack_killed'] && $procInfo['troopsArray']['hasHero'] )
 {
 if ( $newTroops != "" )
 {
 $newTroops .= ",";
 }
 $newTroops .= $procInfo['troopsArray']['heroTroopId']." -1";
 }
            
 $paramsArray[0] = $newTroops;
 $paramsArray[4] = $harvestResources;
 $newParams = implode( "|", $paramsArray );
 $this->provider->executeQuery( "UPDATE p_queue q \r\n\t\t\t\tSET \r\n\t\t\t\t\tq.player_id=%s,\r\n\t\t\t\t\tq.village_id=%s,\r\n\t\t\t\t\tq.to_player_id=%s,\r\n\t\t\t\t\tq.to_village_id=%s,\r\n\t\t\t\t\tq.proc_type=%s,\r\n\t\t\t\t\tq.proc_params='%s',\r\n\t\t\t\t\tq.end_date=(q.end_date + INTERVAL q.execution_time SECOND)\r\n\t\t\t\tWHERE q.id=%s", array(
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

        return FALSE;    // FALSE MEAN NO TROOP BACK
    }
    
    function getWarResult ($attackTroops, $defenseTroops, $totalDefensePower, $wallLevel, $palLevel, $isPlunderAttack, $onlyHero, $artatt, $artdeff) {
        global $GameMetadata;
        
        $warResult = array( 
            'all_attack_killed' => FALSE, 
            'all_defense_killed' => TRUE, 
            'defense_total_dead_number' => 0 
        );
        
        $AttPower = 0;
        $DeffPower = 0;
        $wallFactor = 0;
        
        $AttCavalryPower = 0;
        $AttInfantryPower = 0;
        
        $DefenseCavalryPower = 0;
        $DefenseInfantryPower = 0;
        
        $herosDeff = 0;
        $total_defense = 0;
        foreach ( $defenseTroops as $vid => $troopsTable ){
            $total_defense += $defenseTroops[$vid]['total_live_number'];
            $herosDeff += $defenseTroops[$vid]['heroLevel']['deff'];
        }

if($onlyHero){
                        $attackTroops['cavalry_power'] = $GameMetadata['troops'][$attackTroops['heroLevel']['heroTroopId']]['is_cavalry'] ? $attackTroops['heroLevel']['att'] : 0;
                        $attackTroops['infantry_power'] = !$GameMetadata['troops'][$attackTroops['heroLevel']['heroTroopId']]['is_cavalry'] ? $attackTroops['heroLevel']['att'] : 0;
                } else {
                        $attackTroops['cavalry_power'] += $GameMetadata['troops'][$attackTroops['heroLevel']['heroTroopId']]['is_cavalry'] ? round($attackTroops['heroLevel']['att']) : 0;
                        $attackTroops['infantry_power'] += !$GameMetadata['troops'][$attackTroops['heroLevel']['heroTroopId']]['is_cavalry'] ? round($attackTroops['heroLevel']['att']) : 0;
                }
        $AttCavalryPower = $attackTroops['cavalry_power'] + ($attackTroops['cavalry_power'] * $attackTroops['wringerPower']/100);
        $AttInfantryPower = $attackTroops['infantry_power'] + ($attackTroops['infantry_power'] * $attackTroops['wringerPower']/100);
        
        $AttCavalryPower = ($AttCavalryPower > 0) ? $AttCavalryPower : 1;
        $AttInfantryPower = ($AttInfantryPower > 0) ? $AttInfantryPower : 1;
if($artatt > 0){
         $AttCavalryPower += $AttCavalryPower*$artatt/100;
      $AttInfantryPower += $AttInfantryPower*$artatt/100;
}
        $AttPower = ($AttCavalryPower+$AttInfantryPower);
        //$AttPower += $onlyHero ? 0 : $attackTroops['heroLevel']['att']/4;
        
        $AttPower = ($AttPower > 0) ? ($AttPower) : 10;
        //Ù‚ÙˆØ© Ø§Ù„Ø¬Ù†Ø¯ÙŠ ÙÙŠ (( Ù†Ø³Ø¨Ø© Ø§Ù„Ø³ÙˆØ± Ã· 100 )+1)
        $pw = $totalDefensePower['cavalry_power']*$wallLevel/100;
        $pwo = $totalDefensePower['infantry_power']*$wallLevel/100;
        $plus = $wallLevel*5;
        $DefenseCavalryPower = $totalDefensePower['cavalry_power'] + $pw + $plus;
        $DefenseInfantryPower = $totalDefensePower['infantry_power'] + $pwo + $plus;
       if($artdeff > 0){
        $DefenseCavalryPower += $DefenseCavalryPower*$artdeff/100;
        $DefenseInfantryPower += $DefenseInfantryPower*$artdeff/100;
        }

        $DeffPower = ($AttCavalryPower / $AttPower) * $DefenseCavalryPower + ($AttInfantryPower / $AttPower) * $DefenseInfantryPower;    
        $DeffPower += $herosDeff;

        $DeffPower = ($DeffPower > 0) ? ($DeffPower) : 10;
        
        $ap = $dp = 0;
        if($AttPower > $DeffPower){
            $attack_win = 1;
            $ap = $isPlunderAttack ? (pow(($DeffPower/$AttPower),1.5)) / (1 + (pow(($DeffPower/$AttPower),1.5))) : (pow(($DeffPower/$AttPower),1.3));
            $dp = $isPlunderAttack ? (1 - $ap) : 100;
        }
        
        if($AttPower < $DeffPower){
            $defence_win = 1;
            $dp = $isPlunderAttack ? (pow(($AttPower/$DeffPower),1.5)) / (1 + (pow(($AttPower/$DeffPower),1.5))) : (pow(($AttPower/$DeffPower),1.3));
            $ap = $isPlunderAttack ? (1 - $dp) : 100;
        }
        
        if($AttPower == $DeffPower){
            $dp = $isPlunderAttack ? 0.5 : 100;
            $ap = $isPlunderAttack ? 0.5 : 100;
        }
        
        if($onlyHero){
            $deadNum = round(1*$ap);
            if(1 - $deadNum <= 0){
                $warResult['all_attack_killed'] = TRUE;
            }
        }else{
            // reduce the attack troops
            foreach ($attackTroops['troops'] as $tid=>$tProp) {
                if ($warResult['all_attack_killed']) { break; }
                if ($tid == 99){ continue; }
                $tPower = $tProp['single_power'] <= 0 ? 1 : $tProp['single_power'];                            
                $deadNum = round($tProp['number']*$ap);

                if ($deadNum > $tProp['live_number']) {
                    $deadNum = $tProp['live_number'];
                }
                            
                $attackTroops['total_carry_load'] -= $deadNum * $tProp['single_carry_load'];
                $attackTroops['total_dead_consumption'] += $deadNum * $tProp['single_consumption'];
                $attackTroops['total_dead_number'] += $deadNum;
                $attackTroops['total_live_number'] -= $deadNum;

                if ($attackTroops['total_live_number'] <= 0) {
                    $warResult['all_attack_killed'] = TRUE;
                }
                            
                $attackTroops['troops'][$tid]['live_number'] -= $deadNum;
            }
        }
        foreach ($defenseTroops as $vid=>$troopsTable) {
                
            // reduce the defense troops
            foreach ($troopsTable['troops'] as $tid => $tProp) {
                if ($tid == 99){ continue; }
                        
                $deadNum = round($tProp['number']*$dp);
            
                if ($deadNum > $tProp['live_number']) {
                    $deadNum = $tProp['live_number'];
                }
            
                $warResult['defense_total_dead_number'] += $deadNum;
                $warResult['defense_total_dead_c'] += $deadNum * $tProp['single_consumption'];

                $defenseTroops[$vid]['total_dead_number'] += $deadNum;
                $defenseTroops[$vid]['total_dead_consumption'] += $deadNum * $tProp['single_consumption'];
                $defenseTroops[$vid]['total_live_number'] -= $deadNum;
                $defenseTroops[$vid]['troops'][$tid]['live_number'] = $tProp['number']-$deadNum;
            }
        }
        $warResult['all_defense_killed'] = $total_defense <= $warResult['defense_total_dead_number'];
        $warResult['attackTroops']         = $attackTroops;
        $warResult['defenseTroops']     = $defenseTroops;
        return $warResult;
    }

       function captureArtefact( $toVillageRow, $fromVillageRow ){
                $date = Date('Y/m/d H:i:s', strtotime("+3 hours"));
                $last_art_1 = $toVillageRow['art_last'];
                $last_art_2 = ",";
                $datee = Date('Y/m/d H:i:s');
                $last_art_3 = $fromVillageRow['player_id']."|".$fromVillageRow['player_name']."|".$fromVillageRow['id']."|".$fromVillageRow['village_name']."|".$datee;
        $last_art = $last_art_1.$last_art_2.$last_art_3;
                $this->provider->executeQuery(
            "UPDATE p_villages v
                SET v.artefacts = '%s', type = '%s', is_artefacts = '1' , art_date = NOW(), art_act = '%s' , art_last = '%s'
            WHERE v.id=%s",
            array($toVillageRow['artefacts'] , $toVillageRow['type'], $date,  $last_art, intval($fromVillageRow['id']))
        );
        
        $this->provider->executeQuery( 
            'UPDATE p_villages v
                SET v.artefacts = NULL, type = NULL, is_artefacts = \'0\'
            WHERE v.id=%s',
            array(intval($toVillageRow['id']))
        );


///Start Artefect
if ($toVillageRow['artefacts'] == 7) {
if ($toVillageRow['type'] == 4) {
$this->provider->executeQuery2("UPDATE p_villages SET art7=crop_consumption/2 WHERE player_id='".$fromVillageRow['player_id']."'");
$this->provider->executeQuery2("UPDATE p_villages SET crop_consumption=crop_consumption/2 WHERE player_id='".$fromVillageRow['player_id']."'");
$this->provider->executeQuery2("UPDATE p_villages SET crop_consumption=crop_consumption+art7 WHERE player_id='".$toVillageRow['player_id']."'");
$this->provider->executeQuery2("UPDATE p_villages SET art7=0 WHERE player_id='".$toVillageRow['player_id']."'");
}else if ($toVillageRow['type'] == 3) {
$this->provider->executeQuery2("UPDATE p_villages SET art7=crop_consumption/3 WHERE player_id='".$fromVillageRow['player_id']."'");
$this->provider->executeQuery2("UPDATE p_villages SET crop_consumption=crop_consumption/3 WHERE player_id='".$fromVillageRow['player_id']."'");
$this->provider->executeQuery2("UPDATE p_villages SET crop_consumption=crop_consumption+art7 WHERE player_id='".$toVillageRow['player_id']."'");
$this->provider->executeQuery2("UPDATE p_villages SET art7=0 WHERE player_id='".$toVillageRow['player_id']."'");
}else if ($toVillageRow['type'] == 2) {
$this->provider->executeQuery2("UPDATE p_villages SET art7=crop_consumption/2 WHERE id='".$fromVillageRow['id']."'");
$this->provider->executeQuery2("UPDATE p_villages SET crop_consumption=crop_consumption/2 WHERE id='".$fromVillageRow['id']."'");
$this->provider->executeQuery2("UPDATE p_villages SET crop_consumption=crop_consumption+art7 WHERE id='".$toVillageRow['id']."'");
$this->provider->executeQuery2("UPDATE p_villages SET art7=0 WHERE id='".$toVillageRow['id']."'");
}
}
///End Artefect




    }
    
 public function captureVillage( $toVillageRow, $fromVillageRow, $kingCropConumption )
 {
 $GameMetadata = $GLOBALS['GameMetadata'];
 $SetupMetadata = $GLOBALS['SetupMetadata'];
// fixing plus
 $tplus1 = "0";
 $fplus1 = "0";
 $plus1 = "0";
 $tplus2 = "0";
 $fplus2 = "0";
 $plus2 = "0";
 $tplus3 = "0";
 $fplus3 = "0";
 $plus3 = "0";
 $tplus4 = "0";
 $fplus4 = "0";
 $plus4 = "0";
 $result = $this->provider->fetchResultSet ("SELECT * FROM p_queue WHERE player_id='".$toVillageRow['player_id']."' AND proc_type='".QS_PLUS2."'");
 while ($result->next ())
 {
 $tplus1 = "1";
 }
 $result = $this->provider->fetchResultSet ("SELECT * FROM p_queue WHERE player_id='".$fromVillageRow['player_id']."' AND proc_type='".QS_PLUS2."'");
 while ($result->next ())
 {
 $fplus1 = "1";
 }
 if ( $fplus1.$tplus1 == "10" )
 {
 $plus1 = "1";
 }
 if ( $fplus1.$tplus1 == "01" )
 {
 $plus1 = "2";
 }
 $result = $this->provider->fetchResultSet ("SELECT * FROM p_queue WHERE player_id='".$toVillageRow['player_id']."' AND proc_type='".QS_PLUS3."'");
 while ($result->next ())
 {
 $tplus2 = "1";
 }
 $result = $this->provider->fetchResultSet ("SELECT * FROM p_queue WHERE player_id='".$fromVillageRow['player_id']."' AND proc_type='".QS_PLUS3."'");
 while ($result->next ())
 {
 $fplus2 = "1";
 }
 if ( $fplus2.$tplus2 == "10" )
 {
 $plus2 = "1";
 }
 if ( $fplus2.$tplus2 == "01" )
 {
 $plus2 = "2";
 }
 $result = $this->provider->fetchResultSet ("SELECT * FROM p_queue WHERE player_id='".$toVillageRow['player_id']."' AND proc_type='".QS_PLUS4."'");
 while ($result->next ())
 {
 $tplus3 = "1";
 }
 $result = $this->provider->fetchResultSet ("SELECT * FROM p_queue WHERE player_id='".$fromVillageRow['player_id']."' AND proc_type='".QS_PLUS4."'");
 while ($result->next ())
 {
 $fplus3 = "1";
 }
 if ( $fplus3.$tplus3 == "10" )
 {
 $plus3 = "1";
 }
 if ( $fplus3.$tplus3 == "01" )
 {
 $plus3 = "2";
 }
 $result = $this->provider->fetchResultSet ("SELECT * FROM p_queue WHERE player_id='".$toVillageRow['player_id']."' AND proc_type='".QS_PLUS5."'");
 while ($result->next ())
 {
 $tplus4 = "1";
 }
 $result = $this->provider->fetchResultSet ("SELECT * FROM p_queue WHERE player_id='".$fromVillageRow['player_id']."' AND proc_type='".QS_PLUS5."'");
 while ($result->next ())
 {
 $fplus4 = "1";
 }
 if ( $fplus4.$tplus4 == "10" )
 {
 $plus4 = "1";
 }
 if ( $fplus4.$tplus4 == "01" )
 {
 $plus4 = "2";
 }
 $resources = $toVillageRow['resources'];
 if ( $plus1 == "1" )
 {
 $tempdata1 = $resources;
 $tempdata2 = explode(",", $tempdata1);
 $tempdata3 = explode(" ", $tempdata2[0]);
 $tempdata4 = $tempdata3[count($tempdata3)-1]+25;
 $tempdata5 = str_replace($tempdata2[0], $tempdata3[0]." ".$tempdata3[1]." ".$tempdata3[2]." ".$tempdata3[3]." ".$tempdata3[4]." ".$tempdata4, $tempdata1);
 $resources = $tempdata5;
 }
 if ( $plus1 == "2" )
 {
 $tempdata1 = $resources;
 $tempdata2 = explode(",", $tempdata1);
 $tempdata3 = explode(" ", $tempdata2[0]);
 $tempdata4 = $tempdata3[count($tempdata3)-1]-25;
 $tempdata5 = str_replace($tempdata2[0], $tempdata3[0]." ".$tempdata3[1]." ".$tempdata3[2]." ".$tempdata3[3]." ".$tempdata3[4]." ".$tempdata4, $tempdata1);
 $resources = $tempdata5;
 }
 if ( $plus2 == "1" )
 {
 $tempdata1 = $resources;
 $tempdata2 = explode(",", $tempdata1);
 $tempdata3 = explode(" ", $tempdata2[1]);
 $tempdata4 = $tempdata3[count($tempdata3)-1]+25;
 $tempdata5 = str_replace($tempdata2[1], $tempdata3[0]." ".$tempdata3[1]." ".$tempdata3[2]." ".$tempdata3[3]." ".$tempdata3[4]." ".$tempdata4, $tempdata1);
 $resources = $tempdata5;
 }
 if ( $plus2 == "2" )
 {
 $tempdata1 = $resources;
 $tempdata2 = explode(",", $tempdata1);
 $tempdata3 = explode(" ", $tempdata2[1]);
 $tempdata4 = $tempdata3[count($tempdata3)-1]-25;
 $tempdata5 = str_replace($tempdata2[1], $tempdata3[0]." ".$tempdata3[1]." ".$tempdata3[2]." ".$tempdata3[3]." ".$tempdata3[4]." ".$tempdata4, $tempdata1);
 $resources = $tempdata5;
 }
 if ( $plus3 == "1" )
 {
 $tempdata1 = $resources;
 $tempdata2 = explode(",", $tempdata1);
 $tempdata3 = explode(" ", $tempdata2[2]);
 $tempdata4 = $tempdata3[count($tempdata3)-1]+25;
 $tempdata5 = str_replace($tempdata2[2], $tempdata3[0]." ".$tempdata3[1]." ".$tempdata3[2]." ".$tempdata3[3]." ".$tempdata3[4]." ".$tempdata4, $tempdata1);
 $resources = $tempdata5;
 }
 if ( $plus3 == "2" )
 {
 $tempdata1 = $resources;
 $tempdata2 = explode(",", $tempdata1);
 $tempdata3 = explode(" ", $tempdata2[2]);
 $tempdata4 = $tempdata3[count($tempdata3)-1]-25;
 $tempdata5 = str_replace($tempdata2[2], $tempdata3[0]." ".$tempdata3[1]." ".$tempdata3[2]." ".$tempdata3[3]." ".$tempdata3[4]." ".$tempdata4, $tempdata1);
 $resources = $tempdata5;
 }
 if ( $plus4 == "1" )
 {
 $tempdata1 = $resources;
 $tempdata2 = explode(",", $tempdata1);
 $tempdata3 = explode(" ", $tempdata2[3]);
 $tempdata4 = $tempdata3[count($tempdata3)-1]+25;
 $tempdata5 = str_replace($tempdata2[3], $tempdata3[0]." ".$tempdata3[1]." ".$tempdata3[2]." ".$tempdata3[3]." ".$tempdata3[4]." ".$tempdata4, $tempdata1);
 $resources = $tempdata5;
 }
 if ( $plus4 == "2" )
 {
 $tempdata1 = $resources;
 $tempdata2 = explode(",", $tempdata1);
 $tempdata3 = explode(" ", $tempdata2[3]);
 $tempdata4 = $tempdata3[count($tempdata3)-1]-25;
 $tempdata5 = str_replace($tempdata2[3], $tempdata3[0]." ".$tempdata3[1]." ".$tempdata3[2]." ".$tempdata3[3]." ".$tempdata3[4]." ".$tempdata4, $tempdata1);
 $resources = $tempdata5;
 }
// end fixing plus

        $this->provider->executeQuery( 'DELETE FROM p_queue WHERE player_id=%s AND village_id=%s AND proc_type IN (%s)', array( intval( $toVillageRow['player_id'] ), intval( $toVillageRow['id'] ), QS_BUILD_CREATEUPGRADE.','.QS_BUILD_DROP.','.QS_TROOP_RESEARCH.','.QS_TROOP_UPGRADE_ATTACK.','.QS_TROOP_UPGRADE_DEFENSE.','.QS_TROOP_TRAINING.','.QS_TROOP_TRAINING_HERO.','.QS_WAR_ATTACK.','.QS_WAR_ATTACK_PLUNDER.','.QS_WAR_ATTACK_SPY.','.QS_CREATEVILLAGE ) );
        $troops_training = '';
        $troops_num = '';
        
        foreach ( $GameMetadata['troops'] as $k => $v ){
            if ( $v['for_tribe_id'] == -1 || $v['for_tribe_id'] == $fromVillageRow['tribe_id'] ){
                if ( $troops_training != '' ){
                    $troops_training .= ',';
                }
                
                $researching_done = $v['research_time_consume'] == 0 ? 1 : 0;
                $troops_training .= $k . ' ' . $researching_done . ' 0 0';
                if ( $troops_num != '' ){
                    $troops_num .= ',';
                }
                $troops_num .= $k . ' 0';
            }
        }
        
        $troops_num = '-1:'.$troops_num;
        
        $buildings = '';
        $reCheckBuilding = $this->provider->fetchScalar( 'SELECT v.buildings FROM p_villages v WHERE v.id=%s',array(intval($toVillageRow['id'])));
        $bStr = trim( $reCheckBuilding );
        
        if ( $bStr != '' ){
            $bStrArr = explode( ',', $bStr );
            $mq = new QueueJobModel();
            $ccb = 0;
            foreach ( $bStrArr as $b2Str ){
                ++$ccb;
                if ( $buildings != '' ){ $buildings .= ','; }

                list($item_id, $level, $update_state) = explode(' ', $b2Str);
                
                if ( !isset( $GameMetadata['items'][$item_id]['for_tribe_id'][$fromVillageRow['tribe_id']] ) ){
                    while ( 0 < $level-- ){
                        $mq->upgradeBuilding( $toVillageRow['id'], $ccb, $item_id, TRUE );
                    }
                    
                    $item_id = $level = $update_state = 0;
                }
                
                $buildings .= $item_id . ' ' . $level . ' ' . $update_state;
            }
        }

 $this->provider->executeQuery( "UPDATE p_villages v SET
v.tribe_id=%s,
v.player_id=%s,
v.alliance_id=%s,
v.player_name='%s',
v.alliance_name='%s',
v.troops_num=NULL,
v.troops_out_num=NULL,
v.troops_out_intrap_num=NULL 
 WHERE v.parent_id=%s AND v.is_oasis=1", array(
intval( $fromVillageRow['tribe_id'] ),
intval( $fromVillageRow['player_id'] ),
0 < intval( $fromVillageRow['alliance_id'] ) ? intval( $fromVillageRow['alliance_id'] ) : "NULL",
$fromVillageRow['player_name'],
$fromVillageRow['alliance_name'],
intval( $toVillageRow['id'] )
) );
 $this->provider->executeQuery( "UPDATE p_villages v
 SET
 v.parent_id=%s,
 v.tribe_id=%s,
 v.player_id=%s,
 v.alliance_id=%s,
 v.player_name='%s',
 v.alliance_name='%s',
 v.is_capital=0,
 v.resources='%s',
 v.buildings='%s',
 v.troops_training='%s',
 v.troops_num='%s',
 v.child_villages_id=NULL,
 v.allegiance_percent=1,
 v.troops_out_num=NULL,
 v.troops_out_intrap_num=NULL,
 v.creation_date=NOW(),
 v.last_update_date=NOW()
 WHERE v.id=%s", array(
 intval( $fromVillageRow['id'] ),
 intval( $fromVillageRow['tribe_id'] ),
 intval( $fromVillageRow['player_id'] ),
 0 < intval( $fromVillageRow['alliance_id'] ) ? intval( $fromVillageRow['alliance_id'] ) : "NULL",
 $fromVillageRow['player_name'],
 $fromVillageRow['alliance_name'],
 $resources,
 $buildings,
 $troops_training,
 $troops_num,
 intval( $toVillageRow['id'] )
 ) );

 $child_villages_id = trim( $fromVillageRow['child_villages_id'] );
 if ( $child_villages_id != "" )
 {
 $child_villages_id .= ",";
 }
 $child_villages_id .= $toVillageRow['id'];
 $this->provider->executeQuery( "UPDATE p_villages v\r\n\t\t\tSET\r\n\t\t\t\tv.crop_consumption=v.crop_consumption-%s,\r\n\t\t\t\tv.child_villages_id='%s'\r\n\t\t\tWHERE v.id=%s", array(
 $kingCropConumption,
 $child_villages_id,
 intval( $fromVillageRow['id'] )
 ) );
 $prow = $this->provider->fetchRow( "SELECT p.villages_id, p.villages_data FROM p_players p WHERE p.id=%s", array(
 intval( $fromVillageRow['player_id'] )
 ) );
 $villages_id = trim( $prow['villages_id'] );
 if ( $villages_id != "" )
 {
 $villages_id .= ",";
 }
 $villages_id .= $toVillageRow['id'];
 $villages_data = trim( $prow['villages_data'] );
 if ( $villages_data != "" )
 {
 $villages_data .= "\n";
 }
 $villages_data .= $toVillageRow['id']." ".$toVillageRow['rel_x']." ".$toVillageRow['rel_y']." ".$toVillageRow['village_name'];
 $this->provider->executeQuery( "UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.total_people_count=p.total_people_count+%s,\r\n\t\t\t\tp.villages_count=p.villages_count+1,\r\n\t\t\t\tp.selected_village_id=%s,\r\n\t\t\t\tp.villages_id='%s',\r\n\t\t\t\tp.villages_data='%s'\r\n\t\t\tWHERE\r\n\t\t\t\tp.id=%s", array(
 intval( $toVillageRow['people_count'] ),
 intval( $toVillageRow['id'] ),
 $villages_id,
 $villages_data,
 intval( $fromVillageRow['player_id'] )
 ) );
}


function leaveVillage( $villageId, $playerId, $village_people_count, $parent_id, $doReset = TRUE ){
$prow = $this->provider->fetchRow( 'SELECT p.villages_data, p.villages_id FROM p_players p WHERE p.id=%s', array( intval( $playerId ) ) );
$villages_id = trim( $prow['villages_id'] );
if ( $villages_id != '' ){
$villages_idArr = explode( ',', $villages_id );
$villages_id = '';

foreach ( $villages_idArr as $villages_idArrItem ){
if ( $villages_idArrItem == $villageId ){continue;}
if ( $villages_id != '' ){
$villages_id .= ',';
}
$villages_id .= $villages_idArrItem;
}
}

$villages_data = trim( $prow['villages_data'] );
if ( $villages_data != '' ){
$villages_dataArr = explode( "\n", $villages_data );
$villages_data = '';
foreach ( $villages_dataArr as $villages_dataArrItem ){
$_varr = explode( ' ', $villages_dataArrItem );
if ( $_varr[0] == $villageId ){continue;}

if ( $villages_data != '' ){
$villages_data .= "\n";
}

$villages_data .= implode( ' ', $_varr );
}
}

$this->provider->executeQuery( 'DELETE FROM p_merchants WHERE village_id=%s', array( intval( $villageId ) ) );
if ( trim( $parent_id ) != '' ){
$prow = $this->provider->fetchRow( 'SELECT v.child_villages_id FROM p_villages v WHERE v.id=%s', array( intval( $parent_id ) ) );
$child_villages_id = trim( $prow['child_villages_id'] );

if ( $child_villages_id != '' ){
$villages_idArr = explode( ',', $child_villages_id );
$child_villages_id = '';
foreach ( $villages_idArr as $villages_idArrItem ){
if ( $villages_idArrItem == $villageId ){continue;}

if ( $child_villages_id != '' ){
$child_villages_id .= ',';
}
$child_villages_id .= $villages_idArrItem;
}
}
$this->provider->executeQuery( 
'UPDATE p_villages v 
SET 
v.child_villages_id=\'%s\'
WHERE v.id=%s', array( $child_villages_id, intval( $parent_id ) ) );
}
$troopsOutNum = $this->provider->fetchScalar( 'SELECT v.troops_out_num FROM p_villages v WHERE v.id=%s',array(intval($villageId)));
$tsOutNum = trim($troopsOutNum);
if($tsOutNum != ''){
$t_arr = explode('|', $tsOutNum);
foreach ($t_arr as $t_str) {
list ($invid, $tvtroopsStr) = explode( ':', $t_str );
$t_Arr = explode (',', $tvtroopsStr);
foreach ($t_Arr as $_ArrStr) {
list ($_tid, $_tnum) = explode (' ', $_ArrStr);
if($_tnum == -1) {
$capital_village_id = intval( $this->provider->fetchScalar( 'SELECT v.id FROM p_villages v WHERE v.player_id=%s AND v.is_capital=1', array( intval( $playerId ) ) ) );

$this->provider->executeQuery( 
'UPDATE p_players p
SET
p.hero_troop_id=NULL,
p.hero_in_village_id=NULL
WHERE p.id=%s', array(intval($playerId))
);
}
}
$inVillageTroops = $this->provider->fetchScalar( 'SELECT v.troops_num FROM p_villages v WHERE v.id=%s',array(intval($invid)));

$vtroops_num = '';
$tsInVillage = trim($inVillageTroops);
if($tsInVillage != ''){
$t_arr2 = explode( '|', $tsInVillage );
foreach( $t_arr2 as $t_str2 ) {
list ($vid, $vtroopsStr) = explode( ':', $t_str2 );
if(intval($vid) == intval($villageId)){ continue; }
if ($vtroops_num != '') { $vtroops_num .= '|'; }
$vtroops_num .= $vid . ':' . $vtroopsStr;
}
}
$this->provider->executeQuery( 
'UPDATE p_villages v
SET
v.troops_num=\'%s\'
WHERE v.id=%s', array($vtroops_num, intval($invid))
);
}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////
$this->provider->executeQuery("UPDATE p_villages SET crop_consumption=people_count WHERE id='".$villageId."'");
$this->provider->executeQuery( "DELETE FROM p_queue WHERE player_id='".$playerId."' AND village_id='".$villageId."' AND proc_type  ='13' " );
$this->provider->executeQuery( "DELETE FROM p_queue WHERE player_id='".$playerId."' AND village_id='".$villageId."' AND proc_type  ='13' " );
$this->provider->executeQuery( 'DELETE FROM p_queue WHERE player_id=%s AND village_id=%s AND proc_type IN (%s)', array( intval( $playerId ), intval( $villageId ), QS_BUILD_CREATEUPGRADE.','.QS_BUILD_DROP.','.QS_TROOP_RESEARCH.','.QS_TROOP_UPGRADE_ATTACK.','.QS_TROOP_UPGRADE_DEFENSE.','.QS_TROOP_TRAINING.','.QS_TROOP_TRAINING_HERO.','.QS_WAR_ATTACK.','.QS_WAR_ATTACK_PLUNDER.','.QS_WAR_ATTACK_SPY.','.QS_CREATEVILLAGE ) );
$newvc = $this->provider->fetchRow( "SELECT `id` FROM `p_villages` WHERE player_id = '".$playerId."' AND is_oasis=0 AND id != '".$villageId."' ORDER BY  `people_count` DESC LIMIT 1" );
$nvc = $newvc['id'];
$this->provider->executeQuery("UPDATE p_players SET selected_village_id=".$nvc." WHERE id='".$playerId."'");
$showtatarzx = $this->provider->fetchResultSet( "SELECT proc_params,id FROM p_queue WHERE to_village_id='".$villageId."' AND proc_type  = '12' ");
while($showtatarzx->next ()) {
$_arr = explode( "|", $showtatarzx->row['proc_params'] );
$troopsBack = $_arr[sizeof( $_arr ) - 1] == 1;
if ($troopsBack) {
$this->provider->executeQuery( "DELETE FROM p_queue WHERE id='".$showtatarzx->row['id']."'" );
}
}
/////////////////////////////////////////////////////////////////////////////////////////////////////


if ( $doReset ){
$cap = $this->provider->fetchRow( "SELECT `is_capital` FROM `p_villages` WHERE id = '".$villageId."' " );
if ($cap['is_capital']) {
$newvcx = $this->provider->fetchRow( "SELECT `id` FROM `p_villages` WHERE player_id = '".$playerId."' AND is_oasis=0 AND id != '".$villageId."' ORDER BY  `people_count` DESC LIMIT 1" );
$nvc = $newvcx['id'];
$this->provider->executeQuery("UPDATE p_villages SET is_capital=1 WHERE id='".$nvc."'");
}
$this->provider->executeQuery( 
'UPDATE p_villages v 
SET 
v.tribe_id=IF(v.is_oasis=1, 4, 0),
v.parent_id=NULL,
v.player_id=NULL,
v.alliance_id=NULL,
v.player_name=NULL,
v.village_name=NULL,
v.alliance_name=NULL,
v.is_capital=0,
v.people_count=2,
v.crop_consumption=2,
v.time_consume_percent=100,
v.offer_merchants_count=0,
v.resources=NULL,
v.cp=NULL,
v.buildings=NULL,
v.troops_training=NULL,
v.child_villages_id=NULL,
v.village_oases_id=NULL,
v.troops_trapped_num=0,
v.allegiance_percent=100,
v.troops_num=IF(v.is_oasis=1, \'-1:31 0,34 0,37 0\', NULL),
v.troops_out_num=NULL,
v.troops_intrap_num=NULL,
v.troops_out_intrap_num=NULL,
v.creation_date=NOW()
WHERE v.id=%s OR (v.parent_id=%s AND v.is_oasis=1)', 
array( 
intval( $villageId ), 
intval( $villageId ) 
) 
);

$this->provider->executeQuery( 
'UPDATE p_villages v 
SET 
v.parent_id=NULL
WHERE v.parent_id=%s', 
array( intval( $villageId ) ) 
);

}

$this->provider->executeQuery( 
'UPDATE p_players p 
SET 
p.total_people_count=IF(p.total_people_count-%s<0, 0, p.total_people_count-%s),
p.villages_count=IF(p.villages_count-1<1, 1, p.villages_count-1),
p.villages_id=\'%s\',
p.villages_data=\'%s\'
WHERE p.id=%s', 
array( 
intval( $village_people_count ), 
intval( $village_people_count ), 
$villages_id, 
$villages_data, 
intval( $playerId ) 
) 
);
}
    
    function _harvestTroopsFrom ($villageRow, $maxCarryLoad, $crannyTotalSize) {
        if ($maxCarryLoad <= 0) {
            return array ('string' =>'0 0 0 0' , 'sum' => 0);
        }

        $resources = array ();
        $r_arr = explode (',', $villageRow['resources']);
        foreach( $r_arr as $r_str ) {
            $r2 = explode( ' ', $r_str );

            $prate                 = floor( $r2[4] * ( 1 + $r2[5]/100 ) ) - (($r2[0]==4)? $villageRow['crop_consumption'] : 0);
            $current_value     = floor ($r2[1] + $villageRow['elapsedTimeInSeconds'] * ($prate/3600));
           if ($current_value > $r2[2]) {
                $current_value = $r2[2];
            }

            $resources[ $r2[0] ] = array (
                'current_value'                =>    $current_value - $crannyTotalSize,
                'store_max_limit'            =>    $r2[2],
                'store_init_limit'                =>    $r2[3],
                'prod_rate'                        =>    $r2[4],
                'prod_rate_percentage'    =>    $r2[5]
            );
        }

                $fair = $maxCarryLoad;
        $divFactor = 4;
        $harvest = array (0, 0, 0, 0);
        $sum = 0;
                $loop = 0;
        while ($maxCarryLoad>0) {
            $curTotalRes = 0;
            $m = 0;
            foreach ($resources as $k=>$rdata) {
                $v = $rdata['current_value'];
                                if($loop == 0) {
                $take = floor ($fair/$divFactor);
                                } else {
                                $take = floor ($maxCarryLoad/$divFactor);
                                }
                if ($v > 0) {
                    if ($v < $take) { $take = $v; }
                    $maxCarryLoad -= $take;
                    $resources[$k]['current_value'] -= $take;
                    $harvest[$m] += $take;
                    $sum += $take;
                    $curTotalRes += $resources[$k]['current_value'];
                }
                $m++;
            }
                        $fair -= $sum;
            if ($curTotalRes <= 0 && $divFactor == 1) { break; }
            $divFactor -= 1;
                        $loop += 1;
        }

        $resourcesStr = '';
        foreach ($resources as $k=>$v) {
            if ($resourcesStr != '') {
                $resourcesStr .= ',';
            }

            $resourcesStr .= sprintf ('%s %s %s %s %s %s', $k, $v['current_value'] + $crannyTotalSize, $v['store_max_limit'], $v['store_init_limit'], $v['prod_rate'], $v['prod_rate_percentage']);
        }

        // calc the cp
        $elapsedTimeInSeconds = $villageRow['elapsedTimeInSeconds'];
        if(!$villageRow['is_oasis']){
            list ($cpValue, $cpRate) = explode (' ', $villageRow['cp']);
            $cpValue = floor ($cpValue + $elapsedTimeInSeconds * ($cpRate/86400));
            $cp = $cpValue . ' ' . $cpRate;
        }
        // affect the to village
        $this->provider->executeQuery (
            'UPDATE p_villages v
            SET
                v.resources=\'%s\',
                v.cp=\'%s\',
                v.last_update_date=NOW()
            WHERE
                v.id=%s',
            array ($resourcesStr, $villageRow['is_oasis'] ? '' : $cp, intval( $villageRow['id'] ))
        );

        return array ('string' =>implode (' ', $harvest) , 'sum' => $sum);
    }

    
    function __getCoordInRange ($map_size, $x)
    {
      if ($map_size <= $x)
      {
        $x -= $map_size;
      }
      else
      {
        if ($x < 0)
        {
          $x = $map_size + $x;
        }
      }

      return $x;
    }

    function __getVillageId ($map_size, $x, $y)
    {
      return $x * $map_size + ($y + 1);
    }
  }

?>