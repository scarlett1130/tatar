<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
require_once( LIB_PATH."bot.php" );
class GPage extends DefaultPage
{
        public function GPage()
        {
                parent::defaultpage();
        }
        public function load()
        {
// Kill underfed units every click if crop is less than 0 ...
$qj = new QueueJobModel ();
$koko = $qj->provider->fetchResultSet( "SELECT player_id,parent_id,id,is_oasis FROM p_villages WHERE NOT ISNULL(player_id)");
while($koko->next ()) {
if ($koko->row['is_oasis'] == 1) {
$iso = 1;
$vis = $koko->row['parent_id'];
} else {
$iso = 0;
$vis = 0;
}
$qj->cropBalance ($koko->row['player_id'], $koko->row['id'], $iso , $vis);
}
// end Kill

                  $domain = WebHelper::getdomain();
                  $link = "http://".$domain;
                  $q          = new QueueModel();
                  $b          = new bot($link);
                  $get_p_players     = $q->provider->fetchResultSet("SELECT * FROM p_players WHERE looting_send_every>='15' ORDER BY looting_last_send ASC");
                  while ($get_p_players->next ())
                  {     
                        $get_num_looting = $q->provider->fetchScalar("SELECT COUNT(*) FROM p_looting WHERE pid='%s'", array($get_p_players->row['id']));
                        $timestamp = $get_p_players->row['looting_last_send'] + 60 * $get_p_players->row['looting_send_every'];
                        $timestamp =  date("Y/m/d H:i:s", $timestamp);

                        if($timestamp >= date("Y/m/d H:i:s"))
                        {
                            continue;
                        }
                        if($get_p_players->row['looting_send_every'] == 4)
                        {
                            continue;
                        }
                       if(empty($get_p_players->row['pwd1']))
                       {
                            continue;
                       }
                       if ($get_num_looting <= 0) 
                       {
                            continue;
                       }
                       $get_p_looting = $q->provider->fetchResultSet("SELECT * FROM p_looting WHERE pid='%s'", array($get_p_players->row['id']));
                       while ($get_p_looting->next ())
                       {
                            $u_true = true;
                            $b->login_to_server('login?boot', array('name' => $get_p_players->row['name'], 'password' => $get_p_players->row['pwd1']));
                            $b->open_page('village1?ok');
                            $b->post_page('v2v', array('id' => $get_p_looting->row['avid'], 'c' => '4', 'tro' => $get_p_looting->row['troops']));
                       }
                        if($u_true)
                        {
                             $q->provider->executeQuery2 ("UPDATE p_players SET looting_last_send='".time()."' WHERE id='".$get_p_players->row['id']."';");                            
                        }

                  }
        }
}
$p = new GPage();
$p->run();
?>
