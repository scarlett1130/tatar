<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."adminweb1.php" );
require_once( MODEL_PATH."payhis.php" );
require_once( MODEL_PATH."controlmember.php" );
require_once( MODEL_PATH."news.php" );
class GPage  extends securegamepage
{

    public $saved = NULL;
    public $siteNews = NULL;
    public $isAdmin = FALSE;
    public $Advertisings = array( );
    public $Meber = array( );
    public $pageSize = 20;
    public $pageIndex = NULL;
    public $pageCount = NULL;
    public function GPage( )
    {
        parent::securegamepage( );
        $this->viewFile = "serverst.phtml";
        if ($_GET['t'] == 4 or $_GET['t'] == 2) { $this->contentCssClass = "plus";}
        else if ($_GET['t'] == 7) { $this->contentCssClass = "forum";}
		else { $this->contentCssClass = "messages"; }
    }

    public function load( )
    {
        parent::load( );
        $type = 'cashu';
        $m = new AdminWebModel();
		
		$mpay = new Payhis();
		if(!isset($_GET['type'])){ $this->dataList = $mpay->PayhisByType(); }
		else{ $this->dataList = $mpay->PayhisByTypecashu_paygold($_GET['type']); }
        $payhistotal = $mpay->getTotalMoney();
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
            exit( 0 );
        }
        else
        {
            $this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 0 <= intval($_GET['t'])) && intval($_GET['t']) <= 25) ? intval($_GET['t']) : 0);
            if ($this->selectedTabIndex == 0)
            {
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

            $this->saved = FALSE;
            if ( $this->isPost( ) && isset( $_POST['news'] ) )
            {
                $this->siteNews = $_POST['news'];
                $this->saved = TRUE;
                $m->setGlobalPlayerNews( $this->siteNews );
            }
            else
            {
                $this->siteNews = $m->getGlobalSiteNews( );
            }
            $m->dispose( );
            }
			if ($this->selectedTabIndex == 2){
	    	$win = new wingoldModel();
			$this->wingold = $win->getwingoldAdmin();
			if(isset( $_GET['add_win_gold'] ) && isset( $_GET['id'] ) && isset( $_GET['idplayer'] ) && isset( $_GET['approval'] )){
            $id = (int)$_GET['id'];
			$idplayer = (int)$_GET['idplayer'];
			$approval = (int)$_GET['approval'];
			if($approval == 2){ $win->AddGold($idplayer); }
			$win->EndWinGold($id, $approval);
			$this->redirect( "serverst?t=2" );
			}
			}
        if ($this->selectedTabIndex == 6)
        {
		if ( $this->isPost( ) )
        {
		$m3 = new BlogModel();
		$tel = $_POST['tel'];
		$text = $_POST['text'];
		if($tel == '' or $text == ''){ $this->error = 'احد الحقول فارغة !!'; }
		else{
		$date = date('Y/m/d');
		$this->bloold = $m3->changesummaryBlog();
		while ( $this->bloold->next( ) )
		{
		$blog_old = $this->bloold->row['blog'];
		if($blog_old != '' ){ $blog_old = " | $blog_old"; }
		$blog = "$tel - $text - $date $blog_old";
		}
		$add = $m3->addgesummaryBlog($blog);
		$this->error = '<b>تم الاضافة بنجاح</b>';
		}
		}
		}
		if ($this->selectedTabIndex == 7)
        {
		$this->voting = $m->getGlobalSitevoting();
		if(isset($_GET['add'])){
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
		if ( $this->isPost( ) )
        {
		$this->found = true;
		$id = $m->getPlayerId($_POST['name']);
		$this->file = $m->getfilename($id);
		$this->userin = true;
		}
		}
		if ($this->selectedTabIndex == 3)
		{
		if(isset( $_GET['gold'] )){
		if ( $this->isPost( ) )
        {
		$m->AddGoldPlayer(intval($_POST['gold']));
		$this->AddGold = 'تم اضافة الذهب';
		}
		}
		$this->pageIndex = isset( $_GET['p'] ) && is_numeric( $_GET['p'] ) ? intval( $_GET['p'] ) : 0;
		$rowsCount = $m->getMeberCount( );
		$this->pageCount = 0 < $rowsCount ? ceil( $rowsCount / $this->pageSize ) : 1;
		$this->Meber = $m->GetMeber( $this->pageIndex, $this->pageSize, $this->GoldNumMeber );
		$m->dispose( );
		}
    }
    public function getNextLink( )
    {
        $text = text_nextpage_lang." »";
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
        $text = "« ".text_prevpage_lang;
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