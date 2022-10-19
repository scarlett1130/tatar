<?php
/**
* @   PROJECT WAS MADE FOR SMART SERVS
* @   WHATS APP : 00966501494220 
* @   VISIT : WWW.REDSEA-H.COM
* @   ALL COPY RIGHTS RESERVED PROGRAMMED BY RED SEA HOST 
* @   THIS PROJECT WAS MADE BY THE REGISTERED RED SEA HOST UNDER THE NAME OF WWW.REDSEA-H.COM
**/
require(".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."boot.php");
require_once(MODEL_PATH."chat.php");
require_once(MODEL_PATH."wordsfilter.php");
class GPage extends securegamepage{

        public $chats = NULL;
        public $Filter = NULL;

        public function GPage(){
                parent::securegamepage();
                $this->viewFile = "chat.phtml";
                $this->contentCssClass = "player";
        }

        public function load(){
                parent::load();
                $this->Filter = new FilterWordsModel();
                $m = new ChatModel();
                if($this->isPost() && isset($_POST['text'])){
                $redseahost = stripslashes(htmlspecialchars(trim($_POST['text'])));
                $m->SendToChat( $this->data['name'], $this->player->playerId, $redseahost );
                }
                $m->DeleteOldChat();
                $this->chats = $m->GetFromChat();
                $m->dispose();
        }
}
$p = new GPage();
$p->run();
?>