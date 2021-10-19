<?php
require( '.' . DIRECTORY_SEPARATOR . 'ftd-core' . DIRECTORY_SEPARATOR . 'boot.php' );
require( '.' . DIRECTORY_SEPARATOR . 'ftd-core' . DIRECTORY_SEPARATOR . 'metadata.php' );
require_once( MODEL_PATH . 'battle.php' );
require_once( MODEL_PATH . DIRECTORY_SEPARATOR . 'battles' . DIRECTORY_SEPARATOR . 'war.php' );
class GPage extends SecureGamePage {
        var $showTroopsTable                 = FALSE;
        var $showWarResult                        = FALSE;
        var $errorText                                        = '';
        var $troopsMetadata;
        var $warResult;
        function GPage() {
                parent::SecureGamePage();
                $this->viewFile                                 = 'warsm.phtml';
                $this->contentCssClass = 'warsim';
        }
        function load() {
                parent::load();
                if ($this->isPost ()) {
if ( !isset( $_POST['a1'] ) || intval( $_POST['a1'] ) != 1 && intval( $_POST['a1'] ) != 2 && intval( $_POST['a1'] ) != 3 && intval( $_POST['a1'] ) != 7 && intval( $_POST['a1'] ) != 6 && intval( $_POST['a1'] ) != 8 && intval( $_POST['a1'] ) != 9 ) {
                                $this->errorText = 'لم يتم تحديد نوع القوة المهاجمة';
                                return;
                        }
                        if (!isset ($_POST['ktyp']) || (intval ($_POST['ktyp']) != 1 && intval ($_POST['ktyp']) != 2)) {
                                $this->errorText = 'لم يتم تحديد نوع المعركه';
                                return;
                        }
                        if (!isset ($_POST['a2']) || sizeof ($_POST['a2']) == 0) {
                                $this->errorText = 'لم يتم تحديد نوع القوات المدافعة';
                                return;
                        }
                        foreach ($_POST['a2'] as $tribeId=>$v) {
if ( $tribeId != 1 && $tribeId != 2 && $tribeId != 3 && $tribeId != 4 && $tribeId != 7 && $tribeId != 6 && $tribeId != 8 && $tribeId != 9 ){                                        $this->errorText = 'لم يتم تحديد نوع القوات المدافعة بشكل صحيح';
                                        return;                                
                                }
                        }
                        $this->troopsMetadata = $this->gameMetadata['troops'];
                        $this->showTroopsTable = TRUE;
                        $this->showWarResult = FALSE;
                        if (!isset ($_POST['t1'])) {
                                return;
                        }
                        $m = new WarBattleModel ();                        
                        if (isset ($_POST['h_off_bonus1']) && intval ($_POST['h_off_bonus1']) > 0) {
                                $this->showWarResult = TRUE;
                        }
                        // get attack troops
                        $troops = array ();
                        $troopsPower = array ();
                        foreach ($_POST['t1'] as $tribeId=>$troopArray) {
                                foreach ($troopArray as $tid=>$tnum) {
                                        if ($tnum > 0) {
                                                $this->showWarResult = TRUE;
                                        }
                                        $troops[$tid] = intval ($tnum);
                                        $troopsPower[$tid] = (isset ($_POST['f1']) && isset ($_POST['f1'][$tribeId]) && isset ($_POST['f1'][$tribeId][$tid]))? intval ($_POST['f1'][$tribeId][$tid]) : 0;
                                }
                        }
                        if (!$this->showWarResult) { return; }
                        $heroLevel = (isset ($_POST['h_off_bonus1']))? intval ($_POST['h_off_bonus1']) : 0;
                        $wringerPower = (isset ($_POST['kata']))? intval ($_POST['kata']) : 0;
                        $attackTroops = $m->_getTroopWithPower ($troops, $troopsPower, TRUE, $heroLevel, $wringerPower, 0 , FALSE , 0 , 0 , 0);
                        // get defense troops
                        $wallLevel = (isset ($_POST['wall1']))? intval ($_POST['wall1']) : 0;
                        $item_id = "3".$tribeId."";
                        $wallPower = $wallLevel > 0 ? $this->gameMetadata['items'][$item_id]['levels'][$wallLevel - 1]['value'] : 0;
                        $palLevel = (isset ($_POST['pal']))? intval ($_POST['pal']) : 0;
                        $totalDefensePower['infantry_power'] =  $totalDefensePower['cavalry_power'] = 0;
                        $defenseTroops = array ();
                        foreach ($_POST['t2'] as $tribeId=>$troopArray) {
                                $troops = array ();
                                $troopsPower = array ();

                                foreach ($troopArray as $tid=>$tnum) {
                                        $troops[$tid] = intval ($tnum);
                                        $troopsPower[$tid] = (isset ($_POST['f2']) && isset ($_POST['f2'][$tribeId]) && isset ($_POST['f2'][$tribeId][$tid]))? intval ($_POST['f2'][$tribeId][$tid]) : 0;
                                }
                                $defenseTroops[$tribeId] = $m->_getTroopWithPower ($troops, $troopsPower, FALSE, 0, 0, $wallLevel , FALSE , 0 , 0 , 0);
                                $totalDefensePower['infantry_power'] += $defenseTroops[$tribeId]['infantry_power'];
                                $totalDefensePower['cavalry_power'] += $defenseTroops[$tribeId]['cavalry_power'];
                        }
                        // get the war result
                        $this->warResult = $m->getWarResult ($attackTroops, $defenseTroops, $totalDefensePower, $wallPower, $palLevel, (isset ($_POST['ktyp']) && intval ($_POST['ktyp']) == 2), FALSE , 0 , 0);
                        $m->dispose ();
                }

        }
}

$p = new GPage();
$p->run ();
