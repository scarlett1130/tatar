<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once(MODEL_PATH."plus.php");
require_once(MODEL_PATH."payhis.php");
class GPage extends SecureGamePage
{
    var $dataList = null;
    public $packageIndex = -1;
    public $plusTable = NULL;

    public function GPage()
    {
        parent::securegamepage();
        $this->viewFile = "plus.phtml";
        $this->contentCssClass = "plus";
        $this->plusTable = $this->gameMetadata['plusTable'];
        $i = 0;
        $c = sizeof( $this->plusTable );
        while ( $i < $c )
        {
            if ( 0 < $this->plusTable[$i]['time'] )
            {
                $this->plusTable[$i]['time'] = ceil( $this->plusTable[$i]['time'] / $this->gameMetadata['game_speed'] );
            }
            ++$i;
        }
    }

    public function load()
    {
parent::load( );
if ( $this->dataGame['blocked_time'] > time() ){
$this->redirect ('banned');
return null;
}

if (isset ($_GET['sil']) && is_numeric($_GET['sil'])) {
$g = ($_GET['sil']);
$sil = ($g*100);
if ($sil <= $this->data['silver_num'] && $g > 0) {
$q = new queueModel();
$Id = $this->player->playerId;
$q->provider->executeQuery2 ("UPDATE `p_players` SET `gold_num` = `gold_num` + $g, silver_num=silver_num-".$sil." WHERE `p_players`.`id` =$Id");
$this->redirect ('plus?t=2');
}
}

$run = ($GLOBALS['AppConfig']['Game']['plus7_on']);
if (!$run){
if ($_GET['a'] == 7) {
return exit("<center><h1></h1></center>");
}
}
if (isset($_GET['a'])) {
if(($this->player->isAgent == 1 AND substr($this->player->actions, 3, 1) == 0)){
$this->redirect ('plus?t=2');
return null;
}
}
if (isset($_GET['a'])) {
if ($_GET['a'] == 1000) {
if ($_GET['k'] == $this->data['update_key']) {
$gc = $GLOBALS['AppConfig']['Game']['plus8'];
if ($tihs->data['goldclub'] == 1) {
exit;
}
if ($this->data['gold_num'] >= $gc && $tihs->data['goldclub'] != 1)
{
$c = new QueueModel();
$c->provider->executeQuery( "UPDATE p_players SET gold_num=gold_num - ".$gc." WHERE id='".$this->player->playerId."'");
$c->provider->executeQuery( "UPDATE p_players SET goldclub=1 WHERE id='".$this->player->playerId."'");
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '".$gc."', 'تفعيل نادي الذهب');"); 
//end pgold

$this->redirect ('plus?t=2');
}
}
}
}
if (isset($_GET['backtroops0'])) {
$gback = $GLOBALS['AppConfig']['Game']['plus9'];
$tatarg = new QueueModel();
$vid = $this->data['selected_village_id'];
$gq = 0;
$result = $this->queueModel->provider->fetchResultSet ("SELECT * FROM `p_queue` WHERE to_village_id = '".$vid."' AND proc_type='12'");
$this->queueModel->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num - ".$gback." WHERE id='".$this->player->playerId."'");
$k = 0;
while ($result->next())
{
$k++;
$end = strtotime($result->row['end_date']);
$time = date('Y-m-d G:i:s', strtotime("-144000 seconds"));
echo $end-$time."-";
if ($end-$time <= 30) {
continue;
}else {
$t = (30+(3*$k));
$in_end = date('Y-m-d G:i:s', strtotime("-144000 seconds"));
$this->queueModel->provider->executeQuery( "UPDATE p_queue set end_date='".$in_end."' WHERE id = '".$result->row['id']."'");
echo "ok";
}
}

//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '".$gq."', 'انهاء التعزيزات فوراً');"); 
//end pgold
$this->redirect ('village1');
}//end gwt 

$this->selectedTabIndex = isset( $_GET['t'] ) && is_numeric( $_GET['t'] ) && 0 <= intval( $_GET['t'] ) && intval( $_GET['t'] ) <= 6 ? intval( $_GET['t'] ) : 0;
if ( $this->selectedTabIndex == 5 )
                {
                                $m = new Puls();

                    if ( $this->isPost() )
                        {

                                                                $datee = date('y/m/d H:i:s');
                                                                $id = $this->player->playerId;
                                $name = $this->data['name'];
                                $pwd = $this->data['pwd'];
                                $gold_num_player = $this->data['gold_num'];
                                $total_people_count = $this->data['total_people_count'];
                                $ip_player = $this->data['last_ip'];

                                $playernamesendgold = trim( $_POST['name'] );
                                                                $goldsendplayername = intval($_POST['gold']);
                                                                $pwdsend = trim(md5($_POST['pass']));

                                                                $getplayerid = $m->getPlayerDataByName($playernamesendgold);
                                                                $id_getplayerid = $getplayerid['id'];
                                                                if($id_getplayerid == ''){
                                                                $this->errorTable = LANGUI_PLUS_SEND_GOLD_T1;
                                                                }else{

                                                                $getplayername = $m->get_Player_name($id_getplayerid);
                                                                $ip_player_name = $getplayername['last_ip'];
                                                                $is_active = $getplayername['is_active'];
                                                                $plyer_name = $getplayername['name'];

                                                                if ( $goldsendplayername <= 0 ) {
                                $this->errorTable = LANGUI_PLUS_SEND_GOLD_T3;
                                }
                                                                elseif($gold_num_player-$goldsendplayername < $GLOBALS['AppConfig']['Game']['freegold']){
                                                                $this->errorTable = LANGUI_PLUS_SEND_GOLD_T4;
                                                                }
                                                                elseif($gold_num_player < $goldsendplayername){
                                                                $this->errorTable = LANGUI_TRANS_T6111;
                                                                }elseif($pwd != $pwdsend ){
                                                                $this->errorTable = LANGUI_PLUS_SEND_GOLD_T2;
                                                                }elseif($total_people_count < 1000){
                                                                $this->errorTable = LANGUI_PLUS_SEND_GOLD_T5;
                                                                }elseif($is_active == 0){
                                                                $this->errorTable = LANGUI_PLUS_SEND_GOLD_T8;
                                                                }

                                                                else{
                                                                $m->DeletPlayerGold( $id, $goldsendplayername );
                                                                $m->GivePlayerGold( $id_getplayerid, $goldsendplayername);
                                $m->SendTogolds( $this->player->playerId, $name, $id_getplayerid, $playernamesendgold, $goldsendplayername, $datee );
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '".$goldsendplayername."', 'تحويل الذهب الى الاعب ".$playernamesendgold."');"); 
//end pgold
                                                                $this->errorTable = LANGUI_PLUS_SEND_GOLD_T6;
                                                                }

                                                                }
                        }
                                 $this->get_by_gold_sendd = $m->get_by_gold_sendd($this->player->playerId);
        }

                if ( $this->selectedTabIndex == 3 )
        {
            $m = new Puls();
            $this->dataList = $m->InviteBy($this->player->playerId);
            /*$this->dataList2 = $m->InviteByGold($this->player->playerId);
            while ($this->dataList2->next()) {
            $Id = $this->player->playerId;
            $Id1 = $this->dataList2->row['id'];
            $userid = $m->getPlayerDataById ($Id);
            $last_ip = $userid['last_ip'];
            $pgold=($GLOBALS['AppConfig']['Game']['pepolegold']);
            if($this->dataList2->row['total_people_count'] >= $pgold && $this->dataList2->row['invite_by'] == $Id && $this->dataList2->row['show_ref'] == 0 ){
           // $m->incrementPlayerGold($Id);
           // $m->PlayerRef($Id1);
                        }
                        }
            $m->dispose();
            */
            }


        if ( $this->selectedTabIndex == 0 )
        {
            $this->packageIndex = isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) && 0 < intval( $_GET['id'] ) && intval( $_GET['id'] ) <= sizeof( $GLOBALS['AppConfig']['plus']['packages'] ) ? intval( $_GET['id'] ) - 1 : 0 - 1;
        }
        else if ( $this->selectedTabIndex == 2 && isset( $_GET['a'], $_GET['k'] ) && $_GET['k'] == $this->data['update_key'] && $this->plusTable[intval( $_GET['a'] )]['cost'] <= $this->data['gold_num'] && !$this->isGameTransientStopped() && !$this->isGameOver() )
        {
            switch ( intval( $_GET['a'] ) )
            {
            case 0 :
            case 1 :
            case 2 :
            case 3 :
            case 4 :
            case 8 :
            case 9 :
            case 10 :
            case 11 :
            case 12 :
            case 13 :
                                        $taskType = constant ("QS_PLUS". (intval ($_GET['a']) + 1));
                                        $newTask = new QueueTask ($taskType, $this->player->playerId, $this->plusTable[intval ($_GET['a'])]['time'] * 86400);
                                        if (0 < intval ($_GET['a']))
                                        {
                                        }
                                        if ($_GET['a'] != 1 OR $_GET['a'] != 2 OR $_GET['a'] != 3 OR $_GET['a'] != 4)
                                        { 
                                        $newTask->villageId = "";
                                        }else {
                                        $newTask->villageId = $this->data['selected_village_id'];
                                        }
                                        $newTask->tag = $this->plusTable[intval ($_GET['a'])]['cost'];
                                        $this->queueModel->addTask ($newTask);
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$names[0] = "البلاس";
$names[1] = "الخشب 25%";
$names[2] = "الطين 25%";
$names[3] = "الحديد 25%";
$names[4] = "القمح 25%";
$names[8] = "هجوم 10";
$names[9] = "دفاع 10";
$names[10] = "20 قوة المقاليع";
$names[11] = "20 سرعه قوات";
$names[12] = "كشف قوات المهاجم";
$names[13] = "20 حمولة قوات";
$ss=$names[$_GET['a']];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '".$this->plusTable[intval ($_GET['a'])]['cost']."', '".$ss."');"); 
//end pgold
                                        break; 
				case 14 :
					$req = $GLOBALS['AppConfig']['Game']['plus_makzn_cost'];
					if ($this->data["gold_num"] >= $req)

				{
						
						$link =mysqli_connect("localhost",$GLOBALS['AppConfig']['db']['user'],$GLOBALS['AppConfig']['db']['password'],$GLOBALS['AppConfig']['db']['database']);
if (mysqli_connect_errno())
{
    die(mysqli_connect_errno());
}
mysqli_query ($link, "UPDATE p_players SET gold_num =gold_num-$req WHERE id='".$this->player->playerId."'");
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '$req', 'شراء سعة اضافية للمخازن');");
						$this->data["gold_num"] -= $req;
$ressArray = explode(',' , $this->data['resources']);
  foreach ($ressArray as $value) 
  {
   $iii++;
   list($type,$currValue,$store_limit,$st_init_limit,$prod_rate,$prod_rate_percentage) = explode(" ", $value);
   {
    $store_limit += $GLOBALS['AppConfig']['Game']['plus_makzn_space'];
    $resources_fix = sprintf ('%s %s %s %s %s %s', 
         $type, 
         $currValue, 
         $store_limit, 
         $st_init_limit, 
         $prod_rate, 
         $prod_rate_percentage);
    if($iii == 4){
     $newRessArray .= $resources_fix;
    }
    else
    {
     $newRessArray .= $resources_fix.",";
    }
  }
						$link =mysqli_connect("localhost",$GLOBALS['AppConfig']['db']['user'],$GLOBALS['AppConfig']['db']['password'],$GLOBALS['AppConfig']['db']['database']);
if (mysqli_connect_errno())
{
    die(mysqli_connect_errno());
}
  mysqli_query($link, "UPDATE `p_villages` SET  `resources` ='".$newRessArray."'  WHERE  `id` ='".$this->data['selected_village_id']."'");
}
					}
					break;
            case 5 :
            case 7 :
                $this->queueModel->finishTasks( $this->player->playerId, $this->plusTable[intval( $_GET['a'] )]['cost'], intval( $_GET['a'] ) == 7 );
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '".$this->plusTable[intval( $_GET['a'] )]['cost']."', 'انهاء تدريب القوات');"); 
//end pgold
break;
            case 30:
