<?php
class modelCropfinder extends ModelBase{
    public function getVillagefarmfinder(){
        return $this->provider->fetchResultSet( "SELECT player_id,rel_x,rel_y,image_num,player_name,id FROM p_villages where is_oasis=1");
    }
	
	public function num_oasis_farm($id){
        return $this->provider->fetchScalar( "SELECT tvq FROM p_players p where p.id=%s", array($id) );
    }
	
	public function up_oasis_farm($playerId){
        $this->provider->executeQuery( "UPDATE p_players p SET p.gold_num=p.gold_num-100, p.tvq=p.tvq+300 WHERE p.id=%s", array( $playerId ) );
    }

	

}
?>
