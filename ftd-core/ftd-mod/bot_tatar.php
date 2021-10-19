<?php

class BotTatarModel extends ModelBase
{


    public function get_villages_random( )
    {
        $select = $this->provider->fetchRow( "SELECT id FROM p_villages WHERE tribe_id != 0 ORDER BY RAND() LIMIT 1");
        return $select['id'];
    }

    public function get_villages_random1($id)
    {
        $select = $this->provider->fetchRow( "SELECT * FROM p_villages WHERE id='".$id."' ORDER BY RAND() LIMIT 1");
        return $select['update_key'];
    }

}

?>