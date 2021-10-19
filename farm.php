<?php

require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
require_once MODEL_PATH . 'v2v.php';
class GPage extends ProcessVillagePage
{
        public function GPage()
        {
                parent::processvillagepage( );
                $this->viewFile = "farm.phtml";
                $this->contentCssClass = "a2b";
        }
        public function load()
        {
             parent::load( );

             if (!$this->data['active_plus_account']){ 
$this->redirect ('plus.php?t=2');
exit;
}
$q                      = new QueueModel();
$newcode = md5(md5(md5(time()."tatarwar.co")));
$q->provider->executeQuery2 ("UPDATE `p_players` SET  `farming` =  '".$newcode."' WHERE id='".$this->player->playerId."';");
$this->data['farming'] = $newcode;
if (isset ($_GET['addfarm'])) {
$num_farming = 50+$this->data['num_farm'];
$num_farming = (($num_farming/50)*250);
if ($this->data['gold_num'] >= $num_farming AND ($this->data['num_farm']+50) < 750) {
$q->provider->executeQuery2 ("UPDATE `p_players` SET  `num_farm` =  num_farm+50,gold_num=gold_num-".$num_farming." WHERE id='".$this->player->playerId."';");
}
                       header ("Location: farm.php?more");
                       exit;
}
			  $num_farm = 50+$this->data['num_farm'];
              $this->num_farm = $num_farm;
              $this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 0 <= addslashes($_GET['t'])) && addslashes($_GET['t']) <= 7) ? addslashes($_GET['t']) : 0);
              $this->num_loooting     = $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE pid="'.$this->player->playerId.'"');

             if($this->selectedTabIndex == 5)
             {
                if ($this->isPost() && $_POST['p_name'] != '')
                {
                    $result = $this->queueModel->provider->fetchResultSet ("SELECT id FROM p_villages WHERE player_name='". $_POST['p_name'] ."' AND is_oasis = 0 ORDER BY village_name ASC");
                    while ($result->next ())
                    {
                        $this->queueModel->provider->executeQuery2 ("DELETE FROM p_looting WHERE pid='".$this->player->playerId."' AND avid='". $result->row['id'] ."';");
                    }

                    header ("Location: farm.php");
                    exit;
                }
             }
             /*
             else if($this->selectedTabIndex == 4)
             {
                if(isset($_GET['activation']))
                {
                    $this->queueModel->provider->executeQuery2 ("UPDATE p_players SET activation_automatic_looting='".$this->data['selected_village_id']."' WHERE id='". $this->player->playerId ."';");
                    header ("Location: farm.php?t=4");
                    exit;
                }
                else if(isset($_GET['stop']))
                {
                    $this->queueModel->provider->executeQuery2 ("UPDATE p_players SET activation_automatic_looting='0' WHERE id='". $this->player->playerId ."';");
                    header ("Location: farm.php?t=4");
                    exit;
                }
             }
             */
             else if($this->selectedTabIndex == 0)
             {

                  $this->get_looting = $this->queueModel->provider->fetchResultSet("SELECT * FROM p_looting WHERE pid='". $this->player->playerId ."'");
                  

                  /*
                  $this->activation_automatic_looting = false;
                  if(isset($_GET['automatic']) && $get_num_loooting != 0 && $this->data['activation_automatic_looting'] != 0 && $this->data['activation_automatic_looting'] == $this->data['selected_village_id'] && $this->data['gold_num'] >= 50)
                  {
                      $this->activation_automatic_looting = true;
                      $this->queueModel->provider->executeQuery2 ("UPDATE p_players SET gold_num=gold_num-50 WHERE id='". $this->player->playerId ."';");
                  }
                  */

                  if(isset($_GET['del']))
                  {
                       $this->queueModel->provider->executeQuery2 ("DELETE FROM p_looting WHERE pid='".$this->player->playerId."' AND id='".addslashes($_GET['del'])."';");
                       header ("Location: farm.php");
                       exit;
                  }
                  elseif(isset($_GET['delall']))
                  {
                       $this->queueModel->provider->executeQuery2 ("DELETE FROM p_looting WHERE pid='".$this->player->playerId."';");
                       header ("Location: farm.php");
                       exit;
                  }
             }
             elseif($this->selectedTabIndex == 1)
             {
				

                if (isset ($_GET['allservv']) && $this->isPost())
                {

                    #$result = $this->queueModel->provider->fetchResultSet ("SELECT * FROM p_villages WHERE serv_id='1' and serv_player_id='". $this->player->playerId ."' AND is_oasis = 0 ORDER BY village_name ASC");
                    $result = $this->queueModel->provider->fetchResultSet ("SELECT * FROM p_villages WHERE serv_id='1' and serv_player_id='". $this->player->playerId ."' ORDER BY village_name ASC");
                    while ($result->next ())
                    {
                        $this->playerVillagesx[$result->row['id']] = array ( $result->row['rel_x'], $result->row['rel_y'], $result->row['village_name']);
                    }

                    foreach ( $this->playerVillagesx as $vid => $pvillage )
                    {
                        if ( $vid == $this->data['selected_village_id'] )
                        {
                            continue;
                        }

                        $this->x = addslashes($pvillage[0]);
                        $this->y = addslashes($pvillage[1]);

                        $this->all= "";
                        foreach ( $_POST['t'] as $tid => $tnum )
                       {if ( $tnum > 9999999999999999 )
						{		   
							   $this->error = "يجب أن لا تتجاوز 13 خانة في رقم القوات.";   
						}
						   else
                        {
                            $k = $tid." ".$tnum;
                            if ( $this->all != "" )
                            {
                                $this->all .= ",";
                            }
                            $this->all .= $k;
                        }
                        }

                        $get_villages     = $this->queueModel->provider->fetchRow('SELECT * FROM p_villages WHERE rel_x="'.$this->x.'" AND rel_y="'.$this->y.'" AND ( player_id!=0 or is_oasis=1 )');
                        $get_num_looting  = $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE avid ="'.$get_villages['id'].'" AND pid="'.$this->player->playerId.'"');
                        $get_num_loooting = $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE pid="'.$this->player->playerId.'"');
                        if ($get_num_looting >= 1)
                        {
                            $this->error .= 'هذه المزرعه تمت اضافتها سابقا '.$pvillage[2].'<br />';
                            continue;            
                        }
                        else if ($get_num_loooting > $num_farm)
                        {
                            $this->error .= 'لايمكنك اضافة مزارع اكثر .. نعتذر لكل لاعب '.$num_farm.' مزرعه فقط '.$pvillage[2].'<br />';
                           continue;            
                        }
                        else
                        {
                            $this->queueModel->provider->executeQuery2("INSERT INTO p_looting SET pid='%s', vid='%s', avid='%s', x='%s', y='%s', troops='%s'", array($this->player->playerId, 0, $vid, $this->x, $this->y, "$this->all"));
                        }
                    }

                    header ("Location: farm.php");
                    exit;
                    
                }
				else if (isset ($_GET['allv']) && $this->isPost() && $_POST['p_name'] != '')
                {

                    $result = $this->queueModel->provider->fetchResultSet ("SELECT * FROM p_villages WHERE player_name='". $_POST['p_name'] ."' AND is_oasis = 0 ORDER BY village_name ASC");
                    while ($result->next ())
                    {
                        $this->playerVillagesx[$result->row['id']] = array ( $result->row['rel_x'], $result->row['rel_y'], $result->row['village_name']);
                    }

                    foreach ( $this->playerVillagesx as $vid => $pvillage )
                    {
                        if ( $vid == $this->data['selected_village_id'] )
                        {
                            continue;
                        }

                        $this->x = addslashes($pvillage[0]);
                        $this->y = addslashes($pvillage[1]);

                        $this->all= "";
                        foreach ( $_POST['t'] as $tid => $tnum )
                       {if ( $tnum > 9999999999999999 )
						{		   
							   $this->error = "يجب أن لا تتجاوز 13 خانة في رقم القوات.";   
						}
						   else
                        {
                            $k = $tid." ".$tnum;
                            if ( $this->all != "" )
                            {
                                $this->all .= ",";
                            }
                            $this->all .= $k;
                        }
                        }

                        $get_villages     = $this->queueModel->provider->fetchRow('SELECT * FROM p_villages WHERE rel_x="'.$this->x.'" AND rel_y="'.$this->y.'" AND ( player_id!=0 or is_oasis=1 )');
                        $get_num_looting  = $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE avid ="'.$get_villages['id'].'" AND pid="'.$this->player->playerId.'"');
                        $get_num_loooting = $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE pid="'.$this->player->playerId.'"');
                        if ($get_num_looting >= 1)
                        {
                            $this->error .= 'هذه المزرعه تمت اضافتها سابقا '.$pvillage[2].'<br />';
                            continue;            
                        }
                        else if ($get_num_loooting > $num_farm)
                        {
                            $this->error .= 'لايمكنك اضافة مزارع اكثر .. نعتذر لكل لاعب '.$num_farm.' مزرعه فقط '.$pvillage[2].'<br />';
                           continue;            
                        }
                        else
                        {
                            $this->queueModel->provider->executeQuery2("INSERT INTO p_looting SET pid='%s', vid='%s', avid='%s', x='%s', y='%s', troops='%s'", array($this->player->playerId, 0, $vid, $this->x, $this->y, "$this->all"));
                        }
                    }

                    header ("Location: farm.php");
                    exit;
                    
                }
                else
                {
                    $this->x    = addslashes($_POST['x'].$_GET['x']);
                    $this->y    = addslashes($_POST['y'].$_GET['y']);

                    if($this->isPost())
                    {

                        if (isset ($_POST['tro']))
                        {
                            $test   = $_POST['tro'];
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
						   if ( $tnum > 9999999999999999 )
						   {
							   
							   $this->error = "يجب أن لا تتجاوز 13 خانة في رقم القوات.";
							   
						   }
						   else
						   {
                           $k = $tid." ".$tnum;
                           if ( $this->all != "" )
                           {
                              $this->all .= ",";
                           }
                           $this->all .= $k;
                         }
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
                              $get_villages = $this->queueModel->provider->fetchRow('SELECT * FROM p_villages WHERE rel_x="'.$this->x.'" AND rel_y="'.$this->y.'" AND ( player_id!=0 or is_oasis=1 )');
                              $get_num_looting = $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE avid ="'.$get_villages['id'].'" AND pid="'.$this->player->playerId.'"');
                              $get_num_loooting = $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE pid="'.$this->player->playerId.'"');
                              if($get_villages == NULL)
                              {
                                   $this->error = 'الاحداثيات غير صحيحة';
                              }
                              else if ($get_num_looting >= 1)
                              {
                                  $this->error = 'هذه المزرعه تمت اضافتها سابقا';
                              }
                              else if ($get_num_loooting > $num_farm)
                              {
                                  $this->error = 'لايمكنك اضافة مزارع اكثر .. نعتذر لكل لاعب '.$num_farm.' مزرعه فقط';
                              }
                              else
                              {
                                  $this->queueModel->provider->executeQuery2("INSERT INTO p_looting SET pid='%s', vid='%s', avid='%s', x='%s', y='%s', troops='%s'", array($this->player->playerId, 0, $get_villages['id'], $this->x, $this->y, "$this->all"));
                                  header ("Location: farm.php");
                                  exit;
                              }
                         }
                    }
                 }
             }
             else if($this->selectedTabIndex == 3)
             {
                  $edit              = isset($_GET['edit']) ? addslashes($_GET['edit']) : 0;
                  $this->get_looting = $this->queueModel->provider->fetchRow("SELECT * FROM p_looting WHERE pid='".$this->player->playerId."' && id='". $edit ."'");
                  if($this->get_looting == null)
                  {
                      header ("Location: farm.php");
                      exit;
                  }

                  $this->x = $this->get_looting['x'];
                  $this->y = $this->get_looting['y'];
                  $this->troops = array();
                  $troops = $this->get_looting['troops'];
                  $t2_arr = explode (',', $troops);
                  foreach ($t2_arr as $t2_str)
                  {
                      $t = explode (' ', $t2_str);
                      $this->troops[$t[0]] = $t[1];
                  }


                
                  if($this->isPost())
                  {
                      $this->x    = addslashes($_POST['x'].$_GET['x']);
                      $this->y    = addslashes($_POST['y'].$_GET['y']);

                      if (isset ($_POST['tro']))
                      {
                          $test   = $_POST['tro'];
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
						   if ( $tnum > 9999999999999999 )
						   {
							   
							   $this->error = "يجب أن لا تتجاوز 13 خانة في رقم القوات.";
							   
						   }
						   else
						   {
                       $k = $tid." ".$tnum;
                       if ( $this->all != "" )
                       {
                       $this->all .= ",";
                       }
                       $this->all .= $k;
                       }
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
                            $get_villages     = $this->queueModel->provider->fetchRow('SELECT * FROM p_villages WHERE rel_x="'.$this->x.'" AND rel_y="'.$this->y.'" AND ( player_id!=0 or is_oasis=1 )');
                            $get_num_looting  = $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE avid ="'.$get_villages['id'].'" AND pid="'.$this->player->playerId.'" AND id!="'. $edit .'"');
                            $get_num_loooting = $this->queueModel->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE pid="'.$this->player->playerId.'"');
                            if($get_villages == NULL)
                            {
                                 $this->error = 'الاحداثيات غير صحيحة';
                            }
                            else if ($get_num_looting >= 1)
                            {
                                $this->error = 'هذه المزرعه تمت اضافتها سابقا';
                            }
                            else if ($get_num_loooting > $num_farm)
                            {
                                $this->error = 'لايمكنك اضافة مزارع اكثر .. نعتذر لكل لاعب '.$num_farm.' مزرعه فقط';
                            }
                            else
                            {
                                 $this->queueModel->provider->executeQuery("UPDATE p_looting SET pid='%s', vid='%s', avid='%s', x='%s', y='%s', troops='%s' WHERE id='%s'", array($this->player->playerId, 0, $get_villages['id'], $this->x, $this->y, "$this->all", $edit));
                                 header ("Location: farm.php");
                                 exit;
                            }
                       }
                  }
                 
             }
        }

}
$p = new GPage();
$p->run();
?>