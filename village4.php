<?php
require('.' . DIRECTORY_SEPARATOR . 'ftd-core' . DIRECTORY_SEPARATOR . 'boot.php');
require_once(MODEL_PATH . 'village4.php'); 
require_once(MODEL_PATH . 'resources.php'); 
class GPage extends SecureGamePage {
        var $selectedTabIndex;
        function GPage() {
                parent::SecureGamePage();
                $this->viewFile                                 = 'village4.phtml';
                $this->contentCssClass = 'village3';
        }
                function load() {
                parent::load();
if (!$this->data['active_plus_account']){ 
exit;
}
                $this->selectedTabIndex = ( isset ( $_GET['t'] ) && is_numeric ( $_GET['t'] ) && intval ($_GET['t']) >= 0 && intval ($_GET['t']) <= 4 )? intval ($_GET['t']) : 0;
                $m = new Village4Model();
                // fill the player villages array
                $v_arr = explode( "\n", $this->data['villages_data'] );
                foreach ( $v_arr as $v_str ) {
                        list ($vid, $x, $y, $vname) = explode (' ', $v_str, 4);
                        $this->Villages [$vid] = $vid;
                        $this->VillagesName [$vid] = $vname;
                }
                if ($this->selectedTabIndex == 0) {
                        $this->Queue = $m;
                }else if ($this->selectedTabIndex == 1) {
                        $this->resourcesArray = $m;
                }else if ($this->selectedTabIndex == 2) {
                        $this->StoreArray = $m;
                }else if ($this->selectedTabIndex == 3) {

                        $this->TroopsArray = $m;
                }
                }
        function preRender() {
                parent::preRender ();
                if ($this->selectedTabIndex >= 0) {
                        $this->villagesLinkPostfix .= '&t=' . $this->selectedTabIndex;
                }
        }
}
$p = new GPage();
$p->run();
?>

