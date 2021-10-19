<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
require_once(MODEL_PATH."adcp.php");
class GPage extends SecureGamePage
{
    var $PlayerList;
        var $VillagesList;
        var $VillageList;

        function GPage(){
                parent::securegamepage();
                $this->viewFile = "adcp.phtml";
                $this->contentCssClass = "plus";
        }
                function load()
                {
        parent::load();
        if ( $this->data['player_type'] != PLAYERTYPE_ADMIN  )
        {
           $this->redirect('village1');
            exit( 0 );
        }
                $this->selectedTabIndex = isset( $_GET['t'] ) && is_numeric( $_GET['t'] ) && 0 <= intval( $_GET['t'] ) && intval( $_GET['t'] ) <= 10 ? intval( $_GET['t'] ) : 0;
                    if ( $this->selectedTabIndex == 0 )
                        {

                                if ( isset($_GET['p']) AND isset($_GET['punishment']) OR isset($_GET['newtroops']))
                {
                                $m = new AdminCp();
                                $villages_data = $m->getplvillages_data($_GET['p']);
                                $villages_data = explode( "\n", $villages_data['villages_data'] );
                                foreach ( $villages_data as $v_str ) {
                                list ($vid, $x, $y, $vname) = explode (' ', $v_str, 4);
                                $youvillages_data [$vid] = array ($x, $y, $vname);
                                }
                                
                                if(isset($_GET['punishment'])){
                                foreach ( $youvillages_data as $vid => $pvillage )
                                {
                                $row = $m->getVillageDatas( $vid );
                                $r_arr = explode( ",", $row['resources'] );
                                foreach ( $r_arr as $r_str )
                                {
                                $r2 = explode( " ", $r_str );
                                $prate = floor( $r2[4] * ( 1 + $r2[5] / 100 ) ) - ( $r2[0] == 4 ? $row['crop_consumption'] : 0 );
                                $current_value = floor( $r2[1] + $elapsedTimeInSeconds * ( $prate / 3600 ) );
                                if ( $r2[2] < $current_value ) { $current_value = $r2[2]; }
                                $this->resources[$r2[0]] = array("current_value" => $current_value, "store_max_limit" => $r2[2], "store_init_limit" => $r2[3], "prod_rate" => $r2[4], "prod_rate_percentage" => $r2[5], "calc_prod_rate" => $prate );
                                }
                                $punishment = $_GET['punishment'];
                                $r1 = $this->resources[1]['current_value']-($this->resources[1]['current_value']*$punishment);
                                $r2 = $this->resources[2]['current_value']-($this->resources[2]['current_value']*$punishment);
                                $r3 = $this->resources[3]['current_value']-($this->resources[3]['current_value']*$punishment);
                                $r4 = $this->resources[4]['current_value']-($this->resources[4]['current_value']*$punishment);
                                $m->updateVillageResources( $vid, array( "1" => $r1, "2" => $r2, "3" => $r3, "4" => $r4 ) );
                                $this->redirect('adcp?t=0&p='.$_GET['p'].'&punishments');
                                }
                                }
                                if(isset($_GET['newtroops'])){
                                $row = $m->getVillageDatas( $vid );
                                foreach ( $youvillages_data as $vid => $pvillage )
                                {
                                $troops = $row['troops_num'];
                                $villagetroop = $troops;
                                $edittroop = explode(' ',$villagetroop);
                                for($i = 1;$i <= 5; $i++){
                                $troopnew = explode(',',$edittroop[$i]); 
                                $newcp += floor($troopnew[0]*$_GET['newtroops']/100);
                                $troopnew[0] = floor($troopnew[0]-($troopnew[0]*$_GET['newtroops']/100));
                                $edittroop[$i] = implode(',',$troopnew); 
                                }
                                $edittroop = implode(' ', $edittroop);
                                $troops = str_replace($villagetroop, $edittroop, $troops);
                                
                                $m->updateVillagetroops_num( $vid, $troops, $newcp );
                                }
                                $this->redirect('adcp?t=0&p='.$_GET['p'].'&newtroopss');
                                }
                                }
                            if ( $this->isPost() || isset( $_GET['p'] ) )
                                {
                                    $m = new AdminCp();
                                        if (isset( $_GET['p'] )){
                                        $playerId = intval($_GET['p']);
                                        $this->PlayerList = $m->GetPlayerDataById ( $playerId );
                                        } else {
                                    $playerName = trim( $_POST['name'] );
                                    $this->PlayerList = $m->GetPlayerDataByName ($playerName);
                                        }
                                        if ($this->PlayerList == NULL )
                                        {
                                        $this->errorTable = login_result_msg_notexists;
                                        } else {
                                    if (empty($_POST['pwd']))
                                    {
                                        $pwd = $this->PlayerList['pwd'];
                                    } 
                                        else 
                                        {
                                        $pwd = md5 (trim($_POST['pwd']));
                                    }
                                    if ( trim($_POST['email']) != null )
                                    {
                                        $m->UpdatePlayerData( intval($_POST['id']), intval($_POST['tribe_id']), intval($_POST['alliance_id']), trim($_POST['alliance_name']), trim($_POST['name']), $pwd, trim($_POST['email']), intval($_POST['is_active']), intval($_POST['invite_by']), intval($_POST['is_blocked']), intval($_POST['player_type']), intval($_POST['active_plus_account']), trim($_POST['last_ip']), trim($_POST['house_name']), intval($_POST['gold_num']), intval($_POST['total_people_count']), intval($_POST['villages_count']), trim($_POST['villages_id']), intval($_POST['hero_troop_id']), intval($_POST['hero_level']), intval($_POST['hero_points']), trim($_POST['hero_name']), intval($_POST['hero_in_village_id']), intval($_POST['attack_points']), intval($_POST['defense_points']), trim($_POST['week_attack_points']), intval($_POST['week_defense_points']), intval($_POST['week_dev_points']), intval($_POST['week_thief_points']),$_POST['registration_date'], intval($_POST['id']) );
                                        $this->errorTable = LANGUI_ADCP_E1;
                                    }
                                        }
                            }        
                        }        
                        
                        if ( $this->selectedTabIndex == 1 )
                        {
                            if ( $this->isPost() )
                                {
                                    $m = new AdminCp();
                                        $playerName = trim( $_POST['player_name'] );
                                        $this->VillagesList = $m->GetVillagesDataByName ($playerName);
                                        if (isset( $_GET['v'] )) 
                                        {
                                        $VillageId = intval($_GET['v']);
                                    $this->VillageList = $m->GetVillageDataById ( $VillageId );
                                        }
                                        if ($this->VillageList == NULL and isset( $_GET['v'] ) )
                                        {
                                        $this->errorTable = login_result_msg_notexists;
                                        } else {
                                    if ( trim($_POST['resources']) != null and isset( $_GET['v'] ) )
                                    {
                        $m->UpdateVillageData( intval($_POST['id']), trim($_POST['rel_x']), trim($_POST['rel_y']), intval($_POST['tribe_id']), intval($_POST['player_id']), intval($_POST['alliance_id']), trim($_POST['player_name']), trim($_POST['village_name']), trim($_POST['alliance_name']), intval($_POST['is_capital']), intval($_POST['is_special_village']), intval($_POST['is_oasis']), intval($_POST['people_count']), trim($_POST['crop_consumption']), trim($_POST['resources']), trim($_POST['cp']), trim($_POST['buildings']), trim($_POST['troops_num']), trim($_POST['village_oases_id']), trim($_POST['troops_training']), trim($_POST['allegiance_percent']), intval($_POST['id']) );
                                                $this->errorTable = LANGUI_ADCP_E1;
                                        }
                                        }
                                }
                        }
                        
                        if ( $this->selectedTabIndex == 2 )
                        {
                            
                                $m = new AdminCp();
                                $this->summarylist = $m->GetGsummaryData ();
                                if ( $this->isPost() )
                                {
                                $m->UpdateGsummaryData ( intval($_POST['players_count']), intval($_POST['active_players_count']), intval($_POST['Dboor_players_count']), intval($_POST['Arab_players_count']), intval($_POST['Roman_players_count']), intval($_POST['Teutonic_players_count']), intval($_POST['Gallic_players_count']) );
                                $this->errorTable = LANGUI_ADCP_E1;
                                }
                        }
                        
                        if ( $this->selectedTabIndex == 3 )
                        {
                            if ( $this->isPost() )
                                {
                                $m = new AdminCp();
                                $goldnum = intval($_POST['goldnum']);
                                if (!empty($_POST['goldnum']))
                                {
                                $m->UpdatePlayergold ( $goldnum );
                                $this->errorTable = LANGUI_ADCP_E1;
                                }
                                }
                        }
            
                        if ( $this->selectedTabIndex == 4 )
                        {
                            $m = new AdminCp();
                $this->saved = FALSE;
                if ( $this->isPost() && isset( $_POST['news'] ) )
                {
                $this->siteNews = $_POST['news'];
                $this->saved = TRUE;
                $m->setGlobalPlayerNews( $this->siteNews );
                }
                else
                {
                $this->siteNews = $m->getGlobalSiteNews();
                } 
                $m->dispose();
                        }

                        if ( $this->selectedTabIndex == 5 )
                        {
                            $m = new AdminCp();
                            $this->saved = FALSE;
                if ( $this->isPost() && isset( $_POST['news'] ) )
                {
                $this->siteNews = $_POST['news'];
                $this->saved = TRUE;
                $m->setSiteNews( $this->siteNews );
                }
                else
                {
                $this->siteNews = $m->getSiteNews();
                }
                $m->dispose();
                        }
            
            if ( $this->selectedTabIndex == 6 )
                        {
                            if ( $this->isPost() )
                                {
                            $m = new AdminCp();        
                $paintime =  intval($_POST['painhours']);
                $time = time()+(60*60*$paintime);
                                $playername = trim($_POST['name']);
                                $reason = trim($_POST['reason']);
                $m->UpdatePlayerPainTime ( $playername, $time, $reason );
                                $this->errorTable = LANGUI_ADCP_E1;
                }
            }
            
            if ( $this->selectedTabIndex == 7 )
                        {        
                if ( $this->isPost() )
                                {
                                $m = new AdminCp();
                                $this->summarylist = $m->GetGsummaryData ();
                                $Trucetime = intval($_POST['Trucetime']);
                                $time = time()+(60*60*$Trucetime);
                                $reason = trim($_POST['reason']);
                                $m->UpdateTruceTime ( $time, $reason );
                                $this->errorTable = LANGUI_ADCP_E1;
                }
            }
                        
                        if ( $this->selectedTabIndex == 8 )
                        {        
                if ( $this->isPost() )
                                {
                                $m = new AdminCp();
                                $playerIb = trim($_POST['player_ib']);
                                $this->playerlistib = $m->GetPlayerDataByIB ( $playerIb );
                                }
                        }        
                }
        }
$p = new GPage();
$p->run();
?>        
