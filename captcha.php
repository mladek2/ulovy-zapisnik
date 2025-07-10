<?php
session_start();
$code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
$_SESSION['captcha_code'] = $code;

header('Content-Type: image/png');
$image = imagecreatetruecolor(120, 40);
$bg = imagecolorallocate($image, 255, 255, 255);
$txt = imagecolorallocate($image, 0, 0, 0);
imagefilledrectangle($image, 0, 0, 120, 40, $bg);
imagettftext($image, 20, 0, 10, 30, $txt, __DIR__ . '/fonts/arial.ttf', $code);
imagepng($image);
imagedestroy($image);
