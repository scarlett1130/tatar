<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
class GPage extends SecureGamePage
{
        function GPage(){
                parent::securegamepage();
                $this->viewFile = "admin.phtml";
                $this->contentCssClass = "cropfinder";
        }
        function load()
                {
           parent::load();
                }
}
$p = new GPage();
$p->run();
?>
