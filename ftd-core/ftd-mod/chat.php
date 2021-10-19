<?php 
/**
* @   PROJECT WAS MADE FOR SMART SERVS
* @   WHATS APP : 00966501494220 
* @   VISIT : WWW.REDSEA-H.COM
* @   ALL COPY RIGHTS RESERVED PROGRAMMED BY RED SEA HOST 
* @   THIS PROJECT WAS MADE BY THE REGISTERED RED SEA HOST UNDER THE NAME OF WWW.REDSEA-H.COM
**/
class ChatModel extends ModelBase{

        public function SendToChat($username, $id, $redseahost){
                $this->provider->executeQuery( "INSERT INTO `g_chat` SET `username` = '%s', `userid` = '%s', `date` = '%s', `text` = '%s';", array($username,$id,time(),$redseahost));
        }

        public function GetFromChat(){
                return $this->provider->fetchResultSet( "SELECT * FROM `g_chat` ORDER BY `ID` DESC LIMIT 50;" );
        }

        public function DeleteOldChat(){
                $count = $this->provider->fetchScalar( "SELECT COUNT(*) FROM `g_chat` ;" );
                if(50 < $count){
                        $limit = $count - 50;
                        $this->provider->executeQuery( "DELETE FROM `g_chat` ORDER BY `ID` ASC LIMIT %s ;", array( $limit ) );
                }
        }
}
?>