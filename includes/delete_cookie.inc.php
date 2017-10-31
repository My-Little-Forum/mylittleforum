<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

setcookie($settings['session_prefix'].'userdata', '', 0);

if (isset($_POST['method']) && $_POST['method'] == 'ajax') exit;

else {
	$smarty->assign('message', 'cookie_deleted');
	$smarty->assign('subnav_location', 'subnav_delete_cookie');
	$smarty->assign('subtemplate', 'info.inc.tpl');
	$template = 'main.tpl';
}

?>
