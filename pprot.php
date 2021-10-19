<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
class GPage extends SecureGamePage
{

    public $packageIndex = -1;
    public $plusTable = NULL;

    public function GPage( )
    {
        parent::securegamepage( );
        $this->viewFile = "pprot.phtml";
        $this->contentCssClass = "forum";
        
    }

    public function load( )
    {
        parent::load( );
       
    }

    

}

$p = new GPage( );
$p->run( );
?>
