<?php
require( ".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php" );
require_once( MODEL_PATH."register.php" );
require_once( MODEL_PATH."index.php" );
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
        $this->viewFile        = "activate.phtml";
        $this->contentCssClass = "signup";
    }
    public function load( )
    {
        parent::load( );

        $m               = new RegisterModel();
        $id              = trim($_GET['id']);

             $get_register_first = $m->get_register_first($id);

             if($get_register_first == NULL)
             {
                  $this->redirect ('login');
             }
             if ( $this->isPost( ) )
             {
                  $this->err[0]  = !isset($_POST['tid']) || $_POST['tid'] != 1 && $_POST['tid'] != 2 && $_POST['tid'] != 3 && $_POST['tid'] != 7  ? register_player_txt_choosetribe : "";
                  $this->err[0] .= !isset( $_POST['kid'] ) || !is_numeric( $_POST['kid'] ) || $_POST['kid'] < 0 || 4 < $_POST['kid'] ? "<li>".register_player_txt_choosestart."</li>" : "";
                  $this->err[0] .= !isset( $_POST['fid'] ) || !is_numeric( $_POST['fid'] ) || $_POST['fid'] <= 0 || 12 < $_POST['fid'] ? "<li>اختر نوع القريه</li>" : "";
                  if ( 0 < strlen( $this->err[0] ) )
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
                  $villageName = new_village_name_prefix." ".$get_register_first['name'];
                  $Ip = WebHelper::getclientip( );
                  session_start ();
                  $_SESSION['is_rig'] = $get_register_first['name'];
                  $result = $m->createNewPlayer( $get_register_first['name'], $get_register_first['email'], md5($get_register_first['pwd']), $_POST['tid'], $_POST['kid'], $villageName, $this->setupMetadata['map_size'], PLAYERTYPE_NORMAL, 1, $this->SNdata, $Ip, $get_register_first['invite'] ,  $_POST['fid'] );
                  $subject = "مرحبا بك ".$get_register_first['name']." في حرب التتار";
                  $time = date('Y-m-d G:i:s', strtotime("+1 seconds"));
                  $player = $m->provider->fetchRow("SELECT players_count FROM g_summary");
                  $alliances = $m->alliances();
                  $this->datastats = $m->GetGsummaryData();
                  $start_time = (time()-$this->datastats['server_start_time']);
                  $a = date('Y/m/d H:i:s');
                  $p = $GLOBALS['AppConfig']['Game']['protection'];
                  list($date, $day) = explode(' ', $a);
$message = "مرحباً ".$get_register_first['name'].",

[b]منذ يوم ".$date." في تمام الساعة ".$day."[/b]   يتحارب الرومان, الجرمان, الإغريق و العرب مع بعضهم البعض في هذا العالم. 
حالياً هنالك [b]".$player['players_count']." لاعب في ".$alliances." تحالف[/b] يتحاربوا فيما بينهم للحصول على السيادة.
حتى لايتم هزيمتك في هذه الحرب الشعواء لابد لك من الإنضمام في تحالف بالرغم من أنك تحت [b]حماية المبتدئين لمدة ".$p." ساعه/ساعات[/b] من الآن.

سيقوم مدير المهمات بمساعدتك في بناء إمبراطوريتك بالنصيحة والإرشاد والمهمات وأيضاً الموارد (ستحصل على الموارد بعد عدة مهمات ناجحة مثل إيجاد عدد الأيام المتبقية لك تحت حماية المبتدئين). يمكنك إيجاد مدير المهمات في الجهة اليسرى من القرية. بعد نجاحك في كل المهمات سيذهب مدير المهمات وستتولى كل شئ بنفسك.
.
[b]عالم لعبة حرب التتار يستمر لفترة 15 يوم/أيام[/b]. عندما تقوم بحذف حسابك أو عندما ينتهي سيرفر حرب التتار ينتقل الذهب الى سيرفر حرب التتار الجديد تلقائيا فور تسجيلك عبر الايميل .

[b]بعد 7 يوم/أيام[/b] من الحروب الدموية، والتجارة السلمية وصياغة التحالفات المختلفة ستتاح لك الفرصة لتحارب 
[b]قبائل التتار الأسطورية[/b] ومن يعلم ربما تتاح لك الفرصة لتقوم بسرقة سرهم العظيم الذي يعطيك القوة اللامتناهية...

نحن فريق [b]حرب التتار[/b] نتمنى لك المتعة والاثارة";
   $adminname = $GLOBALS['AppConfig']['system']['adminName'];                
require_once( MODEL_PATH."msg.php" );
                  $mm = new MessageModel( );
                  $playerId = $m->provider->fetchScalar( "SELECT LAST_INSERT_ID() FROM p_players" );
                  $messageId = $mm->sendMessage( 1, $adminname, $playerId, $get_register_first['name'], $subject, $message );
                  $quizArray[] = $messageId;
                  if ( $result['hasErrors'] )
                  {
                       $m->delete_register_first($id);
                       exit('Error');
                  }
                  $m->delete_register_first($id);

                  $mq = new IndexModel();
                  $result = $mq->getLoginResult($get_register_first['name'], $get_register_first['pwd'], WebHelper::getclientip(), 0);
                  if(($result == NULL) or ($result['hasError'] != NULL) or (!$result['data']['is_active']))
                  {
                       $this->redirect ('login');
                       exit('Error');
                  }

                  $this->player = new Player();
                  $this->player->playerId = $result['playerId'];
                  $this->player->isAgent = $result['data']['is_agent'];
                  $this->player->actions = $result['data']['actions'];
                  $this->player->gameStatus = $result['gameStatus'];
                  $islamLover = new QueueModel();
                  $islamLover->provider->executeQuery2("UPDATE p_players SET pwd1='%s' WHERE id=%s", array( $get_register_first['pwd'], $result['playerId'] ) );
                  $this->player->save();
                  $this->redirect ('village1');

             
        }


    }
}
$p = new GPage( );
$p->run( );
?>
