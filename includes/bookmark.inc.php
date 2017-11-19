<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
	$user_id = $_SESSION[$settings['session_prefix'].'user_id'];
	// Derzeit kann nur reorder von aussen kommen.
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'reorder')
		$action = $_REQUEST['action'];
	elseif (isset($_GET['delete_bookmark']))
		$action = 'delete_bookmark';
	elseif (isset($_POST['delete_bookmark_submit']))
		$action = 'delete_bookmark_submit';
	elseif (isset($_GET['move_up_bookmark']) || isset($_GET['move_down_bookmark']))
		$action = 'move_bookmark';
	elseif (isset($_GET['edit_bookmark']))
		$action = 'edit_bookmark';
	elseif (isset($_POST['edit_bookmark_submit']) && isset($_POST['bookmark']))
		$action = 'edit_bookmark_submit';
	else {
		$action = 'main';
		$filter = isset($_GET['filter']) && $_GET['filter'] != '' ? trim(urldecode(trim($_GET['filter']))) : false;
		// Taken from search.inc.php
		if ($filter !== false) {
			// split search query at spaces, but not between double quotes:
			$help_pattern = '[!/*/~/?]'; // pattern to hide spaces between quotes
			$x_filter = preg_replace_callback("#\"(.+?)\"#is", create_function('$string', 'global $help_pattern; return str_replace(" ",$help_pattern,$string[1]);'), $filter);
			$x_filter_array = explode(' ', my_strtolower($x_filter, $lang['charset']));
			foreach($x_filter_array as $item)
				$filter_array[] = mysqli_real_escape_string($connid, str_replace($help_pattern, ' ', $item));
			$filter_string = "LOWER(".$db_settings['bookmark_table'].".`tags`) LIKE '%;".implode(";%' AND LOWER(".$db_settings['bookmark_table'].".`tags`) LIKE '%;",$filter_array).";%'";
		}
	}

	switch($action) {
		case 'main':
			$bookmark_result = mysqli_query($connid, "SELECT ".$db_settings['bookmark_table'].".`subject`, ".$db_settings['bookmark_table'].".`tags`, ".$db_settings['forum_table'].".`user_id`, 
				".$db_settings['forum_table'].".`id`, IF (".$db_settings['forum_table'].".`user_id` = 0, `name`, 
				(SELECT `user_name` FROM ".$db_settings['userdata_table']." WHERE ".$db_settings['userdata_table'].".`user_id` = ".$db_settings['forum_table'].".`user_id` ) ) AS `user_name`, 
				UNIX_TIMESTAMP(".$db_settings['bookmark_table'].".`time` + INTERVAL ".$time_difference." MINUTE) AS `bookmark_time`, 
				UNIX_TIMESTAMP(".$db_settings['forum_table'].".`time` + INTERVAL ".$time_difference." MINUTE) AS `disp_time`, 
				UNIX_TIMESTAMP(".$db_settings['forum_table'].".`last_reply` + INTERVAL ".$time_difference." MINUTE) AS `reply_time`, 
				".$db_settings['bookmark_table'].".`id` AS `bid` FROM ".$db_settings['forum_table']." JOIN ".$db_settings['bookmark_table']." 
				ON ".$db_settings['forum_table'].".`id` = `posting_id` WHERE ".$db_settings['bookmark_table'].".`user_id` = ".intval($user_id)." ".(isset($filter_string) ? "AND ".$filter_string : ""  )."  
				ORDER BY ".$db_settings['bookmark_table'].".`order_id` ASC") or raise_error('database_error',mysqli_error($connid));
			$total_bookmarks = mysqli_num_rows($bookmark_result);
			$i = 0;
			$bookmarkdata = false;
			
			if (empty($row['user_name']))
				$row['user_name'] = $lang['unknown_user'];
			
			while ($row = mysqli_fetch_array($bookmark_result)) {
				$bookmarkdata[$i]['subject']       = htmlspecialchars($row['subject']);
				$bookmarkdata[$i]['user_name']     = htmlspecialchars($row['user_name']);
				$bookmarkdata[$i]['user_id']       = intval($row['user_id']);
				$bookmarkdata[$i]['id']            = intval($row['id']);
				$bookmarkdata[$i]['bid']           = intval($row['bid']);
				$bookmarkdata[$i]['bookmark_time'] = format_time($lang['time_format_full'], $row['bookmark_time']);
				$bookmarkdata[$i]['posting_time']  = format_time($lang['time_format_full'], $row['disp_time']);
				$bookmarkdata[$i]['reply_time']    = format_time($lang['time_format_full'], $row['reply_time']);

				if (!empty($row['tags'])) {
					$tags_array = false;
					$tags_help_array = explode(';', $row['tags']);
					$tidx = 0;
					foreach($tags_help_array as $tag) {
						if ($tag != '') {
							$tag = trim($tag);
							if (my_strpos($tag, ' ', 0, $lang['charset']))
								$tag_escaped='"'.$tag.'"';
							else 
								$tag_escaped = $tag;
							$tags_array[$tidx]['escaped'] = urlencode($tag_escaped);
							$tags_array[$tidx]['display'] = htmlspecialchars($tag);
							$tidx++;
						}
					}
					if (isset($tags_array))
						$bookmarkdata[$i]['tags'] = $tags_array;
				}

				$i++;
			}
			
			mysqli_free_result($bookmark_result);
			if ($bookmarkdata)
				$smarty->assign('bookmarkdata',$bookmarkdata);

			$breadcrumbs[0]['link'] = 'index.php?mode=bookmarks';
			$breadcrumbs[0]['linkname'] = 'subnav_bookmarks';
			$smarty->assign('breadcrumbs',$breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_bookmarks');
			$smarty->assign('filter', isset($filter_string));
			$smarty->assign('total_bookmarks', $total_bookmarks);
			$smarty->assign('action', 'bookmark');
			$smarty->assign('subtemplate', 'bookmark.inc.tpl');
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
			$result = mysqli_query($connid, "SELECT `subject` FROM ".$db_settings['bookmark_table']." WHERE id= ".$id." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			if(mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_array($result);
				$bookmark['id']    = $id;
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
			mysqli_query($connid, "DELETE FROM ".$db_settings['bookmark_table']." WHERE `id` = ". intval($_POST['id']) ." AND `user_id` = ". intval($user_id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			header("Location: index.php?mode=bookmarks");
			exit;
			break;
			
		case 'edit_bookmark':
			$id = intval($_GET['edit_bookmark']);
			$result = mysqli_query($connid, "SELECT `subject`, `tags` FROM ".$db_settings['bookmark_table']." WHERE id= ".$id." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_array($result);
				$bookmark['id']    = $id;
				$bookmark['title'] = htmlspecialchars($row['subject']);
				$bookmark['tags']  = implode(", ", array_filter(array_map('htmlspecialchars', explode(';', $row['tags'])), function($value) { return $value !== ''; }));
				$smarty->assign('bookmark', $bookmark);
			}
			mysqli_free_result($result);
			$breadcrumbs[0]['link'] = 'index.php?mode=bookmarks';
			$breadcrumbs[0]['linkname'] = 'subnav_bookmarks';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('action', 'edit_bookmark');
			$smarty->assign('subnav_location', 'subnav_edit_bookmark');
			$smarty->assign('subtemplate', 'bookmark.inc.tpl');
			$template = 'main.tpl';
			break;
			
		case 'edit_bookmark_submit':
			if (!isset($_POST['bookmark']) || empty($_POST['bookmark']))
				$errors[] = 'error_no_bookmark_subject';
			if (my_strlen(trim($_POST['bookmark']), $lang['charset']) > $settings['subject_maxlength'])
				$errors[] = 'error_bookmark_subject_too_long';
            
			$tags = "";
			if (isset($_POST['tags']) && trim($_POST['tags']) != '') {
				$tags = trim($_POST['tags']);
				
				if (my_strlen($tags, $lang['charset']) > 253) 
					$errors[] = 'error_bookmark_tags_too_long';
				
				// Taken from posting.inc.php
				$tags       = str_replace(';', '', $tags);
				$tags       = str_replace('"', '', $tags);
				$tags_array = explode(',', $tags);
				$tags       = ';';
				foreach ($tags_array as $tag) {
					unset($too_long_word);
					$too_long_word = too_long_word($tag, $settings['text_word_maxlength'], $lang['word_delimiters']);
					if ($too_long_word) { 
						$errors[] = 'error_bookmark_word_too_long'; 
						break;
					}
					$tags .= trim($tag).';';
				}
			}
			
			if (empty($errors)) {
				mysqli_query($connid, "UPDATE ".$db_settings['bookmark_table']." SET `subject` = '". mysqli_real_escape_string($connid, trim($_POST['bookmark'])) ."', `tags` = '". mysqli_real_escape_string($connid, $tags) ."' WHERE `id` = ". intval($_POST['id']) ." AND `user_id` = ". intval($user_id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				header("Location: index.php?mode=bookmarks");
				exit;
			}
			else {
				$bookmark['id']    = intval($_POST['id']);
				$bookmark['title'] = htmlspecialchars(trim($_POST['bookmark']));
				$bookmark['tags']  = htmlspecialchars(trim($_POST['tags']));
				$smarty->assign('bookmark', $bookmark);
				$smarty->assign('errors', $errors);
				if (isset($too_long_word))
					$smarty->assign('word', $too_long_word);
				$breadcrumbs[0]['link'] = 'index.php?mode=bookmarks';
				$breadcrumbs[0]['linkname'] = 'subnav_bookmarks';
				$smarty->assign('breadcrumbs', $breadcrumbs);
				$smarty->assign('action', 'edit_bookmark');
				$smarty->assign('subnav_location', 'subnav_edit_bookmark');
				$smarty->assign('subtemplate', 'bookmark.inc.tpl');
				$template = 'main.tpl';
			}
			break;
			
		case 'reorder':
			if (isset($_POST['bookmarks'])) {		
				$items = array_map(function($item) use($connid) { return mysqli_real_escape_string($connid, $item); }, explode(',', $_POST['bookmarks']));
				$order_result = mysqli_query($connid, "SELECT `id`, `order_id` FROM ".$db_settings['bookmark_table']." WHERE `id` IN (".implode(",", $items).") ORDER BY `order_id` ASC");
				$order = false;
				
				while ($row = mysqli_fetch_array($order_result))
					$order[] = $row["order_id"];
				mysqli_free_result($bookmark_result);
				
				if ($order !== false && count($order) == count($items)) {
					for ($i = 0; $i < count($items); $i++) {
						$order_id = $order[$i];
						$item_id  = $items[$i];
						mysqli_query($connid, "UPDATE ".$db_settings['bookmark_table']." SET `order_id` = ". intval($order_id) ." WHERE `id` = ". intval($item_id) ." AND `user_id` = ". intval($user_id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
					}
				}
			}
			exit;
			break;
			
		default:
			header("Location: index.php");
			exit;
			break;
	}
} else {
	header("Location: index.php");
	exit;
}
?>