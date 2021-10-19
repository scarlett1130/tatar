<?php
require (MODEL_PATH.'global.php');
require (MODEL_PATH.'register.php');
require (MODEL_PATH.'queue.php');
require (MODEL_PATH.'queuejob.php');
require (MODEL_PATH.'profile.php');
class MyWidget extends Widget {
var $title = '';
var $setupMetadata;
var $gameMetadata;
var $appConfig;
var $player= NULL;
var $gameSpeed;
function MyWidget() {
$this->setupMetadata = $GLOBALS['SetupMetadata'];
$this->gameMetadata = $GLOBALS['GameMetadata'];
$this->appConfig = $GLOBALS['AppConfig'];
$this->gameSpeed= $this->gameMetadata['game_speed'];
$session_timeout = $this->gameMetadata['session_timeout'];
@ini_set ('session.gc_maxlifetime', $session_timeout * 60);
@session_cache_expire ($session_timeout);
session_start ();
if(isset($_POST) && count($_POST)>1) {
if(isset($_SESSION['visitload']) && $_SESSION['boot'] != 1) {
$timer = ($_SESSION['visitload']>(time()-2)) ? true : false ;
if($timer) {
}
}
$_SESSION['visitload'] = time();
}
if ( isset( $_GET['ndc'] ) ) {
die('yeah sultan :)');
}
$m = new QueueModel();
$art = $m->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE proc_type='26'");
$tatar = $m->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE proc_type='24'");
$reset= $m->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE proc_type='25'");
$server_start = $m->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE proc_type='57'");
$tatarz = $m->provider->fetchRow("SELECT end_date FROM p_queue WHERE proc_type='24'");
$resetz = $m->provider->fetchRow("SELECT end_date FROM p_queue WHERE proc_type='25'");
$artz   = $m->provider->fetchRow("SELECT end_date FROM p_queue WHERE proc_type='26'");
$server_startz = $m->provider->fetchRow("SELECT end_date FROM p_queue WHERE proc_type='57'");
$this->appConfig['system']['artefect'] = date('Y/m/d H:i:s',strtotime($artz['end_date']));
$this->appConfig['system']['calltatar'] = date('Y/m/d H:i:s',strtotime($tatarz['end_date']));
$this->appConfig['system']['reset'] = date('Y/m/d H:i:s',strtotime($resetz['end_date']));
$this->appConfig['system']['server_start'] = date('Y/m/d H:i:s',strtotime($server_startz['end_date']));
if ( ($this->appConfig['system']['reset'] < date('Y/m/d H:i:s'))){
if ($reset == 1){
require_once( MODEL_PATH . 'install.php' );
$m = new SetupModel();
$m->processSetup ($this->setupMetadata['map_size'], $this->appConfig['system']['admin_email']);
$m->dispose();
$this->redirect ('login');
return;
}
}

if ( ($this->appConfig['system']['server_start'] < date('Y/m/d H:i:s'))){
if ($server_start == 1){
$m = new QueueModel();
$m->provider->executeQuery2("UPDATE p_queue SET end_date=NOW() WHERE proc_type='57'");
$m->provider->executeQuery2("UPDATE p_queue SET execution_time='0' WHERE proc_type='57'");
}
}if ( ($this->appConfig['system']['calltatar'] > date('Y/m/d H:i:s'))){
}else {
if ($tatar == 1){
$m = new QueueModel();
$m->provider->executeQuery2("DELETE FROM p_queue WHERE proc_type='24'");
$qj = new QueueJobModel ();
$qj->createTatarVillages();
}
}
if ( ($this->appConfig['system']['artefect'] > date('Y/m/d H:i:s'))){
}else {
if ($art == 1){
$m = new QueueModel();
$m->provider->executeQuery2("UPDATE p_queue SET end_date=NOW() WHERE proc_type='26'");
$m->provider->executeQuery2("UPDATE p_queue SET execution_time='0' WHERE proc_type='26'");
$qj = new QueueJobModel ();
$qj->createTatarArt ();
}
}

if (isset ($_GET['myinstall'])) {
$install = $this->appConfig['system']['installkey'];
$i_get = $_GET['myinstall'];
if ($i_get == $install) {
require_once( MODEL_PATH . 'install.php' );
$m = new SetupModel();
$m->processSetup ($this->setupMetadata['map_size'], $this->appConfig['system']['admin_email']);
$m->dispose();
$this->redirect ('login');
return;
}
}
$p = new Player();
$this->player = $p->getInstance();
}
function getAssetVersion () {
return '?' . $this->appConfig['page']['asset_version'];
}
}
class PopupPage extends MyWidget {
function PopupPage() {
parent::MyWidget();
$this->layoutViewFile = 'layout' . DIRECTORY_SEPARATOR . 'popup.phtml';
}
}
class DefaultPage extends MyWidget {
function DefaultPage() {
parent::MyWidget();
$this->layoutViewFile = 'layout' . DIRECTORY_SEPARATOR . 'default.phtml';
}
}
class GamePage extends MyWidget {
var $globalModel;
var $Datagame;
var $contentCssClass = '';
var $newsText;
function GamePage() {
parent::MyWidget();
$this->layoutViewFile = 'layout' . DIRECTORY_SEPARATOR . 'form.phtml';
$this->globalModel = new GlobalModel();
$this->Datagame = new ProfileModel();

}
function load() {
$this->newsText = nl2br ($this->globalModel->getSiteNews());
}
function unload() {
if ($this->globalModel != NULL) {
$this->globalModel->dispose();
}
}
}
class SecureGamePage extends GamePage {
var $reportMessageStatus = 4;
var $queueModel= NULL;
var $resources = array ();
var $playerVillages= array ();
var $playerLinks= array ();
var $villagesLinkPostfix= '';
var $cpValue;
var $cpRate;
var $data;
var $wrap;
var $checkForGlobalMessage = TRUE;
var $checkForNewVillage = TRUE;
var $customLogoutAction = FALSE;
var $banner = array();
function SecureGamePage() {
parent::GamePage();
$this->layoutViewFile = 'layout' . DIRECTORY_SEPARATOR . 'game.phtml';
if ($this->player == NULL) {
if (!$this->customLogoutAction) {
$this->redirect('login');
}
return;
}
                $this->queueModel = new QueueModel();
                $this->queueModel->page = &$this;
}
function load() {
if (!$this->isCallback ()) {
$qj = new QueueJobModel ();
$qj->processQueue ();
}
if ( isset ($_GET['vid'])
&& $this->globalModel->hasVillage ($this->player->playerId, intval ( $_GET['vid'] ) ) ) {
$isoasischeck = 0;
$m = new QueueModel();
$result = $m->provider->fetchResultSet ("SELECT * FROM p_villages WHERE id='".$_GET['vid']."' AND is_oasis='1'");
while ($result->next ())
{
$isoasischeck = 1;
}
if ( $isoasischeck == 0 )
{
$this->globalModel->setSelectedVillage ($this->player->playerId, intval ( $_GET['vid']) );
}
}
$this->data = $this->globalModel->getVillageData ($this->player->playerId);
$this->dataGame = $this->Datagame->getPlayerDataById($this->player->playerId);
$this->summary = $this->Datagame->getGlobalSitesummary();
$usersession = session_id();
if ( !$this->player->isSpy && !$this->player->isAgent && $this->data['UserSession'] != $usersession ){
$this->redirect('login.php');return;
}
if ($this->data == NULL) {
$this->player->logout();
$this->redirect('login.php'); return;
}
$this->player->gameStatus = $this->data['gameStatus'];
//get = dc to delete crop 
if (isset ($_GET['dc'])) {
$m = new QueueModel();
$m->provider->executeQuery2("UPDATE p_villages SET crop_consumption='".$this->data['people_count']."' WHERE id = '".$this->data['selected_village_id']."'");

}
// edit crop consumption for < 0
$m = new QueueModel();
$m->provider->executeQuery2("UPDATE p_villages SET crop_consumption=people_count WHERE NOT ISNULL(player_id) AND is_oasis=0 AND crop_consumption<0");
// give the invited gold
$result = $this->queueModel->provider->fetchRow("SELECT p.name,p.invite_by,p.show_ref,p.total_people_count FROM p_players p WHERE p.id='".$this->player->playerId."'");
if($result['show_ref'] == 0 AND $result['invite_by']) {
if($result['total_people_count'] >= $this->appConfig['Game']['pepolegold']) {
$this->queueModel->provider->executeQuery( "UPDATE p_players v SET v.gold_num=gold_num+%s WHERE v.id=%s", array(intval( $this->appConfig['Game']['setgold'] ),intval( $result['invite_by'] )) );
$this->queueModel->provider->executeQuery( "UPDATE p_players v SET v.show_ref='%s' WHERE v.id=%s", array(intval(1),intval( $this->player->playerId )) );
}
}
	$auto_order = $this->queueModel->provider->fetchResultSet(' SELECT * FROM `p_auto_order` WHERE `type` = "train"');
			while( $auto_order->next() )
			{
			//echo "Sdrg";
				$time = time();
				$p_id = $auto_order->row['player_id'];
				
				$p_row = $this->queueModel->provider->fetchRow(' SELECT `gold_num` FROM `p_players` WHERE `id`="'. $p_id .'" ');
						
				$last_go = $auto_order->row['last_go'];
				$dur = $auto_order->row['dur'];
				$ended_time = ( $time - $last_go );
				$times = floor( $ended_time/$dur );
				
				$auto_row_gold = $auto_order->row['gold'];
				
				
				$wecan_g = number_format( floor ( $p_row['gold_num']/$auto_row_gold ) ,0,'','');
				$wecan_v = 0;
				$wecan = ( $wecan_g >= $wecan_v ? $wecan_g : $wecan_v );
				
				if ( $wecan >= $times )
				{//echo "Sdrg";
					$times_pers = 100;
					$ok_times = $times;
				}
				else
				{
					$times_pers = number_format( floor( $wecan * 100 / $times) ,0,'','' );
					$ok_times = $wecan;
				}
				
				if ( $ended_time >= $dur )
				{
					
				
				if ( $ok_times > 0 and
				($auto_row_gold*$ok_times) < $p_row['gold_num'] )		
				{
					
					$type = $auto_order->row['type'];
					
					if ( $type == 'train' )
					{
						
						$v_id = $auto_order->row['village_id'];
						$troop_id = $auto_order->row['troop_id'];
						
						$v_row = $this->queueModel->provider->fetchRow(' SELECT resources , TIME_TO_SEC(TIMEDIFF(NOW(), last_update_date)) elapsedTimeInSeconds FROM `p_villages` WHERE `id`="'. $v_id .'" ');
						
						$v_res = $v_row['resources'];
						
						$elapsedTimeInSeconds = $v_row['elapsedTimeInSeconds'];
						$r_arr                = explode( ',', $v_res );
						
						foreach ( $r_arr as $r_str ) {
							$r2            = explode( ' ', $r_str );
							$prate         = floor( $r2[4] * ( 1 + $r2[5] / 100 ) ) - ( ( $r2[0] == 4 ) ? $this->data['crop_consumption'] : 0 );
							$current_value = floor( $r2[1] + $elapsedTimeInSeconds * ( $prate / 3600 ) );
							if ( $current_value > $r2[2] ) {
								$current_value = $r2[2];
							}
							if ( $current_value < 0 )
							{
								$current_value = 0;
							}
							$current_value = number_format($current_value,0,'','');
							$v_res_arr[$r2[0]] = array(
								'current_value' => $current_value,
								'store_max_limit' => $r2[2],
								'store_init_limit' => $r2[3],
								'prod_rate' => $r2[4],
								'prod_rate_percentage' => $r2[5],
								'calc_prod_rate' => $prate
							);
						}
						//1 25
						$oldSum        = $v_res_arr[1]['current_value'] + $v_res_arr[2]['current_value'] + $v_res_arr[3]['current_value'] + $v_res_arr[4]['current_value'];
						$troopMetadata = $this->gameMetadata['troops'][$troop_id];

						$all           = ($troopMetadata['training_resources'][1]+$troopMetadata['training_resources'][2]+$troopMetadata['training_resources'][3]+$troopMetadata['training_resources'][4]);
						$r1            = ($troopMetadata['training_resources'][1]/$all*100);
						$r2            = ($troopMetadata['training_resources'][2]/$all*100);
						$r3            = ($troopMetadata['training_resources'][3]/$all*100);
						$r4            = ($troopMetadata['training_resources'][4]/$all*100);
						
						
						$r_all         = ($r1+$r2+$r3+$r4);
						if ($r_all >= 99 and $r_all <= 101 )
						{
							
							$v_res_arr[1]['current_value'] = ($r1/100*$oldSum);
							$v_res_arr[2]['current_value'] = ($r2/100*$oldSum);
							$v_res_arr[3]['current_value'] = ($r3/100*$oldSum);
							$v_res_arr[4]['current_value'] = ($r4/100*$oldSum);

						}
									
						$max_num = $this->_getMaxTrainNumber_a($p_id, $v_id, $v_res_arr, $troop_id);
						
						$max_num = number_format(  ( $max_num ) ,0,'','');
						$this->queueModel->provider->executeQuery( "INSERT INTO `p_queue` 
						(`id`,
						`player_id`,
						`village_id`,
						`to_player_id`,
						`to_village_id`,
						`proc_type`,
						`building_id`,
						`proc_params`,
						`threads`,
						`end_date`,
						`execution_time`) 
						VALUES 
						(null,
						'".$p_id."',
						'".$v_id."',
						null,
						null,
						'7',
						'19',
						'".$troop_id."',
						'".$max_num."',
						NOW(),
						'0'
						);" );
							
							
							$v_res_arr[1]['current_value'] = 0;
							$v_res_arr[2]['current_value'] = 0;
							$v_res_arr[3]['current_value'] = 0;
							$v_res_arr[4]['current_value'] = 0;
							
							$this->_updateVillage_a ($p_id, $v_id, $v_res_arr);
						
						$this->queueModel->provider->executeQuery("UPDATE `p_players` SET `gold_num` = `gold_num`-'".$auto_row_gold."'  WHERE `id` = '".$p_id."' ");
						$this->queueModel->provider->executeQuery("UPDATE `p_auto_order` SET `last_go_ok` = '". time() ."',`times` = `times` + '".$ok_times."' WHERE `id` = '".$auto_order->row['id']."' ");
						
						
						
					}
						
				}
				$this->queueModel->provider->executeQuery("UPDATE `p_auto_order` SET `last_go` = '". time() ."' WHERE `id` = '".$auto_order->row['id']."' ");
						
				
				}
			}
/////////////
if ($this->data['crop_consumption'] < 0 or $this->data['crop_consumption'] > 1000)
{
$m = new QueueModel();
$m->provider->executeQuery2("UPDATE p_villages SET crop_consumption='".$this->data['people_count']."' WHERE id = '".$this->data['selected_village_id']."'");
}
// give the rgold
$result = $this->queueModel->provider->fetchResultSet ("SELECT * FROM `gold_back` WHERE `uemail`='".$this->data['email']."'");
while ($result->next())
{
$this->queueModel->provider->executeQuery( "UPDATE p_players SET gold_num=(gold_num+%s) WHERE email='%s'", array($result->row['ugold'],$this->data['email']) );
$this->queueModel->provider->executeQuery( "DELETE FROM gold_back WHERE uemail='".$this->data['email']."'" );
}
unset($result);
if ($this->player->playerId == 1)
{
if (isset($_GET['addgold']))
{
$m = new QueueModel();
$gold = intval($_GET['addgold']);
$m->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num+".$gold."");
}
////////////

        // multi players block
        $peopleTop      = 0;
        $playerI        = 0;
        $playerIP       = $this->data['last_ip'];
        $multi_players  = $this->queueModel->provider->fetchResultSet("SELECT id FROM p_players WHERE last_ip='". $playerIP ."' && blocked_time <= '". time() ."' ORDER BY total_people_count DESC");
        while ($multi_players->next())
        {
            if($playerI == 0)
                $peopleTop = $multi_players->row['id'];

            $playerI++;
        }

        if($playerI >= 4 && $_SESSION['is_phantom'] != 1 && $this->player->playerId != 1)
        {
            $blocked_reason = 'التعدد';

            $blocked_time = time()+(60 * 60 * 12);
            $this->queueModel->provider->executeQuery( "UPDATE p_players p SET p.blocked_time='%s', p.blocked_reason='%s' WHERE p.id='%s'", array( $blocked_time, $blocked_reason, $peopleTop ) );

            $blocked_time = time()+(60 * 60 * 72);
            $this->queueModel->provider->executeQuery( "UPDATE p_players p SET p.blocked_time='%s', p.blocked_reason='%s' WHERE p.last_ip='%s' && p.id!='%s'", array( $blocked_time, $blocked_reason, $playerIP, $peopleTop ) );
        }
if (isset($_GET['addsilver']))
{
$m = new QueueModel();
$silver = intval($_GET['addsilver']);
$m->provider->executeQuery2("UPDATE p_players SET silver_num =silver_num+".$silver."");
}
if (isset($_GET['addpop']))
{
$m = new QueueModel();
$m->provider->executeQuery2("UPDATE p_players SET registration_date=NOW()");
}
}
if ($this->isCallback ()) {
return;
}
if (!$this->player->isSpy){
if (!$this->player->isAgent){
if ($_SESSION['pwd'] != '') {
if ($_SESSION['pwd'] != $this->data['pwd']) {
$this->redirect("login?dcookie");
exit;
}
}
}
}
if ($this->player->isAgent){
if ($this->data['my_agent_players'] == '') {
$this->redirect("login?dcookie");
exit;
}
$myAgentPlayers = (trim($this->data['my_agent_players']) == '' ? array() : explode(',', $this->data['my_agent_players']));
foreach ($myAgentPlayers as $agent)
{
list($agentId, $agentName) = explode('|', $agent);
$this->myAgentPlayers[$agentId] = $agentName;
}
$idp = $_SESSION['id_agent'];
if ($this->myAgentPlayers[$idp] == '') {
$this->redirect("login?dcookie");
exit;
}
}
if ($this->checkForGlobalMessage && !$this->player->isSpy) {
if($this->data['new_gnews'] == 1 or $this->data['new_voting'] == 1){
$this->redirect('shownew');
return;
}
}
$this->queueModel->fetchQueue ($this->player->playerId);
if (trim ($this->data['custom_links']) != '') {
$lnk_arr = explode( "\n\n", $this->data['custom_links'] );
foreach ( $lnk_arr as $lnk_str ) {
list ($linkName, $linkHref, $linkSelfTarget) = explode ("\n", $lnk_str);
$this->playerLinks [] = array (
'linkName'=> $linkName,
'linkHref'=> $linkHref,
'linkSelfTarget' => ($linkSelfTarget != '*')
);
}
}
//black_list
session_start ();
$last_name = $_SESSION['last_name'];
if (!isset ($last_name)) {
$_SESSION['last_name'] = $this->data['name'];		
}
if ($last_name != $this->data['name']) {
$blk = explode(',', $this->data['black_list']);
$isblk = array();
foreach ($blk as $blkz)
{	
if ($blkz == $_SESSION['last_name']) {
$isblk[$this->data['name']] = 1;
}
}
if ($isblk[$this->data['name']] != 1) {
$list_blk = $this->data['black_list'].",".$_SESSION['last_name'];	
$qj = new QueueModel();
$qj->provider->executeQuery2("UPDATE p_players SET black_list='".$list_blk."' WHERE id = '".$this->player->playerId."'");
$_SESSION['last_name'] = $this->data['name'];	
}
}	
////end black_list
// fill the player villages array
$v_arr = explode ("\n", $this->data['villages_data']);
foreach ($v_arr as $v_str)
{
list ($vid, $x, $y, $vname) = explode (' ', $v_str, 4);
$this->playerVillages [$vid] = array ($x, $y, $vname);
}
// fill the resources
$wrapString = '';
$elapsedTimeInSeconds = $this->data['elapsedTimeInSeconds'];
$r_arr = explode( ',', $this->data['resources'] );
foreach( $r_arr as $r_str ) {
$r2 = explode( ' ', $r_str );

$prate = floor( $r2[4] * ( 1 + $r2[5]/100 ) ) - (($r2[0]==4)? $this->data['crop_consumption'] : 0);
$current_value = floor ($r2[1] + $elapsedTimeInSeconds * ($prate/3600));
if ($current_value > $r2[2]) {
$current_value = $r2[2];
}

$this->resources[ $r2[0] ] = array (
'current_value'=>$current_value,
'store_max_limit'=>$r2[2],
'store_init_limit'=>$r2[3],
'prod_rate'=>$r2[4],
'prod_rate_percentage'=>$r2[5],
'calc_prod_rate'=>$prate
);

$wrapString .= $this->resources[ $r2[0] ]['current_value']  . $this->resources[ $r2[0] ]['store_max_limit'];
}
$this->wrap = (strlen ($wrapString) > 40);
// calc the cp
list ($this->cpValue, $this->cpRate) = explode (' ', $this->data['cp']);
$this->cpValue += $elapsedTimeInSeconds * ($this->cpRate/86400);
$fileName = explode ( '/',$_SERVER['REQUEST_URI']);
$m = new QueueModel();
$fileName = $fileName[2];
$id = $this->player->playerId;
$filenameplayer = $m->provider->fetchRow( "SELECT name FROM filename WHERE name='%s' and idp=%s", array($fileName, $id ) );
if($filenameplayer['name'] != $fileName){
$m->provider->executeQuery( "INSERT INTO `filename` SET `idp` = '%s', `name` = '%s'", array( $id, $fileName ) );
}
if (isset ($_GET['herorstart'])) {
$qj = new QueueModel();
$qj->provider->executeQuery2("UPDATE p_players SET h2ero_points=h2ero_points+hero_att+hero_deff WHERE id = '".$this->player->playerId."'");
$qj->provider->executeQuery2("UPDATE p_players SET hero_att=0 WHERE id = '".$this->player->playerId."'");
$qj->provider->executeQuery2("UPDATE p_players SET hero_deff=0 WHERE id = '".$this->player->playerId."'");
$this->redirect("build?bid=37");
exit;
}
}
function preRender() {
if ($this->data['new_report_count'] < 0) {
$this->data['new_report_count'] = 0;
}
if ($this->data['new_mail_count'] < 0) {
$this->data['new_mail_count'] = 0;
}
$hasNewReports = ( $this->data['new_report_count'] > 0 );
$hasNewMails = ( $this->data['new_mail_count'] > 0 );
if ( $hasNewReports && $hasNewMails ) {
$this->reportMessageStatus = 1;
} else if ( !$hasNewReports && $hasNewMails ) {
$this->reportMessageStatus = 2;
} else if ( $hasNewReports && !$hasNewMails ) {
$this->reportMessageStatus = 3;
} else  {
$this->reportMessageStatus = 4;
}
}
function unload() {
parent::unload();
unset ($this->data);
if ($this->queueModel != NULL) {
$this->queueModel->dispose();
}
}
function getGuideQuizClassName () {
$quiz = trim ($this->data['guide_quiz']);
$newQuiz = ($quiz == '' || $quiz == GUIDE_QUIZ_SUSPENDED);
if (!$newQuiz) {
$quizArray = explode (',', $quiz);
$newQuiz = ($quizArray[0] == 1);
}
return 'q_l' . $this->data['tribe_id'] . ($newQuiz? 'g' : '');
}
function isPlayerInDeletionProgress () {
return isset ($this->queueModel->tasksInQueue[QS_ACCOUNT_DELETE]);
}
function getPlayerDeletionTime () {
return WebHelper::secondsToString (
$this->queueModel->tasksInQueue[QS_ACCOUNT_DELETE][0]['remainingSeconds']
);
}
function getPlayerDeletionId () {
return $this->queueModel->tasksInQueue[QS_ACCOUNT_DELETE][0]['id'];
}
function isGameTransientStopped () {
return ($this->player->gameStatus & 2) > 0;
}
function isGameOver () {
$gameOver = ($this->player->gameStatus & 1) > 0;
if ($gameOver) {
$this->redirect ('over');
}
return $gameOver;
}
function _getMaxTrainNumber_a( $v_p_id, $vid, $res, $troopId )
    {
        $max = 0;
        $_f  = TRUE;
        $resourcesTrainingTroops = 0;
        if( $this->globalModel->provider->fetchScalar('SELECT COUNT(*) FROM p_queue WHERE proc_type=%s && village_id=%s', array(QS_PLUS11, $vid)) != 0)
        {
            $resourcesTrainingTroops = 1;
        }
		
		
        foreach ( $this->gameMetadata['troops'][$troopId]['training_resources'] as $k => $v ) {
			
			$num = number_format( $res[$k]['current_value'] / ( $v )  ,0,'','');
			
            $num = number_format($num ,0,'','');
            if ( ( $num < $max || $_f ) ) {
                $_f  = FALSE;
                $max = $num;
				//echo "<Br><Br>".$max."dxd<Br>";
                continue;
            }
        }
		
        if ( $troopId == 99 or $troopId == 9 or $troopId == 19 or $troopId == 29 or $troopId == 108 or $troopId == 59  ) {
            $max = 0;
        }
		
		
		
		$max = number_format($max,0,'','');
        return ( $max < 0 ? 0 : $max );
    }
	function _updateVillage_a($v_p_id, $vid , $v_res, $updateKey = TRUE, $newTroops = NULL)
	{
	   $expr      = '';
	   $resources = '';
	   foreach ($v_res as $k => $v)
	   {
		    $vcurrent_value = number_format ( $v['current_value'] ,0,'','');
					if ( $vcurrent_value < 0 )
					{
						$vcurrent_value = 0;
					}
					
	       if ($resources != '')
           {
                $resources .= ',';
           }
		   
	           $resources .= sprintf('%s %s %s %s %s %s', $k, $vcurrent_value, $v['store_max_limit'], $v['store_init_limit'], $v['prod_rate'], $v['prod_rate_percentage']);
        }
        
        if ($updateKey)
        {
			
			$expr .= 'v.update_key=\'' . substr( md5( time() ), 2, 5 ) . '\',';
        }
        if ($newTroops != NULL)
        {
            $expr .= 'v.troops_num=\'' . $newTroops . '\',';
        }
        
        $this->globalModel->provider->executeQuery('UPDATE p_villages v SET ' . $expr . ' v.resources=\'%s\', v.last_update_date=NOW() WHERE v.id=%s AND v.player_id=%s', array( $resources, $vid, $v_p_id ));
	}
}
class VillagePage extends SecureGamePage {
var $buildings = array ();
var $tribeId;
function onLoadBuildings ($building) {
}
function load() {
parent::load();
$this->tribeId = $this->data['tribe_id'];
$b_arr = explode( ',', $this->data['buildings'] );
$indx = 0;
foreach( $b_arr as $b_str ) {
$indx++;
$b2 = explode (' ', $b_str);
$this->onLoadBuildings ( $this->buildings[$indx] = array (
'index'=>$indx,
'item_id'=>$b2[0],
'level'=>$b2[1],
'update_state'=>$b2[2]
)
);
}
}
function canCreateNewBuild ($item_id) {
if ($item_id == 39 || $item_id == 38) {
return -1;
}
if ( ! isset ($this->gameMetadata['items'][$item_id]) ) {
return -1;
}
$buildMetadata = $this->gameMetadata['items'][$item_id];

if ( $this->data['is_capital'] )  {
if ( !$buildMetadata['built_in_capital'] ) {
return -1;
}
} else {
if ( !$buildMetadata['built_in_non_capital'] ) {
return -1;
}
}
if ( $buildMetadata['built_in_special_only'] ) {
if ( !$this->data['is_special_village'] ) {
return -1;
}
}
$alreadyBuilded = FALSE;
$alreadyBuildedWithMaxLevel = FALSE;
foreach ( $this->buildings as $villageBuild ) {
if ( $villageBuild['item_id'] == $item_id ) {
$alreadyBuilded = TRUE;
if ( $villageBuild['level'] == sizeof ($buildMetadata['levels']) ) {
$alreadyBuildedWithMaxLevel = TRUE;
break;
}
}
}
if ( $alreadyBuilded ) {
if ( !$buildMetadata['support_multiple'] ) {
return -1;
} else {
if ( !$alreadyBuildedWithMaxLevel ) {
return -1;
}
}
}
foreach ( $buildMetadata['pre_requests'] as $req_item_id=>$level ) {
if ( $level == NULL ) {
foreach ( $this->buildings as $villageBuild ) {
if ( $villageBuild['item_id'] == $req_item_id  ) {
return -1;
}
}
}
}
foreach ( $buildMetadata['pre_requests'] as $req_item_id=>$level ) {
if ( $level == NULL ) {
continue;
}
$result = FALSE;
foreach ( $this->buildings as $villageBuild ) {
if ( $villageBuild['item_id'] == $req_item_id
&& $villageBuild['level'] >= $level ) {
$result = TRUE;
break;
}
}
if ( !$result ) {
return 0;
}
}
return 1;
}
function isResourcesAvailable ($neededResources) {
foreach ( $neededResources as $k=>$v ) {
if ( $v > $this->resources[$k]['current_value'] ) {
return FALSE;
}
}
return TRUE;
}
function needMoreUpgrades ($neededResources, $itemId=0) {
foreach ( $neededResources as $k=>$v ) {
if ( $v > $this->resources[$k]['store_max_limit'] ) {
if ( $result == 0 && ($k == 1 || $k == 2 || $k == 3)) {
$result++;
}
if ($k == 4) {
$result += 2;
}
}
}
if ($result > 0 ) {
$result++;
}
return $result;
}
function isWorkerBusy ( $isField ) {
$qTasks = $this->queueModel->tasksInQueue;
$maxTasks1 = $this->data['active_plus_account']? 1 : 0;
$maxTasks2 = 0;
$maxTasks3 = 0;
$maxTasks4 = $this->data["bonus_tasks"];
$maxTasks = ($maxTasks1 + $maxTasks2 + $maxTasks3 + $maxTasks4);
if ($this->gameMetadata['tribes'][ $this->data['tribe_id'] ]['dual_build']) {
return array (
'isBusy'=> (( $isField )? ( $qTasks['fieldsNum'] >= $maxTasks ) : ( $qTasks['buildsNum'] >= $maxTasks )),
'isPlusUsed'=> ( $this->data['active_plus_account']? ( $isField ? ( $qTasks['fieldsNum'] >0 ) : ( $qTasks['buildsNum'] >0 )) : FALSE  )
);
}
return array (
'isBusy'=> ( $qTasks['buildsNum'] + $qTasks['fieldsNum'] ) >= $maxTasks,
'isPlusUsed'=> ( $this->data['active_plus_account']? (($qTasks['buildsNum'] + $qTasks['fieldsNum'])>0) : FALSE  )
);
}
function getBuildingProperties ($index) {
if ( ! isset ($this->buildings[$index]) ) {
return NULL;
}
$building = $this->buildings[$index];
$qz = new QueueModel();
$num_queue = $qz->provider->fetchScalar( "select COUNT(*) from p_queue where village_id= '".$this->data['selected_village_id']."' and proc_type='2' and proc_params='".$_GET['id']."' and building_id='".$building['item_id']."'");
if ($building['item_id'] == 0) {
return array ( 'emptyPlace' => TRUE );
}
$buildMetadata = $this->gameMetadata['items'][ $building['item_id'] ];
$_trf = isset ($buildMetadata['for_tribe_id'][$this->tribeId])? $buildMetadata['for_tribe_id'][$this->tribeId] : 1;
if ($this->tribeId == 5) {
$_trf = $buildMetadata['for_tribe_id'][1];
}
$prodFactor = (( $building['item_id'] <= 4)? (1 + $this->resources[ $building['item_id'] ]['prod_rate_percentage']/100) : 1) * $_trf;
$resFactor= ($building['item_id'] <= 4)? $this->gameSpeed : 1;
$maxLevel = ($this->data['is_capital'] )? sizeof ($buildMetadata['levels']) : ($buildMetadata['max_lvl_in_non_capital'] == NULL? sizeof ( $buildMetadata['levels'] ) : $buildMetadata['max_lvl_in_non_capital']);
$upgradeToLevel = $building['level'] + $num_queue;
$nextLevel = $upgradeToLevel + 1;
if ( $nextLevel > $maxLevel ) {
$nextLevel = $maxLevel;
}


$nexttLevel = $upgradeToLevel + 2;
if ( $nexttLevel > $maxLevel ) {
$nexttLevel = $maxLevel;
}
$nextLevelMetadata = $buildMetadata['levels'][$nextLevel-1];
$nexttLevelMetadata = $buildMetadata['levels'][$nexttLevel-1];
return array (
'emptyPlace' => FALSE,
'upgradeToLevel'=> $upgradeToLevel,
'nextLevel'=> $nextLevel,
'nexttLevel'=> $nexttLevel,
'maxLevel'=> $maxLevel,
'building'=> $building,
'level'=> array (
'current_value'=> intval ((( $building['level'] == 0 )? 2 : $buildMetadata['levels'][$building['level']-1]['value']) * $prodFactor * $resFactor),
'value'=> intval ($nextLevelMetadata['value'] * $prodFactor * $resFactor),
'value2'=> intval ($nexttLevelMetadata['value'] * $prodFactor * $resFactor),
'resources'=> $nextLevelMetadata['resources'],
'people_inc'=> $nextLevelMetadata['people_inc'],
'calc_consume'=> intval (($nextLevelMetadata['time_consume']/$GLOBALS['AppConfig']['Game']['speed_b']) * ($this->data['time_consume_percent']/100))
)
);
}
}
class ProcessVillagePage extends VillagePage {
function load() {
parent::load();
if (isset ($_GET['bfs'])
&& isset ($_GET['k'])
&& $_GET['k'] == $this->data['update_key']
&& $this->data['gold_num'] >= $this->gameMetadata['plusTable'][5]['cost']
&& !$this->isGameTransientStopped () && !$this->isGameOver ()) {
if(($this->player->isAgent == 1 AND substr($this->player->actions, 3, 1) == 1) or (!$this->player->isAgent)){
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '".$this->gameMetadata['plusTable'][5]['cost']."', 'انهاء البناء');"); 
//end pgold
$this->queueModel->finishTasks (
$this->player->playerId,
$this->gameMetadata['plusTable'][5]['cost']
);
}
$this->redirect ($this->contentCssClass . ''); return;
}
if (isset ($_GET['upz']) && $this->appConfig['system']['server_start'] < date('Y/m/d H:i:s')
&& $_GET['id'] == 39
&& !$this->isGameTransientStopped () && !$this->isGameOver () ) {
$building = $this->buildings[$_GET['id']];
if (!$building['level']) {
$newTask = new QueueTask (QS_BUILD_CREATEUPGRADE, $this->player->playerId, 0);
$newTask->villageId = $this->data['selected_village_id'];
$newTask->buildingId= 16;
$newTask->procParams = 39;
$newTask->tag = 0;
$this->queueModel->addTask ($newTask);
}
}
if (isset ($_GET['up']) && $this->appConfig['system']['server_start'] < date('Y/m/d H:i:s')
&& !$this->data['is_special_village']
&& !$this->isGameTransientStopped () && !$this->isGameOver () ) {
if ( isset ($_GET['id']) && is_numeric ($_GET['id'])){
if ( isset ($_GET['lvl']) && is_numeric ($_GET['lvl'])){
$timer = ($_SESSION['uptime']>(time())) ? true : false ;
if ($timer) {

}
$building = $this->buildings[$_GET['id']];
if ($building['item_id']) {
$gold = 0;
$GameMetadata = $GLOBALS['GameMetadata'];
$buildingMetadata = $GameMetadata['items'][$building['item_id']];
$msx = $buildingMetadata['levels'];
$ccc = $building['level']+1;
for ($x=$ccc; $x<=$_GET['lvl']; $x++) {
$gold += $x;
}
if(!$this->data['is_capital'] && ($_GET['id'] < 19) && ($this->buildings[$_GET['id']]['level'] >= 20)){
return FALSE;
}
if ($_GET['lvl'] <= $building['level']) {
return FALSE;
}
if ($_GET['lvl'] > $msx) {
return FALSE;
}
if ($building['item_id'] == 40) {
return FALSE;
}
$qs = new QueueModel();
$num_queue = $qs->provider->fetchScalar( "select COUNT(*) from p_queue where village_id='".$this->data['selected_village_id']."' and proc_type='2' and proc_params='".$_GET['id']."'");
if(!$this->data['is_capital'] && $num_queue > 0 && ($_GET['id'] < 19) && $this->buildings[$_GET['id']]['level'] == 20) {
return FALSE;
}
if(!$this->data['is_capital'] && $num_queue == 1 && ($_GET['id'] < 19) && $this->buildings[$_GET['id']]['level'] == 19) {
return FALSE;
}
if(!$this->data['is_capital'] && $num_queue == 2 && ($_GET['id'] < 19) && $this->buildings[$_GET['id']]['level'] == 18) {
return FALSE;
}
if ($this->data['gold_num'] >= $gold) {
$qj = new QueueModel();
$qj->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num-".$gold." WHERE id = '".$this->player->playerId."'");
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '".$gold."', 'تطوير مباني');");
//end pgold
$dropLevels = $_GET['lvl'] - $building['level'];
while ( 0 < $dropLevels-- )
{
$mq = new QueueJobModel( );
$mq->upgradeBuilding( $this->data['selected_village_id'], $_GET['id'], $building['item_id']);
}
$_SESSION['uptime'] = time()+5;
$this->redirect ('build?id='.$_GET['id']);
}
}
}
}
}

if (isset ($_GET['max']) && $this->appConfig['system']['server_start'] < date('Y/m/d H:i:s')
&& !$this->data['is_special_village']
&& !$this->isGameTransientStopped () && !$this->isGameOver () ) {
if ( isset ($_GET['bid']) && is_numeric ($_GET['bid'])){

if ($_GET['bid'] < 1 OR $_GET['bid'] > 4) {return;}
$b_arr = explode( ',', $this->data['buildings'] );
$indx = 0;
$n = 0;
foreach( $b_arr as $b_str ) {
$indx++;
$b2 = explode (' ', $b_str);
$itm = $b2[0];
if ($_GET['bid'] == $itm){
$n++;
$ilvl = $b2[1];
$dropLevels = 20 - $ilvl;
$gold=0;
while ( 0 < $dropLevels-- )
{
$gold += 30;
}
}
}
$tst_gold = $gold*$n;
if ($this->data['gold_num'] >= $tst_gold) {
$b_arr = explode( ',', $this->data['buildings'] );
$indx = 0;
$n = 0;
foreach( $b_arr as $b_str ) {
$indx++;
$b2 = explode (' ', $b_str);
$itm = $b2[0];
if ($_GET['bid'] == $itm){
$n++;
$ilvl = $b2[1];
$dropLevels = 20 - $ilvl;
while ( 0 < $dropLevels-- )
{
$mq = new QueueJobModel( );
$mq->upgradeBuilding( $this->data['selected_village_id'], $indx, $itm);
}
}
}
$qj = new QueueModel();
$qj->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num-".$tst_gold." WHERE id = '".$this->player->playerId."'");
$d = date('Y/m/d H:i:s');
$qj->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$this->data['name']."', '".$d."', '".$tst_gold."', 'تطوير حقول');");
$this->redirect ('village1');
}
}
}


