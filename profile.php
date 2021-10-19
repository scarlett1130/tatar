<?php
require('.' . DIRECTORY_SEPARATOR . 'ftd-core' . DIRECTORY_SEPARATOR . 'boot.php');
require_once(MODEL_PATH . 'profile.php');
require_once( MODEL_PATH."links.php" );
require (MODEL_PATH."wordsfilter.php");
class GPage extends SecureGamePage
    {

     public $err = array
    (
        0 => "",
        1 => "",
        2 => "",
        3 => ""
    );

    var $fullView = null;
    var $profileData = null;
    var $selectedTabIndex = null;
    var $villagesCount = null;
    var $villages = null;
    var $birthDate = null;
    var $agentForPlayers = array();
    var $myAgentPlayers = array();
    var $errorText = null;
    var $bbCodeReplacedArray = array();
    var $isAdmin = null;
    function GPage()
        {
        parent::securegamepage();
        $this->viewFile        = 'profile.phtml';
        $this->contentCssClass = 'player';
}
    function load()
        {
        parent::load();
        $this->isAdmin = $this->data['player_type'] == PLAYERTYPE_ADMIN;
        $uid           = ((isset($_GET['uid']) && 0 < intval($_GET['uid'])) ? intval($_GET['uid']) : $this->player->playerId);
if (isset($_GET['anblock'])) {
$del = htmlspecialchars(trim(abs(ceil(intval($_GET['anblock'])))));
        $m                      = new ProfileModel();
$m->removeAlliancewar( $this->player->playerId, $del );
header ("Location: profile?uid=".$_GET['uid']."");
}
if (isset($_GET['block'])) {
$add = htmlspecialchars(trim(abs(ceil(intval($_GET['block'])))));
        $m                      = new ProfileModel();
$m->addAllianceContracts( $this->player->playerId, $add );
header ("Location: profile?uid=".$_GET['uid']."");
}
session_start();
//verbs 
$name = $_SESSION['nm_admin'];
$pwd = $_SESSION['pwd_admin'];
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."admin.php");
if ( $name==$a && $pwd==$p && $this->isAdmin && isset( $_GET['MokaBard'] ) && 0 < $uid && $uid != $this->player->playerId &&  $uid != 1)
            {
            $gameStatus                 = $this->player->gameStatus;
            $previd                     = $this->player->playerId;
            $this->player               = new Player();
            $this->player->playerId     = $uid;
            $this->player->prevPlayerId = $previd;
            $this->player->isAgent      = FALSE;
            $this->player->isSpy        = TRUE;
            $this->player->gameStatus   = $gameStatus;
            $this->player->save();
            $this->redirect('village1');
            return null;
            }
        $this->selectedTabIndex = 0;
        $this->fullView         = FALSE;
        $m                      = new ProfileModel();
        if ($uid != $this->player->playerId)
            {
            $this->profileData = $m->getPlayerDataById($uid);
            if ($this->profileData == NULL)
                {
                $m->dispose();
                $this->redirect('village1');
                return null;
                }
            }
        else
            {
            $this->profileData       = $this->data;
            $this->profileData['id'] = $uid;
            $this->fullView          = !$this->player->isAgent;
            $this->selectedTabIndex  = (((((!$this->player->isAgent && isset($_GET['t'])) && is_numeric($_GET['t'])) && 0 <= intval($_GET['t'])) && intval($_GET['t']) <= 6) ? intval($_GET['t']) : 0);
            if (($this->selectedTabIndex == 2 && $this->data['player_type'] == PLAYERTYPE_TATAR))
                {
                $this->selectedTabIndex = 0;
                }



if (($this->selectedTabIndex == 6)){

if ($this->isPost( )){
                         $mr = new QueueModel( );
                         $name = trim($this->data['name']);
$if2 = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/name/".$name;
            if(file_exists($if2)){
                              $this->error = 'لقد قمت بالتأمين سابقاَ';
}else

if($this->data['gold_num'] <= 50)
                         {
                              $this->error = 'الذهب قليل جداً';
                         }
                         else
                         {
                              $this->error = 'تم تأمين الاسم بنجاح , لاتنسى ايميلك اذ نسيته فلن تستطيع التسجيل بهذا الاسم مره اخرى';
                              $mr->provider->executeQuery2("UPDATE p_players p SET p.gold_num=p.gold_num-%s WHERE p.id=%s", array( 50, $this->player->playerId ) );
$if = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/name/".$name;
$if = fopen($if, "w");
fwrite($if, $this->data['email']);
fclose($if);
                         }
                         }



}


//

if((isset($_POST['color_name']) AND $_POST['color_name'] != '') AND isset($_POST['nn_pwd']) AND $_POST['nn_pwd'] != '')
                    {
                         $mr = new QueueModel( );
                         $color_name = trim($_POST['color_name']);

                         if(md5($_POST['nn_pwd']) != $this->data['pwd'])
                         {
                              $this->error = 'كلمة المرور غير صحيحة';
                         }
                         elseif($this->data['gold_num'] <= 50)
                         {
                              $this->error = 'الذهب قليل جداً';
                         }
                         else
                         {
                              $this->error = 'تم تغيير اللون بنجاح';
                              $mr->provider->executeQuery2("UPDATE p_players p SET p.color_name='%s', p.gold_num=p.gold_num-%s WHERE p.id=%s", array( $color_name, 50, $this->player->playerId ) );
                         }
                    }
//pro

require_once(MODEL_PATH . 'statistics.php');
$mtest = new StatisticsModel();
$tatarRaised = $mtest->tatarRaised();
$Testm = new QueueModel();
$test = $Testm->provider->fetchScalar("SELECT COUNT(*) FROM p_villages where is_artefacts= 1 AND player_id='".$this->player->playerId."'");
if (isset($_GET['protection'])) {
if ($test) {
$this->ResaultMsg = 'لايمكنك تفعيل الحمايه عند وجود تحفه في قريتك';
}else if ($tatarRaised) {
$this->ResaultMsg = 'لايمكنك تفعيل الحمايه اثناء وجود التتار';
}else {
if ($_GET['protection'] == 1) {
if (!$this->data['protection']) {
								$m->Protection2($this->player->playerId);
$this->ResaultMsg = 'تم تفعيل الحمايه';
}else {
$this->ResaultMsg = 'الحمايه مفعله من قبل';
}
}
}
}
$this->ResaultMsg = '<span class="error">'.$this->ResaultMsg.'</span>';

//end pro




            $agentForPlayers = (trim($this->profileData['agent_for_players']) == '' ? array() : explode(',', $this->profileData['agent_for_players']));
            foreach ($agentForPlayers as $agent)
                {
                list($agentId, $agentName, $actions) = explode('|', $agent);
                $this->agentForPlayers[$agentId] = array ($agentName, $actions);
                }
            $myAgentPlayers = (trim($this->profileData['my_agent_players']) == '' ? array() : explode(',', $this->profileData['my_agent_players']));
            foreach ($myAgentPlayers as $agent)
                {
                list($agentId, $agentName, $actions) = explode('|', $agent);
				$this->myAgentPlayers[$agentId] = array ($agentName, $actions);
                }
            }
if (isset ($_GET['dc'])) {
$dc = htmlspecialchars(trim(abs(ceil(intval($_GET['dc'])))));
$tatarzx = new QueueModel();
$c = $tatarzx->provider->fetchRow("SELECT to_name,id FROM coment WHERE id = '".$dc."'");
$fr = $this->data['name'];
if ($fr == $c['to_name']) {
		$tatarzx->provider->executeQuery (
			'DELETE FROM coment
			WHERE
				id=%s', 
			array ($dc)
		);
}
}
session_start();
if ($_SESSION['num_com'] >= 1 && $_SESSION['cliprz_com'] <= time() - 10) {
$_SESSION['num_com'] = 0;
}
$cm = htmlspecialchars(trim($_POST['coment']));
if (isset ($cm)) {
if ($cm != '' && $this->data['total_people_count'] > 200) {
if ($_SESSION['cliprz_com'] > time() - 10 && $_SESSION['num_com'] >= 1) {

}else {
$to = $this->profileData['name'];
$fr = $this->data['name'];
$dt = date('Y/m/d H:i:s');
$tatarzx = new QueueModel();
$tatarzx->provider->executeQuery2("INSERT INTO `coment` (`from_name`, `to_name`, `date`, `coment`) VALUES ('".$fr."', '".$to."', '".$dt."', '".$cm."');");
$_SESSION['cliprz_com'] = time();
$_SESSION['num_com'] = ($_SESSION['num_request']+1);
require_once( MODEL_PATH."msg.php" );
$msg = new MessageModel( );
$subject = "رد جديد على حائطك";
$message = 'تحيه طيبه

عزيزي '.$to.'

نود اعلامك بأنه هناك تعليق جديد على حائطك

ادارة اللعبة';
$messageId = $msg->sendMessage( 1, "النظام", $_GET['uid'], $to, $subject, $message );
$msg->dispose();

$f = $tatarzx->provider->fetchResultSet( "SELECT * FROM coment WHERE to_name = '".$to."'" );
while($f->next ()) {
$name = $tatarzx->provider->fetchRow("select * from p_players where name = '".$f->row['from_name']."' LIMIT 1");  
if ($is[$name['id']] == false && $fr != $name['name'] && $to != $name['name']){
$domain = WebHelper::getdomain();
$l = "http://".$domain."profile?uid=".$_GET['uid'];
$subject = "رد جديد على حائط :".$to;
$message = 'تحيه طيبه

عزيزي '.$name['name'].',

نود اعلامك بأنه هناك تعليق جديد على حائط الاعب '.$to.' 
اذ كنت تود الاطلاع عليه 

هذا رابط مختصر للحائط : 
'.$l.'

ادارة اللعبة';
$messageId = $msg->sendMessage( 1, "النظام", $name['id'], $name['name'], $subject, $message );
$is[$name['id']] = true;
$msg->dispose();
}
}
}
}
}
        if (isset($_GET[links]))
            {
        if ( !$this->data['active_plus_account'] ) 
        { 
            exit( 0 ); 
        } 
        else if ( $this->isPost( ) ) 
        { 
            $this->playerLinks = array( ); 
            $i = 0; 
            $c = sizeof( $_POST['nr'] ); 
            while ( $i < $c ) 
            { 
                $name = trim( $_POST['linkname'][$i] ); 
                $url = trim( $_POST['linkurl'][$i] ); 
                if ( $url == "" || $name == "" || $_POST['nr'][$i] == "" || !is_numeric( $_POST['nr'][$i] ) ) 
                {
                    ++$i;   
                }  else{ 
                $selfTarget = TRUE; 
                if ( substr( $url, strlen( $url ) - 1 ) == "*" ) 
                { 
                    $url = substr( $url, 0, strlen( $url ) - 1 ); 
                    $selfTarget = FALSE; 
                } 
                if ( isset( $this->playerLinks[$_POST['nr'][$i]] ) ) 
                { 
                    ++$_POST['nr'][$i]; 
                } 
                $this->playerLinks[$_POST['nr'][$i]] = array( 
                    "linkName" => $name, 
                    "linkHref" => $url, 
                    "linkSelfTarget" => $selfTarget 
                ); 
                   ++$i;   
                } 
            } 
            ksort( $this->playerLinks ); 
            $links = ""; 
            foreach ( $this->playerLinks as $link ) 
            { 
                if ( $links != "" ) 
                { 
                    $links .= "\n\n"; 
                } 
                $links .= $link['linkName']."\n".$link['linkHref']."\n".( $link['linkSelfTarget'] ? "?" : "*" );
            } 
            $m = new LinksModel( ); 
            $m->changePlayerLinks( $this->player->playerId, $links ); 
            $m->dispose( ); 
            $this->redirect('profile?links');
        } 
            }
        $this->profileData['rank'] = $m->getPlayerRank($uid, $this->profileData['total_people_count'] * 10 + $this->profileData['villages_count']);
        if ($this->isPost())
            {
            if (($this->fullView && isset($_POST['e'])))
                {
if ( $this->dataGame['blocked_time'] > time() ){
$this->redirect ('banned');
return null;
}

                switch ($_POST['e'])
                {
                    case 1:
                        $avatar  = (isset($_POST['avatar']) ? htmlspecialchars($_POST['avatar']) : '');
                        $_y_     = (((isset($_POST['jahr']) && 1930 <= intval($_POST['jahr'])) && intval($_POST['jahr']) <= 2005) ? intval($_POST['jahr']) : '');
                        $_m_     = (((isset($_POST['monat']) && 1 <= intval($_POST['monat'])) && intval($_POST['monat']) <= 12) ? intval($_POST['monat']) : '');
                        $_d_     = (((isset($_POST['tag']) && 1 <= intval($_POST['tag'])) && intval($_POST['tag']) <= 31) ? intval($_POST['tag']) : '');
                        $filter = new FilterWordsModel();
                        $newData = array(
                            'gender' => ((0 <= intval($_POST['mw']) && intval($_POST['mw']) <= 2) ? intval($_POST['mw']) : 0),
                            'house_name' => ($filter->FilterWords(isset($_POST['ort'])) ? $filter->FilterWords(htmlspecialchars($_POST['ort']))  : ''),
/////////////////////////////////////////
                            'village_name' => $_POST['dnm'],
/////////////////////////////////////////
                            'used1' => htmlspecialchars($_POST['used1']),
                            'description1' => (isset($_POST['be1']) ? $filter->FilterWords(htmlspecialchars($_POST['be1'])) : ''),
                            'description2' => (isset($_POST['be2']) ? $filter->FilterWords(htmlspecialchars($_POST['be2'])) : ''),
                            'birthData' => $_y_ . '-' . $_m_ . '-' . $_d_,
                            'villages' => $this->data['villages_data']
                        );
                        $m->editPlayerProfile($this->player->playerId, $newData);
                        $m->dispose();
                        $this->redirect('profile');
                     case 2:
//is_haat
if (isset ($_POST['haat'])) {
if ($_POST['haat'] == 1) {
$tatarzx = new QueueModel();
$tatarzx->provider->executeQuery2("UPDATE p_players SET is_haat=1 WHERE id ='".$this->player->playerId."'");
}else {
$tatarzx = new QueueModel();
$tatarzx->provider->executeQuery2("UPDATE p_players SET is_haat=0 WHERE id ='".$this->player->playerId."'");
}
}


if (isset ($_POST['in'])) {
if ($_POST['in'] == 1) {
$tatarzx = new QueueModel();
$tatarzx->provider->executeQuery2("UPDATE p_players SET totalgold=1 WHERE id ='".$this->player->playerId."'");
}else {
$tatarzx = new QueueModel();
$tatarzx->provider->executeQuery2("UPDATE p_players SET totalgold=0 WHERE id ='".$this->player->playerId."'");
}
}
if (isset ($_POST['new_name']) AND md5($_POST['n_pwd']) == $this->data['pwd'] AND $this->data['new_p'] < 3 AND $this->data['gold_num'] > 499) {
$nam = trim(htmlspecialchars($_POST['new_name']));
$tatarzx = new QueueModel();
$num_n = $tatarzx->provider->fetchScalar("SELECT COUNT(*) FROM p_players WHERE name='".$nam."'");
if ( !preg_replace('/[ًًٌٌٍٍََُُِِ   ~ْ}­­­­­­­­­{	 ׅׅׅׅׅׅׅׅׅׅׅׅׅׅׅ~ْ]/', '', $nam) ){

}else
if (strlen($nam) > 3 AND strlen($nam) < 20 AND $nam != $this->data['name'] AND !$num_n ) {
$tatarzx->provider->executeQuery2("UPDATE money_log SET usernam='".$nam."' WHERE usernam='".$this->data['name']."'");

$tatarzx->provider->executeQuery2("UPDATE p_players SET new_p=new_p+1 WHERE id ='".$this->player->playerId."'");
$tatarzx->provider->executeQuery2("UPDATE p_players SET gold_num=gold_num-500 WHERE id ='".$this->player->playerId."'");
$tatarzx->provider->executeQuery2("UPDATE p_players SET name='".$nam."' WHERE id='".$this->player->playerId."'");
$tatarzx->provider->executeQuery2("UPDATE p_villages SET player_name='".$nam."' WHERE player_id='".$this->player->playerId."'");
}
}

                        if ((((((isset($_POST['pw1']) && isset($_POST['pw2'])) && isset($_POST['pw3'])) && $_POST['pw2'] == $_POST['pw3']) && 4 <= strlen($_POST['pw2'])) && strtolower($this->profileData['pwd']) == strtolower(md5($_POST['pw1']))))
                            {
                            $m->changePlayerPassword($this->player->playerId, md5($_POST['pw2']));
                            }
                        if ((((isset($_POST['email_alt']) && isset($_POST['email_neu'])) && strtolower($this->profileData['email']) == strtolower($_POST['email_alt'])) && preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $_POST['email_neu'])))
                            {
							$code_email_alt = substr(md5(sha1(time())),0,5); 
							$code_email_new = substr(sha1(md5(time())),0,5);
							$email_neu = $_POST['email_neu'];
							$email_alt = "1:$email_neu:$code_email_alt:$code_email_new";
							$m->changePlayerEmail_ate_new( $this->player->playerId, $_POST['email_neu'], $email_alt );
                            // email alt
							$to = $_POST['email_alt'];
							$from = $this->appConfig['system']['email'];
							$subject = 'تغيير البريد الإلكتروني - الخطوة 1‏';
							$message = sprintf( LANGUI_PROFILE_SEND_EMAIL_ALT, $this->data['name'], $code_email_alt );
							WebHelper::sendmail( $to, $from, $subject, $message );

							// email new
							$to_n = $_POST['email_neu'];
							$from_n = $this->appConfig['system']['email'];
							$subject_n = 'تغيير البريد الإلكتروني - الخطوة 1‏';
							$message_n = sprintf( LANGUI_PROFILE_SEND_EMAIL_NEW, $this->data['name'], $code_email_new );
							WebHelper::sendmail( $to_n, $from_n, $subject_n, $message_n );
							$this->redirect('profile?t=2');
							}
						if ( isset($_POST['code_email_alt']) && isset($_POST['code_email_neu']) )
                            {
							list ($activ, $email_new, $code_email_alt, $code_email_new) = explode (':', $this->data['email_alt']);
							if($activ == 1 AND $code_email_alt == $_POST['code_email_alt'] AND $code_email_new == $_POST['code_email_neu'] ){
                             $m->changePlayerEmail($this->player->playerId, $email_new);
							 $m->email_cancel($this->player->playerId);
							 $this->redirect('profile?t=2');
							 }
                            }
							
						if ((((((isset($_POST['del']) && $_POST['del'] == 1) && strtolower($this->profileData['pwd']) == strtolower(md5($_POST['del_pw']))) && !$this->isPlayerInDeletionProgress()) && !$this->isGameTransientStopped()) && !$this->isGameOver()))
                            {
                            $this->queueModel->addTask(new QueueTask(QS_ACCOUNT_DELETE, $this->player->playerId, 259200));
                            }
                           if ((((isset($_POST['v1']) || isset($_POST['v2']) && trim($_POST['v1']) != '') || trim($_POST['v2']) != '')  && sizeof($this->myAgentPlayers) < 2))
                            {
							$v1 = trim($_POST['v1']);
							if($v1 != ''){ $name = trim($_POST['v1']); }
							else{ $name = trim($_POST['v2']); }
							if(!$name){ return NULL; }

                            $aid = $m->getPlayerIdByName($name);
                            if (((0 < intval($aid) && $aid != $this->player->playerId) && !isset($this->myAgentPlayers[$aid])))
                                {
                                $_agentsFor = $m->getPlayerAgentForById(intval($aid));
                                if (1 < sizeof(explode(',', $_agentsFor)))
                                    {
                                    $this->errorText = profile_setagent_err_msg;
                                    }
                                else
                                    {
									if($v1 != ''){
									if($_POST['e1'] == 1){ $actionsNaw .= 1; }else{ $actionsNaw .= 0; }
									if($_POST['e2'] == 1){ $actionsNaw .= 1; }else{ $actionsNaw .= 0; }
									if($_POST['e3'] == 1){ $actionsNaw .= 1; }else{ $actionsNaw .= 0; }
									if($_POST['e4'] == 1){ $actionsNaw .= 1; }else{ $actionsNaw .= 0; }
									}else{
									if($_POST['e5'] == 1){ $actionsNaw .= 1; }else{ $actionsNaw .= 0; }
									if($_POST['e6'] == 1){ $actionsNaw .= 1; }else{ $actionsNaw .= 0; }
									if($_POST['e7'] == 1){ $actionsNaw .= 1; }else{ $actionsNaw .= 0; }
									if($_POST['e8'] == 1){ $actionsNaw .= 1; }else{ $actionsNaw .= 0; }
									}
                                    $this->myAgentPlayersName = $name;
                                    $m->setMyAgents($this->player->playerId, $this->data['name'], $this->myAgentPlayersName, $actionsNaw, $aid);
									}
                                
								}
                            }                            $this->redirect('profile?t=2');

						break;
						case 3:
                        break;
                    case 4:
                        {

                        }
                }
                }
            }
        else
            {
            if ($this->selectedTabIndex == 2)
                {
				echo $_POST['actions1'];
                if ((isset($_GET['aid']) && 0 < intval($_GET['aid'])))
                    {
                    $aid = intval($_GET['aid']);
                    if (isset($this->myAgentPlayers[$aid]))
                        {
                        unset($this->myAgentPlayers[$aid]);
                        $m->removeMyAgents($this->player->playerId, $this->myAgentPlayers, $aid);
						$this->redirect('profile?t=2');
                        }
                    }
                else
                    {
                    if ((isset($_GET['afid']) && 0 < intval($_GET['afid'])))
                        {
                        $aid = intval($_GET['afid']);
                        if (isset($this->agentForPlayers[$aid]))
                            {
                            unset($this->agentForPlayers[$aid]);
                            $m->removeAgentsFor($this->player->playerId, $this->agentForPlayers, $aid);
							$this->redirect('profile?t=2');
                            }
                        }
                    }

                if ((isset($_GET['qid']) && 0 < intval($_GET['qid'])))
                    {
                    $this->queueModel->cancelTask($this->player->playerId, intval($_GET['qid']));
                    }
					
				if (isset($_GET['email_abbrechen']))
                    {
                    $m->email_cancel($this->player->playerId);
					$this->redirect('profile?t=2');
                    }
					
                }
            }
        if ($this->selectedTabIndex == 0)
            {
            $this->villagesCount = sizeof(explode(',', $this->profileData['villages_id']));
            $this->villages      = $m->getVillagesSummary($this->profileData['villages_id']);
            }
        else
            {
            if ($this->selectedTabIndex == 1)
                {
                $birth_date = $this->profileData['birth_date'];
                if (!$birth_date)
                    {
                    $birth_date = '0-0-0';
                    }
                list($year, $month, $day) = explode('-', $birth_date);
                $this->birthDate = array(
                    'year' => $year,
                    'month' => $month,
                    'day' => $day
                );
                }
            }
        $m->dispose();
        }
    function canCancelPlayerDeletionProcess()
        {
        if (!QueueTask::iscancelabletask(QS_ACCOUNT_DELETE))
            {
            return TRUE;
            }
        $timeout = QueueTask::getmaxcanceltimeout(QS_ACCOUNT_DELETE);
        if (0 - 1 < $timeout)
            {
            $elapsedTime = $this->queueModel->tasksInQueue[QS_ACCOUNT_DELETE][0]['elapsedTime'];
            if ($timeout < $elapsedTime)
                {
                return TRUE;
                }
            }
        return TRUE;
        }
    function preRender()
        {
        parent::prerender();
        if (isset($_GET['uid']))
            {
            $this->villagesLinkPostfix .= '&uid=' . intval($_GET['uid']);
            }
        if (0 < $this->selectedTabIndex)
            {
            $this->villagesLinkPostfix .= '&t=' . $this->selectedTabIndex;
            }
        }
  function getProfileDescription($text)
        {
//Here IS [mycode] is show in profile
        $contractsStr = '';
        $img    = '';
        $medals = explode(',', $this->profileData['medals']);
        foreach ($medals as $medal)
            {
            $contractsStr .= '<div><b>معجزه العالم</b><br>===============<br><img src="'.$GLOBALS['AppConfig']['system']['linksite'].'default/img/ww_start.jpg" border="0" alt="معجزه العالم"><br>===============</div>';
            }
        if (!isset($this->bbCodeReplacedArray['tatara']))
            {
if ($this->profileData['player_type'] == PLAYERTYPE_TATAR){
            $text  = preg_replace('/\[tatara\]/', $contractsStr, $text);
}
            }
            $img = "<img class=\"%s\" src=\"".$GLOBALS['AppConfig']['system']['linksite']."x.gif\" onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>%s</p>')\">";
        $contractsStr = '';
        $img    = '';
        $medals = explode(',', $this->profileData['medals']);
        foreach ($medals as $medal)
            {
//
$f = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/end/".$this->profileData['email'];
if(file_exists($f)){
            $contractsStr = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/ww.png' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>أنت واحد من محترفي حرب التتار! لذلك سنمنحك هذا الوسام!<br>يحصل على هذا الوسام اللاعبون الذين حصلو على المعجزة في سيرفرات حرب التتار فقط هذه الوسام دليل على احترافك للعبه .<br>اعتباراً من الآن، ستحصل على هذا الوسام على كل عضوية تسجّلها في حرب التتار</p>')\">";
}
            }
        foreach ($medals as $medal)
            {
//
$f2 = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/thanks/".$this->profileData['email'];
if(file_exists($f2)){
            $contractsStr2 = "<img  width='80' height='80' src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/www.png' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>يحصل على هذا الوسام اللاعبون 
الاوفياء لحرب التتار انه دليل على احترافك اللعبه لأكثر من سنه واعتبارا من الان ستحصل على هذا الوسام على كل عضوية تسجّلها في حرب التتار</p>')\">";
}
            }


