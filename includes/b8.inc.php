<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

// include B8 php resources
require 'modules/b8/b8.php';

// include config
require 'config/b8_config.php';

// create instance
//$B8_BAYES_FILTER = new b8($B8_CONFIG_DATABASE_TYPE, $B8_CONFIG_AUTHENTICATION, $B8_CONFIG_LEXER, $B8_CONFIG_DEGENERATOR);
$B8_BAYES_FILTER = new b8\b8($B8_CONFIG_DATABASE_TYPE, $B8_CONFIG_STORAGE, $B8_CONFIG_LEXER, $B8_CONFIG_DEGENERATOR);
?>