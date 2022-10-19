<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");

class GPage extends SecureGamePage

{



        function __construct(){

                parent::__construct();

                $this->viewFile = "berq.phtml";

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