///////////////////////////////////////////////////////////
        foreach ($medals as $medal)
            {
//
$f3 = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/kings/att/".$this->profileData['email'];
if(file_exists($f3)){
$fp = fopen($f3, 'r');
$r = fread($fp, filesize($f3));
            $contractsStr3 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/1.png' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>يحصل على هذا الوسام اللاعبون الذين صبغو قرى اعدائهم بدماء جيوشهم , لاعبين اشاوس , حاربو وقاتلو بثبات يحصل على هذا الوسام اللاعب الذي بذل اقصى جهده لكسر رقم قياسي في عدد قتلاه عدد قتلى هذا الوسام قد وصلت الى  : ".$r."</p>')\">";
}
            }

        foreach ($medals as $medal)
            {
//
$f4 = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/kings/deff/".$this->profileData['email'];
if(file_exists($f4)){
$fp = fopen($f4, 'r');
$r = fread($fp, filesize($f4));
            $contractsStr4 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/2.png' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>يحصل على هذا الوسام اللاعبون الذين علقو جثث اعدائهم على اسوارهم , الذين قاتلو بثبات وماتهاونو ابدا في الحروب , وهذا اللاعب بذل اقصى جهده ليكسر رقم قياسي لاحد يستطيع كسره في الدفاع هذا اللاعب قتل في حرب التتار من سيرفر حرب التتارات  : ".$r."</p>')\">";
}
            }



        foreach ($medals as $medal)
            {
//
$f7 = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/kings/hero/".$this->profileData['email'];
if(file_exists($f7)){
$fp = fopen($f7, 'r');
$r = fread($fp, filesize($f7));
            $contractsStr7 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/VV.png' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>يحصل على هذا الوسام اللاعبون الذين دربو الابطال وحصنوهم ودربوهم اشد التدريب هذا اللاعب في يوما من الايام امتلك بطل الابطال الذي قوته في الحروب : ".$r."</p>')\">";
}
            }




        foreach ($medals as $medal)
            {
//
$f5 = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/kings/dev/".$this->profileData['email'];
if(file_exists($f5)){
$fp = fopen($f5, 'r');
$r = fread($fp, filesize($f5));
            $contractsStr5 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/4.png' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>يحصل على هذا الوسام اللاعبون الذين كسرو رقم قياسي في التطوير الرقم : ".$r."</p>')\">";
}
            }

        foreach ($medals as $medal)
            {
//
$f6 = ROOT_PATH.DIRECTORY_SEPARATOR."ndc/kings/win/".$this->profileData['email'];
if(file_exists($f6)){
$fp = fopen($f6, 'r');
$r = fread($fp, filesize($f6));
            $contractsStr6 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/3.png' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>يحصل على هذا الوسام اللاعبون الذين كسرو رقم قياسي في النهب الرقم : ".$r."</p>')\">";
}
            }
        /*foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 100){
	            $pop1 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/20.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز ال100 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 150){
	            $pop2 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/21.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 150 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 200){
	            $pop3 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/22.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 200 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 250){
	            $pop4 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/23.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 250 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 300){
	            $pop5 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/24.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 300 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 350){
	            $pop6 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/25.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 350 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 400){
	            $pop6 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/26.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 400 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 450){
	            $pop7 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/27.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 450 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 500){
	            $pop8 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/28.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 500 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 550){
	            $pop9 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/29.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 550 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 600){
	            $pop10 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/30.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 600 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 650){
	            $pop11 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/31.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 650 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 700){
	            $pop13 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/32.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 700 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 750){
	            $pop14 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/33.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 750 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 800){
	            $pop15 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/34.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 800 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 850){
	            $pop16 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/35.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 850 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 900){
	            $pop17 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/36.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 900 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 950){
	            $pop18 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/37.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 950 ساكن".$r."</p>')\">";

}
}
        foreach ($medals as $medal)
            {
$tatarzx = new QueueModel();

$is->total = $tatarzx->provider->fetchRow ( "SELECT SUM(people_count) as t FROM p_villages WHERE is_oasis=0 AND player_id='".$this->profileData['id']."'");
$pop=$is->total['t'];
if($pop > 1000){
	            $pop19 = "<img src='".$GLOBALS['AppConfig']['system']['linksite']."default/img/medals/38.gif' onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>تم حصول اللاعب على هذا الوسام لكسره حاجز 1000 ساكن".$r."</p>')\">";

}
}*/

        if (!isset($this->bbCodeReplacedArray['hi']))
            {
            $text  = preg_replace('/\[Take_your\]/', $contractsStr, $text);
            $text  = preg_replace('/\[Thank_you\]/', $contractsStr2, $text);
            $text  = preg_replace('/\[att\]/', $contractsStr3, $text);
            $text  = preg_replace('/\[deff\]/', $contractsStr4, $text);
            $text  = preg_replace('/\[dev\]/', $contractsStr5, $text);
            $text  = preg_replace('/\[win\]/', $contractsStr6, $text);
            $text  = preg_replace('/\[hero\]/', $contractsStr7, $text);
			/*$text  = preg_replace('/\[medal1\]/', $pop1, $text);
            $text  = preg_replace('/\[medal2\]/', $pop2, $text);
            $text  = preg_replace('/\[medal3\]/', $pop3, $text);
            $text  = preg_replace('/\[medal4\]/', $pop4, $text);
            $text  = preg_replace('/\[medal5\]/', $pop5, $text);
            $text  = preg_replace('/\[medal6\]/', $pop6, $text);
            $text  = preg_replace('/\[medal7\]/', $pop7, $text);
            $text  = preg_replace('/\[medal8\]/', $pop8, $text);
            $text  = preg_replace('/\[medal9\]/', $pop9, $text);
            $text  = preg_replace('/\[medal10\]/', $pop10, $text);
            $text  = preg_replace('/\[medal11\]/', $pop11, $text);
            $text  = preg_replace('/\[medal12\]/', $pop12, $text);
            $text  = preg_replace('/\[medal13\]/', $pop13, $text);
            $text  = preg_replace('/\[medal14\]/', $pop14, $text);
            $text  = preg_replace('/\[medal15\]/', $pop15, $text);
            $text  = preg_replace('/\[medal16\]/', $pop16, $text);
            $text  = preg_replace('/\[medal17\]/', $pop17, $text);
            $text  = preg_replace('/\[medal18\]/', $pop18, $text);
            $text  = preg_replace('/\[medal19\]/', $pop19, $text);
            $text  = preg_replace('/\[medal20\]/', $pop20, $text);*/
            }
            $img = "<img class=\"%s\" src=\"".$GLOBALS['AppConfig']['system']['linksite']."x.gif\" onmouseout=\"med_closeDescription()\" onmousemove=\"med_mouseMoveHandler(arguments[0],'<p>%s</p>')\">";
        $medals = explode(',', $this->profileData['medals']);
        foreach ($medals as $medal)
            {
            if (trim($medal) == '')
                {
                continue;
                }
            list($index, $rank, $week, $points) = explode(':', $medal);
            if (!isset($this->gameMetadata['medals'][$index]))
                {
                continue;
                }
            $medalData = $this->gameMetadata['medals'][$index];
            $bbCode    = '';
            if ($index == 0)
                {
                $bbCode   = intval($medalData['BBCode']);
                $postfix  = (0 < $this->profileData['protection_remain_sec'] ? '' : 'd');
                $cssClass = $medalData['cssClass'] . $postfix;
                $altText  = htmlspecialchars(sprintf(constant('medal_' . $medalData['textIndex'] . $postfix), ($postfix == 'd' ? $this->profileData['registration_date'] : $this->profileData['protection_remain'])));
                }
            elseif($index > 8 ){
                $bbCode   = intval($medalData['BBCode']) + intval($week) * 10 + (intval($rank) - 1);
                $cssClass = 'medal ' . $medalData['cssClass'] . '_' . $rank;
                $altText  = htmlspecialchars(sprintf('<tr><th>%s %s</th></tr>', constant('medal_' . $medalData['textIndex']),$week));
			}else
                {
				if ($index == 4){ $points_w = profile_medal_txt_points_w; }else if ($index == 1) {$points_w = profile_medal_txt_points_d;} else { $points_w = profile_medal_txt_points; }
                $bbCode   = intval($medalData['BBCode']) + intval($week) * 10 + (intval($rank) - 1);
                $cssClass = 'medal ' . $medalData['cssClass'] . '_' . $rank;
                $altText  = htmlspecialchars(sprintf('<tr><th>' . profile_medal_txt_cat . '    :     </th><td>%s</td></tr><br><tr><th>' . profile_medal_txt_week . '    :   </th><td>%s</td></tr><br><tr><th>' . profile_medal_txt_rank . '  :  </th><td>%s</td></tr><br><tr><th>%s :  </th><td>%s</td></tr>', constant('medal_' . $medalData['textIndex']), $week, $rank, $points_w, number_format($points)));
                }
            if (!isset($this->bbCodeReplacedArray[$bbCode]))
                {
                $count = 0;
                $text  = preg_replace('/\[#' . $bbCode . '\]/', sprintf($img, $cssClass, $altText), $text, 1, $count);
                if (0 < $count)
                    {
                    $this->bbCodeReplacedArray[$bbCode] = $count;
                    }
                }
            }
                return nl2br ($text);
        }
    }
$p = new GPage();
$p->run();

?>