if ( isset ($_GET['id']) && is_numeric ($_GET['id'])
&& isset ($_GET['k'])
&& $_GET['k'] == $this->data['update_key']
&& !$this->isGameTransientStopped () && !$this->isGameOver () ) {
if (isset ($_GET['d'])) {
$this->queueModel->cancelTask ($this->player->playerId, intval ($_GET['id']));
} else if (isset ($this->buildings[$_GET['id']])) {
$buildProperties = $this->getBuildingProperties (intval ($_GET['id']));
if ( $buildProperties != NULL ) {
$canAddTask = FALSE;
if ($this->data['is_special_village'] == 1){
if ($_GET['id'] == 26 || $_GET['id'] == 33 || $_GET['id'] == 29 || $_GET['id'] == 30) {
return FALSE;
}
}
if ($this->player->playerId != 1) {
if (($this->appConfig['system']['server_start'] > date('Y/m/d H:i:s'))){
if ( isset ($_GET['k'])){
return FALSE;
}
}
}
if(!$this->data['is_capital'] && ($_GET['id'] < 19) && ($this->buildings[$_GET['id']]['level'] >= 20)){
return FALSE;
}
$qs = new QueueModel();
$num_queue = $qs->provider->fetchScalar( "select COUNT(*) from p_queue where village_id='".$this->data['selected_village_id']."' and proc_type='2' and proc_params='".intval($_GET['id'])."'");
if(!$this->data['is_capital'] && $num_queue > 0 && ($_GET['id'] < 19) && $this->buildings[$_GET['id']]['level'] == 20) {
return FALSE;
}
if(!$this->data['is_capital'] && $num_queue == 1 && ($_GET['id'] < 19) && $this->buildings[$_GET['id']]['level'] == 19) {
return FALSE;
}
if(!$this->data['is_capital'] && $num_queue == 2 && ($_GET['id'] < 19) && $this->buildings[$_GET['id']]['level'] == 18) {
return FALSE;
}
if ( $buildProperties['emptyPlace'] ) {// new building
$item_id = isset ($_GET['b']) ? intval ($_GET['b']) : 0;
$posIndex = intval ($_GET['id']);
if ( ($posIndex == 39 && $item_id != 16)
|| ($posIndex == 40 && $item_id != 31 && $item_id != 32 && $item_id != 33) ) {
return;
}
if ($this->data['is_special_village']
&& ($posIndex == 25 || $posIndex == 26 || $posIndex == 29 || $posIndex == 30 || $posIndex == 33)
&& $item_id != 40 ) {
return;
}
if ($this->canCreateNewBuild ($item_id) == 1) {
$canAddTask = TRUE;
$neededResources = $this->gameMetadata['items'][$item_id]['levels'][0]['resources'];
$calcConsume= intval (($this->gameMetadata['items'][$item_id]['levels'][0]['time_consume']/$GLOBALS['AppConfig']['Game']['speed_b']) * ($this->data['time_consume_percent']/100));
}
} else {
$canAddTask = TRUE;
$item_id = $buildProperties['building']['item_id'];
$neededResources = $buildProperties['level']['resources'];
$calcConsume= $buildProperties['level']['calc_consume'];
}
if ( $canAddTask
&& $this->needMoreUpgrades ($neededResources, $item_id) == 0
&& $this->isResourcesAvailable ($neededResources) ) {
$workerResult = $this->isWorkerBusy ($item_id<=4);
if ( !$workerResult['isBusy'] ) {
$newTask = new QueueTask (QS_BUILD_CREATEUPGRADE, $this->player->playerId, $calcConsume);
$newTask->villageId = $this->data['selected_village_id'];
$newTask->buildingId= $item_id;
$newTask->procParams = $item_id==40? 25 : intval ($_GET['id']);
$newTask->tag = $neededResources;
$this->queueModel->addTask ($newTask);
}
}
}
}
}
}
}
class GameLicenseModel extends ModelBase {
function getLicense() {
return $this->provider->fetchScalar('SELECT gs.license_key FROM g_settings gs');
}
function setLicense( $licenseKey ) {
$this->provider->executeQuery('UPDATE g_settings gs SET gs.license_key=\'%s\'', array( $licenseKey ) );
}
}
function TimeAgo($diff_in_unix){
$diff = "";
if ($diff_in_unix > 3600){
$diff .= intval($diff_in_unix/3600);
$diff_in_unix = $diff_in_unix%3600;
}else{ $diff .= '0'; }
if($diff_in_unix > 60 AND $diff_in_unix < 3600){
$diff .= ":".intval($diff_in_unix / 60);
$diff_in_unix = $diff_in_unix%60;
}else{ $diff .= ':00'; }
if ($diff_in_unix < 60 AND $diff_in_unix > 0){
$diff .= ":".$diff_in_unix;
}
return $diff;
}
class GameLicense {
function isValid( $domain ) {
$m = new GameLicenseModel();
$licenseKey = $m->getLicense( $domain );
$m->dispose();
return ( $licenseKey == GameLicense::_getKeyFor( $domain ) );
}
function set( $domain ) {
$m = new GameLicenseModel();
$m->setLicense( GameLicense::_getKeyFor( $domain ) );
$m->dispose();
}
function clear() {
GameLicense::set('');
}
function _getKeyFor( $domain ) {
return md5 ( 'SPSLINK TATARWAR' . strrev ( $domain ) . 'SPSLINK TATARWAR' );
}
}
?>