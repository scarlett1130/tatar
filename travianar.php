<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");


require_once MODEL_PATH . 'v2v.php';



class GPage extends ProcessVillagePage


{





        public function GPage()


        {


                parent::processvillagepage( );


                $this->viewFile = "travianar.phtml";


                $this->contentCssClass = "a2b";


        }


public function find1( )


{


    return $this->queueModel->provider->fetchResultSet("SELECT rel_x,id,rel_y,image_num,player_name FROM p_villages where is_oasis=1");


}


public function find2( )


{


    return $this->queueModel->provider->fetchResultSet ("SELECT * FROM p_looting WHERE pid='".$this->player->playerId."' AND vid='".$this->data['selected_village_id']."' AND avid=0");


}
public function find3( )


{


    $result = $this->queueModel->provider->fetchResultSet("SELECT avid FROM p_looting WHERE pid='".$this->player->playerId."'");
    $r = array();
    
    while($result->next()){
        $r[] = $result->row['avid'];
    }
    return $r;
}

public function moreoasis( )


{


    if($this->data['gold_num'] >= 500)


    {


    $this->queueModel->provider->executeQuery( "UPDATE p_players p SET p.gold_num=(p.gold_num-500) WHERE p.id=%s", array(


    $this->player->playerId


    ) );


    $this->queueModel->provider->executeQuery ("INSERT INTO p_looting (id, pid, vid, avid, x, y, troops) VALUES (NULL, '".$this->player->playerId."', '".$this->data['selected_village_id']."', '0', '0', '0', '');");


    }


    header ("Location: trevianos?t=3");


    exit;


}


        public function load()


        {


             parent::load( );


if (!$this->data['active_plus_account']){ 


exit;


}


             $this->selectedTabIndex = ((((isset($_GET['t']) && is_numeric($_GET['t'])) && 0 <= intval($_GET['t'])) && intval($_GET['t']) <= 4) ? intval($_GET['t']) : 0);


             if($this->selectedTabIndex == 3)


             {


             $q                      = new QueueModel();





if (isset($_POST['list']))


{


foreach ($_POST['list'] as $list)


{


list ($this->x , $this->y ,$vid) = explode ("|" , $list);
$q                      = new QueueModel();

 $get_num_looting = $q->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE avid ="'.$vid.'" AND pid="'.$this->player->playerId.'"');
 if($get_num_looting > 0){
     WebHelper::redirect('trevianos');
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


                            continue;


                       }


                       elseif( abs($this->y) > (($GLOBALS['SetupMetadata']['map_size']-1)/2) )


                       {


                            continue;


                       }


                       elseif($this->x == 0 AND $this->y == 0)


                       {


                            continue;


                       }


                       else


                       {


                            $get_villages = $q->provider->fetchRow('SELECT * FROM p_villages WHERE rel_x="'.$this->x.'" AND rel_y="'.$this->y.'" AND ( player_id!=0 or is_oasis=1 )');


 $get_num_looting = $q->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE avid ="'.$get_villages['id'].'" AND pid="'.$this->player->playerId.'"');


 $get_num_loooting = $q->provider->fetchScalar('SELECT COUNT(*) FROM p_looting WHERE pid="'.$this->player->playerId.'"');
$num_farm = $q->provider->fetchScalar('SELECT num_farm FROM p_players WHERE id="'.$this->player->playerId.'" ');


                            if($get_villages == NULL)


                            {


                            continue;


                            }


else if ($get_num_looting >= ($num_farm+50)) {


                            continue;


}


else if ($get_num_loooting > ($num_farm+50)) {


                            continue;


}else                             {


                                 $q->provider->executeQuery2("INSERT INTO p_looting SET pid='%s', vid='%s', avid='%s', x='%s', y='%s', troops='%s'", array($this->player->playerId, $this->data['selected_village_id'], $get_villages['id'], $this->x, $this->y, "$this->all"));


                            }


                       }








}


}


if(isset($_GET['moreoasis']))


{


$this->moreoasis();


}


}








             $q                      = new QueueModel();





             $plus_true    = 1;//$q->provider->fetchScalar('SELECT COUNT(*) FROM p_queue p WHERE p.player_id=%s AND p.proc_type=56', array ($this->player->playerId));


             if($plus_true == 0)


             {


                  $this->selectedTabIndex = 3;


                  $q->provider->executeQuery2 ("DELETE FROM p_looting WHERE pid='".$this->player->playerId."';");


             }





             if($this->selectedTabIndex == 0)


             {


                  $this->get_looting = $q->provider->fetchResultSet("SELECT * FROM p_looting WHERE vid='".$this->data['selected_village_id']."'");





                  if(isset($_GET['del']))


                  {


                       $q->provider->executeQuery2 ("DELETE FROM p_looting WHERE pid='".$this->player->playerId."' AND vid='".$this->data['selected_village_id']."' AND id='".intval($_GET['del'])."';");


                       header ("Location: trevianos");


                       exit;


                  }


                  elseif(isset($_GET['delall']))


                  {


                       $q->provider->executeQuery2 ("DELETE FROM p_looting WHERE pid='".$this->player->playerId."' AND vid='".$this->data['selected_village_id']."';");


                       header ("Location: trevianos");


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


                            $q->provider->executeQuery2 ("UPDATE p_players SET looting_send_every='".$this->time."', looting_last_send='".time()."' WHERE id='".$this->player->playerId."';");


                            header ("Location: trevianos");


                            exit;


                       }


                  }


             }


             elseif($this->selectedTabIndex == 2)


             {


if (isset ($_GET['allv'])) {


                // fill the player villages array


                $v_arr = explode ("\n", $this->data['villages_data']);


                foreach ($v_arr as $v_str)


                {


                        list ($vid, $x, $y, $vname) = explode (' ', $v_str, 4);


                        $this->playerVillages [$vid] = array ($x, $y, $vname);


                }


        foreach ( $this->playerVillages as $vid => $pvillage )


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


$q->provider->executeQuery2("INSERT INTO p_looting SET pid='%s', vid='%s', avid='%s', x='%s', y='%s', troops='%s'", array($this->player->playerId, $this->data['selected_village_id'], $vid, $this->x, $this->y, "$this->all"));





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


else if ($get_num_loooting > 30) {


                            $this->error = 'لايمكنك اضافة مزارع اكثر .. نعتذر لكل لاعب 30 مزرعه فقط';


}else                             {


                                 $q->provider->executeQuery2("INSERT INTO p_looting SET pid='%s', vid='%s', avid='%s', x='%s', y='%s', troops='%s'", array($this->player->playerId, $this->data['selected_village_id'], $get_villages['id'], $this->x, $this->y, "$this->all"));


                                 header ("Location: trevianos");


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