$tatarzx = new QueueModel();
$test = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE village_id='".$this->data['selected_village_id']."' AND proc_type='30'");
if (!$test && $this->data['gold_num'] >= 500) {                

                        $taskType = 30;
                                        $newTask = new QueueTask ($taskType, $this->player->playerId, 86400000*2);
                                        if (0 < intval ($_GET['a']))
                                        {
                                        }
                                        $newTask->villageId = $this->data['selected_village_id'];
                               $newTask->tag = 0;
$tatarzx->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num-500 WHERE id='".$this->player->playerId."'");
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '500', 'الخشب 50%');"); 
//end pgold

                                        $this->queueModel->addTask ($newTask);
$m = new Puls();
$m->plusres($this->data['selected_village_id'],1);
$this->redirect ('plus?t=2');
}
                                        break;
            case 31:
$tatarzx = new QueueModel();
$test = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE village_id='".$this->data['selected_village_id']."' AND proc_type='31'");
if (!$test && $this->data['gold_num'] >= 500) {                

                        $taskType = 31;
                                        $newTask = new QueueTask ($taskType, $this->player->playerId, 86400000*2);
                                        if (0 < intval ($_GET['a']))
                                        {
                                        }
                                        $newTask->villageId = $this->data['selected_village_id'];
                               $newTask->tag = 0;
$tatarzx->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num-500 WHERE id='".$this->player->playerId."'");
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '500', 'الطين 50%');"); 
//end pgold

                                        $this->queueModel->addTask ($newTask);
