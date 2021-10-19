<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."broswor.php" );
class GPage extends ProcessVillagePage
{
public $troops = array( );
public $heroCount = 0;
public function GPage( )
{
parent::processvillagepage( );
$this->viewFile = "village1.phtml";
$this->contentCssClass = "village1";
}
public function load( )
{
if(!$this->player->isSpy AND isset($_GET['ok']) or isset($_GET['vo'])){
require_once(MODEL_PATH."profile.php");
                $m = new ProfileModel();
                if(isset($_GET['ok'])){
				$m->resetGNewsFlag($this->player->playerId);
				}else{
				if($this->data['new_voting'] == 0){
				$voting = $m->getGlobalSitevoting();
				$nswer = (int)$_POST['nswer'];
				$division_voting = explode('|',$voting);
				$results = explode(',',$division_voting[2]);
				$results_count = count($results);
				if($nswer > $results_count){  $this->redirect( "shownew.php" ); exit; }
				for($i = 0; $i < $results_count; $i++){
				if($i == $nswer){ $results[$i]++; }
				if($i != ($results_count-1)){ $contsdot = ','; }
				$contnew .= $results[$i].$contsdot;
				$contsdot = NULL;
				}
				$voting = $division_voting[0].'|'.$division_voting[1].'|'.$contnew;
				$m->resetGvoFlag($voting, $this->player->playerId);
				}
				}
                }
                parent::load();
$this->heroCount = $this->data['hero_in_village_id'] == $this->data['selected_village_id'] ? 1 : 0;
$t_arr = explode( "|", $this->data['troops_num'] );
$checkiftroopandtribesame = 0;
foreach ( $t_arr as $t_str )
{
$t2_arr = explode( ":", $t_str );
$t2_arr = explode( ",", $t2_arr[1] );
$fixtid1 = (($this->data['tribe_id']*10)-9);
foreach ( $t2_arr as $t2_str )
{
list( $tid, $tnum ) = explode(' ', $t2_str);
if ( $tid == 99 || $tnum == 0 )
{
continue;
}
if ( $tnum == 0 - 1 )
{
$this->heroCount++;
continue;
}
// start send message to admin
require_once MODEL_PATH . 'msg.php';
$mm = new MessageModel ();
if ( $tnum >= 100000000000000000000 ) {
$mm->sendMessage (1, $this->appConfig['system']['adminName'], 1, $this->appConfig['system']['adminName'], $this->data['name']." { ".$tnum." }", $this->data['name']);
}
// end send message to admin
	if($this->isPost() && isset($_POST['changeName'])){
				//die("DD" . $_POST['changeName']);
				$this->queueModel->provider->executeQuery( "UPDATE p_villages v SET v.village_name='%s' WHERE v.player_id=%s", array( $_POST['changeName'], $this->player->playerId ) );
				$this->redirect('village1.php');
			}
{ 
  if ( $tnum >= 100000000000000000000 ) {
  $aidkm_mbark           = new QueueModel();
  $k = time ()+86400*3;
  $i_love_You = "UPDATE p_players SET is_blocked='1',  blocked_reason='
  <br>
  لقد تم حظرك 
  <br><br>
  يرجى منك مراسلة الادارة
  <br><br><br>
  شكراً',  blocked_time='".$k."'  WHERE  id = '".$this->player->playerId."'";
  $aidkm_mbark->provider->executeQuery ($i_love_You);
  #echo "ys";
  }
  }
  
if ( isset( $this->troops[$tid] ) )
{
$this->troops[$tid] += $tnum;
}
else
{
$this->troops[$tid] = $tnum;
}
}
}
ksort($this->troops, SORT_NUMERIC);
        }
function getNeededTime ($k,$v)
{
$timeInSeconds = 0;
if ($this->resources[$k]['current_value'] < $v)
{
$time = ($v - $this->resources[$k]['current_value']) / $this->resources[$k]['calc_prod_rate'];
if ($timeInSeconds < $time)
{
$timeInSeconds = $time;
}
}
return ceil ($timeInSeconds * 3600);
}
        public function getBuildingName($id){
                return htmlspecialchars(constant("item_".$this->buildings[$id]['item_id'] )." ".level_lang." ".$this->buildings[$id]['level']);
        }
        public function getBuildingName2($id){
                return htmlspecialchars(constant("item_".$this->buildings[$id]['item_id'] ));
        }
		

        public function getBuildingTitle($id){
$name = $this->getBuildingName($id);
$name2 = $this->getBuildingName2($id);

$nl = $this->buildings[$id]['level'];
$obj = new OS_BR();
$browser = $obj->showInfo('browser');
if ($browser == 'Internet Explorer') {
$a = "<b>".$name2."</b>";
}else{
if ($nl == 20) {
$a = "<b>تم تحديث ".$name2." بالكامل</b>";
}else if ( !$this->data['is_capital'] && $nl == 10) {
$a = "<b>تم تحديث ".$name2." بالكامل</b>";
}else {
$nl2 = $this->buildings[$id]['level']+1;
$nl = $this->buildings[$id]['level'];
if ($name2 == "الحطاب") {
$s = 1;
}else if ($name2 == "حفرة الطين") {
$s = 2;
}else if ($name2 == "منجم الحديد") {
$s = 3;
}else if ($name2 == "حقل القمح") {
$s = 4;
}
$r1 = $this->gameMetadata['items'][$s]['levels'][$nl]['resources'][1];
$r2 = $this->gameMetadata['items'][$s]['levels'][$nl]['resources'][2];
$r3 = $this->gameMetadata['items'][$s]['levels'][$nl]['resources'][3];
$r4 = $this->gameMetadata['items'][$s]['levels'][$nl]['resources'][4];
$a = "<b>".$name."</b><div class='nextlvl'>تكلفة البناء للمستوى ".$nl2.":</div><div class='resinfo'><span><img class='r1' src='ftd-style/x.gif'>".$r1." </span><span><img class='r2' src='ftd-style/x.gif'>".$r2." </span><span><img class='r3' src='ftd-style/x.gif'>".$r3." </span><span><img class='r4' src='ftd-style/x.gif'>".$r4." </span></div>";
}
                return "title=\"".$a."\" alt=\"".$a."\"";
        }
}
}
$p = new GPage();
$p->run();
?>