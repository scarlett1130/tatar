<?php
class FarmList extends ModelBase{
	
	function addFarm($playerId, $villageId, $toVillageId, $troops){
		$this->provider->executeQuery (
			'INSERT p_farms
			SET
				player_id=%s,
				from_village_id=%s,
				to_village_id=%s,
				troops=\'%s\'
			', 
			array ($playerId, $villageId, $toVillageId, $troops)
		);
	}
	
	function isFarmFull($playerId){
		return $this->provider->fetchScalar('SELECT COUNT(*) FROM p_farms f WHERE f.player_id=%s',array($playerId));
	}
	
	function hasThisFarm($farmId, $playerId){
		return $this->provider->fetchScalar('SELECT COUNT(*) FROM p_farms f WHERE  f.id=%s AND f.player_id=%s',array($farmId, $playerId));
	}
	
	function DeleteThisFarm($farmId){
		$this->provider->executeQuery (
			'DELETE FROM p_farms
			WHERE
				id=%s', 
			array ($farmId)
		);
	}

	function DeleteallFarm($pid){
		$this->provider->executeQuery (
			'DELETE FROM p_farms
			WHERE
				player_id=%s', 
			array ($pid)
		);
	}
	
	function getFarmList( $playerId ){
        return $this->provider->fetchResultSet( 'SELECT f.id, f.player_id, f.from_village_id, f.to_village_id, f.troops FROM p_farms f WHERE f.player_id=%s', array( $playerId ) );
    }
	
	function getVillageDataById ($villageId) {
		return $this->provider->fetchRow (
			'SELECT v.id, v.rel_x, v.rel_y, v.village_name, v.player_id, v.is_oasis, people_count FROM p_villages v WHERE v.id=%s',
			array ($villageId)
		);
	}
	
	function getVillageDataByName ($villageName) {
		return $this->provider->fetchRow (
			'SELECT v.id, v.rel_x, v.rel_y, v.village_name, v.player_id, v.player_name, v.is_oasis FROM p_villages v WHERE v.village_name=\'%s\'',
			array ($villageName)
		);
	}
	
	function __getCoordInRange($map_size, $x) {
		if( $x >= $map_size ) {
			$x -= $map_size;
		} else if( $x < 0 ) {
			$x = $map_size + $x;
		}

		return $x;
	}
	
	function __getVillageId($map_size, $x, $y) {
		return ( ($x * $map_size) + ($y+1) );
	}
}
?>