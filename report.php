<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."report.php" );
class GPage extends SecureGamePage
{

    public $showList;
    public $selectedTabIndex;
    public $test = 100;
    public $reportData;
    public $dataList;
    public $pageSize = 20;
    public $pageCount;
    public $pageIndex;

    public function GPage( )
    {
        parent::securegamepage( );
        $this->viewFile = "report.phtml";
        $this->contentCssClass = "reports";
    }

    public function load( )
    {
        parent::load( ); 
        $this->showList = !( isset( $_GET['id'] ) && 0 < intval( $_GET['id'] ) );
        $this->selectedTabIndex = $this->showList && isset( $_GET['t'] ) && is_numeric( $_GET['t'] ) && 1 <= intval( $_GET['t'] ) && intval( $_GET['t'] ) <= 6 ? intval( $_GET['t'] ) : 0;
        $m = new ReportModel( );

        if(isset($_GET['dellall']))
        {
            if ($_GET['dellall'] >= 0 && $_GET['dellall'] <= 4)
            {
                $qj = new QueueJobModel ();
                if ($_GET['dellall']!=0)
                {
                    $dlal = $qj->provider->fetchResultSet( "SELECT id FROM p_rpts WHERE ((to_player_id=".$this->player->playerId." AND delete_status!=1) OR (from_player_id=".$this->player->playerId." AND delete_status!=2)) AND rpt_cat=".$_GET['dellall']."");
                }
                else
                {
                    $dlal = $qj->provider->fetchResultSet( "SELECT id FROM p_rpts WHERE ((to_player_id=".$this->player->playerId." AND delete_status!=1) OR (from_player_id=".$this->player->playerId." AND delete_status!=2))");
                }
                while($dlal->next ())
                {
                    if ( $m->deleteReport( $this->player->playerId, $dlal->row['id'] ) )
                    {
                        --$this->data['new_report_count'];
                    }
                }
            }
        }
        else if(isset($_GET['del']) && $this->data['active_plus_account'] == 1)
        {
            if($m->deleteReport( $this->player->playerId, $_GET['del']))
            {
                --$this->data['new_report_count'];
            }
        }
        else if(isset($_GET['archiv']) && $this->data['active_plus_account'] == 1)
        {
            $conekt_sher = $m->getReport($_GET['archiv']);
            if ( $conekt_sher->next( ) )
            {
                $from_player_id = $conekt_sher->row['from_player_id'];
                $to_player_id   = $conekt_sher->row['to_player_id'];
                if($this->player->playerId == $from_player_id)
                {
                    $archiv    = 'form_archiv';
                    $player_id = 'from_player_id';
                }
                else
                {
                    $archiv    = 'to_archiv';
                    $player_id = 'to_player_id';
                }
                $m->deletearchiv($this->player->playerId, $player_id, $_GET['archiv'], $archiv);
            }
        }



        if ( !$this->isPost( ) )
        {
            if ( !$this->showList )
            {
                $this->selectedTabIndex = 0;
                $reportId = intval( $_GET['id'] );
                $result = $m->getReport( $reportId );
                if ( $result->next( ) )
                {
                    
                    $readStatus = $result->row['read_status'];
                    $deleteStatus = $result->row['delete_status'];
                    $this->reportData = array( );
                    $this->reportData['messageDate'] = $result->row['mdate'];
                    $this->reportData['messageTime'] = $result->row['mtime'];
                    $this->reportData['from_player_id'] = $from_player_id = intval( $result->row['from_player_id'] );
                    $this->reportData['to_player_id'] = $to_player_id = intval( $result->row['to_player_id'] );
                    $this->reportData['from_village_id'] = intval( $result->row['from_village_id'] );
                    $this->reportData['to_village_id'] = intval( $result->row['to_village_id'] );
                    $this->reportData['from_player_name'] = $result->row['from_player_name'];
                    $this->reportData['to_player_name'] = $result->row['to_player_name'];
                    $this->reportData['to_village_name'] = $result->row['to_village_name'];
                    $this->reportData['from_village_name'] = $result->row['from_village_name'];
                    $this->reportData['rpt_body'] = $result->row['rpt_body'];
                    $this->reportData['rpt_cat'] = $result->row['rpt_cat'];
                    $this->reportData['mdate'] = $result->row['mdate'];
                    $this->reportData['mtime'] = $result->row['mtime'];
                    $this->reportData['to_player_alliance_id'] = $m->getPlayerAllianceId( $to_player_id );
                    $this->reportData['from_player_alliance_id'] = $m->getPlayerAllianceId( $this->reportData['from_village_id'] );
                    switch ( $this->reportData['rpt_cat'] )
                    {
                        case 1:
                            $this->reportData['resources'] = explode(' ', $this->reportData['rpt_body']);
                            break;
                        case 2:
                            list($troopsStr, $this->reportData['cropConsume']) = explode('|', $this->reportData['rpt_body']);
                            $this->reportData['troopsTable'] = array(
                                'troops' => array(),
                                'hasHero' => FALSE
                            );
                            $troopsStrArr                    = explode(',', $troopsStr);
                                                        foreach ( $troopsStrArr as $t )
                            {
                                $tnum = explode( " ", $t );
                                $tid = explode( " ", $t );
                                list( $tid, $tnum ) = $tid;
                                if ( $tnum == 0 - 1 )
                                {
                                    $this->reportData['troopsTable']['hasHero'] = TRUE;
                                }
                                else
                                {
                                    $this->reportData['troopsTable']['troops'][$tid] = $tnum;
                                }
                            }
                            break;
                        case 3 :
                            $bodyArr = explode( "|", $this->reportData['rpt_body'] );
                            $harvestResources = $bodyArr;
                            $total_carry_load = $bodyArr;
                            $defenseTableTroopsStr = $bodyArr;
                            $attackTroopsStr = $bodyArr;
                            list( $attackTroopsStr, $defenseTableTroopsStr, $total_carry_load, $harvestResources ) = $attackTroopsStr;
                            $wallDestructionResult = isset( $bodyArr[4] ) ? $bodyArr[4] : "";
                            $catapultResult = isset( $bodyArr[5] ) ? $bodyArr[5] : "";
                            $oasisResult = isset( $bodyArr[6] ) ? $bodyArr[6] : "";
                            $captureResult = isset( $bodyArr[7] ) ? $bodyArr[7] : "";
                            $artefactResult = isset( $bodyArr[8] ) ? $bodyArr[8] : "";
                            $silverResult  = isset( $bodyArr[9] ) ? $bodyArr[9] : "";

                            $fff = explode(':', $bodyArr[10]);
                            $trapsResult  = isset( $bodyArr[10] ) && $fff[1] != 0 ? $fff[1] : 0;

                            $xfff = explode(':', $bodyArr[11]);
                            $raidsR  = isset( $bodyArr[11] ) && $xfff[1] != 0 ? $xfff[1] : 0;

                            $this->reportData['raidsR'] = $raidsR;
                            $this->reportData['trapsResult'] = $trapsResult;
                            $this->reportData['silverResult'] = $silverResult;
                            $this->reportData['total_carry_load'] = $total_carry_load;
                            $this->reportData['total_harvest_carry_load'] = 0;
                            $this->reportData['harvest_resources'] = array( );
                            $res = explode( " ", $harvestResources );
                            foreach ( $res as $r )
                            {
                                    $this->reportData['total_harvest_carry_load'] += $r;
                                    $this->reportData['harvest_resources'][] = $r;
                            }
                             $attackTroopsStrArr = explode( ",", $attackTroopsStr );
                                $this->reportData['attackTroopsTable'] = array(
                                    "troops" => array( ),
                                    "heros" => array( "number" => 0, "dead_number" => 0, "trap_number" => 0 )
                                );
                                $totalAttackTroops_live = 0;
                                $totalAttackTroops_dead = 0;
                                $attackWallDestrTroopId = 0;
                                $attackCatapultTroopId = 0;
                                $kingTroopId = 0;
                                $toops_number = 0;
                                $dead_number  = 0; 
                                $this->trap_number  = 0;
                                foreach ( $attackTroopsStrArr as $s )
                                {
                                        $deadNum = explode( " ", $s );
                                        $num = explode( " ", $s );
                                        $tid = explode( " ", $s );
                                        list( $tid, $num, $deadNum, $trapNum ) = $tid;                                   
                                        $totalAttackTroops_live += $num;
                                        $totalAttackTroops_dead += $deadNum;
                                        if ( $tid == 7 || $tid == 17 || $tid == 27 || $tid == 106 || $tid == 57|| $tid == 67|| $tid == 77 )
                                        {
                                            $attackWallDestrTroopId = $tid;
                                        }
                                        else if ( $tid == 8 || $tid == 18 || $tid == 28 || $tid == 107 || $tid == 58|| $tid == 68|| $tid == 78 )
                                        {
                                            $attackCatapultTroopId = $tid;
                                        }
                                        else if ( $tid == 9 || $tid == 19 || $tid == 29 || $tid == 108 || $tid == 59|| $tid == 69|| $tid == 79 )
                                        {
                                            $kingTroopId = $tid;
                                        }
                                        if ( $tid == 0 - 1 )
                                        {
                                            $this->reportData['attackTroopsTable']['heros']['number'] = $num;
                                            $this->reportData['attackTroopsTable']['heros']['dead_number'] = $deadNum;
                                            $this->reportData['attackTroopsTable']['heros']['trap_number'] = $trapNum;
                                        }
                                        else
                                        {
                                            $toops_number += $num;
                                            $dead_number  += $deadNum; 
                                            $this->trap_number  += $trapNum; 
                                            $this->reportData['attackTroopsTable']['troops'][$tid] = array( "number" => $num, "dead_number" => $deadNum , "trap_number" => $trapNum );
                                        }
                                }
                                $this->reportData['all_attackTroops_dead'] = $totalAttackTroops_live <= $totalAttackTroops_dead;

                                // send again
                                if( $this->data['active_plus_account'] == 1 && $this->reportData['from_player_id'] == $this->player->playerId && $this->reportData['rpt_cat'] == 3 && isset($_GET['again']) && isset($_GET['key']) && isset($_GET['c']) && $_GET['key'] == md5('report-'.intval($_GET['id'])) && ($_GET['c'] == 3 || $_GET['c'] == 4))
                                {
                                    require_once( LIB_PATH . "bot.php" );
                                    $data = array();
                                    foreach ($this->reportData['attackTroopsTable']['troops'] as $tid => $tnum)
                                    {
                                        $data['t['. $tid .']'] = $tnum['number'];
                                    }
                                    if($this->reportData['attackTroopsTable']['heros']['number'] != 0)
                                    {
                                        $data['_t'] = $this->reportData['attackTroopsTable']['heros']['number'];
                                    }
                                    $data['c']  = intval($_GET['c']);
                                    $data['id'] = $this->reportData['to_village_id'];

                                    $COOKIE = COOKIES_PATH . $this->player->playerId .'.cookie';
                                    $link   = 'http://' . WebHelper::getdomain();
                                    $bot    = new bot($link, $COOKIE);
                                    $bot->login_to_server('login?login_as_boot&is_phantom', array('email' => $this->data['name'], 'password' => $this->data['pwd1'], 'captcha' => 'login_as_boot'));
                                    $bot->post_page('v2v?boot', $data);
                                    header ("Location: village1");
                                    exit;
                                    
                                }


                                $this->reportData['defenseTroopsTable'] = array( );
                                $troopsTableStrArr = trim( $defenseTableTroopsStr ) == "" ? array( ) : explode( "#", $defenseTableTroopsStr );
                                $j = 0 - 1;
                                foreach ( $troopsTableStrArr as $defenseTableTroopsStr2 )
                                {
                                    ++$j;
                                    $defenseTroopsStrArr = explode( ",", $defenseTableTroopsStr2 );
                                    $this->reportData['defenseTroopsTable'][$j] = array( "troops" => array( ), "heros" => array( "number" => 0, "dead_number" => 0 ) );
                                    foreach ( $defenseTroopsStrArr as $s )
                                    {
                                       $deadNum = explode( " ", $s );
                                        $num = explode( " ", $s );
                                        $tid = explode( " ", $s );
                                        list( $tid, $num, $deadNum ) = $tid;  
                                        $d_live = 0;                                 
                                        $d_dead = 0;                                 
                                        $d_live += $num;
                                        $d_dead += $deadNum;
                                        if ($d_live != 0) {
                                        $this->test = ($d_dead/$d_live*100);
                                        }
                                         if ( $tid == 0 - 1 )
                                        {
                                            $this->reportData['defenseTroopsTable'][$j]['heros']['number'] = $num;
                                            $this->reportData['defenseTroopsTable'][$j]['heros']['dead_number'] = $deadNum;
                                        }
                                        else
                                        {
                                            $this->reportData['defenseTroopsTable'][$j]['troops'][$tid] = array( "number" => $num, "dead_number" => $deadNum );
                                        }
                                    }
                                }

                                if(isset($this->reportData['defenseTroopsTable'][0]['troops'][21]))
                                {
                                    if(($this->trap_number+$dead_number) == $toops_number)
                                    {
                                        $this->reportData['all_attackTroops_dead'] = true;
                                        $this->test=0;
                                    }
                                    
                                }

                            if ( $artefactResult != "" )
                            {            
                                $wstr = "";
                                if ($artefactResult == "+")
                                {                   
                                    $wstr = REPORT_PHP1;
                                    $wstr = "<img src=\"ftd-style/x.gif\" class=\"unit uhero\" align=\"center\" /> ".$wstr;
                                }
                                else if ($artefactResult == "-1")
                                {
                                    $wstr = REPORT_PHP2;
                                    $wstr = "<img src=\"ftd-style/x.gif\" class=\"unit uhero\" align=\"center\" /> ".$wstr;
                                }
                                else if ($artefactResult == "-2")
                                {
                                    $wstr = REPORT_PHP3;
                                    $wstr = "<img src=\"ftd-style/x.gif\" class=\"unit uhero\" align=\"center\" /> ".$wstr;
                                }
                                else if ($artefactResult == "-3")
                                {
                                    $wstr = REPORT_PHP4;
                                    $wstr = "<img src=\"ftd-style/x.gif\" class=\"unit uhero\" align=\"center\" /> ".$wstr;
                                }
                                $this->reportData['artefactResult'] = $wstr;
                            }
                            if ( $captureResult != "" )
                            {
                                $wstr = "";
                                if ( $captureResult == "#" )
                                {
                                    $wstr = REPORT_PHP5;
                                }
                                else
                                if ( $captureResult == "+" )
                                {
                                    $wstr = REPORT_PHP6;
                                }
                                else
                                {
                                    $warr = explode( "-", $captureResult );
                                    $wstr = REPORT_PHP7." ".$warr[0]." ".REPORT_PHP13." ".$warr[1];
                                }
                                if ( $wstr != "" )
                                {
                                    $wstr = "<img src=\"ftd-style/x.gif\" class=\"unit u".$kingTroopId."\" align=\"center\" /> ".$wstr;
                                }
                                $this->reportData['captureResult'] = $wstr;
                            }
                            if ( $oasisResult != "" )
                            {
                                $wstr = "";
                                if ( $oasisResult == "+" )
                                {
                                $id = $this->reportData['to_village_id'];
                                $ma = new ReportModel();
                                $this->art = $ma->GetReportArt($id);
                                if ( $this->art['is_artefact'] == 1 ) {
                                    $wstr = REPORT_PHP1;
                                }
                                else
                                {
                                    $wstr = REPORT_PHP8;
                                 }
                                }
                                else
                                {
                                    $warr = explode( "-", $oasisResult );
                                    $wstr = REPORT_PHP7." ".$warr[0]." ".REPORT_PHP13." ".$warr[1];
                                }
                                if ( $wstr != "" )
                                {
                                    $wstr = "<img src=\"ftd-style/x.gif\" class=\"unit uhero\" align=\"center\" /> ".$wstr;
                                }
                                $this->reportData['oasisResult'] = $wstr;
                            }
                            if ( $wallDestructionResult != "" )
                            {
                                $wstr = "";
                                if ( $wallDestructionResult == "-" )
                                {
                                    $wstr = REPORT_PHP9;
                                }
                                else if ( $wallDestructionResult == "+" )
                                {
                                    $wstr = REPORT_PHP10;
                                }
                                else
                                {
                                    $warr = explode( "-", $wallDestructionResult );
                                    if ( $warr[1] == 0 )
                                    {
                                        $wstr = REPORT_PHP11;
                                    }else {
                                    $wstr = REPORT_PHP12." ".$warr[0]." ".REPORT_PHP13." ".$warr[1];
                                }
                                  }
                                if ( $wstr != "" )
                                {
                                    $wstr = "<img src=\"ftd-style/x.gif\" class=\"unit u".$attackWallDestrTroopId."\" align=\"center\" /> ".$wstr;
                                }
                                $this->reportData['wallDestructionResult'] = $wstr;
                            }
                            if ( $catapultResult != "" )
                            {
                                $bdestArr = array( );
                                if ( $catapultResult == "+" )
                                {
                                    $bdestArr[] = "<img src=\"ftd-style/x.gif\" class=\"unit u".$attackCatapultTroopId."\" align=\"center\" /> ".REPORT_PHP14;
                                }
                                else
                                {
                                    $catapultResultArr = explode( "#", $catapultResult );
                                    foreach ( $catapultResultArr as $catapultResultInfo )
                                    {
                                           $toLevel = explode( " ", $catapultResultInfo );
                                            $fromLevel = explode( " ", $catapultResultInfo );
                                            $itemId = explode( " ", $catapultResultInfo );
                                            $sss = explode( " ", $catapultResultInfo );
                                            list( $itemId, $fromLevel, $toLevel ,$sss ) = $itemId;

                                            if ( $sss == "+" )
                                            {
                                                $bdestArr[] = "<img src=\"ftd-style/x.gif\" class=\"unit u".$attackCatapultTroopId."\" align=\"center\" /> ".REPORT_PHP14;

                                            }
                                            if (!isset($toLevel))
                                            {
                                                $bdestArr[] = "<img src=\"ftd-style/x.gif\" class=\"unit u".$attackCatapultTroopId."\" align=\"center\" /> ".REPORT_PHP15." ".constant( "item_".$itemId );
                                            }
                                            else if ( $toLevel == 0 - 1 )
                                            {
                                                $bdestArr[] = "<img src=\"ftd-style/x.gif\" class=\"unit u".$attackCatapultTroopId."\" align=\"center\" /> ".REPORT_PHP15." ".constant( "item_".$itemId );
                                            }
                                            else if ( $toLevel == 0 )
                                            {
                                                $bdestArr[] = "<img src=\"ftd-style/x.gif\" class=\"unit u".$attackCatapultTroopId."\" align=\"center\" /> ".REPORT_PHP16." ".constant( "item_".$itemId );
                                            }
                                            else if ($toLevel)
                                            {
                                                $bdestArr[] = "<img src=\"ftd-style/x.gif\" class=\"unit u".$attackCatapultTroopId."\" align=\"center\" /> ".REPORT_PHP17." ".constant( "item_".$itemId )." ".REPORT_PHP18." ".$fromLevel." ".REPORT_PHP13." ".$toLevel;
                                            }
                                    }
                                }
                                $this->reportData['buildingDestructionResult'] = $bdestArr;
                            }
                            break;
                        case 4 :

                            $spyType = explode( "|", $this->reportData['rpt_body'] );
                            $harvestInfo = explode( "|", $this->reportData['rpt_body'] );
                            $harvestResources = explode( "|", $this->reportData['rpt_body'] );
                            $defenseTableTroopsStr = explode( "|", $this->reportData['rpt_body'] );
                            $attackTroopsStr = explode( "|", $this->reportData['rpt_body'] );
                             list( $attackTroopsStr, $defenseTableTroopsStr, $harvestResources, $harvestInfo, $spyType ) = $attackTroopsStr;
                            if ( trim( $harvestResources ) != "" && $spyType == 1 )
                            {
                                $this->reportData['harvest_resources'] = explode( " ", trim( $harvestResources ) );
                            }
                            if ( trim( $harvestInfo ) != "" && $spyType == 2 ){
                                $this->reportData['harvest_info'] = $harvestInfo;
                            
                            }
                            $this->reportData['all_spy_dead'] = FALSE;
                            if ( $spyType == 3 )
                            {
                                $this->reportData['all_spy_dead'] = TRUE;
                                $this->reportData['harvest_info'] = REPORT_TEXT46;
                            }
                            $attackTroopsStrArr = explode( ",", $attackTroopsStr );
                            $this->reportData['attackTroopsTable'] = array( "troops" => array( ), "heros" => array( "number" => 0, "dead_number" => 0 ) );
                            foreach ( $attackTroopsStrArr as $s )
                            {
                                $deadNum = explode( " ", $s );
                                $num = explode( " ", $s );
                                $tid = explode( " ", $s );
                                list( $tid, $num, $deadNum ) = $tid;
                                if ( $tid == 0 - 1 )
                                {
                                    $this->reportData['attackTroopsTable']['heros']['number'] = $num;
                                    $this->reportData['attackTroopsTable']['heros']['dead_number'] = $deadNum;
                                }
                                else
                                {
                                    $this->reportData['attackTroopsTable']['troops'][$tid] = array( "number" => $num, "dead_number" => $deadNum );
                                }
                            }
                            $this->reportData['defenseTroopsTable'] = array( );
                            $troopsTableStrArr = trim( $defenseTableTroopsStr ) == "" ? array( ) : explode( "#", $defenseTableTroopsStr );
                            $j = 0 - 1;
                            foreach ( $troopsTableStrArr as $defenseTableTroopsStr2 )
                            {
                                ++$j;
                                $defenseTroopsStrArr = explode( ",", $defenseTableTroopsStr2 );
                                $this->reportData['defenseTroopsTable'][$j] = array( "troops" => array( ), "heros" => array( "number" => 0, "dead_number" => 0 ) );
                                foreach ( $defenseTroopsStrArr as $s )
                                {
                                    $deadNum = explode( " ", $s );
                                    $num = explode( " ", $s );
                                    $tid = explode( " ", $s );
                                    list( $tid, $num, $deadNum ) = $tid;
                                    if ( $tid == 0 - 1 )
                                    {
                                        $this->reportData['defenseTroopsTable'][$j]['heros']['number'] = $num;
                                        $this->reportData['defenseTroopsTable'][$j]['heros']['dead_number'] = $deadNum;
                                    }
                                    else
                                    {
                                        $this->reportData['defenseTroopsTable'][$j]['troops'][$tid] = array( "number" => $num, "dead_number" => $deadNum );
                                    }
                                }
                            }
                            break;
                        case 5 : 

                            $spyType = explode( "|", $this->reportData['rpt_body'] );
                            $harvestInfo = explode( "|", $this->reportData['rpt_body'] );
                            $harvestResources = explode( "|", $this->reportData['rpt_body'] );
                            $defenseTableTroopsStr = explode( "|", $this->reportData['rpt_body'] );
                            $attackTroopsStr = explode( "|", $this->reportData['rpt_body'] );
                             list( $attackTroopsStr, $defenseTableTroopsStr, $harvestResources, $harvestInfo, $spyType ) = $attackTroopsStr;
                            if ( trim( $harvestResources ) != "" && $spyType == 1 )
                            {
                                $this->reportData['harvest_resources'] = explode( " ", trim( $harvestResources ) );
                            }
                            if ( trim( $harvestInfo ) != "" && $spyType == 2 )
                            {
                                $level = explode( " ", $harvestInfo );
                                $itemId = explode( " ", $harvestInfo );
                                list( $itemId, $level ) = $itemId;
                                $this->reportData['harvest_info'] = constant( "item_".$itemId )." ".REPORT_PHP19." ".$level;
                            }
                            $this->reportData['all_spy_dead'] = FALSE;
                            if ( $spyType == 3 )
                            {
                                $this->reportData['all_spy_dead'] = TRUE;
                                $this->reportData['harvest_info'] = REPORT_TEXT41;
                            }
                            $attackTroopsStrArr = explode( ",", $attackTroopsStr );
                            $this->reportData['attackTroopsTable'] = array( "troops" => array( ), "heros" => array( "number" => 0, "dead_number" => 0 ) );
                            foreach ( $attackTroopsStrArr as $s )
                            {
                                $deadNum = explode( " ", $s );
                                $num = explode( " ", $s );
                                $tid = explode( " ", $s );
                                list( $tid, $num, $deadNum ) = $tid;
                                if ( $tid == 0 - 1 )
                                {
                                    $this->reportData['attackTroopsTable']['heros']['number'] = $num;
                                    $this->reportData['attackTroopsTable']['heros']['dead_number'] = $deadNum;
                                }
                                else
                                {
                                    $this->reportData['attackTroopsTable']['troops'][$tid] = array( "number" => $num, "dead_number" => $deadNum );
                                }
                            }
                            $this->reportData['defenseTroopsTable'] = array( );
                            $troopsTableStrArr = trim( $defenseTableTroopsStr ) == "" ? array( ) : explode( "#", $defenseTableTroopsStr );
                            $j = 0 - 1;
                            foreach ( $troopsTableStrArr as $defenseTableTroopsStr2 )
                            {
                                ++$j;
                                $defenseTroopsStrArr = explode( ",", $defenseTableTroopsStr2 );
                                $this->reportData['defenseTroopsTable'][$j] = array( "troops" => array( ), "heros" => array( "number" => 0, "dead_number" => 0 ) );
                                foreach ( $defenseTroopsStrArr as $s )
                                {
                                    $deadNum = explode( " ", $s );
                                    $num = explode( " ", $s );
                                    $tid = explode( " ", $s );
                                    list( $tid, $num, $deadNum ) = $tid;
                                    if ( $tid == 0 - 1 )
                                    {
                                        $this->reportData['defenseTroopsTable'][$j]['heros']['number'] = $num;
                                        $this->reportData['defenseTroopsTable'][$j]['heros']['dead_number'] = $deadNum;
                                    }
                                    else
                                    {
                                        $this->reportData['defenseTroopsTable'][$j]['troops'][$tid] = array( "number" => $num, "dead_number" => $deadNum );
                                    }
                                }
                            }
                    }
                    $isDeleted = FALSE;
                    if ( !$isDeleted )
                    {
                        $canOpenReport = TRUE;

                        if ( $this->player->playerId != $from_player_id && $this->player->playerId != $to_player_id )
                        {
                            $canOpenReport = 0 < intval( $this->data['alliance_id'] ) && ( $this->data['alliance_id'] == $m->getPlayerAllianceId( $to_player_id ) || $this->data['alliance_id'] == $m->getPlayerAllianceId( $from_player_id ) );
                        }
                        if(isset($_GET['hash']) && 
                                (
                                    $_GET['hash'] == substr(md5($_GET['id'] * $from_player_id * 2972), 0, 10) or 
                                    $_GET['hash'] == substr(md5($_GET['id'] * $to_player_id * 2972), 0, 10)
                                )
                            ) 
                        {
                            $canOpenReport = TRUE;
                        }

                        if ( $canOpenReport )
                        {
                            if ( !$this->player->isSpy )
                            {
                                if ( $to_player_id == $this->player->playerId )
                                {
                                    if ( $readStatus == 0 || $readStatus == 2 )
                                    {
                                        $m->markReportAsReaded( $this->player->playerId, $to_player_id, $reportId, $readStatus );
                                        --$this->data['new_report_count'];
                                    }
                                }
                                else
                                {
                                    if ( $from_player_id == $this->player->playerId && ( $readStatus == 0 || $readStatus == 1 ) )
                                    {
                                        $m->markReportAsReaded( $this->player->playerId, $to_player_id, $reportId, $readStatus );
                                        --$this->data['new_report_count'];
                                    }
                                }
                            }
                        }
                        else
                        {
                            $this->showList = TRUE;
                        }
                    }
                    else
                    {
                        $this->showList = TRUE;
                    }
                }
                else
                {
                    $this->showList = TRUE;
                }
                $result->free( );
            }
        }
        else if ( isset( $_POST['dr'] ) && isset( $_POST['dr'] ) )
        {
            foreach ( $_POST['dr'] as $reportId )
            {
			if(isset($_POST['delmsg'])){
            if ( $m->deleteReport( $this->player->playerId, $reportId ) )
            {
            --$this->data['new_report_count'];
            }
			}elseif(isset($_POST['archiv']) AND $this->data['active_plus_account'] == 1){
			$conekt_sher = $m->getReport($reportId);
			if ( $conekt_sher->next( ) )
            {
            $from_player_id = $conekt_sher->row['from_player_id'];
            $to_player_id = $conekt_sher->row['to_player_id'];
			if($this->player->playerId == $from_player_id){
			$archiv = 'form_archiv';
			$player_id = 'from_player_id';
			}else{
			$archiv = 'to_archiv';
			$player_id = 'to_player_id';
			}
            $m->deletearchiv($this->player->playerId, $player_id, $reportId, $archiv);
			}
			}
            }
        }        if ( $this->showList )
        {
            $rowsCount = $m->getReportListCount( $this->player->playerId, $this->selectedTabIndex );
            $this->pageCount = 0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1;
            $this->pageIndex = isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) && intval( $_GET['p'] ) < $this->pageCount ? intval( $_GET['p'] ) : 0;
            $this->dataList = $m->getReportList( $this->player->playerId, $this->selectedTabIndex, $this->pageIndex, $this->pageSize );
            if ( 0 < $this->data['new_report_count'] )
            {
                $this->data['new_report_count'] = $m->syncReports( $this->player->playerId );
            }
        }
        $m->dispose( );
    }

    public function getVillageName( $playerId, $villageName )
    {
        return 0 < intval( $playerId ) ? $villageName : "<span class=\"none\">".REPORT_PHP20."</span>";
    }

    public function preRender( )
    {
        parent::prerender( );
        if ( isset( $_GET['id'] ) )
        {
            $this->villagesLinkPostfix .= "villagesLinkPostfix";
        }
        if ( isset( $_GET['p'] ) )
        {
            $this->villagesLinkPostfix .= "villagesLinkPostfix";
        }
        if ( 0 < $this->selectedTabIndex )
        {
            $this->villagesLinkPostfix .= "villagesLinkPostfix";
        }
    }

    public function getNextLink( )
    {
        $text = "»";
        if ( $this->pageIndex + 1 == $this->pageCount )
        {
            return $text;
        }
        $link = "";
        if ( 0 < $this->selectedTabIndex )
        {
            $link .= "t=".$this->selectedTabIndex;
        }
        if ($this->selectedTabIndex == 3)
            {
            $link .= "&ac=" . $_GET['ac'];
            }

        if ( $link != "" )
        {
            $link .= "&";
        }
        $link .= "p=".( $this->pageIndex + 1 );
        $link = "report?".$link;
        return "<a href=\"".$link."\">".$text."</a>";
    }

    public function getPreviousLink( )
    {
        $text = "«";
        if ( $this->pageIndex == 0 )
        {
            return $text;
        }
        $link = "";
        if ( 0 < $this->selectedTabIndex )
        {
            $link .= "t=".$this->selectedTabIndex;
        }
        if ($this->selectedTabIndex == 3)
            {
            $link .= "&ac=" . $_GET['ac'];
            }

        if ( 1 < $this->pageIndex )
        {
            if ( $link != "" )
            {
                $link .= "&";
            }
            $link .= "p=".( $this->pageIndex - 1 );
        }
        if ( $link != "" )
        {
            $link = "?".$link;
        }
        $link = "report".$link;
        return "<a href=\"".$link."\">".$text."</a>";
    }

}

$p = new GPage( );
$p->run( );
?>