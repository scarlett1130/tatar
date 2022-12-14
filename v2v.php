<?php
require '.' . DIRECTORY_SEPARATOR . 'ftd-core' . DIRECTORY_SEPARATOR . 'boot.php';
require_once MODEL_PATH . 'v2v.php';
require_once MODEL_PATH . 'build.php';
class GPage extends VillagePage
{
var $pageState = null;
var $targetVillage = array ('x' => NULL, 'y' => NULL);
var $troops = null;
var $disableFirstTwoAttack = FALSE;
var $attackWithCatapult = FALSE;
var $transferType = 2;
var $errorTable = array ();
var $newVillageResources = array (1 => 750, 2 => 750, 3 => 750, 4 => 750);
var $rallyPointLevel = 0;
var $totalCatapultTroopsCount = 0;
var $catapultCanAttackLastIndex = 0;
var $availableCatapultTargetsString = '';
var $catapultCanAttack = array (
0 => 0,
1 => 10, 
2 => 11, 
3 => 9, 
4 => 6, 
5 => 2, 
6 => 4, 
7 => 8, 
8 => 7, 
9 => 3, 10 => 5, 11 => 1, 12 => 22, 13 => 13, 14 => 19, 15 => 12, 16 => 35, 17 => 18, 18 => 29, 19 => 30, 20 => 37, 21 => 41, 22 => 15, 23 => 17, 24 => 26, 25 => 16, 26 => 25, 27 => 20, 28 => 14, 29 => 24, 30 => 28, 31 => 40, 32 => 27, 33=> 38, 34 => 39);
var $onlyOneSpyAction = FALSE;
var $backTroopsProperty = array ();
function GPage ()
{
parent::villagepage ();
$this->viewFile = 'v2v.phtml';
$this->contentCssClass = 'a2b';
}
function onLoadBuildings ($building)
{
if (($building['item_id'] == 16 AND $this->rallyPointLevel < $building['level']))
{
$this->rallyPointLevel = $building['level'];
}
}
function load ()
{
parent::load ();
if ( $this->dataGame['blocked_time'] > time() ){
$this->redirect ('banned');
return null;
}

if ($this->rallyPointLevel <= 0)
{
$this->redirect ('build?id=39');
return null;
}
if (((isset ($_GET['d1']) OR isset ($_GET['d2'])) OR isset ($_GET['d3'])))
{
$this->pageState = 3;
$this->handleTroopBack ();
return null;
}
$m = new WarModel ();
$this->pageState = 1;
$map_size = $this->setupMetadata['map_size'];
$half_map_size = floor ($map_size / 2);
if( !empty($this->data['hero_in_village_id']) and empty($this->data['hero_troop_id']) )
{
}
else
{
$this->hasHero = $this->data['hero_in_village_id'] == $this->data['selected_village_id'];
}
$t_arr = explode ('|', $this->data['troops_num']);
foreach ($t_arr as $t_str)
{
$t2_arr = explode (':', $t_str);
if ($t2_arr[0] == 0 - 1)
{
$t2_arr = explode (',', $t2_arr[1]);
foreach ($t2_arr as $t2_str)
{
$t = explode (' ', $t2_str);
if ($t[0] == 99)
{
continue;
}
$this->troops[] = array ('troopId' => $t[0], 'number' => $t[1]);
}
continue;
}
}
$attackOptions1 = '';
$sendTroops = FALSE;
$playerData = NULL;
$villageRow = NULL;
if (!$this->isPost ())
{
if ((isset ($_GET['id']) AND is_numeric ($_GET['id'])))
{
$vid = intval ($_GET['id']);
if ($vid < 1)
{
$vid = 1;
}
$villageRow = $m->getVillageDataById ($vid);
}
}
else
{
if (isset ($_POST['id']))
{
$sendTroops = (!$this->isGameTransientStopped () AND !$this->isGameOver ());
$vid = intval ($_POST['id']);
$villageRow = $m->getVillageDataById ($vid);
}
else
{
if ((isset ($_POST['dname']) AND trim ($_POST['dname']) != ''))
{
$villageRow = $m->getVillageDataByName (trim ($_POST['dname']));
}
else
{
if ((((isset ($_POST['x']) AND isset ($_POST['y'])) AND trim ($_POST['x']) != '') AND trim ($_POST['y']) != ''))
{
$vid = $this->__getVillageId ($map_size, $this->__getCoordInRange ($map_size, intval ($_POST['x'])), $this->__getCoordInRange ($map_size, intval ($_POST['y'])));
$villageRow = $m->getVillageDataById ($vid);
}
}
}
}
if ($villageRow == NULL)
{
if ($this->isPost ())
{
$this->errorTable = v2v_p_entervillagedata;
}
return null;
}
$this->disableFirstTwoAttack = (intval ($villageRow['player_id']) == 0 AND $villageRow['is_oasis']);
$this->targetVillage['x'] = floor (($villageRow['id'] - 1) / $map_size);
$this->targetVillage['y'] = $villageRow['id'] - ($this->targetVillage['x'] * $map_size + 1);
if ($half_map_size < $this->targetVillage['x'])
{
$this->targetVillage['x'] -= $map_size;
}
if ($half_map_size < $this->targetVillage['y'])
{
$this->targetVillage['y'] -= $map_size;
}
if ($villageRow['id'] == $this->data['selected_village_id'])
{
return null;
}
if ((0 < intval ($villageRow['player_id']) AND $m->getPlayType ($villageRow['player_id']) == PLAYERTYPE_ADMIN))
{
return null;
}
$spyOnly = FALSE;
if ((!$villageRow['is_oasis'] AND intval ($villageRow['player_id']) == 0))
{
$this->transferType = 1;
$humanTroopId = 0;
$renderTroops = array ();
foreach ($this->troops as $troop)
{
$renderTroops[$troop['troopId']] = 0;
if ((((((($troop['troopId'] == 10 OR $troop['troopId'] == 20) OR $troop['troopId'] == 30) OR $troop['troopId'] == 109) OR $troop['troopId'] == 60) OR $troop['troopId'] == 70) OR $troop['troopId'] == 80))
{
$humanTroopId = $troop['troopId'];
$renderTroops[$humanTroopId] = $troop['number'];
if($renderTroops[$humanTroopId] >= 3) {
$renderTroops[$humanTroopId] = 3;
}
continue;
}
}
$canBuildNewVillage = (isset ($renderTroops[$humanTroopId]) AND 3 <= $renderTroops[$humanTroopId]);
if ($canBuildNewVillage)
{
$count = (trim ($this->data['child_villages_id']) == '' ? 0 : sizeof (explode (',', $this->data['child_villages_id'])));
if ($count >= 3)
{
$this->errorTable = v2v_p_cannotbuildnewvill;
return null;
}
if (!$this->_canBuildNewVillage ())
{
$this->errorTable = v2v_p_cannotbuildnewvill1;
return null;
}
if (!$this->isResourcesAvailable ($this->newVillageResources))
{
$this->errorTable = sprintf (v2v_p_cannotbuildnewvill2, $this->newVillageResources['1']);
return null;
}
if ($m->hasNewVillageTask ($this->player->playerId))
{
$this->errorTable = v2v_p_cannotbuildnewvill3;
return null;
}
}
else
{
$this->errorTable = v2v_p_cannotbuildnewvill4;
return null;
}
$this->pageState = 2;
}
else
{
if ($this->isPost ())
{
if( $this->player->isAgent == 1 AND substr($this->player->actions, 0, 1) == 0 AND $_POST['c'] != 2) {
$this->errorTable = '?????????? ?????? ???????? ???? ??????????????';
return null;
}
if( $this->player->isAgent == 1 AND substr($this->player->actions, 1, 1) == 0 AND $_POST['c'] == 2) {
$this->errorTable = '?????????? ?????? ???????? ???? ????????????????';
return null;
}

if ((!$villageRow['is_oasis'] AND intval ($villageRow['player_id']) == 0))
{
$this->errorTable = v2v_p_novillagehere;
return null;
}
if (((!isset ($_POST['c']) OR intval ($_POST['c']) < 1) OR 4 < intval ($_POST['c'])))
{
return null;
}
$this->transferType = ($this->disableFirstTwoAttack ? 4 : intval ($_POST['c']));

if (0 < intval ($villageRow['player_id']) && $_POST['c'] != 2)
{
if ( $this->player->playerId != $villageRow['player_id'] )
{
if(0 < $this->data['protection_remain_sec']) {
$this->errorTable = "?????? ?????????? ?????? ??????????????";
return null;
}
}
$m = new WarModel ();
$playerDatav = $m->getVillageDataById ($this->data['selected_village_id']);
$hispeople = $villageRow['people_count'];
$mypeople = $playerDatav['people_count'];
$this->Gsummry = $m->GetGsummaryData ();
if ($this->Gsummry['truce_time'] > time())
{
$this->errorTable = $this->Gsummry['truce_reason'];
return null;
}

$playerData = $m->getPlayerDataById (intval ($villageRow['player_id']));
if ($playerData['blocked_time'] > time())
{
$this->errorTable = v2v_p_playerwas_blocked;
return null;
}
if (0 < $playerData['protection_remain_sec'])
{
if ( $this->player->playerId != $villageRow['player_id'] )
{
$this->errorTable = v2v_p_playerwas_inprotectedperiod;
return null;
}
}
}
$totalTroopsCount = 0;
$totalSpyTroopsCount = 0;
$this->totalCatapultTroopsCount = 0;
$hasTroopsSelected = FALSE;
$renderTroops = array ();
if (isset ($_POST['t']) OR isset ($_POST['tro']))
{
if (isset ($_POST['tro']) AND !isset ($_POST['t'])) {
$test = $_POST['tro'];
$t2_arr = explode (',', $test);
foreach ($t2_arr as $t2_str)
{
$t = explode (' ', $t2_str);
$_POST['t'][$t[0]] = $t[1];
}
}
foreach ($this->troops as $troop)
{
$num = 0;
if ((isset ($_POST['t'][$troop['troopId']]) AND 0 < intval ($_POST['t'][$troop['troopId']])))
{
if(preg_match('/^[+-]?[0-9]+$/', $_POST['t'][$troop['troopId']]) == 0) {
exit;
}
$num = ($troop['number'] < $_POST['t'][$troop['troopId']] ? $troop['number'] : intval ($_POST['t'][$troop['troopId']]));
}
$renderTroops[$troop['troopId']] = $num;
$totalTroopsCount += $num;
if (0 < $num)
{
$hasTroopsSelected = TRUE;
}
if ((((((($troop['troopId'] == 4 OR $troop['troopId'] == 14) OR $troop['troopId'] == 23) OR $troop['troopId'] == 103) OR $troop['troopId'] == 54) OR $troop['troopId'] == 64) OR $troop['troopId'] == 74))
{
$totalSpyTroopsCount += $num;
continue;
}
else
{
if ((((((($troop['troopId'] == 8 OR $troop['troopId'] == 18) OR $troop['troopId'] == 28) OR $troop['troopId'] == 107) OR $troop['troopId'] == 58) OR $troop['troopId'] == 68) OR $troop['troopId'] == 78))
{
$this->totalCatapultTroopsCount = $num;
//+= 'totalCatapultTroopsCount';
//= $num;
continue;
}
continue;
}
}
}
if ((($this->hasHero AND isset ($_POST['_t'])) AND intval ($_POST['_t']) == 1))
{
$hasTroopsSelected = TRUE;
$totalTroopsCount += 1;
}
$spyOnly = (($totalSpyTroopsCount == $totalTroopsCount AND ($this->transferType == 3 OR $this->transferType == 4)) AND 0 < intval ($villageRow['player_id']));
if ($spyOnly)
{
$this->onlyOneSpyAction = $villageRow['is_oasis'];
}
$this->attackWithCatapult = (((0 < $this->totalCatapultTroopsCount AND $this->transferType == 3) AND 0 < intval ($villageRow['player_id'])) AND !$villageRow['is_oasis']);
if ($this->attackWithCatapult)
{
///Start Artefect
$efect = 5;
$pid = $villageRow['player_id'];
$vid = $villageRow['id'];
$tatarzx = new QueueModel();
$this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" ); 
$this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
if ($villageRow['tribe_id'] == 5) {
$this->h = $this->rallyPointLevel;
}else 
if ($this->BigArt) {
$artefact = 2;
}else if ($this->SeCArt) {
$artefact = 2;
}else if ($this->SmallArt) {
$artefact = 2;
}else {
$this->h = $this->rallyPointLevel;
}
///End Artefect
if (10 <= $this->h)
{
$this->catapultCanAttackLastIndex = sizeof ($this->catapultCanAttack) - 1;
}
else
{
if (5 <= $this->h)
{
$this->catapultCanAttackLastIndex = 11;
}
else
{
if (3 <= $this->h)
{
$this->catapultCanAttackLastIndex = 2;
}
else
{
$this->catapultCanAttackLastIndex = 0;
}
}
}
$attackOptions1 = ((isset ($_POST['dtg']) AND $this->_containBuildingTarget ($_POST['dtg'])) ? intval ($_POST['dtg']) : 0);
if (($this->rallyPointLevel == 20 AND 1000 <= $this->totalCatapultTroopsCount))
{
$attackOptions1 = '2:' . ($attackOptions1 . ' ' . ((isset ($_POST['dtg1']) AND $this->_containBuildingTarget ($_POST['dtg1'])) ? intval ($_POST['dtg1']) : 0));
}
else
{
$attackOptions1 = '1:' . $attackOptions1;
}
$this->availableCatapultTargetsString = '';
$selectComboTargetOptions = '';
$i = 1;
while ($i <= 9)
{
if ($this->_containBuildingTarget ($i))
{
$selectComboTargetOptions .= sprintf ('<option value="%s">%s</option>', $i, constant ('item_' . $i));
}
++$i;
}
if ($selectComboTargetOptions != '')
{
$this->availableCatapultTargetsString .= '<optgroup label="' . v2v_p_catapult_grp1 . '">' . $selectComboTargetOptions . '</optgroup>';
}
$selectComboTargetOptions = '';
$i = 10;
while ($i <= 40)
{
if ((((((((((((((($i == 10 OR $i == 11) OR $i == 27) OR $i == 40) OR $i == 38) OR $i == 39) OR $i == 15) OR $i == 17) OR $i == 18) OR $i == 24) OR $i == 25) OR $i == 26) OR $i == 28) OR $i == 38) OR $i == 39))
{
if ($this->_containBuildingTarget ($i))
{
$selectComboTargetOptions .= sprintf ('<option value="%s">%s</option>', $i, constant ('item_' . $i));
}
}
++$i;
}
if ($selectComboTargetOptions != '')
{
$this->availableCatapultTargetsString .= '<optgroup label="' . v2v_p_catapult_grp2 . '">' . $selectComboTargetOptions . '</optgroup>';
}
$selectComboTargetOptions = '';
$i = 12;
while ($i <= 37)
{
if (((((((((($i == 12 OR $i == 13) OR $i == 14) OR $i == 16) OR $i == 19) OR $i == 20) OR $i == 21) OR $i == 22) OR $i == 35) OR $i == 37))
{
if ($this->_containBuildingTarget ($i))
{
$selectComboTargetOptions .= sprintf ('<option value="%s">%s</option>', $i, constant ('item_' . $i));
}
}
++$i;
}
if ($selectComboTargetOptions != '')
{
$this->availableCatapultTargetsString .= '<optgroup label="' . v2v_p_catapult_grp3 . '">' . $selectComboTargetOptions . '</optgroup>';
}
}
if (!$hasTroopsSelected)
{
$this->errorTable = v2v_p_thereisnoattacktroops;
return null;
}
$this->pageState = 2;
}
}

////////////////////////
//if(0 < $this->data['protection_remain_sec']) {
//$sendTroops = FALSE;
//}

if ($this->pageState == 2)
{
if ($this->transferType != 2) {
if (0 < $playerData['protection_remain_sec'])
{
if ( $this->player->playerId != $villageRow['player_id'] )
{
$sendTroops = FALSE;
}
}
}

$this->targetVillage['transferType'] = ($this->transferType == 1 ? v2v_p_attacktyp1 : ($this->transferType == 2 ? v2v_p_attacktyp2 . ' ' : ($this->transferType == 3 ? v2v_p_attacktyp3 : ($this->transferType == 4 ? v2v_p_attacktyp4 : ''))));
$playerData = $m->getPlayerDataById (intval ($villageRow['player_id']));
if ($villageRow['is_oasis'])
{
$this->targetVillage['villageName'] = ($playerData != NULL ? v2v_p_placetyp1 : v2v_p_placetyp2);
}
else
{
$this->targetVillage['villageName'] = ($playerData != NULL ? $villageRow['village_name'] : v2v_p_placetyp3);
}
$this->targetVillage['villageId'] = $villageRow['id'];
$this->targetVillage['playerName'] = ($playerData != NULL ? $playerData['name'] : ($villageRow['is_oasis'] ? v2v_p_monster : ''));
$this->targetVillage['playerId'] = ($playerData != NULL ? $playerData['id'] : 0);
$this->targetVillage['troops'] = $renderTroops;
$this->targetVillage['hasHero'] = (((1 < $this->transferType AND $this->hasHero) AND isset ($_POST['_t'])) AND intval ($_POST['_t']) == 1);
$distance = WebHelper::getdistance ($this->data['rel_x'], $this->data['rel_y'], $this->targetVillage['x'], $this->targetVillage['y'], $this->setupMetadata['map_size'] / 2);
$this->targetVillage['needed_time'] = intval ($distance / $this->_getTheSlowestTroopSpeed ($renderTroops) * 3600);
$this->targetVillage['spy'] = $spyOnly;
$to_player_Data = $m->getVillageDataById($this->data['selected_village_id']);
$fo_player_Data = $m->getVillageDataById($villageRow['id']);
if( 0 < $to_player_Data['player_id'] AND $fo_player_Data['player_id'] && $fo_player_Data['alliance_name'] != '' && $fo_player_Data['alliance_name'] == $to_player_Data['alliance_name'] &&  $_POST['c'] != 2 ){
$this->errorTableSend = "<span class='error'><b>?????? ?????????? ?????? ???? ????????????  !!</b><p>";
}
}


if ($sendTroops)
{
$taskType = 0;
switch ($this->transferType)
{
case 1:
{
$taskType = QS_CREATEVILLAGE;
break;
}
case 2:
{
$taskType = QS_WAR_REINFORCE;
break;
}
case 3:
{
$taskType = QS_WAR_ATTACK;
break;
}
case 4:
{
$taskType = QS_WAR_ATTACK_PLUNDER;
break;
}
default:
{
}
}
//return null;
$spyAction = 0;
if ($spyOnly)
{
$taskType = QS_WAR_ATTACK_SPY;
$spyAction = ((isset ($_POST['spy']) AND (intval ($_POST['spy']) == 1 OR intval ($_POST['spy']) == 2)) ? intval ($_POST['spy']) : 1);
if ($this->onlyOneSpyAction)
{
$spyAction = 1;
}
}
$troopsStr = '';
foreach ($this->targetVillage['troops'] as $tid => $tnum)
{
if ($troopsStr != '')
{
$troopsStr .= ',';
}
$troopsStr .= $tid . ' ' . $tnum;
}
if ($this->targetVillage['hasHero'])
{
$troopsStr .= ',' . $this->data['hero_troop_id'] . ' -1';
}

    session_start();
if ($_SESSION['ask20'] <= time() - 120) {
$_SESSION['num20'] = 0;
}
if ($_SESSION['ask10'] <= time() - 10) {
$_SESSION['num10'] = 0;
}
$t11 = new GlobalModel();
$vid=$this->data['selected_village_id'];
$war11 = $t11->getattack2($vid,13,$vid,14);
$war111 = $t11->getattack2($vid,14,$vid,14);
$war1 = $war11 + $war111;
    if ($war1 >= 100){ // ?????? ?????????????? ??????????????
$this->redirect ('build?id=39');
}else 

if($_SESSION['ask10'] > time() - 10 && $_SESSION['num10'] >= 1 && $_POST['farm'] != 1 && $_SESSION['boot'] != 1) {
$this->redirect ('build?id=39');
}else 
    if($_SESSION['ask20'] > time() - 120 && $_SESSION['num20'] >= 20 && $_POST['farm'] != 1 && $_SESSION['boot'] != 1) {
$this->redirect ('build?id=39');
        }else
if (1==1) {
$catapultTargets = $attackOptions1;
$carryResources = ($taskType == QS_CREATEVILLAGE ? implode (' ', $this->newVillageResources) : '');
$procParams = $troopsStr . '|' . ($this->targetVillage['hasHero'] ? 1 : 0) . '|' . $spyAction . '|' . $catapultTargets . '|' . $carryResources . '|||0';
$newTask = new QueueTask ($taskType, $this->player->playerId, $this->targetVillage['needed_time']);
$newTask->villageId = $this->data['selected_village_id'];
$newTask->toPlayerId = intval ($villageRow['player_id']);
$newTask->toVillageId = $villageRow['id'];
$newTask->procParams = $procParams;
session_start();  
if ($_SESSION['boot'] != 1) {
$_SESSION['ask10'] = time();
$_SESSION['num10'] = ($_SESSION['num10']+1);
$_SESSION['ask20'] = time();
$_SESSION['num20'] = ($_SESSION['num20']+1);
}
$newTask->tag = array ('troops' => $this->targetVillage['troops'], 'hasHero' => $this->targetVillage['hasHero'], 'resources' => ($taskType == QS_CREATEVILLAGE ? $this->newVillageResources : NULL));
$this->queueModel->addTask ($newTask);
$to_player_Data = $m->getVillageDataById($this->data['selected_village_id']);
$fo_player_Data = $m->getVillageDataById($villageRow['id']);
$m->dispose ();
$this->redirect ('build?id=39&t=2');
//return null;
}
}
$m->dispose ();
}
function handleTroopBack ()
{
$qstr = '';
$fromVillageId = 0;
$toVillageId = 0;
$action = 0;
if (isset ($_GET['d1']))
{
$action = 1;
$qstr = 'd1=' . intval ($_GET['d1']);
if (isset ($_GET['o']))
{
$qstr .= '&o=' . intval ($_GET['o']);
$fromVillageId = intval ($_GET['o']);
}
else
{
$fromVillageId = $this->data['selected_village_id'];
}
$toVillageId = intval ($_GET['d1']);
}
else
{
if (isset ($_GET['d2']))
{
$action = 2;
$qstr = 'd2=' . intval ($_GET['d2']);
$fromVillageId = $this->data['selected_village_id'];
$toVillageId = intval ($_GET['d2']);
}
else
{
if (isset ($_GET['d3']))
{
$action = 3;
$qstr = 'd3=' . intval ($_GET['d3']);
$fromVillageId = intval ($_GET['d3']);
$toVillageId = $this->data['selected_village_id'];
}
else
{
$this->redirect ('build?id=39');
//return null;
}
}
}
$this->backTroopsProperty['queryString'] = $qstr;
$m = new WarModel ();
$fromVillageData = $m->getVillageData2ById ($fromVillageId);
$toVillageData = $m->getVillageData2ById ($toVillageId);
if (($fromVillageData == NULL OR $toVillageData == NULL))
{
$m->dispose ();
$this->redirect ('build?id=39');
//return null;
}
$vid = $toVillageId;
$_backTroopsStr = '';
$this->backTroopsProperty['headerText'] = v2v_p_backtroops;
$this->backTroopsProperty['action1'] = '<a href="village3?id=' . $fromVillageData['id'] . '">' . $fromVillageData['village_name'] . '</a>';
$this->backTroopsProperty['action2'] = '<a href="profile?uid=' . $fromVillageData['player_id'] . '">' . v2v_p_troopsinvillagenow . '</a>';
$column1 = '';
$column2 = '';
if ($action == 1)
{
$_backTroopsStr = $fromVillageData['troops_num'];
$column1 = 'troops_num';
$column2 = 'troops_out_num';
}
else
{
if ($action == 2)
{
$this->backTroopsProperty['headerText'] = v2v_p_backcaptivitytroops;
$_backTroopsStr = $fromVillageData['troops_intrap_num'];
$column1 = 'troops_intrap_num';
$column2 = 'troops_out_intrap_num';
}
else
{
if ($action == 3)
{
$_backTroopsStr = $toVillageData['troops_out_num'];
$vid = $fromVillageId;
$column1 = 'troops_num';
$column2 = 'troops_out_num';
}
}
}
$this->backTroopsProperty['backTroops'] = $this->_getTroopsForVillage ($_backTroopsStr, $vid);
if ($this->backTroopsProperty['backTroops'] == NULL)
{
$m->dispose ();
$this->redirect ('build?id=39');
//return null;
}
$distance = WebHelper::getdistance ($fromVillageData['rel_x'], $fromVillageData['rel_y'], $toVillageData['rel_x'], $toVillageData['rel_y'], $this->setupMetadata['map_size'] / 2);
if ($this->isPost ())
{
$canSend = FALSE;
$troopsGoBack = array ();
foreach ($this->backTroopsProperty['backTroops']['troops'] as $tid => $tnum)
{
if ((isset ($_POST['t']) AND isset ($_POST['t'][$tid])))
{
$selNum = intval ($_POST['t'][$tid]);
if ($selNum < 0)
{
$selNum = 0;
}
if ($tnum < $selNum)
{
$selNum = $tnum;
}
$troopsGoBack[$tid] = $selNum;
if (0 < $selNum)
{
$canSend = TRUE;
continue;
}
continue;
}
else
{
$troopsGoBack[$tid] = 0;
continue;
}
}
$sendTroopsArray = array ('troops' => $troopsGoBack, 'hasHero' => FALSE, 'heroTroopId' => 0);
$hasHeroTroop = (($this->backTroopsProperty['backTroops']['hasHero'] AND isset ($_POST['_t'])) AND intval ($_POST['_t']) == 1);
if ($hasHeroTroop)
{
$sendTroopsArray['hasHero'] = TRUE;
$sendTroopsArray['heroTroopId'] = $this->backTroopsProperty['backTroops']['heroTroopId'];
$canSend = TRUE;
}
if (!$canSend)
{
$m->dispose ();
$this->redirect ('build?id=39');
//return null;
}
if ((!$this->isGameTransientStopped () AND !$this->isGameOver ()))
{
$troops1 = $this->_getTroopsAfterReduction ($fromVillageData[$column1], $toVillageId, $sendTroopsArray);
$troops2 = $this->_getTroopsAfterReduction ($toVillageData[$column2], $fromVillageId, $sendTroopsArray);
$m->backTroopsFrom ($fromVillageId, $column1, $troops1, $toVillageId, $column2, $troops2);
$timeInSeconds = intval ($distance / $this->_getTheSlowestTroopSpeed2 ($sendTroopsArray,$toVillageId,$toVillageData['player_id']) * 3600);
$procParams = $this->_getTroopAsString ($sendTroopsArray) . '|0||||||1';
$newTask = new QueueTask (QS_WAR_REINFORCE, intval ($fromVillageData['player_id']), $timeInSeconds);
$newTask->villageId = $fromVillageId;
$newTask->toPlayerId = intval ($toVillageData['player_id']);
$newTask->toVillageId = $toVillageId;
$newTask->procParams = $procParams;
///////////////tatarx/////////////////
                $newTask->tag          = array(
                    'troops' => NULL,
                    'hasHero' => FALSE,
                    'resources' => NULL
                );
                $affectCropConsumption = TRUE;
                if (($fromVillageData['is_oasis'] && trim($toVillageData['village_oases_id']) != ''))
                    {
                    $oArr = explode(',', trim($toVillageData['village_oases_id']));
                    foreach ($oArr as $oid)
                        {
                        if ($oid == $fromVillageData['id'])
                            {
                            $affectCropConsumption = FALSE;
                            break;
                            }
                        }
                    }
                if ($affectCropConsumption)
                    {
                    $newTask->tag['troopsCropConsume'] = $this->_getTroopCropConsumption($sendTroopsArray);
                    }
$this->queueModel->addTask ($newTask);
$m->dispose ();
$this->redirect ('build?id=39');
//return null;
}
}
else
{
$this->backTroopsProperty['time'] = intval ($distance / $this->_getTheSlowestTroopSpeed2 ($this->backTroopsProperty['backTroops']) * 3600);
}
$m->dispose ();
}
function _getTroopCropConsumption ($troopsArray)
{
$GameMetadata = $GLOBALS['GameMetadata'];
$consume = 0;
foreach ($troopsArray['troops'] as $tid => $tnum)
{
$consume += $GameMetadata['troops'][$tid]['crop_consumption'] * $tnum;
}
if ($troopsArray['hasHero'])
{
$consume += $GameMetadata['troops'][$troopsArray['heroTroopId']]['crop_consumption'];
}
return $consume;
}
function _getTroopAsString ($troopsArray)
{
$str = '';
foreach ($troopsArray['troops'] as $tid => $num)
{
if ($str != '')
{
$str .= ',';
}
$str .= $tid . ' ' . $num;
}
if ($troopsArray['hasHero'])
{
if ($str != '')
{
$str .= ',';
}
$str .= $troopsArray['heroTroopId'] . ' -1';
}
return $str;
}
function _getTroopsAfterReduction ($troopString, $targetVillageId, $sendTroopsArray)
{
if (trim ($troopString) == '')
{
return '';
}
$reductionTroopsString = '';
$t_arr = explode ('|', $troopString);
foreach ($t_arr as $t_str)
{
$t2_arr = explode (':', $t_str);
if ($t2_arr[0] == $targetVillageId)
{
$completelyBacked = TRUE;
$newTroopStr = '';
$t2_arr = explode (',', $t2_arr[1]);
foreach ($t2_arr as $t2_str)
{
list ($tid, $tnum) = explode (' ', $t2_str);
if ($tnum == 0 - 1)
{
if (!$sendTroopsArray['hasHero'])
{
if ($newTroopStr != '')
{
$newTroopStr .= ',';
}
$newTroopStr .= $tid . ' ' . $tnum;
$completelyBacked = FALSE;
continue;
}
continue;
}
else
{
if (isset ($sendTroopsArray['troops'][$tid]))
{
$n = $sendTroopsArray['troops'][$tid];
if ($n < 0)
{
$n = 0;
}
if ($tnum < $n)
{
$n = $tnum;
}
$tnum -= $n;
if (0 < $tnum)
{
$completelyBacked = FALSE;
}
}
if ($newTroopStr != '')
{
$newTroopStr .= ',';
}
$newTroopStr .= $tid . ' ' . $tnum;
continue;
}
}
if (!$completelyBacked)
{
if ($reductionTroopsString != '')
{
$reductionTroopsString .= '|';
}
$reductionTroopsString .= $targetVillageId . ':' . $newTroopStr;
continue;
}
continue;
}
else
{
if ($reductionTroopsString != '')
{
$reductionTroopsString .= '|';
}
$reductionTroopsString .= $t_str;
continue;
}
}
return $reductionTroopsString;
}
function _getTroopsForVillage ($troopString, $villageId)
{
if (trim ($troopString) == '')
{
return 0 - 1;
}
$t_arr = explode ('|', $troopString);
foreach ($t_arr as $t_str)
{
$t2_arr = explode (':', $t_str);
if ($t2_arr[0] == $villageId)
{
$troopTable = array ('hasHero' => FALSE, 'heroTroopId' => 0, 'troops' => array ());
$t2_arr = explode (',', $t2_arr[1]);
foreach ($t2_arr as $t2_str)
{
list ($tid, $tnum) = explode (' ', $t2_str);
if ($tid == 99)
{
continue;
}
if ($tnum == 0 - 1)
{
$troopTable['heroTroopId'] = $tid;
$troopTable['hasHero'] = TRUE;
continue;
}
$troopTable['troops'][$tid] = $tnum;
}
return $troopTable;
}
}
//return NULL;
}
function _getMaxBuildingLevel ($itemId)
{
$result = 0;
foreach ($this->buildings as $villageBuild)
{
if (($villageBuild['item_id'] == $itemId AND $result < $villageBuild['level']))
{
$result = $villageBuild['level'];
continue;
}
}
return $result;
}
function _getTheSlowestTroopSpeed2 ($troopsArray,$vv=0,$pp=0)
{
$minSpeed = 0 - 1;
foreach ($troopsArray['troops'] as $tid => $num)
{
if (0 < $num)
{
$speed = $this->gameMetadata['troops'][$tid]['velocity'];
if (($minSpeed == 0 - 1 OR $speed < $minSpeed))
{
$minSpeed = $speed;
continue;
}
continue;
}
}
if ($troopsArray['hasHero'])
{
$htid = $troopsArray['heroTroopId'];
$speed = $this->gameMetadata['troops'][$htid]['velocity'];
if (($minSpeed == 0 - 1 OR $speed < $minSpeed))
{
$minSpeed = $speed;
}
}
$q = new QueueModel();
$artefact= 1;
///Start Artefect
$efect = 1;
$pid = $this->player->playerId;
$vid = $this->data['selected_village_id'];
$tatarzx = new QueueModel();
$this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" ); 
$this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
if ($this->BigArt) {
$artefact = 2;
}else if ($this->SeCArt) {
$artefact = 1.5;
}else if ($this->SmallArt) {
$artefact = 2;
}
///End Artefect



$blvl = $this->_getMaxBuildingLevel (14);
$factor = ($blvl == 0 ? 100 : $this->gameMetadata['items'][14]['levels'][$blvl - 1]['value']);
$factor *= $this->gameMetadata['game_speed'];
$time = ($minSpeed * $artefact) * ($factor / 100);

$Plus_speed = $q->provider->fetchScalar( "SELECT COUNT(*) FROM p_queue p WHERE p.player_id=%s AND p.proc_type=53", array($this->player->playerId));
if($Plus_speed != 0)
{
     $time += ROUND($time*0.30);
}
return $time;
}
function _getTheSlowestTroopSpeed ($troopsArray)
{
$minSpeed = 0 - 1;
foreach ($troopsArray as $tid => $num)
{
if (0 < $num)
{
$speed = $this->gameMetadata['troops'][$tid]['velocity'];
if (($minSpeed == 0 - 1 OR $speed < $minSpeed))
{
$minSpeed = $speed;
continue;
}
continue;
}
}
if ((($this->hasHero AND isset ($_POST['_t'])) AND intval ($_POST['_t']) == 1))
{
$htid = $this->data['hero_troop_id'];
$speed = $this->gameMetadata['troops'][$htid]['velocity'];
if (($minSpeed == 0 - 1 OR $speed < $minSpeed))
{
$minSpeed = $speed;
}
}
$q = new QueueModel();
$artefact= 1;
///Start Artefect
$efect = 1;
$pid = $this->player->playerId;
$vid = $this->data['selected_village_id'];
$tatarzx = new QueueModel();
$this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type='4' AND artefacts='".$efect."' AND player_id='".$pid."'" );
$this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" ); 
$this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
if ($this->BigArt) {
$artefact = 2;
}else if ($this->SeCArt) {
$artefact = 1.5;
}else if ($this->SmallArt) {
$artefact = 2;
}
///End Artefect

$blvl = $this->_getMaxBuildingLevel (14);
$factor = ($blvl == 0 ? 100 : $this->gameMetadata['items'][14]['levels'][$blvl - 1]['value']);
$factor *= $this->gameMetadata['game_speed'];
$time =  ($minSpeed * $artefact) * ($factor / 100);

$Plus_speed = $q->provider->fetchScalar( "SELECT COUNT(*) FROM p_queue p WHERE p.player_id=%s AND p.proc_type=53", array($this->player->playerId));
if($Plus_speed != 0)
{
     $time += ROUND($time*0.10);
}

return $time;
}
function _canBuildNewVillage ()
{
$GameMetadata = $GLOBALS['GameMetadata'];
$neededCpValue = $totalCpRate = $totalCpValue = 0;
$m = new BuildModel ();
$result = $m->getVillagesCp ($this->data['villages_id']);
while ($result->next ())
{
list ($cpValue, $cpRate) = explode (' ', $result->row['cp']);
$cpValue += $result->row['elapsedTimeInSeconds'] * ($cpRate / 86400);
$totalCpRate += $cpRate;
$totalCpValue += $cpValue;
$neededCpValue = ceil(($this->data['villages_count']*$this->data['villages_count'])*255+500);
}
$totalCpRate = 0;
$totalCpValue = 0;
$m = new BuildModel ();
foreach ( $this->playerVillages as $vid => $pvillage )
{
$result = $m->getVillagesCp ($vid);
while ($result->next ())
{
$tempdata = explode(' ', $result->row['cp']);
$totalCpValue += $tempdata[0];
$totalCpRate += $tempdata[1];
}
}
$totalCpRate = floor($totalCpRate);
$totalCpValue = floor ($totalCpValue);
$m->dispose ();
return $neededCpValue <= $totalCpValue;
}
function __getCoordInRange ($map_size, $x)
{
if ($map_size <= $x)
{
$x -= $map_size;
}
else
{
if ($x < 0)
{
$x = $map_size + $x;
}
}
return $x;
}
function __getVillageId ($map_size, $x, $y)
{
return $x * $map_size + ($y + 1);
}
function _containBuildingTarget ($item_id)
{
$i = 0;
while ($i <= $this->catapultCanAttackLastIndex)
{
if ($this->catapultCanAttack[$i] == $item_id)
{
return TRUE;
}
++$i;
}
return FALSE;
}
}
$p = new GPage ();
$p->run ();
?>