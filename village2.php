<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
class GPage extends ProcessVillagePage
{
public $showLevelsStr = NULL;
public function GPage( )
{
parent::processvillagepage( );
$this->viewFile = "village2.phtml";
$this->contentCssClass = "village2";
}
public function load( )
{
parent::load( );
$cookie = ClientData::getinstance( );
$this->showLevelsStr = $cookie->showLevels ? "on" : "off";
}
public function getWallCssName( )
{
if ( $this->buildings[40]['level'] == 0 && $this->buildings[40]['update_state'] == 0 )
{
return "d2_0";
}
return $this->gameMetadata['tribes'][$this->data['tribe_id']]['wall_css'];
}
public function getBuildingName( $id )
{
$emptyName = "";
switch ( $id )
{
case 39 :
$emptyName = buildin_place_railpoint;
break;
case 40 :
$emptyName = buildin_place_wall;
break;
default:
$emptyName = $this->data['is_special_village'] && ( $id == 25 || $id == 26 || $id == 29 || $id == 30 || $id == 33 ) ? buildin_place_topbuild : buildin_place_empty;
break;
}
return htmlspecialchars( $this->buildings[$id]['item_id'] == 0 ? $emptyName : constant( "item_".$this->buildings[$id]['item_id'] )." ".level_lang." ".$this->buildings[$id]['level'] );
}
public function getBuildingCssName( $id )
{
$cssName = "";
switch ( $id )
{
case 39 :
$e = "";
if ( $this->buildings[$id]['level'] == 0 && 0 < $this->buildings[$id]['update_state'] )
{
$e = "b";
}
else if ( $this->buildings[$id]['level'] == 0 )
{
$e = "e";
}
$cssName = "g".$this->buildings[$id]['item_id'].$e;
break;
case 25 :
case 26 :
case 29 :
case 30 :
case 33 :
case 19 :
case 20 :
case 21 :
case 22 :
case 23 :
case 24 :
case 25 :
case 26 :
case 27 :
case 28 :
case 29 :
case 30 :
case 31 :
case 32 :
case 33 :
case 34 :
case 35 :
case 36 :
case 37 :
case 38 :
case 40 :
if ( $this->data['is_special_village'] && $id == 0 )
{
$cssName = "g40";
echo $this->buildings[$id]['level'];
if ( 100 <= $this->buildings[$id]['level'] )
{
if(floor( $this->buildings[$id]['level']) <= 25)
{
$cssName .= "_1";
} else {
$cssName .= "_3";
}
} else {
$cssName .= "_5";
}
break;
}
$e = $this->buildings[$id]['level'] == 0 && 0 < $this->buildings[$id]['update_state'] ? "b" : "";
$cssName = $this->buildings[$id]['item_id'] == 0 ? "iso" : "g".$this->buildings[$id]['item_id'].$e;
if($this->buildings[$id]['item_id'] == 40)
{
$cssName = "g40";
$g40lvl = 0;
if($this->buildings[$id]['level'] >= 20)
{
$g40lvl += 1;
}
if($this->buildings[$id]['level'] >= 40)
{
$g40lvl += 1;
}
if($this->buildings[$id]['level'] >= 60)
{
$g40lvl += 1;
}
if($this->buildings[$id]['level'] >= 80)
{
$g40lvl += 1;
}
if($this->buildings[$id]['level'] >= 100)
{
$g40lvl += 1;
}
if($g40lvl >= 1)
{
$cssName .= "_".$g40lvl;
}
}
break;
}
return $cssName;
}
public function getBuildingTitle( $id )
{
$name = $this->getBuildingName( $id );
return "title=\"".$name."\" alt=\"".$name."\"";
}
public function getBuildingTitleClass( $id )
{
$name = $this->getBuildingName( $id );
$cssClass = $this->getBuildingCssName( $id );
if ($cssClass == 'g10') {
if ($this->buildings[$id]['level']>20){
$cssClass = 'g38';
}
}
if ($cssClass == 'g11') {
if ($this->buildings[$id]['level']>20){
$cssClass = 'g39';
}
}
return $cssClass."\" alt=\"".$name;
}
}
$p = new GPage( );
$p->run( );
?>