<?php
ini_set('session.save_path',".".DIRECTORY_SEPARATOR."ftd-core".DIRECTORY_SEPARATOR."sessions");
session_start();  
$ttt = rand(111,999);
header("Content-Type: image/png");
$im = @imagecreate(35, 20)
    or die("Cannot Initialize new GD image stream");
$background_color = imagecolorallocate($im, 0, 0, 0);
$text_color = imagecolorallocate($im, 233, 14, 91);
imagestring($im, 4, 5, 2,  $ttt, $text_color);
imagepng($im);
imagedestroy($im); 
$_SESSION['cap_sess'] = $ttt;   
