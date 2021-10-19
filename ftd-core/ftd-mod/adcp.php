<?php

class AdminCp extends ModelBase
{ 
   public function GetPlayerDataByName ( $playerName ) 
    {
        return $this->provider->fetchRow( "SELECT * FROM p_players  WHERE name='%s'", array(
            $playerName
                ) );
        }
        
        public function GetPlayerDataById ( $playerID ) 
    {
        return $this->provider->fetchRow( "SELECT * FROM p_players  WHERE id=%s", array(
            $playerID
                ) );
        }
        
        public function GetPlayerDataByIB ( $playerIB ) 
    {
        return $this->provider->fetchResultSet( "SELECT * FROM p_players  WHERE last_ip='%s'", array(
            $playerIB
                ) );
        }
        
        public function GetVillageDataById ( $VillageId ) 
    {
        return $this->provider->fetchRow( "SELECT * FROM p_villages WHERE id=%s", array(
            $VillageId
                ) );
        }
        
        public function GetVillagesDataByName ( $playerName ) 
    {
        return $this->provider->fetchResultSet( "SELECT v.id, v.village_name, v.is_capital, v.is_special_village, v.people_count, v.crop_consumption  FROM p_villages v WHERE is_oasis=0 AND v.player_name='%s'", array(
                        $playerName
                ) );
        }
        
        public function UpdatePlayerData ( $playerId, $tribe_id, $alliance_id, $alliance_name, $name, $pwd, $email, $is_active, $invite_by, $is_blocked, $player_type, $active_plus_account, $last_ip, $house_name, $gold_num, $total_people_count, $villages_count, $villages_id, $hero_troop_id, $hero_level, $hero_points, $hero_name, $hero_in_village_id, $attack_points, $defense_points, $week_attack_points, $week_defense_points, $week_dev_points, $week_thief_points,$registration_date, $Id )
        {
        
            $this->provider->executeQuery( "UPDATE p_players p SET p.id=%s, p.tribe_id=%s, p.alliance_id=%s, p.alliance_name='%s', p.name='%s', p.pwd='%s', p.email='%s', p.is_active=%s, p.invite_by=%s, p.is_blocked=%s, p.player_type=%s, p.active_plus_account=%s, p.last_ip='%s', p.house_name='%s', p.gold_num=%s, p.total_people_count=%s, p.villages_count=%s, p.villages_id='%s', p.hero_troop_id=%s, p.hero_level=%s, p.hero_points=%s, p.hero_name='%s', p.hero_in_village_id=%s, p.attack_points=%s, p.defense_points=%s, p.week_attack_points=%s, p.week_defense_points=%s, p.week_dev_points=%s, p.week_thief_points=%s, registration_date='%s' WHERE p.id=%s", array(
                    $playerId, $tribe_id, $alliance_id, $alliance_name, $name, $pwd, $email, $is_active, $invite_by, $is_blocked, $player_type, $active_plus_account, $last_ip, $house_name, $gold_num, $total_people_count, $villages_count, $villages_id, $hero_troop_id, $hero_level, $hero_points, $hero_name, $hero_in_village_id, $attack_points, $defense_points, $week_attack_points, $week_defense_points, $week_dev_points, $week_thief_points,$registration_date, $Id
                ) );
        }

        public function UpdateVillageData ( $id, $rel_x, $rel_y, $tribe_id, $player_id, $alliance_id, $player_name, $village_name, $alliance_name, $is_capital, $is_special_village, $is_oasis, $people_count, $crop_consumption, $resources, $cp, $buildings, $troops_num, $village_oases_id, $troops_training, $allegiance_percent, $vid )
        {
        
            $this->provider->executeQuery( "UPDATE p_villages v SET v.id=%s, v.rel_x='%s', v.rel_y='%s', v.tribe_id=%s, v.player_id=%s, v.alliance_id=%s, v.player_name='%s', v.village_name='%s', v.alliance_name='%s', v.is_capital=%s, v.is_special_village=%s, v.is_oasis=%s, v.people_count=%s, v.crop_consumption='%s', v.resources='%s', v.cp='%s', v.buildings='%s', v.troops_num='%s', v.village_oases_id='%s', v.troops_training='%s', v.allegiance_percent='%s', v.last_update_date=NOW() WHERE  v.id=%s", array(
                    $id, $rel_x, $rel_y, $tribe_id, $player_id, $alliance_id, $player_name, $village_name, $alliance_name, $is_capital, $is_special_village, $is_oasis, $people_count, $crop_consumption, $resources, $cp, $buildings, $troops_num, $village_oases_id, $troops_training, $allegiance_percent, $vid
                ) );
    }
        
        public function GetGsummaryData () 
    {
        return $this->provider->fetchRow( "SELECT * FROM g_summary ");
        }
        
        public function UpdateGsummaryData ( $players_count, $active_players_count, $Dboor_players_count, $Arab_players_count, $Roman_players_count, $Teutonic_players_count, $Gallic_players_count )
        {
        
            $this->provider->executeQuery( "UPDATE g_summary SET players_count=%s, active_players_count=%s, Dboor_players_count=%s, Arab_players_count=%s, Roman_players_count=%s, Teutonic_players_count=%s, Gallic_players_count=%s", array(
                $players_count, $active_players_count, $Dboor_players_count, $Arab_players_count, $Roman_players_count, $Teutonic_players_count, $Gallic_players_count
                ) );
        }
        
