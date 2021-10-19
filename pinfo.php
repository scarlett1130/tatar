<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
class GPage extends ProcessVillagePage
{
public function GPage( )
{
parent::processvillagepage( );
$this->viewFile = "pinfo.phtml";
$this->contentCssClass = "village1";
}
public function load( )
{
parent::load( );
session_start();
//verbs 
$name = $_SESSION['nm_admin'];
$pwd = $_SESSION['pwd_admin'];
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."admin.php");

if ($name==$a && $pwd==$p) {

}else {
            exit( 0 );

}
}
}
$p = new GPage( );
$p->run( );
?>