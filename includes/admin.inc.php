<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

if (isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type'] == 2) {
	// remove not activated user accounts:
	@mysqli_query($connid, "DELETE FROM ".$db_settings['userdata_table']." WHERE registered < (NOW() - INTERVAL 24 HOUR) AND activate_code != '' AND logins = 0");

	unset($errors);
	if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
	if (isset($_GET['delete_page'])) $action = 'delete_page';
	if (isset($_GET['edit_page'])) $action = 'edit_page';
	if (isset($_GET['move_up_page']) || isset($_GET['move_down_page'])) $action = 'move_page';
	if (isset($_GET['move_up_smiley']) || isset($_GET['move_down_smiley'])) $action = 'move_smiley';
	if (isset($_GET['move_up_category']) || isset($_GET['move_down_category'])) $action = 'move_category';
	
	if (isset($_POST['delete_page_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'])
		$action = 'delete_page_submit';
	
	if (isset($_POST['edit_page_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'])
		$action = 'edit_page_submit';

	if (isset($_POST['new_category']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		$new_category = trim($_POST['new_category']);
		$new_category = str_replace('"', '\'', $new_category);
		$accession = intval($_POST['accession']);
		if ($new_category != '') {
			// does this category already exist?
			$result = mysqli_query($connid, "SELECT category FROM ".$db_settings['category_table']." WHERE lower(category) = '". mysqli_real_escape_string($connid, my_strtolower($new_category, $lang['charset']))."'") or raise_error('database_error', mysqli_error($connid));
			if (mysqli_num_rows($result) > 0) $errors[] = 'category_already_exists';
			mysqli_free_result($result);

			if (empty($errors)) {
				$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['category_table']);
				list($category_count) = mysqli_fetch_row($count_result);
				mysqli_free_result($count_result);
				@mysqli_query($connid, "INSERT INTO ".$db_settings['category_table']." (order_id, category, description, accession) VALUES (". $category_count ."+1, '". mysqli_real_escape_string($connid, $new_category) ."', '', ". $accession .")") or die(mysqli_error($connid));
				header("location: index.php?mode=admin&action=categories");
				exit();
			} else {
				$smarty->assign('errors',$errors);
			}
		}
		$action='categories';
	}

	if (isset($_GET['edit_user'])) {
		$edit_user_id = intval($_GET['edit_user']);
		$result = mysqli_query($connid, "SELECT user_type, user_name, user_real_name, user_email, email_contact, user_hp, user_location, signature, profile, user_view, new_posting_notification, new_user_notification, gender, birthday, activate_code, language, time_zone, time_difference, theme FROM ".$db_settings['userdata_table']." WHERE user_id = '". $edit_user_id ."'") or raise_error('database_error', mysqli_error($connid));
		$field = mysqli_fetch_array($result);
		mysqli_free_result($result);

		if (trim($field['birthday']) == '' || $field['birthday'] == '0000-00-00') $user_birthday = '';
		else {
			$year = my_substr($field['birthday'], 0, 4, $lang['charset']);
			$month = my_substr($field['birthday'], 5, 2, $lang['charset']);
			$day = my_substr($field['birthday'], 8, 2, $lang['charset']);
			$user_birthday = $year.'-'.$month.'-'.$day;
		}
		// timezones:
		if (function_exists('date_default_timezone_set') && $time_zones = get_timezones()) {
			$smarty->assign('user_time_zone', htmlspecialchars($field['time_zone']));
			$smarty->assign('time_zones', $time_zones);
		}
		if ($field['time_difference'] < 0) $time_difference_hours = ceil($field['time_difference'] / 60);
		else $time_difference_hours = floor($field['time_difference'] / 60);
		$time_difference_minutes = abs($field['time_difference'] - $time_difference_hours * 60);
		if ($time_difference_minutes < 10) $time_difference_minutes = '0'.$time_difference_minutes;
		if (intval($field['time_difference']) > 0) $user_time_difference = '+'.$time_difference_hours;
		else $user_time_difference = $time_difference_hours;
		if ($time_difference_minutes > 0) $user_time_difference .= ':'.$time_difference_minutes;
		$smarty->assign('user_time_difference', $user_time_difference);
		$languages = get_languages(true);
		if (isset($languages) && count($languages) > 1) {
			$smarty->assign('user_language', htmlspecialchars($field['language']));
			$smarty->assign('languages', $languages);
		}
		$themes = get_themes(true);
		if (isset($themes) && count($themes) > 1) {
			$smarty->assign('user_theme', htmlspecialchars($field['theme']));
			$smarty->assign('themes', $themes);
		}

		$smarty->assign('edit_user_id',  intval($edit_user_id));
		$smarty->assign('edit_user_name', htmlspecialchars($field["user_name"]));
		$smarty->assign('edit_user_type', intval($field["user_type"]));
		$smarty->assign('user_email', htmlspecialchars($field["user_email"]));
		$smarty->assign('email_contact',  intval($field["email_contact"]));
		$smarty->assign('user_real_name', htmlspecialchars($field["user_real_name"]));
		$smarty->assign('user_gender', intval($field['gender']));
		$smarty->assign('user_birthday', htmlspecialchars($user_birthday));
		$smarty->assign('user_hp', htmlspecialchars($field["user_hp"]));
		$smarty->assign('user_location', htmlspecialchars($field["user_location"]));
		$smarty->assign('profile', htmlspecialchars($field["profile"]));
		$smarty->assign('signature', htmlspecialchars($field["signature"]));
		$smarty->assign('user_view', intval($field["user_view"]));
		$smarty->assign('new_posting_notification', intval($field["new_posting_notification"]));
		$smarty->assign('new_user_notification', intval($field["new_user_notification"]));
		if (trim($field['activate_code']) != '') $smarty->assign('inactive', true);

		$avatarInfo = getAvatar($edit_user_id);
		$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
		if (isset($avatar) && $avatar['image'] !== false) {
			$image_info = getimagesize($avatar['image']);
			$avatar['width'] = $image_info[0];
			$avatar['height'] = $image_info[1];
			$smarty->assign('avatar', $avatar);
		}
		$action = 'edit_user';
	}

	if (isset($_POST['edit_user_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		// import posted data:
		$edit_user_id = intval($_POST['edit_user_id']);
		$edit_user_name = trim($_POST['edit_user_name']);
		$edit_user_type = intval($_POST['edit_user_type']);
		$user_email = trim($_POST['user_email']);
		$email_contact = (isset($_POST['email_contact'])) ? 1 : 0;
		$user_real_name = trim($_POST['user_real_name']);
		$user_birthday = trim($_POST['user_birthday']);
		$gender = (isset($_POST['user_gender'])) ? intval($_POST['user_gender']) : 0;
		if ($gender !=0 && $gender !=1 && $gender !=2) $gender = 0;
		$user_hp = trim($_POST['user_hp']);
		$user_location = trim($_POST['user_location']);
		$profile = trim($_POST['profile']);
		$signature = trim($_POST['signature']);
		if (isset($_POST['user_view'])) $user_view = intval($_POST['user_view']);
		else $user_view = $settings['default_view'];
		$user_time_difference = trim($_POST['user_time_difference']);
		if (isset($_POST['new_posting_notification'])) $new_posting_notification = trim($_POST['new_posting_notification']);
		else $new_posting_notification = 0;
		if (isset($_POST['new_user_notification'])) $new_user_notification = trim($_POST['new_user_notification']);
		else $new_user_notification = 0;

		// time zone:
		$user_time_zone = '';
		if (isset($_POST['user_time_zone']) && $_POST['user_time_zone'] != '' && function_exists('date_default_timezone_set') && $time_zones = get_timezones()) {
		if (in_array($_POST['user_time_zone'], $time_zones)) $user_time_zone = $_POST['user_time_zone'];
		}

		// time difference:
		$time_difference = isset($_POST['user_time_difference']) ? trim($_POST['user_time_difference']) : '';
		if (isset($time_difference[0]) && $time_difference[0] == '-') $negative = true;
		$time_difference_array = explode(':', $_POST['user_time_difference']);
		$hours_difference = abs(intval($time_difference_array[0]));
		if ($hours_difference <- 24 || $hours_difference > 24) $hours_difference = 0;
		if (isset($time_difference_array[1])) $minutes_difference = intval($time_difference_array[1]);
		if (isset($minutes_difference)) {
			if ($minutes_difference < 0 || $minutes_difference > 59) $minutes_difference = 0;
		} else {
			$minutes_difference = 0;
		}
		if (isset($negative)) {
			$time_difference = 0 - ($hours_difference * 60 + $minutes_difference);
		}
		else $time_difference = $hours_difference * 60 + $minutes_difference;

		// language:
		$user_language = '';
		if (isset($_POST['user_language']) && trim($_POST['user_language']) != '') {
			$languages = get_languages();
			if (isset($languages) && count($languages) > 1) {
				if (in_array($_POST['user_language'], $languages)) {
					$user_language = $_POST['user_language'];
				}
			}
		}

		// theme:
		$user_theme = '';
		if(isset($_POST['user_theme']) && trim($_POST['user_theme']) != '') {
			$themes = get_themes();
			if (isset($themes) && count($themes) > 1) {
				if (in_array($_POST['user_theme'], $themes)) {
				$user_theme = $_POST['user_theme'];
				}
			}
		}
		
		$avatarInfo = getAvatar($edit_user_id);
		$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
		if (isset($avatar) && $avatar['image'] !== false) {
			$image_info = getimagesize($avatar['image']);
			$avatar['width'] = $image_info[0];
			$avatar['height'] = $image_info[1];
			$smarty->assign('avatar', $avatar);
		}

		// old user name:
		$oun_result = @mysqli_query($connid, "SELECT user_name FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($edit_user_id). " LIMIT 1") or raise_error('database_error', mysqli_error($connid));
		$oun_data = mysqli_fetch_array($oun_result);
		mysqli_free_result($oun_result);
		$old_user_name = $oun_data['user_name'];

		// check data:
		if ($edit_user_name == '') $errors[] = 'user_name_empty';

		// does the name already exist?
		$name_result = @mysqli_query($connid, "SELECT user_name FROM ".$db_settings['userdata_table']." WHERE user_id != ". intval($edit_user_id) ." AND lower(user_name) = '". mysqli_real_escape_string($connid, my_strtolower($edit_user_name, $lang['charset'])) ."'") or raise_error('database_error', mysqli_error($connid));
		if (mysqli_num_rows($name_result) > 0) $errors[] = 'user_name_already_exists';
		mysqli_free_result($name_result);

		if (my_strlen($edit_user_name, $lang['charset']) > $settings['username_maxlength']) $errors[] = 'error_username_too_long';
		if (my_strlen($user_real_name, $lang['charset']) > $settings['name_maxlength']) $errors[] = 'error_name_too_long';
		if (my_strlen($user_hp, $lang['charset']) > $settings['hp_maxlength']) $errors[] = 'error_hp_too_long';
		if (my_strlen($user_location, $lang['charset']) > $settings['location_maxlength']) $errors[] = 'error_location_too_long';
		$smarty->assign('profil_length', my_strlen($profile, $lang['charset']));
		if (my_strlen($profile, $lang['charset']) > $settings['profile_maxlength']) $errors[] = 'error_profile_too_long';
		$smarty->assign('signature_length', my_strlen($signature, $lang['charset']));
		if (my_strlen($signature, $lang['charset']) > $settings['signature_maxlength']) $errors[] = 'error_signature_too_long';

		// birthday check:
		if ($user_birthday != '') {
			if (is_valid_birthday($user_birthday)) {
				$year = intval(my_substr($user_birthday, 0, 4, $lang['charset']));
				$month = intval(my_substr($user_birthday, 5, 2, $lang['charset']));
				$day = intval(my_substr($user_birthday, 8, 2, $lang['charset']));
				$birthday = $year.'-'.$month.'-'.$day;
			}
			else $errors[] = 'error_invalid_date';
		}
		else $birthday = NULL;

		// check for too long words:
		$too_long_word = too_long_word($edit_user_name, $settings['name_word_maxlength']);
		if ($too_long_word) $errors[] = 'error_word_too_long';
		if (empty($too_long_word)) {
			$too_long_word = too_long_word($user_location, $settings['location_word_maxlength']);
			if ($too_long_word) $errors[] = 'error_word_too_long';
		}

		$profile_check = html_format($profile);
		$profile_check = strip_tags($profile_check);
		if (empty($too_long_word)) {
			$too_long_word = too_long_word($profile_check, $settings['text_word_maxlength']);
			if ($too_long_word) $errors[] = 'error_word_too_long';
		}

		$signature_check = signature_format($signature);
		$signature_check = strip_tags($signature_check);
		if (empty($too_long_word)) {
			$too_long_word = too_long_word($signature_check, $settings['text_word_maxlength']);
			if ($too_long_word) $errors[] = 'error_word_too_long';
		}
		// end of checking

		// save if no errors:
		if(empty($errors)) {
			$queryUserDataEdit  = "UPDATE ".$db_settings['userdata_table']." SET user_name='". mysqli_real_escape_string($connid, $edit_user_name) ."', user_type='". intval($edit_user_type) ."', user_email='". mysqli_real_escape_string($connid, $user_email) ."', user_real_name='". mysqli_real_escape_string($connid, $user_real_name) ."', gender=". intval($gender) .", birthday= ";
			$queryUserDataEdit .=  ($birthday !== NULL) ? "'". mysqli_real_escape_string($connid, $birthday) ."'" : "NULL";
			$queryUserDataEdit .= ", email_contact=". intval($email_contact) .", user_hp='". mysqli_real_escape_string($connid, $user_hp) ."', user_location='". mysqli_real_escape_string($connid, $user_location) ."', profile='". mysqli_real_escape_string($connid, $profile) ."', signature='". mysqli_real_escape_string($connid, $signature) ."', last_login=last_login, registered=registered, new_posting_notification=". intval($new_posting_notification) .", new_user_notification=". intval($new_user_notification) .", language='". mysqli_real_escape_string($connid, $user_language) ."', time_zone='". mysqli_real_escape_string($connid, $user_time_zone) ."', time_difference=". intval($time_difference) .", theme='". mysqli_real_escape_string($connid, $user_theme) ."' WHERE user_id=". $edit_user_id;
			@mysqli_query($connid, $queryUserDataEdit) or raise_error('database_error', mysqli_error($connid));
			@mysqli_query($connid, "DELETE FROM ".$db_settings['userdata_cache_table']." WHERE cache_id = ". $edit_user_id);
			
			if (isset($_POST['delete_avatar'])) {
				$avatarInfo = getAvatar($edit_user_id);
				$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
				if (isset($avatar) && $avatar['image'] !== false && file_exists($avatar['image'])) {
					@chmod($avatar['image'], 0777);
					@unlink($avatar['image']);
				}
			}

			header("location: index.php?mode=admin&action=user");
			exit;
		} else {
			$smarty->assign('errors', $errors);
			// timezones:
			if (function_exists('date_default_timezone_set') && $time_zones = get_timezones()) {
				$smarty->assign('time_zones', $time_zones);
				$smarty->assign('user_time_zone', htmlspecialchars($user_time_zone));
			}
			// languages:
			$languages = get_languages(true);
			if( isset($languages) && count($languages) > 1) {
				$smarty->assign('languages', $languages);
				$smarty->assign('user_language', htmlspecialchars($user_language));
			}
			// themes:
			$themes = get_themes(true);
			if(isset($themes) && count($themes) > 1) {
				$smarty->assign('themes', $themes);
				$smarty->assign('user_theme', htmlspecialchars($user_theme));
			}
			if (isset($too_long_word)) $smarty->assign('word', $too_long_word);
			$smarty->assign('edit_user_id', intval($_POST['edit_user_id']));
			$smarty->assign('edit_user_name', htmlspecialchars($_POST['edit_user_name']));
			$smarty->assign('edit_user_type', intval($_POST['edit_user_type']));
			$smarty->assign('user_email', htmlspecialchars($_POST['user_email']));
			$smarty->assign('email_contact', intval($email_contact));
			$smarty->assign('user_real_name', htmlspecialchars($_POST['user_real_name']));
			if (isset($_POST['user_gender'])) $smarty->assign('user_gender', intval($_POST['user_gender']));
			$smarty->assign('user_birthday', htmlspecialchars($_POST['user_birthday']));
			$smarty->assign('user_hp', htmlspecialchars($_POST['user_hp']));
			$smarty->assign('user_location', htmlspecialchars($_POST['user_location']));
			$smarty->assign('profile', htmlspecialchars($_POST['profile']));
			$smarty->assign('signature', htmlspecialchars($_POST['signature']));
			if (isset($_POST['user_view'])) $smarty->assign('user_view', intval($_POST['user_view']));
			if (isset($_POST['user_time_difference'])) $smarty->assign('user_time_difference', htmlspecialchars($_POST['user_time_difference']));
			if (isset($_POST['new_posting_notification'])) $smarty->assign('new_posting_notification', intval($_POST['new_posting_notification']));
			if (isset($_POST['new_user_notification'])) $smarty->assign('new_user_notification', intval($_POST['new_user_notification']));
			if (isset($_POST['delete_avatar'])) $smarty->assign('delete_avatar', 1);
		}
	$action = 'edit_user';
	}

	if (isset($_GET['edit_category'])) {
		$category_result = mysqli_query($connid, "SELECT id, order_id, category, accession FROM ".$db_settings['category_table']." WHERE id = ". intval($_GET['edit_category']). " LIMIT 1");
		if (!$category_result) raise_error('database_error', mysqli_error($connid));
		$field = mysqli_fetch_array($category_result);
		mysqli_free_result($category_result);
		$smarty->assign('category_id', $field['id']);
		$smarty->assign('edit_category', htmlspecialchars($field['category']));
		$smarty->assign('edit_accession', $field['accession']);
		$action = "edit_category";
	}

	if (isset($_GET['delete_category'])) {
		$category_result = mysqli_query($connid, "SELECT id, category FROM ".$db_settings['category_table']." WHERE id = ". intval($_GET['delete_category'])." LIMIT 1");
		if (!$category_result) raise_error('database_error', mysqli_error($connid));
		$field = mysqli_fetch_array($category_result);
		mysqli_free_result($category_result);
		$smarty->assign('category_id', $field['id']);
		$smarty->assign('category_name', $field['category']);

		if (count($categories) > 1) {
			foreach ($categories as $key => $val) {
				if ($key != $field['id']) $move_categories[$key] = $val;
			}
			$smarty->assign('move_categories', $move_categories);
		}
		$smarty->assign('categories_count', count($categories));
		$breadcrumbs[0]['link'] = 'index.php?mode=admin';
		$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
		$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=categories';
		$breadcrumbs[1]['linkname'] = 'subnav_categories';
		$smarty->assign('breadcrumbs', $breadcrumbs);
		$smarty->assign('subnav_location', 'subnav_delete_category');
		$action='delete_category';
	}

	if (isset($_POST['edit_category_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		$id = intval($_POST['id']);
		$category = trim($_POST['category']);
		$category = str_replace('"', '\'', $category);
		$accession = intval($_POST['accession']);
		// does this category already exist?
		$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['category_table']." WHERE category LIKE '". mysqli_real_escape_string($connid, $category) ."' AND id != ". $id);
		if (!$count_result) raise_error('database_error', mysqli_error($connid));
		list($category_count) = mysqli_fetch_row($count_result);
		mysqli_free_result($count_result);

		if ($category_count > 0) { $errors[] = 'category_already_exists'; }

		if (empty($errors)) {
			mysqli_query($connid, "UPDATE ".$db_settings['category_table']." SET category='". mysqli_real_escape_string($connid, $category) ."', accession=". $accession ." WHERE id=". $id);
			header("location: index.php?mode=admin&action=categories");
			die();
		} else {
			$smarty->assign('category_id', intval($_POST['id']));
			$smarty->assign('edit_category', htmlspecialchars($_POST['category']));
			$smarty->assign('edit_accession', intval($_POST['accession']));
			$smarty->assign('errors', $errors);
		}
		$action='edit_category';
	}

	if (isset($_POST['entries_in_not_existing_categories_submit'])) {
		if ($_POST['entry_action'] == "delete") {
			if (isset($category_ids_query)) {
				mysqli_query($connid, "DELETE FROM ".$db_settings['forum_table']." WHERE category NOT IN (". $category_ids_query .")");
			} else {
				mysqli_query($connid, "DELETE FROM ".$db_settings['forum_table']." WHERE category != 0");
			}
		} else {
			if (isset($category_ids_query)) {
				mysqli_query($connid, "UPDATE ".$db_settings['forum_table']." SET time = time, last_reply = last_reply, category = ". intval($_POST['move_category']) ." WHERE category NOT IN (". $category_ids_query .")");
			} else {
				mysqli_query($connid, "UPDATE ".$db_settings['forum_table']." SET time = time, last_reply = last_reply, category = ". intval($_POST['move_category']) ." WHERE category != 0");
			}
		}
		header("location: index.php?mode=admin&action=categories");
		exit;
	}

	if (isset($_POST['delete_category_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		$category_id = intval($_POST['category_id']);
		if ($category_id > 0) {
			// delete category from category table:
			mysqli_query($connid, "DELETE FROM ".$db_settings['category_table']." WHERE id=". $category_id);

			// reset order:
			$result = mysqli_query($connid, "SELECT id FROM ".$db_settings['category_table']." ORDER BY order_id ASC");
			$i = 1;
			while ($data = mysqli_fetch_array($result)) {
				mysqli_query($connid, "UPDATE ".$db_settings['category_table']." SET order_id=". $i ." WHERE id = ". intval($data['id']));
				$i++;
			}
			mysqli_free_result($result);

			// what to to with the entries of deleted category:
			if ($_POST['delete_mode'] == "complete") {
				mysqli_query($connid, "DELETE FROM ".$db_settings['forum_table']." WHERE category = ". $category_id);
			} else {
				mysqli_query($connid, "UPDATE ".$db_settings['forum_table']." SET time = time, last_reply = last_reply, category = ". intval($_POST['move_category']) ." WHERE category = ". $category_id);
			}
			header("location: index.php?mode=admin&action=categories");
			die();
		}
		$action = 'categories';
	}

	if (isset($_GET['delete_user'])) {
		$user_id = intval($_GET['delete_user']);
		$user_result = mysqli_query($connid, "SELECT user_name FROM ".$db_settings['userdata_table']." WHERE user_id = ". $user_id ." LIMIT 1");
		if (!$user_result) raise_error('database_error', mysqli_error($connid));
		$user = mysqli_fetch_array($user_result);
		mysqli_free_result($user_result);
		$selected_users[0]['id'] = $user_id;
		$selected_users[0]['name'] = htmlspecialchars($user["user_name"]);
		$selected_users_count = 1;
		$action = 'delete_users';
	}

	if (isset($_POST['delete_selected_users'])) {
		if (isset($_POST['selected'])) {
			$selected = $_POST['selected'];
			$selected_users_count = count($selected);
			for ($x=0; $x<$selected_users_count; $x++) {
				$user_result = mysqli_query($connid, "SELECT user_name FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($selected[$x]) ." LIMIT 1");
				if (!$user_result) raise_error('database_error', mysqli_error($connid));
				$user = mysqli_fetch_array($user_result);
				mysqli_free_result($user_result);
				$selected_users[$x]['id'] = $selected[$x];
				$selected_users[$x]['name'] = htmlspecialchars($user["user_name"]);
			}
			$action = 'delete_users';
		}
		else $action = 'user';
	}

	if (isset($_POST['user_delete_entries']) && isset($_POST['delete_confirmed']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		$user_delete_entries = intval($_POST['user_delete_entries']);
		$result = mysqli_query($connid, "SELECT id FROM ".$db_settings['forum_table']." WHERE user_id = ". $user_delete_entries) or raise_error('database_error', mysqli_error($connid));
		while ($data = mysqli_fetch_array($result)) {
			delete_posting_recursive($data['id']);
		}
		mysqli_free_result($result);
		header('Location: index.php?mode=user&show_user='.$user_delete_entries);
		exit;
	}

	if (isset($_GET['user_delete_all_entries'])) {
		$user_id = intval($_GET['user_delete_all_entries']);
		$user_result = mysqli_query($connid, "SELECT user_name FROM ".$db_settings['userdata_table']." WHERE user_id = ". $user_id ." LIMIT 1");
		if (!$user_result) raise_error('database_error', mysqli_error($connid));
		if (mysqli_num_rows($user_result) == 1) {
			$user = mysqli_fetch_array($user_result);
			mysqli_free_result($user_result);
			$user_delete_entries['id'] = $user_id;
			$user_delete_entries['user'] = htmlspecialchars($user['user_name']);
			$action = 'user_delete_entries';
		}
	}

	if (isset($_GET['download_backup_file'])) {
		$file = 'backup/'.$_GET['download_backup_file'];
		if (check_filename($_GET['download_backup_file']) && file_exists($file)) {
			$len = filesize($file);
			$fh = @fopen($file, "r");
			if (!$fh) return false;
			$data = fread($fh, $len);
			fclose($fh);
			header("Content-Type: text/plain; charset=".$lang['charset']);
			header("Content-Disposition: attachment; filename=".$_GET['download_backup_file']);
			header("Accept-Ranges: bytes");
			header("Content-Length: ".$len);
			echo $data;
			exit;
		} else {
			$errors[] = 'error_file_doesnt_exist';
			$smarty->assign('errors', $errors);
			$action = 'backup';
		}
	}

	if (isset($_POST['delete_selected_backup_files']) && empty($_POST['delete_backup_files'])) $action = 'backup';

	if (isset($_REQUEST['delete_backup_files'])) {
		if (empty($_REQUEST['delete_backup_files_confirm'])) {
			$action = 'delete_backup_files_confirm';
		} else {
			foreach ($_REQUEST['delete_backup_files'] as $backup_file) {
				if (!@unlink('backup/'.$backup_file)) $error = true;
			}
			if (isset($error)) {
				$errors[] = 'error_delete_backup_file';
				$smarty->assign('errors', $errors);
				$action = 'backup';
			} else {
				header('Location: index.php?mode=admin&action=backup');
				exit;
			}
		}
	}

	if (isset($_GET['restore'])) {
		$file = 'backup/'.$_GET['restore'];
		if (check_filename($_GET['restore']) && file_exists($file)) {
			$backup_file = $_GET['restore'];
			if (ini_get('safe_mode')) $smarty->assign('safe_mode_warning', true);
			$action = 'restore';
		} else {
			$errors[] = 'error_file_doesnt_exist';
			$smarty->assign('errors', $errors);
			$action = 'backup';
		}
	}

	if (isset($_REQUEST['restore_submit'])) {
		if ($_POST['backup_file'] == '' || !file_exists('backup/'.$_POST['backup_file']) || !check_filename($_POST['backup_file'])) $errors[] = 'error_file_doesnt_exist';
		if (empty($errors)) {
			if (empty($_POST['restore_password']) || $_POST['restore_password'] == '') $errors[] = 'error_password_wrong';
			if (empty($errors)) {
				$result = mysqli_query($connid, "SELECT user_pw FROM ".$db_settings['userdata_table']." WHERE user_id=". intval($_SESSION[$settings['session_prefix'].'user_id']) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				if (mysqli_num_rows($result) != 1) raise_error('database_error', mysqli_error($connid));
				$data = mysqli_fetch_array($result);
				if (!is_pw_correct($_POST['restore_password'], $data['user_pw'])) $errors[] = 'error_password_wrong';
				if (empty($errors)) {
					if (!restore_backup('backup/'.$_POST['backup_file'])) {
						$errors[] = 'error_restore_mysql';
						$smarty->assign('mysql_error',$error_message);
					}
					if(empty($errors)) {
						header('Location: index.php?mode=admin&action=backup&msg=restore_backup_ok');
						exit;
					}
				}
			}
		}
		if (isset($errors)) {
			$backup_file = $_POST['backup_file'];
			$smarty->assign('errors', $errors);
			$action = 'restore';
		}
	}

	if (isset($_GET['create_backup'])) {
		$mode = intval($_GET['create_backup']);
		if (!create_backup_file($mode)) $errors[] = 'error_create_backup_file';
		if (empty($errors)) {
			header('Location: index.php?mode=admin&action=backup&msg=backup_file_created');
			exit;
		}
		else $smarty->assign('errors', $errors);
		$action = 'backup';
	}

	if (isset($_GET['run_update'])) {
		$file = 'update/'.$_GET['run_update'];
		if(check_filename($_GET['run_update']) && file_exists($file)) {
			$update_file = $_GET['run_update'];
			$action = 'run_update';
		} else {
			$errors[] = 'error_file_doesnt_exist';
			$smarty->assign('errors', $errors);
			$action = 'update';
		}
	}

	if (isset($_POST['update_file_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		if ($_POST['update_file_submit'] == '' || !file_exists('update/'.$_POST['update_file_submit']) || !check_filename($_POST['update_file_submit'])) {
			$errors[] = 'error_file_doesnt_exist';
			$smarty->assign('errors', $errors);
			$action = 'update';
		}
		if (empty($errors)) {
			if (empty($_POST['update_password']) || $_POST['update_password'] == '') $errors[] = 'error_password_wrong';
			if (empty($errors)) {
				$result = mysqli_query($connid, "SELECT user_pw FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id']) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				if (mysqli_num_rows($result) != 1) raise_error('database_error', mysqli_error($connid));
				$data = mysqli_fetch_array($result);
				if (!is_pw_correct($_POST['update_password'], $data['user_pw'])) $errors[] = 'error_password_wrong';
				if (empty($errors)) {
					include('update/'.$_POST['update_file_submit']);
					$action = 'update_done';
					$breadcrumbs[0]['link'] = 'index.php?mode=admin';
					$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
					$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=update';
					$breadcrumbs[1]['linkname'] = 'subnav_update';
					$smarty->assign('breadcrumbs', $breadcrumbs);
					$smarty->assign('subnav_location', 'subnav_update_run');

					if (isset($update['errors'])) $smarty->assign('update_errors', $update['errors']);
					else {
						if (isset($update['items'])) {
							$i = 0;
							foreach ($update['items'] as $item) {
								if (my_substr($item, -1, 1, $lang['charset']) == '/') {
									$items[$i]['type'] = 0;
									$items[$i]['name'] = my_substr($item, 0, -1, $lang['charset']);
								} else {
									$items[$i]['type'] = 1;
									$items[$i]['name'] = $item;
								}
								$i++;
							}
							$smarty->assign('update_items', $items);
						}
						if (isset($update['download_url'])) $smarty->assign('update_download_url', $update['download_url']);
						if (isset($update['message'])) $smarty->assign('update_message', $update['message']);
						if (isset($update['new_version'])) $smarty->assign('update_new_version', $update['new_version']);
					}
				}
			}
			if(isset($errors)) {
				$update_file = $_POST['update_file_submit'];
				$smarty->assign('errors', $errors);
				$action = 'run_update';
			}
		}
	}

	if (isset($_POST['clear_userdata']) && isset($_POST['logins']) && isset($_POST['days']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		$logins = intval($_POST['logins']);
		$days = intval($_POST['days']);

		$result = @mysqli_query($connid, "SELECT user_id, user_name FROM ".$db_settings['userdata_table']." WHERE user_type != 1 AND user_type != 2 AND logins <= ". $logins ." AND last_login < (NOW()-INTERVAL ". $days ." DAY) AND registered < (NOW()-INTERVAL ". $days ." DAY) ORDER BY user_name") or raise_error('database_error', mysqli_error($connid));
		$i = 0;
		while ($line = mysqli_fetch_array($result)) {
			$selected_users[$i]['id'] = $line['user_id'];
			$selected_users[$i]['name'] = $line['user_name'];
			$i++;
		}
		mysqli_free_result($result);
		if (isset($selected_users)) {
			$selected_users_count = count($selected_users);
			$action = "delete_users";
		} else {
			$smarty->assign('no_users_in_selection', true);
			$action = 'clear_userdata';
		}
	}

	if (isset($_POST['email_list'])) $action = "email_list";

	if (isset($_POST['delete_confirmed']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		if (isset($_POST['selected_confirmed'])) {
			$selected_confirmed = $_POST['selected_confirmed'];
			for ($x=0; $x<count($selected_confirmed); $x++) {
				if ($_SESSION[$settings['session_prefix'].'user_id'] != $selected_confirmed[$x]) {
					// get user name:
					$delete_result = @mysqli_query($connid, "SELECT user_name FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($selected_confirmed[$x]) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
					if (mysqli_num_rows($delete_result) == 1) {
						unset($user_name);
						list($user_name) = @mysqli_fetch_array($delete_result);

						// save user name in forum table (like unregistered users):
						@mysqli_query($connid, "UPDATE ".$db_settings['forum_table']." SET time = time, last_reply = last_reply, name = '". mysqli_real_escape_string($connid, $user_name) ."' WHERE user_id = ". intval($selected_confirmed[$x]));

						// set "edited_by" in own edited postings to 0:
						@mysqli_query($connid, "UPDATE ".$db_settings['forum_table']." SET time = time, last_reply = last_reply, edited_by = 0 WHERE edited_by = ". intval($selected_confirmed[$x]) ." AND user_id = ". intval($selected_confirmed[$x]));

						// set user_id = 0
						@mysqli_query($connid, "UPDATE ".$db_settings['forum_table']." SET time = time, last_reply = last_reply, user_id = 0 WHERE user_id = ". intval($selected_confirmed[$x]));

						@mysqli_query($connid, "DELETE FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($selected_confirmed[$x]));
						@mysqli_query($connid, "DELETE FROM ".$db_settings['userdata_cache_table']." WHERE cache_id = ". intval($selected_confirmed[$x]));
						@mysqli_query($connid, "DELETE FROM ".$db_settings['bookmark_table']." WHERE user_id = ". intval($selected_confirmed[$x]));
						@mysqli_query($connid, "DELETE FROM ".$db_settings['read_status_table']." WHERE user_id = ". intval($selected_confirmed[$x]));
					}
					@mysqli_free_result($delete_result);

					// delete avatar:
					$avatarInfo = getAvatar(intval($selected_confirmed[$x]));
					$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
					if (isset($avatar) && $avatar['image'] !== false && file_exists($avatar['image'])) {
						@chmod($avatar['image'], 0777);
						@unlink($avatar['image']);
					}
				}
			}
		}
		header('Location: index.php?mode=admin&action=user');
		exit;
	}

	if (isset($_GET['user_lock'])) {
		$page = intval($_GET['page']);
		if ($page < 1) $page = 1;
		$order = $_GET['order'];
		if ($order != 'user_id' && $order != 'user_name' && $order != 'user_email' && $order != 'user_type' && $order != 'registered' && $order != 'logins' && $order != 'last_login' && $order != 'user_lock' && $order != 'activate_code') $order = 'user_id';
		$descasc = $_GET['descasc'];
		if ($descasc != 'DESC' && $descasc != 'ASC') $descasc = 'ASC';
		if (isset($_GET['search_user'])) $search_user_q = '&search_user = '. urlencode($_GET['search_user']);
		else $search_user_q = '';

		$lock_result = mysqli_query($connid, "SELECT user_type, user_lock FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_GET['user_lock']) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
		$field = mysqli_fetch_array($lock_result);
		mysqli_free_result($lock_result);

		if ($field['user_type'] == 0) {
			if ($field['user_lock'] == 0) $new_lock = 1; else $new_lock = 0;
			$update_result = mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET user_lock = '". $new_lock ."', last_login = last_login, registered = registered WHERE user_id = ". intval($_GET['user_lock'])." LIMIT 1");
		}
		header('Location: index.php?mode=admin&action=user'. $search_user_q .'&page='. $page .'&order='. $order .'&descasc='. $descasc);
		exit;
	}

	if (isset($_GET['activate'])) {
		// Active and unlock user
		mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, registered = registered, activate_code = '', user_lock = 0 WHERE user_id = ". intval($_GET['activate']) ." LIMIT 1");

		// Send notification to user
		$user_result = mysqli_query($connid, "SELECT user_name, user_email FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_GET['activate']) ." LIMIT 1");
		$field = mysqli_fetch_array($user_result);
		mysqli_free_result($user_result);

		$lang['admin_activate_user_email_text'] = str_replace("[name]", htmlspecialchars($field['user_name']), $lang['admin_activate_user_email_text']);
		$lang['admin_activate_user_email_text'] = str_replace("[login_link]", $settings['forum_address']."index.php?mode=login", $lang['admin_activate_user_email_text']);
		if (!my_mail(htmlspecialchars($field['user_email']), $lang['admin_activate_user_email_subj'], $lang['admin_activate_user_email_text'])) {
			$send_error = '&send_error=true';
		}
		header('Location: index.php?mode=admin&edit_user='.intval($_GET['activate']).$send_error);
		exit;
	}

	if ((isset($_POST['reset_forum_confirmed']) || isset($_POST['uninstall_forum_confirmed'])) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		if (empty($_POST['confirm_pw'])) $errors[] = 'error_password_wrong';

		if (empty($errors)) {
			$pw_result = @mysqli_query($connid, "SELECT user_pw FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id']) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			$field = mysqli_fetch_array($pw_result);
			mysqli_free_result($pw_result);
			if (!is_pw_correct($_POST['confirm_pw'], $field['user_pw'])) $errors[] = 'error_password_wrong';
		}

		if (empty($errors)) {
			if (isset($_POST['reset_forum_confirmed'])) {
				if (empty($_POST['delete_postings']) && empty($_POST['delete_userdata'])) $errors[] = 'error_no_selection_made';
				else {
					if (isset($_POST['delete_postings'])) {
						@mysqli_query($connid, "TRUNCATE TABLE ".$db_settings['forum_table']) or raise_error('database_error', mysqli_error($connid));
						@mysqli_query($connid, "TRUNCATE TABLE ".$db_settings['entry_cache_table']) or raise_error('database_error', mysqli_error($connid));
					}
					if (isset($_POST['delete_userdata'])) {
						@mysqli_query($connid, "DELETE FROM ".$db_settings['userdata_table']." WHERE user_id != ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
						@mysqli_query($connid, "DELETE FROM ".$db_settings['bookmark_table']." WHERE user_id != ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
						@mysqli_query($connid, "DELETE FROM ".$db_settings['read_status_table']." WHERE user_id != ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
						@mysqli_query($connid, "TRUNCATE TABLE ".$db_settings['userdata_cache_table']) or raise_error('database_error', mysqli_error($connid));
					}
					header('Location: index.php?mode=admin');
					exit;
				}
			}
			elseif (isset($_POST['uninstall_forum_confirmed'])) {
				echo '<pre>';
				echo 'Deleting table <strong>'.$db_settings['forum_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['forum_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['userdata_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['userdata_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['settings_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['settings_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['category_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['category_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> (MySQL: '. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['pages_table'].'</strong>... ';
				if(@mysqli_query($connid, "DROP TABLE ".$db_settings['pages_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> (MySQL: '. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['smilies_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['smilies_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> (MySQL: '. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['banlists_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['banlists_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> (MySQL: '. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['useronline_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['useronline_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['login_control_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['login_control_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['entry_cache_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['entry_cache_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['userdata_cache_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['userdata_cache_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['bookmark_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['bookmark_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['read_status_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['read_status_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '<br />';
				echo 'Deleting table <strong>'.$db_settings['temp_infos_table'].'</strong>... ';
				if (@mysqli_query($connid, "DROP TABLE ".$db_settings['temp_infos_table'])) echo '<b style="color:green;">OK</b>'; else echo '<b style="color:red;">FAILED</b> ('. mysqli_error($connid) .')';
				echo '</pre>';
				exit;
			}
		}
		if (isset($errors)) {
			$smarty->assign('errors', $errors);
			$action = 'reset_uninstall';
		}
	}

	if (isset($_POST['settings_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		// not checked checkboxes:
		if (empty($_POST['show_if_edited'])) $_POST['show_if_edited'] = 0;
		if (empty($_POST['dont_reg_edit_by_admin'])) $_POST['dont_reg_edit_by_admin'] = 0;
		if (empty($_POST['dont_reg_edit_by_mod'])) $_POST['dont_reg_edit_by_mod'] = 0;
		if (empty($_POST['smilies'])) $_POST['smilies'] = 0;
		if (empty($_POST['bbcode'])) $_POST['bbcode'] = 0;
		if (empty($_POST['bbcode_img'])) $_POST['bbcode_img'] = 0;
		if (empty($_POST['tag_cloud'])) $_POST['tag_cloud'] = 0;
		if (empty($_POST['avatars'])) $_POST['avatars'] = 0;
		if (empty($_POST['autolink'])) $_POST['autolink'] = 0;
		if (empty($_POST['count_views'])) $_POST['count_views'] = 0;
		if (empty($_POST['count_users_online'])) $_POST['count_users_online'] = 0;
		if (empty($_POST['rss_feed'])) $_POST['rss_feed'] = 0;
		if (empty($_POST['terms_of_use_agreement'])) $_POST['terms_of_use_agreement'] = 0;
		if (empty($_POST['data_privacy_agreement'])) $_POST['data_privacy_agreement'] = 0;
		if (empty($_POST['forum_enabled'])) $_POST['forum_enabled'] = 0;
		if (empty($_POST['user_edit_if_no_replies'])) $_POST['user_edit_if_no_replies'] = 0;
		if (empty($_POST['time_zone'])) $_POST['time_zone'] = '';
		if (empty($_POST['read_state_expiration_method'])) $_POST['read_state_expiration_method'] = 0;

		foreach ($settings as $key => $val) {
			if (isset($_POST[$key])) mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '". mysqli_real_escape_string($connid, $_POST[$key]) ."' WHERE name = '". mysqli_real_escape_string($connid, $key) ."' LIMIT 1");
		}
		if (isset($_POST['clear_cache'])) {
			mysqli_query($connid, "TRUNCATE TABLE ".$db_settings['entry_cache_table']);
			mysqli_query($connid, "TRUNCATE TABLE ".$db_settings['userdata_cache_table']);
		}
		if ($settings['autologin'] == 1 && isset($_POST['autologin']) && $_POST['autologin'] == 0) {
			mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET auto_login_code = ''");
		}
		$qSetChangeTime = "INSERT INTO " . $db_settings['temp_infos_table'] . " (name, value, time)
		VALUES ('last_changes', UNIX_TIMESTAMP(NOW()), NOW()) ON DUPLICATE KEY UPDATE value = UNIX_TIMESTAMP(NOW()), time = NOW()";
		mysqli_query($connid, $qSetChangeTime);
		if (isset($_POST['return_to']) and $_POST['return_to'] === 'advanced_settings') {
			header("Location: index.php?mode=admin&action=advanced_settings&saved=true");
		} else {
			header("Location: index.php?mode=admin&action=settings&saved=true");
		}
		exit;
	}

	if (isset($_POST['register_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		$ar_username = $_POST['ar_username'];
		$ar_email = $_POST['ar_email'];
		$ar_pw = $_POST['ar_pw'];
		if (isset($_POST['ar_send_userdata']) && $_POST['ar_send_userdata'] != '') $ar_send_userdata = true;
		$ar_username = trim($ar_username);
		$ar_email = trim($ar_email);
		$ar_pw = trim($ar_pw);
		// look if form complete
		if ($ar_username == '' or $ar_email == '') $errors[] = 'error_form_uncomplete';

		if (empty($errors)) {
			// look if name already exists:
			$name_result = mysqli_query($connid, "SELECT user_name FROM ".$db_settings['userdata_table']." WHERE lower(user_name) = '". mysqli_real_escape_string($connid, my_strtolower($ar_username, $lang['charset'])) ."'") or raise_error('database_error', mysqli_error($connid));
			if (mysqli_num_rows($name_result) > 0) $errors[] = 'user_name_already_exists';
			mysqli_free_result($name_result);

			if (!is_valid_email($ar_email)) 
				$errors[] = 'error_email_wrong';
			if ($ar_pw == "" && !isset($ar_send_userdata)) 
				$errors[] = 'error_send_userdata';

			if (my_strlen($ar_username, $lang['charset']) > $settings['name_maxlength'])
				$errors[] = $lang['name_marking'] . " " .$lang['error_username_too_long'];

			$too_long_word = too_long_word($ar_username, $settings['name_word_maxlength']);
			if ($too_long_word)
				$errors[] = 'error_word_too_long';
		}
		// save user if no errors:
		if (empty($errors)) {
			// generate password if not specified:
			if ($ar_pw == '') {
				if ($settings['min_pw_length'] < 8) $pwl = 8;
				else $pwl = $settings['min_pw_length'];
				$ar_pw = random_string($pwl);
			}
			$pw_hash = generate_pw_hash($ar_pw);
			mysqli_query($connid, "INSERT INTO ".$db_settings['userdata_table']." (user_type, user_name, user_real_name, user_pw, user_email, user_hp, user_location, email_contact, last_login, last_logout, user_ip, registered, user_view, fold_threads, signature, profile, auto_login_code, pwf_code, activate_code) VALUES (0,'". mysqli_real_escape_string($connid, $ar_username) ."', '', '". mysqli_real_escape_string($connid, $pw_hash) ."','". mysqli_real_escape_string($connid, $ar_email) ."', '', '', ". $settings['default_email_contact'] .", NULL, NOW(), '". mysqli_real_escape_string($connid, $_SERVER["REMOTE_ADDR"]) ."',NOW(),". intval($settings['default_view']) .", ". intval($settings['fold_threads']) .",'','','','','')") or die(mysqli_error($connid)); //raise_error('database_error',mysqli_error($connid));

			// send userdata:
			$send_error = '';
			if (isset($ar_send_userdata)) {
				// load e-mail strings from language file:
				$smarty->configLoad($settings['language_file'], 'emails');
				$lang = $smarty->getConfigVars();
				$lang['admin_reg_user_email_text'] = str_replace("[name]", $ar_username, $lang['admin_reg_user_email_text']);
				$lang['admin_reg_user_email_text'] = str_replace("[password]", $ar_pw, $lang['admin_reg_user_email_text']);
				$lang['admin_reg_user_email_text'] = str_replace("[login_link]", $settings['forum_address']."index.php?mode=login&username=".urlencode($ar_username)."&userpw=".$ar_pw, $lang['admin_reg_user_email_text']);
				if (!my_mail($ar_email, $lang['admin_reg_user_email_subj'], $lang['admin_reg_user_email_text'])) $send_error = '&send_error=true';
			}
			header("location: index.php?mode=admin&action=user&new_user=".urlencode($ar_username).$send_error);
			exit;
		} else {
			$smarty->assign('errors', $errors);
			$smarty->assign('ar_username', htmlspecialchars($ar_username));
			$smarty->assign('ar_email', htmlspecialchars($ar_email));
			$action = 'register';
		}
	}

	if (isset($_POST['spam_protection_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'])
		$action = 'spam_protection_submit';

	if (isset($_POST['add_smiley']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		if (!file_exists('images/smilies/'.$_POST['add_smiley'])) $errors[] = 'smiley_file_doesnt_exist';
		if (trim($_POST['smiley_code']) == '') $errors[] = 'smiley_code_empty';

		if(empty($errors)) {
			$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['smilies_table']);
			list($smilies_count) = mysqli_fetch_row($count_result);
			mysqli_free_result($count_result);
			$order_id = $smilies_count + 1;

			mysqli_query($connid, "INSERT INTO ".$db_settings['smilies_table']." (order_id, file, code_1, code_2, code_3, code_4, code_5, title) VALUES (". mysqli_real_escape_string($connid, $order_id) .",'". mysqli_real_escape_string($connid, $_POST['add_smiley']) ."','". mysqli_real_escape_string($connid, trim($_POST['smiley_code'])) ."','','','','','')");

			header("location: index.php?mode=admin&action=smilies");
			exit;
		} else {
			$smarty->assign('errors', $errors);
			$action = 'smilies';
		}
	}

	if (isset($_GET['delete_smiley'])) {
		mysqli_query($connid, "DELETE FROM ".$db_settings['smilies_table']." WHERE id = ". intval($_GET['delete_smiley']));

		$result = mysqli_query($connid, "SELECT id FROM ".$db_settings['smilies_table']." ORDER BY order_id ASC");
		$i = 1;
		while ($data = mysqli_fetch_array($result)) {
			mysqli_query($connid, "UPDATE ".$db_settings['smilies_table']." SET order_id=". intval($i) ." WHERE id = ". intval($data['id']));
			$i++;
		}
		mysqli_free_result($result);

		header("location: index.php?mode=admin&action=smilies");
		die();
	}

	if (isset($_GET['edit_smiley'])) {
		$result = mysqli_query($connid, "SELECT id, file, code_1, code_2, code_3, code_4, code_5, title FROM ".$db_settings['smilies_table']." WHERE id = ". intval($_GET['edit_smiley']) ." LIMIT 1");
		if (!$result) raise_error('database_error', mysqli_error($connid));
		$data = mysqli_fetch_array($result);
		mysqli_free_result($result);
		$id = intval($data['id']);
		$file = $data['file'];
		$code_1 = $data['code_1'];
		$code_2 = $data['code_2'];
		$code_3 = $data['code_3'];
		$code_4 = $data['code_4'];
		$code_5 = $data['code_5'];
		$title = $data['title'];
		$action = 'edit_smiley';
	}

	if (isset($_POST['edit_smiley_submit']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		$id = intval($_POST['id']);
		$file = trim($_POST['file']);
		$code_1 = trim($_POST['code_1']);
		$code_2 = trim($_POST['code_2']);
		$code_3 = trim($_POST['code_3']);
		$code_4 = trim($_POST['code_4']);
		$code_5 = trim($_POST['code_5']);
		$title = trim($_POST['title']);

		if (!file_exists('images/smilies/'.$file)) $errors[] = 'smiley_file_doesnt_exist';
		if ($code_1 == '' && $code_2 == '' && $code_3 == '' && $code_4 == '' && $code_5 == '') $errors[] = 'smiley_code_empty';
		if (empty($errors)) {
			mysqli_query($connid, "UPDATE ".$db_settings['smilies_table']." SET file = '". mysqli_real_escape_string($connid, $file) ."', code_1 = '". mysqli_real_escape_string($connid, $code_1) ."', code_2 = '". mysqli_real_escape_string($connid, $code_2) ."', code_3 = '". mysqli_real_escape_string($connid, $code_3) ."', code_4 = '". mysqli_real_escape_string($connid, $code_4) ."', code_5 = '". mysqli_real_escape_string($connid, $code_5) ."', title = '". mysqli_real_escape_string($connid, $title) ."' WHERE id = ". intval($id));
			header("location: index.php?mode=admin&action=smilies");
			exit;
		} else {
			$smarty->assign('errors', $errors);
			$action = 'edit_smiley';
		}
	}

 if (isset($_GET['enable_smilies'])) {
		mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = 1 WHERE name = 'smilies'");
		header("location: index.php?mode=admin&action=smilies");
		die();
	}

	if (isset($_GET['disable_smilies'])) {
		mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = 0 WHERE name = 'smilies'");
		header("location: index.php?mode=admin&action=smilies");
		die();
	}

	if (isset($_GET['action']) and $_GET['action'] == 'reset_tou') {
		mysqli_query($connid, "UPDATE ". $db_settings['userdata_table'] ." SET tou_accepted = NULL");
		header("location: index.php?mode=admin&action=user");
		die();
	}

	if (isset($_GET['action']) and $_GET['action'] == 'reset_dps') {
		mysqli_query($connid, "UPDATE ". $db_settings['userdata_table'] ." SET dps_accepted = NULL");
		header("location: index.php?mode=admin&action=user");
		die();
	}

	if (isset($_GET['action']) and $_GET['action'] == 'list_uploads') {
		$uploaded_images_path = 'images/uploaded/';
		$images = array();
		$browse_images = (isset($_GET['browse_images']) && $_GET['browse_images'] > 0) ? intval($_GET['browse_images']) : 1;
		$handle = opendir($uploaded_images_path);
		while ($file = readdir($handle)) {
			if (preg_match('/\.(gif|png|jpg|svg)$/i', $file)) {
				$images[] = $file;
			}
		}
		closedir($handle);
		if ($images) {
			rsort($images);
			$page_browse['total_items'] = count($images);
			$page_browse['items_per_page'] = $settings['uploads_per_page'];
			$total_pages = ceil($page_browse['total_items'] / $page_browse['items_per_page']);
			$page_browse['page'] = isset($_GET['page']) ? intval($_GET['page']) : 1;
			$page_browse['page'] = ($page_browse['page'] > $total_pages) ? $total_pages : $page_browse['page'];
			$page_browse['page'] = ($page_browse['page'] < 1) ? 1 : $page_browse['page'];
			$page_browse['browse_array'][] = ($page_browse['page'] > 5) ? 0 : 1;
			for ($browse = $page_browse['page'] - 3; $browse < $page_browse['page'] + 4; $browse++) {
				if ($browse > 1 && $browse < $total_pages) $page_browse['browse_array'][] = $browse;
			}
			if ($page_browse['page'] < $total_pages - 4) $page_browse['browse_array'][] = 0;
			if ($total_pages > 1) $page_browse['browse_array'][] = $total_pages;
			$page_browse['next_page'] = ($page_browse['page'] < $total_pages) ? $page_browse['page'] + 1 : 0;
			$page_browse['previous_page'] = ($page_browse['page'] > 1) ? $page_browse['page'] - 1 : 0;
			$start = $page_browse['page'] * $page_browse['items_per_page'] - $page_browse['items_per_page'];
		}
		$action = 'list_uploads';
	}

	if (isset($_POST['delete_selected_uploads']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		if (isset($_POST['uploads_remove'])) {
			$selected = $_POST['uploads_remove'];
			$selected_uploads_count = count($selected);
			for ($x=0; $x<$selected_uploads_count; $x++) {
				$selected_uploads[$x]['name'] = htmlspecialchars($selected[$x]);
			}
			$action = 'delete_uploads';
		}
		else $action = 'list_uploads';
	}

	if (isset($_POST['delete_uploads_confirmed']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
		if (isset($_POST['selected_confirmed'])) {
			foreach ($_POST['selected_confirmed'] as $upload_rm) {
				$path_rm = 'images/uploaded/'. $upload_rm;
				if (file_exists($path_rm)) {
					@chmod($path_rm, 0777);
					@unlink($path_rm);
				}
			}
		}
		header("location: index.php?mode=admin&action=list_uploads");
		die();
	}

	if (empty($action)) $action='main';
	$smarty->assign('action', $action);

	// check if graphical captcha is available:
	if ($action == 'spam_protection' || $action == 'spam_protection_submit') {
		// is a font available for the graphical CAPTCHA?
		$handle=opendir('modules/captcha/fonts/');
		while ($file = readdir($handle)) {
			if(preg_match('/\.ttf$/i', $file)) {
				$font_available = true;
				break;
			}
		}
		closedir($handle);
		if (isset($font_available)) $smarty->assign('font_available', true);
		// gd lib available for graphical CAPTCHA?
		if (extension_loaded('gd')) $smarty->assign('graphical_captcha_available', true);
	}

	switch($action) {
		case "main":
			$smarty->assign('subnav_location', 'subnav_admin_area');
		break;
		case "categories":
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			 $smarty->assign('subnav_location', 'subnav_categories');

			// look if there are entries in not existing categories:
			if (isset($category_ids_query)) {
				$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE category NOT IN (". $category_ids_query .")");
			} else {
				$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE category != 0");
			}
			list($entries_in_not_existing_categories) = mysqli_fetch_row($count_result);
			mysqli_free_result($count_result);
			$smarty->assign('entries_in_not_existing_categories', $entries_in_not_existing_categories);

			$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['category_table']);
			list($categories_count) = mysqli_fetch_row($count_result);
			mysqli_free_result($count_result);
			$smarty->assign('categories_count',$categories_count);

			if (isset($errors)) $smarty->assign('errors', $errors);

			if ($categories_count > 0) {
				$result = mysqli_query($connid, "SELECT id, order_id, category, accession FROM ".$db_settings['category_table']." ORDER BY order_id ASC");
				if (!$result) raise_error('database_error', mysqli_error($connid));

				$i = 0;
				while ($line = mysqli_fetch_array($result)) {
					$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE category = ". intval($line['id']) ." AND pid = 0");
					list($threads_in_category) = mysqli_fetch_row($count_result);
					mysqli_free_result($count_result);
				$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE category = ". intval($line['id']));
					list($postings_in_category) = mysqli_fetch_row($count_result);
					mysqli_free_result($count_result);
					$categories_list[$i]['id'] = $line['id'];
					$categories_list[$i]['name'] = htmlspecialchars($line['category']);
					$categories_list[$i]['accession'] = $line['accession'];
					$categories_list[$i]['threads_in_category'] = $threads_in_category;
					$categories_list[$i]['postings_in_category'] = $postings_in_category;
					$i++;
				}
				$smarty->assign('categories_list', $categories_list);
				mysqli_free_result($result);
			}
		break;
		case 'user':
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_user');

			if (isset($_GET['new_user'])) $smarty->assign('new_user', htmlspecialchars(urldecode($_GET['new_user'])));
			if (isset($_GET['send_error'])) $smarty->assign('send_error', TRUE);

			if (isset($_GET['order'])) $order = $_GET['order']; else $order = "user_name";
			if ($order != 'user_id' && $order != 'user_name' && $order != 'user_email' && $order != 'user_type' && $order != 'registered' && $order != 'logins' && $order != 'last_login' && $order != 'user_lock' && $order != 'activate_code') $order = 'user_id';
			if (isset($_GET['descasc'])) $descasc = $_GET['descasc']; else $descasc = "ASC";
			if ($descasc != 'DESC' && $descasc != 'ASC') $descasc = 'ASC';

			if (isset($_GET['page'])) $page = intval($_GET['page']); else $page = 1;
			if (empty($category)) $category = "all";

			if (isset($_GET['search_user']) && trim($_GET['search_user']) != '') $search_user = trim($_GET['search_user']);

			// count users and pages:
			if (isset($search_user)) {
				$user_count_result = @mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['userdata_table']." WHERE concat(lower(user_name),lower(user_email)) LIKE '%".mysqli_real_escape_string($connid, my_strtolower($search_user, $lang['charset']))."%'");
			} else {
				$user_count_result = @mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['userdata_table']);
			}
			list($total_users) = mysqli_fetch_row($user_count_result);
			mysqli_free_result($user_count_result);
			$total_pages = ceil($total_users / $settings['users_per_page']);
			if ($page > $total_pages) $page = $total_pages;
			if ($page < 1) $page = 1;
			$ul = ($page-1) * $settings['users_per_page'];

			if (isset($search_user)) {
				$result = @mysqli_query($connid, "SELECT user_id, user_name COLLATE utf8_general_ci AS user_name, user_type, user_email, logins, UNIX_TIMESTAMP(last_login + INTERVAL ". $time_difference ." MINUTE) AS last_login_time, UNIX_TIMESTAMP(registered + INTERVAL ". $time_difference ." MINUTE) AS registered_time, user_lock, activate_code FROM ".$db_settings['userdata_table']." WHERE concat(lower(user_name),lower(user_email)) LIKE '%". mysqli_real_escape_string($connid, my_strtolower($search_user, $lang['charset'])) ."%' ORDER BY ". $order ." ". $descasc ." LIMIT ". intval($ul) .", ". intval($settings['users_per_page']));
			} else {
				$result = @mysqli_query($connid, "SELECT user_id, user_name COLLATE utf8_general_ci AS user_name, user_type, user_email, logins, UNIX_TIMESTAMP(last_login + INTERVAL ". $time_difference ." MINUTE) AS last_login_time, UNIX_TIMESTAMP(registered + INTERVAL ". $time_difference ." MINUTE) AS registered_time, user_lock, activate_code FROM ".$db_settings['userdata_table']." ORDER BY ". $order ." ". $descasc ." LIMIT ". intval($ul) .", ". intval($settings['users_per_page']));
			}
			$result_count = mysqli_num_rows($result);

			if (isset($_GET['letter']) && $_GET['letter'] !=" ") $su_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['userdata_table']." WHERE user_name LIKE '". mysqli_real_escape_string($connid, $_GET['letter']) ."%'");
			else $su_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['userdata_table']);
			list($sel_user_count) = mysqli_fetch_row($su_result);
			mysqli_free_result($su_result);

			$smarty->assign('order', $order);
			$smarty->assign('descasc', $descasc);
			$smarty->assign('ul', $ul);
			$smarty->assign('total_users', $total_users);
			$smarty->assign('page', $page);

			if (isset($search_user)) {
				$smarty->assign('search_user', htmlspecialchars($search_user));
				$smarty->assign('search_user_encoded', urlencode($search_user));
			}

			// data for browse navigation:
			$user_page_browse['page'] = $page;
			$user_page_browse['total_items'] = $total_users;
			$user_page_browse['items_per_page'] = $settings['users_per_page'];
			$user_page_browse['browse_array'][] = 1;
			if ($page > 5) $user_page_browse['browse_array'][] = 0;
			for($browse=$page-3; $browse<$page+4; $browse++) {
				if ($browse > 1 && $browse < $total_pages) $user_page_browse['browse_array'][] = $browse;
			}
			if ($page < $total_pages-4) $user_page_browse['browse_array'][] = 0;
			if ($total_pages > 1) $user_page_browse['browse_array'][] = $total_pages;
			if ($page < $total_pages) $user_page_browse['next_page'] = $page + 1; else $user_page_browse['next_page'] = 0;
			if ($page > 1) $user_page_browse['previous_page'] = $page - 1; else $user_page_browse['previous_page'] = 0;
			$smarty->assign('user_page_browse',$user_page_browse);
			$smarty->assign('pagination', pagination($total_pages,$page,3));

			$i = 0;
			while ($row = mysqli_fetch_array($result)) {
				$userdata[$i]['user_id'] = intval($row['user_id']);
				$userdata[$i]['user_name'] = htmlspecialchars($row['user_name']);
				$userdata[$i]['user_email'] = htmlspecialchars($row['user_email']);
				$userdata[$i]['user_type'] = intval($row['user_type']);
				$userdata[$i]['registered_time'] = htmlspecialchars($row['registered_time']);
				$userdata[$i]['logins'] = intval($row['logins']);
				$userdata[$i]['last_login_time'] = htmlspecialchars($row['last_login_time']);
				$userdata[$i]['user_lock'] = $row['user_lock'];
				if ($row['activate_code'] != '') $userdata[$i]['inactive'] = true;
				$i++;
			}
			mysqli_free_result($result);
			$smarty->assign('result_count', $result_count);
			if (isset($userdata)) $smarty->assign('userdata', $userdata);
		break;
		case 'register':
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=user';
			$breadcrumbs[1]['linkname'] = 'subnav_user';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_register_user');
		break;
		case 'settings':
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs',$breadcrumbs);
			$smarty->assign('subnav_location','subnav_settings');
			if (isset($_GET['saved'])) $smarty->assign('saved', true);
			$rGetSettingsEdit = mysqli_query($connid, "SELECT name, value FROM " . $db_settings['settings_table']);
			while ($line = mysqli_fetch_assoc($rGetSettingsEdit)) {
				$settings_array[$line['name']] = $line['value'];
			}
			$smarty->assign('edSet', $settings_array);
			// languages
			$languages = get_languages(true);
			if (isset($languages) && count($languages) > 1) {
				$smarty->assign('languages', $languages);
			}
			// themes
			$themes = get_themes(true);
			if(isset($themes) && count($themes) > 1) {
				$smarty->assign('themes', $themes);
			}
			$std = (isset($settings['time_difference'])) ? intval($settings['time_difference']) : 0;
			// time zones:
			if(function_exists('date_default_timezone_set') && $time_zones = get_timezones()) {
				$smarty->assign('time_zones', $time_zones);
			}
		break;
		case "advanced_settings":
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=settings';
			$breadcrumbs[1]['linkname'] = 'subnav_settings';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_advanced_settings');
			if (isset($_GET['saved'])) $smarty->assign('saved', true);
			$rGetSettingsEdit = mysqli_query($connid, "SELECT name, value FROM " . $db_settings['settings_table'] ." ORDER BY name ASC");
			while ($line = mysqli_fetch_assoc($rGetSettingsEdit)) {
				$settings_array[$line['name']] = $line['value'];
			}
			$i=0;
			foreach ($settings_array as $key => $val) {
				$settings_sorted[$i]['key'] = $key;
				$settings_sorted[$i]['val'] = $val;
				$i++;
			}
			$smarty->assign('settings_sorted',$settings_sorted);
		break;
		case "delete_users":
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=user';
			$breadcrumbs[1]['linkname'] = 'subnav_user';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_delete_users');
			$smarty->assign('selected_users', $selected_users);
			$smarty->assign('selected_users_count', $selected_users_count);
		break;
		case "user_delete_entries":
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=user';
			$breadcrumbs[1]['linkname'] = 'subnav_user';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_delete_entries_user');
			$smarty->assign('user_delete_entries', $user_delete_entries);
		break;
		case "delete_category":
		break;
		case "edit_category":
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=categories';
			$breadcrumbs[1]['linkname'] = 'subnav_categories';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_edit_category');
		break;
		case "backup":
			if (isset($_GET['msg'])) {
			switch ($_GET['msg']) {
					case 'backup_file_created': $smarty->assign('message', 'backup_file_created'); break;
					case 'restore_backup_ok': $smarty->assign('message', 'restore_backup_ok'); break;
				}
			}
			$fp = opendir('backup/');
			while ($file = readdir($fp)) {
				if (preg_match('/\.sql$/i', $file)) {
					$files[$file] = filemtime('backup/'.$file);
				}
			}
			closedir($fp);

			if (isset($files)) {
				arsort($files); // order by date
				$i = 0;
				foreach ($files as $key => $val) {
					$backup_files[$i]['file'] = htmlspecialchars($key);
					$backup_files[$i]['date'] = $val;
					$backup_files[$i]['size'] = number_format(filesize('backup/'.$key) / 1048576,2) .' MB';
					$i++;
				}
				$smarty->assign('backup_files',$backup_files);
			}

			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_backup');
		break;
		case 'delete_backup_files_confirm':
			$smarty->assign('delete_backup_files', $_REQUEST['delete_backup_files']);
			$smarty->assign('file_number', count($_REQUEST['delete_backup_files']));
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=backup';
			$breadcrumbs[1]['linkname'] = 'subnav_backup';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_delete_backup_file');
		break;
		case 'restore':
			$smarty->assign('backup_file', $backup_file);
			$smarty->assign('backup_file_date', filemtime('backup/'.$backup_file));

			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=backup';
			$breadcrumbs[1]['linkname'] = 'subnav_backup';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_restore');
		break;
		case 'update':
			if ($fp=@opendir('update/')) {
				$i = 0;
				while ($file = readdir($fp)) {
					if (preg_match('/\.php$/i', $file)) {
						$update_files[$i]['filename'] = htmlspecialchars($file);
						$i++;
					}
				}
				closedir($fp);
			}
			if (isset($update_files)) $smarty->assign('update_files', $update_files);

			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_update');
		break;
		case 'run_update':
			$smarty->assign('update_file',$update_file);

			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=update';
			$breadcrumbs[1]['linkname'] = 'subnav_update';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_update_run');
		break;

		case "email_list":
			$email_result = mysqli_query($connid, "SELECT user_email FROM ".$db_settings['userdata_table']);
			if (!$email_result) raise_error('database_error', mysqli_error($connid));
			while ($line = mysqli_fetch_array($email_result)) {
     $email_list[] = htmlspecialchars($line['user_email']);
			}
			mysqli_free_result($email_result);
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=user';
			$breadcrumbs[1]['linkname'] = 'subnav_user';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_email_list');
			$smarty->assign('email_list', implode(", ", $email_list));
		break;
		case "clear_userdata":
			if (empty($logins)) $logins = 1;
			if (empty($days)) $days = 60;
			$smarty->assign('logins', $logins);
			$smarty->assign('days', $days);
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=user';
			$breadcrumbs[1]['linkname'] = 'subnav_user';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_clear_userdata');
		break;
		case "spam_protection":
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_spam_protection');
			if (isset($_GET['saved'])) $smarty->assign('saved', true);

			// get banned users:
			$result = mysqli_query($connid, "SELECT list FROM ".$db_settings['banlists_table']." WHERE name = 'user_agents' LIMIT 1");
			if (!$result) raise_error('database_error', mysqli_error($connid));
			$data = mysqli_fetch_array($result);
			$smarty->assign('banned_user_agents', htmlspecialchars($data['list']));
			mysqli_free_result($result);
			// get banned ips:
			$result = mysqli_query($connid, "SELECT list FROM ".$db_settings['banlists_table']." WHERE name = 'ips' LIMIT 1");
			if (!$result) raise_error('database_error', mysqli_error($connid));
			$data = mysqli_fetch_array($result);
			$smarty->assign('banned_ips', htmlspecialchars($data['list']));
			mysqli_free_result($result);
			// get not accepted words:
			$result = mysqli_query($connid, "SELECT list FROM ".$db_settings['banlists_table']." WHERE name = 'words' LIMIT 1");
			if (!$result) raise_error('database_error', mysqli_error($connid));
			$data = mysqli_fetch_array($result);
			$smarty->assign('not_accepted_words', htmlspecialchars($data['list']));
			mysqli_free_result($result);

			$smarty->assign('captcha_posting', $settings['captcha_posting']);
			$smarty->assign('captcha_email', $settings['captcha_email']);
			$smarty->assign('captcha_register', $settings['captcha_register']);
			$smarty->assign('stop_forum_spam', $settings['stop_forum_spam']);
			$smarty->assign('bad_behavior', $settings['bad_behavior']);
			$smarty->assign('akismet_key', $settings['akismet_key']);
			$smarty->assign('akismet_entry_check', $settings['akismet_entry_check']);
			$smarty->assign('akismet_mail_check', $settings['akismet_mail_check']);
			$smarty->assign('save_spam', $settings['save_spam']);
			$smarty->assign('auto_delete_spam', $settings['auto_delete_spam']);
			$smarty->assign('spam_check_registered', $settings['spam_check_registered']);			
			$smarty->assign('b8_entry_check', $settings['b8_entry_check']);
			$smarty->assign('b8_auto_training', $settings['b8_auto_training']);
			$smarty->assign('b8_spam_probability_threshold', $settings['b8_spam_probability_threshold']);
		break;
		case "smilies":
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_smilies');

			if ($settings['smilies'] == 1) {
				$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['smilies_table']);
				list($smilies_count) = mysqli_fetch_row($count_result);
				mysqli_free_result($count_result);

				$fp = opendir('images/smilies/');
				while ($file = readdir($fp)) {
					if (preg_match('/\.(gif|png|jpg|svg)$/i', $file)) {
						$smiley_files[] = $file;
					}
				}
				closedir($fp);

				if ($smilies_count > 0) {
					$result = mysqli_query($connid, "SELECT id, file, code_1, code_2, code_3, code_4, code_5, title FROM ".$db_settings['smilies_table']." ORDER BY order_id ASC");
					if (!$result) raise_error('database_error', mysqli_error($connid));
					$i = 0;
					while ($line = mysqli_fetch_array($result)) {
						// remove used smilies from smiley array:
						if (isset($smiley_files)) {
							unset($cleared_smiley_files);
							foreach ($smiley_files as $smiley_file) {
								if ($line['file'] != $smiley_file) $cleared_smiley_files[] = $smiley_file;
							}
							if (isset($cleared_smiley_files)) $smiley_files = $cleared_smiley_files;
							else unset($smiley_files);
						}

						unset($codes);
						if (trim($line['code_1']) != '') $codes[] = $line['code_1'];
						if (trim($line['code_2']) != '') $codes[] = $line['code_2'];
						if (trim($line['code_3']) != '') $codes[] = $line['code_3'];
						if (trim($line['code_4']) != '') $codes[] = $line['code_4'];
						if (trim($line['code_5']) != '') $codes[] = $line['code_5'];
						$codes_disp = implode(' &nbsp;',$codes);

						$smilies[$i]['id'] = $line['id'];
						$smilies[$i]['file'] = $line['file'];
						$smilies[$i]['code_1'] = $line['code_1'];
						$smilies[$i]['codes'] = $codes_disp;
						$smilies[$i]['title'] = $line['title'];
						$i++;
					}
					mysqli_free_result($result);
				}
				else $smilies = '';

				$smarty->assign('smilies', $smilies);

				if (isset($smiley_files)) $smiley_count = count($smiley_files);
				else $smiley_count = 0;
				if ($smiley_count > 0) $smarty->assign('smiley_files', $smiley_files);
			}
		break;
		case 'edit_smiley':
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=smilies';
			$breadcrumbs[1]['linkname'] = 'subnav_smilies';
			$smarty->assign('breadcrumbs',$breadcrumbs);
			$smarty->assign('subnav_location','subnav_edit_smiley');

			$fp = opendir('images/smilies/');
			while ($smiley_file = readdir($fp)) {
				if (preg_match('/\.(gif|png|jpg|svg)$/i', $smiley_file)) {
					$smiley_files[] = $smiley_file;
				}
			}
			closedir($fp);
			if (isset($smiley_files)) $smiley_count = count($smiley_files);
			else $smiley_count = 0;
			if ($smiley_count > 0) $smarty->assign('smiley_files', $smiley_files);
			$smarty->assign('id', $id);
			$smarty->assign('file', $file);
			$smarty->assign('code_1', $code_1);
			$smarty->assign('code_2', $code_2);
			$smarty->assign('code_3', $code_3);
			$smarty->assign('code_4', $code_4);
			$smarty->assign('code_5', $code_5);
			$smarty->assign('title', $title);
		break;
		case 'edit_user':
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=user';
			$breadcrumbs[1]['linkname'] = 'subnav_user';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_edit_user');
		break;
		case 'reset_uninstall':
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_reset_uninstall');
		break;
		case 'spam_protection_submit':
			$akismet_key = trim($_POST['akismet_key']);
			if (empty($_POST['stop_forum_spam'])) $stop_forum_spam = 0; else $stop_forum_spam = 1;
			if (empty($_POST['bad_behavior'])) $bad_behavior = 0; else $bad_behavior = 1;
			if (isset($_POST['captcha_posting'])) $captcha_posting = intval($_POST['captcha_posting']); else $captcha_posting = 0;
			if (isset($_POST['captcha_email'])) $captcha_email = intval($_POST['captcha_email']); else $captcha_email = 0;
			if (isset($_POST['captcha_register'])) $captcha_register = intval($_POST['captcha_register']); else $captcha_register = 0;
			if (empty($_POST['akismet_entry_check'])) $akismet_entry_check = 0; else $akismet_entry_check = 1;
			if (empty($_POST['akismet_mail_check'])) $akismet_mail_check = 0; else $akismet_mail_check = 1;
			if (empty($_POST['save_spam'])) $save_spam = 0; else $save_spam = 1;
			if (empty($_POST['spam_check_registered'])) $spam_check_registered = 0; else $spam_check_registered = 1;
			if (empty($_POST['b8_entry_check'])) $b8_entry_check = 0; else $b8_entry_check = 1;
			if (empty($_POST['b8_auto_training'])) $b8_auto_training = 0; else $b8_auto_training = 1;
			if (isset($_POST['b8_spam_probability_threshold']))	$b8_spam_probability_threshold = intval($_POST['b8_spam_probability_threshold']);
			$b8_spam_probability_threshold = min(100, max(0, $b8_spam_probability_threshold));

			// check akismet API key:
			if ($akismet_key != '') {
				require('modules/akismet/akismet.class.php');
				$akismet = new Akismet($settings['forum_address'], $akismet_key);
				// test for errors
				if ($akismet->errorsExist()) {
					// returns true if any errors exist
					if ($akismet->isError(AKISMET_INVALID_KEY)) {
						$errors[] = 'error_akismet_api_key';
					} elseif ($akismet->isError(AKISMET_RESPONSE_FAILED)) {
						$errors[] = 'error_akismet_connection_admin';
					} elseif ($akismet->isError(AKISMET_SERVER_NOT_FOUND)) {
						$errors[] = 'error_akismet_connection_admin';
					}
				}
			}

			// banists:
			if (trim($_POST['banned_ips']) != '') {
				$banned_ips_array = preg_split('/\015\012|\015|\012/',$_POST['banned_ips']);
				foreach ($banned_ips_array as $banned_ip) {
					if (trim($banned_ip) != '') $banned_ips_array_checked[] = trim($banned_ip);
				}
				natcasesort($banned_ips_array_checked);
				$banned_ips = implode("\n", $banned_ips_array_checked);
				if (is_ip_banned($_SERVER['REMOTE_ADDR'], $banned_ips_array_checked)) $errors[] = 'error_own_ip_banned';
			}
			else $banned_ips = '';

			if (trim($_POST['banned_user_agents']) != '') {
				$banned_user_agents_array = preg_split('/\015\012|\015|\012/', $_POST['banned_user_agents']);
				foreach ($banned_user_agents_array as $banned_user_agent) {
					if (trim($banned_user_agent) != '') $banned_user_agents_array_checked[] = trim($banned_user_agent);
				}
				natcasesort($banned_user_agents_array_checked);
				$banned_user_agents = implode("\n", $banned_user_agents_array_checked);
				if (is_user_agent_banned($_SERVER['HTTP_USER_AGENT'], $banned_user_agents_array_checked)) $errors[] = 'error_own_user_agent_banned';
			}
			else $banned_user_agents = '';

			if (trim($_POST['not_accepted_words']) != '') {
				$not_accepted_words_array = preg_split('/\015\012|\015|\012/',$_POST['not_accepted_words']);
				foreach ($not_accepted_words_array as $not_accepted_word) {
					if (trim($not_accepted_word) != '') $not_accepted_words_array_checked[] = trim($not_accepted_word);
				}
				natcasesort($not_accepted_words_array_checked);
				$not_accepted_words = implode("\n", $not_accepted_words_array_checked);
			}
			else $not_accepted_words = '';

			if (empty($errors)) {
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='". mysqli_real_escape_string($connid, $captcha_posting) ."' WHERE name = 'captcha_posting'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='". mysqli_real_escape_string($connid, $captcha_email) ."' WHERE name = 'captcha_email'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='". mysqli_real_escape_string($connid, $captcha_register) ."' WHERE name = 'captcha_register'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='". mysqli_real_escape_string($connid, $stop_forum_spam) ."' WHERE name = 'stop_forum_spam'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='". mysqli_real_escape_string($connid, $bad_behavior) ."' WHERE name = 'bad_behavior'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='". mysqli_real_escape_string($connid, $akismet_key) ."' WHERE name = 'akismet_key'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '". intval($akismet_entry_check) ."' WHERE name = 'akismet_entry_check'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '". intval($akismet_mail_check) ."' WHERE name = 'akismet_mail_check'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '". intval($save_spam)."' WHERE name = 'save_spam'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '". intval($_POST['auto_delete_spam']) ."' WHERE name = 'auto_delete_spam'");
				mysqli_query($connid, "UPDATE ".$db_settings['banlists_table']." SET list = '". mysqli_real_escape_string($connid, $banned_ips) ."' WHERE name = 'ips'");
				mysqli_query($connid, "UPDATE ".$db_settings['banlists_table']." SET list = '". mysqli_real_escape_string($connid, $banned_user_agents) ."' WHERE name = 'user_agents'");
				mysqli_query($connid, "UPDATE ".$db_settings['banlists_table']." SET list = '". mysqli_real_escape_string($connid, $not_accepted_words) ."' WHERE name = 'words'");
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '". intval($spam_check_registered) ."' WHERE name = 'spam_check_registered'");	
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '". intval($b8_entry_check) ."' WHERE name = 'b8_entry_check'");	
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '". intval($b8_auto_training) ."' WHERE name = 'b8_auto_training'");	
				mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '". intval($b8_spam_probability_threshold) ."' WHERE name = 'b8_spam_probability_threshold'");

				if (trim($banned_ips) == '' && trim($banned_user_agents) == '') $access_permission_checks = 0;
				else $access_permission_checks = 1;
				mysqli_query($connid, "UPDATE ".$db_settings['temp_infos_table']." SET value = '". intval($access_permission_checks) ."' WHERE name = 'access_permission_checks'");

				header("Location: index.php?mode=admin&action=spam_protection&saved=true");
				exit;
			} else {
				$smarty->assign('action', 'spam_protection');
				$smarty->assign('captcha_posting', $captcha_posting);
				$smarty->assign('captcha_email', $captcha_email);
				$smarty->assign('captcha_register', $captcha_register);
				$smarty->assign('stop_forum_spam', $stop_forum_spam);
				$smarty->assign('bad_behavior', $bad_behavior);
				$smarty->assign('akismet_key', htmlspecialchars($akismet_key));
				$smarty->assign('akismet_entry_check', $akismet_entry_check);
				$smarty->assign('akismet_mail_check', $akismet_mail_check);
				$smarty->assign('save_spam', $save_spam);
				$smarty->assign('auto_delete_spam', $_POST['auto_delete_spam']);
				$smarty->assign('banned_ips', htmlspecialchars($_POST['banned_ips']));
				$smarty->assign('banned_user_agents', htmlspecialchars($_POST['banned_user_agents']));
				$smarty->assign('not_accepted_words', htmlspecialchars($_POST['not_accepted_words']));
				$smarty->assign('spam_check_registered', $spam_check_registered);			
				$smarty->assign('b8_entry_check', $b8_entry_check);
				$smarty->assign('b8_auto_training', $b8_auto_training);
				$smarty->assign('b8_spam_probability_threshold', $b8_spam_probability_threshold);
				$smarty->assign('errors', $errors);
				$breadcrumbs[0]['link'] = 'index.php?mode=admin';
				$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
				$smarty->assign('breadcrumbs', $breadcrumbs);
				$smarty->assign('subnav_location', 'subnav_spam_protection');
			}
		break;
		case 'pages':
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_pages');

			$result = mysqli_query($connid, "SELECT id, title, content, menu_linkname, access FROM ".$db_settings['pages_table']." ORDER BY order_id ASC") or raise_error('database_error', mysqli_error($connid));
			if (mysqli_num_rows($result) > 0) {
				$i = 0;
				while($data = mysqli_fetch_array($result)) {
					$pages[$i]['id'] = intval($data['id']);
					$pages[$i]['title'] = $data['title'];
					$pages[$i]['content'] = $data['content'];
					$pages[$i]['menu_linkname'] = $data['menu_linkname'];
					$pages[$i]['access'] = intval($data['access']);
					$i++;
				}
				$smarty->assign('pages',$pages);
			}
			mysqli_free_result($result);
		break;
		case 'edit_page':
			if (isset($_GET['edit_page'])) {
				$id = intval($_GET['edit_page']);
				$result = mysqli_query($connid, "SELECT id, title, content, menu_linkname, access FROM ".$db_settings['pages_table']." WHERE id = ". $id ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				if (mysqli_num_rows($result) > 0) {
				$data = mysqli_fetch_array($result);
				$smarty->assign('id', $id);
				$smarty->assign('title', htmlspecialchars($data['title']));
				$smarty->assign('content', htmlspecialchars($data['content']));
				$smarty->assign('menu_linkname', htmlspecialchars($data['menu_linkname']));
				$smarty->assign('access', $data['access']);
				}
				mysqli_free_result($result);
			}
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=pages';
			$breadcrumbs[1]['linkname'] = 'subnav_pages';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			if (isset($id)) $smarty->assign('subnav_location', 'subnav_edit_page');
			else $smarty->assign('subnav_location', 'subnav_add_page');
		break;
		case 'edit_page_submit':
			if (isset($_POST['id'])) $id = intval($_POST['id']);
			if (isset($_POST['title'])) $title = trim($_POST['title']); else $title = '';
			if (isset($_POST['content'])) $content = trim($_POST['content']); else $content = '';
			if (isset($_POST['menu_linkname'])) $menu_linkname = trim($_POST['menu_linkname']); else $menu_linkname = '';
			if (isset($_POST['access']) && $_POST['access'] == 1) $access = 1; else $access = 0;
			if ($title == '') $errors[] = 'error_no_page_title';

			if (empty($errors)) {
				if (isset($id)) {
					@mysqli_query($connid, "UPDATE ".$db_settings['pages_table']." SET title = '". mysqli_real_escape_string($connid, $title) ."', content = '". mysqli_real_escape_string($connid, $content) ."', menu_linkname = '". mysqli_real_escape_string($connid, $menu_linkname) ."', access = ". intval($access) ." WHERE id = ". $id) or die(mysqli_error($connid));
				} else {
					list($pages_count) = mysqli_fetch_row(mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['pages_table']));
					$order_id = $pages_count + 1;
					@mysqli_query($connid, "INSERT INTO ".$db_settings['pages_table']." (order_id, title, content, menu_linkname, access) VALUES (". intval($order_id) .", '". mysqli_real_escape_string($connid, $title) ."', '". mysqli_real_escape_string($connid, $content) ."', '". mysqli_real_escape_string($connid, $menu_linkname) ."', ". intval($access) .")") or die(mysqli_error($connid));
				}
				header('Location: index.php?mode=admin&action=pages');
				exit;
			} else {
				if (isset($id)) $smarty->assign('id', $id);
				$smarty->assign('title', htmlspecialchars($title));
				$smarty->assign('content', htmlspecialchars($content));
				$smarty->assign('menu_linkname', htmlspecialchars($menu_linkname));
				$smarty->assign('access', $access);
				$breadcrumbs[0]['link'] = 'index.php?mode=admin';
				$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
				$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=pages';
				$breadcrumbs[1]['linkname'] = 'subnav_pages';
				$smarty->assign('breadcrumbs', $breadcrumbs);
				if(isset($id)) $smarty->assign('subnav_location', 'subnav_edit_page');
				else $smarty->assign('subnav_location', 'subnav_add_page');
				$smarty->assign('errors', $errors);
				$smarty->assign('action', 'edit_page');
			}
		break;
		case 'delete_page':
			$id = intval($_GET['delete_page']);
			$result = mysqli_query($connid, "SELECT id, title FROM ".$db_settings['pages_table']." WHERE id= ". $id ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			if (mysqli_num_rows($result) > 0) {
				$data = mysqli_fetch_array($result);
				$page['id'] = intval($data['id']);
				$page['title'] = $data['title'];
				$smarty->assign('page',$page);
			}
			mysqli_free_result($result);
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=pages';
			$breadcrumbs[1]['linkname'] = 'subnav_pages';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_delete_page');
		break;
		case 'delete_page_submit':
			// delete item:
			mysqli_query($connid, "DELETE FROM ".$db_settings['pages_table']." WHERE id= ". intval($_POST['id'])) or raise_error('database_error', mysqli_error($connid));
			// reorder remaining items:
			$result = mysqli_query($connid, "SELECT id FROM ".$db_settings['pages_table']." ORDER BY order_id ASC");
			$i = 1;
			while ($data = mysqli_fetch_array($result)) {
				mysqli_query($connid, "UPDATE ".$db_settings['pages_table']." SET order_id=". intval($i) ." WHERE id = ". intval($data['id']));
				$i++;
			}
			mysqli_free_result($result);
			header('Location: index.php?mode=admin&action=pages');
			exit;
		break;
		case 'move_page':
			if (isset($_GET['move_up_page'])) move_item($db_settings['pages_table'], intval($_GET['move_up_page']), 'up');
			elseif (isset($_GET['move_down_page'])) move_item($db_settings['pages_table'], intval($_GET['move_down_page']), 'down');
			header('Location: index.php?mode=admin&action=pages');
			exit;
		break;
		case 'move_smiley':
			if (isset($_GET['move_up_smiley'])) move_item($db_settings['smilies_table'], intval($_GET['move_up_smiley']), 'up');
			elseif (isset($_GET['move_down_smiley'])) move_item($db_settings['smilies_table'], intval($_GET['move_down_smiley']), 'down');
			header('Location: index.php?mode=admin&action=smilies');
			exit;
		break;
		case 'move_category':
			if (isset($_GET['move_up_category'])) move_item($db_settings['category_table'], intval($_GET['move_up_category']), 'up');
			elseif (isset($_GET['move_down_category'])) move_item($db_settings['category_table'], intval($_GET['move_down_category']), 'down');
			header('Location: index.php?mode=admin&action=categories');
			exit;
		break;
		case 'reorder':
			if (isset($_POST['pages'])) {
				$table = $db_settings['pages_table'];
				$list = $_POST['pages'];
			} elseif (isset($_POST['smilies'])) {
				$table = $db_settings['smilies_table'];
				$list = $_POST['smilies'];
			} elseif(isset($_POST['categories'])) {
				$table = $db_settings['category_table'];
				$list = $_POST['categories'];
			}
			if (isset($list) && isset($table)) {
				$list_items = explode(',', $list);
				$order_id = 1;
				foreach ($list_items as $id) {
					mysqli_query($connid, "UPDATE ".$table." SET order_id = ". intval($order_id) ." WHERE id = ". intval($id));
					++$order_id;
				}
			}
			exit;
		break;
		case 'list_uploads':
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_list_uploads');
			if ($images) {
				$smarty->assign('user_page_browse', $page_browse);
				$smarty->assign('pagination', pagination($total_pages, $page_browse['page'], 3));
				$smarty->assign('images_per_page', $page_browse['items_per_page']);
				$smarty->assign('images', $images);
				if (isset($start)) $smarty->assign('start', $start);
			}
		break;
		case 'delete_uploads':
			$breadcrumbs[0]['link'] = 'index.php?mode=admin';
			$breadcrumbs[0]['linkname'] = 'subnav_admin_area';
			$breadcrumbs[1]['link'] = 'index.php?mode=admin&amp;action=list_uploads';
			$breadcrumbs[1]['linkname'] = 'subnav_list_uploads';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_confirm_delete_uploads');
			$smarty->assign('selected_uploads', $selected_uploads);
			$smarty->assign('selected_uploads_count', $selected_uploads_count);
		break;
	}

	// show the version number of the installation
	if (isset($settings) && isset($settings['version'])) {
		$smarty->assign('installed_version_number', htmlspecialchars($settings['version']));
	}

	// Prueft, ob die Datei install/index.php noch existiert
	$smarty->assign('install_script_exists', file_exists('./install/index.php'));

	// Pruefe, ob eine neue Version zur Verfuegung steht
	$lastVersionCheck = false;
	$lastVersionURI   = false;
	$lastVersionCheck = @mysqli_query($connid, "SELECT `value` AS `version` FROM `".$db_settings['temp_infos_table']."` WHERE `name` = 'last_version_check'");
	$lastVersionURI   = @mysqli_query($connid, "SELECT `value` AS `uri` FROM `".$db_settings['temp_infos_table']."` WHERE `name` = 'last_version_uri'");

	if (($lastVersionCheck !== false && mysqli_num_rows($lastVersionCheck) == 1) && ($lastVersionURI !== false and mysqli_num_rows($lastVersionURI) == 1) && (isset($settings) && isset($settings['version']))) {
		$lastVC = mysqli_fetch_assoc($lastVersionCheck);
		$lastVU = mysqli_fetch_assoc($lastVersionURI);
		mysqli_free_result($lastVersionCheck);
		mysqli_free_result($lastVersionURI);

		if ($lastVC !== NULL) {
			$smarty->assign('latest_release_uri', $lastVU == NULL ? "https://github.com/ilosuna/mylittleforum/releases/latest" : htmlspecialchars($lastVU['uri']));
			$smarty->assign('latest_release_version', htmlspecialchars($lastVC['version']));
		}
	}
	$smarty->assign('subtemplate', 'admin.inc.tpl');
	$template = 'main.tpl';
} else {
	header("location: index.php");
	exit;
}
?>