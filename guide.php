<?php
require '.' . DIRECTORY_SEPARATOR . 'ftd-core' . DIRECTORY_SEPARATOR . 'boot.php';
require_once MODEL_PATH . 'guide.php';
class GPage extends VillagePage
{
var $taskNumber = 0;
var $taskState = 0;
var $quiz = null;
var $clientAction = null;
var $guideData = array ();
function GPage ()
{
$this->customLogoutAction = TRUE;
parent::villagepage ();
if ($this->player == NULL)
{
exit (0);

}

$this->layoutViewFile = NULL;

$this->viewFile = 'guide.phtml';

$this->checkForGlobalMessage = FALSE;

}

function load ()

{

parent::load ();

$this->quiz = trim ($this->data['guide_quiz']);

if ($this->quiz == GUIDE_QUIZ_COMPLETED)

{

exit (0);

return null;

}

$m = new GuideModel ();

$this->taskState = 0;

if (($this->quiz == GUIDE_QUIZ_NOTSTARTED OR $this->quiz == GUIDE_QUIZ_SUSPENDED))

{

$this->clientAction = 0 - 1;

if ((isset ($_GET['v']) AND $_GET['v'] == 'f'))

{

if ($this->quiz == GUIDE_QUIZ_NOTSTARTED)

{

$m->setGuideTask ($this->player->playerId, GUIDE_QUIZ_SUSPENDED);

}

$m->dispose ();

exit (0);

return null;

}

if ((isset ($_GET['v']) AND $_GET['v'] == 'n'))

{

$this->quiz = '1,200';

$this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, $this->quiz);

}

else

{

$this->taskNumber = ((isset ($_GET['v']) AND $_GET['v'] == 's') ? 1 : 0);

if ($this->taskNumber == 1)

{

$this->clientAction = 0;

$this->quiz = '0,1';

$m->setGuideTask ($this->player->playerId, $this->quiz);

}

return null;

}

}

$quizArray = explode (',', $this->quiz);

$this->taskNumber = $quizArray[1];

if ($this->taskNumber == 200)

{

if (isset ($_GET['v']))

{

if ($_GET['v'] == 'y')

{

$this->taskNumber = 0;

$this->clientAction = 1;

$this->quiz = GUIDE_QUIZ_NOTSTARTED;

$m->setGuideTask ($this->player->playerId, $this->quiz);

return null;

}

if ($_GET['v'] == 'c')

{

$this->quiz = '0,201,0';

$quizArray = explode (',', $this->quiz);

$this->taskNumber = $quizArray[1];

$m->setGuideTask ($this->player->playerId, $this->quiz);

}

}

}

if ($this->taskNumber == 201)

{

$this->handleNoQuiz ($m, $quizArray[2]);

}

else

{

if ($quizArray[0] == 1)

{

$quizArray[0] = 0;

$this->clientAction = '';

$m->setGuideTask ($this->player->playerId, implode (',', $quizArray));

$this->newReadQuiz ($this->taskNumber, $m, $quizArray);

}

else

{

$this->checkForQuiz ($this->taskNumber, $m, $quizArray);

}

}

$m->dispose ();

}

function handleNoQuiz ($m, $quizStep)

{

$time = floor (180);

$this->guideData['quiztime'] = WebHelper::secondstostring ($time);

$result = 0;

switch ($quizStep)

{

case 0:

{

if ((isset ($_GET['v']) AND trim ($_GET['v']) == 'y'))

{

$result = 1;

$newTask = new QueueTask (QS_PLUS1, $this->player->playerId, 86400);

$newTask->villageId = '';

$newTask->tag = 0;

$this->queueModel->addTask ($newTask);

$m->increaseGoldNumber ($this->player->playerId, 15);

$newTask = new QueueTask (QS_GUIDENOQUIZ, $this->player->playerId, $time);

$this->queueModel->addTask ($newTask);

}

break;

}

case 1:

{

}

case 2:

{

}

case 3:

{

}

case 4:

{

}

case 5:

{

if (((!isset ($this->queueModel->tasksInQueue[QS_GUIDENOQUIZ]) AND isset ($_GET['v'])) AND trim ($_GET['v']) == 'y'))

{

$result = 1;

$m->addResourcesTo ($this->data['selected_village_id'], array (217, 247, 177, 207));

$newTask = new QueueTask (QS_GUIDENOQUIZ, $this->player->playerId, $time);

$this->queueModel->addTask ($newTask);

}

break;

}

case 6:

{

if (((!isset ($this->queueModel->tasksInQueue[QS_GUIDENOQUIZ]) AND isset ($_GET['v'])) AND trim ($_GET['v']) == 'y'))

{

$this->clientAction = 100;

$this->quiz = GUIDE_QUIZ_COMPLETED;

$m->setGuideTask ($this->player->playerId, $this->quiz);

$newTask = new QueueTask (QS_PLUS1, $this->player->playerId, 172800);

$newTask->villageId = '';

$newTask->tag = 0;

$this->queueModel->addTask ($newTask);

$m->increaseGoldNumber ($this->player->playerId, 20);

}

}

}

if (($result == 1 AND $quizStep < 6))

{

++$quizStep;

$this->quiz = '0,201,' . $quizStep;

$m->setGuideTask ($this->player->playerId, $this->quiz);

}

$this->guideData['quizStep'] = $quizStep;

$this->guideData['pended'] = isset ($this->queueModel->tasksInQueue[QS_GUIDENOQUIZ]);

if ($this->guideData['pended'])

{

$this->guideData['remainingSeconds'] = $this->queueModel->tasksInQueue[QS_GUIDENOQUIZ][0]['remainingSeconds'];

}

}