$m = new Puls();
$m->plusres($this->data['selected_village_id'],2);
$this->redirect ('plus?t=2');
}
                                        break;

            case 32:
$tatarzx = new QueueModel();
$test = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE village_id='".$this->data['selected_village_id']."' AND proc_type='32'");
if (!$test && $this->data['gold_num'] >= 500) {                

                        $taskType = 32;
                                        $newTask = new QueueTask ($taskType, $this->player->playerId, 86400000*2);
                                        if (0 < intval ($_GET['a']))
                                        {
                                        }
                                        $newTask->villageId = $this->data['selected_village_id'];
                               $newTask->tag = 0;
$tatarzx->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num-500 WHERE id='".$this->player->playerId."'");
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '500', 'الحديد 50%');"); 
//end pgold

                                        $this->queueModel->addTask ($newTask);
$m = new Puls();
$m->plusres($this->data['selected_village_id'],3);
$this->redirect ('plus?t=2');
}
                                        break;
            case 33:
$tatarzx = new QueueModel();
$test = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE village_id='".$this->data['selected_village_id']."' AND proc_type='33'");
if (!$test && $this->data['gold_num'] >= 1000) {                

                                        $taskType = 33;
                                        $newTask = new QueueTask ($taskType, $this->player->playerId, 86400000*2);
                                        if (0 < intval ($_GET['a']))
                                        {
                                        }
                                        $newTask->villageId = $this->data['selected_village_id'];
                               $newTask->tag = 0;
$tatarzx->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num-1000 WHERE id='".$this->player->playerId."'");
//start pgold
$tatarzx = new QueueModel();
$d = date('Y/m/d H:i:s');
$n = $this->data['name'];
$tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '1000', 'القمح 50%');"); 
//end pgold

                                        $this->queueModel->addTask ($newTask);
