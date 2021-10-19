<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
class GPage extends ProcessVillagePage
{
public function GPage( )
{
parent::processvillagepage( );
$this->viewFile = "faq.phtml";
$this->contentCssClass = "village1";
}
public function load( )
{
parent::load( );
}
}
$p = new GPage( );
$p->run( );
?>