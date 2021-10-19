<?php

class MapModel extends ModelBase
{

    public function getVillagesMatrix( $matrixStr )
    {
        return $this->provider->fetchResultSet( "SELECT\r\n\t\t\t\tv.id,\r\n\t\t\t\tv.rel_x, v.rel_y, v.field_maps_id, \r\n\t\t\t\tv.image_num, v.tribe_id, v.player_id, v.alliance_id, \r\n\t\t\t\tv.player_name, v.village_name,is_artefacts, v.alliance_name,\r\n\t\t\t\tv.people_count, v.is_oasis\r\n\t\t\tFROM\r\n\t\t\t\tp_villages v \r\n\t\t\tWHERE\r\n\t\t\t\tv.id IN (%s)", array(
            $matrixStr
        ) );
    }

    public function getContractsAllianceId( $allianceId )
    {
        return $this->provider->fetchScalar( "SELECT a.contracts_alliance_id FROM p_alliances a WHERE a.id=%s", array(
            $allianceId
        ) );
    }




    public function getwarAllianceId( $allianceId )
    {
        return $this->provider->fetchScalar( "SELECT a.war_alliance_id FROM p_alliances a WHERE a.id=%s", array(
            $allianceId
        ) );
    }





    public function getSearch( $rel_x, $rel_y )
    {
        return $this->provider->fetchScalar( "SELECT rel_x,rel_y  FROM p_villages WHERE rel_x=%s and rel_y=%s", array(
            $rel_x,
            $rel_y
        ) );
    }
    public function getSearchField( $field_maps )
    {
        return $this->provider->fetchScalar( "SELECT rel_x,rel_y,image_num,player_name,player_id,id FROM p_villages WHERE field_maps_id=%s", array(
            $field_maps
        ) );
    }
}

?>