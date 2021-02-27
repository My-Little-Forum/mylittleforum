<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

/** config for b8 filter **/
$B8_CONFIG_LEXER = array(
	'min_size'      => 3,
	'max_size'      => 30,
	'allow_numbers' => FALSE,
	'get_html'      => TRUE,
	'get_uris'      => TRUE,
	'get_bbcode'    => FALSE
);

$B8_CONFIG_DEGENERATOR = array(
	'encoding'  => isset($lang['charset']) ? $lang['charset'] : 'UTF-8',
	'multibyte' => function_exists('mb_strtolower') && function_exists('mb_strtoupper') && function_exists('mb_substr')
);

$B8_CONFIG_DATABASE_TYPE = array(
	'storage' => 'mysql'
);

$B8_CONFIG_STORAGE = array(
	'resource' => new mysqli($db_settings['host'], $db_settings['user'], $db_settings['password'], $db_settings['database']),
	'table'    => $db_settings['b8_wordlist_table']
);
/** config for b8 filter **/
?>