<?php

require( LIB_PATH."mysql.php" );
class ModelBase extends MysqlModel
{

    public function ModelBase()
    {
        parent::mysqlmodel();
        $this->provider->debug = FALSE;
        $this->provider->properties = $GLOBALS['AppConfig']['db'];
    }

}

?>
