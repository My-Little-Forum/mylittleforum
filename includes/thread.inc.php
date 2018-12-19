<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

if (isset($_REQUEST['id'])) {
	$id = intval($_REQUEST['id']);
}

if (empty($id)) {
	header('location: index.php');
	exit;
}

if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
	$tmp_user_id = $_SESSION[$settings['session_prefix'] . 'user_id'];
} else {
	$tmp_user_id = 0;
}

if (isset($_SESSION[$settings['session_prefix'] . 'usersettings']['thread_display']))
	$thread_display = $_SESSION[$settings['session_prefix'] . 'usersettings']['thread_display'];
else
	$thread_display = 0;

if (isset($_GET['page']))
	$page = intval($_GET['page']);
else
	$page = 1;

if (isset($_GET['order']) && $_GET['order'] == 'last_reply')
	$order = 'last_reply';
else
	$order = 'time';

// tid, subject and category of starting posting:
$result = mysqli_query($connid, "SELECT tid, subject, category FROM " . $db_settings['forum_table'] . " WHERE id = " . intval($id) . " LIMIT 1") or raise_error('database_error', mysqli_error($connid));
if (mysqli_num_rows($result) != 1) {
	header('Location: index.php');
	exit;
} else {
	$data = mysqli_fetch_array($result);
	mysqli_free_result($result);
}

