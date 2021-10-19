<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
class GPage extends SecureGamePage
{

    public $packageIndex = -1;
    public $plusTable = NULL;

    public function GPage( )
    {
        parent::securegamepage( );
        $this->viewFile = "InfiniteAccounts.phtml";
        $this->contentCssClass = "plus";
        
    }

    public function load( )
    {
        parent::load( );
       
    }

    

}

$p = new GPage( );
$p->run( );
?>
