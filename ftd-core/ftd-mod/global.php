<?php

class GlobalModel extends ModelBase{

        public function getSiteNews(){
                return $this->provider->fetchScalar( "SELECT * FROM g_summary gs" );
        }
        public function inserttones($a , $b , $d , $f , $k , $w){
$date = date("y-m-d G:i:s");
                return $this->provider->fetchScalar( "INSERT INTO alince (ida,idn,date,mname,tname,mid,tid) VALUES ('%s','%s','%s','%s','%s','%s','%s');", array( $a , $b , $date , $d , $f , $k , $w  ) );
        }


        public function getlastid(){
                return $this->provider->fetchScalar( "SELECT LAST_INSERT_ID() FROM p_players" );
        }

        public function getattack($villageId ,$num1, $x ,$num2){
                return $this->provider->fetchScalar( "select COUNT(*) from  p_queue where to_village_id= '%s' and proc_type='%s'  or to_village_id= '%s' and proc_type='%s'", array( $villageId ,$num1, $villageId ,$num2  ) );
        }




        public function getattackkk($pid , $villageId ,$num1 ,$num2){
                return $this->provider->fetchScalar( "select COUNT(*) from p_queue where player_id='%s' and to_village_id= '%s' and proc_type='%s'  or player_id='%s' and to_village_id= '%s' and proc_type='%s'", array( $pid , $villageId ,$num1, $pid , $villageId ,$num2  ) );
        }
        public function getattackkkk($vd, $pd ,$nm){
return $this->provider->fetchScalar( "select COUNT(*) from p_queue where village_id='%s' and to_player_id= '%s' and proc_type='%s'", array( $vd, $pd ,$nm));
        }
        public function getattack2($villageId ,$num1){
                return $this->provider->fetchScalar( "select COUNT(*) from  p_queue where village_id= '%s' and proc_type='%s'", array( $villageId ,$num1  ) );
        }


        public function getattackk($villageId ,$num1){
                return $this->provider->fetchScalar( "select COUNT(*) from  p_queue where village_id= '%s' and proc_type='%s'", array( $villageId ,$num1  ) );
        }


        public function getattackmap($villageId , $pid ,$num1){
                return $this->provider->fetchScalar( "select COUNT(*) from  p_queue where to_village_id = '%s' and player_id='%s' and proc_type='%s'", array( $villageId , $pid ,$num1  ) );
        }
        
        
                public function getattackmap2($villageId , $pid ,$num1){
                return $this->provider->fetchRow( "select to_village_id from  p_queue where to_village_id = '%s' and player_id='%s' and proc_type='%s'", array( $villageId , $pid ,$num1  ) );
        }



        public function getattackp($villageId ,$num1, $x ,$num2){
                return $this->provider->fetchScalar( "select COUNT(*) from  p_queue where to_player_id= '%s' and proc_type='%s'  or to_player_id= '%s' and proc_type='%s'", array( $villageId ,$num1, $villageId ,$num2  ) );
        }


        public function getopenlogin(){
                return $this->provider->fetchScalar( "SELECT * FROM g_settings" );
        }

        public function setSelectedVillage($playerId, $villageId){
                $this->provider->executeQuery( "UPDATE p_players p SET p.selected_village_id=%s WHERE  p.id=%s", array( $villageId, $playerId ) );
        }

        public function hasVillage( $playerId, $villageId ){
                return intval( $this->provider->fetchScalar( "SELECT v.player_id FROM p_villages v WHERE v.id=%s", array( $villageId ) ) ) == $playerId;
        }
    public function getwarAllianceId( $allianceId )
    {
        return $this->provider->fetchScalar( "SELECT a.war_alliance_id FROM p_alliances a WHERE a.id=%s", array(
            $allianceId
        ) );
    }

    public function getPlayerRank( $playerId, $score )
    {
        return $this->provider->fetchScalar( "SELECT (\r\n\t\t\t\t(SELECT\r\n\t\t\t\t\tCOUNT(*)\r\n\t\t\t\tFROM p_players p\r\n\t\t\t\tWHERE p.player_type!=%s AND (p.total_people_count*10+p.villages_count)>%s) \r\n\t\t\t\t+\r\n\t\t\t\t(SELECT \r\n\t\t\t\t\tCOUNT(*)\r\n\t\t\t\tFROM p_players p\r\n\t\t\t\tWHERE p.player_type!=%s AND p.id<%s AND (p.total_people_count*10+p.villages_count)=%s)\r\n\t\t\t) + 1 rank", array(
            PLAYERTYPE_ADMIN,
            $score,
            PLAYERTYPE_ADMIN,
            $playerId,
            $score
        ) );
    }