function newReadQuiz ($quizNumber, $m, $quizArray)

{

switch ($quizNumber)

{

case 6:

{

$this->clientAction = 2;

require_once MODEL_PATH . 'msg.php';

$mm = new MessageModel ();

$messageId = $mm->sendMessage (1, $this->appConfig['system']['adminName'], $this->player->playerId, $this->data['name'], guide_task_msg_subject, guide_task_msg_body);

$quizArray[] = $messageId;

$m->setGuideTask ($this->player->playerId, implode (',', $quizArray));

break;

}

case 7:

{

$map_size = $this->setupMetadata['map_size'];

$_x = $this->data['rel_x'];

$_y = $this->data['rel_y'];

$mapMatrix = implode (',', $this->__getVillageMatrix ($map_size, $_x, $_y, 3));

$reader = $m->getVillagesMatrix ($mapMatrix);

$availableVillages = array ();

while ($reader->next ())

{

if (((!$reader->row['is_oasis'] AND 0 < intval ($reader->row['player_id'])) AND intval ($reader->row['player_id']) != $this->player->playerId))

{

$availableVillages[] = array ($reader->row['rel_x'], $reader->row['rel_y'], $reader->row['village_name']);

continue;

}

}

if (sizeof ($availableVillages) == 0)

{

$availableVillages[] = array ($this->data['rel_x'], $this->data['rel_y'], $this->data['village_name']);

}

$r_indx = mt_rand (0, sizeof ($availableVillages) - 1);

$this->guideData['vname'] = $availableVillages[$r_indx][2];

$quizArray[] = implode ('|', $availableVillages[$r_indx]);

$m->setGuideTask ($this->player->playerId, implode (',', $quizArray));

break;

}

case 20:

{

}

case 21:

{

$this->taskState = ($quizArray[sizeof ($quizArray) - 1] == 1 ? 2 : 0);

$this->guideData['troop_id'] = $this->getFirstTroopId ($this->data['tribe_id']);

$this->guideData['troop_name'] = constant ('troop_' . $this->guideData['troop_id']);

}

}

}

function checkForQuiz ($quizNumber, $m, $quizArray)

{

switch ($quizNumber)

{

case 1:

{

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 1 AND 0 < $build['level']))

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,2');

break;

continue;

}

}

break;

}

case 2:

{

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 4 AND 0 < $build['level']))

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,3');

$newTask = new QueueTask (QS_PLUS1, $this->player->playerId, 86400);

$newTask->villageId = '';

$newTask->tag = 0;

$this->queueModel->addTask ($newTask);

break;

continue;

}

}

break;

}

case 3:

{

if ($this->data['village_name'] != new_village_name_prefix . ' ' . $this->data['name'])

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,4');

$m->addResourcesTo ($this->data['selected_village_id'], array (30, 60, 30, 20));

}

break;

}

case 4:

{

if (isset ($_GET['v']))

{

$num = trim ($_GET['v']);

if (!is_numeric ($num))

{

$this->taskState = 1;

}

else

{

$playerRank = $m->getPlayerRank ($this->player->playerId, $this->data['total_people_count'] * 10 + $this->data['villages_count']);

if ($num == $playerRank)

{

$this->taskState = 4;

}

else

{

$this->taskState = ($num < $playerRank ? 2 : 3);

}

$this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,5');

$m->addResourcesTo ($this->data['selected_village_id'], array (40, 30, 20, 30));

}

}

break;

}