        public function UpdatePlayergold ( $goldnum )
        {
            $this->provider->executeQuery( "UPDATE p_players p SET p.gold_num=p.gold_num+%s", array(
                $goldnum
                ) );
        }
     
        public function getSiteNews()
    {
        return $this->provider->fetchScalar( "SELECT g.news_text FROM g_summary g" );
    }

    public function setSiteNews( $news )
    {
        $this->provider->executeQuery( "UPDATE g_summary g SET g.news_text='%s'", array(
            $news
        ) );
    }

    public function getGlobalSiteNews( )
    {
        return $this->provider->fetchScalar( "SELECT g.gnews_text FROM g_summary g" );
    }

    public function setGlobalPlayerNews( $news )
    {
        $this->provider->executeQuery( "UPDATE g_summary g SET g.gnews_text='%s'", array(
            $news
        ) );
        $flag = trim( $news ) != "" ? 1 : 0;
        $this->provider->executeQuery( "UPDATE p_players p SET p.new_gnews=%s", array(
            $flag
        ) );
    } 
        
        public function UpdatePlayerPainTime( $playername, $time, $reason )
    {
            $this->provider->executeQuery( "UPDATE p_players p SET p.blocked_time='%s', p.blocked_reason='%s' WHERE p.name='%s'", array(
                $time,
                $reason,
                $playername
                ) );
        }

    public function UpdateTruceTime( $time, $reason )
    {
            $this->provider->executeQuery( "UPDATE g_summary g SET g.truce_time='%s', g.truce_reason='%s'", array(
                $time,
                $reason
                ) );
        }        
	public function getVillageData( $id )
    {
        return $this->provider->fetchRow( "SELECT v.id, v.player_id, v.troops_num, v.is_oasis, v.player_name, v.village_name, v.resources, v.cp, v.crop_consumption, TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds FROM p_villages v WHERE v.id = %s", array( $id ) );
    }
    
    
    public function updatehero( $troopid, $pid )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.hero_troop_id='%s' WHERE p.id=%s", array(
                $troopid,
                $pid
            ) );
    }
	
	public function getVillageDatas( $id )
    {
        return $this->provider->fetchRow( "SELECT v.id, v.player_id, v.is_oasis, v.player_name, v.village_name, v.resources, v.troops_num, v.cp, v.crop_consumption, TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds FROM p_villages v WHERE v.id = %s", array($id ) );
    }

    public function updateVillageResources( $vid, $new_resources )
    {
        $villageRow = $this->getVillageData( $vid );
        $elapsedTimeInSeconds = $villageRow['elapsedTimeInSeconds'];
        $resources = array( );
        $r_arr = explode( ",", $villageRow['resources'] );
        foreach ( $r_arr as $r_str )
        {
            $r2 = explode( " ", $r_str );
            $prate = floor( $r2[4] * ( 1 + $r2[5] / 100 ) ) - ( $r2[0] == 4 ? $villageRow['crop_consumption'] : 0 );
            $current_value = $new_resources[$r2[0]] < 0 ? floor( $r2[1] + $elapsedTimeInSeconds * ( $prate / 3600 ) ) : $new_resources[$r2[0]];
            if ( $r2[2] < $current_value )
            {
                $current_value = $r2[2];
            }
            $resources[$r2[0]] = array(
                "current_value" => $current_value,
                "store_max_limit" => $r2[2],
                "store_init_limit" => $r2[3],
                "prod_rate" => $r2[4],
                "prod_rate_percentage" => $r2[5],
                "calc_prod_rate" => $prate
            );
        }
        list( $cpValue, $cpRate ) = explode( " ", $villageRow['cp'] );
        $cpValue = round( $cpValue + $elapsedTimeInSeconds * ( $cpRate / 86400 ), 4 );
        $resourcesStr = "";
        foreach ( $resources as $k => $v )
        {
            if ( $resourcesStr != "" )
            {
                $resourcesStr .= ",";
            }
            $resourcesStr .= sprintf( "%s %s %s %s %s %s", $k, $v['current_value'], $v['store_max_limit'], $v['store_init_limit'], $v['prod_rate'], $v['prod_rate_percentage'] );
        }
        $cp = $cpValue." ".$cpRate;
        $this->provider->executeQuery( "UPDATE p_villages v SET v.resources='%s', v.cp='%s', v.last_update_date=NOW() WHERE  v.id=%s", array(
            $resourcesStr,
            $cp,
            intval( $villageRow['id'] )
        ) );
    }
	
	
	public function updateVillagetroops_num( $vid, $new_troops_num, $crop_consumption )
    {
        $this->provider->executeQuery( "UPDATE p_villages v SET v.troops_num='%s', v.crop_consumption=v.crop_consumption-%s WHERE  v.id=%s", array( $new_troops_num, $crop_consumption,  intval( $vid ) ) );
    }
	public function getplvillages_data( $id )
    {
        return $this->provider->fetchRow( "SELECT p.villages_data FROM p_players p WHERE p.id = %s", array( $id ) );
    }
}
?>