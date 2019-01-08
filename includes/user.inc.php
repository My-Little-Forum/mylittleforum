<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

// only registered users have access to this section or user area is public:
if (isset($_SESSION[$settings['session_prefix'].'user_id']) || $settings['user_area_public'] == 1) {
	if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
	else $action = 'main';

	if (isset($_GET['user_lock'])) $action = 'user_lock';
	if (isset($_GET['show_user'])) $action = 'show_user';
	if (isset($_GET['show_posts'])) $action = 'show_posts';
	if (isset($_POST['edit_user_submit'])) $action = 'edit_userdata';
	if (isset($_POST['edit_pw_submit'])) $action = 'edit_pw_submitted';
	if (isset($_POST['edit_email_submit'])) $action = 'edit_email_submit';
	if (isset($_POST['remove_account_submit'])) $action = 'remove_account_submitted';

	if(isset($_REQUEST['id'])) $id = $_REQUEST['id'];

	switch($action) {
		case 'main':
			if (isset($_GET['search_user']) && trim($_GET['search_user']) != '') $search_user = trim($_GET['search_user']);

			// count users and pages:
			if (isset($search_user)) {
				$user_count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['userdata_table']." WHERE activate_code = '' AND lower(user_name) LIKE '%". mysqli_real_escape_string($connid, my_strtolower($search_user, $lang['charset'])) ."%'");
			} else {
				$user_count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['userdata_table']." WHERE activate_code = ''");
			}
			list($total_users) = mysqli_fetch_row($user_count_result);
			mysqli_free_result($user_count_result);
			$total_pages = ceil($total_users / $settings['users_per_page']);

			// who is online:
			if ($settings['count_users_online'] > 0) {
				$useronline_result = mysqli_query($connid, "SELECT ".$db_settings['userdata_table'].".user_name COLLATE utf8_general_ci AS user_name, ".$db_settings['useronline_table'].".user_id
					FROM ".$db_settings['useronline_table']."
					LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id = ".$db_settings['useronline_table'].".user_id
					WHERE ".$db_settings['useronline_table'].".user_id > 0
					ORDER BY user_name ASC") or raise_error('database_error', mysqli_error($connid));
				$i = 0;
				while($uid_field = mysqli_fetch_array($useronline_result)) {
					$useronline_array[] = intval($uid_field['user_id']);
					$users_online[$i]['id'] = intval($uid_field['user_id']);
					$users_online[$i]['name'] = htmlspecialchars($uid_field['user_name']);
					++$i;
				}
				mysqli_free_result($useronline_result);
			}

			if (isset($users_online)) $smarty->assign('users_online', $users_online);

			if (isset($_GET['page'])) $page = intval($_GET['page']); else $page = 1;
			if ($page > $total_pages) $page = $total_pages;
			if ($page < 1) $page = 1;

			if (isset($_GET['order'])) $order = $_GET['order']; else $order='user_name';
			if ($order != 'user_id' && $order != 'user_name' && $order != 'user_email' && $order != 'user_type' && $order != 'registered' && $order != 'logins' && $order != 'last_login' && $order != 'user_lock' && $order != 'user_hp' && $order != 'email_contact' && $order != 'online') $order = 'user_name';
			if ($order == 'user_lock' && (empty($_SESSION[$settings['session_prefix'].'user_type']) || isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type'] < 1)) $order = 'user_name';
			if (isset($_GET['descasc'])) $descasc = $_GET['descasc']; else $descasc = "ASC";
			if ($descasc != 'DESC' && $descasc != 'ASC') $descasc = 'ASC';

			$ul = ($page - 1) * $settings['users_per_page'];

			// get userdata:
			$category_query_add = '';

			if (isset($search_user)) {
				$result = @mysqli_query($connid, "SELECT ".$db_settings['userdata_table'].".user_id, user_name COLLATE utf8_general_ci AS user_name, user_type, user_email, email_contact, user_hp, user_lock
					FROM ".$db_settings['userdata_table']."
					WHERE activate_code = ''". $category_query_add ." AND lower(user_name) LIKE '%". mysqli_real_escape_string($connid, my_strtolower($search_user, $lang['charset'])) ."%'
					ORDER BY ". $order ." ". $descasc ." LIMIT ". intval($ul) .", ". intval($settings['users_per_page'])) or raise_error('database_error', mysqli_error($connid));
			} else {
				$result = @mysqli_query($connid, "SELECT ".$db_settings['userdata_table'].".user_id, user_name COLLATE utf8_general_ci AS user_name, user_type, user_email, email_contact, user_hp, user_lock
					FROM ".$db_settings['userdata_table']."
					WHERE activate_code = ''". $category_query_add ."
					ORDER BY ". $order ." ". $descasc ." LIMIT ". intval($ul) .", ". intval($settings['users_per_page'])) or raise_error('database_error', mysqli_error($connid));
			}

			$i = 0;
			while ($row = mysqli_fetch_array($result)) {
				$userdata[$i]['user_id'] = intval($row['user_id']);
				$userdata[$i]['user_name'] = htmlspecialchars($row['user_name']);
				if ($row['email_contact'] == 1) $userdata[$i]['user_email'] = TRUE;
				$userdata[$i]['user_hp'] = htmlspecialchars($row['user_hp']);
				if (trim($userdata[$i]['user_hp']) != '') {
					$userdata[$i]['user_hp'] = add_http_if_no_protocol($userdata[$i]['user_hp']);
				}
				$userdata[$i]['user_type'] = intval($row['user_type']);
				$userdata[$i]['user_lock'] = $row['user_lock'];
				$i++;
			}
			mysqli_free_result($result);

			$smarty->assign('pagination', pagination($total_pages, $page, 3));

			if (isset($userdata)) $smarty->assign('userdata', $userdata);
			$smarty->assign('total_users',$total_users);

			if (isset($search_user)) {
				$smarty->assign('search_user', htmlspecialchars($search_user));
				$smarty->assign('search_user_encoded', urlencode($search_user));
			}
			$smarty->assign('order', $order);
			$smarty->assign('descasc', $descasc);
			$smarty->assign('ul', $ul);
			$smarty->assign('page', $page);
			$smarty->assign('subnav_location', 'subnav_userarea');
			$smarty->assign('subtemplate', 'user.inc.tpl');
			$template = 'main.tpl';
		break;
		case 'user_lock':
			if (isset($_GET['page'])) {
				$page = intval($_GET['page']);
				if ($page < 1) $page = 1;
				$order = urlencode($_GET['order']);
				$descasc = urlencode($_GET['descasc']);
				if (isset($_GET['search_user'])) $search_user_q = '&search_user='.urlencode($_GET['search_user']);
				else $search_user_q = '';
			}
			if (isset($_SESSION[$settings['session_prefix'].'user_type']) && ($_SESSION[$settings['session_prefix'].'user_type'] == 1 || $_SESSION[$settings['session_prefix'].'user_type'] == 2)) {
				$lock_result = @mysqli_query($connid, "SELECT user_type, user_lock FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_GET['user_lock']) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				$field = mysqli_fetch_array($lock_result);
				mysqli_free_result($lock_result);
				if ($field['user_type'] == 0) {
					if ($field['user_lock'] == 0) $new_lock = 1; else $new_lock = 0;
					@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET user_lock = ".$new_lock.", last_login = last_login, registered = registered WHERE user_id = ". intval($_GET['user_lock']) ." LIMIT 1");
				}
			}
			if (isset($_GET['page'])) header('Location: index.php?mode=user'.$search_user_q.'&page='.$page.'&order='.$order.'&descasc='.$descasc);
			else header('Location: index.php?mode=user&show_user='.intval($_GET['user_lock']));
			exit;
		break;
		case 'show_user':
			$id = intval($_GET['show_user']);

			$result = mysqli_query($connid, "SELECT user_id, user_type, user_name, user_real_name, gender, birthday, user_email, email_contact, user_hp, user_location, profile, cache_profile, logins, UNIX_TIMESTAMP(registered) AS registered, UNIX_TIMESTAMP(registered + INTERVAL ".$time_difference." MINUTE) AS user_registered, UNIX_TIMESTAMP(last_login + INTERVAL ".$time_difference." MINUTE) AS user_last_login, user_lock
				FROM ".$db_settings['userdata_table']."
				LEFT JOIN ".$db_settings['userdata_cache_table']." ON ".$db_settings['userdata_cache_table'].".cache_id = ".$db_settings['userdata_table'].".user_id
				WHERE user_id = ". intval($id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));

			if (mysqli_num_rows($result) == 1) {
				$row = mysqli_fetch_array($result);
				$user_name = $row['user_name'];

				// count postings:
				$count_postings_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE user_id = ". intval($id));
				list($postings) = mysqli_fetch_row($count_postings_result);
				mysqli_free_result($count_postings_result);
				// last posting:
				if ($categories == false) $result = mysqli_query($connid, "SELECT id, subject, UNIX_TIMESTAMP(time + INTERVAL ".$time_difference." MINUTE) AS disp_time FROM ".$db_settings['forum_table']." WHERE user_id = ". intval($id) ." ORDER BY time DESC LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				else $result = mysqli_query($connid, "SELECT id, subject, UNIX_TIMESTAMP(time + INTERVAL ".$time_difference." MINUTE) AS disp_time FROM ".$db_settings['forum_table']." WHERE user_id = ". intval($id) ." AND category IN (". $category_ids_query .") ORDER BY time DESC LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				$last_posting = mysqli_fetch_array($result);
				mysqli_free_result($result);

				$year = my_substr($row['birthday'], 0, 4, $lang['charset']);
				$month = my_substr($row['birthday'], 5, 2, $lang['charset']);
				$day = my_substr($row['birthday'], 8, 2, $lang['charset']);

				$ystr = strrev(intval(strftime("%Y%m%d")) - intval($year.$month.$day));
				$years = intval(strrev(my_substr($ystr, 4, my_strlen($ystr, $lang['charset']), $lang['charset'])));

				$smarty->assign('p_user_id', intval($row['user_id']));
				$smarty->assign('user_name', htmlspecialchars($user_name));
				$smarty->assign('p_user_type', intval($row['user_type']));
				$smarty->assign('user_real_name', htmlspecialchars($row['user_real_name']));
				$smarty->assign('gender', $row['gender']);
				if ($day != 0 && $month != 0 && $year != 0) {
					$birthdate['day'] = $day;
					$birthdate['month'] = $month;
					$birthdate['year'] = $year;
					$smarty->assign('birthdate', $birthdate);
					$smarty->assign('years', $years);
				}
				if ($row['email_contact'] == 1) $smarty->assign('user_email', TRUE);
				if (trim($row['user_hp']) != '') {
					$row['user_hp'] = add_http_if_no_protocol($row['user_hp']);
				}
				$smarty->assign('user_hp', htmlspecialchars($row['user_hp']));
				$smarty->assign('user_location', htmlspecialchars($row['user_location']));
				$smarty->assign('user_registered', format_time($lang['time_format'], $row['user_registered']));
				if ($row['user_registered'] != $row['user_last_login']) $smarty->assign('user_last_login', format_time($lang['time_format'],$row['user_last_login']));
				$smarty->assign('postings', $postings);
				if ($postings > 0) $smarty->assign('postings_percent', number_format($postings / $total_postings * 100, 1));
				else $smarty->assign('postings_percent', 0);
				$smarty->assign('logins', intval($row['logins']));
				$days_registered = (TIMESTAMP - $row['registered']) / 86400;
				if ($days_registered < 1) $days_registered = 1;
				$smarty->assign('logins_per_day', number_format($row['logins'] / $days_registered, 2));
				$smarty->assign('postings_per_day', number_format($postings / $days_registered, 2));
				$smarty->assign('last_posting_id', intval($last_posting['id']));
				$smarty->assign('last_posting_time', $last_posting['disp_time']);
				$smarty->assign('last_posting_subject', htmlspecialchars($last_posting['subject']));

				if ($settings['avatars']>0) {
					$avatarInfo = getAvatar($id);
					$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
					if (isset($avatar) && $avatar['image'] !== false) {
						$image_info = getimagesize($avatar['image']);
						$avatar['width']  = $image_info[0];
						$avatar['height'] = $image_info[1];
						$smarty->assign('avatar', $avatar);
					}
				}

				if ($row['profile'] != '' && $row['cache_profile'] == '') {
					// no cached profile so parse it and cache it:
					$profile = html_format($row['profile']);

					// check if there's already a cached record for this user_id
					list($row_count) = @mysqli_fetch_row(mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['userdata_cache_table']." WHERE cache_id = ". intval($row['user_id'])));
					if ($row_count == 1) {
						// there's already a record (cached signature) so update it:
						@mysqli_query($connid, "UPDATE ".$db_settings['userdata_cache_table']." SET cache_profile = '". mysqli_real_escape_string($connid, $profile) ."' WHERE cache_id = ". intval($row['user_id']));
					} else {
						// prevent double entries (probably not really necessary because we already counted the records):
						@mysqli_query($connid, "DELETE FROM ".$db_settings['userdata_cache_table']." WHERE cache_id = ". intval($row['user_id']));
						// insert cached profile:
						@mysqli_query($connid, "INSERT INTO ".$db_settings['userdata_cache_table']." (cache_id, cache_signature, cache_profile) VALUES (". intval($row['user_id']) .",'','". mysqli_real_escape_string($connid, $profile) ."')");
					}
				} elseif($row['profile'] == '') {
					$profile = '';
				} else {
					// there's already a cached profile so just take it without any parsing:
					$profile = $row['cache_profile'];
				}

				$smarty->assign('profile', $profile);
				if ($row['user_lock'] == 1) $smarty->assign('user_is_locked', true);
				else $smarty->assign('user_is_locked', false);
				$breadcrumbs[0]['link'] = 'index.php?mode=user';
				$breadcrumbs[0]['linkname'] = 'subnav_userarea';
				$smarty->assign('breadcrumbs', $breadcrumbs);
				$smarty->assign('subnav_location', 'subnav_userarea_show_user');
				$smarty->assign('subnav_location_var', htmlspecialchars($user_name));
			} else {
				$subnav_link = array('mode'=>'index', 'title'=>'forum_index_link_title', 'name'=>'forum_index_link');
				$smarty->assign('subnav_link', $subnav_link);
			}
			$smarty->assign('subtemplate', 'user_profile.inc.tpl');
			$template = 'main.tpl';
		break;
		case 'show_posts':
			$id = intval($_GET['id']);
			$result = mysqli_query($connid, "SELECT user_id, user_name
				FROM ".$db_settings['userdata_table']."
				WHERE user_id = ". intval($id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			$row = mysqli_fetch_array($result);
			mysqli_free_result($result);
			$user_name = $row['user_name'];

			// count postings:
			if ($categories == false) $count_postings_result = @mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE user_id = ". intval($id));
			else $count_postings_result = @mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE user_id = ". intval($id) ." AND category IN (". $category_ids_query .")");
			list($user_postings_count) = mysqli_fetch_row($count_postings_result);
			mysqli_free_result($count_postings_result);

			$total_pages = ceil($user_postings_count / $settings['search_results_per_page']);
			if (isset($_GET['page'])) $page = intval($_GET['page']); else $page = 1;
			if ($page < 1) $page = 1;
			if ($page > $total_pages) $page = $total_pages;
			$ul = ($page - 1) * $settings['search_results_per_page'];
			$smarty->assign('pagination', pagination($total_pages, $page, 3));

			if ($user_postings_count > 0) {
				if ($categories == false) $result = @mysqli_query($connid, "SELECT id, pid, tid, user_id, UNIX_TIMESTAMP(time) AS time, UNIX_TIMESTAMP(time + INTERVAL ".$time_difference." MINUTE) AS disp_time, UNIX_TIMESTAMP(last_reply) AS last_reply, subject, category, marked, sticky FROM ".$db_settings['forum_table']." WHERE user_id = ". intval($id) ." ORDER BY time DESC LIMIT ". intval($ul) .", ". intval($settings['search_results_per_page']));
				else $result = @mysqli_query($connid, "SELECT id, pid, tid, user_id, UNIX_TIMESTAMP(time) AS time, UNIX_TIMESTAMP(time + INTERVAL ".$time_difference." MINUTE) AS disp_time, UNIX_TIMESTAMP(last_reply) AS last_reply, subject, category, marked, sticky FROM ".$db_settings['forum_table']." WHERE user_id = ". intval($id) ." AND category IN (". $category_ids_query .") ORDER BY time DESC LIMIT ". intval($ul) .", ". intval($settings['search_results_per_page']));
				$i = 0;
				while ($row = mysqli_fetch_array($result)) {
					$user_postings_data[$i]['id'] = intval($row['id']);
					$user_postings_data[$i]['pid'] = intval($row['pid']);
					$user_postings_data[$i]['name'] = htmlspecialchars($user_name);
					$user_postings_data[$i]['subject'] = htmlspecialchars($row['subject']);
					$user_postings_data[$i]['disp_time'] = $row['disp_time'];
					if (isset($categories[$row['category']]) && $categories[$row['category']] != '') {
						$user_postings_data[$i]['category'] = $row["category"];
						$user_postings_data[$i]['category_name'] = $categories[$row["category"]];
					}
					$i++;
				}
				mysqli_free_result($result);
			}
			if (isset($user_postings_data)) $smarty->assign('user_postings_data', $user_postings_data);
			$smarty->assign('user_postings_count', $user_postings_count);
			$smarty->assign('action', 'show_posts');
			$smarty->assign('id', $id);
			$breadcrumbs[0]['link'] = 'index.php?mode=user';
			$breadcrumbs[0]['linkname'] = 'subnav_userarea';
			$smarty->assign('breadcrumbs', $breadcrumbs);
			$smarty->assign('subnav_location', 'subnav_userarea_show_posts');
			$smarty->assign('subnav_location_var', htmlspecialchars($user_name));
			$smarty->assign('subtemplate', 'user_postings.inc.tpl');
			$template = 'main.tpl';
		break;
		case 'edit_profile':
			if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
				$id = $_SESSION[$settings['session_prefix'].'user_id'];
				$result = mysqli_query($connid, "SELECT user_id, user_name, user_real_name, gender, birthday, user_email, email_contact, user_hp, user_location, signature, profile, new_posting_notification, new_user_notification, auto_login_code, language, time_zone, time_difference, theme FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				$row = mysqli_fetch_array($result);
				mysqli_free_result($result);
				if (trim($row['birthday']) == '' || $row['birthday'] == '0000-00-00') $user_birthday = '';
				else {
					$year = my_substr($row['birthday'], 0, 4, $lang['charset']);
					$month = my_substr($row['birthday'], 5, 2, $lang['charset']);
					$day = my_substr($row['birthday'], 8, 2, $lang['charset']);
					$user_birthday = $year.'-'.$month.'-'.$day;
				}

				if (isset($category_selection)) $smarty->assign('category_selection', $category_selection);

				// time zones:
				if (function_exists('date_default_timezone_set') && $time_zones = get_timezones()) {
					$smarty->assign('user_time_zone', htmlspecialchars($row['time_zone']));
					$smarty->assign('time_zones', $time_zones);
					if (!empty($settings['time_zone'])) $smarty->assign('default_time_zone', $settings['time_zone']);
				}

				$languages = get_languages(true);
				if (isset($languages) && count($languages) > 1) {
					$smarty->assign('user_language', htmlspecialchars($row['language']));
					$smarty->assign('languages', $languages);
					foreach ($languages as $l) {
						if ($l['identifier'] == $settings['language_file']) {
							$default_language = $l['title'];
							$smarty->assign('default_language', $default_language);
							break;
						}
					}
				}

				$themes = get_themes(true);
				if (isset($themes) && count($themes) > 1) {
					$smarty->assign('user_theme', htmlspecialchars($row['theme']));
					$smarty->assign('themes', $themes);
					foreach ($themes as $t) {
						if ($t['identifier'] == $settings['theme']) {
							$default_theme = $t['title'];
							$smarty->assign('default_theme', $default_theme);
							break;
						}
					}
				}

				if ($row['time_difference'] < 0) $time_difference_hours = ceil($row['time_difference'] / 60);
				else $time_difference_hours = floor($row['time_difference'] / 60);
				$time_difference_minutes = abs($row['time_difference'] - $time_difference_hours * 60);
				if ($time_difference_minutes < 10) $time_difference_minutes = '0'.$time_difference_minutes;
				if (intval($row['time_difference']) > 0) $user_time_difference = '+'.$time_difference_hours;
				else $user_time_difference = $time_difference_hours;
				if ($time_difference_minutes > 0) $user_time_difference .= ':'.$time_difference_minutes;
				$smarty->assign('user_time_difference', $user_time_difference);

				if (isset($_GET['msg'])) $smarty->assign('msg', htmlspecialchars($_GET['msg']));
				$smarty->assign('user_name', htmlspecialchars($row['user_name']));
				$smarty->assign('user_real_name', htmlspecialchars($row['user_real_name']));
				$smarty->assign('user_gender', $row['gender']);
				$smarty->assign('user_birthday', $user_birthday);
				$smarty->assign('user_email', htmlspecialchars($row['user_email']));
				$smarty->assign('email_contact', $row['email_contact']);
				$smarty->assign('user_hp', htmlspecialchars($row['user_hp']));
				$smarty->assign('user_location', htmlspecialchars($row['user_location']));
				$profile = htmlspecialchars($row['profile']);
				$smarty->assign('profile', htmlspecialchars($row['profile']));
				$smarty->assign('signature', htmlspecialchars($row['signature']));
				if ($row['auto_login_code'] != '') $smarty->assign('auto_login', 1);
				else $smarty->assign('auto_login', 0);

				if($settings['avatars'] > 0) {
					$avatarInfo = getAvatar($_SESSION[$settings['session_prefix'].'user_id']);
					$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
					if (isset($avatar) && $avatar['image'] !== false) {
						$image_info = getimagesize($avatar['image']);
						$avatar['width'] = $image_info[0];
						$avatar['height'] = $image_info[1];
						$smarty->assign('avatar', $avatar);
					}
				}

				if ($_SESSION[$settings['session_prefix'].'user_type'] == 1 || $_SESSION[$settings['session_prefix'].'user_type'] == 2) {
					$smarty->assign('new_posting_notification', $row['new_posting_notification']);
					$smarty->assign('new_user_notification', $row['new_user_notification']);
				}
				$breadcrumbs[0]['link'] = 'index.php?mode=user';
				$breadcrumbs[0]['linkname'] = 'subnav_userarea';
				$smarty->assign('breadcrumbs', $breadcrumbs);
				$smarty->assign('subnav_location', 'subnav_userarea_edit_user');
				$smarty->assign('subtemplate', 'user_edit.inc.tpl');
				$template = 'main.tpl';
			}
		break;
		case 'edit_userdata':
			if (isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']){
				$id = $_SESSION[$settings['session_prefix'].'user_id'];
				if (empty($_POST['email_contact'])) 
					$email_contact = 0;
				else 
					$email_contact = 1;
				$user_hp = trim($_POST['user_hp']);
				$user_real_name = trim($_POST['user_real_name']);
				$user_birthday = trim($_POST['user_birthday']);
				if (isset($_POST['user_gender'])) 
					$gender = intval($_POST['user_gender']);
				else 
					$gender = 0;
				if ($gender != 0 && $gender !=1 && $gender != 2) 
					$gender = 0;
				$user_location = trim($_POST['user_location']);
				$profile = trim($_POST['profile']);
				$signature = trim($_POST['signature']);

				// time zone:
				$user_time_zone = '';
				if (isset($_POST['user_time_zone']) && $_POST['user_time_zone'] != '' && function_exists('date_default_timezone_set') && $time_zones = get_timezones()) {
					if (in_array($_POST['user_time_zone'], $time_zones)) $user_time_zone = $_POST['user_time_zone'];
				}

				// time difference:
				$user_time_difference = isset($_POST['user_time_difference']) ? trim($_POST['user_time_difference']) : '';
				if (isset($user_time_difference[0]) && $user_time_difference[0] == '-') $negative = true;
				$user_time_difference_array = explode(':', $_POST['user_time_difference']);
				$hours_difference = abs(intval($user_time_difference_array[0]));
				if ($hours_difference < -24 || $hours_difference > 24) $hours_difference = 0;
				if (isset($user_time_difference_array[1])) $minutes_difference = intval($user_time_difference_array[1]);
				if (isset($minutes_difference)) {
					if ($minutes_difference < 0 || $minutes_difference > 59) $minutes_difference = 0;
				} else {
					$minutes_difference = 0;
				}
				if (isset($negative)) {
					$user_time_difference = 0 - ($hours_difference * 60 + $minutes_difference);
				}
				else $user_time_difference = $hours_difference * 60 + $minutes_difference;

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
				if (isset($_POST['user_theme']) && trim($_POST['user_theme']) != '') {
					$themes = get_themes();
					if (isset($themes) && count($themes) > 1) {
						if (in_array($_POST['user_theme'], $themes)) {
							$user_theme = $_POST['user_theme'];
						}
					}
				}

				if (isset($_POST['user_view'])) $user_view = intval($_POST['user_view']); else $user_view = 0;
				if ($user_view != 0 && $user_view != 1 && $user_view != 2) $user_view = 0;
				if ($_SESSION[$settings['session_prefix'].'user_type'] == 1 || $_SESSION[$settings['session_prefix'].'user_type'] == 2) {
					if (isset($_POST['new_posting_notification']) && $_SESSION[$settings['session_prefix'].'user_type'] > 0) $new_posting_notification = intval($_POST['new_posting_notification']);
					else $new_posting_notification = 0;
					if ($new_posting_notification != 0 && $new_posting_notification != 1) $new_posting_notification = 0;
					if (isset($_POST['new_user_notification']) && $_SESSION[$settings['session_prefix'].'user_type'] > 0) $new_user_notification = intval($_POST['new_user_notification']);
					else $new_user_notification = 0;
					if ($new_user_notification != 0 && $new_user_notification != 1) $new_user_notification = 0;
				} else {
					$new_posting_notification = 0;
					$new_user_notification = 0;
				}

				if ($settings['autologin'] == 1 && isset($_POST['auto_login']) && intval($_POST['auto_login']) == 1) {
					$auto_login = 1;
				} else {
					$auto_login = 0;
				}

				// check posted data:
				if (my_strlen($user_hp, $lang['charset']) > $settings['hp_maxlength']) $errors[] = 'error_hp_too_long';
				if (my_strlen($user_real_name, $lang['charset']) > $settings['name_maxlength']) $errors[] = 'error_name_too_long';
				if (isset($user_hp) && $user_hp != '' && !is_valid_url($user_hp)) $errors[] = 'error_hp_wrong';

				if (isset($_POST['category_selection']) && is_array($_POST['category_selection'])) {
					$filtered_category_selection = filter_category_selection($_POST['category_selection'], $category_ids);
					if (count($filtered_category_selection) > 0) $category_selection_db = implode(',', $filtered_category_selection);
				}

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

				if (my_strlen($user_hp, $lang['charset']) > $settings['hp_maxlength']) $errors[] = 'error_hp_too_long';
				if (my_strlen($user_location, $lang['charset']) > $settings['location_maxlength']) $errors[] = 'error_location_too_long';
				$smarty->assign('profil_length', my_strlen($profile, $lang['charset']));
				if (my_strlen($profile, $lang['charset']) > $settings['profile_maxlength']) $errors[] = 'error_profile_too_long';
				$smarty->assign('signature_length', my_strlen($signature, $lang['charset']));
				if (my_strlen($signature, $lang['charset']) > $settings['signature_maxlength']) $errors[] = 'error_signature_too_long';

				// check for too long words:
				$too_long_word = too_long_word($user_real_name, $settings['name_word_maxlength']);
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

				// check for not accepted words:
				$joined_message = my_strtolower($user_real_name.' '.$user_hp.' '.$profile.' '.$signature, $lang['charset']);
				$not_accepted_words = get_not_accepted_words($joined_message);
				if ($not_accepted_words != false) {
					$not_accepted_words_listing = implode(', ', $not_accepted_words);
					if (count($not_accepted_words) == 1) {
						$smarty->assign('not_accepted_word', htmlspecialchars($not_accepted_words_listing));
						$errors[] = 'error_not_accepted_word';
					} else {
						$smarty->assign('not_accepted_words', htmlspecialchars($not_accepted_words_listing));
						$errors[] = 'error_not_accepted_words';
					}
				}

				if (isset($errors)) {
					$smarty->assign('errors', $errors);
					$result = mysqli_query($connid, "SELECT user_name, user_email FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
					$row = mysqli_fetch_array($result);
					mysqli_free_result($result);
					// timezones:
					if (function_exists('date_default_timezone_set') && $time_zones = get_timezones()) {
						$smarty->assign('time_zones', $time_zones);
						$smarty->assign('user_time_zone', htmlspecialchars($user_time_zone));
					}
					// languages:
					$languages = get_languages(true);
					if (isset($languages) && count($languages) > 1) {
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
					$smarty->assign('user_name', htmlspecialchars($row['user_name']));
					$smarty->assign('user_email', htmlspecialchars($row['user_email']));
					$smarty->assign('email_contact', $email_contact);
					$smarty->assign('user_hp', htmlspecialchars($user_hp));
					$smarty->assign('user_real_name', htmlspecialchars($user_real_name));
					$smarty->assign('user_gender', $gender);
					$smarty->assign('user_birthday', htmlspecialchars($user_birthday));
					$smarty->assign('user_location', htmlspecialchars($user_location));
					$smarty->assign('profile', htmlspecialchars($profile));
					$smarty->assign('signature', htmlspecialchars($signature));
					if (isset($_POST['user_time_difference'])) $smarty->assign('user_time_difference', htmlspecialchars($_POST['user_time_difference']));
					$smarty->assign('auto_login', $auto_login);
					$smarty->assign('new_posting_notification', $new_posting_notification);
					$smarty->assign('new_user_notification', $new_user_notification);
					if (isset($_POST['category_selection']) && is_array($_POST['category_selection'])) $smarty->assign('category_selection', $_POST['category_selection']);
					$smarty->assign('time_difference_array', $user_time_difference_array);
					$breadcrumbs[0]['link'] = 'index.php?mode=user';
					$breadcrumbs[0]['linkname'] = 'subnav_userarea';
					$smarty->assign('breadcrumbs', $breadcrumbs);
					$smarty->assign('subnav_location', 'subnav_userarea_edit_user');
					$smarty->assign('subtemplate', 'user_edit.inc.tpl');
					$template = 'main.tpl';
				} else {
					if (isset($category_selection_db)) {
						$queryUserDataEdit  = "UPDATE ".$db_settings['userdata_table']." SET email_contact = ". intval($email_contact) .", user_hp = '". mysqli_real_escape_string($connid, $user_hp) ."', user_real_name = '". mysqli_real_escape_string($connid, $user_real_name) ."', gender = ". intval($gender) .", birthday = ";
						$queryUserDataEdit .= ($birthday !== NULL) ? "'". mysqli_real_escape_string($connid, $birthday) ."'" : "NULL";
						$queryUserDataEdit .= ", user_location = '". mysqli_real_escape_string($connid, $user_location) ."', profile = '". mysqli_real_escape_string($connid, $profile) ."', signature = '". mysqli_real_escape_string($connid, $signature) ."', user_view = ".intval($user_view) .", new_posting_notification = ". intval($new_posting_notification) .", new_user_notification = ". intval($new_user_notification) .", category_selection = '". mysqli_real_escape_string($connid, $category_selection_db) ."', language = '". mysqli_real_escape_string($connid, $user_language) ."', time_zone = '". mysqli_real_escape_string($connid, $user_time_zone) ."', time_difference = ". intval($user_time_difference) .", theme = '". mysqli_real_escape_string($connid, $user_theme) ."', last_login = last_login, last_logout = last_logout, registered = registered WHERE user_id = ". intval($id);
						$_SESSION[$settings['session_prefix'].'usersettings']['category_selection'] = $filtered_category_selection;
					} else {
						$queryUserDataEdit = "UPDATE ".$db_settings['userdata_table']." SET email_contact = ". intval($email_contact) .", user_hp = '". mysqli_real_escape_string($connid, $user_hp) ."', user_real_name = '". mysqli_real_escape_string($connid, $user_real_name) ."', gender = ". intval($gender) .", birthday = ";
						$queryUserDataEdit .= ($birthday !== NULL) ? "'". mysqli_real_escape_string($connid, $birthday) ."'" : "NULL";
						$queryUserDataEdit .= ", user_location = '". mysqli_real_escape_string($connid, $user_location) ."', profile = '". mysqli_real_escape_string($connid, $profile) ."', signature = '". mysqli_real_escape_string($connid, $signature) ."', user_view = ". intval($user_view) .", new_posting_notification = ". intval($new_posting_notification) .", new_user_notification = ". intval($new_user_notification) .", category_selection = NULL, language = '". mysqli_real_escape_string($connid, $user_language) ."', time_zone = '". mysqli_real_escape_string($connid, $user_time_zone) ."', time_difference = ". intval($user_time_difference) .", theme = '". mysqli_real_escape_string($connid, $user_theme) ."', last_login = last_login, last_logout = last_logout, registered = registered WHERE user_id = ". intval($id);
						unset($_SESSION[$settings['session_prefix'].'usersettings']['category_selection']);
					}
					@mysqli_query($connid, $queryUserDataEdit);
					// auto login:
					if ($auto_login == 1) {
						$result = mysqli_query($connid, "SELECT auto_login_code FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($id) ." LIMIT 1") or raise_error('database_error',mysqli_error($connid));
						$row = mysqli_fetch_array($result);
						mysqli_free_result($result);
						if (strlen($row['auto_login_code']) != 50) {
							$auto_login_code = random_string(50);
						} else {
							$auto_login_code = $row['auto_login_code'];
						}
						$auto_login_code_cookie = $auto_login_code . intval($id);
						setcookie($settings['session_prefix'].'auto_login', $auto_login_code_cookie, TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days']));
						@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, last_logout = last_logout, registered = registered, auto_login_code = '". mysqli_real_escape_string($connid, $auto_login_code) ."' WHERE user_id = ". intval($id));
					} else {
						setcookie($settings['session_prefix'].'auto_login', '', 0);
						@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, last_logout = last_logout, registered = registered, auto_login_code = '' WHERE user_id = ". intval($id));
					}

					@mysqli_query($connid, "DELETE FROM ".$db_settings['userdata_cache_table']." WHERE cache_id = ". intval($id));
					if (!empty($user_language)) $_SESSION[$settings['session_prefix'].'usersettings']['language'] = $user_language;
					else unset($_SESSION[$settings['session_prefix'].'usersettings']['language']);
					if (!empty($user_time_zone)) $_SESSION[$settings['session_prefix'].'usersettings']['time_zone'] = $user_time_zone;
					else unset($_SESSION[$settings['session_prefix'].'usersettings']['time_zone']);
					if (!empty($user_time_difference)) $_SESSION[$settings['session_prefix'].'usersettings']['time_difference'] = intval($user_time_difference);
					else unset($_SESSION[$settings['session_prefix'].'usersettings']['time_difference']);
					if (!empty($user_theme)) $_SESSION[$settings['session_prefix'].'usersettings']['theme'] = $user_theme;
					else unset($_SESSION[$settings['session_prefix'].'usersettings']['theme']);
					header('Location: index.php?mode=user&action=edit_profile&msg=profile_saved');
					exit;
				}
			}
		break;
		case 'remove_account':
			if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
				$user_id = $_SESSION[$settings['session_prefix'].'user_id'];
				$result = mysqli_query($connid, "SELECT `user_name` FROM `".$db_settings['userdata_table']."` WHERE `user_id` = ". intval($user_id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				if (mysqli_num_rows($result) == 1) {
					$row = mysqli_fetch_array($result);
					mysqli_free_result($result);
					$smarty->assign('user_name', htmlspecialchars($row['user_name']));
					$breadcrumbs[0]['link'] = 'index.php?mode=user';
					$breadcrumbs[0]['linkname'] = 'subnav_userarea';
					$breadcrumbs[1]['link'] = 'index.php?mode=user&amp;action=edit_profile';
					$breadcrumbs[1]['linkname'] = 'subnav_userarea_edit_user';
					$smarty->assign('breadcrumbs', $breadcrumbs);
					$smarty->assign('subnav_location', 'subnav_userarea_remove_account');
					$smarty->assign('subtemplate', 'user_remove_account.inc.tpl');
					$template = 'main.tpl';
				}
			}
		break;
		case 'remove_account_submitted':
			if (isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
				$user_id = $_SESSION[$settings['session_prefix'].'user_id'];
				$result = @mysqli_query($connid, "SELECT `user_name`, `user_pw` FROM `".$db_settings['userdata_table']."` WHERE `user_id` = ". intval($user_id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				if (mysqli_num_rows($result) == 1) {
					$row = mysqli_fetch_array($result);
					mysqli_free_result($result);
					$user_name = $row['user_name'];
					// checking password
					if (isset($_POST['user_password']) && is_pw_correct($_POST['user_password'], $row['user_pw'])) {
						// set "edited_by" in own edited postings to 0:
						@mysqli_query($connid, "UPDATE `".$db_settings['forum_table']."` SET `edited_by` = 0 WHERE `edited_by` = ". intval($user_id));
						// save user name in forum table (like unregistered users) and set user_id = 0:
						@mysqli_query($connid, "UPDATE `".$db_settings['forum_table']."` SET `user_id` = 0, `name` = '". mysqli_real_escape_string($connid, $user_name) ."' WHERE `user_id` = ". intval($user_id));
	
						@mysqli_query($connid, "DELETE FROM `".$db_settings['userdata_table']."`       WHERE `user_id` = ". intval($user_id));
						@mysqli_query($connid, "DELETE FROM `".$db_settings['userdata_cache_table']."` WHERE `cache_id` = ". intval($user_id));
						@mysqli_query($connid, "DELETE FROM `".$db_settings['bookmark_table']."`       WHERE `user_id` = ". intval($user_id));
						@mysqli_query($connid, "DELETE FROM `".$db_settings['read_status_table']."`    WHERE `user_id` = ". intval($user_id));
	
						// delete avatar:
						$avatarInfo = getAvatar(intval($user_id));
						$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
						if (isset($avatar) && $avatar['image'] !== false && file_exists($avatar['image'])) {
							@chmod($avatar['image'], 0777);
							@unlink($avatar['image']);
						}
						$_SESSION[$settings['session_prefix'].'user_id'] = false;
						$_SESSION[$settings['session_prefix'].'user_name'] = '';
						$_SESSION[$settings['session_prefix'].'user_type'] = 0;
						$_SESSION['csrf_token'] = Null;
						setcookie($settings['session_prefix'].'userdata', '', 0);
						
						header('location: index.php?mode=index');
						exit;
					}
					else {
						$errors[] = 'error_pw_wrong';
						$smarty->assign('errors', $errors);
						$smarty->assign('user_name', htmlspecialchars($user_name));
						$breadcrumbs[0]['link'] = 'index.php?mode=user';
						$breadcrumbs[0]['linkname'] = 'subnav_userarea';
						$breadcrumbs[1]['link'] = 'index.php?mode=user&amp;action=edit_profile';
						$breadcrumbs[1]['linkname'] = 'subnav_userarea_edit_user';
						$smarty->assign('breadcrumbs', $breadcrumbs);
						$smarty->assign('subnav_location', 'subnav_userarea_remove_account');
						$smarty->assign('subtemplate', 'user_remove_account.inc.tpl');
						$template = 'main.tpl';
					}
				}
			}
		break;
		case 'edit_pw':
			if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
				$breadcrumbs[0]['link'] = 'index.php?mode=user';
				$breadcrumbs[0]['linkname'] = 'subnav_userarea';
				$breadcrumbs[1]['link'] = 'index.php?mode=user&amp;action=edit_profile';
				$breadcrumbs[1]['linkname'] = 'subnav_userarea_edit_user';
				$smarty->assign('breadcrumbs', $breadcrumbs);
				$smarty->assign('subnav_location', 'subnav_userarea_edit_pw');
				$smarty->assign('subtemplate', 'user_edit_pw.inc.tpl');
				$template = 'main.tpl';
			}
		break;
		case 'edit_pw_submitted':
			if (isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
				$user_id = $_SESSION[$settings['session_prefix'].'user_id'];
				$pw_result = mysqli_query($connid, "SELECT user_pw FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($user_id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				$field = mysqli_fetch_array($pw_result);
				mysqli_free_result($pw_result);
				
				if (!isset($_POST['old_pw']) || !isset($_POST['new_pw']) || trim($_POST['old_pw']) == '' || trim($_POST['new_pw']) == '')
					$errors[] = 'error_form_uncomplete';
				else {
					$old_pw = $_POST['old_pw'];
					$new_pw = $_POST['new_pw'];
					$min_new_password_length_by_restrictions = intval($settings['min_pw_digits']) + intval($settings['min_pw_lowercase_letters']) + intval($settings['min_pw_uppercase_letters']) + intval($settings['min_pw_special_characters']);
					// old password is wrong?
					if (!is_pw_correct($old_pw, $field['user_pw'])) 
						$errors[] = 'error_old_pw_wrong';
					// new password too short?
					if ($min_new_password_length_by_restrictions < intval($settings['min_pw_length']) && my_strlen($new_pw, $lang['charset']) < intval($settings['min_pw_length'])) 
						$errors[] = 'error_new_pw_too_short';
					// see: http://php.net/manual/en/regexp.reference.unicode.php
					// \p{N}                == numbers
					// [\p{Ll}\p{Lm}\p{Lo}] == lowercase, modifier, other letters
					// [\p{Lu}\p{Lt}]       == uppercase, titlecase letters
					// [\p{S}\p{P}\p{Z}]    == symbols, punctuations, separator
					// new password contains numbers?
					if ($settings['min_pw_digits'] > 0 && !preg_match("/(?=(.*\p{N}){" . intval($settings['min_pw_digits']) . ",})/u", $new_pw))
						$errors[] = 'error_new_pw_needs_digit';
					// password contains lowercase letter?
					if ($settings['min_pw_lowercase_letters'] > 0 && !preg_match("/(?=(.*[\p{Ll}\p{Lm}\p{Lo}]){" . intval($settings['min_pw_lowercase_letters']) . ",})/u", $new_pw))
						$errors[] = 'error_new_pw_needs_lowercase_letter';
					// password contains uppercase letter?
					if ($settings['min_pw_uppercase_letters'] > 0 && !preg_match("/(?=(.*[\p{Lu}\p{Lt}]){" . intval($settings['min_pw_uppercase_letters']) . ",})/u", $new_pw))
						$errors[] = 'error_new_pw_needs_uppercase_letter';
					// password contains special character?
					if ($settings['min_pw_special_characters'] > 0 && !preg_match("/(?=(.*[\p{S}\p{P}\p{Z}]){" . intval($settings['min_pw_special_characters']) . ",})/u", $new_pw))
						$errors[] = 'error_new_pw_needs_special_character';
				}
				// Update, if no errors:
				if(empty($errors)) {
					$pw_hash = generate_pw_hash($new_pw);
					$pw_update_result = mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET user_pw = '". mysqli_real_escape_string($connid, $pw_hash) ."', last_login = last_login, registered = registered WHERE user_id = ". intval($user_id));
					header('location: index.php?mode=user&action=edit_profile&msg=pw_changed');
					exit;
				} else {
					$smarty->assign('errors', $errors);
					$breadcrumbs[0]['link'] = 'index.php?mode=user';
					$breadcrumbs[0]['linkname'] = 'subnav_userarea';
					$breadcrumbs[1]['link'] = 'index.php?mode=user&amp;action=edit_profile';
					$breadcrumbs[1]['linkname'] = 'subnav_userarea_edit_user';
					$smarty->assign('breadcrumbs', $breadcrumbs);
					$smarty->assign('subnav_location', 'subnav_userarea_edit_pw');
					$smarty->assign('subtemplate', 'user_edit_pw.inc.tpl');
					$template = 'main.tpl';
				}
			}
		break;
		case 'edit_email':
			if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
				$breadcrumbs[0]['link'] = 'index.php?mode=user';
				$breadcrumbs[0]['linkname'] = 'subnav_userarea';
				$breadcrumbs[1]['link'] = 'index.php?mode=user&amp;action=edit_profile';
				$breadcrumbs[1]['linkname'] = 'subnav_userarea_edit_user';
				$smarty->assign('breadcrumbs', $breadcrumbs);
				$smarty->assign('subnav_location', 'subnav_userarea_edit_mail');
				$smarty->assign('subtemplate', 'user_edit_email.inc.tpl');
				$template = 'main.tpl';
			}
		break;
		case 'edit_email_submit':
			if (isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
				$new_email = trim($_POST['new_email']);
				$new_email_confirm = trim($_POST['new_email_confirm']);
				$pw_new_email = $_POST['pw_new_email'];
				// Check data:
				$email_result = @mysqli_query($connid, "SELECT user_id, user_name, user_pw, user_email FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id']) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
				$data = mysqli_fetch_array($email_result);
				mysqli_free_result($email_result);
				if ($pw_new_email == '' || $new_email == '') $errors[] = 'error_form_uncompl';
				if (empty($errors)) {
					if ($new_email != $new_email_confirm) $errors[] = 'error_email_confirmation';
					if (my_strlen($new_email, $lang['charset']) > $settings['email_maxlength']) $errors[] = 'error_email_too_long';
					if ($new_email == $data['user_email']) $errors[] = 'error_identic_email';
					if (!is_valid_email($new_email)) $errors[] = 'error_email_invalid';
					if (!is_pw_correct($pw_new_email, $data['user_pw'])) $errors[] = 'pw_wrong';
				}
				if (empty($errors)) {
					$smarty->configLoad($settings['language_file'], 'emails');
					$lang = $smarty->getConfigVars();
					$activate_code = random_string(20);
					$activate_code_hash = generate_pw_hash($activate_code);
					// send mail with activation key:
					$lang['edit_address_email_txt'] = str_replace("[name]", $data['user_name'], $lang['edit_address_email_txt']);
					$lang['edit_address_email_txt'] = str_replace("[activate_link]", $settings['forum_address']."index.php?mode=register&id=".$data['user_id']."&key=".$activate_code, $lang['edit_address_email_txt']);
					if (!my_mail($new_email, $lang['edit_address_email_sj'], $lang['edit_address_email_txt'])) $errors[] = 'mail_error';
					if (empty($errors)) {
						@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET user_email = '". mysqli_real_escape_string($connid, $new_email) ."', last_login = last_login, registered = registered, activate_code = '". mysqli_real_escape_string($connid, $activate_code_hash) ."' WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
						log_out($_SESSION[$settings['session_prefix'].'user_id']);
						header("Location: index.php");
						exit;
					}
				}
				if(isset($errors)) {
					$smarty->assign('new_user_email', htmlspecialchars($new_email));
					$smarty->assign('errors', $errors);
					$breadcrumbs[0]['link'] = 'index.php?mode=user';
					$breadcrumbs[0]['linkname'] = 'subnav_userarea';
					$breadcrumbs[1]['link'] = 'index.php?mode=user&amp;action=edit_profile';
					$breadcrumbs[1]['linkname'] = 'subnav_userarea_edit_user';
					$smarty->assign('breadcrumbs',$breadcrumbs);
					$smarty->assign('subnav_location', 'subnav_userarea_edit_mail');
					$smarty->assign('subtemplate', 'user_edit_email.inc.tpl');
					$template = 'main.tpl';
				}
			}
		break;
	}
} else {
	header("Location: index.php");
	exit;
}
?>
