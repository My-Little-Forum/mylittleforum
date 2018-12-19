<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

if (empty($_SESSION[$settings['session_prefix'].'user_id']) && isset($_COOKIE[$settings['session_prefix'].'auto_login']) && isset($settings['autologin']) && $settings['autologin'] == 1) {
	$auto_login_code = substr($_COOKIE[$settings['session_prefix'].'auto_login'], 0, 50);
	$auto_login_id = intval(substr($_COOKIE[$settings['session_prefix'].'auto_login'], 50));
	if (isset($auto_login_id) && $auto_login_id > 0 && isset($auto_login_code) && trim($auto_login_code) != '') {
		$result = mysqli_query($connid, "SELECT user_id, user_name, user_pw, user_type, UNIX_TIMESTAMP(last_login) AS last_login, UNIX_TIMESTAMP(last_logout) AS last_logout, thread_order, user_view, sidebar, fold_threads, thread_display, category_selection, auto_login_code, activate_code, language, time_zone, time_difference, theme FROM ". $db_settings['userdata_table'] ." WHERE user_id = ". intval($auto_login_id)) or raise_error('database_error', mysqli_error($connid));
		if (mysqli_num_rows($result) == 1) {
			$feld = mysqli_fetch_array($result);
			if (strlen($feld['auto_login_code']) == 50 && $auto_login_code == $feld['auto_login_code'] && trim($feld['activate_code'] == '')) {
				$user_id = $feld['user_id'];
				$user_name = $feld['user_name'];
				$user_type = $feld['user_type'];
				$usersettings['newtime'] = $feld['last_logout'];
				$usersettings['user_view'] = $feld['user_view'];
				$usersettings['thread_order'] = $feld['thread_order'];
				$usersettings['sidebar'] = $feld['sidebar'];
				$usersettings['fold_threads'] = $feld['fold_threads'];
				$usersettings['thread_display'] = $feld['thread_display'];
				$usersettings['page'] = 1;
				$usersettings['category'] = 0;
				$usersettings['latest_postings'] = 1;

				if (!is_null($feld['category_selection'])) {
					$category_selection = explode(',', $feld['category_selection']);
					$usersettings['category_selection'] = $category_selection;
				}

				if ($feld['language'] != '') {
					$languages = get_languages();
					if (isset($languages) && in_array($feld['language'], $languages)) {
						$usersettings['language'] = $feld['language'];
						$language_update = $feld['language'];
					}
				}
				if (empty($language_update)) $language_update = '';

				if ($feld['theme'] != '') {
					$themes = get_themes();
					if(isset($themes) && in_array($feld['theme'], $themes)) {
						$usersettings['theme'] = $feld['theme'];
						$theme_update = $feld['theme'];
					}
				}
				if (empty($theme_update)) $theme_update = '';

				if ($feld['time_zone'] != '') {
					if (function_exists('date_default_timezone_set') && $time_zones = get_timezones()) {
						if (in_array($feld['time_zone'], $time_zones)) {
							$usersettings['time_zone'] = $feld['time_zone'];
							$time_zone_update = $feld['time_zone'];
						}
					}
				}
				if (empty($time_zone_update)) $time_zone_update = '';

				if (!empty($feld['time_difference'])) $usersettings['time_difference'] = $feld['time_difference'];

				$_SESSION[$settings['session_prefix'].'user_id'] = $user_id;
				$_SESSION[$settings['session_prefix'].'user_name'] = $user_name;
				$_SESSION[$settings['session_prefix'].'user_type'] = $user_type;
				$_SESSION[$settings['session_prefix'].'usersettings'] = $usersettings;

				@mysqli_query($connid, "UPDATE ". $db_settings['userdata_table'] ." SET logins=logins+1, last_login=NOW(), last_logout=NOW(), user_ip='". mysqli_real_escape_string($connid, $_SERVER['REMOTE_ADDR']) ."', pwf_code='', language='". mysqli_real_escape_string($connid, $language_update) ."', time_zone='". mysqli_real_escape_string($connid, $time_zone_update) ."', theme='". mysqli_real_escape_string($connid, $theme_update) ."' WHERE user_id=". intval($user_id));

				setcookie($settings['session_prefix'].'auto_login', $_COOKIE[$settings['session_prefix'].'auto_login'], TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days']));
				if ($db_settings['useronline_table'] != "") {
					@mysqli_query($connid, "DELETE FROM ". $db_settings['useronline_table'] ." WHERE ip = '". mysqli_real_escape_string($connid, $_SERVER['REMOTE_ADDR']) ."'");
				}
			} else {
				if ($settings['temp_block_ip_after_repeated_failed_logins'] > 0) count_failed_logins();
				setcookie($settings['session_prefix'].'auto_login', '', 0);
			}
		} else {
			if ($settings['temp_block_ip_after_repeated_failed_logins'] > 0) count_failed_logins();
			setcookie($settings['session_prefix'].'auto_login', '', 0);
		}
	} else {
		if($settings['temp_block_ip_after_repeated_failed_logins'] > 0) count_failed_logins();
		setcookie($settings['session_prefix'].'auto_login','',0);
	}
}
