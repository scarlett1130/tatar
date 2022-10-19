<?php

require '.' . DIRECTORY_SEPARATOR . 'ftd-core' . DIRECTORY_SEPARATOR . 'boot.php';
require_once MODEL_PATH . 'build.php';
require_once MODEL_PATH . 'village3.php';
class GPage extends VillagePage
{
    var $productionPane = TRUE;
    var $buildingView = '';
    var $buildingIndex = -1;
    var $buildProperties = NULL;
    var $newBuilds = NULL;
    var $troopsUpgrade = null;
    var $troopsUpgradeType = null;
    var $buildingTribeFactor = null;
    var $troops = array();
    var $selectedTabIndex = 0;
    var $villageOases = null;
    var $childVillages = null;
    var $hasHero = FALSE;
    var $totalCpRate = null;
    var $totalCpValue = null;
    var $neededCpValue = null;
    var $childVillagesCount = null;
    var $showBuildingForm = null;
    var $embassyProperty = null;
    var $merchantProperty = null;
    var $rallyPointProperty = null;
    var $crannyProperty = array('buildingCount' => 0, 'totalSize' => 0);
    var $warriorMessage = null;
    var $dataList = null;
    var $pageSize = 20;
    var $pageCount = null;
    var $pageIndex = null;
    function GPage()
    {
        parent::villagepage();
        $this->viewFile        = 'build.phtml';
        $this->contentCssClass = 'build';
    }
    function onLoadBuildings($building)
    {
        $GameMetadata = $GLOBALS['GameMetadata'];
        if (((($this->buildingIndex == 0 - 1 AND isset($_GET['bid'])) AND is_numeric($_GET['bid'])) AND $_GET['bid'] == $building['item_id'])) {
            $this->buildingIndex = $building['index'];
        }
        if (($building['item_id'] == 23 AND 0 < $building['level'])) {
            ++$this->crannyProperty['buildingCount'];
            $this->crannyProperty['totalSize'] += $GameMetadata['items'][$building['item_id']]['levels'][$building['level'] - 1]['value'] * $GameMetadata['items'][$building['item_id']]['for_tribe_id'][$this->tribeId];
        }
    }
    function load()
    {
        parent::load();
        if ($this->data['is_special_village'] == 1) {
            if ($_GET['id'] == 26 || $_GET['id'] == 33 || $_GET['id'] == 29 || $_GET['id'] == 30) {
                $this->buildingIndex = 25;
                //$this->redirect ('build?id=25');
                //return null;
            }
        }
        if ($this->dataGame['blocked_time'] > time()) {
            $this->redirect('banned');
            return null;
        }
        if (((($this->buildingIndex == 0 - 1 AND isset($_GET['id'])) AND is_numeric($_GET['id'])) AND isset($this->buildings[$_GET['id']]))) {
            $this->buildingIndex = intval($_GET['id']);
        }
        $this->buildProperties = $this->getBuildingProperties($this->buildingIndex);
        if ($this->buildProperties == NULL) {
            $this->redirect('village1');
            return null;
        }
        if ($this->buildProperties['emptyPlace']) {
            $this->villagesLinkPostfix .= '&id=' . $this->buildingIndex;
            $this->newBuilds = array(
                'available' => array(),
                'soon' => array()
            );
            foreach ($this->gameMetadata['items'] as $item_id => $build) {
                if (($item_id <= 4 OR !isset($build['for_tribe_id'][$this->tribeId]))) {
                    if ($this->tribeId != 5) {
                        continue;
                    }
                }
                $canBuild = $this->canCreateNewBuild($item_id);
                if ($canBuild != 0 - 1) {
                    if ($canBuild) {
                        if (!isset($this->newBuilds['available'][$build['levels'][0]['time_consume']])) {
                            $this->newBuilds['available'][$build['levels'][0]['time_consume']] = array();
                        }
                        $this->newBuilds['available'][$build['levels'][0]['time_consume']][$item_id] = $build;
                        continue;
                    } else {
                        $dependencyCount = 0;
                        foreach ($build['pre_requests'] as $reqId => $reqValue) {
                            if ($reqValue != NULL) {
                                $build['pre_requests_dependencyCount'][$reqId] = $reqValue - $this->_getMaxBuildingLevel($reqId);
                                $dependencyCount += $build['pre_requests_dependencyCount'][$reqId];
                                continue;
                            }
                        }
                        if (!isset($this->newBuilds['soon'][$dependencyCount])) {
                            $this->newBuilds['soon'][$dependencyCount] = array();
                        }
                        $this->newBuilds['soon'][$dependencyCount][$item_id] = $build;
                        continue;
                    }
                    continue;
                }
            }
            ksort($this->newBuilds['available'], SORT_NUMERIC);
            ksort($this->newBuilds['soon'], SORT_NUMERIC);
            return null;
        }
        $bitemId = $this->buildProperties['building']['item_id'];
        $this->villagesLinkPostfix .= '&id=' . $this->buildingIndex;
        if (4 < $bitemId) {
            $this->villagesLinkPostfix .= '&bid=' . $bitemId;
        }
        $this->buildingTribeFactor = (isset($this->gameMetadata['items'][$bitemId]['for_tribe_id'][$this->data['tribe_id']]) ? $this->gameMetadata['items'][$bitemId]['for_tribe_id'][$this->data['tribe_id']] : 1);
        if ($this->buildings[$this->buildingIndex]['level'] == 0) {
            return null;
        }
        switch ($bitemId) {
            case 12: {
            }
            case 13: {
                $this->productionPane = FALSE;
                $this->buildingView   = 'Blacksmith_Armoury';
                $this->handleBlacksmithArmoury();
                break;
            }
            case 15: {
                if (10 <= $this->buildings[$this->buildingIndex]['level']) {
                    $this->buildingView = 'MainBuilding';
                    $this->handleMainBuilding();
                }
                break;
            }
            case 16: {
                $this->productionPane = FALSE;
                $this->buildingView   = 'RallyPoint';
                $this->handleRallyPoint();
                break;
            }
            case 17: {
                $this->productionPane = FALSE;
                $this->_getOnlyMyTroops();
                $this->buildingView = 'Marketplace';
                $this->handleMarketplace();
                $this->handleWarrior();
                break;
            }
            case 18: {
                $this->productionPane = FALSE;
                $this->buildingView   = 'Embassy';
                $this->handleEmbassy();
                break;
            }
            case 19: {
            }
            case 20: {
            }
            case 21: {
            }
            case 29: {
            }
            case 30: {
            }
            case 36: {
                $this->_getOnlyMyTroops();
                $this->productionPane = $bitemId == 36;
                $this->buildingView   = 'TroopBuilding';
                $this->handleTroopBuilding();
                break;
            }
            case 22: {
                $this->productionPane = FALSE;
                $this->buildingView   = 'Academy';
                $this->handleAcademy();
                break;
            }
            case 23: {
                $this->productionPane = TRUE;
                $this->buildingView   = 'Cranny';
                break;
            }
            case 24: {
                $this->productionPane = FALSE;
                $this->buildingView   = 'TownHall';
                $this->handleTownHall();
                break;
            }
            case 25: {
            }
            case 26: {
                $this->productionPane = FALSE;
                $this->buildingView   = 'Residence_Palace';
                $this->handleResidencePalace();
                break;
            }
            case 27: {
                $this->productionPane = FALSE;
                $this->buildingView   = 'Treasury';
                break;
            }
            case 37: {
                $this->productionPane = FALSE;
                $this->buildingView   = 'HerosMansion';
                $this->handleHerosMansion();
                break;
            }
            case 40: {
                $this->productionPane = FALSE;
            }
            case 42: {
                $this->_getOnlyMyTroops();
                $this->productionPane = TRUE;
                $this->buildingView   = "Warrior";
                $this->handleWarrior();
}
}
}
    function handleBlacksmithArmoury()
    {
        $this->troopsUpgradeType = ($this->buildings[$this->buildingIndex]['item_id'] == 12 ? QS_TROOP_UPGRADE_ATTACK : QS_TROOP_UPGRADE_DEFENSE);
        $this->troopsUpgrade     = array();
        $_arr                    = explode(',', $this->data['troops_training']);
        $_c                      = 0;
        foreach ($_arr as $troopStr) {
            ++$_c;
            list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
            $tlevel = ($this->troopsUpgradeType == QS_TROOP_UPGRADE_ATTACK ? $attack_level : $defense_level);
            if (((($troopId != 99 AND $_c <= 8)) AND $researches_done == 1)) {
                $this->troopsUpgrade[$troopId] = $tlevel;
                continue;
            }
        }
        
        if (isset($_GET['a']) AND !isset($_GET['k']) AND !isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType]) AND isset($this->troopsUpgrade[intval($_GET['a'])]) AND !$this->isGameTransientStopped() AND !$this->isGameOver()) {
            $troopId          = intval($_GET['a']);
            $level            = $this->troopsUpgrade[$troopId];
            $levelbuildings   = $this->buildings[$this->buildingIndex]['level'];
            $buildingMetadata = $this->gameMetadata['items'][$this->buildProperties['building']['item_id']]['troop_upgrades'][$troopId][$level];
            if ($this->isGameOver()) {
                $this->redirect('over');
            }
            if ($this->isGameTransientStopped()) {
                return null;
            }
            if ($levelbuildings <= $level) {
                return null;
            } elseif ($this->data['gold_num'] < $this->appConfig['Game']['dev_troop_to_20']) {
                return null;
            }
            
            $for_level = 20 - $level;
            
            if ($for_level > 0) {
                $mq = new QueueJobModel();
                if ($this->buildings[$this->buildingIndex]['item_id'] == 12) {
                    $mq->provider->executeQuery2("INSERT INTO p_queue SET player_id='%s', village_id='%s', to_player_id='', to_village_id='', proc_type=5, building_id='', proc_params='%s', threads=1, end_date='2012-08-27 02:46:24', execution_time='500'", array(
                        $this->player->playerId,
                        $this->data['selected_village_id'],
                        "$troopId $levelbuildings"
                    ));
                } else {
                    $mq->provider->executeQuery2("INSERT INTO p_queue SET player_id='%s', village_id='%s', to_player_id='', to_village_id='', proc_type=6, building_id='', proc_params='%s', threads=1, end_date='2012-08-27 02:46:24', execution_time='500'", array(
                        $this->player->playerId,
                        $this->data['selected_village_id'],
                        "$troopId $levelbuildings"
                    ));
                }
                $mq->provider->executeQuery2("UPDATE `p_players` SET `gold_num` = `gold_num` - %s WHERE id=%s", array(
                    $this->appConfig['Game']['dev_troop_to_20'],
                    $this->player->playerId
                ));
                //start pgold
                $tatarzx = new QueueModel();
                $d       = date('Y/m/d H:i:s');
                $n       = $this->data['name'];
                $tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('" . $n . "', '" . $d . "', '" . $this->appConfig['Game']['dev_troop_to_20'] . "', 'تحسين جندي الى المستوى 20');");
                //end pgold
                $this->redirect('build?id=' . $_GET['id'] . '');
            }
        } elseif (((((((isset($_GET['a']) AND isset($_GET['k'])) AND $_GET['k'] == $this->data['update_key']) AND !isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType])) AND isset($this->troopsUpgrade[intval($_GET['a'])])) AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
            $troopId          = intval($_GET['a']);
            $level            = $this->troopsUpgrade[$troopId];
            $levelbuildings   = $this->buildings[$this->buildingIndex]['level'];
            $buildingMetadata = $this->gameMetadata['items'][$this->buildProperties['building']['item_id']]['troop_upgrades'][$troopId][$level];
            if (!$this->isResourcesAvailable($buildingMetadata['resources'])) {
                return null;
            } elseif ($levelbuildings <= $level) {
                return null;
            }
            $artefact            = 10;
            $q                   = new QueueModel();
            $calcConsume         = intval($buildingMetadata['time_consume'] * $this->gameSpeed * ($artefact / ($this->buildProperties['building']['level'] + 9)));
            $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
            $newTask->villageId  = $this->data['selected_village_id'];
            $newTask->procParams = $troopId . ' ' . ($level + 1);
            $newTask->tag        = $buildingMetadata['resources'];
            $this->queueModel->addTask($newTask);
        }
    }
    function handleMainBuilding()
    {
        if ((((((((($this->isPost() AND isset($_POST['drbid'])) AND 19 <= intval($_POST['drbid'])) AND intval($_POST['drbid']) <= sizeof($this->buildings)) AND isset($this->buildings[$_POST['drbid']])) AND 0 < $this->buildings[$_POST['drbid']]['level']) AND !isset($this->queueModel->tasksInQueue[QS_BUILD_DROP])) AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
            if (isset($_POST['full']) && $this->data['gold_num'] > 5) {
                $qj   = new QueueModel();
                $gold = 5;
                $qj->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num-" . $gold . " WHERE id = '" . $this->player->playerId . "'");
                
                $buildingArr = explode(",", $this->data['buildings']);
                $c           = 0;
                foreach ($buildingArr as $buildingItem) {
                    ++$c;
                    list($item_id, $level, $update_state) = explode(" ", $buildingItem);
                    if ($c == $this->buildings[$_POST['drbid']]['index']) {
                        $dropLevels = $level + $update_state;
                        while (0 < $dropLevels--) {
                            $mq = new QueueJobModel();
                            $mq->upgradeBuilding($this->data['selected_village_id'], $c, $item_id, TRUE);
                        }
                    }
                }
                $this->redirect('build?bid=15');
            } else {
                $item_id             = $this->buildings[$_POST['drbid']]['item_id'];
                $calcConsume         = intval($this->gameMetadata['items'][$item_id]['levels'][$this->buildings[$_POST['drbid']]['level'] - 1]['time_consume'] / $this->gameSpeed * ($this->data['time_consume_percent'] / 400));
                $newTask             = new QueueTask(QS_BUILD_DROP, $this->player->playerId, $calcConsume);
                $newTask->villageId  = $this->data['selected_village_id'];
                $newTask->buildingId = $item_id;
                $newTask->procParams = $this->buildings[$_POST['drbid']]['index'];
                $this->queueModel->addTask($newTask);
            }
            return null;
        }
        if ((((((((isset($_GET['qid']) AND is_numeric($_GET['qid'])) AND isset($_GET['k'])) AND $_GET['k'] == $this->data['update_key']) AND isset($_GET['d'])) AND isset($this->queueModel->tasksInQueue[QS_BUILD_DROP])) AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
            $this->queueModel->cancelTask($this->player->playerId, intval($_GET['qid']));
        }
    }
    function handleRallyPoint()
    {
        
        $m               = new BuildModel();
        $this->hidetroop = $m->hidetroop($this->player->playerId);
        if ($this->isPost() AND $this->data['active_plus_account']) {
            if (isset($_POST['troop_escape_active'])) {
                $hidetroop = 1;
            } else {
                $hidetroop = 0;
            }
            $m->uphidetroop($hidetroop, $this->player->playerId);
            $this->redirect('build?id=39&t=4');
        }
        if (isset($_GET['d'])) {
            $this->queueModel->cancelTask($this->player->playerId, intval($_GET['d']));
        }
        $this->rallyPointProperty = array(
            'troops_in_village' => array(
                'troopsTable' => $this->_getTroopsList('troops_num'),
                'troopsIntrapTable' => $this->_getTroopsList('troops_intrap_num')
            ),
            'troops_out_village' => array(
                'troopsTable' => $this->_getTroopsList('troops_out_num'),
                'troopsIntrapTable' => $this->_getTroopsList('troops_out_intrap_num')
            ),
            'troops_in_oases' => array(),
            'war_to_village' => $this->queueModel->tasksInQueue['war_troops']['to_village'],
            'war_from_village' => $this->queueModel->tasksInQueue['war_troops']['from_village'],
            'war_to_oasis' => $this->queueModel->tasksInQueue['war_troops']['to_oasis']
        );
        $village_oases_id         = trim($this->data['village_oases_id']);
        if ($village_oases_id != '') {
            $m      = new BuildModel();
            $result = $m->getOasesDataById($village_oases_id);
            while ($result->next()) {
                $this->rallyPointProperty['troops_in_oases'][$result->row['id']] = array(
                    'oasisRow' => $result->row,
                    'troopsTable' => $this->_getOasisTroopsList($result->row['troops_num']),
                    'war_to' => (isset($this->rallyPointProperty['war_to_oasis'][$result->row['id']]) ? $this->rallyPointProperty['war_to_oasis'][$result->row['id']] : NULL)
                );
            }
            $m->dispose();
        }
    }
    function _canCancelWarTask($taskType, $taskId)
    {
        if (!QueueTask::iscancelabletask($taskType)) {
            return FALSE;
        }
        $timeout = QueueTask::getmaxcanceltimeout($taskType);
        if (0 - 1 < $timeout) {
            $_task = NULL;
            foreach ($this->queueModel->tasksInQueue[$taskType] as $t) {
                if ($t['id'] == $taskId) {
                    $_task = $t;
                    break;
                }
            }
            if ($_task == NULL) {
                return FALSE;
            }
            $elapsedTime = $t['elapsedTime'];
            if ($timeout < $elapsedTime) {
                return FALSE;
            }
        }
        return TRUE;
    }
    function _getOasisTroopsList($troops_num)
    {
        $GameMetadata = $GLOBALS['GameMetadata'];
        $m            = new BuildModel();
        $returnTroops = array();
        if (trim($troops_num) != '') {
            $t_arr = explode('|', $troops_num);
            foreach ($t_arr as $t_str) {
                $t2_arr             = explode(':', $t_str);
                $vid                = $t2_arr[0];
                $villageData        = $m->getVillageData2ById($vid);
                $returnTroops[$vid] = array(
                    'villageData' => $villageData,
                    'cropConsumption' => 0,
                    'hasHero' => FALSE,
                    'troops' => array()
                );
                $t2_arr             = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str) {
                    list($tid, $tnum) = explode(' ', $t2_str);
                    if ($tid == 99) {
                        continue;
                    }
                    if ($tnum == 0 - 1) {
                        $tnum                          = 1;
                        $returnTroops[$vid]['hasHero'] = TRUE;
                    } else {
                        $returnTroops[$vid]['troops'][$tid] = $tnum;
                    }
                    $returnTroops[$vid]['cropConsumption'] += $GameMetadata['troops'][$tid]['crop_consumption'] * $tnum;
                }
            }
        }
        $m->dispose();
        return $returnTroops;
    }
    function _getTroopsList($key)
    {
        $GameMetadata = $GLOBALS['GameMetadata'];
        $m            = new BuildModel();
        $returnTroops = array();
        if (trim($this->data[$key]) != '') {
            $t_arr = explode('|', $this->data[$key]);
            foreach ($t_arr as $t_str) {
                $t2_arr      = explode(':', $t_str);
                $vid         = intval($t2_arr[0]);
                $villageData = NULL;
                if ($vid == 0 - 1) {
                    $vid         = $this->data['selected_village_id'];
                    $villageData = array(
                        'id' => $vid,
                        'village_name' => $this->data['village_name'],
                        'player_id' => $this->player->playerId,
                        'player_name' => buildings_p_thisvillage
                    );
                } else {
                    $villageData = $m->getVillageData2ById($vid);
                }
                $returnTroops[$vid] = array(
                    'villageData' => $villageData,
                    'cropConsumption' => 0,
                    'hasHero' => FALSE,
                    'troops' => array()
                );
                if ($vid == $this->data['selected_village_id']) {
                    $returnTroops[$vid]['hasHero'] = intval($this->data['hero_in_village_id']) == intval($this->data['selected_village_id']);
                    if ($returnTroops[$vid]['hasHero']) {
                        $returnTroops[$vid]['cropConsumption'] += $GameMetadata['troops'][$this->data['hero_troop_id']]['crop_consumption'];
                    }
                }
                $t2_arr = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str) {
                    list($tid, $tnum) = explode(' ', $t2_str);
                    if ($tid == 99) {
                        continue;
                    }
                    if ($tnum == 0 - 1) {
                        $tnum                          = 1;
                        $returnTroops[$vid]['hasHero'] = TRUE;
                    } else {
                        $returnTroops[$vid]['troops'][$tid] = $tnum;
                    }
                    $returnTroops[$vid]['cropConsumption'] += $GameMetadata['troops'][$tid]['crop_consumption'] * $tnum;
                }
            }
        }
        $m->dispose();
        return $returnTroops;
    }
    function handleMarketplace()
    {
        $this->selectedTabIndex = ((((isset($_GET['t']) AND is_numeric($_GET['t'])) AND 1 <= intval($_GET['t'])) AND intval($_GET['t']) <= 4) ? intval($_GET['t']) : 0);
        $itemId                 = $this->buildings[$this->buildingIndex]['item_id'];
        $itemLevel              = $this->buildings[$this->buildingIndex]['level'];
        $tribeMetadata          = $this->gameMetadata['tribes'][$this->data['tribe_id']];
        $tradeOfficeLevel       = $this->_getMaxBuildingLevel(28);
        $capacityFactor         = ($tradeOfficeLevel == 0 ? 1 : $this->gameMetadata['items'][28]['levels'][$tradeOfficeLevel - 1]['value'] / 100);
        $capacityFactor *= $this->gameMetadata['game_speed'];
        $total_merchants_num = $this->gameMetadata['items'][$itemId]['levels'][$itemLevel - 1]['value'];
        $exist_num           = $total_merchants_num - $this->queueModel->tasksInQueue['out_merchants_num'] - $this->data['offer_merchants_count'];
        if ($exist_num < 0) {
            $exist_num = 0;
        }
        $this->merchantProperty = array(
            "speed" => $tribeMetadata['merchants_velocity'],
            "capacity" => floor($tribeMetadata['merchants_capacity'] * $capacityFactor),
            "total_num" => $total_merchants_num,
            "exits_num" => $exist_num,
            "confirm_snd" => FALSE,
            "same_village" => FALSE,
            "vRow" => NULL
        );
        if ($this->selectedTabIndex == 0) {
            $m = new BuildModel();
            if ($this->isPost() || isset($_GET['vid2'])) {
                $resources                             = array(
                    "1" => isset($_POST['r1']) ? intval($_POST['r1']) : 0,
                    "2" => isset($_POST['r2']) ? intval($_POST['r2']) : 0,
                    "3" => isset($_POST['r3']) ? intval($_POST['r3']) : 0,
                    "4" => isset($_POST['r4']) ? intval($_POST['r4']) : 0
                );
                $this->merchantProperty['confirm_snd'] = $this->isPost() ? isset($_POST['act']) && $_POST['act'] == 1 : isset($_GET['vid2']);
                $map_size                              = $this->setupMetadata['map_size'];
                $doSend                                = FALSE;
                if ($this->merchantProperty['confirm_snd']) {
                    $vRow = NULL;
                    if (isset($_POST['x'], $_POST['y']) && trim($_POST['x']) != "" && trim($_POST['y']) != "") {
                        $vid  = $this->__getVillageId($map_size, $this->__getCoordInRange($map_size, intval($_POST['x'])), $this->__getCoordInRange($map_size, intval($_POST['y'])));
                        $vRow = $m->getVillageDataById($vid);
                    } else if (isset($_POST['vname']) && trim($_POST['vname']) != "") {
                        $vRow = $m->getVillageDataByName(trim($_POST['vname']));
                    } else if (isset($_GET['vid2'])) {
                        $vRow = $m->getVillageDataById(intval($_GET['vid2']));
                        if ($vRow != NULL) {
                            $_POST['x'] = $vRow['rel_x'];
                            $_POST['y'] = $vRow['rel_y'];
                        }
                    }
                } else {
                    $doSend                              = TRUE;
                    $vRow                                = $m->getVillageDataById(intval($_POST['vid2']));
                    $this->merchantProperty['showError'] = FALSE;
                    $_POST['r1']                         = $_POST['r2'] = $_POST['r3'] = $_POST['r4'] = "";
                }
                if (0 < intval($vRow['player_id']) && $m->getPlayType(intval($vRow['player_id'])) == PLAYERTYPE_ADMIN) {
                    $this->merchantProperty['showError']   = FALSE;
                    $this->merchantProperty['confirm_snd'] = FALSE;
                } else {
                    $this->merchantProperty['vRow']         = $vRow;
                    $vid                                    = $this->merchantProperty['to_vid'] = $vRow != NULL ? $vRow['id'] : 0;
                    $rel_x                                  = $vRow['rel_x'];
                    $rel_y                                  = $vRow['rel_y'];
                    $this->merchantProperty['same_village'] = $vid == $this->data['selected_village_id'];
                    if ($this->player->isAgent == 1 AND substr($this->player->actions, 2, 1) == 0) {
                        $this->merchantProperty['actions'] = 0;
                    } else {
                        $this->merchantProperty['actions'] = 1;
                    }
                    if ($pRow['last_ip'] == $this->data['last_ip'] AND $vRow['player_id'] != $this->player->playerId) {
                        $this->merchantProperty['send_alien'] = 0;
                    } else {
                        $this->merchantProperty['send_alien'] = 1;
                    }
                    
                    
                    $this->merchantProperty['available_res']     = $this->isResourcesAvailable($resources);
                    $this->merchantProperty['vRow_merchant_num'] = ceil(($resources[1] + $resources[2] + $resources[3] + $resources[4]) / $this->merchantProperty['capacity']);
                    $this->merchantProperty['confirm_snd']       = 0 < $vid && $pRow['last_ip'] != $this->data['last_ip'] && $this->merchantProperty['available_res'] && 0 < $this->merchantProperty['vRow_merchant_num'] && $this->merchantProperty['vRow_merchant_num'] <= $this->merchantProperty['exits_num'] && !$this->merchantProperty['same_village'] && $this->merchantProperty['actions'] && $this->merchantProperty['send_alien'];
                    
                    $pvid          = $vRow['player_id'];
                    $test_kings    = new QueueModel();
                    $test_black    = $test_kings->provider->fetchRow("SELECT name FROM p_players WHERE id = '" . $pvid . "'");
                    $black_list    = $this->data['black_list'];
                    $ex_black_list = explode(',', $black_list);
                    foreach ($ex_black_list as $test) {
                        if ($test_black['name'] == $test && $vRow['player_id'] != $this->player->playerId) {
                            return exit("<h1>Error 505 !</h1>");
                        }
                    }
                    
                    
                    $this->merchantProperty['showError'] = !$this->merchantProperty['confirm_snd'];
                    $distance                            = WebHelper::getdistance($this->data['rel_x'], $this->data['rel_y'], $rel_x, $rel_y, $this->setupMetadata['map_size'] / 2);
                    $this->merchantProperty['vRow_time'] = intval($distance / $this->merchantProperty['speed'] * 3600);
                    if (!$this->merchantProperty['showError'] && $doSend && !$this->isGameTransientStopped() && !$this->isGameOver()) {
                        if (intval($_POST['send_bell']) >= 1 AND intval($_POST['send_bell']) <= 20 AND $this->data['active_plus_account']) {
                            $send_bell = intval($_POST['send_bell']);
                        } else {
                            $send_bell = 1;
                        }
                        
                        $this->merchantProperty['confirm_snd'] = FALSE;
                        $this->merchantProperty['exits_num'] -= $this->merchantProperty['vRow_merchant_num'];
                        $newTask              = new QueueTask(QS_MERCHANT_GO, $this->player->playerId, $this->merchantProperty['vRow_time']);
                        $newTask->villageId   = $this->data['selected_village_id'];
                        $newTask->toPlayerId  = $vRow['player_id'];
                        $newTask->toVillageId = $vid;
                        $newTask->procParams  = $this->merchantProperty['vRow_merchant_num'] . "|" . ($resources[1] . " " . $resources[2] . " " . $resources[3] . " " . $resources[4]) . "|" . $send_bell;
                        $newTask->tag         = $resources;
                        $this->queueModel->addTask($newTask);
                    }
                }
            }
            $m->dispose();
        }
        if ($this->selectedTabIndex == 1) {
            $m             = new BuildModel();
            $showOfferList = TRUE;
            if ((isset($_GET['oid']) AND 0 < intval($_GET['oid']))) {
                $oRow = $m->getOffer2(intval($_GET['oid']), $this->data['rel_x'], $this->data['rel_y'], $this->setupMetadata['map_size'] / 2);
                if ($oRow != NULL) {
                    $aid = 0;
                    if ($oRow['alliance_only']) {
                        if (0 < intval($this->data['alliance_id'])) {
                            $aid = $m->getPlayerAllianceId($oRow['player_id']);
                        }
                    }
                    list($res1, $res2) = explode('|', $oRow['offer']);
                    $resArr1       = explode(' ', $res1);
                    $needResources = array(
                        '1' => $resArr1[0],
                        '2' => $resArr1[1],
                        '3' => $resArr1[2],
                        '4' => $resArr1[3]
                    );
                    $res1_item_id  = 0;
                    $res1_value    = 0;
                    $i             = 0;
                    $_c            = sizeof($resArr1);
                    while ($i < $_c) {
                        if (0 < $resArr1[$i]) {
                            $res1_item_id = $i + 1;
                            $res1_value   = $resArr1[$i];
                            break;
                        }
                        ++$i;
                    }
                    $resArr1       = explode(' ', $res2);
                    $giveResources = array(
                        '1' => $resArr1[0],
                        '2' => $resArr1[1],
                        '3' => $resArr1[2],
                        '4' => $resArr1[3]
                    );
                    $res2_item_id  = 0;
                    $res2_value    = 0;
                    $i             = 0;
                    $_c            = sizeof($resArr1);
                    while ($i < $_c) {
                        if (0 < $resArr1[$i]) {
                            $res2_item_id = $i + 1;
                            $res2_value   = $resArr1[$i];
                            break;
                        }
                        ++$i;
                    }
                    $distance     = $oRow['timeInSeconds'] / 3600 * $oRow['merchants_speed'];
                    $acceptResult = $this->_canAcceptOffer($needResources, $giveResources, $oRow['village_id'], $oRow['alliance_only'], $aid, $oRow['max_time'], $distance);
                    if ((($acceptResult == 5 AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
                        $showOfferList                           = FALSE;
                        $this->merchantProperty['offerProperty'] = array(
                            'player_id' => $oRow['player_id'],
                            'player_name' => $oRow['player_name'],
                            'res1_item_id' => $res1_item_id,
                            'res1_value' => $res1_value,
                            'res2_item_id' => $res2_item_id,
                            'res2_value' => $res2_value
                        );
                        $merchantNum                             = ceil(($giveResources[1] + $giveResources[2] + $giveResources[3] + $giveResources[4]) / $this->merchantProperty['capacity']);
                        $newTask                                 = new QueueTask(QS_MERCHANT_GO, $this->player->playerId, $distance / ($this->gameMetadata['tribes'][$this->data['tribe_id']]['merchants_velocity']) * 3600);
                        $newTask->villageId                      = $this->data['selected_village_id'];
                        $newTask->toPlayerId                     = $oRow['player_id'];
                        $newTask->toVillageId                    = $oRow['village_id'];
                        $newTask->procParams                     = $merchantNum . '|' . ($giveResources[1] . ' ' . $giveResources[2] . ' ' . $giveResources[3] . ' ' . $giveResources[4]);
                        $newTask->tag                            = $giveResources;
                        $this->queueModel->addTask($newTask);
                        $newTask              = new QueueTask(QS_MERCHANT_GO, $oRow['player_id'], $oRow['timeInSeconds']);
                        $newTask->villageId   = $oRow['village_id'];
                        $newTask->toPlayerId  = $this->player->playerId;
                        $newTask->toVillageId = $this->data['selected_village_id'];
                        $newTask->procParams  = $oRow['merchants_num'] . '|' . ($needResources[1] . ' ' . $needResources[2] . ' ' . $needResources[3] . ' ' . $needResources[4]);
                        $newTask->tag         = array(
                            '1' => 0,
                            '2' => 0,
                            '3' => 0,
                            '4' => 0
                        );
                        $this->queueModel->addTask($newTask);
                        $m->removeMerchantOffer(intval($_GET['oid']), $oRow['player_id'], $oRow['village_id']);
                    }
                }
            }
            $this->merchantProperty['showOfferList'] = $showOfferList;
            if ($showOfferList) {
                $rowsCount                            = $m->getAllOffersCount($this->data['selected_village_id'], $this->data['rel_x'], $this->data['rel_y'], $this->setupMetadata['map_size'] / 2, $this->gameMetadata['tribes'][$this->data['tribe_id']]['merchants_velocity']);
                $this->pageCount                      = (0 < $rowsCount ? ceil($rowsCount / $this->pageSize) : 1);
                $this->pageIndex                      = (((isset($_GET['p']) AND is_numeric($_GET['p'])) AND intval($_GET['p']) < $this->pageCount) ? intval($_GET['p']) : 0);
                $this->merchantProperty['all_offers'] = $m->getAllOffers($this->data['selected_village_id'], $this->data['rel_x'], $this->data['rel_y'], $this->setupMetadata['map_size'] / 2, $this->gameMetadata['tribes'][$this->data['tribe_id']]['merchants_velocity'], $this->pageIndex, $this->pageSize);
            }
            $m->dispose();
            return null;
        }
        if ($this->selectedTabIndex == 2) {
            $m                                    = new BuildModel();
            $this->merchantProperty['showError']  = FALSE;
            $this->merchantProperty['showError2'] = FALSE;
            $this->merchantProperty['showError3'] = FALSE;
            if ($this->isPost()) {
                if ((((((((isset($_POST['m1']) AND 0 < intval($_POST['m1'])) AND isset($_POST['m2'])) AND 0 < intval($_POST['m2'])) AND isset($_POST['rid1'])) AND 0 < intval($_POST['rid1'])) AND isset($_POST['rid2'])) AND 0 < intval($_POST['rid2']))) {
                    $resources1 = array(
                        '1' => ((isset($_POST['rid1']) AND intval($_POST['rid1']) == 1) ? intval($_POST['m1']) : 0),
                        '2' => ((isset($_POST['rid1']) AND intval($_POST['rid1']) == 2) ? intval($_POST['m1']) : 0),
                        '3' => ((isset($_POST['rid1']) AND intval($_POST['rid1']) == 3) ? intval($_POST['m1']) : 0),
                        '4' => ((isset($_POST['rid1']) AND intval($_POST['rid1']) == 4) ? intval($_POST['m1']) : 0)
                    );
                    $resources2 = array(
                        '1' => ((isset($_POST['rid2']) AND intval($_POST['rid2']) == 1) ? intval($_POST['m2']) : 0),
                        '2' => ((isset($_POST['rid2']) AND intval($_POST['rid2']) == 2) ? intval($_POST['m2']) : 0),
                        '3' => ((isset($_POST['rid2']) AND intval($_POST['rid2']) == 3) ? intval($_POST['m2']) : 0),
                        '4' => ((isset($_POST['rid2']) AND intval($_POST['rid2']) == 4) ? intval($_POST['m2']) : 0)
                    );
                    if (((intval($_POST['rid1']) == intval($_POST['rid2']) OR intval($resources1[1] + $resources1[2] + $resources1[3] + $resources1[4]) <= 0) OR intval($resources2[1] + $resources2[2] + $resources2[3] + $resources2[4]) <= 0)) {
                        $this->merchantProperty['showError'] = TRUE;
                    } else {
                        if (10 < ceil(($resources2[1] + $resources2[2] + $resources2[3] + $resources2[4]) / ($resources1[1] + $resources1[2] + $resources1[3] + $resources1[4]))) {
                            $this->merchantProperty['showError']  = TRUE;
                            $this->merchantProperty['showError3'] = TRUE;
                        }
                    }
                    $this->merchantProperty['available_res'] = $this->isResourcesAvailable($resources1);
                    if (($this->merchantProperty['available_res'] AND !$this->merchantProperty['showError'])) {
                        $this->merchantProperty['vRow_merchant_num'] = ceil(($resources1[1] + $resources1[2] + $resources1[3] + $resources1[4]) / $this->merchantProperty['capacity']);
                        if ((0 < $this->merchantProperty['vRow_merchant_num'] AND $this->merchantProperty['vRow_merchant_num'] <= $this->merchantProperty['exits_num'])) {
                            $this->merchantProperty['exits_num'] -= $this->merchantProperty['vRow_merchant_num'];
                            $this->data['offer_merchants_count'] += $this->merchantProperty['vRow_merchant_num'];
                            $offer = $resources1[1] . ' ' . $resources1[2] . ' ' . $resources1[3] . ' ' . $resources1[4] . '|' . ($resources2[1] . ' ' . $resources2[2] . ' ' . $resources2[3] . ' ' . $resources2[4]);
                            $m->addMerchantOffer($this->player->playerId, $this->data['name'], $this->data['selected_village_id'], $this->data['rel_x'], $this->data['rel_y'], $this->merchantProperty['vRow_merchant_num'], $offer, isset($_POST['ally']), (((isset($_POST['d1']) AND isset($_POST['d2'])) AND 0 < intval($_POST['d2'])) ? intval($_POST['d2']) : 0), $this->gameMetadata['tribes'][$this->data['tribe_id']]['merchants_velocity']);
                            foreach ($resources1 as $k => $v) {
                                $this->resources[$k]['current_value'] -= $v;
                            }
                            $this->queueModel->_updateVillage(FALSE, FALSE);
                        } else {
                            $this->merchantProperty['showError'] = TRUE;
                        }
                    } else {
                        $this->merchantProperty['showError'] = TRUE;
                    }
                } else {
                    $this->merchantProperty['showError']  = TRUE;
                    $this->merchantProperty['showError2'] = TRUE;
                }
            } else {
                if ((isset($_GET['d']) AND 0 < intval($_GET['d']))) {
                    $row = $m->getOffer(intval($_GET['d']), $this->player->playerId, $this->data['selected_village_id']);
                    if ($row != NULL) {
                        $this->merchantProperty['exits_num'] += $row['merchants_num'];
                        $this->data['offer_merchants_count'] -= $row['merchants_num'];
                        list($resources1, $resources2) = explode('|', $row['offer']);
                        $resourcesArray1 = explode(' ', $resources1);
                        $res             = array();
                        $i               = 0;
                        $_c              = sizeof($resourcesArray1);
                        while ($i < $_c) {
                            $res[$i + 1] = $resourcesArray1[$i];
                            ++$i;
                        }
                        foreach ($res as $k => $v) {
                            $this->resources[$k]['current_value'] += $v;
                        }
                        $this->queueModel->_updateVillage(FALSE, FALSE);
                        $m->removeMerchantOffer(intval($_GET['d']), $this->player->playerId, $this->data['selected_village_id']);
                    }
                }
            }
            $this->merchantProperty['offers'] = $m->getOffers($this->data['selected_village_id']);
            $m->dispose();
            return null;
        }
		if ($this->selectedTabIndex == 4)
{
$gold = $GLOBALS['AppConfig']['Game']['tid_npc'];
$m = new BuildModel ();
$this->error_message = "";
$this->page = 1;
if($this->isPost())
{
if(!isset ($_POST['tid']))
{
$this->error_message = 'يجب ان تختار الجندي على الاقل.';
return;
}

if(!isset ($_POST['tid_npc']))
{
$this->error_message = 'يجب ان تختار الجندي على الاقل.';
return;
}

if($_POST['tid_npc'] == $_POST['tid'])
{
$this->error_message = 'لايمكنك تبديل واستبدال نفس النوع.';
return;
}

$_arr = explode (',', $this->data['troops_training']);
foreach ($_arr as $troopStr)
{
list ($troopId, $researches_done, $defense_level, $attack_level) = explode (' ', $troopStr);
if ($troopId == intval ($_POST['tid_npc'])) { $t_R = $researches_done;  }
}

if($t_R == 0)
{
$this->error_message = 'المحاوله مره اخرى. ';
return;
}

$troops_toVillageRowi     = explode('|', $this->data['troops_num']);
$troops_toVili = explode(':', $troops_toVillageRowi[0]);
$troops_toVili = explode(',', $troops_toVili[1]);
foreach ($troops_toVili as $tri)
{
list($tidi, $tnumi) = explode(' ', $tri);
if ($tidi == $_POST['tid']) {
$t_num = $tnumi;
} 
}

if($t_num == 0)
{
$this->error_message = 'المحاوله مره اخرى. ';
return;
}
if ($this->error_message == "") {
$this->page = 2;
}
if ($this->page == 2) {
$this->troops_1_type = intval ($_POST['tid']);
$this->troops_1_num = WebHelper::fix_num($t_num);
$this->troops_1_res = WebHelper::fix_num(floor(($GLOBALS['GameMetadata']['troops'][$this->troops_1_type]['training_resources'][1]+$GLOBALS['GameMetadata']['troops'][$this->troops_1_type]['training_resources'][2]+$GLOBALS['GameMetadata']['troops'][$this->troops_1_type]['training_resources'][3]+$GLOBALS['GameMetadata']['troops'][$this->troops_1_type]['training_resources'][4])*$this->troops_1_num));
$this->troops_2_type = intval($_POST['tid_npc']);
$this->troops_2_res = WebHelper::fix_num(($GLOBALS['GameMetadata']['troops'][$this->troops_2_type]['training_resources'][1]+$GLOBALS['GameMetadata']['troops'][$this->troops_2_type]['training_resources'][2]+$GLOBALS['GameMetadata']['troops'][$this->troops_2_type]['training_resources'][3]+$GLOBALS['GameMetadata']['troops'][$this->troops_2_type]['training_resources'][4]));
$this->troops_2_num = WebHelper::fix_num(floor($this->troops_1_res/$this->troops_2_res));

if (isset ($_POST['done']) AND $_POST['done'] == 1){
$this->page = "1";
if($this->data['gold_num'] < $gold)
{
$this->error_message = 'الذهب قليل جدا.';
return;
}
#$this->queueModel->provider->executeQuery2("UPDATE p_players SET gold_num=gold_num-".$gold." WHERE id=".$this->player->playerId);
$this->data['gold_num'] -= $gold;
			$newTroops = '';
		$t_arr = explode( '|', $this->data['troops_num'] );
		foreach ($t_arr as $t_str) {
			if ($newTroops != '') {
				$newTroops .= '|';
			}
			$t2_arr = explode( ':', $t_str );
			if ($t2_arr[0] == 0 - 1) {
				$newTroops .= $t2_arr[0] . ':';
				$vtroops = '';
				$t3_arr = explode( ',', $t2_arr[1] );
				foreach ($t3_arr as $t2_str) {
					list( $tid, $tnum ) = explode( ' ', $t2_str );
if ($tid == $this->troops_2_type) {
$tnum += $this->troops_2_num; 
} 
if ($tid == $this->troops_1_type) {
$tnum = 0;
} 
					if ($vtroops != '') {
						$vtroops .= ',';
					}
					$vtroops .= $tid . ' ' . $tnum;
				}
				$newTroops .= $vtroops;
				continue;
			}
			$newTroops .= $t_str;
		}
$this->queueModel->provider->executeQuery('UPDATE p_villages SET troops_num="'. $newTroops .'" WHERE id="'. $this->data['selected_village_id'] .'"');
$this->error_message = 'تمت المبادلة بنجاح.';
return;
}
}

}
}
        if ($this->selectedTabIndex == 3) {
            
            if (isset($_GET['resret']) and isset($_GET['rid']) and isset($_GET['r1']) and isset($_GET['r1']) and isset($_GET['r2']) and isset($_GET['r3']) and isset($_GET['r4']) and $this->gameMetadata['plusTable'][6]['cost'] <= $this->data['gold_num']) {
                $semodel = new QueueModel();
                
                $ss1 = number_format(($this->resources[1]['current_value'] + $this->resources[2]['current_value'] + $this->resources[3]['current_value'] + $this->resources[4]['current_value']), 0, "", "");
                
                $ro1 = $ss1;
                $r1  = $_GET['r1'];
                $r2  = $_GET['r2'];
                $r3  = $_GET['r3'];
                $r4  = $_GET['r4'];
                $r5  = $r1 + $r2 + $r3 + $r4;
                $r1m = $r1 * 100 / $r5;
                $r2m = $r2 * 100 / $r5;
                $r3m = $r3 * 100 / $r5;
                $r4m = $r4 * 100 / $r5;
                $r1n = number_format($r1m * $ro1 / 100, 0, "", "");
                $r2n = number_format($r2m * $ro1 / 100, 0, "", "");
                $r3n = number_format($r3m * $ro1 / 100, 0, "", "");
                $r4n = number_format($r4m * $ro1 / 100, 0, "", "");
                
                $ro   = $semodel->provider->fetchRow("SELECT * FROM `p_villages` WHERE `id` = '" . $this->data['selected_village_id'] . "'");
                //$ro = mysqli_fetch_array($se);
                $res1 = explode(",", $ro['resources']);
                $res2 = explode(" ", $res1[0]);
                $res6 = $res2[0] . " " . $r1n . " " . $res2[2] . " " . $res2[3] . " " . $res2[4] . " " . $res2[5] . ",";
                
                $res3 = explode(" ", $res1[1]);
                $res7 = $res3[0] . " " . $r2n . " " . $res3[2] . " " . $res3[3] . " " . $res3[4] . " " . $res3[5] . ",";
                
                $res4 = explode(" ", $res1[2]);
                $res8 = $res4[0] . " " . $r3n . " " . $res4[2] . " " . $res4[3] . " " . $res4[4] . " " . $res4[5] . ",";
                
                $res5 = explode(" ", $res1[3]);
                $res9 = $res5[0] . " " . $r4n . " " . $res5[2] . " " . $res5[3] . " " . $res5[4] . " " . $res5[5];
                
                $res = $res6 . $res7 . $res8 . $res9;
                $semodel->provider->executeQuery2("UPDATE `p_villages` SET `resources` = '" . $res . "' , `last_update_date` = NOW() WHERE `id` = '" . $this->data['selected_village_id'] . "'") or die(mysql_error());
                $Id = $this->player->playerId;
                $semodel->provider->executeQuery2("UPDATE  `p_players` SET  `gold_num` =  `gold_num` - '1' WHERE  `id` = $Id");
                
                header("location:build.php?id=" . $_GET['rid'] . "&translatedone");
                exit;
            }
            if (isset($_GET['retrunres']) && $this->gameMetadata['plusTable'][6]['cost'] <= $this->data['gold_num']) {
                $oldSum    = $this->resources[1]['current_value'] + $this->resources[2]['current_value'] + $this->resources[3]['current_value'] + $this->resources[4]['current_value'];
                $resources = array(
                    '1' => $oldSum / 4,
                    '2' => $oldSum / 4,
                    '3' => $oldSum / 4,
                    '4' => $oldSum / 4
                );
                $newSum    = $oldSum / 4;
                
                // temp fix for merch trade plus
                // fix not -cro
                if (($newSum - 500) <= $oldSum) {
                    foreach ($resources as $k => $v) {
                        $this->resources[$k]['current_value'] = $v;
                    }
                    $this->queueModel->_updateVillage(FALSE, FALSE);
                    
                    $m = new BuildModel();
                    $m->decreaseGoldNum($this->player->playerId, $this->gameMetadata['plusTable'][6]['cost']);
                    $m->dispose();
                }
            } else if ((((($this->isPost() AND isset($_POST['m2'])) AND is_array($_POST['m2'])) AND sizeof($_POST['m2']) == 4) AND $this->gameMetadata['plusTable'][6]['cost'] <= $this->data['gold_num'])) {
                $resources = array(
                    '1' => intval($_POST['m2'][0]),
                    '2' => intval($_POST['m2'][1]),
                    '3' => intval($_POST['m2'][2]),
                    '4' => intval($_POST['m2'][3])
                );
                $oldSum    = $this->resources[1]['current_value'] + $this->resources[2]['current_value'] + $this->resources[3]['current_value'] + $this->resources[4]['current_value'];
                $newSum    = $resources[1] + $resources[2] + $resources[3] + $resources[4];
                //start pgold
                $tatarzx   = new QueueModel();
                $d         = date('Y/m/d H:i:s');
                $n         = $this->data['name'];
                $tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('" . $n . "', '" . $d . "', '" . $this->gameMetadata['plusTable'][6]['cost'] . "', 'تاجر المبادله');");
                $tst = 0;
                if (($newSum - $tst) <= $oldSum) {
                    foreach ($resources as $k => $v) {
                        $this->resources[$k]['current_value'] = $v;
                    }
                    $this->queueModel->_updateVillage(FALSE, FALSE);
                    $m = new BuildModel();
                    $m->decreaseGoldNum($this->player->playerId, $this->gameMetadata['plusTable'][6]['cost']);
                    $m->dispose();
                }
            }
        }
			
    }
    function handleEmbassy()
    {
        if (0 < intval($this->data['alliance_id'])) {
            return null;
        }
        $this->embassyProperty = array(
            'level' => $this->buildings[$this->buildingIndex]['level'],
            'invites' => NULL,
            'error' => 0,
            'ally1' => '',
            'ally2' => ''
        );
        $maxPlayers            = $this->gameMetadata['items'][18]['levels'][$this->embassyProperty['level'] - 1]['value'];
        if (((($this->isPost() AND 3 <= $this->embassyProperty['level']) AND isset($_POST['ally1'])) AND isset($_POST['ally2']))) {
            $this->embassyProperty['ally1'] = $ally1 = trim($_POST['ally1']);
            $this->embassyProperty['ally2'] = $ally2 = trim($_POST['ally2']);
            if (($ally1 == '' OR $ally2 == '')) {
                $this->embassyProperty['error'] = (($ally1 == '' AND $ally2 == '') ? 3 : ($ally1 == '' ? 1 : 2));
            } else {
                $m = new BuildModel();
                if (!$m->allianceExists($this->embassyProperty['ally1'])) {
                    $this->data['alliance_name'] = $this->embassyProperty['ally1'];
                    $this->data['alliance_id']   = $m->createAlliance($this->player->playerId, $this->embassyProperty['ally1'], $this->embassyProperty['ally2'], $maxPlayers);
                    $global                      = new GlobalModel();
                    $tatarzx                     = new QueueModel();
                    $show                        = $tatarzx->provider->fetchScalar("SELECT * FROM alince WHERE ida='" . $this->data['alliance_id'] . "' AND idn='7' AND mname='" . $this->data['name'] . "'");
                    if ($show < 1) {
                        $global->inserttones($this->data['alliance_id'], 7, $this->data['name'], "", $this->player->playerId, "");
                    }
                    $m->dispose();
                    return null;
                }
                $this->embassyProperty['error'] = 4;
                $m->dispose();
            }
        }
        $invites_alliance_ids             = trim($this->data['invites_alliance_ids']);
        $this->embassyProperty['invites'] = array();
        if ($invites_alliance_ids != '') {
            $_arr = explode("\n", $invites_alliance_ids);
            foreach ($_arr as $_s) {
                list($allianceId, $allianceName) = explode(' ', $_s, 2);
                $this->embassyProperty['invites'][$allianceId] = $allianceName;
            }
        }
        if (!$this->isPost()) {
            if ((isset($_GET['a']) AND 0 < intval($_GET['a']))) {
                $allianceId = intval($_GET['a']);
                if (isset($this->embassyProperty['invites'][$allianceId])) {
                    $m            = new BuildModel();
                    $acceptResult = $m->acceptAllianceJoining($this->player->playerId, $allianceId);
                    $global       = new GlobalModel();
                    $tatarzx      = new QueueModel();
                    $show         = $tatarzx->provider->fetchScalar("SELECT * FROM alince WHERE ida='" . $allianceId . "' AND idn='1' AND mname='" . $this->data['name'] . "'");
                    if ($show < 1) {
                        $global->inserttones($allianceId, 1, $this->data['name'], "", $this->player->playerId, "");
                    }
                    if ($acceptResult == 2) {
                        $this->data['alliance_name'] = $this->embassyProperty['invites'][$allianceId];
                        $this->data['alliance_id']   = $allianceId;
                        unset($this->embassyProperty['invites'][$allianceId]);
                        $m->removeAllianceInvites($this->player->playerId, $allianceId);
                    } else {
                        if ($acceptResult == 1) {
                            $this->embassyProperty['error'] = 15;
                        }
                    }
                    $m->dispose();
                    return null;
                }
            } else {
                if ((isset($_GET['d']) AND 0 < intval($_GET['d']))) {
                    $allianceId = intval($_GET['d']);
                    if (isset($this->embassyProperty['invites'][$allianceId])) {
                        unset($this->embassyProperty['invites'][$allianceId]);
                        $m = new BuildModel();
                        $m->removeAllianceInvites($this->player->playerId, $allianceId);
                        $m->dispose();
                    }
                }
            }
        }
    }
    public function handleWarrior()
    {
        $itemId              = $this->buildings[$this->buildingIndex]['item_id'];
        $this->troopsUpgrade = array();
        $_arr                = explode(",", $this->data['troops_training']);
        foreach ($_arr as $troopStr) {
            $tempdata        = explode(" ", $troopStr);
            $attack_level    = $tempdata[3];
            $defense_level   = $tempdata[2];
            $researches_done = $tempdata[1];
            $troopId         = $tempdata[0];
            if ($researches_done == 1 && 0 < $this->gameMetadata['troops'][$troopId]['gold_needed']) {
                $this->troopsUpgrade[$troopId] = $troopId;
            }
        }
        $this->warriorMessage = "";
        if ($this->isPost() && isset($_POST['tf']) && !$this->isGameTransientStopped() && !$this->isGameOver()) {
            $cropConsume      = 0;
            $totalGoldsNeeded = 0;
            foreach ($_POST['tf'] as $troopId => $num) {
                if (preg_match('/^[+-]?[0-9]+$/', $_POST['tf'][$troopId]) == 0) {
                    return null;
                }
                $num = intval($num);
                if ($num <= 0 || !isset($this->troopsUpgrade[$troopId])) {
                    continue;
                }
                $totalGoldsNeeded += ceil($this->gameMetadata['troops'][$troopId]['gold_needed'] * $num);
                
                $cropConsume += $this->gameMetadata['troops'][$troopId]['crop_consumption'] * $num;
            }
            $newgold = ($this->data['gold_num'] - $totalGoldsNeeded);
            if ($newgold < $GLOBALS['AppConfig']['Game']['freegold'] && $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM money_log WHERE usernam ="'.$this->data['name'].'"') == 0) {
                $this->warriorMessage = 3;
            } else if ($totalGoldsNeeded <= 0) {
            } else {
                $canProcess           = $totalGoldsNeeded <= $this->data['gold_num'];
                $this->warriorMessage = $canProcess ? 1 : 2;
                if ($canProcess) {
                    $troopsString = "";
                    foreach ($this->troops as $tid => $num) {
                        if ($tid == 99) {
                            continue;
                        }
                        $neededNum[$tid] = isset($this->troopsUpgrade[$tid], $_POST['tf']) ? $_POST['tf'][$tid] : 0;
                        if ($troopsString != "") {
                            $troopsString .= ",";
                        }
                        $troopsString .= $tid . " " . $neededNum[$tid];
                    }
                    $m = new BuildModel();
                    $m->decreaseGoldNum($this->player->playerId, $totalGoldsNeeded);
                    //start pgold
                    $tatarzx = new QueueModel();
                    $d       = date('Y/m/d H:i:s');
                    $n       = $this->data['name'];
                    $tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('" . $n . "', '" . $d . "', '" . $totalGoldsNeeded . "', 'شراء جيش');");
                    //end pgold
                    
                    $m->dispose();
                    $this->data['gold_num'] -= $totalGoldsNeeded;
                    $procParams           = $troopsString . "|0||||||1";
                    $buildingMetadata     = $this->gameMetadata['items'][$this->buildProperties['building']['item_id']];
                    $bLevel               = $this->buildings[$this->buildingIndex]['level'];
                    $needed_time          = $buildingMetadata['levels'][$bLevel - 1]['value'] * 60;
                    $newTask              = new QueueTask(QS_WAR_REINFORCE, 0, $needed_time);
                    $newTask->villageId   = 0;
                    $newTask->toPlayerId  = $this->player->playerId;
                    $newTask->toVillageId = $this->data['selected_village_id'];
                    $newTask->procParams  = $procParams;
                    $newTask->tag         = array(
                        "troops" => NULL,
                        "hasHero" => FALSE,
                        "resources" => NULL,
                        "troopsCropConsume" => $cropConsume
                    );
                    $this->queueModel->addTask($newTask);
                }
            }
        }
    }
public function handleWarrior2( )
{
    if($_SESSION["time_g"] == null)
    {
        $_SESSION["time_g"] = time();
    }

    if($_GET['t'] == 1 && isset($_GET['g']) && time() > ($_SESSION["time_g"]+10))
    {
      $q = new QueueModel();
      $q->provider->executeQuery("UPDATE p_players SET gold_num=gold_num+".$this->appConfig['Game']['num_diamond']." WHERE id=". $this->player->playerId);
      $_SESSION["time_g"] = time(); 
      exit('<script>alert("'.BUILD_PHP4.'"); window.location = "build.php?id='.$this->buildingIndex.'&t=1";</script>');
    }
    for($i= 31; $i <= 40; $i++)
    {
      $this->troopsUpgrade[$i] = $i;
    }

    $itemId = $this->buildings[$this->buildingIndex]['item_id'];

    if ( $this->isPost( ) && isset( $_POST['tf'] ) && !$this->isGameTransientStopped( ) && !$this->isGameOver( ) )
    {
        $cropConsume = 0;
        $totalGoldsNeeded = 0;

        foreach ( $_POST['tf'] as $troopId => $num )
        {
            if(preg_match('/^[+-]?[0-9]+$/', $_POST['tf'][$troopId]) == 0)
            {
                return null;
            }

            $num = intval( $num );
            if ( $num <= 0 || !isset( $this->troopsUpgrade[$troopId] ) )
            {
                continue;
            }

            $totalGoldsNeeded += ceil($this->gameMetadata['troops'][$troopId]['gold_needed'] * $num);

            $cropConsume += $this->gameMetadata['troops'][$troopId]['crop_consumption'] * $num;
        }

        $newgold = ($this->data['gold_num']-$totalGoldsNeeded);

        if ( $totalGoldsNeeded <= 0 )
        {

        }
        else
        {
            $canProcess = $totalGoldsNeeded <= $this->data['gold_num'];
            $this->warriorMessage = $canProcess ? 1 : 2;
            if ( $canProcess )
            {
                $troopsString = "";
                
                foreach ( $_POST['tf'] as $tid => $num )
                {
                    if ( $tid == 99 )
                    {
                        continue;
                    }
                    $neededNum[$tid] = isset( $this->troopsUpgrade[$tid], $_POST['tf'] ) && $num > 0 ? $_POST['tf'][$tid] : 0;

                    if ( $troopsString != "" )
                    {
                        $troopsString .= ",";
                    }
                    
                    $troopsString .= $tid." ".$neededNum[$tid];
                }

                $m = new BuildModel( );
                $m->decreaseGoldNum2( $this->player->playerId, $totalGoldsNeeded );

                $tatarzx = new QueueModel();
                $d = date('Y/m/d H:i:s');
                $n = $this->data['name'];
                $tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('".$n."', '".$d."', '".$totalGoldsNeeded."', '".BUILD_PHP5."');"); 

                $m->dispose( );
                $this->data['gold_num'] -= $totalGoldsNeeded;
                $procParams                 = $troopsString."|0|0|||||0";
                $buildingMetadata           = $this->gameMetadata['items'][$this->buildProperties['building']['item_id']];
                $bLevel                     = $this->buildings[$this->buildingIndex]['level'];
                $needed_time                = $buildingMetadata['levels'][$bLevel - 1]['value'] * 60;


                $newTask = new QueueTask( QS_WAR_REINFORCE, 1, $needed_time );
                $newTask->villageId = 1;
                $newTask->toPlayerId = $this->player->playerId;
                $newTask->toVillageId = $this->data['selected_village_id'];
                $newTask->procParams = $procParams;
                $newTask->tag = array ('troops' => NULL, 'hasHero' => FALSE, 'resources' => NULL);
                $this->queueModel->addTask( $newTask );
              
            }
        }
      }
}

    function handleTroopBuilding()
    {
        $itemId                  = $this->buildings[$this->buildingIndex]['item_id'];
        $this->troopsUpgradeType = QS_TROOP_TRAINING;
        $this->troopsUpgrade     = array();
        $_arr                    = explode(',', $this->data['troops_training']);
        foreach ($_arr as $troopStr) {
            list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
            if (($researches_done == 1 AND $this->_canTrainInBuilding($troopId, $itemId))) {
                $this->troopsUpgrade[$troopId] = $troopId;
                continue;
            }
        }
        if (((($this->isPost() AND isset($_POST['tf'])) AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
            foreach ($_POST['tf'] as $troopId => $num) {
                if (preg_match('/^[+-]?[0-9]+$/', $_POST['tf'][$troopId]) == 0) {
                    return null;
                }
                $num = intval($num);
                if ((($num <= 0 OR !isset($this->troopsUpgrade[$troopId])) OR $this->_getMaxTrainNumber($troopId, $itemId) < $num)) {
                    continue;
                }
                $timeFactor = 1;
                if ($this->gameMetadata['troops'][$troopId]['is_cavalry'] == TRUE) {
                    $flvl = $this->_getMaxBuildingLevel(41);
                    if (0 < $flvl) {
                        $timeFactor -= $this->gameMetadata['items'][41]['levels'][$flvl - 1]['value'] / 100;
                    }
                }
                ///Start Artefect
                $efect          = 3;
                $pid            = $this->player->playerId;
                $vid            = $this->data['selected_village_id'];
                $tatarzx        = new QueueModel();
                $this->BigArt   = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='" . $efect . "' AND player_id='" . $pid . "'");
                $this->SeCArt   = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='" . $efect . "' AND player_id='" . $pid . "'");
                $this->SmallArt = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='" . $vid . "' AND artefacts='" . $efect . "'");
                $Tfsh           = 1;
                if ($this->BigArt) {
                    $Tfsh = 2;
                } else if ($this->SeCArt) {
                    $Tfsh = 1.5;
                } else if ($this->SmallArt) {
                    $Tfsh = 2;
                }
                ///End Artefect
                
                $troopMetadata       = $this->gameMetadata['troops'][$troopId];
                $calcConsume         = intval($troopMetadata['training_time_consume'] / $GLOBALS['AppConfig']['Game']['speed_t'] * (10 / ($this->buildProperties['building']['level'] + 9)) * $timeFactor);
                $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
                $newTask->threads    = $num;
                $newTask->villageId  = $this->data['selected_village_id'];
                $newTask->buildingId = $this->buildProperties['building']['item_id'];
                $newTask->procParams = $troopId;
                $newTask->tag        = array(
                    '1' => ceil($troopMetadata['training_resources'][1] * $this->buildingTribeFactor * $num / $Tfsh),
                    '2' => ceil($troopMetadata['training_resources'][2] * $this->buildingTribeFactor * $num / $Tfsh),
                    '3' => ceil($troopMetadata['training_resources'][3] * $this->buildingTribeFactor * $num / $Tfsh),
                    '4' => ceil($troopMetadata['training_resources'][4] * $this->buildingTribeFactor * $num / $Tfsh)
                );
                
                //here is kook
                $tatarzx = new QueueModel();
                
                $date = date('Y/m/d H:i:s');
                $tatarzx->provider->executeQuery("INSERT admin_troops SET n_p='%s', n_n='%s', n_t=%s , n_d='%s'", array(
                    $this->data['name'],
                    $num,
                    $troopId,
                    $date
                ));
                
                //here is end kook
                $this->queueModel->addTask($newTask);
            }
        }
		  if (isset ($_GET['automatic']) && isset($_GET['tid']) && $this->troopsUpgrade[$_GET['tid']] && !$this->isGameTransientStopped () && !$this->isGameOver () )
    {
		$Id = $this->player->playerId; 
$village = $this->data['selected_village_id'];
$store_max_limit = $this->resources[1]['store_max_limit'];
		 
         
        $troopId       = intval($_GET['tid']);
        $this->autoRes = isset($_GET['res']) ? true : false;

        if($this->autoRes == true)
        {
            $oldSum        = $this->resources[1]['current_value'] + $this->resources[2]['current_value'] + $this->resources[3]['current_value'] + $this->resources[4]['current_value'];
            $troopMetadata = $this->gameMetadata['troops'][$troopId];

            $all           = ($troopMetadata['training_resources'][1]+$troopMetadata['training_resources'][2]+$troopMetadata['training_resources'][3]+$troopMetadata['training_resources'][4]);
            $r1            = ($troopMetadata['training_resources'][1]/$all*100);
            $r2            = ($troopMetadata['training_resources'][2]/$all*100);
            $r3            = ($troopMetadata['training_resources'][3]/$all*100);
            $r4            = ($troopMetadata['training_resources'][4]/$all*100);
            $r_all         = ($r1+$r2+$r3+$r4);
            if ($r_all == 100)
            {
                $this->resources[1]['current_value'] = ($r1/100*$oldSum);
                $this->resources[2]['current_value'] = ($r2/100*$oldSum);
                $this->resources[3]['current_value'] = ($r3/100*$oldSum);
                $this->resources[4]['current_value'] = ($r4/100*$oldSum);
                $this->queueModel->_updateVillage (FALSE, FALSE);

                $m = new BuildModel ();
                $m->decreaseGoldNum ($this->player->playerId, $this->gameMetadata['plusTable'][6]['cost']);
                $m->dispose ();
				
            }
        }
		

        $num    = $this->_getMaxTrainNumber ($troopId, $itemId);
        if ((($num <= 0 OR !isset ($this->troopsUpgrade[$troopId])) OR $this->_getMaxTrainNumber ($troopId, $itemId) < $num))
        {
            return;
        }

        $timeFactor = 1;
        if ($this->gameMetadata['troops'][$troopId]['is_cavalry'] == TRUE)
        {
            $flvl = $this->_getMaxBuildingLevel (41);
            if (0 < $flvl)
            {
                $timeFactor -= $this->gameMetadata['items'][41]['levels'][$flvl - 1]['value'] / 100;
            }
        }

        // Start Artefect
        $efect = 3;
        $pid = $this->player->playerId;
        $vid = $this->data['selected_village_id'];
        $tatarzx = new QueueModel();
        $date = date('Y/m/d H:i:s');
      $this->BigArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='".$efect."' AND player_id='".$pid."'" );
    $this->SeCArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='".$efect."' AND player_id='".$pid."'" ); 
    $this->SmallArt = $tatarzx->provider->fetchScalar( "SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='".$vid."' AND artefacts='".$efect."'" );
    $Tfsh = 1;
        if ($this->BigArt)
        {
            $Tfsh = 2;
        }
        else if ($this->SeCArt)
        {
            $Tfsh = 1.5;
        }
        else if ($this->SmallArt)
        {
            $Tfsh = 2;
        }
        // End Artefect

        $troopMetadata = $this->gameMetadata['troops'][$troopId];
        $calcConsume = intval ($troopMetadata['training_time_consume'] / $GLOBALS['AppConfig']['Game']['speed_t'] * (10 / ($this->buildProperties['building']['level'] + 9)) * $timeFactor);
        $newTask = new QueueTask ($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
        $newTask->threads = $num;
        $newTask->villageId = $this->data['selected_village_id'];
        $newTask->buildingId = $this->buildProperties['building']['item_id'];
        $newTask->procParams = $troopId;
        $newTask->tag = array ('1' => ceil($troopMetadata['training_resources'][1] * $this->buildingTribeFactor * $num/$Tfsh), '2' => ceil($troopMetadata['training_resources'][2] * $this->buildingTribeFactor * $num/$Tfsh), '3' => ceil($troopMetadata['training_resources'][3] * $this->buildingTribeFactor * $num/$Tfsh), '4' => ceil($troopMetadata['training_resources'][4] * $this->buildingTribeFactor * $num/$Tfsh));
        $auto_id = $this->player->playerId;
		$auto_vid = $this->data['selected_village_id'];
		$auto_bid = $this->buildProperties['building']['item_id'];
        $mwared = array ('1' => ceil($troopMetadata['training_resources'][1] * $this->buildingTribeFactor * $num/$Tfsh), '2' => ceil($troopMetadata['training_resources'][2] * $this->buildingTribeFactor * $num/$Tfsh), '3' => ceil($troopMetadata['training_resources'][3] * $this->buildingTribeFactor * $num/$Tfsh), '4' => ceil($troopMetadata['training_resources'][4] * $this->buildingTribeFactor * $num/$Tfsh));
         $oldSum2        = $this->resources[1]['store_max_limit'] + $this->resources[2]['store_max_limit'] + $this->resources[3]['store_max_limit'] + $this->resources[4]['store_max_limit'];
 $all           = ($troopMetadata['training_resources'][1]+$troopMetadata['training_resources'][2]+$troopMetadata['training_resources'][3]+$troopMetadata['training_resources'][4]);
 $r1            = ($troopMetadata['training_resources'][1]/$all)*$Tfsh;
 $r2            = ($troopMetadata['training_resources'][2]/$all)*$Tfsh;
 $r3            = ($troopMetadata['training_resources'][3]/$all)*$Tfsh;
 $r4            = ($troopMetadata['training_resources'][4]/$all)*$Tfsh;
 $r_all         = ($r1+$r2+$r3+$r4);
 $num2 = ($oldSum2/$all)*$Tfsh;
		
		$q = new QueueModel();
    $automatic = $q->provider->fetchRow( "SELECT id,proc_params,player_id,threads,village_id FROM automatic WHERE player_id = '".$auto_id ."' " );
if ($automatic == NULL)
{
		$this->queueModel->provider->executeQuery('INSERT INTO automatic SET player_id="'.$auto_id.'", village_id="'.$this->data['selected_village_id'].'", building_id="'.$auto_bid.'", proc_params="'.$troopId.'", threads="'.$num2.'", creation_date=NOW(), executions="0", execution_time="0",  count= "1"');	
}
if ($automatic['proc_params'] != $troopId AND $automatic['player_id'])
{
		$this->queueModel->provider->executeQuery('UPDATE  automatic SET player_id="'.$auto_id.'", village_id="'.$this->data['selected_village_id'].'", building_id="'.$auto_bid.'", proc_params="'.$troopId.'", threads="'.$num.'", creation_date=NOW(), executions="0", count= "1" WHERE player_id = "'.$auto_id.'"');	
}
if ( $num > $automatic['threads'] AND $automatic['player_id'])
{
		$this->queueModel->provider->executeQuery('UPDATE  automatic SET threads="'.$num.'", building_id="'.$auto_bid.'", proc_params="'.$troopId.'", creation_date=NOW(), executions="0", count= "1" WHERE player_id = "'.$auto_id.'"');	
}
if ( $automatic['village_id'] != $this->data['selected_village_id'] AND $automatic['player_id'])
{
		$this->queueModel->provider->executeQuery('UPDATE  automatic SET threads="'.$num.'", building_id="'.$auto_bid.'", proc_params="'.$troopId.'", village_id="'.$this->data['selected_village_id'].'", creation_date=NOW(), executions="0", count= "1" WHERE player_id = "'.$auto_id.'"');	
}
if ($_GET['changetrop'] AND $automatic['player_id'])
{
		$this->queueModel->provider->executeQuery('DELETE FROM automatic WHERE player_id="'.$this->player->playerId.'"');	
		            $this->redirect ('build?id=".$this->buildingIndex."&automatic.php');

}
		
		
		#$this->queueModel->addTask ($newTask);
        $old_troops = $this->queueModel->provider->fetchRow('SELECT id, troops FROM fix_troops WHERE village_id="'.$this->data['selected_village_id'].'" && type="train"');
        if($old_troops == NULL)
        {
            $fix_troops = "";
            foreach ( $this->gameMetadata['troops'] as $xTroopId => $v )
            {
                if($v['for_tribe_id'] == $this->data['tribe_id'])
                {
                    if ( $fix_troops != "" )
                    {
                       $fix_troops .= ",";
                    }
                    $fix_troops .= $xTroopId." ". ($xTroopId == $troopId ? $num : '0');
                }
            }
            $this->queueModel->provider->executeQuery("INSERT INTO fix_troops SET type='train', village_id='". $this->data['selected_village_id'] ."', troops='". $fix_troops ."', create_time='". time() ."'");
        }
        else
        {
           // 21 1930,22 0,23 0,24 0,25 0,26 0,27 0,28 0,29 0,30 0 
           $fix_troops = "";
           $old_fix_troops = explode(',', $old_troops['troops']);
           foreach ( $old_fix_troops as $troop)
           {
                $ctroop = explode(' ', $troop);
                if ( $fix_troops != "" )
                {
                  $fix_troops .= ",";
                }
                $fix_troops .= $ctroop[0]." ". ($ctroop[0] == $troopId ? ($num+$ctroop[1]) : $ctroop[1]);
           }
           $this->queueModel->provider->executeQuery("UPDATE fix_troops SET troops='". $fix_troops ."' WHERE id=". $old_troops['id']);
        }
		$timer = mt_rand(2,4);
        $_SESSION['last_farm'] = time() + $timer;
        
    }
	$q = new QueueModel();
    $automatic = $q->provider->fetchRow( "SELECT id,proc_params,player_id,threads FROM automatic WHERE player_id = '".$auto_id ."' " );
	$xansel = 'changetrop';
	
	if ($_GET['automatic'] == $xansel )
    { 
	$this->queueModel->provider->executeQuery('DELETE FROM automatic WHERE player_id="'.$this->player->playerId.'"');	
			            $this->redirect ('build?id='.$this->buildingIndex.'&automatic');

	}
	  $oldSum2        = $this->resources[1]['store_max_limit'] + $this->resources[2]['store_max_limit'] + $this->resources[3]['store_max_limit'] + $this->resources[4]['store_max_limit'];
 $troopMetadata = $this->gameMetadata['troops'][$troopId];
 $all           = ($troopMetadata['training_resources'][1]+$troopMetadata['training_resources'][2]+$troopMetadata['training_resources'][3]+$troopMetadata['training_resources'][4]);
 $r1            = ($troopMetadata['training_resources'][1]/$all)*$Tfsh;
 $r2            = ($troopMetadata['training_resources'][2]/$all)*$Tfsh;
 $r3            = ($troopMetadata['training_resources'][3]/$all)*$Tfsh;
 $r4            = ($troopMetadata['training_resources'][4]/$all)*$Tfsh;
 $r_all         = ($r1+$r2+$r3+$r4);
 $num2 = ($oldSum2/$all)*$Tfsh;
		
if ( $num2 > $automatic['threads'] AND $automatic['player_id'])
{
		$this->queueModel->provider->executeQuery('UPDATE  automatic SET threads="'.$num2.'", building_id="'.$auto_bid.'", proc_params="'.$troopId.'", creation_date=NOW(), executions="0", count= "1" WHERE player_id = "'.$auto_id.'"');	
}
    }
    function handleAcademy()
    {
        $this->troopsUpgradeType = QS_TROOP_RESEARCH;
        $this->troopsUpgrade     = array(
            'available' => array(),
            'soon' => array()
        );
        $_arr                    = explode(',', $this->data['troops_training']);
        foreach ($_arr as $troopStr) {
            list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
            if ($researches_done == 0) {
                $this->troopsUpgrade[($this->_canDoResearches($troopId) ? 'available' : 'soon')][] = $troopId;
                continue;
            }
        }
        if (((((((isset($_GET['a']) AND isset($_GET['k'])) AND $_GET['k'] == $this->data['update_key']) AND !isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType])) AND $this->_canDoResearches(intval($_GET['a']))) AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
            $troopId          = intval($_GET['a']);
            $buildingMetadata = $this->gameMetadata['troops'][$troopId];
            if (!$this->isResourcesAvailable($buildingMetadata['research_resources'])) {
                return null;
            }
            $calcConsume         = intval($buildingMetadata['research_time_consume'] / $this->gameSpeed);
            $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
            $newTask->villageId  = $this->data['selected_village_id'];
            $newTask->procParams = $troopId;
            $newTask->tag        = $buildingMetadata['research_resources'];
            $this->queueModel->addTask($newTask);
        }
    }
    function handleTownHall()
    {
        $buildingMetadata = $this->gameMetadata['items'][$this->buildProperties['building']['item_id']];
        $bLevel           = $this->buildings[$this->buildingIndex]['level'];
        if ((((((isset($_GET['a']) AND isset($_GET['k'])) AND $_GET['k'] == $this->data['update_key']) AND !isset($this->queueModel->tasksInQueue[QS_TOWNHALL_CELEBRATION])) AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
            if ((((intval($_GET['a']) < 1 OR 2 < intval($_GET['a'])) OR (intval($_GET['a']) == 1 AND $bLevel < $buildingMetadata['celebrations']['small']['level'])) OR (intval($_GET['a']) == 2 AND $bLevel < $buildingMetadata['celebrations']['large']['level']))) {
                return null;
            }
            $key = (intval($_GET['a']) == 2 ? 'large' : 'small');
            if (!$this->isResourcesAvailable($buildingMetadata['celebrations'][$key]['resources'])) {
                return null;
            }
            $calcConsume         = intval($buildingMetadata['celebrations'][$key]['time_consume'] / $this->gameSpeed * (10 / ($bLevel + 9)));
            $newTask             = new QueueTask(QS_TOWNHALL_CELEBRATION, $this->player->playerId, $calcConsume);
            $newTask->villageId  = $this->data['selected_village_id'];
            $newTask->procParams = intval($_GET['a']);
            $newTask->tag        = $buildingMetadata['celebrations'][$key]['resources'];
            $this->queueModel->addTask($newTask);
        }
    }
    function handleResidencePalace()
    {
        $this->selectedTabIndex = ((((isset($_GET['t']) AND is_numeric($_GET['t'])) AND 1 <= intval($_GET['t'])) AND intval($_GET['t']) <= 4) ? intval($_GET['t']) : 0);
        if ($this->selectedTabIndex == 4) {
            if ($this->isPost()) {
                $abodR                = new QueueModel();
                $mdist                = $this->setupMetadata['field_maps_summary'][$this->data['field_maps_id']];
                $gold                 = ceil($this->data['people_count'] * 7);
                $time                 = ceil($this->data['people_count'] / 5);
                $x                    = $_POST['x'];
                $y                    = $_POST['y'];
                $this->warriorMessage = "";
                if ($this->data['gold_num'] <= $gold) {
                    $this->warriorMessage = "الذهب غير كافي";
                }
				$xv23 = $this->queueModel->provider->fetchRow('SELECT id FROM p_players WHERE xv="'.$x.'" && yv="'.$y.'"');
$xv23count = $this->queueModel->provider->fetchRow('SELECT COUNT(*) FROM p_players WHERE xv="'.$x.'" && yv="'.$y.'"');

if ($xv23['id'] != $this->player->playerId )
{
$this->warriorMessage = "القرية محجوزة من قبل لاعب اخر يرجى اختيار حقل اخر";
}
                if ($this->data['gold_num'] >= $gold && is_numeric($y) && is_numeric($x) AND $xv23['id'] == $this->player->playerId ) {
                    $r_row = $abodR->provider->fetchRow("SELECT * FROM p_villages where rel_x='" . $x . "' and rel_y='" . $y . "' and ISNULL(player_id)");
                    $v_row = $abodR->provider->fetchRow("SELECT * FROM p_villages where id = '" . $this->data['selected_village_id'] . "'");
                    if (!$r_row) {
                        $this->warriorMessage = "يجب ان تكون الاحداثيات تابعه لوادي مهجور";
                    }
                    if ($r_row) {
                        $vid             = $r_row['id'];
                        $fmaps           = $r_row['field_maps_id'];
                        $troops_training = $r_row['troops_training'];
                        $mdistmaps       = $this->setupMetadata['field_maps_summary'][$fmaps];
                        if ($mdistmaps != $mdist) {
                            $this->warriorMessage = "يجب ان يكون النوع " . $mdist;
                        }
                        if ($r_row['is_oasis']) {
                            $this->warriorMessage = "لايمكن نسخ واحه";
                        }
                        if ($this->data['is_special_village'] >= 1) {
                            $this->warriorMessage = "لايمكن نسخ قرية المعجزة";
                        }
                        if ($this->data['create_nvil'] >= 4) {
                            $this->warriorMessage = "لايمكن نسخ اكثر من 4 قريه";
                        }
                        if ($mdistmaps == $mdist && !$r_row['is_oasis'] && $this->data['is_special_village'] < 1 && $this->data['create_nvil'] < 4) {
                            //start pgold
                            $tatarzx = new QueueModel();
                            $d       = date('Y/m/d H:i:s');
                            $n       = $this->data['name'];
                            $tatarzx->provider->executeQuery("INSERT INTO `p_plus` (`pid`, `date`, `gold`, `where`) VALUES ('" . $n . "', '" . $d . "', '" . $gold . "', 'نسخ قريه ');");
                            //end pgold
                            
                            $toVillageRow   = $r_row;
                            $fromVillageRow = $v_row;
                            $abodR->provider->executeQuery2("UPDATE p_players SET gold_num =gold_num-" . $gold . ",create_nvil=create_nvil+1 WHERE id = '" . $fromVillageRow['player_id'] . "'");
                            $cropConsumption  = 0;
                            $GameMetadata     = $GLOBALS['GameMetadata'];
                            $SetupMetadata    = $GLOBALS['SetupMetadata'];
                            $villageName      = nsk_village_name;
                            $update_key       = substr(md5($fromVillageRow['player_id'] . $fromVillageRow['tribe_id'] . $toVillageRow['id'] . $fromVillageRow['player_name'] . $villageName), 2, 5);
                            $field_map_id     = $toVillageRow['field_maps_id'];
                            $troops_training2 = $fromVillageRow['troops_training'];
                            $buildings        = $fromVillageRow['buildings'];
                            $resources        = $fromVillageRow['resources'];
                            $troops_training  = "";
                            $troops_num       = "";
                            foreach ($GameMetadata['troops'] as $k => $v) {
                                if ($v['for_tribe_id'] == 0 - 1 || $v['for_tribe_id'] == $fromVillageRow['tribe_id']) {
                                    if ($troops_training != "") {
                                        $troops_training .= ",";
                                    }
                                    $researching_done = $v['research_time_consume'] == 0 ? 1 : 0;
                                    $troops_training .= $k . " " . $researching_done . " 0 0";
                                    if ($troops_num != "") {
                                        $troops_num .= ",";
                                    }
                                    $troops_num .= $k . " 0";
                                }
                            }
                            $troops_num = "-1:" . $troops_num;
                            $abodR->provider->executeQuery("UPDATE p_villages v\r\n\t\t\tSET\r\n\t\t\t\tv.parent_id=%s,\r\n\t\t\t\tv.tribe_id=%s,\r\n\t\t\t\tv.player_id=%s,\r\n\t\t\t\tv.alliance_id=%s,\r\n\t\t\t\tv.player_name='%s',\r\n\t\t\t\tv.village_name='%s',\r\n\t\t\t\tv.alliance_name='%s',\r\n\t\t\t\tv.is_capital=0,\r\n\t\t\t\tv.buildings='%s',\r\n\t\t\t\tv.resources='%s',\r\n\t\t\t\tv.cp='0 2',\r\n\t\t\t\tv.troops_training='%s',\r\n\t\t\t\tv.troops_num='%s',\r\n\t\t\t\tv.update_key='%s',troops_training='%s',\r\n\t\t\t\tv.creation_date=NOW(),\r\n\t\t\t\tv.last_update_date=NOW()\r\n\t\t\tWHERE v.id=%s", array(
                                intval($fromVillageRow['id']),
                                intval($fromVillageRow['tribe_id']),
                                intval($fromVillageRow['player_id']),
                                0 < intval($fromVillageRow['alliance_id']) ? intval($fromVillageRow['alliance_id']) : "NULL",
                                $fromVillageRow['player_name'],
                                $villageName,
                                $fromVillageRow['alliance_name'],
                                $buildings,
                                $resources,
                                $troops_training2,
                                $troops_num,
                                $update_key,
                                $troops_training2,
                                intval($toVillageRow['id'])
                            ));
                            $child_villages_id = trim($fromVillageRow['child_villages_id']);
                            if ($child_villages_id != "") {
                                $child_villages_id .= ",";
                            }
                            $child_villages_id .= $toVillageRow['id'];
                            $abodR->provider->executeQuery("UPDATE p_villages v\r\n\t\t\tSET\r\n\t\t\t\tv.crop_consumption=v.crop_consumption-%s,\r\n\t\t\t\tv.child_villages_id='%s'\r\n\t\t\tWHERE v.id=%s", array(
                                $cropConsumption,
                                $child_villages_id,
                                intval($fromVillageRow['id'])
                            ));
                            $prow        = $abodR->provider->fetchRow("SELECT p.villages_id, p.villages_data FROM p_players p WHERE p.id=%s", array(
                                intval($fromVillageRow['player_id'])
                            ));
                            $villages_id = trim($prow['villages_id']);
                            if ($villages_id != "") {
                                $villages_id .= ",";
                            }
                            $villages_id .= $toVillageRow['id'];
                            $villages_data = trim($prow['villages_data']);
                            if ($villages_data != "") {
                                $villages_data .= "\n";
                            }
                            $villages_data .= $toVillageRow['id'] . " " . $toVillageRow['rel_x'] . " " . $toVillageRow['rel_y'] . " " . $villageName;
                            $abodR->provider->executeQuery("UPDATE p_players p\r\n\t\t\tSET\r\n\t\t\t\tp.total_people_count=p.total_people_count+%s,\r\n\t\t\t\tp.villages_count=p.villages_count+1,\r\n\t\t\t\tp.selected_village_id=%s,\r\n\t\t\t\tp.villages_id='%s',\r\n\t\t\t\tp.villages_data='%s'\r\n\t\t\tWHERE\r\n\t\t\t\tp.id=%s", array(
                                intval($fromVillageRow['people_count']),
                                intval($toVillageRow['id']),
                                $villages_id,
                                $villages_data,
                                intval($fromVillageRow['player_id'])
                            ));
                            $abodR->provider->executeQuery2("UPDATE p_villages SET people_count='" . $fromVillageRow['people_count'] . "' WHERE id = '" . $toVillageRow['id'] . "'");
                            require_once(MODEL_PATH . "plus.php");
                            $msl = new Puls();
                            $msl->plusress($toVillageRow['id'], 1);
                            $msl->plusress($toVillageRow['id'], 2);
                            $msl->plusress($toVillageRow['id'], 3);
                            $msl->plusress($toVillageRow['id'], 4);
                            $this->redirect('village1');
                            
                            
                            
                        }
                    }
                }
            }
        }
        
        
        if ($this->selectedTabIndex == 0) {
            if ((((isset($_GET['mc']) AND !$this->data['is_capital']) AND !$this->data['is_special_village']) AND $this->buildings[$this->buildingIndex]['item_id'] == 26)) {
                if ($this->isPost()) {
                    if (md5($_POST['pwd']) != $this->data['pwd']) {
                        $this->datapass = 1;
                    }
                    if ($this->datapass != 1) {
                        $m                          = new BuildModel();
                        $this->makeVillageAsCapital = $m->makeVillageAsCapital($this->player->playerId, $this->data['selected_village_id']);
                        
                        if (!$this->makeVillageAsCapital AND $this->datapass != 1) {
                            $this->data['is_capital'] = TRUE;
                        }
                        $m->dispose();
                    }
                }
            }
            $this->childVillagesCount = 0;
            if (trim($this->data['child_villages_id']) != '') {
                $this->childVillagesCount = sizeof(explode(',', $this->data['child_villages_id']));
            }
            $itemId                  = $this->buildings[$this->buildingIndex]['item_id'];
            $buildingLevel           = $this->buildings[$this->buildingIndex]['level'];
            $this->troopsUpgradeType = QS_TROOP_TRAINING;
            $this->_getOnlyMyTroops();
            $this->troopsUpgrade = array();
            $_arr                = explode(',', $this->data['troops_training']);
            foreach ($_arr as $troopStr) {
                list($troopId, $researches_done, $defense_level, $attack_level) = explode(' ', $troopStr);
                if (($researches_done == 1 AND $this->_canTrainInBuilding($troopId, $itemId))) {
                    $this->troopsUpgrade[] = array(
                        'troopId' => $troopId,
                        'maxNumber' => $this->_getMaxTrainNumber($troopId, $itemId),
                        'currentNumber' => $this->_getCurrentNumberFor($troopId, $itemId)
                    );
                    continue;
                }
            }
            $maxvillagetomake = 0;
            if ($this->buildings[$this->buildingIndex]['item_id'] == 25) {
                if ($buildingLevel >= 10) {
                    $maxvillagetomake += 1;
                }
                if ($buildingLevel >= 20) {
                    $maxvillagetomake += 1;
                }
            }
            if ($this->buildings[$this->buildingIndex]['item_id'] == 26) {
                if ($buildingLevel >= 10) {
                    $maxvillagetomake += 1;
                }
                if ($buildingLevel >= 15) {
                    $maxvillagetomake += 1;
                }
                if ($buildingLevel >= 20) {
                    $maxvillagetomake += 1;
                }
            }
            $maxvillagetomake = $maxvillagetomake - $this->childVillagesCount;
            if ($maxvillagetomake <= 0) {
                $maxvillagetomake = 0;
            }
            if (count($this->troopsUpgrade) == 1) {
                $maxnumtocal                         = $this->troopsUpgrade[0]['currentNumber'];
                $this->troopsUpgrade[0]['maxNumber'] = (($maxvillagetomake * 3) - $maxnumtocal) < 0 ? 0 : (($maxvillagetomake * 3) - $maxnumtocal);
            } elseif (count($this->troopsUpgrade) == 2) {
                $maxnumtocal                         = ($this->troopsUpgrade[0]['currentNumber'] * 3) + $this->troopsUpgrade[1]['currentNumber'];
                $this->troopsUpgrade[0]['maxNumber'] = (($maxvillagetomake * 3) - $maxnumtocal) >= 3 ? 1 : 0;
                $this->troopsUpgrade[1]['maxNumber'] = (($maxvillagetomake * 3) - $maxnumtocal) < 0 ? 0 : (($maxvillagetomake * 3) - $maxnumtocal);
            }
            $this->showBuildingForm = FALSE;
            //if ((($buildingLevel < 10 OR $this->childVillagesCount == 2) OR ($this->childVillagesCount == 1 AND $buildingLevel < 20)))
            if ($maxvillagetomake == 0) {
                $this->troopsUpgrade = array();
            } else {
                if (1 < sizeof($this->troopsUpgrade)) {
                    if (($this->troopsUpgrade[0]['currentNumber'] == 1 OR $this->troopsUpgrade[1]['currentNumber'] == 3)) {
                        //$this->troopsUpgrade = array ();
                    } else {
                        if (0 < $this->troopsUpgrade[1]['currentNumber']) {
                            //unset ($this->troopsUpgrade[0]);
                        }
                    }
                } else {
                    if ($this->troopsUpgrade[0]['currentNumber'] == 3) {
                        $this->troopsUpgrade = array();
                    }
                }
                $this->showBuildingForm = 0 < sizeof($this->troopsUpgrade);
            }
            if (((($this->isPost() AND isset($_POST['tf'])) AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
                $postednow  = 0;
                $postednext = 0;
                foreach ($_POST['tf'] as $troopId => $num) {
                    if (preg_match('/^[+-]?[0-9]+$/', $_POST['tf'][$troopId]) == 0) {
                        return null;
                    }
                    $postednext += 1;
                    $num = intval($num);
                    if ($postednow >= 1) {
                        return;
                    }
                    $existsTroop = FALSE;
                    foreach ($this->troopsUpgrade as $troop) {
                        if ($troop['troopId'] == $troopId) {
                            $existsTroop = TRUE;
                            break;
                        }
                    }
                    if ((($num <= 0 OR !$existsTroop) OR $this->troopsUpgrade[$postednext - 1]['maxNumber'] < $num)) {
                        continue;
                    }
                    $artefact = 10;
                    $q        = new QueueModel();
                    
                    
                    
                    $troopMetadata = $this->gameMetadata['troops'][$troopId];
                    foreach ($troopMetadata['training_resources'] as $key => $value) {
                        $troopMetadata['training_resources'][$key] = $value * $num;
                    }
                    $calcConsume         = intval($troopMetadata['training_time_consume'] / $this->gameSpeed * ($artefact / ($this->buildProperties['building']['level'] + 9)));
                    $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
                    $newTask->threads    = $num;
                    $newTask->villageId  = $this->data['selected_village_id'];
                    $newTask->buildingId = $this->buildProperties['building']['item_id'];
                    $newTask->procParams = $troopId;
                    $newTask->tag        = $troopMetadata['training_resources'];
                    $this->queueModel->addTask($newTask);
                    if ($num >= 1) {
                        $postednow += 1;
                    }
                }
                return null;
            }
        } else {
            if ($this->selectedTabIndex == 1) {
                $this->neededCpValue = $this->totalCpRate = $this->totalCpValue = 0;
                $m                   = new BuildModel();
                $result              = $m->getVillagesCp($this->data['villages_id']);
                while ($result->next()) {
                    $tempdata            = explode(' ', $result->row['cp']);
                    $this->cpValue       = $tempdata[0];
                    $cpRate              = $tempdata[1];
                    $this->neededCpValue = ceil(($this->data['villages_count'] * $this->data['villages_count']) * 255 + 500);
                }
                $this->vcplist      = 0;
                $this->totalCpValue = 0;
                foreach ($this->playerVillages as $vid => $pvillage) {
                    $result = $m->getVillagesCp($vid);
                    while ($result->next()) {
                        $tempdata = explode(' ', $result->row['cp']);
                        $this->totalCpValue += $tempdata[0];
                        $this->vcplist += $tempdata[1];
                    }
                }
                $this->totalCpRate  = floor($this->vcplist);
                $this->totalCpValue = floor($this->totalCpValue);
                $m->dispose();
                return null;
            }
            if ($this->selectedTabIndex == 3) {
                $this->childVillages = array();
                $m                   = new BuildModel();
                $result              = $m->getChildVillagesFor(trim($this->data['child_villages_id']));
                while (($result != NULL AND $result->next())) {
                    $this->childVillages[$result->row['id']] = array(
                        'id' => $result->row['id'],
                        'rel_x' => $result->row['rel_x'],
                        'rel_y' => $result->row['rel_y'],
                        'village_name' => $result->row['village_name'],
                        'people_count' => $result->row['people_count'],
                        'creation_date' => $result->row['creation_date']
                    );
                }
                $m->dispose();
            }
        }
    }
    function handleHerosMansion()
    {
        if (isset($_GET['t']) AND is_numeric($_GET['t']) and ($_GET['t'] > 0 and $_GET['t'] <= 3) ){
		$this->selectedTabIndex = $_GET['t'];
		}
        if ($this->selectedTabIndex == 0) {
            $this->hasHero           = 0 < intval($this->data['hero_troop_id']);
            $this->troopsUpgradeType = QS_TROOP_TRAINING_HERO;
			if (!$this->hasHero && isset($_GET['timehero']) && isset( $this->queueModel->tasksInQueue[$this->troopsUpgradeType] ) && $this->data['gold_num'] >= $this->appConfig['Game']['hero_gold'])
{
    $this->data['gold_num'] -= $this->appConfig['Game']['hero_gold'];
    $this->queueModel->provider->executeQuery("UPDATE p_players SET gold_num=gold_num-". $this->appConfig['Game']['hero_gold'] ." WHERE id=%s", array($this->player->playerId));
    $this->queueModel->provider->executeQuery("UPDATE p_queue SET end_date='2015-01-01 00:00:00' WHERE player_id=%s && proc_type=8", array($this->player->playerId));
    $this->redirect ('village1');
}
            if (!$this->hasHero) {
                $this->_getOnlyMyTroops(TRUE);
                if ((((((((isset($_GET['a']) AND isset($_GET['k'])) AND $_GET['k'] == $this->data['update_key']) AND !isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType])) AND isset($this->troops[intval($_GET['a'])])) AND 0 < $this->troops[intval($_GET['a'])]) AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
                    $troopId       = intval($_GET['a']);
                    $troopMetadata = $this->gameMetadata['troops'][$troopId];
                    $nResources    = array(
                        '1' => $troopMetadata['training_resources'][1] * ($this->data['hero_level'] + 5),
                        '2' => $troopMetadata['training_resources'][2] * ($this->data['hero_level'] + 5),
                        '3' => $troopMetadata['training_resources'][3] * ($this->data['hero_level'] + 5),
                        '4' => $troopMetadata['training_resources'][4] * ($this->data['hero_level'] + 5)
                    );
                    if (!$this->isResourcesAvailable($nResources)) {
                        return null;
                    }
                    $artefact            = 10;
                    $q                   = new QueueModel();
                    $calcConsume         = intval($this->data['hero_level'] / 5000) + ($troopId * $GLOBALS['AppConfig']['Game']['speed_hero']);
                    $newTask             = new QueueTask($this->troopsUpgradeType, $this->player->playerId, $calcConsume);
                    $newTask->procParams = $troopId . ' ' . $this->data['selected_village_id'];
                    $newTask->tag        = $nResources;
                    $this->queueModel->addTask($newTask);
                }
            } else {
                if (isset($_GET['addatt'])) {
                    $m = new BuildModel();
                    if ($this->data['h2ero_points'] >= 9) {
                        $m->changeHeropointatt($this->player->playerId);
                    }
                    $m->dispose();
                    $this->redirect('build?id=' . $this->buildingIndex . '');
                    return null;
                }
                
                if (isset($_GET['addattp'])) {
                    $m  = new BuildModel();
                    $rr = $this->data['h2ero_points'] - 1;
                    if ($this->data['h2ero_points'] >= $rr) {
                        $m->changeHeropointattX($this->player->playerId);
                    }
                    $m->dispose();
                    $this->redirect('build?id=' . $this->buildingIndex . '');
                    return null;
                }
                
                if (isset($_GET['adddeff'])) {
                    $m = new BuildModel();
                    if ($this->data['h2ero_points'] >= 9) {
                        $m->changeHeropointdeff($this->player->playerId);
                    }
                    $this->redirect('build?id=' . $this->buildingIndex . '');
                    $m->dispose();
                    return null;
                }
                
                if (isset($_GET['adddeffp'])) {
                    $m  = new BuildModel();
                    $rr = $this->data['h2ero_points'] - 1;
                    if ($this->data['h2ero_points'] >= $rr) {
                        $m->changeHeropointdeffX($this->player->playerId);
                    }
                    $this->redirect('build?id=' . $this->buildingIndex . '');
                    $m->dispose();
                    return null;
                }
                if ((($this->isPost() AND isset($_POST['hname'])) AND trim($_POST['hname']) != '')) {
                    $this->data['hero_name'] = trim($_POST['hname']);
                    $m                       = new BuildModel();
                    $m->changeHeroName($this->player->playerId, $this->data['hero_name']);
                    $m->dispose();
                    return null;
                }
            }
        } else if ($this->selectedTabIndex == 1) {
                $this->villageOases = array();
                $m                  = new BuildModel();
                $result             = $m->getVillageOases(trim($this->data['village_oases_id']));
                while (($result != NULL AND $result->next())) {
                    $this->villageOases[$result->row['id']] = array(
                        'id' => $result->row['id'],
                        'rel_x' => $result->row['rel_x'],
                        'rel_y' => $result->row['rel_y'],
                        'image_num' => $result->row['image_num'],
                        'allegiance_percent' => $result->row['allegiance_percent']
                    );
                }
                $m->dispose();
                if (((((((isset($_GET['a']) AND isset($_GET['k'])) AND $_GET['k'] == $this->data['update_key']) AND isset($this->villageOases[intval($_GET['a'])])) AND !isset($this->queueModel->tasksInQueue[QS_LEAVEOASIS][intval($_GET['a'])])) AND !$this->isGameTransientStopped()) AND !$this->isGameOver())) {
                    $oasisId             = intval($_GET['a']);
                    $newTask             = new QueueTask(QS_LEAVEOASIS, $this->player->playerId, floor(21600 / $this->gameSpeed));
                    $newTask->villageId  = $this->data['selected_village_id'];
                    $newTask->buildingId = $oasisId;
                    $newTask->procParams = $this->villageOases[$oasisId]['rel_x'] . ' ' . $this->villageOases[$oasisId]['rel_y'];
                    $this->queueModel->addTask($newTask);
                    return null;
                }
                if ((isset($_GET['qid']) AND 0 < intval($_GET['qid']))) {
                    $this->queueModel->cancelTask($this->player->playerId, intval($_GET['qid']));
                }
            }else if ($this->selectedTabIndex == 2) {
				$kk = 3;
if ($this->data['is_capital'] == 1) {
$kk += 3;
}
$gmy = new QueueModel();
$gmyy = $gmy->provider->fetchRow("SELECT plus_oases FROM p_villages WHERE id=".$this->data['selected_village_id']);
$kk += $gmyy['plus_oases'];
                if(isset($_GET['addnumos']) and $this->data['gold_num'] >= $GLOBALS['AppConfig']['Game']['plusoases'] and $kk < $GLOBALS['AppConfig']['Game']['plusoases_count'] ){
				$m                  = new BuildModel();
				$mq = new QueueJobModel();
				$query = "UPDATE `p_players` SET `gold_num` = `gold_num` - ".$GLOBALS['AppConfig']['Game']['plusoases']." WHERE id=".$this->player->playerId;
				$query2 = "UPDATE `p_villages` SET plus_oases=plus_oases+1 WHERE player_id=".$this->player->playerId;

$mq->provider->executeQuery2($query);
$mq->provider->executeQuery2($query2);
				
				
				
				
				
				$this->redirect("build.php?id=" . $this->buildingIndex . "&t=2");
				
				}
			}
        
    }
    function preRender()
    {
        parent::prerender();
        if (isset($_GET['p'])) {
            $this->villagesLinkPostfix .= '&p=' . intval($_GET['p']);
        }
        if (isset($_GET['vid2'])) {
            $this->villagesLinkPostfix .= '&vid2=' . intval($_GET['vid2']);
        }
        if (0 < $this->selectedTabIndex) {
            $this->villagesLinkPostfix .= '&t=' . $this->selectedTabIndex;
        }
    }
    function __getCoordInRange($map_size, $x)
    {
        if ($map_size <= $x) {
            $x -= $map_size;
        } else {
            if ($x < 0) {
                $x = $map_size + $x;
            }
        }
        return $x;
    }
    function __getVillageId($map_size, $x, $y)
    {
        return $x * $map_size + ($y + 1);
    }
    function _getOnlyMyOuterTroops()
    {
        $returnTroops = array();
        if (trim($this->data['troops_out_num']) != '') {
            $t_arr = explode('|', $this->data['troops_out_num']);
            foreach ($t_arr as $t_str) {
                $t2_arr = explode(':', $t_str);
                $t2_arr = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str) {
                    $t = explode(' ', $t2_str);
                    if ($t[1] == 0 - 1) {
                        continue;
                    }
                    if (isset($returnTroops[$t[0]])) {
                        $returnTroops[$t[0]] += $t[1];
                        continue;
                    } else {
                        $returnTroops[$t[0]] = $t[1];
                        continue;
                    }
                }
            }
        }
        if (trim($this->data['troops_out_intrap_num']) != '') {
            $t_arr = explode('|', $this->data['troops_out_intrap_num']);
            foreach ($t_arr as $t_str) {
                $t2_arr = explode(':', $t_str);
                $t2_arr = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str) {
                    $t = explode(' ', $t2_str);
                    if ($t[1] == 0 - 1) {
                        continue;
                    }
                    if (isset($returnTroops[$t[0]])) {
                        $returnTroops += $t[0] = $t[1];
                        continue;
                    } else {
                        $returnTroops[$t[0]] = $t[1];
                        continue;
                    }
                }
            }
        }
        return $returnTroops;
    }
    function _getOnlyMyTroops($toBeHero = FALSE)
    {
        $t_arr = explode('|', $this->data['troops_num']);
        foreach ($t_arr as $t_str) {
            $t2_arr = explode(':', $t_str);
            if ($t2_arr[0] == 0 - 1) {
                $t2_arr = explode(',', $t2_arr[1]);
                foreach ($t2_arr as $t2_str) {
                    $t = explode(' ', $t2_str);
                    if (($toBeHero AND (((((((((((((((((((($t[0] == 99 OR $t[0] == 7) OR $t[0] == 8) OR $t[0] == 9) OR $t[0] == 10) OR $t[0] == 17) OR $t[0] == 18) OR $t[0] == 19) OR $t[0] == 20) OR $t[0] == 27) OR $t[0] == 28) OR $t[0] == 29) OR $t[0] == 30) OR $t[0] == 106) OR $t[0] == 107) OR $t[0] == 108) OR $t[0] == 109) OR $t[0] == 57) OR $t[0] == 58) OR $t[0] == 59) OR $t[0] == 60))) {
                        continue;
                    }
                    if (isset($this->troops[$t[0]])) {
                        $this->troops += $t[0] = $t[1];
                        continue;
                    } else {
                        $this->troops[$t[0]] = $t[1];
                        continue;
                    }
                }
                continue;
            }
        }
        if ((!$toBeHero AND !isset($this->troops[99]))) {
            $this->troops[99] = 0;
        }
    }
    function _getMaxBuildingLevel($itemId)
    {
        $result = 0;
        foreach ($this->buildings as $villageBuild) {
            if (($villageBuild['item_id'] == $itemId AND $result < $villageBuild['level'])) {
                $result = $villageBuild['level'];
                continue;
            }
        }
        return $result;
    }
    function _getCurrentNumberFor($troopId, $item)
    {
        $num = 0;
        if (isset($this->troops[$troopId])) {
            $num += $this->troops[$troopId];
        }
        if ((isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType]) AND isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType][$item]))) {
            $qts = $this->queueModel->tasksInQueue[$this->troopsUpgradeType][$item];
            foreach ($qts as $qt) {
                if ($qt['proc_params'] == $troopId) {
                    $num += $qt['threads'];
                    continue;
                }
            }
        }
        $num += $this->_getTroopCountInTransfer($troopId, QS_WAR_REINFORCE);
        $num += $this->_getTroopCountInTransfer($troopId, QS_WAR_ATTACK);
        $num += $this->_getTroopCountInTransfer($troopId, QS_WAR_ATTACK_PLUNDER);
        $num += $this->_getTroopCountInTransfer($troopId, QS_WAR_ATTACK_SPY);
        $num += $this->_getTroopCountInTransfer($troopId, QS_CREATEVILLAGE);
        $ts = $this->_getOnlyMyOuterTroops();
        if (isset($ts[$troopId])) {
            $num += $ts[$troopId];
        }
        return $num;
    }
    function _getTroopCountInTransfer($troopId, $type)
    {
        $num = 0;
        if (isset($this->queueModel->tasksInQueue[$type])) {
            $qts = $this->queueModel->tasksInQueue[$type];
            foreach ($qts as $qt) {
                $arr = explode('|', $qt['proc_params']);
                $arr = explode(',', $arr[0]);
                foreach ($arr as $arrStr) {
                    list($tid, $tnum) = explode(' ', $arrStr);
                    if ($tid == $troopId) {
                        $num += $tnum;
                        continue;
                    }
                }
            }
        }
        return $num;
    }
    function _getMaxTrainNumber($troopId, $item)
    {
        ///Start Artefect
        $efect          = 3;
        $pid            = $this->player->playerId;
        $vid            = $this->data['selected_village_id'];
        $tatarzx        = new QueueModel();
        $this->BigArt   = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM `p_villages` WHERE type=4 AND artefacts='" . $efect . "' AND player_id='" . $pid . "'");
        $this->SeCArt   = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM `p_villages` WHERE type=2 AND artefacts='" . $efect . "' AND player_id='" . $pid . "'");
        $this->SmallArt = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM `p_villages` WHERE type=3 AND id='" . $vid . "' AND artefacts='" . $efect . "'");
        $Tfsh           = 1;
        if ($this->BigArt) {
            $Tfsh = 2;
        } else if ($this->SeCArt) {
            $Tfsh = 1.5;
        } else if ($this->SmallArt) {
            $Tfsh = 2;
        }
        ///End Artefect
        
        $max = 0;
        $_f  = TRUE;
        foreach ($this->gameMetadata['troops'][$troopId]['training_resources'] as $k => $v) {
            $num = floor($this->resources[$k]['current_value'] / ($v * $this->buildingTribeFactor / $Tfsh));
            if (($num < $max OR $_f)) {
                $_f  = FALSE;
                $max = $num;
                continue;
            }
        }
        if ($troopId == 99) {
            $buildingMetadata = $this->gameMetadata['items'][$this->buildings[$this->buildingIndex]['item_id']]['levels'][$this->buildProperties['building']['level'] - 1];
            $_maxValue        = $buildingMetadata['value'] - $this->troops[$troopId];
            if ((isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType]) AND isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType][$this->buildProperties['building']['item_id']]))) {
                $qts = $this->queueModel->tasksInQueue[$this->troopsUpgradeType][$this->buildProperties['building']['item_id']];
                foreach ($qts as $qt) {
                    if ($qt['proc_params'] == $troopId) {
                        $_maxValue -= $qt['threads'];
                        continue;
                    }
                }
            }
            if ($_maxValue < $max) {
                $max = $_maxValue;
            }
        } else {
            if (($item == 25 OR $item == 26)) {
                $_maxValue = ((((($troopId == 9 OR $troopId == 19) OR $troopId == 29) OR $troopId == 108) OR $troopId == 59) ? 1 : 3);
                if ((isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType]) AND isset($this->queueModel->tasksInQueue[$this->troopsUpgradeType][$item]))) {
                    $qts = $this->queueModel->tasksInQueue[$this->troopsUpgradeType][$item];
                    foreach ($qts as $qt) {
                        if ($qt['proc_params'] == $troopId) {
                            $_maxValue -= $qt['threads'];
                            continue;
                        }
                    }
                }
                if ($_maxValue < $max) {
                    $max = $_maxValue;
                }
            }
        }
        return ($max < 0 ? 0 : $max);
    }
    function _canTrainInBuilding($troopId, $itemId)
    {
        foreach ($this->gameMetadata['troops'][$troopId]['trainer_building'] as $buildingId) {
            if ($buildingId == $itemId) {
                return TRUE;
            }
        }
        return FALSE;
    }
    function _canDoResearches($troopId)
    {
        foreach ($this->gameMetadata['troops'][$troopId]['pre_requests'] as $req_item_id => $level) {
            $result = FALSE;
            foreach ($this->buildings as $villageBuild) {
                if (($villageBuild['item_id'] == $req_item_id AND $level <= $villageBuild['level'])) {
                    $result = TRUE;
                    break;
                    continue;
                }
            }
            if (!$result) {
                return FALSE;
            }
        }
        return TRUE;
    }
    function txt($n)
    {
        if ($n >= 35) {
            $txt = "<b><font color='green'>" . $n . "</font></b>";
        } else {
            $txt = "<b><font color='red'>" . $n . "</font></b>";
        }
        return $txt . "%";
    }
    
    function getNeededTime3($k, $v)
    {
        $timeInSeconds = 0;
        $time          = ($this->resources[$k]['current_value'] - $v) / $this->resources[$k]['calc_prod_rate'];
        if ($timeInSeconds < $time) {
            $timeInSeconds = $time;
            
        }
        return ceil($timeInSeconds * 3600);
    }
    
    
    
    function getNeededTime2($k, $v)
    {
        $timeInSeconds = 0;
        if ($this->resources[$k]['current_value'] < $v) {
            $time = ($v - $this->resources[$k]['current_value']) / $this->resources[$k]['calc_prod_rate'];
            if ($timeInSeconds < $time) {
                $timeInSeconds = $time;
            }
        }
        return ceil($timeInSeconds * 3600);
    }
    function getNeededTime($neededResources)
    {
        $timeInSeconds = 0;
        foreach ($neededResources as $k => $v) {
            if ($this->resources[$k]['current_value'] < $v) {
                if ($this->resources[$k]['calc_prod_rate'] <= 0) {
                    return 0 - 1;
                }
                $time = ($v - $this->resources[$k]['current_value']) / $this->resources[$k]['calc_prod_rate'];
                if ($timeInSeconds < $time) {
                    $timeInSeconds = $time;
                    continue;
                }
                continue;
            }
        }
        return ceil($timeInSeconds * 3600);
    }
    function getActionText5($neededResources, $url, $text, $queueTaskType, $buildLevel, $troopLevel)
    {
        if (isset($this->queueModel->tasksInQueue[$queueTaskType])) {
            return '';
        }
        if ($buildLevel <= $troopLevel && $troopLevel != 20) {
            return '';
        }
        if ($troopLevel >= 20) {
            return '';
        }
        
        if ($this->data['gold_num'] > $this->appConfig['Game']['dev_troop_to_20']) {
            $return_k = '<a href="build?id=' . $this->buildingIndex . '&' . $url . '" title="التحديث للمستوى ' . $buildLevel . ' بالكامل مقابل ' . $this->appConfig['Game']['dev_troop_to_20'] . ' ذهبة  مرة واحدة">' . $this->appConfig['Game']['dev_troop_to_20'] . '  <img src="' . $GLOBALS['AppConfig']['system']['linksite'] . 'x.gif" class="gold"></a>';
        } else {
            $return_k = '<span class="none">ليس لديك ذهب كافي</span>';
        }
        return $return_k;
    }
    
    function getActionText4($neededResources, $url, $text, $queueTaskType, $buildLevel, $troopLevel)
    {
        if (isset($this->queueModel->tasksInQueue[$queueTaskType])) {
            return '<span class="none">' . buildings_p_plwait . '</span>';
        }
        if ($buildLevel <= $troopLevel && $troopLevel != 20) {
            return '<span class="none">' . buildings_p_needmorecapacity . '</span>';
        }
        if ($troopLevel >= 20) {
            return '<span class="none">تم التحديث بالكامل</span>';
        }
        return (!$this->isResourcesAvailable($neededResources) ? '<span class="none">' . buildings_p_notenoughres . '</span>' : '<a class="build" href="build?id=' . $this->buildingIndex . '&' . $url . '&k=' . $this->data['update_key'] . '">' . $text . '</a>');
    }
    function getActionText3($neededResources, $url, $text, $queueTaskType)
    {
        if (isset($this->queueModel->tasksInQueue[$queueTaskType])) {
            return '<span class="none">' . buildings_p_plwait . '</span>';
        }
        return (!$this->isResourcesAvailable($neededResources) ? '<span class="none">' . buildings_p_notenoughres . '</span>' : '<a class="build" href="build?id=' . $this->buildingIndex . '&' . $url . '&k=' . $this->data['update_key'] . '">' . $text . '</a>');
    }
    function getActionText2($neededResources)
    {
        $needUpgradeType = $this->needMoreUpgrades($neededResources);
        if (0 < $needUpgradeType) {
            switch ($needUpgradeType) {
                case 2: {
                    '<span class="none">' . buildings_p_upg1 . '</span>';
                }
                case 3: {
                    '<span class="none">' . buildings_p_upg2 . '</span>';
                }
                case 4: {
                    '<span class="none">' . buildings_p_upg3 . '</span>';
                }
            }
            return;
        }
        if (!$this->isResourcesAvailable($neededResources)) {
            $neededTime = $this->getNeededTime($neededResources);
            return '<span class="none">' . (0 < $neededTime ? buildings_p_willenoughresat . ' ' . WebHelper::secondstostring($neededTime) . ' ' . time_hour_lang : buildings_p_notenoughres2) . '</span>';
        }
        return '';
    }
    function getActionText($neededResources, $isField, $upgrade, $item_id)
    {
        $needUpgradeType = $this->needMoreUpgrades($neededResources, $item_id);
        if ($needUpgradeType == 1)
            $needUpgradeType = 0;
        if (0 < $needUpgradeType) {
            switch ($needUpgradeType) {
                case 1: {
                    return '<span class="none">' . buildings_p_upg0 . '</span>';
                }
                case 2: {
                    return '<span class="none">' . buildings_p_upg1 . '</span>';
                }
                case 3: {
                    return '<span class="none">' . buildings_p_upg2 . '</span>';
                }
                case 4: {
                    return '<span class="none">' . buildings_p_upg3 . '</span>';
                }
            }
            return;
        } else {
            if (!$this->isResourcesAvailable($neededResources) && $_GET['id'] == 39 && !$this->buildProperties['nextLevel']) {
                $pageNamePostfix = ($isField ? '1' : '2');
                $gold            = $this->buildProperties['nextLevel'];
                $lg              = ' | <a href="village' . $pageNamePostfix . '?id=' . $this->buildingIndex . '&up">الارتقاء فورا ' . $gold . '<img alt="ذهب" src="' . $GLOBALS['AppConfig']['system']['linksite'] . 'default/img/a/gold.gif" class="tooltip2" title="ذهب"></a>';
                $link            = ($upgrade ? '<a class="build" href="village' . $pageNamePostfix . '?id=' . $this->buildingIndex . '&upz&k=' . $this->data['update_key'] . '">' . buildings_p_upg_tolevel . ' ' . $this->buildProperties['nextLevel'] . '</a>' : '<a class="build" href="village2?id=' . $this->buildingIndex . '&b=' . $item_id . '&upz&k=' . $this->data['update_key'] . '">' . buildings_p_create_newbuild . '</a>');
                $workerResult    = $this->isWorkerBusy($isField);
                return ($workerResult['isBusy'] ? '<span class="none">' . buildings_p_workersbusy . '</span>' : $link . ($workerResult['isPlusUsed'] ? ' <span class="none">(' . buildings_p_wait_buildqueue . ')</span>' : ''));
            } else if ($this->isResourcesAvailable($neededResources)) {
                $gold            = 0;
                $pageNamePostfix = ($isField ? '1' : '2');
                //$gold = $this->buildProperties['nextLevel'];
                $lg              = ' | <a href="village' . $pageNamePostfix . '?id=' . $this->buildingIndex . '&up">الارتقاء فورا ' . $gold . '<img alt="ذهب" src="' . $GLOBALS['AppConfig']['system']['linksite'] . 'default/img/a/gold.gif" class="tooltip2" title="ذهب"></a>';
                $link            = ($upgrade ? '<a class="build" href="village' . $pageNamePostfix . '?id=' . $this->buildingIndex . '&k=' . $this->data['update_key'] . '">' . buildings_p_upg_tolevel . ' ' . $this->buildProperties['nextLevel'] . '</a>' : '<a class="build" href="village2?id=' . $this->buildingIndex . '&b=' . $item_id . '&k=' . $this->data['update_key'] . '">' . buildings_p_create_newbuild . '</a>');
                $workerResult    = $this->isWorkerBusy($isField);
                return ($workerResult['isBusy'] ? '<span class="none">' . buildings_p_workersbusy . '</span>' : $link . ($workerResult['isPlusUsed'] ? ' <span class="none">(' . buildings_p_wait_buildqueue . ')</span>' : ''));
            }
        }
        $neededTime = $this->getNeededTime($neededResources);
        return '<span class="none">' . (0 < $neededTime ? buildings_p_willenoughresat . ' ' . WebHelper::secondstostring($neededTime) . ' ' . time_hour_lang : buildings_p_notenoughres2) . '</span>';
    }
    function _canAcceptOffer($needResources, $giveResources, $villageId, $onlyForAlliance, $allianceId, $maxTime, $distance)
    {
        if ($villageId == $this->data['selected_village_id']) {
            return 0;
        }
        if (!$this->isResourcesAvailable($giveResources)) {
            return 1;
        }
        $needMerchantCount = ceil(($giveResources[1] + $giveResources[2] + $giveResources[3] + $giveResources[4]) / $this->merchantProperty['capacity']);
        if (($needMerchantCount == 0 OR $this->merchantProperty['exits_num'] < $needMerchantCount)) {
            return 2;
        }
        if (($onlyForAlliance AND (intval($this->data['alliance_id']) == 0 OR $allianceId != intval($this->data['alliance_id'])))) {
            return 3;
        }
        if ((0 < $maxTime AND $maxTime < $distance / $this->merchantProperty['speed'])) {
            return 4;
        }
        return 5;
    }
    public function getNextLink()
    {
        $text = "»";
        if ($this->pageIndex + 1 == $this->pageCount) {
            return $text;
        }
        $link = "";
        if (0 < $this->selectedTabIndex) {
            $link .= "t=" . $this->selectedTabIndex;
        }
        if ($link != "") {
            $link .= "&";
        }
        $link .= "p=" . ($this->pageIndex + 1);
        $link = "build?id=" . $this->buildingIndex . "&" . $link;
        return "<a href=\"" . $link . "\">" . $text . "</a>";
    }
    
    public function getPreviousLink()
    {
        $text = "«";
        if ($this->pageIndex == 0) {
            return $text;
        }
        $link = "";
        if (0 < $this->selectedTabIndex) {
            $link .= "t=" . $this->selectedTabIndex;
        }
        if (1 < $this->pageIndex) {
            if ($link != "") {
                $link .= "&";
            }
            $link .= "p=" . ($this->pageIndex - 1);
        }
        $link = "build?id=" . $this->buildingIndex . "&" . $link;
        return "<a href=\"" . $link . "\">" . $text . "</a>";
    }
    
    
    function getResourceGoldExchange($neededResources, $itemId, $buildingIndex, $multiple = FALSE)
    {
        if ($itemId != 0) {
            if ((($this->data['gold_num'] < $this->gameMetadata['plusTable'][6]['cost'] OR 0 < $this->needMoreUpgrades($neededResources, $itemId)) OR ($this->isResourcesAvailable($neededResources) AND !$multiple))) {
                return '';
            }
        }
        $s1               = 0;
        $s2               = 0;
        $exchangeResource = '';
        foreach ($neededResources as $k => $v) {
            $s1 += $v;
            $s2 += $this->resources[$k]['current_value'];
            if ($exchangeResource != '') {
                $exchangeResource .= '&';
            }
            $exchangeResource .= 'r' . $k . '=' . $v;
        }
        $canExchange = $s1 <= $s2;
        if (($multiple AND $canExchange)) {
            $num              = floor($s2 / $s1);
            $exchangeResource = '';
            foreach ($neededResources as $k => $v) {
                if ($exchangeResource != '') {
                    $exchangeResource .= '&';
                }
                $exchangeResource .= 'r' . $k . '=' . $v * $num;
            }
        }
        return ' | <a href="build?resret1&bid=17&t=3&rid=' . $buildingIndex . '&' . $exchangeResource . '" title="' . buildings_p_m2m . '"><img class="npc' . ($canExchange ? '' : '_inactive') . '" src="' . $GLOBALS['AppConfig']['system']['linksite'] . 'x.gif" alt="' . buildings_p_m2m . '" title="' . buildings_p_m2m . '"></a>';
    }
}
$p = new GPage();
$p->run();
?>