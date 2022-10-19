<?php
require('.' . DIRECTORY_SEPARATOR . 'ftd-core' . DIRECTORY_SEPARATOR . 'boot.php');
require_once(MODEL_PATH . 'alliance.php');
require_once(MODEL_PATH."wordsfilter.php");
class GPage extends SecureGamePage
    {
    var $selectedTabIndex = null;
    var $fullView = null;
    var $hasAlliance = FALSE;
    var $allianceData = NULL;
    var $lastReports = NULL;
    var $hasErrors = FALSE;
    var $invitesResult = -1;
    var $contracts = null;
    var $bbCodeReplacedArray = array();
    public $chats = NULL;
    public $Filter = NULL;
    function GPage()
        {
        parent::securegamepage();
        $this->viewFile        = 'alliance.phtml';
        $this->contentCssClass = 'alliance';
        }
    function load()
        {
        parent::load();
        $m                  = new AllianceModel();
        $allianceId         = 0;
        $this->allianceData = NULL;
        if ((isset($_GET['id']) && 0 < intval($_GET['id'])))
            {
            $allianceId         = intval($_GET['id']);
            $this->allianceData = $m->getAllianceData($allianceId);
            }
        if ($this->allianceData == NULL)
            {
            $allianceId = intval($this->data['alliance_id']);
            if ($allianceId <= 0)
                {
                $this->hasAlliance = FALSE;
                return null;
                }
            $this->allianceData = $m->getAllianceData($allianceId);
            }
        $this->hasAlliance      = TRUE;
        $this->fullView         = $allianceId == intval($this->data['alliance_id']);
        $this->selectedTabIndex = 0;
if (isset($_GET['chat'])) {
                $this->Filter = new FilterWordsModel();
                if($this->isPost() && isset($_POST['text'])){
                        $text = stripslashes(htmlspecialchars(trim($_POST['text'])));
                        $alliance_id = $this->data['alliance_id'];
                        if($text != ""){
                                $m->SendToChat( $this->data['name'], $this->player->playerId, $text, $alliance_id );
                        }
                }
                $m->DeleteOldChat($this->data['alliance_id']);
                $this->chats = $m->GetFromChat($this->data['alliance_id']);
                $m->dispose();
}
        if ($this->fullView)
            {
            $this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 0 <= intval($_GET['t'])) && intval($_GET['t']) <= 4) ? intval($_GET['t']) : 0);
            if (($this->selectedTabIndex == 1 && !$this->hasAllianceEditRole()))
                {
                $this->selectedTabIndex = 0;
                }
            }
        if ($this->isPost())
            {
            if ((($this->fullView && $this->selectedTabIndex == 1) && $this->hasAllianceEditRole()))
                {
                $newData = array(
                    'name' => ((isset($_POST['aname1']) && trim(stripslashes($_POST['aname1'])) != '' && strlen($_POST['aname1']) < 10 ) ? strip_tags($_POST['aname1']) : $this->allianceData['name']),
                    'name2' => ((isset($_POST['aname2']) && trim(stripslashes($_POST['aname2'])) != '' && strlen($_POST['aname2']) < 25 ) ? strip_tags($_POST['aname2']) : $this->allianceData['name2']),
                    'description1' => strip_tags($_POST['be1']),
                    'description2' => strip_tags($_POST['be2'])
                );
                $m->editAllianceData(intval($this->data['alliance_id']), $newData, $this->allianceData['players_ids']);
                $m->dispose();
                $this->redirect('alliance');
                return null;
                }
            }
        if (((((($this->selectedTabIndex == 0 && isset($_GET['d'])) && 0 < intval($_GET['d'])) && $this->hasAllianceRemovePlayerRole()) && $this->player->playerId != intval($_GET['d'])) && $this->isMemberOfAlliance(intval($_GET['d']))))
            {

            $this->allianceData['players_ids'] = $m->removeFromAlliance(intval($_GET['d']), $allianceId, $this->allianceData['players_ids'], $this->allianceData['player_count']);


                                        $global = new GlobalModel();
$namek = $m->getPlayerName($_GET['d']);
                                        $global->inserttones($allianceId , 6 , $this->data['name'] , $namek['name'], $this->player->playerId , $_GET['d']);

            --$this->allianceData['player_count'];
            }
        else
            {
            if ($this->selectedTabIndex == 2)
                {
                $lastReportsType = 0;
                if (isset($_GET['ac']))
                    {
                    if ($_GET['ac'] == 1)
                        {
                        $lastReportsType = 1;
                        }
                    else if ($_GET['ac'] == 2)
                        {
                        $lastReportsType = 2;
                        }
                    }
                $this->lastReports = $m->getLatestReports($this->allianceData['players_ids'], $lastReportsType);
                }
            else
                {
                if ($this->selectedTabIndex == 3)
                    {

                         if (isset($_POST['po']))
                    {
                                        switch ($_POST['o'])
                    {
                                        case 1:
                                        $this->redirect('alliance?t=3&a=4');
                                        break;
                                        case 2:
                                        $this->redirect('alliance?t=3&a=5');
                                        break;
                                        case 3:
                                        $this->redirect('alliance?t=3&a=6');
                                        break;
                                        case 4:
                                        $this->redirect('alliance?t=1');
                                        break;
                                        case 5:
                                        $this->redirect('alliance?t=3&a=2');
                                        break;
                                        case 6:
                                        $this->redirect('alliance?t=3&a=1');
                                        break;
                                        case 7:
                                        $this->redirect('alliance?t=3&a=3');
                                        break;
                                        }
                                        }
                    if (isset($_GET['a']))
                        {
                        switch ($_GET['a'])
                        {
            case 1:
                                                                if (!$this->hasAllianceInviteRoles())
                                                                {
                                                                        unset ($_GET['a']);
                                                                        break;
                                                                }
                                                                $this->allianceData['players_invites'] = array();
                                                                if (trim ($this->allianceData['invites_player_ids']) != '')
                                                                {
                                                                        $invites = explode ("\n", trim ($this->allianceData['invites_player_ids']));
                                                                        foreach ($invites as $invite)
                                                                        {
                                                                                list ($pid, $pname) = explode (" ", $invite, 2);
                                                                                $this->allianceData['players_invites'][$pid] = $pname;
                                                                        }
                                                                }
                                                                if ( ($this->isPost() && isset ($_POST['a_name'])))
                                                                {
                                                                        $pid = intval ($m->getPlayerId ($_POST['a_name']));
                                                                        if (0 < $pid)
                                                                        {
                                                                                if (!isset ($this->allianceData['players_invites'][$pid]))
                                                                                {
                                                                                        $this->invitesResult                         = 2;
                                                                                        $this->allianceData['players_invites'][$pid] = $_POST['a_name'];
                                                                                        $m->addAllianceInvites ($pid, $allianceId);
                                        $global = new GlobalModel();
                                        $global->inserttones($allianceId , 3 , $this->data['name'] , $_POST['a_name'] , $this->player->playerId , $pid);


                                                                                }
                                                                        }
                                                                        else
                                                                        {
                                                                                $this->invitesResult = 1;
                                                                        }
                                                                }
                                                                if ( ( (isset ($_GET['d']) && 0 < intval ($_GET['d'])) && isset ($this->allianceData['players_invites'][intval ($_GET['d'])])))
                                                                {
                                                                        unset ($this->allianceData['players_invites'][intval ($_GET['d'])]);
                                                                        $m->removeAllianceInvites (intval ($_GET['d']), $allianceId);
                                                                }
                                                                break;
                            case 2:
                                if (!$this->hasAllianceEditContractRole())
                                    {
                                    unset($_GET['a']);
                                    break;
                                    }
                                $contracts_alliance_id = trim($this->allianceData['contracts_alliance_id']);
                                $contracts             = array();
                                if ($contracts_alliance_id != '')
                                    {
                                    $contracts_alliance_idArr = explode(',', $contracts_alliance_id);
                                    foreach ($contracts_alliance_idArr as $item)
                                        {
                                        list($aid, $pendingStatus) = explode(' ', $item);
                                        $contracts[$aid] = $pendingStatus;
                                        }
                                    }
                                $this->hasErrors = TRUE;
                                if (!$this->isPost())
                                    {
                                    if (((isset($_GET['d']) && 0 < intval($_GET['d'])) && isset($contracts[$_GET['d']])))
                                        {
                                        unset($contracts[$_GET['d']]);
                                        $m->removeAllianceContracts($allianceId, intval($_GET['d']));
                                        }


                                    if (((isset($_GET['dw']) && 0 < intval($_GET['dw']))))
                                        {
                                        $m->removeAlliancewar($allianceId, $_GET['dw']);
$this->redirect ('alliance?t=3&a=2');
                                        }


                                    if (((isset($_GET['c']) && 0 < intval($_GET['c'])) && isset($contracts[$_GET['c']])))
                                        {
                                        $contracts[$_GET['c']] = 0;
                                        $m->acceptAllianceContracts($allianceId, intval($_GET['c']));
                                        }
                                    }
                                else if ((isset($_POST['a_name']) && trim($_POST['a_name']) != '' && $_POST['dipl'] == '1'))

                                    {
                                    $caid = intval($m->getAllianceId(trim($_POST['a_name'])));
                                    if ((0 < $caid && !isset($contracts[$caid])))
                                        {
                                        $m->addAllianceContracts($allianceId, $caid);
                                        $global = new GlobalModel();
                                        $global->inserttones($allianceId , 5 , $this->allianceData['name'] , $_POST['a_name'] , $allianceId , $caid);
                                        $global->inserttones($caid , 5 , $this->allianceData['name'] , $_POST['a_name'] , $allianceId , $caid);
                                        $contracts[$caid] = 1;
                                        $this->hasErrors  = FALSE;
                                        }
                                    }
if ((isset($_POST['a_name']) && trim($_POST['a_name']) != '' && $_POST['dipl'] == '2'))
                                    {
                                    $caid = intval($m->getAllianceId(trim($_POST['a_name'])));
                                    if ((0 < $caid && !isset($contracts[$caid])))
                                        {
                                        $global = new GlobalModel();
                                        $global->inserttones($allianceId , 4 , $this->allianceData['name'] , $_POST['a_name'] , $allianceId , $caid);
                                        $global->inserttones($caid , 4 , $this->allianceData['name'] , $_POST['a_name'] , $allianceId , $caid);

                                        $m->addAllianceWars($allianceId, $caid);
                                        $m->addAllianceWars($caid ,$allianceId);
                                        $contracts[$caid] = 0;
                                        $this->hasErrors  = FALSE;
$this->redirect ('alliance?t=3&a=2');
}
                                    }
                                $this->contracts = $contracts;
                                break;
                            case 3:
                                  if ($this->isPost())
                                    {
                                    if($this->allianceData['player_count'] == 0){
                                    if ((isset($_POST['pw']) && strtolower($this->data['pwd']) == strtolower(md5($_POST['pw']))))
                                        {
                                        $m->addAllianceContracts($this->allianceData['id']);
                                        $m->dispose();
                                        $this->redirect('alliance');
                                        return null;
                                        }
                                                }else {
                                    if ((isset($_POST['pw']) && strtolower($this->data['pwd']) == strtolower(md5($_POST['pw']))))
                                        {
                                        $this->allianceData['players_ids'] = $m->removeFromAlliance($this->player->playerId, $allianceId, $this->allianceData['players_ids'], $this->allianceData['player_count']);
                                        --$this->allianceData['player_count'];
                                        $global = new GlobalModel();
                                        $global->inserttones($allianceId , 2 , $this->data['name'] , "", $this->player->playerId , "");

                                        $m->dispose();
                                        $this->redirect('alliance');
                                        return null;

                                        }
                                        }
                                    $this->hasErrors = TRUE;
                                    }
                                    
                                break;
                                    case 4:
                                                        if(!$this->hasAllianceSetRoles()){ $this->redirect('alliance'); exit; }
                                                        $this->rights = false;
                                                        $uid = intval($_POST['a_user']);
                                                        if ($this->isPost() AND  $uid != 0)
                                                        {
                                                        if($uid == 0 ){ $this->redirect('alliance'); }
                                                        if (!$this->isMemberOfAlliance($uid)){ $this->redirect('alliance'); }
                                                        $this->rights = TRUE;
                                                        $row = $m->getPlayerAllianceRole($uid);
                                                        $alliance_roles   = trim($row['alliance_roles']);
                                                        if ($row == NULL){ exit(0); }
                                                        $this->playerName = $row['name'];
                                                        list($rolesNumber, $roleName) = explode(' ', $alliance_roles, 2);
                                                        $this->playerRoles = array('name' => ($roleName == '.' ? '' : $roleName), 'roles' => $rolesNumber);

                                                        if(isset($_POST['a_titel'])){
                                                        $this->rights = TRUE;
                                                        $roleName = (isset($_POST['a_titel']) ? strip_tags($_POST['a_titel']) : '');
                                                        if (trim($roleName) == '') { $roleName = '.'; }
                                                        $roleNumber = 0;
                                                        if (isset($_POST['e1'])){
                                                        $roleNumber |= ALLIANCE_ROLE_SETROLES;
                                                        }
                                                        if (isset($_POST['e2'])){
                                                        $roleNumber |= ALLIANCE_ROLE_REMOVEPLAYER;
                                                        }
                                                        if (isset($_POST['e3'])){
                                                        $roleNumber |= ALLIANCE_ROLE_EDITNAMES;
                                                        }
                                                        if (isset($_POST['e4'])){
                                                        $roleNumber |= ALLIANCE_ROLE_EDITCONTRACTS;
                                                        }
                                                        if (isset($_POST['e5'])){
                                                        $roleNumber |= ALLIANCE_ROLE_SENDMESSAGE;
                                                        }
                                                        if (isset($_POST['e6'])){
                                                        $roleNumber |= ALLIANCE_ROLE_INVITEPLAYERS;
                                                        }
                                                        $m->setPlayerAllianceRole($uid, $roleName, $roleNumber);
                                                        $this->redirect('alliance');
                                                        }
                                                        }
                                                        break;
                            case 5:
                                                        if ($this->isPost() AND $this->hasAllianceEditRole( ))
                                                        {
                                                        if(trim ($_POST['ally1']) == $this->allianceData['name'] AND trim ($_POST['ally2']) == $this->allianceData['name2']){
                                                        return NULL;
                                                        }
                                                        $this->er = true;
                                                        if (!$m->allianceExists(trim ($_POST['ally1'])))
                                                        {
                                                        $m->editalliancename('name',trim ($_POST['ally1']),$allianceId);
                                                        $this->hasErrors = 'تم حفظ التغييرات';
                                                        $this->er = false;
                                                        }
                                                        if(trim ($_POST['ally2']) != '' AND trim ($_POST['ally2']) != $this->allianceData['name2']){
                                                        $m->editalliancename('name2',trim ($_POST['ally2']),$allianceId);
                                                        $this->hasErrors = 'تم حفظ التغييرات';
                                                        $this->er = false;
                                                        }
                                                        if(!$this->er){

                                                        }
                                                        if($this->er){
                                                        $this->hasErrors = 'الرمز مستخدم من غيرك';
                                                        }
                                                        }
                                                        break;
                                                        case 6;
                                                        if(!$this->hasAllianceRemovePlayerRole()){  $this->redirect('alliance'); }
                                                        $this->remove = false;
                                                        if ($this->isPost()){
                                                        $this->remove = true;
                                                        $this->passok = false;
                                                        if($this->isMemberOfAlliance(intval($_POST['a_user'])) == 1){
                                                        $this->passok = true;
                                                        }else{
                                                        $this->nameno = 'اللاعب ليس من ضمن تحالفك';
                                                        }
                                                        if(isset($_POST['pwd'])){
                                                        if(md5($_POST['pwd']) != $this->data['pwd'] ){
                                                        $this->nopass = 'كلمة المرور غير صحيحة';
                                                        }elseif($this->isMemberOfAlliance(intval($_POST['a_user'])) == 1){
                                                        $idpn = $this->player->playerId.','.intval($_POST['a_user']);

                                                        $this->nopass = 'تم ازالة اللاعب';
                                                        $this->allianceData['players_ids'] = $m->removeFromAlliance(intval($_POST['a_user']), $allianceId, $this->allianceData['players_ids'], $this->allianceData['player_count']);
            --$this->allianceData['player_count'];
                                                        }
                                                        }
                                                        }




                        }
                        }
                    }
                }
            }
        if ($this->selectedTabIndex == 0)
            {
            $contracts_alliance_id = trim($this->allianceData['contracts_alliance_id']);
            $this->contracts       = array();
            if ($contracts_alliance_id != '')
                {
                $contracts_alliance_idArr = explode(',', $contracts_alliance_id);
                foreach ($contracts_alliance_idArr as $item)
                    {
                    list($aid, $pendingStatus) = explode(' ', $item);
                    if ($pendingStatus == 0)
                        {
                        $this->contracts[$aid] = $m->getAllianceName($aid);
                        }
                    }
                }


            $war_alliance_id = trim($this->allianceData['war_alliance_id']);
            $this->war       = array();
            if ($war_alliance_id != '')
                {
                $war_alliance_idArr = explode(',', $war_alliance_id);
                foreach ($war_alliance_idArr as $war)
                    {
                        $this->war[$war] = $m->getAllianceName($war);
                       
                    }
                }


            $this->allianceData['rank']    = $m->getAllianceRank($allianceId, $this->allianceData['score']);
            $result                        = $m->getAlliancePlayers($this->allianceData['players_ids']);
            $this->allianceData['players'] = array();
            while (($result != NULL && $result->next()))
                {
                $this->allianceData['players'][] = array(
                    'id' => $result->row['id'],
                    'name' => $result->row['name'],
                    'color_name' => $result->row['color_name'],
                    'total_people_count' => $result->row['total_people_count'],
                    'alliance_roles' => $result->row['alliance_roles'],
                    'villages_count' => $result->row['villages_count'],
                    'lastLoginFromHours' => $result->row['lastLoginFromHours']
                );
                }
            }
        $m->dispose();
        }
    function _hasAllianceRole($role)
        {
        $alliance_roles = trim($this->data['alliance_roles']);
        if ($alliance_roles == '')
            {
            return FALSE;
            }
        list($roleNumber, $roleName) = explode(' ', $alliance_roles, 2);
        return $roleNumber & $role;
        }
    function hasAllianceEditRole()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_EDITNAMES);
        }

    function hasAllianceRemovePlayerRole()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_REMOVEPLAYER);
        }
    function hasAllianceSetRoles()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_SETROLES);
        }
    function hasAllianceInviteRoles()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_INVITEPLAYERS);
        }
    function hasAllianceEditContractRole()
        {
        return $this->_hasAllianceRole(ALLIANCE_ROLE_EDITCONTRACTS);
        }
    function preRender()
        {
        parent::prerender();
        if (isset($_GET['id']))
            {
            $this->villagesLinkPostfix .= '&id=' . intval($_GET['id']);
            }
        if (0 < $this->selectedTabIndex)
            {
            $this->villagesLinkPostfix .= '&t=' . $this->selectedTabIndex;
            }
        }
   function getAllianceName($aid)
        {
        $m = new AllianceModel();
        $n = $m->getAllianceName($aid);
        return (trim($n) != '' ? $n : '[?]');
        }
    function getAllianceDataFor($playerId)
        {
        $m = new AllianceModel();
        return $m->getAllianceDataFor($playerId);
        }