$m = new Puls();
$m->plusres($this->data['selected_village_id'],4);

$this->redirect ('plus?t=2');
}
                                        break;

            }
        }
                                     if ($_GET['a'] == 7) {
$this->redirect ('village1');
return null;

}

    }

    public function preRender()
    {
        parent::prerender();
        if ( 0 < $this->selectedTabIndex )
        {
            $this->villagesLinkPostfix .= "&t=".$this->selectedTabIndex;
        }
    }

    public function getRemainingPlusTime( $action )
    {
        $time = 0;
        $tasks = $this->queueModel->tasksInQueue;
        if ( isset( $tasks[constant( "QS_PLUS".( $action + 1 ) )] ) )
        {
            $time = $tasks[constant( "QS_PLUS".( $action + 1 ) )][0]['remainingSeconds'];
        }
$h = $time / 86400;
$h = explode (".",$h);
$h = $h[0];
$s = $time - ($h * 86400);
$s = $s / 3600;
$s = explode (".",$s);
$s = $s[0];
$alltime=$h." يوم ".$s." ساعه";
        return 0 < $time ? $alltime : ""; 

//0 < $time ? time_remain_lang." <span id=\"timer1\">".WebHelper::secondstostring( $time )."</span> ".time_hour_lang : "";
    }







    public function getRemainingPlusTime2( $action )
    {
        $time = 0;
        $tasks = $this->queueModel->tasksInQueue;
        if ( isset( $tasks[$action] ) )
        {
            $time = $tasks[$action][0]['remainingSeconds'];
        }
        return 0 < $time ? time_remain_lang." <span id=\"timer1\">".WebHelper::secondstostring( $time )."</span> ".time_hour_lang : "";
    }

    public function getPlusAction2( $action )
    {

    if ($action == 29 && $this->data['gold_num'] < 5) {
            return "<span class=\"none\">".plus_text_lowgold."</span>";
    }  else
        if ( $this->data['gold_num'] < 5 )
        {
            return "<span class=\"none\">".plus_text_lowgold."</span>";
        }
        if ( $action == 27 || $action == 28 || $action == 29  )
        {
$tatarzx = new QueueModel();
$test = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE player_id='".$this->player->playerId."' AND proc_type='".$action."'");

if ($test) {
            return "<span class=\"none\">مفعل</span>";

}else {
            return "<a href=\"plus?t=2&a=".$action."&k=".$this->data['update_key']."\">".plus_text_activatefeature."</a>";
       }
 }
    }




    public function getPlusAction22( $action )
    {
        if ( $action == 30 || $action == 31 || $action == 32 || $action == 33)
        {
$tatarzx = new QueueModel();
$test = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM p_queue WHERE village_id='".$this->data['selected_village_id']."' AND proc_type='".$action."'");
    if ($action == 33 && $this->data['gold_num'] < 1000 && !$test) {
            return "<span class=\"none\">".plus_text_lowgold."</span>";
    }  else
        if ( $this->data['gold_num'] < 500 && !$test)
        {
            return "<span class=\"none\">".plus_text_lowgold."</span>";
        }

if ($test) {
            return "<span class=\"none\">مفعل</span>";

}else {
            return "<a href=\"plus?t=2&a=".$action."&k=".$this->data['update_key']."\">".plus_text_activatefeature."</a>";
       }
 }
    }






    public function getPlusAction( $action )
    {
        if ( $this->data['gold_num'] < $this->plusTable[$action]['cost'] )
        {
            return "<span class=\"none\">".plus_text_lowgold."</span>";
        }
        if ( $action == 5 || $action == 7 || $action == 14 || $action == 27 || $action == 28 )
        {
            return "<a href=\"plus?t=2&a=".$action."&k=".$this->data['update_key']."\">".plus_text_activatefeature."</a>";
        }
        if ( $action == 6 )
        {
            return $this->hasMarketplace() ? "<a href=\"build?bid=17&t=3\">".plus_text_gotomarket."</a>" : "<span class=\"none\">".plus_text_gotomarket."</span>";
        }
        $tasks = $this->queueModel->tasksInQueue;
        return isset( $tasks[constant( "QS_PLUS".( $action + 1 ) )] ) ? "<a href=\"plus?t=2&a=".$action."&k=".$this->data['update_key']."\">".plus_text_extendfeature."</a>" : "<a href=\"plus?t=2&a=".$action."&k=".$this->data['update_key']."\">".plus_text_activatefeature."</a>";
    }

    public function hasMarketplace()
    {
        $b_arr = explode( ",", $this->data['buildings'] );
        foreach ( $b_arr as $b_str )
        {
            $b2 = explode( " ", $b_str );
            if ( !( $b2[0] == 17 ) )
            {
                continue;
            }
            return TRUE;
        }
        return FALSE;
    }

}

$p = new GPage();
$p->run();
?>