case 5:

{

$count = 0;

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 2 AND 0 < $build['level']))

{

$count |= 1;

continue;

}

else

{

if (($build['item_id'] == 3 AND 0 < $build['level']))

{

$count |= 2;

continue;

}

continue;

}

}

if ((0 < ($count & 1) AND 0 < ($count & 2)))

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,6');

$m->addResourcesTo ($this->data['selected_village_id'], array (50, 60, 30, 30));

}

break;

}

case 6:

{

if ($m->isOpenedMessage ($quizArray[2]))

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,7');

$m->increaseGoldNumber ($this->player->playerId, 20);

}

break;

}

case 7:

{

list ($x, $y, $vname) = explode ('|', $quizArray[sizeof ($quizArray) - 1]);
$abodR = new QueueModel();
$r_row = $abodR->provider->fetchRow("SELECT * FROM p_villages where rel_x='".$x."' and rel_y='".$y."' and ISNULL(player_id)");
if (!$r_row) {
$x = 0;
$y = 0;
$vname = "????????????";
}
$this->guideData['vname'] = $vname;

if (isset ($_GET['v']))

{

$arr = explode ('|', trim ($_GET['v']));

if (((sizeof ($arr) < 2 OR $x != $arr[0]) OR $y != $arr[1]))

{

$this->taskState = 1;

return null;

}

$this->clientAction = 1;

$this->taskState = 2;

$m->setGuideTask ($this->player->playerId, '1,8');

$m->addResourcesTo ($this->data['selected_village_id'], array (60, 30, 40, 90));

}

break;

}

case 8:

{

if ((isset ($_GET['v']) AND trim ($_GET['v']) == 'send'))

{

if ($this->resources[4]['current_value'] < 200)

{

$this->taskState = 1;

return null;

}

$this->clientAction = 1;

$this->taskState = 2;

$qid = $this->sendReinforcements ();

$m->setGuideTask ($this->player->playerId, '1,9,' . $qid);

$m->addResourcesTo ($this->data['selected_village_id'], array (0, 0, 0, 0 - 200));

}

break;

}

case 9:

{

$count = 0;

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 1 AND 0 < $build['level']))

{

$count |= 1;

continue;

}

else

{

if (($build['item_id'] == 2 AND 0 < $build['level']))

{

$count |= 2;

continue;

}

else

{

if (($build['item_id'] == 3 AND 0 < $build['level']))

{

$count |= 4;

continue;

}

else

{

if (($build['item_id'] == 4 AND 0 < $build['level']))

{

$count |= 8;

continue;

}

continue;

}

continue;

}

continue;

}

}

if ((((0 < ($count & 1) AND 0 < ($count & 2)) AND 0 < ($count & 4)) AND 0 < ($count & 8)))

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,10,' . $quizArray[sizeof ($quizArray) - 1]);

$m->addResourcesTo ($this->data['selected_village_id'], array (100, 80, 40, 40));

}

break;

}

case 10:

{

$qid = $quizArray[sizeof ($quizArray) - 1];

if ($m->guideTroopsReached ($qid))

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,11');

$newTask = new QueueTask (QS_PLUS1, $this->player->playerId, 2 * 86400);

$newTask->villageId = '';

$newTask->tag = 0;

$this->queueModel->addTask ($newTask);

}

break;

}

case 11:

{

if ($m->isOpenedReport ($this->player->playerId))

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,12');

$m->addResourcesTo ($this->data['selected_village_id'], array (75, 140, 40, 230));

}

break;

}

case 12:

{

$result = 1;

foreach ($this->buildings as $build)

{

if ((((($build['item_id'] == 1 AND $build['level'] < 1) OR ($build['item_id'] == 2 AND $build['level'] < 1)) OR ($build['item_id'] == 3 AND $build['level'] < 1)) OR ($build['item_id'] == 4 AND $build['level'] < 1)))

{

$result = 0;

break;

}

}

if ($result == 1)

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,13');

$m->addResourcesTo ($this->data['selected_village_id'], array (75, 80, 30, 50));

}

break;

}

case 13:

{

if ((0 < preg_match ('/\\[#0\\]/', $this->data['description1']) OR 0 < preg_match ('/\\[#0\\]/', $this->data['description2'])))

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,14');

$m->addResourcesTo ($this->data['selected_village_id'], array (120, 200, 140, 100));

}

