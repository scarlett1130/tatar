<?php

class Village4Model extends ModelBase{
        function BuildInQueue($vid, $qType){
                return $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_queue q WHERE q.village_id=%s AND q.proc_type=%s',array($vid, $qType) );
        }




        function tatarzx($vid){
                return $this->provider->fetchResultSet( 'SELECT threads,proc_params FROM p_queue q WHERE q.village_id=%s AND q.proc_type=7',array($vid) );
        }

        function tatarzx3($vid,$p){
                return $this->provider->fetchResultSet( 'SELECT threads,village_id FROM p_queue q WHERE q.village_id=%s AND q.proc_type=7 AND q.proc_params=%s',array($vid,$p) );
        }

        function tatarzx2($vid,$vid2){
                return $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_queue q WHERE q.village_id=%s AND q.proc_type=7 AND q.proc_params=%s',array($vid ,$vid2) );
        }


        
        function RainInQueue($vid, $qType){
                return $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_queue q WHERE q.to_village_id=%s AND q.proc_type=%s',array($vid, $qType) );
        }
        
        function AttInQueue($vid, $Attack, $Plunder){
                return $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_queue q WHERE q.to_village_id=%s AND q.proc_type=%s OR q.to_village_id=%s AND q.proc_type=%s',array($vid, $Attack, $vid, $Plunder) );
        }
        
        function AttFromInQueue($vid, $Attack, $Plunder){
                return $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_queue q WHERE q.village_id=%s AND q.proc_type=%s OR q.village_id=%s AND q.proc_type=%s',array($vid, $Attack, $vid, $Plunder) );
        }
        
        function MarInQueue($vid, $qType){
                return $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_queue q WHERE q.to_village_id=%s AND q.proc_type=%s',array($vid, $qType) );
        }
function Merchants($vid){
                return $this->provider->fetchScalar( 'SELECT COUNT(*) FROM p_queue q WHERE q.to_village_id=%s AND q.proc_type=%s OR q.to_village_id=%s AND q.proc_type=%s',array($vid, $Attack, $vid, $Plunder) );
        }
        
    public function getVillageDataById( $villagesId )
    {
        return $this->provider->fetchRow( "SELECT v.id,v.buildings,v.offer_merchants_count FROM p_villages v WHERE v.id='%s' AND NOT ISNULL(v.player_id) AND v.is_oasis=0", array(
            $villagesId
        ) );
    }
        
        function getVillageData($id){
        return $this->provider->fetchRow( 
                        'SELECT
                                v.id,
                                v.player_id, v.is_oasis,
                                v.player_name, v.village_name,
                                v.resources, v.cp,
                                v.crop_consumption,
                                v.troops_num,
                                TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds
                        FROM
                                p_villages v 
                        WHERE
                                v.id = %s', array($id)
                );
    }
	function getVillageDatat4($id){
    return $this->provider->fetchRow( 'SELECT v.cp, v.troops_num, v.child_villages_id, TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds FROM p_villages v  WHERE v.id = %s', array($id) );
    }
	function TownHall_vid($id, $q){
    return $this->provider->fetchRow('SELECT TIME_TO_SEC(TIMEDIFF(q.end_date, NOW())) remainingSeconds FROM p_queue q WHERE q.village_id=%s AND q.proc_type=%s', array($id, $q ));
    }

        function getVillageResources($vid, $r, $type = 1){
        $villageRow = $this->getVillageData($vid);
        $elapsedTimeInSeconds = $villageRow['elapsedTimeInSeconds'];
        $resources = array( );
        $r_arr = explode( ',', $villageRow['resources'] );
                
        foreach ($r_arr as $r_str){
            $r2 = explode( ' ', $r_str );
                        $prate = floor( $r2[4] * ( 1 + $r2[5]/100 ) ) - (($r2[0]==4)? $villageRow['crop_consumption'] : 0);
                        $current_value         = floor ($r2[1] + $elapsedTimeInSeconds * ($prate/3600));
                        if ($current_value > $r2[2]) {
                                $current_value = $r2[2];
                        }
                        
                        $resources[ $r2[0] ] = array (
                                'current_value'                                =>        $current_value,
                                'store_max_limit'                        =>        $r2[2],
                        );
        }
                return ($type == 1) ? $resources[$r]['current_value'] : $resources[$r]['store_max_limit'];
    }
        
        function getVillageTroops($vid){
                $villageRow = $this->getVillageData($vid);

                $t_arr = explode( '|', $villageRow['troops_num'] );
                $t2_arr = explode( ':', $t_arr[0] );
                $t2_arr = explode( ',', $t2_arr[1] );
                foreach( $t2_arr as $t2_str ) {
                        list ($tid, $tnum) = explode( ' ', $t2_str );
                        if ( $tid == 99 ) {
                                continue;
                        }
                        $this->troops[$tid] = $tnum;
                }
                return $this->troops;
        }
}
?>