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
	'old_get_html'  => FALSE,
	'get_html'      => TRUE,
	'get_uris'      => TRUE,
	'get_bbcode'    => FALSE
);

$B8_CONFIG_DEGENERATOR = array(
	'encoding'  => isset($lang['charset']) ? $lang['charset'] : 'UTF-8',
	'multibyte' => function_exists('mb_strtolower') && function_exists('mb_strtoupper') && function_exists('mb_substr')
);

$B8_CONFIG_DATABASE = array(
	'storage' => 'mysqli'
);

$B8_CONFIG_AUTHENTICATION = array(
	'database'   => $db_settings['database'],
	'table_name' => $db_settings['b8_wordlist_table'],
	'host'       => $db_settings['host'],
	'user'       => $db_settings['user'],
	'pass'       => $db_settings['password']
);
/** config for b8 filter **/
?>