public function getCat( $allianceId )
{
$m = new AllianceModel( );
$result = $m->getCat( $allianceId );
while ( $result != NULL && $result->next( ) )
{
echo "<tr>";
if($this->hasAllianceEditRole( ))
{
echo "<td style='width: 1%'>";
echo "<table cellpadding='0' cellspacing='0' style='width: 1%'>";
echo "<tr>";
echo "<td><a href='alliance.php?t=4&order=1&catoid=".$result->row['id']."'><img src='ftd-style/default/img/f/up_arr.gif'></a></td>";
echo "<td>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='alliance.php?t=4&order=2&catoid=".$result->row['id']."'><img src='ftd-style/default/img/f/down_arr.gif'></a></td>";
echo "<td><a href='alliance.php?t=4&order=3&catoid=".$result->row['id']."'><img src='ftd-style/default/img/f/del.gif'></a></td>";
echo "</tr>";
echo "</table>";
echo "</td>";
} else {
echo "<td style='width: 1%'>";
echo "<img src='ftd-style/default/img/f/v_folder.gif'>";
echo "</td>";
}
echo "<td><a href='alliance.php?t=4&catid=".$result->row['id']."'>".$result->row['catn']."</a>";
echo "</td></tr>";
}
}


public function catorder( $order,$catoid )
{
if($this->hasAllianceEditRole( ))
{
$m = new AllianceModel( );
return $m->catorder($order,$catoid);
}
}
public function DeleteTID( $allianceId,$tid,$pid )
{
if( $pid == $this->player->playerId OR $this->hasAllianceEditRole( ) )
{
$m = new AllianceModel( );
return $m->DeleteTID( $allianceId,$tid );
}
return false;
}
public function getTopic( $allianceId,$tid )
{
$m = new AllianceModel( );
$result = $m->getTopic( $allianceId,$tid );
$nothing = 0;
while ( $result != NULL && $result->next( ) )
{
echo "<table cellpadding='1' cellspacing='1' id='posts'>";
echo "<thead><tr><th colspan='2'>";
echo $result->row['Topic'];
if( $result->row['pid'] == $this->player->playerId OR $this->hasAllianceEditRole( ) )
{
echo "<a href='alliance.php?t=4&did=".$result->row['id']."&id=".$result->row['pid']."'><img border='0' src='ftd-style/default/img/a/del.gif'></a>";
}
echo "</th></tr></thead>";
echo "<tr>";
echo "<td style='width: 25%'>";
echo LANGUI_ALLIANCE_T66;
echo "</td>";
echo "<td style='width: 75%'>";
echo LANGUI_ALLIANCE_T67;
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='profile.php?uid=".$result->row['pid']."'>".$result->row['pname']."</a></td>";
echo "<td><div style='font-size: small'>";
echo LANGUI_ALLIANCE_T68;
echo " ".$result->row['date'];
echo "</div>";
echo "<div style='border:1px dotted black;'></div>";
echo "<div>";
echo nl2br ($result->row['Subject']);
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "<br>";
$nothing += 1;
$this->getTopic1 = 0;
}
if($nothing == 0)
{
$this->getTopic1 = 1;
}
}
public function getPosts( $allianceId,$tid )
{
$m = new AllianceModel( );
$result = $m->getPosts( $allianceId,$tid );
while ( $result != NULL && $result->next( ) )
{
echo "<table cellpadding='1' cellspacing='1' id='posts'>";
echo "<thead><tr><th colspan='2'>";
echo $result->row['Topic'];
if( $result->row['pid'] == $this->player->playerId OR $this->hasAllianceEditRole( ) )
{
echo "<a href='alliance.php?t=4&did=".$result->row['id']."&id=".$result->row['pid']."'><img border='0' src='ftd-style/default/img/a/del.gif'></a>";
}
echo "</th></tr></thead>";
echo "<tr>";
echo "<td style='width: 25%'>";
echo LANGUI_ALLIANCE_T66;
echo "</td>";
echo "<td style='width: 75%'>";
echo LANGUI_ALLIANCE_T67;
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td><a href='profile.php?uid=".$result->row['pid']."'>".$result->row['pname']."</a></td>";
echo "<td><div style='font-size: small'>";
echo LANGUI_ALLIANCE_T68;
echo " ".$result->row['date'];
echo "</div>";
echo "<div style='border:1px dotted black;'></div>";
echo "<div>";
echo nl2br ($result->row['Subject']);
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "<br>";
}
}
public function getTopics( $catid,$allianceId )
{
$m = new AllianceModel( );
$result = $m->getTopics( $catid,$allianceId );
while ( $result != NULL && $result->next( ) )
{
echo "<tr><td><a href='alliance.php?t=4&tid=".$result->row['id']."'>".$result->row['Topic']."</a></td></tr>";
}
}
public function makeCat( $name,$allianceId )
{
$m = new AllianceModel( );
return $m->makeCat( $name,$allianceId );
}
public function fpost( $type,$allianceId,$cid,$pid,$pname,$topic,$subject )
{
$m = new AllianceModel( );
return $m->fpost( $type,$allianceId,$cid,$pid,$pname,$topic,$subject );
}


    function isMemberOfAlliance($playerId)
        {
        $players_ids = trim($this->allianceData['players_ids']);
        if ($players_ids == '')
            {
            return FALSE;
            }
        $arr = explode(',', $players_ids);
        foreach ($arr as $pid)
            {
            if ($pid == $playerId)
                {
                return TRUE;
                }
            }
        return FALSE;
        }


    function getOnlineStatus($lastLoginFromHours)
        {
        if ($lastLoginFromHours <= 1)
            {
            return '<img class="online1" src="'.$GLOBALS['AppConfig']['system']['linksite'].'x.gif" title="' . alliance_p_status1 . '" alt="' . alliance_p_status1 . '">';
            }
        if ($lastLoginFromHours <= 24)
            {
            return '<img class="online2" src="'.$GLOBALS['AppConfig']['system']['linksite'].'x.gif" title="' . alliance_p_status2 . '" alt="' . alliance_p_status2 . '">';
            }
        if ($lastLoginFromHours <= 24 * 3)
            {
            return '<img class="online3" src="'.$GLOBALS['AppConfig']['system']['linksite'].'x.gif" title="' . alliance_p_status3 . '" alt="' . alliance_p_status3 . '">';
            }
        if ($lastLoginFromHours <= 24 * 7)
            {
            return '<img class="online4" src="'.$GLOBALS['AppConfig']['system']['linksite'].'x.gif" title="' . alliance_p_status4 . '" alt="' . alliance_p_status4 . '">';
            }
        return '<img class="online5" src="'.$GLOBALS['AppConfig']['system']['linksite'].'x.gif" title="' . alliance_p_status5 . '" alt="' . alliance_p_status5 . '">';
        }
    function getAllianceDescription($text)
        {
        $img    = '<img class="%s" src="'.$GLOBALS['AppConfig']['system']['linksite'].'x.gif" onmouseout="med_closeDescription()" onmousemove="med_mouseMoveHandler(arguments[0],\'<p>%s</p>\')">';
        $medals = explode(',', $this->allianceData['medals']);
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
            $bbCode    = intval($medalData['BBCode']) + intval($week) * 10 + (intval($rank) - 1);
            $cssClass  = 'medal ' . $medalData['cssClass'] . '_' . $rank;
                $altText  = htmlspecialchars(sprintf('<table><tr><th>' . profile_medal_txt_cat . ':</th><td>%s</td></tr><tr><th>' . profile_medal_txt_week . ':</th><td>%s</td></tr><tr><th>' . profile_medal_txt_rank . ':</th><td>%s</td></tr> <tr><th>النقاط:</th><td>%s</td></tr></table>', constant('medal_' . $medalData['textIndex']), $week, $rank, $points));
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
        $contractsStr = 'ميثاق عدم أعتداء : <br>';
        $a = 0;
        foreach ($this->contracts as $aid => $aname)
            {
$a++;
            $contractsStr .= '<a href="alliance?id=' . $aid . '">' . $aname . '</a><br/>';
            }
if ($a == 0) {
$contractsStr .= '<div class="none">-</div>';
}
        if (!isset($this->bbCodeReplacedArray['contracts']))
            {
            $count = 0;
            $text  = preg_replace('/\[contracts\]/', $contractsStr, $text, 1, $count);
            if (0 < $count)
                {
                $this->bbCodeReplacedArray['contracts'] = $count;
                }
            }
$contractsStr = 'في الحرب مع : <br>';
$s = 0;
        foreach ($this->war as $aid => $aname)
            { $s++;
            $contractsStr .= '<a href="alliance?id=' . $aid . '">' . $aname . '</a><br/>';
            }
if ($s == 0) {
$contractsStr .= '<div class="none">-</div>';
}
        if (!isset($this->bbCodeReplacedArray['war']))
            {
            $count = 0;
            $text  = preg_replace('/\[war\]/', $contractsStr, $text, 1, $count);
            if (0 < $count)
                {
                $this->bbCodeReplacedArray['war'] = $count;
                }
            }
        return nl2br($text);
        }
        function getAllianceRoleCheckValue($role)
        {
        return ($this->playerRoles['roles'] & $role ? 'value="" checked="checked"' : 'value="0"');
        }
    }
$p = new GPage();
$p->run();
?>
