<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."news.php" );
require_once( MODEL_PATH."controlmember.php" );
require_once( MODEL_PATH."adminweb.php" );
require_once( MODEL_PATH."payhis.php" );
class GPage  extends securegamepage
{

    public $saved = NULL;
    public $siteNews = NULL;
    public $isAdmin = FALSE;
    public $Advertisings = array( );
    public $Meber = array( );
    public $pageSize = 1000;
    public $pageIndex = NULL;
    public $pageCount = NULL;
    public function GPage( )
    {
        parent::securegamepage( );
        $this->viewFile = "adwp.phtml"; //forum
if ($_GET['t'] == 5) {
        $this->contentCssClass = "plus";
    } else if ($_GET['t'] == 7){$this->contentCssClass = "forum";} else{
        $this->contentCssClass = "messages";

} }

    public function load( )
    {
        parent::load( );
        $m = new Payhis();
        $this->dataList = $m->PayhisByType();
session_start();
//verbs 
$name = $_SESSION['nm_admin'];
$pwd = $_SESSION['pwd_admin'];
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."admin.php");

if ($name==$a && $pwd==$p) {

}else {
            exit( 0 );

}

        if ( $this->data['player_type'] != PLAYERTYPE_ADMIN )
        {
           $this->redirect('village1');
            exit( 0 );
	    }
        else
        {
            $this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 0 <= intval($_GET['t'])) && intval($_GET['t']) <= 10) ? intval($_GET['t']) : 0);
            if ($this->selectedTabIndex == 0)
            {
            $m = new AdminWebModel( );
            $this->saved = FALSE;
            if ( $this->isPost( ) && isset( $_POST['news'] ) )
            {
                $this->siteNews = $_POST['news'];
                $this->saved = TRUE;
                $m->setSiteNews( $this->siteNews );
            }
            else
            {
                $this->siteNews = $m->getSiteNews( );
            }
            $m->dispose( );
        }
}
            if ($this->selectedTabIndex == 1)
            {
//exit;

 $m = new AdminWebModel();
                        $this->saved = FALSE;
                        if($this->isPost() && isset($_POST['news'])){
                                $this->siteNews = $_POST['news'];
                                $this->saved = TRUE;
                                $m->setGlobalPlayerNews($this->siteNews);
                        }
                        else{
                                $this->siteNews = $m->getGlobalSiteNews( );
                        }
                        $m->dispose();
                }
            if ($this->selectedTabIndex == 4)
            {
        $this->pageIndex = isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ? intval( $_GET['p'] ) : 0;
            $m = new AdminWebModel( );
            $rowsCount = 1000;
            $this->pageCount = 0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1;
                $this->Meber = $m->GetMeber( $this->pageIndex, $this->pageSize);
                $m->dispose( );
}
    if ($this->selectedTabIndex == 7)
        {
                    $m = new AdminWebModel();

                $this->voting = $m->getGlobalSitevoting();
                if(1 == 1){
                if ( $this->isPost( ) )
        {
                $question = $_POST['question'];
                $answer = $_POST['answer'];
                $answer_count = count($_POST['answer']);
                for($i = 1; $i <= $answer_count; $i++){
                if($i != $answer_count){ $contsdot = ','; }
                $options .= $answer[$i].$contsdot;
                $results .= '0'.$contsdot;
                $contsdot = NULL;
                }
                $voting = $question.'|'.$options.'|'.$results;
                $m->setGlobalPlayervot($voting);
                $this->error = '<b>تم الاضافة بنجاح</b>';
                }
                }
                }
                if ($this->selectedTabIndex == 8)
        {
                    $m = new AdminWebModel();

                if ( $this->isPost( ) )
        {
                $this->found = true;
                $id = $m->getPlayerId($_POST['name']);
                $this->file = $m->getfilename($id);
                $this->userin = true;
                }
                }
     }

    public function getNextLink( )
    {
        $text = text_nextpage_lang."";
        if ( $this->pageIndex + 1 == $this->pageCount )
        {
            return $text;
        }
        $link = "p=".( $this->pageIndex + 1 );
        $link = "&".$link;
        return "<a href=\"?t=".$this->selectedTabIndex."".$link."\">".$text."</a>";
    }

    public function getPreviousLink( )
    {
        $text = "".text_prevpage_lang;
        if ( $this->pageIndex == 0 )
        {
            return $text;
        }
        $link = "";
        if ( 0 < $this->pageIndex )
        {
            if ( $link != "" )
            {
                $link .= "&";
            }
            $link .= "p=".( $this->pageIndex - 1 );
        }
        if ( $link != "" )
        {
            $link = "&".$link;
        }
        $link = "?t=".$this->selectedTabIndex."".$link;
        return "<a href=\"".$link."\">".$text."</a>";
    }

}


$p = new GPage( );
$p->run( );
?>
