<?php

class Puls extends ModelBase{
        public function InviteBy($id){
        return $this->provider->fetchResultSet("SELECT * FROM p_players WHERE invite_by='$id'");
   }
           public function get_Player_name( $getplayerid )
    {
        return $this->provider->fetchRow( "SELECT p.name, p.last_ip, p.total_people_count, p.is_active FROM p_players p WHERE p.id=%s", array(
            $getplayerid
        ) );
    }
   public function InviteByGold($id){
        return $this->provider->fetchResultSet("SELECT * FROM p_players WHERE invite_by='$id'");
   }


   public function setgoldisdone($id){
        return $this->provider->fetchResultSet("SELECT * FROM setgoldisdone WHERE my_id='$id'");
   }


   	public function _getResourcesArray($resourceString, $elapsedTimeInSeconds, $crop_consumption, $cp)
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

	function _getResourcesString($resources)
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
	public function plusres($vid, $resource_id)
	{
		$villageRow = $this->provider->fetchRow('SELECT
				v.player_id,
				v.resources,
				v.cp,
				v.crop_consumption,
				TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds
			FROM p_villages v
			WHERE v.id=%s', array(
			intval($vid)
		));
		if (intval($villageRow['player_id']) == 0)
		{
			return null;
		}
		$resultArr = $this->_getResourcesArray($villageRow['resources'], $villageRow['elapsedTimeInSeconds'], $villageRow['crop_consumption'], $villageRow['cp']);
		$resultArr['resources'][$resource_id]['prod_rate_percentage'] += 50;
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
			intval($vid)
		));
	}




	public function plusress($vid, $resource_id)
	{
		$villageRow = $this->provider->fetchRow('SELECT
				v.player_id,
				v.resources,
				v.cp,
				v.crop_consumption,
				TIME_TO_SEC(TIMEDIFF(NOW(), v.last_update_date)) elapsedTimeInSeconds
			FROM p_villages v
			WHERE v.id=%s', array(
			intval($vid)
		));
		if (intval($villageRow['player_id']) == 0)
		{
			return null;
		}
		$resultArr = $this->_getResourcesArray($villageRow['resources'], $villageRow['elapsedTimeInSeconds'], $villageRow['crop_consumption'], $villageRow['cp']);
		$resultArr['resources'][$resource_id]['prod_rate_percentage'] = 0;
		if ($resultArr['resources'][$resource_id]['prod_rate_percentage'] < 0)
		{//current_value
			$resultArr['resources'][$resource_id]['prod_rate_percentage'] = 0;
		}
			$resultArr['resources'][$resource_id]['current_value'] = 0;
		$this->provider->executeQuery('UPDATE p_villages v
			SET
				v.resources=\'%s\',
				v.cp=\'%s\',
				v.last_update_date=NOW()
			WHERE v.id=%s', array(
			$this->_getResourcesString($resultArr['resources']),
			$resultArr['cp']['cpValue'] . ' ' . $resultArr['cp']['cpRate'],
			intval($vid)
		));
	}







    public function incrementPlayerGold( $playerId )
    {
        $this->provider->executeQuery( "UPDATE p_players SET gold_num=gold_num+%s WHERE id=%s", array(
            $GLOBALS['AppConfig']['Game']['setgold'],
            $playerId
        ) );
    }
        public function PlayerRef( $RefId )
    {
        $this->provider->executeQuery( "UPDATE p_players SET show_ref=1 WHERE id=%s", array(
            $RefId
        ) );
    }

        public function getPlayerDataById( $playerId )
    {
        return $this->provider->fetchRow( "SELECT p.last_ip, p.pwd, p.total_people_count FROM p_players p WHERE p.id=%s", array(
            $playerId
        ) );
    }

        public function getPlayerDataByName( $playerName )
    {
        return $this->provider->fetchRow( "SELECT p.id FROM p_players p WHERE p.name='%s'", array(
            $playerName
        ) );
    }

        public function DeletPlayerGold( $playerId, $GoldNum )
    {
        $this->provider->executeQuery( "UPDATE p_players SET gold_num=gold_num-%s WHERE id=%s", array(
                    $GoldNum,
            $playerId
        ) );
    }

        public function getPlayerId( $playerName )
    {
        return $this->provider->fetchRow( "SELECT p.id FROM p_players p WHERE p.name=%s", array(
            $playerName
        ) );
    }

        public function SendTogolds( $id, $name, $id_getplayerid, $playernamesendgold, $goldsendplayername, $datee ){
		$this->provider->executeQuery( "INSERT INTO `p_golds` (`id`, `myname`, `gold`, `date`, `to_name`, `to_id`, `my_id`) VALUES (NULL, '$name', '$goldsendplayername', '$datee', '$playernamesendgold', '$id_getplayerid', '$id')");
    }

	public function get_by_gold_sendd($id){
        return $this->provider->fetchResultSet("SELECT * FROM p_golds WHERE my_id='%s' or to_id='%s' ORDER BY `id` DESC LIMIT 10", array($id,$id) );
   }



	public function get_by_gold_senddd($id){
        return $this->provider->fetchResultSet("SELECT * FROM p_golds");
   }



        public function GivePlayerGold( $playerName, $GoldNum )
    {
        $this->provider->executeQuery( "UPDATE p_players SET gold_num=gold_num+%s WHERE id='%s'", array(
                    $GoldNum,
            $playerName
        ) );
    }
}
?>