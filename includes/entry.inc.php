<?php
	if (!defined('IN_INDEX')) {
		header('Location: ../index.php');
		exit;
	}
	
	if (isset($_REQUEST['id']))
		$id = intval($_REQUEST['id']);
	else {
		header('Location: index.php');
		exit;
	}
	
	if (isset($_GET['page']))
		$page = intval($_GET['page']);
	else
		$page = 1;
	
	if (isset($_GET['order']) && $_GET['order'] == 'last_reply')
		$order = 'last_reply';
	else
		$order = 'time';
	
	if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
		$tmp_user_id = $_SESSION[$settings['session_prefix'] . 'user_id'];
	} else {
		$tmp_user_id = 0;
	}
	
	if (isset($id) && $id > 0) {
		$result = @mysqli_query($connid, "SELECT ft.id, ft.pid, ft.tid, ft.user_id, UNIX_TIMESTAMP(ft.time + INTERVAL " . $time_difference . " MINUTE) AS disp_time,
                         UNIX_TIMESTAMP(ft.time) AS time, UNIX_TIMESTAMP(edited + INTERVAL " . $time_difference . " MINUTE) AS edit_time,
                         UNIX_TIMESTAMP(edited - INTERVAL " . $settings['edit_delay'] . " MINUTE) AS edited_diff, edited_by, name, email,
                         subject, hp, location, ip, text, cache_text, show_signature, category, locked, views, edit_key,
                         user_name, user_type, user_email, email_contact, user_hp, user_location, signature, cache_signature, rst.user_id AS req_user,
						 " . $db_settings['akismet_rating_table'] . ".spam AS akismet_spam, spam_check_status,
						 " . $db_settings['b8_rating_table'] . ".spam AS b8_spam, training_type
                         FROM " . $db_settings['forum_table'] . " AS ft
                         LEFT JOIN " . $db_settings['entry_cache_table'] . " ON " . $db_settings['entry_cache_table'] . ".cache_id=ft.id
                         LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id=ft.user_id
                         LEFT JOIN " . $db_settings['userdata_cache_table'] . " ON " . $db_settings['userdata_cache_table'] . ".cache_id=" . $db_settings['userdata_table'] . ".user_id
                         LEFT JOIN " . $db_settings['read_status_table'] . " AS rst ON rst.posting_id = ft.id AND rst.user_id = " . intval($tmp_user_id) . "
						 LEFT JOIN " . $db_settings['akismet_rating_table'] . " ON " . $db_settings['akismet_rating_table'] . ".`eid` = `ft`.`id` 
						 LEFT JOIN " . $db_settings['b8_rating_table'] . " ON " . $db_settings['b8_rating_table'] . ".`eid` = `ft`.`id` 
                         WHERE ft.id = " . intval($id)) or raise_error('database_error', mysqli_error($connid));

		if (mysqli_num_rows($result) == 1) {
			$entrydata = mysqli_fetch_array($result);
			mysqli_free_result($result);
			
			$entrydata['formated_time'] = format_time($lang['time_format_full'], $entrydata['disp_time']);
			$entrydata['tags']          = getEntryTags($id);
			
			// category of this posting accessible by user?
			if (is_array($category_ids) && !in_array($entrydata['category'], $category_ids)) {
				header("Location: index.php");
				exit;
			}
			
			if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
				// bookmark handling
				$user_id = $_SESSION[$settings['session_prefix'] . 'user_id'];
				$bookmark_result = mysqli_query($connid, "SELECT TRUE AS 'bookmark' FROM " . $db_settings['bookmark_table'] . " WHERE `user_id` = " . intval($user_id) . " AND `posting_id` = " . intval($id) . "") or raise_error('database_error', mysqli_error($connid));
				$bookmark = mysqli_fetch_row($bookmark_result);
				mysqli_free_result($bookmark_result);
				if (isset($bookmark) && intval($bookmark) == 1)
					$entrydata['bookmarkedby'] = intval($user_id);
				// read-status handling
				$rstatus = save_read_status($connid, $user_id, $id);
			}
			
			if ($entrydata['req_user'] !== NULL and is_numeric($entrydata['req_user']))
				$entrydata['is_read'] = true;
			else
				$entrydata['is_read'] = false;
			
			$entrydata['spam'] = $entrydata['akismet_spam'] || $entrydata['b8_spam'] ? 1 : 0;

			$smarty->assign('is_read', $entrydata['is_read']);
			
			if (isset($settings['count_views']) && $settings['count_views'] == 1)
				mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time=time, last_reply=last_reply, edited=edited, views=views+1 WHERE id=" . $id);
			
			if ($entrydata['user_id'] > 0) {
				if ($settings['avatars'] == 2) {
					$avatarInfo      = getAvatar($entrydata['user_id']);
					$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
					
					if (isset($avatar) && $avatar['image'] !== false) {
						$image_info       = getimagesize($avatar['image']);
						$avatar['width']  = $image_info[0];
						$avatar['height'] = $image_info[1];
						$smarty->assign('avatar', $avatar);
					}
				}
				
				$entrydata['email']    = $entrydata['user_email'];
				#$entrydata['email_contact'] = $userdata['email_contact'];
				$entrydata['location'] = $entrydata['user_location'];
				$entrydata['hp']       = $entrydata['user_hp'];
			} else
				$entrydata['email_contact'] = 1;
		} else {
			header("Location: index.php");
			exit;
		}
	} else {
		header("Location: index.php");
		exit;
	}
	
	if ($entrydata['cache_text'] == '') {
		// no cached text so parse it and cache it:
		$ftext = html_format($entrydata['text']);
		// make sure not to make a double entry:
		@mysqli_query($connid, "DELETE FROM " . $db_settings['entry_cache_table'] . " WHERE cache_id=" . intval($entrydata['id']));
		@mysqli_query($connid, "INSERT INTO " . $db_settings['entry_cache_table'] . " (cache_id, cache_text) VALUES (" . intval($entrydata['id']) . ",'" . mysqli_real_escape_string($connid, $ftext) . "')");
	} else {
		$ftext = $entrydata['cache_text'];
	}
	
	if (isset($_REQUEST['ajax_preview'])) {
		header('Content-Type: application/xml; charset=UTF-8');
		echo '<?xml version="1.0"?>';
?><posting><content><![CDATA[<?php
		echo $ftext;
?>]]></content><locked><?php
		echo $entrydata['locked'];
?></locked></posting><?php
		exit;
	}
	
	// thread-data:
	$thread = $entrydata['tid'];
	if ($entrydata['akismet_spam'] == 1 || $entrydata['b8_spam'] == 1)
		$display_spam_query_and = '';
	$result = mysqli_query($connid, "SELECT id, pid, tid, ft.user_id, UNIX_TIMESTAMP(ft.time) AS time, UNIX_TIMESTAMP(ft.time + INTERVAL " . $time_difference . " MINUTE) AS disp_time,
                        UNIX_TIMESTAMP(last_reply) AS last_reply, name, user_name, subject, category, marked, text, rst.user_id AS req_user,
						" . $db_settings['akismet_rating_table'] . ".spam AS akismet_spam,
						" . $db_settings['b8_rating_table'] . ".spam AS b8_spam					
						FROM " . $db_settings['forum_table'] . " AS ft
                        LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id = ft.user_id
                        LEFT JOIN " . $db_settings['read_status_table'] . " AS rst ON rst.posting_id = ft.id AND rst.user_id = " . intval($tmp_user_id) . "
						LEFT JOIN " . $db_settings['akismet_rating_table'] . " ON " . $db_settings['akismet_rating_table'] . ".`eid` = `ft`.`id` 
						LEFT JOIN " . $db_settings['b8_rating_table'] . " ON " . $db_settings['b8_rating_table'] . ".`eid` = `ft`.`id` 
                        WHERE tid = " . $thread . $display_spam_query_and . " ORDER BY time ASC");
	
	if (!$result)
		raise_error('database_error', mysqli_error($connid));
	
	while ($data = mysqli_fetch_array($result)) {
		if ($data['user_id'] > 0) {
			if (!$data['user_name'])
				$data['name'] = $lang['unknown_user'];
			else
				$data['name'] = htmlspecialchars($data['user_name']);
		} else
			$data['name'] = htmlspecialchars($data['name']);
		$data['subject']       = htmlspecialchars($data['subject']);
		$data['formated_time'] = format_time($lang['time_format'], $data['disp_time']);
		
		// set read or new status of messages
		$data = getMessageStatus($data, $last_visit);
		
		if ($data['text'] == '')
			$data['no_text'] = true;
		unset($data['text']);
		
		if (isset($categories[$data['category']]) && $categories[$data['category']] != '')
			$data['category_name'] = $categories[$data["category"]];
		$data_array[$data['id']]     = $data;
		$child_array[$data['pid']][] = $data['id'];
		if ($data['pid'] == $id)
			$direct_replies[] = $data['id'];
		$last = $data['id'];
		if ($data['pid'] > $last)
			$last = $data['id'];
		$data['spam'] = $data['akismet_spam'] || $data['b8_spam'] ? 1 : 0;
	}
	if (isset($child_array)) {
		$smarty->assign('child_array', $child_array);
		get_thread_items($child_array, $entrydata['tid'], $entrydata['id']);
		$thread_items_count = count($thread_items);
		if ($thread_items_count > 1) {
			foreach ($thread_items as $key => $val) {
				if ($val == $entrydata['id'])
					$current_key = $key;
			}
			if ($entrydata['id'] != $thread_items[0])
				$smarty->assign('link_rel_first', 'index.php?id=' . $thread_items[0]);
			if (isset($current_key) && isset($thread_items[$current_key - 1]))
				$smarty->assign('link_rel_prev', 'index.php?id=' . $thread_items[$current_key - 1]);
			if (isset($current_key) && isset($thread_items[$current_key + 1]))
				$smarty->assign('link_rel_next', 'index.php?id=' . $thread_items[$current_key + 1]);
			if ($entrydata['id'] != $thread_items[$thread_items_count - 1])
				$smarty->assign('link_rel_last', 'index.php?id=' . $thread_items[$thread_items_count - 1]);
		}
	}
	
	mysqli_free_result($result);
	
	// tags:
	if (isset($entrydata['tags']) && $entrydata['tags']) {
		$tags_array = false;
		$i          = 0;
		foreach ($entrydata['tags'] as $tag) {
			if (my_strpos($tag, ' ', 0, $lang['charset']))
				$tag_escaped = '"' . $tag . '"';
			else
				$tag_escaped = $tag;
			
			$tags_array[$i]['escaped'] = urlencode($tag_escaped);
			$tags_array[$i]['display'] = htmlspecialchars($tag);
			$keywords[]                = htmlspecialchars($tag);
			$i++;
		}
		if (isset($tags_array))
			$smarty->assign('tags', $tags_array);
		if (isset($keywords))
			$smarty->assign('keywords', implode(', ', $keywords));
	}
	$category = $category;
	$smarty->assign('id', intval($entrydata['id']));
	$smarty->assign('tid', intval($entrydata['tid']));
	$smarty->assign('pid', intval($entrydata['pid']));
	$smarty->assign('posting_user_id', intval($entrydata['user_id']));
	$smarty->assign('page_title', htmlspecialchars($entrydata['subject']));
	$smarty->assign('subject', htmlspecialchars($entrydata['subject']));
	
	if ($entrydata['user_id'] > 0) {
		if (!$entrydata['user_name'])
			$name = $lang['unknown_user'];
		else
			$name = htmlspecialchars($entrydata['user_name']);
	} else
		$name = htmlspecialchars($entrydata['name']);
	
	$smarty->assign('name', $name);
	
	$smarty->assign('user_type', htmlspecialchars($entrydata['user_type']));
	$smarty->assign('disp_time', htmlspecialchars($entrydata['disp_time']));
	$smarty->assign('formated_time', htmlspecialchars($entrydata['formated_time']));
	$smarty->assign('locked', htmlspecialchars($entrydata['locked']));
	
	$ago['days']    = floor((TIMESTAMP - $entrydata['time']) / 86400);
	$ago['hours']   = floor(((TIMESTAMP - $entrydata['time']) / 3600) - ($ago['days'] * 24));
	$ago['minutes'] = floor(((TIMESTAMP - $entrydata['time']) / 60) - ($ago['hours'] * 60 + $ago['days'] * 1440));
	if ($ago['hours'] > 12)
		$ago['days_rounded'] = $ago['days'] + 1;
	else
		$ago['days_rounded'] = $ago['days'];
	$smarty->assign('ago', $ago);
	
	$authorization = get_edit_authorization($id, $entrydata['user_id'], $entrydata['edit_key'], $entrydata['time'], $entrydata['locked']);
	if ($authorization['edit'] == true)
		$options['edit'] = true;
	if ($authorization['delete'] == true)
		$options['delete'] = true;
	
	if (isset($direct_replies))
		$smarty->assign('direct_replies', $direct_replies);
	if ($settings['count_views'] == 1) {
		$views = $entrydata['views'] - 1; // this subtracts the first view by the author after posting
		if ($views < 0)
			$views = 0; // prevents negative number of views
		$smarty->assign('views', $entrydata['views']);
	}
	$smarty->assign('ip', $entrydata['ip']);
	if ($entrydata['akismet_spam'] == 1 || $entrydata['b8_spam'] == 1)
		$smarty->assign('spam', true);
	if (isset($categories[$entrydata["category"]]) && $categories[$entrydata['category']] != '') {
		$smarty->assign('category_name', $categories[$entrydata["category"]]);
	}
	
	if (empty($entrydata['email_contact']))
		$entrydata["email_contact"] = 0;
	if ($entrydata['hp'] != '') {
		$entrydata['hp'] = add_http_if_no_protocol($entrydata['hp']);
		$smarty->assign('hp', $entrydata["hp"]);
	}
	if ($entrydata['email'] != '' && $entrydata["email_contact"] == 1)
		$smarty->assign('email', true);
	if ($entrydata['location'] != '')
		$smarty->assign('location', htmlspecialchars($entrydata['location']));
	
	$subnav_link = array(
		'mode' => 'index',
		'name' => 'thread_entry_back_link',
		'title' => 'thread_entry_back_title'
	);
	
	// edited:
	if ($entrydata["edited_diff"] > 0 && $entrydata["edited_diff"] > $entrydata["time"] && $settings['show_if_edited'] == 1) {
		$smarty->assign('edited', true);
		$smarty->assign('edit_time', htmlspecialchars($entrydata['edit_time']));
		$entrydata['formated_edit_time'] = format_time($lang['time_format_full'], $entrydata['edit_time']);
		$smarty->assign('formated_edit_time', htmlspecialchars($entrydata['formated_edit_time']));
		
		if ($entrydata['user_id'] == $entrydata['edited_by'])
			$edited_by = $name;
		else {
			$result      = @mysqli_query($connid, "SELECT user_name FROM " . $db_settings['userdata_table'] . " WHERE user_id = " . intval($entrydata['edited_by']) . " LIMIT 1");
			$edited_data = mysqli_fetch_array($result);
			@mysqli_free_result($result);
			if (!$edited_data['user_name'])
				$edited_by = $lang['unknown_user'];
			else
				$edited_by = htmlspecialchars($edited_data['user_name']);
		}
		$smarty->assign('edited_by', $edited_by);
	}
	
	if (isset($entrydata['signature']) && $entrydata['signature'] != '' && $entrydata["show_signature"] == 1) {
		// user has a signature and wants it to be displayed in this posting. Check if it's already cached:
		if ($entrydata['cache_signature'] != '') {
			$smarty->assign('signature', $entrydata['cache_signature']);
		} else {
			$signature = signature_format($entrydata['signature']);
			// cache signature:
			list($row_count) = @mysqli_fetch_row(mysqli_query($connid, "SELECT COUNT(*) FROM " . $db_settings['userdata_cache_table'] . " WHERE cache_id=" . intval($entrydata['user_id'])));
			if ($row_count == 1) {
				mysqli_query($connid, "UPDATE " . $db_settings['userdata_cache_table'] . " SET cache_signature='" . mysqli_real_escape_string($connid, $signature) . "' WHERE cache_id=" . intval($entrydata['user_id']));
			} else {
				@mysqli_query($connid, "DELETE FROM " . $db_settings['userdata_cache_table'] . " WHERE cache_id=" . intval($entrydata['user_id']));
				@mysqli_query($connid, "INSERT INTO " . $db_settings['userdata_cache_table'] . " (cache_id, cache_signature, cache_profile) VALUES (" . intval($entrydata['user_id']) . ",'" . mysqli_real_escape_string($connid, $signature) . "','')");
			}
			$smarty->assign('signature', $signature);
		}
	}
	
	if (isset($branch))
		$smarty->assign('branch', $branch);
	if (isset($data_array))
		$smarty->assign("data", $data_array);
	$smarty->assign('posting', $ftext);
	$smarty->assign('subnav_link', $subnav_link);
	$smarty->assign('page', $page);
	$smarty->assign('order', $order);
	$smarty->assign('category', $category);
	if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0) {
		$options['move'] = true;
		$options['lock'] = true;
	}
	if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
		if (isset($entrydata['bookmarkedby']))
			$options['delete_bookmark'] = true;
		else
			$options['add_bookmark'] = true;
	}
	if (isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0) {
		if (($settings['akismet_key'] != '' && $settings['akismet_entry_check'] == 1 && $entrydata['akismet_spam'] == 0 && $entrydata['spam_check_status'] > 0) || ($settings['b8_entry_check'] == 1 && $entrydata['b8_spam'] == 0 || $entrydata['training_type'] == 0))
			$options['report_spam'] = true;
		if (($settings['akismet_key'] != '' && $settings['akismet_entry_check'] == 1 && $entrydata['akismet_spam'] == 1 && $entrydata['spam_check_status'] > 0) || ($settings['b8_entry_check'] == 1 && $entrydata['b8_spam'] == 1 || $entrydata['training_type'] == 0))
			$options['flag_ham'] = true;
	}

	if (isset($options))
		$smarty->assign('options', $options);
	$smarty->assign('subtemplate', 'entry.inc.tpl');
	$template = 'main.tpl';
?>