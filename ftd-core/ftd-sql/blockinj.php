<?php
require_once( MODEL_PATH."blockinj.php" );
class protect extends SecureGamePage
{
public $getlist = NULL;
public $postlist = NULL;
public $cookielist = NULL;
public function protect( )
{
}
public function load( )
{
foreach ($_GET as $key => $value) {
if(strlen($this->getlist) == 0) {
$this->getlist .= $key." = ".$value;
} else {
$this->getlist .= "||".$key." = ".$value;
}
}
foreach ($_POST as $key => $value) {
if(is_array($value)) {
$arraying = "";
foreach ($value as $v1 => $v2) {
if(strlen($this->postlist) == 0) {
$arraying .= $v1." = ".$v2;
} else {
$arraying .= "||".$v1." = ".$v2;
}
}
$value = $arraying;
}
if(strlen($this->postlist) == 0) {
$this->postlist .= $key." = ".$value;
} else {
$this->postlist .= "||".$key." = ".$value;
}
}
foreach ($_COOKIE as $key => $value) {
if(strlen($this->cookielist) == 0) {
$this->cookielist .= $key." = ".$value;
} else {
$this->cookielist .= "||".$key." = ".$value;
}
}
$m = new ProtectionModel( );
$m->online( );
//$m->add( $this->getlist,$this->postlist,$this->cookielist );
$m->blockbadcall( $this->getlist.$this->postlist,$GLOBALS['AppConfig']['system']['blocklistword'] );
$m->is_ip_blocked();
}
}
$p = new protect( );
$p->run( );
?>