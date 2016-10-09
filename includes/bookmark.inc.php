<?php
if(!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

if(isset($_SESSION[$settings['session_prefix'].'user_id'])) {
	$user_id = $_SESSION[$settings['session_prefix'].'user_id'];
	
	// Derzeit kann nur reorder von aussen kommen.
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'reorder')
		$action = $_REQUEST['action'];
	elseif(isset($_GET['delete_bookmark']))
		$action = 'delete_bookmark';
	elseif(isset($_POST['delete_bookmark_submit']))
		$action = 'delete_bookmark_submit';
	elseif(isset($_GET['move_up_bookmark']) || isset($_GET['move_down_bookmark'])) 
		$action = 'move_bookmark';
	elseif (isset($_GET['edit_bookmark']))
		$action = 'edit_bookmark';
	elseif(isset($_POST['edit_bookmark_submit']) && isset($_POST['bookmark']) && !empty($_POST['bookmark']))
		$action = 'edit_bookmark_submit';
	else
		$action = 'main';

	switch($action) {
		case 'main':				
			$bookmark_result = mysqli_query($connid, "SELECT ".$db_settings['bookmark_table'].".`subject`, ".$db_settings['forum_table'].".`user_id`, 
													".$db_settings['forum_table'].".`id`, IF (".$db_settings['forum_table'].".`user_id` = 0, `name`, 
													(SELECT `user_name` FROM ".$db_settings['userdata_table']." WHERE ".$db_settings['userdata_table'].".`user_id` = ".$db_settings['forum_table'].".`user_id` ) ) AS `user_name`, 
													UNIX_TIMESTAMP(".$db_settings['bookmark_table'].".`time` + INTERVAL ".$time_difference." MINUTE) AS `bookmark_time`, 
													UNIX_TIMESTAMP(".$db_settings['forum_table'].".`time` + INTERVAL ".$time_difference." MINUTE) AS `disp_time`, 
													UNIX_TIMESTAMP(".$db_settings['forum_table'].".`last_reply` + INTERVAL ".$time_difference." MINUTE) AS `reply_time`, 
													".$db_settings['bookmark_table'].".`id` AS `bid` FROM ".$db_settings['forum_table']." JOIN ".$db_settings['bookmark_table']." 
													ON ".$db_settings['forum_table'].".`id` = `posting_id` WHERE ".$db_settings['bookmark_table'].".`user_id` = ".intval($user_id)." 
													ORDER BY ".$db_settings['bookmark_table'].".`order_id` ASC") or raise_error('database_error',mysqli_error($connid));
			$total_bookmarks = mysqli_num_rows($bookmark_result);
			$i=0;
			$bookmarkdata = false;
			
			if (empty($row['user_name']))
				$row['user_name'] = $lang['unknown_user'];
			
			while($row = mysqli_fetch_array($bookmark_result)) {
				$bookmarkdata[$i]['subject']       = htmlspecialchars($row['subject']);
				$bookmarkdata[$i]['user_name']     = htmlspecialchars($row['user_name']);
				$bookmarkdata[$i]['user_id']       = intval($row['user_id']);
				$bookmarkdata[$i]['id']            = intval($row['id']);
				$bookmarkdata[$i]['bid']           = intval($row['bid']);
				$bookmarkdata[$i]['bookmark_time'] = format_time($lang['time_format_full'],$row['bookmark_time']);
				$bookmarkdata[$i]['posting_time']  = format_time($lang['time_format_full'],$row['disp_time']);
				$bookmarkdata[$i]['reply_time']    = format_time($lang['time_format_full'],$row['reply_time']);
				$i++;
			}
			
			mysqli_free_result($bookmark_result);
			if ($bookmarkdata)
				$smarty->assign('bookmarkdata',$bookmarkdata);

			//$breadcrumbs[0]['link'] = 'index.php?mode=bookmarks';
			//$breadcrumbs[0]['linkname'] = 'subnav_bookmarks';
			//$smarty->assign('breadcrumbs',$breadcrumbs);
			$smarty->assign('subnav_location','subnav_bookmarks');
			$smarty->assign('total_bookmarks',$total_bookmarks);
			$smarty->assign('action','bookmark');
			$smarty->assign('subtemplate','bookmark.inc.tpl');
			$template = 'main.tpl';

			break;
			
		case 'move_bookmark':
			if (isset($_GET['move_up_bookmark']))
				move_item($db_settings['bookmark_table'], intval($_GET['move_up_bookmark']), 'up');
			elseif (isset($_GET['move_down_bookmark'])) 
				move_item($db_settings['bookmark_table'], intval($_GET['move_down_bookmark']), 'down');
			header("Location: index.php?mode=bookmarks");
			exit;
			break;
			
		case 'delete_bookmark':
			$id = intval($_GET['delete_bookmark']);
			$result = mysqli_query($connid, "SELECT `posting_id`, `subject` FROM ".$db_settings['bookmark_table']." WHERE id= ".$id." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			if(mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_array($result);
				$bookmark['id']    = $id;
				$bookmark['pid']   = intval($row['posting_id']);
				$bookmark['title'] = htmlspecialchars($row['subject']);
				$smarty->assign('bookmark', $bookmark);
			}
			mysqli_free_result($result);
			$breadcrumbs[0]['link'] = 'index.php?mode=bookmarks';
			$breadcrumbs[0]['linkname'] = 'subnav_bookmarks';
			$smarty->assign('breadcrumbs',$breadcrumbs);
			$smarty->assign('action','delete_bookmark');
			$smarty->assign('subnav_location','subnav_delete_bookmark');
			$smarty->assign('subtemplate','bookmark.inc.tpl');
			$template = 'main.tpl';
			break;
			
		case 'delete_bookmark_submit':	
			mysqli_query($connid, "DELETE FROM ".$db_settings['bookmark_table']." WHERE `id` = ".intval($_POST['id'])." AND `user_id` = ".intval($user_id)." LIMIT 1") or raise_error('database_error',mysqli_error($connid));
			header("Location: index.php?mode=bookmarks");
			exit;
			break;
			
		case 'edit_bookmark':
			$id = intval($_GET['edit_bookmark']);
			$result = mysqli_query($connid, "SELECT `posting_id`, `subject` FROM ".$db_settings['bookmark_table']." WHERE id= ".$id." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			if(mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_array($result);
				$bookmark['id']    = $id;
				$bookmark['pid']   = intval($row['posting_id']);
				$bookmark['title'] = htmlspecialchars($row['subject']);
				$smarty->assign('bookmark', $bookmark);
			}
			mysqli_free_result($result);
			$breadcrumbs[0]['link'] = 'index.php?mode=bookmarks';
			$breadcrumbs[0]['linkname'] = 'subnav_bookmarks';
			$smarty->assign('breadcrumbs',$breadcrumbs);
			$smarty->assign('action','edit_bookmark');
			$smarty->assign('subnav_location','subnav_edit_bookmark');
			$smarty->assign('subtemplate','bookmark.inc.tpl');
			$template = 'main.tpl';
			break;
			
		case 'edit_bookmark_submit':
			mysqli_query($connid, "UPDATE ".$db_settings['bookmark_table']." SET `subject` = '".mysqli_real_escape_string($connid, $_POST['bookmark'])."' WHERE `id` = ".intval($_POST['id'])." AND `user_id` = ".intval($user_id)." LIMIT 1") or raise_error('database_error',mysqli_error($connid));
			header("Location: index.php?mode=bookmarks");
			exit;
			break;
			
		case 'reorder':
			if (isset($_POST['bookmarks'])) {			
				$items = explode(',', $_POST['bookmarks']);
				$order_id = 1;
				foreach($items as $id) {
					mysqli_query($connid, "UPDATE ".$db_settings['bookmark_table']." SET `order_id` = ".$order_id." WHERE id = ".intval($id)." AND `user_id` = ".intval($user_id)." LIMIT 1") or raise_error('database_error',mysqli_error($connid));
					$order_id++;
				}
			}
			exit;
			break;	
			
		default:
			header("Location: index.php");
			exit;
			break;
	}
 
 
}
else {
	header("Location: index.php");
	exit;
}
?>