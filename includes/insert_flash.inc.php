<?php
if(!defined('IN_INDEX'))
 {
  header('Location: ../index.php');
  exit;
 }

if($settings['bbcode_flash']==1) $template = 'insert_flash.tpl';
?>