// category of this posting accessible by user?
if (is_array($category_ids) && !in_array($data['category'], $category_ids)) {
	header("location: index.php");
	exit;
} elseif ($data['tid'] != $id) {
	// it wasn't the id of the thread start
	header('Location: index.php?mode=thread&id=' . $data['tid'] . '#p' . $id);
	exit;
} else {
	$tid = $data['tid'];
	$smarty->assign("tid", $tid);
	
	if (isset($settings['count_views']) && $settings['count_views'] == 1)
		@mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time=time, last_reply=last_reply, edited=edited, views=views+1 WHERE tid=" . $id);
	
	$smarty->assign('page_title', htmlspecialchars($data['subject']));
	$smarty->assign('category_name', $categories[$data["category"]]);
	
	// get all postings of thread:
	$result = mysqli_query($connid, "SELECT id, pid, tid, ft.user_id, UNIX_TIMESTAMP(ft.time + INTERVAL " . intval($time_difference) . " MINUTE) AS disp_time,
						   UNIX_TIMESTAMP(last_reply + INTERVAL " . intval($time_difference) . " MINUTE) AS last_reply,	
                           UNIX_TIMESTAMP(ft.time) AS time, UNIX_TIMESTAMP(edited + INTERVAL " . intval($time_difference) . " MINUTE) AS e_time,
                           UNIX_TIMESTAMP(edited - INTERVAL " . $settings['edit_delay'] . " MINUTE) AS edited_diff, edited_by, name, email,
                           subject, hp, location, ip, text, cache_text, show_signature, views, category, locked, ip,
                           user_name, user_type, user_email, email_contact, user_hp, user_location, signature, cache_signature, edit_key, rst.user_id AS req_user,
                           " . $db_settings['akismet_rating_table'] . ".spam AS akismet_spam, spam_check_status,
                           " . $db_settings['b8_rating_table'] . ".spam AS b8_spam, training_type
                           FROM " . $db_settings['forum_table'] . " AS ft
                           LEFT JOIN " . $db_settings['entry_cache_table'] . " ON " . $db_settings['entry_cache_table'] . ".cache_id=ft.id
                           LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id=ft.user_id
                           LEFT JOIN " . $db_settings['userdata_cache_table'] . " ON " . $db_settings['userdata_cache_table'] . ".cache_id=" . $db_settings['userdata_table'] . ".user_id
                           LEFT JOIN " . $db_settings['read_status_table'] . " AS rst ON rst.posting_id = ft.id AND rst.user_id = " . intval($tmp_user_id) . "
						   LEFT JOIN " . $db_settings['akismet_rating_table'] . " ON " . $db_settings['akismet_rating_table'] . ".`eid` = `ft`.`id` 
						   LEFT JOIN " . $db_settings['b8_rating_table'] . " ON " . $db_settings['b8_rating_table'] . ".`eid` = `ft`.`id` 
                           WHERE tid = " . $tid . $display_spam_query_and . " ORDER BY ft.time ASC") or raise_error('database_error', mysqli_error($connid));
	
	if (mysqli_num_rows($result) > 0) {
		while ($data = mysqli_fetch_array($result)) {
			
			// tags:
			$tags = getEntryTags($data['id']);
			if (!empty($tags)) {
				unset($tags_array);
				$i=0;
				foreach ($tags as $tag) {
					if (my_strpos($tag, ' ', 0, $lang['charset']))
						$tag_escaped = '"' . $tag . '"';
					else
						$tag_escaped = $tag;
					
					$tags_array[$i]['escaped'] = urlencode($tag_escaped);
					$tags_array[$i]['display'] = htmlspecialchars($tag);
					$i++;
				}
				if (isset($tags_array))
					$data['tags'] = $tags_array;
			}
			$data['formated_time'] = format_time($lang['time_format_full'], $data['disp_time']);
			$ago['days']           = floor((TIMESTAMP - $data['time']) / 86400);
			$ago['hours']          = floor(((TIMESTAMP - $data['time']) / 3600) - ($ago['days'] * 24));
			$ago['minutes']        = floor(((TIMESTAMP - $data['time']) / 60) - ($ago['hours'] * 60 + $ago['days'] * 1440));
			if ($ago['hours'] > 12)
				$ago['days_rounded'] = $ago['days'] + 1;
			else
				$ago['days_rounded'] = $ago['days'];
			$data['ago'] = $ago;
			
			if ($data['user_id'] > 0) {
				if (!$data['user_name'])
					$data['name'] = $lang['unknown_user'];
				else
					$data['name'] = htmlspecialchars($data['user_name']);
			} else
				$data['name'] = htmlspecialchars($data['name']);
			
			
			$data['subject'] = htmlspecialchars($data['subject']);
			
			$authorization = get_edit_authorization($data['id'], $data['user_id'], $data['edit_key'], $data['time'], $data['locked']);
			if ($authorization['edit'] == true)
				$data['options']['edit'] = true;
			if ($authorization['delete'] == true)
				$data['options']['delete'] = true;
			
			if ($data['user_id'] > 0) {
				$data['email']    = $data['user_email'];
				$data['location'] = $data['user_location'];
				$data['hp']       = $data['user_hp'];
				
				if ($settings['avatars'] == 2) {
					$avatarInfo      = getAvatar($data['user_id']);
					$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
					if (isset($avatar) && $avatar['image'] !== false) {
						$image_info       = getimagesize($avatar['image']);
						$avatar['width']  = $image_info[0];
						$avatar['height'] = $image_info[1];
						$data['avatar']   = $avatar;
						unset($avatar);
					}
				}
			} else {
				$data["email_contact"] = 1;
			}
			
			if ($data['edited_diff'] > 0 && $data["edited_diff"] > $data["time"] && $settings['show_if_edited'] == 1) {
				$data['edited']             = true;
				$data['formated_edit_time'] = format_time($lang['time_format_full'], $data['e_time']);
				if ($data['user_id'] == $data['edited_by'])
					$data['edited_by'] = $data['name'];
				else {
					$edited_result = @mysqli_query($connid, "SELECT user_name FROM " . $db_settings['userdata_table'] . " WHERE user_id = " . intval($data['edited_by']) . " LIMIT 1");
					$edited_data   = mysqli_fetch_array($edited_result);
					@mysqli_free_result($edited_result);
					if (!$edited_data['user_name'])
						$data['edited_by'] = $lang['unknown_user'];
					else
						$data['edited_by'] = htmlspecialchars($edited_data['user_name']);
				}
			}
			
			if ($data['cache_text'] == '') {
				// no cached text so parse it and cache it:
				$data['posting'] = html_format($data['text']);
				// make sure not to make a double entry:
				@mysqli_query($connid, "DELETE FROM " . $db_settings['entry_cache_table'] . " WHERE cache_id=" . intval($data['id']));
				@mysqli_query($connid, "INSERT INTO " . $db_settings['entry_cache_table'] . " (cache_id, cache_text) VALUES (" . intval($data['id']) . ",'" . mysqli_real_escape_string($connid, $data['posting']) . "')");
			} else {
				$data['posting'] = $data['cache_text'];
			}
			
			if (isset($data['signature']) && $data['signature'] != '' && $data["show_signature"] == 1) {
				// user has a signature and wants it to be displaed in this posting. Check if it's already cached:
				if ($data['cache_signature'] != '') {
					$data['signature'] = $data['cache_signature'];
				} else {
					$s_result = @mysqli_query($connid, "SELECT cache_signature FROM " . $db_settings['userdata_cache_table'] . " WHERE cache_id=" . intval($data['user_id']) . " LIMIT 1");
					$s_data   = mysqli_fetch_array($s_result);
					if ($s_data['cache_signature'] != '') {
						$data['signature'] = $s_data['cache_signature'];
					} else {
						$data['signature'] = signature_format($data['signature']);
						// cache signature:
						$xxx = mysqli_query($connid, "SELECT COUNT(*) FROM " . $db_settings['userdata_cache_table'] . " WHERE cache_id=" . intval($data['user_id'])) or die(mysqli_error($connid));
						list($row_count) = mysqli_fetch_row($xxx);
						#echo 'row count: '.$row_count.' user_id: '.$data['user_id'].'<br />';
						if ($row_count == 1) {
							@mysqli_query($connid, "UPDATE " . $db_settings['userdata_cache_table'] . " SET cache_signature='" . mysqli_real_escape_string($connid, $data['signature']) . "' WHERE cache_id=" . intval($data['user_id']));
						} else {
							@mysqli_query($connid, "DELETE FROM " . $db_settings['userdata_cache_table'] . " WHERE cache_id=" . intval($data['user_id']));
							@mysqli_query($connid, "INSERT INTO " . $db_settings['userdata_cache_table'] . " (cache_id, cache_signature, cache_profile) VALUES (" . intval($data['user_id']) . ",'" . mysqli_real_escape_string($connid, $data['signature']) . "','')");
						}
					}
				}
			} else {
				unset($data['signature']);
			}
			if (empty($data["email_contact"]))
				$data["email_contact"] = 0;
			if ($data['hp'] != '') {
				$data['hp'] = add_http_if_no_protocol($data['hp']);
			}
			
			if ($data['email'] != '' && $data['email_contact'] == 1)
				$data['email'] = true;
			else
				$data['email'] = false;
			if ($data['location'] != '')
				$data['location'] = htmlspecialchars($data['location']);
			if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0) {
				$data['options']['move'] = true;
				$data['options']['lock'] = true;
				if (($settings['akismet_key'] != '' && $settings['akismet_entry_check'] == 1 && $data['akismet_spam'] == 0 && $data['spam_check_status'] > 0) || ($settings['b8_entry_check'] == 1 && $data['b8_spam'] == 0 || $data['training_type'] == 0))
					$data['options']['report_spam'] = true;
				if (($settings['akismet_key'] != '' && $settings['akismet_entry_check'] == 1 && $data['akismet_spam'] == 1 && $data['spam_check_status'] > 0) || ($settings['b8_entry_check'] == 1 && $data['b8_spam'] == 1 || $data['training_type'] == 0))
					$data['options']['flag_ham'] = true;
			}
			if ($settings['count_views'] == 1) {
				$views = $data['views'] - 1; // this subtracts the first view by the author after posting
				if ($views < 0)
					$views = 0; // prevents negative number of views
				$data['views'] = $views;
			} else
				$data['views'] = 0;
			
			if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
				// bookmark handling
				$user_id = $_SESSION[$settings['session_prefix'] . 'user_id'];
				$bookmark_result = mysqli_query($connid, "SELECT TRUE AS 'bookmark' FROM " . $db_settings['bookmark_table'] . " WHERE `user_id` = " . intval($user_id) . " AND `posting_id` = " . intval($data['id']) . "") or raise_error('database_error', mysqli_error($connid));
				$bookmark = mysqli_fetch_row($bookmark_result);
				mysqli_free_result($bookmark_result);
				if (isset($bookmark) && intval($bookmark) == 1) {
					$data['bookmarkedby']               = intval($user_id);
					$data['options']['delete_bookmark'] = true;
				} else
					$data['options']['add_bookmark'] = true;
				// read-status handling
				$rstatus = save_read_status($connid, $user_id, $data['id']);
			}
			// set read or new status of messages
			$data = getMessageStatus($data, $last_visit);
			
			$data_array[$data["id"]]     = $data;
			$child_array[$data["pid"]][] = $data["id"];
		}
		mysqli_free_result($result);
	} else {
		header("location: index.php");
		exit;
	}
	
	$subnav_link = array(
		'mode' => 'index',
		'name' => 'thread_entry_back_link',
		'title' => 'thread_entry_back_title'
	);
	$smarty->assign('id', $id);
	$smarty->assign('data', $data_array);
	if (isset($child_array))
		$smarty->assign('child_array', $child_array);
	$smarty->assign('subnav_link', $subnav_link);
	$smarty->assign('page', $page);
	$smarty->assign('order', $order);
	$smarty->assign('category', $category);
	if ($thread_display == 0)
		$smarty->assign('subtemplate', 'thread.inc.tpl');
	else
		$smarty->assign('subtemplate', 'thread_linear.inc.tpl');
	$template = 'main.tpl';
}
?>
