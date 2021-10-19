<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."adminweb.php" );
class GPage extends ProcessVillagePage{
        public $saved = NULL;
        public $siteNews = NULL;
        public function GPage(){
        parent::processvillagepage( );
        $this->viewFile = "shownew.phtml";
        $this->contentCssClass = "messages";
        $this->checkForGlobalMessage = FALSE;
        $this->checkForNewVillage = FALSE;
    }
           public function load()
    {
        parent::load();
        if ( intval( $this->data['new_gnews'] ) == 0 AND intval( $this->data['new_voting'] ) == 0 || $this->player->isSpy ) { $this->redirect( "village1" ); exit; }
        else
        {
                $m = new AdminWebModel();
                if(intval( $this->data['new_gnews'] ) == 1){
                $this->siteNews = $m->getGlobalSiteNews();
                }else{
                $this->siteNews = $m->getGlobalSitevoting();
                }
            $m->dispose();
        }
    }
}
$p = new GPage();
$p->run();
?>