break;

}

case 14:

{

$result = 0;

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 23 AND 0 < $build['level']))

{

$result = 1;

break;

continue;

}

}

if ($result == 1)

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,15');

$m->addResourcesTo ($this->data['selected_village_id'], array (150, 180, 30, 130));

}

break;

}

case 15:

{

$count = 0;

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 1 AND 1 < $build['level']))

{

$count |= 1;

continue;

}

else

{

if (($build['item_id'] == 2 AND 1 < $build['level']))

{

$count |= 2;

continue;

}

else

{

if (($build['item_id'] == 3 AND 1 < $build['level']))

{

$count |= 4;

continue;

}

else

{

if (($build['item_id'] == 4 AND 1 < $build['level']))

{

$count |= 8;

continue;

}

continue;

}

continue;

}

continue;

}

}

if ((((0 < ($count & 1) AND 0 < ($count & 2)) AND 0 < ($count & 4)) AND 0 < ($count & 8)))

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,16');

$m->addResourcesTo ($this->data['selected_village_id'], array (60, 50, 40, 30));

}

break;

}

case 16:

{

$this->guideData['wood'] = $this->gameMetadata['items'][19]['levels'][0]['resources'][1];

if ((isset ($_GET['v']) AND is_numeric ($_GET['v'])))

{

if (intval ($_GET['v']) == $this->guideData['wood'])

{

$this->taskState = 3;

$this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,17');

$m->addResourcesTo ($this->data['selected_village_id'], array (50, 30, 60, 20));

}

else

{

if ($this->guideData['wood'] < intval ($_GET['v']))

{

$this->taskState = 1;

}

else

{

$this->taskState = 2;

}

}

}

break;

}

case 17:

{

$result = 0;

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 15 AND 2 < $build['level']))

{

$result = 1;

break;

continue;

}

}

if ($result == 1)

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,18');

$m->addResourcesTo ($this->data['selected_village_id'], array (75, 75, 40, 40));

}

break;

}

case 18:

{

if (isset ($_GET['v']))

{

$num = trim ($_GET['v']);

if (!is_numeric ($num))

{

$this->taskState = 1;

}

else

{

$playerRank = $m->getPlayerRank ($this->player->playerId, $this->data['total_people_count'] * 10 + $this->data['villages_count']);

if ($num == $playerRank)

{

$this->taskState = 4;

$this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,19');

$m->addResourcesTo ($this->data['selected_village_id'], array (100, 90, 100, 60));

}

else

{

$this->taskState = ($num < $playerRank ? 2 : 3);

}

}

}

break;

}

case 19:

{

if (sizeof ($quizArray) == 2)

{

if (isset ($_GET['v']))

{

$num = trim ($_GET['v']);

if (is_numeric ($num))

{

$this->taskState = (intval ($num) == 1 ? 2 : 1);

$m->setGuideTask ($this->player->playerId, '0,19,' . $this->taskState);

}

}

}

else

{

$this->taskState = $quizArray[sizeof ($quizArray) - 1];

$result = 0;

if ($this->taskState == 1)

{

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 16 AND 0 < $build['level']))

{

$result = 1;

break;

continue;

}

}

}

else

{

if ($this->taskState == 2)

{

$result = 0;

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 11 AND 0 < $build['level']))

{

$result = 1;

break;

continue;

}

}

}

}

if ($result == 1)

{

$m->setGuideTask ($this->player->playerId, '1,20,' . $this->taskState);

$this->taskState = ($this->taskState == 1 ? 3 : 4);

$this->clientAction = 1;

$m->addResourcesTo ($this->data['selected_village_id'], array (80, 90, 60, 40));

}

}

break;

}

case 20:

{

$this->taskState = ($quizArray[sizeof ($quizArray) - 1] == 1 ? 2 : 0);

$result = 0;

if ($this->taskState == 2)

{

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 19 AND 0 < $build['level']))

{

$result = 1;

$m->addResourcesTo ($this->data['selected_village_id'], array (70, 100, 90, 100));

break;

continue;

}

}

}

else

{

if ($this->taskState == 0)

{

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 10 AND 0 < $build['level']))

{

$result = 1;

$m->addResourcesTo ($this->data['selected_village_id'], array (70, 120, 90, 50));

break;

continue;

}

}

}

}

if ($result == 1)

{

$m->setGuideTask ($this->player->playerId, '1,21,' . ($this->taskState == 0 ? 2 : 1));

$this->taskState = ($this->taskState == 0 ? 1 : 3);

$this->clientAction = 1;

}

