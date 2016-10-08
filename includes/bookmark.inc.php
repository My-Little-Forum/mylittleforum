<?php
if(!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}
 
// only registered users have access to its bookmark list
if(isset($_SESSION[$settings['session_prefix'].'user_id'])) {
	$user_id = $_SESSION[$settings['session_prefix'].'user_id'];
	
	//if(isset($_REQUEST['action']))
	//	$action = $_REQUEST['action'];
	
	
	if(isset($_GET['delete_bookmark']))
		$action = 'delete_bookmark';
	elseif(isset($_POST['delete_bookmark_submit']))
		$action = 'delete_bookmark_submit';
	elseif(isset($_GET['move_up_bookmark']) || isset($_GET['move_down_bookmark'])) 
		$action = 'move_bookmark';
	else
		$action = 'main';
	
	
	
	$bookmark_count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['bookmark_table']." WHERE `user_id` = ".intval($user_id)."") or raise_error('database_error',mysqli_error($connid));	
	list($total_bookmarks) = mysqli_fetch_row($bookmark_count_result);
	mysqli_free_result($bookmark_count_result);
	$total_pages = ceil($total_bookmarks / $settings['users_per_page']);
	
	if(isset($_GET['page']))
		$page = intval($_GET['page']); 
	else 
		$page = 1;
	
	if($page > $total_pages) 
		$page = $total_pages;
	
	if($page < 1) 
		$page = 1;

	
	switch($action) {
		case 'main':
			// Sortierung Auf-/Absteigend
			if(isset($_GET['descasc']) && ($_GET['descasc'] == 'DESC' || $_GET['descasc'] == 'ASC'))
				$descasc = $_GET['descasc']; 
			else
				$descasc = 'ASC';
			
			// Spalte, nach der sortiert werden soll
			if(isset($_GET['order']) && ($_GET['order'] == 'user_name' || $_GET['order'] == 'subject' || $_GET['order'] == 'time' || $_GET['order'] == 'reply_time'))
				$order = $_GET['order'];
			else
				$order = $db_settings['bookmark_table'].".`order_id`";
			
			$bookmark_limit = ($page-1) * $settings['users_per_page'];
	
			$bookmark_result = mysqli_query($connid, "SELECT `subject`, ".$db_settings['forum_table'].".`user_id`, 
													".$db_settings['forum_table'].".`id`, IF (".$db_settings['forum_table'].".`user_id` = 0, `name`, 
													(SELECT `user_name` FROM ".$db_settings['userdata_table']." WHERE ".$db_settings['userdata_table'].".`user_id` = ".$db_settings['forum_table'].".`user_id` ) ) AS `user_name`, 
													UNIX_TIMESTAMP(`time` + INTERVAL ".$time_difference." MINUTE) AS `disp_time`, UNIX_TIMESTAMP(`last_reply` + INTERVAL ".$time_difference." MINUTE) AS `reply_time`, 
													".$db_settings['bookmark_table'].".`id` AS `bid` FROM ".$db_settings['forum_table']." JOIN ".$db_settings['bookmark_table']." 
													ON ".$db_settings['forum_table'].".`id` = ".$db_settings['bookmark_table'].".`pid` WHERE ".$db_settings['bookmark_table'].".`user_id` = ".intval($user_id)." 
													ORDER BY ".mysqli_real_escape_string($connid, $order)." ".$descasc." 
													LIMIT ".$bookmark_limit.", ".$settings['users_per_page']." ") or raise_error('database_error',mysqli_error($connid));
			$i=0;
			$bookmarkdata = false;
			while($row = mysqli_fetch_array($bookmark_result)) {
				$bookmarkdata[$i]['subject']      = htmlspecialchars($row['subject']);
				$bookmarkdata[$i]['user_name']    = htmlspecialchars($row['user_name']);
				$bookmarkdata[$i]['user_id']      = intval($row['user_id']);
				$bookmarkdata[$i]['id']           = intval($row['id']);
				$bookmarkdata[$i]['bid']          = intval($row['bid']);
				$bookmarkdata[$i]['posting_time'] = format_time($lang['time_format_full'],$row['disp_time']);
				$bookmarkdata[$i]['reply_time']   = format_time($lang['time_format_full'],$row['reply_time']);
				$i++;
			}
			
			mysqli_free_result($bookmark_result);
			if ($bookmarkdata)
				$smarty->assign('bookmarkdata',$bookmarkdata);
			$smarty->assign('total_bookmarks',$total_bookmarks);
			$smarty->assign('bookmark_limit',$bookmark_limit);
		
			//$breadcrumbs[0]['link'] = 'index.php?mode=bookmarks';
			//$breadcrumbs[0]['linkname'] = 'subnav_bookmarks';
			//$smarty->assign('breadcrumbs',$breadcrumbs);
			$smarty->assign('order',$order);
			$smarty->assign('descasc',$descasc);
			$smarty->assign('page',$page);
			$smarty->assign('pagination', pagination($total_pages,$page,3));
			$smarty->assign('subnav_location','subnav_bookmarks');
			$smarty->assign('action','bookmark');
			$smarty->assign('subtemplate','bookmark.inc.tpl');
			$template = 'main.tpl';

			break;
			
		case 'move_bookmark':
			if (isset($_GET['move_up_bookmark']))
				move_item($db_settings['bookmark_table'], intval($_GET['move_up_bookmark']), 'up');
			elseif (isset($_GET['move_down_bookmark'])) 
				move_item($db_settings['bookmark_table'], intval($_GET['move_down_bookmark']), 'down');
			header("Location: index.php?mode=bookmarks&page=".intval($page)."");
			exit;
			break;
			
		case 'delete_bookmark':
			$id = intval($_GET['delete_bookmark']);
			$result = mysqli_query($connid, "SELECT `subject` FROM ".$db_settings['forum_table']." WHERE `id`= (SELECT `pid` FROM ".$db_settings['bookmark_table']." WHERE id= ".$id." LIMIT 1)") or raise_error('database_error', mysqli_error($connid));
			if(mysqli_num_rows($result) > 0) {
				$data = mysqli_fetch_array($result);
				$bookmark['id'] = $id;
				$bookmark['title'] = htmlspecialchars($data['subject']);
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
			header("Location: index.php?mode=bookmarks&page=".intval($page)."");
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