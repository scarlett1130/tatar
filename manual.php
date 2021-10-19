<?php
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
require_once(MODEL_PATH."index.php");
class GPage extends DefaultPage{

        public $data = NULL;
        public $error = NULL;
        public $errorState = -1;
        public $name = NULL;
        public $password = NULL;

        public function GPage(){
                parent::defaultpage();
                $this->viewFile = "manual.phtml";
                $this->layoutViewFile = "layout".DIRECTORY_SEPARATOR."form.phtml";
                $this->contentCssClass = "login";
                }

}
$p = new GPage();
$p->run();
?>
