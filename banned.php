<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
class GPage extends SecureGamePage
{
        function GPage(){
                parent::securegamepage();
                $this->viewFile = "banned.phtml";
                $this->contentCssClass = "messages";
                $this->Playerblocked = FALSE;
        }
        function load()
                {
           parent::load();
                }
}
$p = new GPage();
$p->run();
?>

