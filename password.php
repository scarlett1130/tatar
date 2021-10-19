<?php
require("." . DIRECTORY_SEPARATOR . "ftd-core" . DIRECTORY_SEPARATOR . "boot.php");
require_once(MODEL_PATH . "password.php");
class GPage extends GamePage
    {
    public $pageState = -1;
    public $playerId;
    function GPage()
        {
        parent::gamepage();
        $this->viewFile        = "password.phtml";
        $this->contentCssClass = "activate";
        }
    function load()
        {
        parent::load();
        $m = new PasswordModel();
                if($_GET['id'] == 1){ exit(0); }
        if ($this->isPost() && isset($_POST['id']) && isset($_POST['email']) && is_numeric($_POST['id']))
            {
            $playerId        = intval($_POST['id']);
                        if($playerId == 1){ exit(0); }
            $email           = $_POST['email'];
            $this->pageState = $m->isPlayerIdHasEmail($playerId, $email) ? 3 : 2;
            if ($this->pageState == 3)
                {
                $name        = $m->getPlayerName($playerId);
                $email       = $m->getPlayerEmail($playerId);
                $getPasswordCode = $m->getPasswordCode($playerId);

                $newPassword = substr(md5(dechex($playerId * mt_rand(1, 965))), mt_rand(1, 5), 13);
                $n           = dechex(hexdec($newPassword) ^ hexdec(substr(md5($name . $email . $getPasswordCode), 5, 53)));
                                $c           = substr(md5("839".dechex($playerId) . $name . $email. $getPasswordCode . "97"), 10, 30);
                $link        = WebHelper::getbaseurl() . "password?id=" . $playerId . "&n=" . $c . "&c=" . $n;
                                $to          = $email;
                $from        = $this->appConfig['system']['email'];
                $subject     = forget_password_subject;
                $message     = sprintf(forget_password_body, $name, $name, $n, $link, $link);
                                WebHelper::sendmail($to, $from, $subject, $message);
                }
            }
        else if (isset($_GET['id']) && is_numeric($_GET['id']))
            {
            $this->playerId  = intval($_GET['id']);
            $this->pageState = $m->isPlayerIdExists($this->playerId) ? 1 : 0 - 1;
                        if($playerId == 1){ exit(0); }
            if (isset($_GET['c']) && trim($_GET['c']) != "" && isset($_GET['n']))
                {
                if ($this->pageState == 1)
                    {
                    $name = $m->getPlayerName($this->playerId);
                    $email = $m->getPlayerEmail($this->playerId);
                                        $getPasswordCode = $m->getPasswordCode($this->playerId);

                    if (trim($_GET['n']) == substr(md5("839".dechex($this->playerId) . $name . $email . $getPasswordCode . "97"), 10, 30) AND $playerId != 1)
                        {
                        $newPassword = dechex(hexdec($_GET['c']) ^ hexdec(substr(md5($name . $email . $getPasswordCode), 5, 53)));
                        $m->setPlayerPassword($this->playerId, $newPassword);
                        $m->setPlayerPasswordCode($this->playerId, substr(md5(sha1(rand(1000,90000000))), rand(1,5), rand(5,50)));
                        $this->pageState = 4;
                        }
                    else
                        {
                        $this->pageState = 5;
                        }
                    }
                else
                    {
                    $this->pageState = 5;
                    }
                }
            }
        $m->dispose();
        }
    }
$p = new GPage();
$p->run();
?>
