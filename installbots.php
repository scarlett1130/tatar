<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");

class GPage extends SecureGamePage

{



        function GPage(){

                parent::securegamepage();

                $this->viewFile = "InfiniteAccounts.phtml";

                $this->contentCssClass = "forum";

        }

        function load()

                {

           parent::load();



                }

}

$p = new GPage();

$p->run();

?>