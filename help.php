<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
class GPage extends PopupPage
{
public $state = 0;
public $id = NULL;
public $tribeId = NULL;
public $buildingGroup = NULL;
public $build = NULL;
public $troopId = NULL;
public $troop = NULL;
public $next = NULL;
public $plusIndex = NULL;
public $nextLink = NULL;
public $previousLink = NULL;
public function GPage( )
{
parent::popuppage( );
$this->viewFile = "help.phtml";
}
public function load( )
{
parent::load( );
$this->nextLink = "";
$this->previousLink = "";
$this->state = isset( $_GET['c'] ) && is_numeric( $_GET['c'] ) && 0 <= intval( $_GET['c'] ) && intval( $_GET['c'] ) <= 7 ? intval( $_GET['c'] ) : 0;
$id = isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ? $_GET['id'] : 0;
switch ( $this->state )
{
case 1 :
do
{
do
{
do
{
if ( $id != 1 && $id != 2 && $id != 3 && $id != 6 && $id != 7 && $id != 8 && $id != 9 )
{
$this->state = 0;
break;
}
else
{
$this->tribeId = $id;
if ( $id == 1 )
{
$next = 2;
$prev = 7;
break;
}
else if ( $id == 2 )
{
$next = 3;
$prev = 1;
break;
}
else if ( $id == 3 )
{
$next = 8;
$prev = 2;
break;
}
else if ( $id == 8 )
{
$next = 9;
$prev = 3;
break;
}
else if ( $id == 9 )
{
$next = 6;
$prev = 8;
break;
}
else
{
if ( !( $id == 6 ) )
{
break;
}
else
{
$next = 7;
$prev = 3;
}
}
}
break;
} while ( 0 );
if ( !( $id == 7 ) )
{
break;
}
$next = 1;
$prev = 6;
} while ( 0 );
$this->nextLink = "?c=1&id=".$next;
$this->previousLink = "?c=1&id=".$prev;
} while ( 0 );
break;
case 2 :
do
{
do
{
do
{
if ( $id <= 0 || 4 < $id )
{
$this->state = 0;
break;
}
else
{
$this->buildingGroup = $id;
if ( $id == 1 )
{
$next = 2;
$prev = 3;
break;
}
else
{
if ( !( $id == 2 ) )
{
break;
}
else
{
$next = 3;
$prev = 1;
}
}
}
break;
} while ( 0 );
if ( !( $id == 3 ) )
{
break;
}
$next = 1;
$prev = 2;
} while ( 0 );
$this->nextLink = "?c=2&id=".$next;
$this->previousLink = "?c=2&id=".$prev;
} while ( 0 );
break;
case 3 :
$fnext = null;
$fprev = null;
do
{
do
{
do
{
if ( !isset( $this->gameMetadata['troops'][$id] ) )
{
$this->state = 0;
break;
}
else
{
$this->troopId = $id;
$this->troop = $this->gameMetadata['troops'][$id];
if ( $id >= 31 )
{
if ( $id <= 50 )
{
exit;
}
}
if ( $id == 1 )
{
$fnext = 2;
$fprev = 109;
break;
}
else if ( $id == 30 )
{
$fnext = 51;
$fprev = 29;
break;
}
else if ( $id == 51 )
{
$fnext = 52;
$fprev = 30;
break;
}
else if ( $id == 60 )
{
$fnext = 61;
$fprev = 59;
break;
}
else if ( $id == 70 )
{
$fnext = 71;
$fprev = 69;
break;
}
else if ( $id == 80 )
{
$fnext = 100;
$fprev = 79;
break;
}
else if ( $id == 100 )
{
$fnext = 101;
$fprev = 80;
break;
}
else if ( !( $id == 109 ) )
{
break;
}
else
{
$fnext = 1;
$fprev = 108;
}
}
break;
} while ( 0 );
$this->next = $id + 1;
if($fnext != null) $this->next = $fnext;
$this->prev = $id - 1;
if($fprev != null) $this->prev = $fprev;
} while ( 0 );
//if()
//{
//}
$this->nextLink = "?c=3&id=".$this->next;
$this->previousLink = "?c=3&id=".$this->prev;
} while ( 0 );
break;
case 4 :
do
{
do
{
do
{
if ( !isset( $this->gameMetadata['items'][$id] ) )
{
$this->state = 0;
break;
}
else
{
$this->itemId = $id;
$this->build = $this->gameMetadata['items'][$id];
if ( $id == 1 )
{
$next = 2;
$prev = 40;
break;
}
else if ( $id == 14 )
{
$next = 16;
$prev = 13;
break;
}
else if ( $id == 16 )
{
$next = 19;
$prev = 14;
break;
}
else if ( $id == 19 )
{
$next = 20;
$prev = 16;
break;
}
else if ( $id == 22 )
{
$next = 29;
$prev = 21;
break;
}
else if ( $id == 29 )
{
$next = 30;
$prev = 22;
break;
}
else if ( $id == 30 )
{
$next = 36;
$prev = 29;
break;
}
else if ( $id == 36 )
{
$next = 37;
$prev = 30;
break;
}
else if ( $id == 37 )
{
$next = 42;
$prev = 36;
break;
}
else if ( $id == 42 )
{
$next = 15;
$prev = 37;
break;
}
else if ( $id == 15 )
{
$next = 17;
$prev = 42;
break;
}
else if ( $id == 17 )
{
$next = 18;
$prev = 15;
break;
}
else if ( $id == 18 )
{
$next = 23;
$prev = 17;
break;
}
else if ( $id == 23 )
{
$next = 24;
$prev = 18;
break;
}
else if ( $id == 26 )
{
$next = 28;
$prev = 25;
break;
}
else if ( $id == 28 )
{
$next = 34;
$prev = 26;
break;
}
else if ( $id == 34 )
{
$next = 35;
$prev = 28;
break;
}
else if ( $id == 35 )
{
$next = 38;
$prev = 34;
break;
}
else if ( $id == 38 )
{
$next = 39;
$prev = 35;
break;
}
else if ( $id == 39 )
{
$next = 41;
$prev = 38;
break;
}
else if ( $id == 41 )
{
$next = 40;
$prev = 39;
break;
}
else if ( !( $id == 40 ) )
{
break;
}
else
{
$next = 1;
$prev = 41;
}
}
break;
} while ( 0 );
$next = $id + 1;
$prev = $id - 1;
} while ( 0 );
$this->nextLink = "?c=4&id=".$next;
$this->previousLink = "?c=4&id=".$prev;
} while ( 0 );
break;
case 5 :
$this->plusIndex = $id;
break;
case 6 :
case 7 :
$this->id = $id;
}
}
}
$p = new GPage( );
$p->run( );
?>
