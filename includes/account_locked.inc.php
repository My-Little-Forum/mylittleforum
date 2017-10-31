<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

$smarty->assign('message', 'user_locked_message');
$smarty->assign('subnav_location', 'subnav_locked');
$smarty->assign('subtemplate', 'info.inc.tpl');
$template = 'main.tpl';
?>
