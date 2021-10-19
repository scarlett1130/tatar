<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
require_once MODEL_PATH . 'v2v.php';
class GPage extends ProcessVillagePage
{

        public function GPage()
        {
                parent::processvillagepage( );
                $this->viewFile = "looting.phtml";
                $this->contentCssClass = "a2b";
        }
        public function load()
        {
             parent::load( );
if (!$this->data['active_plus_account']){ 
$this->redirect ('plus?t=2');
exit;
}
$q                      = new QueueModel();
$newcode = md5(md5(md5(time()."twar.us")));
$q->provider->executeQuery2 ("UPDATE `p_players` SET  `farming` =  '".$newcode."' WHERE id='".$this->player->playerId."';");
$this->data['farming'] = $newcode;
if (isset ($_GET['addfarm'])) {
$num_farming = 250+$this->data['num_farm'];
$num_farming = (($num_farming/250)*250);
if ($this->data['gold_num'] >= $num_farming AND ($this->data['num_farm']+50) < 750) {
$q->provider->executeQuery2 ("UPDATE `p_players` SET  `num_farm` =  num_farm+250,gold_num=gold_num-".$num_farming." WHERE id='".$this->player->playerId."';");
}
                       header ("Location: looting?t=5");
                       exit;
}
             $num_farm = 250+$this->data['num_farm'];
             $this->num_farm = $num_farm;
             $this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 0 <= intval($_GET['t'])) && intval($_GET['t']) <= 6) ? intval($_GET['t']) : 0);
             $q                      = new QueueModel();

             $plus_true    = 1;
             if($plus_true == 0)
             {
                  $this->selectedTabIndex = 3;
                  $q->provider->executeQuery2 ("DELETE FROM p_looting WHERE pid='".$this->player->playerId."';");
             }
                  if(isset($_GET['edit']) AND $this->isPost())
                  {
                       $this->all= "";
                       foreach ( $_POST['t'] as $tid => $tnum )
                       {
                       $k = $tid." ".$tnum;
                       if ( $this->all != "" )
                       {
                       $this->all .= ",";
                       }
                       $this->all .= $k;
                       }
                       $edit = intval($_GET['edit']);
                       $get_edit = $q->provider->fetchRow("SELECT * FROM p_looting WHERE id='".$edit."' AND pid='".$this->player->playerId."'");
                       if ($get_edit != null) {
                       $q->provider->executeQuery2 ("UPDATE p_looting SET troops='".$this->all."' WHERE id='".$edit."';");
                       }
                       header ("Location: looting");
                       exit;
                  }
             if($this->selectedTabIndex == 6)
             {
if ($this->isPost()) { 
                  $this->get_looting = $q->provider->fetchResultSet("SELECT * FROM p_looting");
}
             }
             if($this->selectedTabIndex == 0)
             {
                  $this->get_looting = $q->provider->fetchResultSet("SELECT * FROM p_looting WHERE pid='".$this->player->playerId."'");

                  if(isset($_GET['del']))
                  {
                       $q->provider->executeQuery2 ("DELETE FROM p_looting WHERE pid='".$this->player->playerId."' AND id='".intval($_GET['del'])."';");
                       header ("Location: looting");
                       exit;
                  }
                  elseif(isset($_GET['delall']))
                  {
                       $q->provider->executeQuery2 ("DELETE FROM p_looting WHERE pid='".$this->player->playerId."';");
                       header ("Location: looting");
                       exit;
                  }
                  elseif(isset($_GET['dellall']))
                  {
$p_name = strip_tags(htmlspecialchars(trim($_POST['p_name'])));
$result = $q->provider->fetchResultSet ("SELECT * FROM p_villages WHERE player_name='".$p_name."' AND is_oasis = 0 ORDER BY village_name ASC");
while ($result->next ())
{
                       $q->provider->executeQuery2 ("DELETE FROM p_looting WHERE avid='".$result->row['id']."';");
}

                       header ("Location: looting");
                       exit;
                  }
             }
             elseif($this->selectedTabIndex == 1)
             {

                  $this->looting_send_every = $q->provider->fetchRow("SELECT looting_send_every FROM p_players WHERE id='".$this->player->playerId."'");
                  if($this->isPost())
                  {
                       $this->time = intval($_POST['time']);

                       if($this->time < 4 or $this->time > 120)
                       {
                            $this->error = 'يرجى اختيار التوقيت بشكل صحيح';
                       }
                       else
                       {
if($this->time <= 15) { 
$this->time = 15;
}
                            $q->provider->executeQuery2 ("UPDATE p_players SET looting_send_every='".$this->time."', looting_last_send='".time()."' WHERE id='".$this->player->playerId."';");
                            header ("Location: looting");
                            exit;
                       }
                  }
             }
             elseif($this->selectedTabIndex == 2)
             {
if (isset ($_GET['allv'])) {
$p_name = strip_tags(htmlspecialchars(trim($_POST['p_name'])));
$result = $q->provider->fetchResultSet ("SELECT * FROM p_villages WHERE player_name='".$p_name."' AND is_oasis = 0 ORDER BY village_name ASC");
while ($result->next ())
{
$this->playerVillagess [$result->row['id']] = array ( $result->row['rel_x'], $result->row['rel_y'], $result->row['village_name']);
}
        foreach ( $this->playerVillagess as $vid => $pvillage )
        {
        if ( $vid == $this->data['selected_village_id'] )
            {
       continue;            
            }
        $this->x    = intval($pvillage[0]);
        $this->y    = intval($pvillage[1]);
                       $this->all= "";
                       foreach ( $_POST['t'] as $tid => $tnum )
                       {
                       $k = $tid." ".$tnum;
                       if ( $this->all != "" )
                       {
                       $this->all .= ",";
                       }
                       $this->all .= $k;
                       }
                  $get_villages = $q->provider->fetchRow('SELECT * FROM p_villages WHERE rel_x="'.$this->x.'" AND rel_y="'.$this->y.'" AND ( player_id!=0 or is_oasis=1 )');
 $get_num_looting = $q->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE avid ="'.$get_villages['id'].'" AND pid="'.$this->player->playerId.'"');
 $get_num_loooting = $q->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE pid="'.$this->player->playerId.'" ');
if ($get_num_looting >= 1) {
                            $this->error .= 'هذه المزرعه تمت اضافتها سابقا '.$pvillage[2].'<br />';
       continue;            
}
else if ($get_num_loooting > $num_farm) {
                            $this->error .= 'لايمكنك اضافة مزارع اكثر .. نعتذر لكل لاعب '.$num_farm.' مزرعه فقط '.$pvillage[2].'<br />';
       continue;            
}else                             {

$q->provider->executeQuery2("INSERT INTO p_looting SET pid='%s', vid='%s', avid='%s', x='%s', y='%s', troops='%s'", array($this->player->playerId, $this->data['selected_village_id'], $vid, $this->x, $this->y, "$this->all"));
}
        }
      }else{ 
                       $this->x    = intval($_POST['x'].$_GET['x']);
                       $this->y    = intval($_POST['y'].$_GET['y']);
                  if($this->isPost())
                  {
                  if (isset ($_POST['tro'])) {
                  $test = $_POST['tro'];
                  $t2_arr = explode (',', $test);
                  foreach ($t2_arr as $t2_str)
                  {
                  $t = explode (' ', $t2_str);
                  $_POST['t'][$t[0]] = $t[1];
                  }
                  }
                       $this->all= "";
                       foreach ( $_POST['t'] as $tid => $tnum )
                       {
                       $k = $tid." ".$tnum;
                       if ( $this->all != "" )
                       {
                       $this->all .= ",";
                       }
                       $this->all .= $k;
                       }
                       if( abs($this->x) > (($GLOBALS['SetupMetadata']['map_size']-1)/2) )
                       {
                            $this->error = 'الاحداثيات غير صحيحة';
                       }
                       elseif( abs($this->y) > (($GLOBALS['SetupMetadata']['map_size']-1)/2) )
                       {
                            $this->error = 'الاحداثيات غير صحيحة';
                       }
                       elseif($this->x == 0 AND $this->y == 0)
                       {
                            $this->error = 'الاحداثيات غير صحيحة';
                       }
                       else
                       {
                            $get_villages = $q->provider->fetchRow('SELECT * FROM p_villages WHERE rel_x="'.$this->x.'" AND rel_y="'.$this->y.'" AND ( player_id!=0 or is_oasis=1 )');
 $get_num_looting = $q->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE avid ="'.$get_villages['id'].'" AND pid="'.$this->player->playerId.'"');
 $get_num_loooting = $q->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE pid="'.$this->player->playerId.'"');
                            if($get_villages == NULL)
                            {
                                 $this->error = 'الاحداثيات غير صحيحة';
                            }
else if ($get_num_looting >= 1) {
                            $this->error = 'هذه المزرعه تمت اضافتها سابقا';
}
else if ($get_num_loooting > $num_farm) {
                            $this->error = 'لايمكنك اضافة مزارع اكثر .. نعتذر لكل لاعب '.$num_farm.' مزرعه فقط';
}else                             {
                                 $q->provider->executeQuery2("INSERT INTO p_looting SET pid='%s', vid='%s', avid='%s', x='%s', y='%s', troops='%s'", array($this->player->playerId, $this->data['selected_village_id'], $get_villages['id'], $this->x, $this->y, "$this->all"));
                                 header ("Location: looting");
                                 exit;
                            }
                       }
                  }
                 }
             }


        }

}
$p = new GPage();
$p->run();
?>