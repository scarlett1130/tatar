<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."register.php" );
require_once(MODEL_PATH."index.php");

class GPage extends GamePage
{
    public $err = array
    (
        0 => "",
        1 => "",
        2 => "",
        3 => ""
    );
    public $success = NULL;
    public $SNdata = NULL;
    public $UserID = 0;

    public function GPage( )
    {
        parent::gamepage( );
        $this->viewFile = "register.phtml";
        $this->contentCssClass = "signup";
    }
    public function load( )
    {
        parent::load( );
        $this->SNdata = 0;
        $this->success = FALSE;

        $m      = new RegisterModel( );

        if(!isset($_GET['p']))
        {
             $this->show = 0;
        if ( $this->isPost( ) )
        {
             session_start();

             $name   = trim( $_POST['naame'] );
             $email  = trim( $_POST['email'] );
             $pwd    = trim( $_POST['pwd'] );
             $wp = new WebHelper();
             $Ip     = $wp->getclientip( );
             $Invite = intval($_GET['ref']);

             $this->err[0] = strlen( $name ) < 3 ? register_player_txt_notless3 : "";

             if ( $name == "[tatar]" || $name == "admin" || $name == "Admin" || $name == "administrator" || $name == "Administrator" || $name == "multihunter" || $name == "Multihunter" || $name == "tatar" || $name == "Tatar" || $name == "?I??" || $name == "الادارة" || $name == "الاداره" || $name == "الدعم" || $name == "الادمن" || $name == $GLOBALS['AppConfig']['system']['adminName'] || $name == tatar_tribe_player )
             {
                  $this->err[0] = register_player_txt_reserved;
             }
             if (strlen($name) > 100)
             {
                  $this->err[0] = register_player_txt_invalidchar;
             }
             if ( $name != htmlspecialchars($name) )
             {
                  $this->err[0] = register_player_txt_invalidchar;
             }

             $this->err[1] = !preg_match( "/^[^@]+@[a-zA-Z0-9._-]+\\.[a-zA-Z]+\$/", $email ) ? register_player_txt_invalidemail : "";
             $this->err[2] = strlen( $pwd ) < 4 ? register_player_txt_notless4 : "";

             if (!preg_replace('/[^a-zA-Z]/', '', $pwd))
             {
                  $this->err[2] = '(لابد من وجود حرف واحد انجليزي (a-z))';
             }
             if ( $m->isnotspam( $Ip ) ) {
            // $this->err[3] = register_player_txt_fullserver;
             } 
                  session_start ();
             if (isset ($_SESSION['is_rig'])) {
//             $this->err[3] = "انت مسجل لدينا بأسم ".$_SESSION['is_rig'];
             }
             if ( 0 < strlen( $this->err[0] ) || 0 < strlen( $this->err[1] ) || 0 < strlen( $this->err[2] ) || 0 < strlen( $this->err[3] ) )
             {
                  return;
             }

             $this->err[0] = $m->isPlayerNameExists2( $name ) ? register_player_txt_usedname : "";
             $this->err[1] = $m->isPlayerEmailExists2( $email ) ? register_player_txt_usedemail : "";

             if ( 0 < strlen( $this->err[0] ) || 0 < strlen( $this->err[1] ) )
             {
                  return;
             }

             $this->err[0] = $m->isPlayerNameExists( $name ) ? register_player_txt_usedname : "";
             $this->err[1] = $m->isPlayerEmailExists( $email ) ? register_player_txt_usedemail : "";
            $file = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/name/".$name;
            if(file_exists($file)){
            $op = fopen($file, 'r');
            $read = fread($op, filesize($file));
            if ($email != $read) {
            $this->err[0] = "هذا الاسم محجوز لصاحبه";
            }
            }
             if ( 0 < strlen( $this->err[0] ) || 0 < strlen( $this->err[1] ) )
             {
                  return; 
             }

             $this->datastats = $m->GetGsummaryData();
             $start_time = (time()-$this->datastats['server_start_time']);
             $regover = ($GLOBALS['AppConfig']['Game']['RegisterOver']*24*60*60);
             if ($start_time > $regover)
             {
                  exit;
             }
             $activationCode = substr( md5( dechex( time() ).dechex( time() ) ), 5, 10 );
             $m->createNewPlayer1($name, $email, $pwd, $Invite, $activationCode);
                  session_start ();
                  $_SESSION['is_rig'] = $name;
$this->redirect ("activate?id=".$activationCode);
             $from    = $GLOBALS['AppConfig']['system']['email'];
             $subject = register_player_txt_regmail_sub;
             $link    = WebHelper::getbaseurl( )."activate?id=".$activationCode;
             $message = sprintf( register_player_txt_regmail_body, $name, $name, $pwd, $activationCode, $link, $link );
             WebHelper::sendmail( $email, $from, $subject, $message );

             $this->success = true;

        }
        }
        else
        {
             $this->show = 1;

             if ( $this->isPost( ) )
             {
                  $name   = trim( $_POST['naame'] );
                  $pwd    = trim( $_POST['pwd'] );

                  $this->err[0] = strlen( $name ) < 3 ? register_player_txt_notless3 : "";

                  if ( $name == "[tatar]" || $name == "admin" || $name == "Admin" || $name == "administrator" || $name == "Administrator" || $name == "multihunter" || $name == "Multihunter" || $name == "tatar" || $name == "Tatar" || $name == "?I??" || $name == "الادارة" || $name == "الاداره" || $name == "الدعم" || $name == "الادمن" || $name == $GLOBALS['AppConfig']['system']['adminName'] || $name == tatar_tribe_player )
                  {
                       $this->err[0] = register_player_txt_reserved;
                  }
                  if (strlen($name) > 100)
                  {
                       $this->err[0] = register_player_txt_invalidchar;
                  }
                  if ( $name != htmlspecialchars($name) )
                  {
                       $this->err[0] = register_player_txt_invalidchar;
                  }

                  $this->err[1] = strlen( $pwd ) < 4 ? register_player_txt_notless4 : "";

                  if ( 0 < strlen( $this->err[0] ) || 0 < strlen( $this->err[1] ) )
                  {
                       return;
                  }

                  if($m->isPlayerNamePwd2( $name, $pwd ) == 0)
                  {
                       $this->err[2] = 'الحساب غير مسجل لدينا';
                       return;
                  }

                  $m->delete_register_firstByname($name);
                  $this->success = true;
             }
        }
    }
}
$p = new GPage( );
$p->run( );
?>