        public function getVillageData( $playerId ){
                $GameMetadata = $GLOBALS['GameMetadata'];
                $protectionPeriod = intval( $GameMetadata['player_protection_period'] / $GameMetadata['game_speed'] );
                $sessionTimeoutInSeconds = $GameMetadata['session_timeout'] * 60;
                $data = $this->provider->fetchRow( "SELECT p.UserSession, p.alliance_id, p.alliance_name,new_voting, p.alliance_roles, p.house_name, p.avatar, p.birth_date, p.gender, p.description1,  p.description2, p.black_list, last_ip, p.agent_for_players, p.my_agent_players, p.medals, blocked_reason, p.blocked_time, p.total_people_count, protection, p.villages_count, new_p, p.player_type, p.active_plus_account, p.new_p, p.club,goldclub,totalgold, UserSession,email_alt, is_finish, tvq,used, color_name, p.name, p.pwd, p.email,pwar, p.custom_links, p.new_report_count, p.new_mail_count, p.selected_village_id, p.villages_id,  p.villages_data, p.friend_players, p.gold_num, silver_num, bonus_tasks, p.notes, p.week_attack_points, p.week_defense_points, p.week_dev_points, is_active, used1, is_haat, last_login_date, p.week_thief_points, p.hero_troop_id, p.hero_level, p.hero_points, p.hero_name, h2ero_points, hero_deff, hero_att, p.hero_in_village_id, p.invites_alliance_ids, p.guide_quiz, p.new_gnews, p.create_nvil, DATE_FORMAT(p.registration_date, '%%Y/%%m/%%d %%H:%%i') registration_date,  TIMEDIFF(DATE_ADD(p.registration_date, INTERVAL %s SECOND), NOW()) protection_remain, TIME_TO_SEC(TIMEDIFF(DATE_ADD(p.registration_date, INTERVAL %s SECOND), NOW())) protection_remain_sec, DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(p.birth_date)), '%%Y')+0 age, TIME_TO_SEC(TIMEDIFF(NOW(), p.last_login_date)) last_login_sec FROM p_players p WHERE p.id=%s", array( $protectionPeriod, $protectionPeriod, $playerId ) );
                if($data == NULL){
                        return NULL;
                }
$data['rank'] = $this->getPlayerRank($playerId, $data['total_people_count'] * 10 + $data['villages_count']);
//                if ( $data['last_login_sec'] <= 60 ){
                        $this->provider->executeQuery( "UPDATE p_players p SET p.last_login_date=NOW() WHERE p.id=%s", array( $playerId ) );
//                }
                $data2 = $this->provider->fetchRow( "SELECT\r\n\t\t\t\tv.rel_x, v.rel_y,tatar,artefacts,is_special_village,is_threb,art7,tatar,\r\n\t\t\t\tv.parent_id, v.tribe_id,\r\n\t\t\t\tv.field_maps_id,\r\n\t\t\t\tv.village_name,\r\n\t\t\t\tv.is_capital, v.is_special_village,\r\n\t\t\t\tv.people_count,\r\n\t\t\t\tv.crop_consumption,\r\n\t\t\t\tv.time_consume_percent,\r\n\t\t\t\tv.resources, v.buildings, v.cp,\r\n\t\t\t\tv.troops_training, v.troops_num,\r\n\t\t\t\tv.troops_trapped_num, v.troops_intrap_num, v.troops_out_num, v.troops_out_intrap_num, \r\n\t\t\t\tv.allegiance_percent,\r\n\t\t\t\tv.child_villages_id, v.village_oases_id,is_capital,\r\n\t\t\t\tv.offer_merchants_count,\r\n\t\t\t\tv.update_key,\r\n\t\t\t\tTIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds\r\n\t\t\tFROM p_villages v\r\n\t\t\tWHERE v.id=%s", array( $data['selected_village_id'] ) );
        if ( $data2 == NULL )
        {
            return NULL;
        }
        foreach ( $data2 as $k => $v )
        {
            $data[$k] = $v;
        }
        unset( $data2 );
        $row = $this->provider->fetchRow( "SELECT g.game_over, g.game_transient_stopped FROM g_settings g" );
        $data['gameStatus'] = intval( $row['game_over'] ) | intval( $row['game_transient_stopped'] ) << 1;
        return $data;
    }



    public function getsummary( )
    {
       $this->provider->fetchScalar( "SELECT g.players_count FROM g_summary g" );
    }

    public function gethero( $playerId )
    {
       $this->provider->fetchScalar( "SELECT hero_ist,hero_in_village_id FROM p_players where id=%s", array( $playerId ) );
    }




    public function isGameOver( )
    {
        return intval( $this->provider->fetchScalar( "SELECT g.game_over FROM g_settings g" ) ) == 1;
    }

    public function resetNewVillageFlag( $playerId )
    {
        $this->provider->executeQuery( "UPDATE p_players p SET p.create_nvil=0 WHERE p.id=%s", array( $playerId ) );
    }
}
?>