<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."over.php" );
require_once( MODEL_PATH."index.php" );
class GPage extends ProcessVillagePage{

    public $playerData = NULL;
        public function GPage(){
        parent::processvillagepage( );
        $this->viewFile = "over.phtml";
        $this->contentCssClass = "messages";
    }
    public function load( )
    {
        parent::load( );
        if ( !$this->globalModel->isGameOver( ) )
        {
            exit( 0 );
        }
        else
        {
            $m = new OverGameModel( );
            $this->playerData = $m->getWinnerPlayer( );
            $this->TopOff = $m->getTopsAttacker( );
            $this->TopDef = $m->getTopsDeffer( );
            $this->TopPop = $m->getTopsPop( );
            $this->TopHero = $m->getTopsHero( );
            $m->dispose( );
            $m = new IndexModel();
            $this->datas = $m->getIndexSummary();
        }
    }
}
$p = new GPage( );
$p->run( );
?>
