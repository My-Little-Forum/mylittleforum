<?php
ini_set('error_reporting','E_ALL');
session_start();
require('captcha.php');
$captcha = new Captcha();
if(isset($_SESSION['captcha_session']))
 {
  $captcha->generate_image($_SESSION['captcha_session'],'backgrounds/','fonts/');
 }
else
 {
  $captcha->generate_dummy_image();
 }
?>