break;

}

case 21:

{

$this->taskState = ($quizArray[sizeof ($quizArray) - 1] == 1 ? 2 : 0);

$this->guideData['troop_id'] = $this->getFirstTroopId ($this->data['tribe_id']);

$this->guideData['troop_name'] = constant ('troop_' . $this->guideData['troop_id']);

$result = 0;

if ($this->taskState == 2)

{

$troops = $this->_getOnlyMyTroops ();

if (2 <= $troops[$this->guideData['troop_id']])

{

$result = 1;

$m->addResourcesTo ($this->data['selected_village_id'], array (300, 320, 360, 570));

}

}

else

{

if ($this->taskState == 0)

{

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 17 AND 0 < $build['level']))

{

$result = 1;

$m->addResourcesTo ($this->data['selected_village_id'], array (200, 200, 700, 450));

break;

continue;

}

}

}

}

if ($result == 1)

{

$m->setGuideTask ($this->player->playerId, '1,22');

$this->taskState = ($this->taskState == 0 ? 1 : 3);

$this->clientAction = 1;

}

break;

}

case 22:

{

$result = 1;

foreach ($this->buildings as $build)

{

if ((((($build['item_id'] == 1 AND $build['level'] < 2) OR ($build['item_id'] == 2 AND $build['level'] < 2)) OR ($build['item_id'] == 3 AND $build['level'] < 2)) OR ($build['item_id'] == 4 AND $build['level'] < 2)))

{

$result = 0;

break;

}

}

if ($result == 1)

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,23');

$m->increaseGoldNumber ($this->player->playerId, 15);

}

break;

}

case 23:

{

$result = 0;

foreach ($this->buildings as $build)

{

if (($build['item_id'] == 18 AND 0 < $build['level']))

{

$result = 1;

break;

continue;

}

}

if ($result == 1)

{

$this->taskState = $this->clientAction = 1;

$m->setGuideTask ($this->player->playerId, '1,24');

$m->addResourcesTo ($this->data['selected_village_id'], array (100, 60, 90, 40));

}

break;

}

case 24:

{

if (0 < intval ($this->data['alliance_id']))

{

$this->taskState = 1;

$this->clientAction = 100;

$m->setGuideTask ($this->player->playerId, GUIDE_QUIZ_COMPLETED);

$m->addResourcesTo ($this->data['selected_village_id'], array (395, 315, 345, 230));

}

}

}

}

function preRender ()

{

header ('gquiz:' . $this->clientAction);

}

function _getOnlyMyTroops ()

{

$troops = array ();

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

if (isset ($troops[$t[0]]))

{

$troops += $t[0] = $t[1];

continue;

}

else

{

$troops[$t[0]] = $t[1];

continue;

}

}

continue;

}

}

return $troops;

}

function getFirstTroopId ($tribeId)

{

foreach ($this->gameMetadata['troops'] as $tid => $troop)

{

if ($troop['for_tribe_id'] == $tribeId)

{

return $tid;

}

}

return 0;

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

function __getVillageMatrix ($map_size, $x, $y, $scale)

{

$matrix = array ();

$i = 0 - $scale;

while ($i <= $scale)

{

$j = 0 - $scale;

while ($j <= $scale)

{

$nx = $this->__getCoordInRange ($map_size, $x + $i);

$ny = $this->__getCoordInRange ($map_size, $y + $j);

$matrix[] = $this->__getVillageId ($map_size, $nx, $ny);

++$j;

}

++$i;

}

return $matrix;

}

function sendReinforcements ()

{

$needed_time = floor (18000 / $this->gameMetadata['game_speed']);

$troopsStr = '31 1,32 0,33 0,34 0,35 0,36 0,37 0,38 0,39 0,40 0';

$catapultTargets = $carryResources = '';

$spyAction = 0;

$procParams = $troopsStr . '|0|' . $spyAction . '|' . $catapultTargets . '|' . $carryResources . '|||0';

$newTask = new QueueTask (QS_WAR_REINFORCE, 0, $needed_time);

$newTask->villageId = 0;

$newTask->toPlayerId = $this->player->playerId;

$newTask->toVillageId = $this->data['selected_village_id'];

$newTask->procParams = $procParams;

$newTask->tag = array ('troops' => NULL, 'hasHero' => FALSE, 'resources' => NULL);

return $this->queueModel->addTask ($newTask);

}

}

$p = new GPage ();

$p->run ();

?>

