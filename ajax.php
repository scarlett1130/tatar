<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
class GPage extends DefaultPage
{
        public function GPage()
        {
                parent::defaultpage();
        }
        public function load()
        {
			//				   url: 'ajax.hp?f=vp&id='+id+'&state='+state.type,
if (isset ($_GET['f'])) {
$id = $_GET['id'];
$state = $_GET['state'];	
setcookie( "id_".$id, $state, time() + 60*60*24 );
	
}
        }
}
$p = new GPage();
$p->run();
?>
