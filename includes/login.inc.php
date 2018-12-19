<?php
if(!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

if (isset($_REQUEST['action'])) $action = $_REQUEST['action'];
if (isset($_POST['pwf_submit'])) $action = 'pw_forgotten_submitted';
if (isset($_POST['sort_of_agreement']) && $_POST['sort_of_agreement'] === 'dps_agreement') $action = 'dps_agreement';
if (isset($_POST['sort_of_agreement']) && $_POST['sort_of_agreement'] === 'tou_agreement') $action = 'tou_agreement';

// import posted or got username and password:
if (isset($_POST['username']) && trim($_POST['username']) != '') $request_username = $_POST['username'];
elseif (isset($_GET['username']) && trim($_GET['username']) != '') $request_username = $_GET['username'];
if (isset($_POST['userpw']) && trim($_POST['userpw']) != '') $request_userpw = $_POST['userpw'];
elseif (isset($_GET['userpw']) && trim($_GET['userpw']) != '') $request_userpw = $_GET['userpw'];

// look if session is active, if not: login
if (isset($_SESSION[$settings['session_prefix'].'user_id']) && empty($action)) {
	$action = "logout";
}
elseif (empty($_SESSION[$settings['session_prefix'].'user_id']) && isset($request_username) && isset($request_userpw)) {
	$action = "do_login";
}
elseif (empty($_SESSION[$settings['session_prefix'].'user_id']) && empty($action) && empty($_GET['activate'])) {
	$action = "login";
}
elseif (empty($_SESSION[$settings['session_prefix'].'user_id']) && empty($action) && isset($_GET['activate'])) {
	$action = "activate";
}

if (isset($_GET['login_message'])) $smarty->assign('login_message', $_GET['login_message']);

// clear failed logins and check if there are failed logins from this ip:
// a value greater than zero is interpreted as time in minutes.
if ($settings['temp_block_ip_after_repeated_failed_logins'] > 0) {
	@mysqli_query($connid, "DELETE FROM ".$db_settings['login_control_table']." WHERE time < (NOW()-INTERVAL (SELECT CONVERT(`value`, UNSIGNED INTEGER) FROM ".$db_settings['settings_table']." WHERE `name` = 'temp_block_ip_after_repeated_failed_logins') MINUTE)");
	$failed_logins_result = @mysqli_query($connid, "SELECT logins FROM ".$db_settings['login_control_table']." WHERE ip='". mysqli_real_escape_string($connid, $_SERVER["REMOTE_ADDR"]) ."'");
	if (mysqli_num_rows($failed_logins_result) == 1) {
		$data = mysqli_fetch_array($failed_logins_result);
		if ($data['logins'] >= 3) $action = 'ip_temporarily_blocked';
	}
	mysqli_free_result($failed_logins_result);
}

switch ($action) {
	case "do_login":
		if (isset($request_username) && isset($request_userpw)) {
			$result = mysqli_query($connid, "SELECT user_id, user_name, user_pw, user_type, UNIX_TIMESTAMP(last_login) AS last_login, UNIX_TIMESTAMP(last_logout) AS last_logout, thread_order, user_view, sidebar, fold_threads, thread_display, category_selection, auto_login_code, activate_code, language, time_zone, time_difference, theme, tou_accepted, dps_accepted FROM ".$db_settings['userdata_table']." WHERE lower(user_name) = '". mysqli_real_escape_string($connid, my_strtolower($request_username, $lang['charset'])) ."'") or raise_error('database_error', mysqli_error($connid));
			if (mysqli_num_rows($result) == 1) {
				$feld = mysqli_fetch_array($result);
				if (is_pw_correct($request_userpw, $feld['user_pw'])) {
					if (trim($feld["activate_code"]) != '') {
						header("location: index.php?mode=login&login_message=account_not_activated");
						exit;
					}

					if (isset($_POST['autologin_checked']) && isset($settings['autologin']) && $settings['autologin'] == 1) {
						if (strlen($feld['auto_login_code']) != 50) {
							$auto_login_code = random_string(50);
						} else {
							$auto_login_code = $feld['auto_login_code'];
						}
						$auto_login_code_cookie = $auto_login_code . intval($feld['user_id']);
						setcookie($settings['session_prefix'].'auto_login', $auto_login_code_cookie, TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days']));
						$save_auto_login = true;
					} else {
						setcookie($settings['session_prefix'].'auto_login', '', 0);
					}
					$user_id = $feld["user_id"];
					$user_name = $feld["user_name"];
					$user_type = $feld["user_type"];
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
						$category_selection = explode(',',$feld['category_selection']);
						$usersettings['category_selection'] = $category_selection;
					}
					if($feld['language'] != '') {
						$languages = get_languages();
						if (isset($languages) && in_array($feld['language'], $languages)) {
							$usersettings['language'] = $feld['language'];
							$language_update = $feld['language'];
						}
					}
					if (empty($language_update)) $language_update = '';

					if ($feld['theme'] != '') {
						$themes = get_themes();
						if (isset($themes) && in_array($feld['theme'], $themes)) {
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

					if (isset($read)) $read_before_logged_in = $read; // get read postings from cookie (read before logged in)

					$_SESSION[$settings['session_prefix'].'user_id'] = $user_id;
					$_SESSION[$settings['session_prefix'].'user_name'] = $user_name;
					$_SESSION[$settings['session_prefix'].'user_type'] = $user_type;
					$_SESSION[$settings['session_prefix'].'usersettings'] = $usersettings;

					if(isset($save_auto_login)) {
						@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET logins = logins + 1, last_login = NOW(), last_logout = NOW(), user_ip = '". mysqli_real_escape_string($connid, $_SERVER['REMOTE_ADDR']) ."', auto_login_code = '". mysqli_real_escape_string($connid, $auto_login_code) ."', pwf_code = '', language = '". mysqli_real_escape_string($connid, $language_update) ."', time_zone = '". mysqli_real_escape_string($connid, $time_zone_update) ."', theme = '". mysqli_real_escape_string($connid, $theme_update) ."' WHERE user_id = ". intval($user_id));
					} else {
						@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET logins = logins + 1, last_login = NOW(), last_logout = NOW(), user_ip = '". mysqli_real_escape_string($connid, $_SERVER['REMOTE_ADDR']) ."', pwf_code = '', language = '". mysqli_real_escape_string($connid, $language_update) ."', time_zone = '". mysqli_real_escape_string($connid, $time_zone_update) ."', theme = '". mysqli_real_escape_string($connid, $theme_update) ."' WHERE user_id = ".intval($user_id));
					}

					if ($db_settings['useronline_table'] != "") {
						@mysqli_query($connid, "DELETE FROM ".$db_settings['useronline_table']." WHERE ip = '". mysqli_real_escape_string($connid, $_SERVER['REMOTE_ADDR']) ."'");
					}

					if ($settings['data_privacy_agreement'] == 1 && $feld['dps_accepted'] === NULL) {
						$redir = 'index.php?mode=login&action=dps';
					} else if ($settings['terms_of_use_agreement'] == 1 && $feld['tou_accepted'] === NULL) {
						$redir = 'index.php?mode=login&action=tou';
					} else if (isset($_SESSION[$settings['session_prefix'].'last_visited_uri'])) {
						$redir = $_SESSION[$settings['session_prefix'].'last_visited_uri'];
					} else if (isset($_POST['back']) && isset($_POST['id'])) {
						$redir = 'index.php?mode='.$_POST['back'].'&id='.$_POST['id'].'&back=entry';
					} elseif (isset($_POST['back'])) {
						$redir = 'index.php?mode='.$_POST['back'];
					} elseif (isset($_POST['id'])) {
						$redir = 'index.php?id='.$_POST['id'].'&back=entry';
					} else {
						$redir = 'index.php';
					}

					header('Location: '.$redir);
					exit;
				} else {
					if ($settings['temp_block_ip_after_repeated_failed_logins'] > 0) count_failed_logins();
					$action = 'login';
					$login_message = 'login_failed';
				}
			} else {
				if ($settings['temp_block_ip_after_repeated_failed_logins'] > 0) count_failed_logins();
				$action = 'login';
				$login_message = 'login_failed';
			}
		} else {
			if ($settings['temp_block_ip_after_repeated_failed_logins'] > 0) count_failed_logins();
			$action = 'login';
			$login_message = 'login_failed';
		}
	break;
	case "logout":
		log_out($_SESSION[$settings['session_prefix'].'user_id']);
		header("location: index.php");
		exit;
	break;
	case "dps":
		# the user has to accept (again) the data privacy statement
		if ($settings['data_privacy_agreement'] == 1 && isset($_SESSION[$settings['session_prefix'].'user_id'])) {
			# user is logged in and accepting of the data privacy statement is necessary
			$resultDPS = mysqli_query($connid, "SELECT dps_accepted, tou_accepted FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
			$feld = mysqli_fetch_assoc($resultDPS);
			if ($feld['dps_accepted'] === NULL) {
				# display the form for accepting the data privacy statement
				$action = 'show_dps';
			} else {
				# data privacy statement was accepted before, redirect
				if ($settings['terms_of_use_agreement'] == 1 && $feld['tou_accepted'] === NULL) {
					$redir = 'index.php?mode=login&action=tou';
				} else if (isset($_SESSION[$settings['session_prefix'].'last_visited_uri'])) {
					$redir = $_SESSION[$settings['session_prefix'].'last_visited_uri'];
				} else {
					$redir = 'index.php';
				}
				header('Location: '.$redir);
				exit;
			}
		} else {
			# redirect to the index view
			header("location: index.php");
			exit;
		}
	break;
	case "tou":
		# the user has to accept (again) the terms of use
		if ($settings['terms_of_use_agreement'] == 1 && isset($_SESSION[$settings['session_prefix'].'user_id'])) {
			# user is logged in and accepting of the terms of use agreement is necessary
			$resultTOU = mysqli_query($connid, "SELECT dps_accepted, tou_accepted FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
			$feld = mysqli_fetch_assoc($resultTOU);
			if ($feld['tou_accepted'] === NULL) {
				# display the form for accepting the terms of use agreement
				$action = 'show_tou';
			} else {
				# terms of use agreement was accepted before, redirect
				if ($settings['data_privacy_agreement'] == 1 && $feld['dps_accepted'] === NULL) {
					$redir = 'index.php?mode=login&action=dps';
				} else if (isset($_SESSION[$settings['session_prefix'].'last_visited_uri'])) {
					$redir = $_SESSION[$settings['session_prefix'].'last_visited_uri'];
				} else {
					$redir = 'index.php';
				}
				header('Location: '.$redir);
				exit;
			}
		} else {
			# redirect to the index view
			header("location: index.php");
			exit;
		}
	break;
	case "dps_agreement":
		if ($settings['data_privacy_agreement'] == 1 && isset($_POST['agreed']) && isset($_SESSION[$settings['session_prefix'].'user_id'])) {
			$resultDPS = mysqli_query($connid, "SELECT dps_accepted, tou_accepted FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
			$feld = mysqli_fetch_assoc($resultDPS);
			if ($feld['dps_accepted'] === NULL) {
				$writeDPS = mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET dps_accepted = NOW() WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
			}
			# data privacy statement got accepted, redirect
			if ($settings['terms_of_use_agreement'] == 1 && $feld['tou_accepted'] === NULL) {
				$redir = 'index.php?mode=login&action=tou';
			} else if (isset($_SESSION[$settings['session_prefix'].'last_visited_uri'])) {
				$redir = $_SESSION[$settings['session_prefix'].'last_visited_uri'];
			} else {
				$redir = 'index.php';
			}
			header('Location: '.$redir);
			exit;
		} else {
			if (isset($_SESSION[$settings['session_prefix'].'last_visited_uri'])) {
				$redir = $_SESSION[$settings['session_prefix'].'last_visited_uri'];
			} else {
				$redir = 'index.php';
			}
			header('Location: '.$redir);
			exit;
		}
	break;
	case "tou_agreement":
		if ($settings['terms_of_use_agreement'] == 1 && isset($_POST['agreed']) && isset($_SESSION[$settings['session_prefix'].'user_id'])) {
			$resultTOU = mysqli_query($connid, "SELECT dps_accepted, tou_accepted FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
			$feld = mysqli_fetch_assoc($resultTOU);
			if ($feld['tou_accepted'] === NULL) {
				$writeTOU = mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET tou_accepted = NOW() WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or raise_error('database_error', mysqli_error($connid));
			}
			# terms of use got accepted, redirect
			if ($settings['data_privacy_agreement'] == 1 && $feld['dps_accepted'] === NULL) {
				$redir = 'index.php?mode=login&action=dps';
			} else if (isset($_SESSION[$settings['session_prefix'].'last_visited_uri'])) {
				$redir = $_SESSION[$settings['session_prefix'].'last_visited_uri'];
			} else {
				$redir = 'index.php';
			}
			header('Location: '.$redir);
			exit;
		} else {
			if (isset($_SESSION[$settings['session_prefix'].'last_visited_uri'])) {
				$redir = $_SESSION[$settings['session_prefix'].'last_visited_uri'];
			} else {
				$redir = 'index.php';
			}
			header('Location: '.$redir);
			exit;
		}
	break;
	case "pw_forgotten_submitted":
		if (trim($_POST['pwf_email']) == '') $error = true;
		if (empty($error)) {
			$pwf_result = @mysqli_query($connid, "SELECT user_id, user_name, user_email FROM ".$db_settings['userdata_table']." WHERE user_email = '". mysqli_real_escape_string($connid, $_POST['pwf_email']) ."' LIMIT 1") or raise_error('database_error', mysqli_error($connid));
			if (mysqli_num_rows($pwf_result) != 1) $error = true;
			else $field = mysqli_fetch_array($pwf_result);
			mysqli_free_result($pwf_result);
		}
		if (empty($error)) {
			$pwf_code = random_string(20);
			$pwf_code_hash = generate_pw_hash($pwf_code);
			$update_result = mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, registered = registered, pwf_code = '". mysqli_real_escape_string($connid, $pwf_code_hash) ."' WHERE user_id = ". intval($field['user_id']) ." LIMIT 1");
			// send mail with activating link:
			$smarty->configLoad($settings['language_file'], 'emails');
			$lang = $smarty->getConfigVars();
			$lang['pwf_activating_email_txt'] = str_replace("[name]", $field["user_name"], $lang['pwf_activating_email_txt']);
			$lang['pwf_activating_email_txt'] = str_replace("[forum_address]", $settings['forum_address'], $lang['pwf_activating_email_txt']);
			$lang['pwf_activating_email_txt'] = str_replace("[activating_link]", $settings['forum_address'].basename($_SERVER['PHP_SELF'])."?mode=login&activate=".$field["user_id"]."&code=".$pwf_code, $lang['pwf_activating_email_txt']);

			if (my_mail($field["user_email"], $lang['pwf_activating_email_sj'], $lang['pwf_activating_email_txt'])) {
				header("location: index.php?mode=login&login_message=mail_sent");
				exit;
			} else {
				header("Location: index.php?mode=login&login_message=mail_error");
				exit;
			}
		}
		header("Location: index.php?mode=login&login_message=pwf_failed");
		exit;
	break;
	case "activate":
		if (isset($_GET['activate']) && trim($_GET['activate']) != "" && isset($_GET['code']) && trim($_GET['code']) != "") {
			$pwf_result = mysqli_query($connid, "SELECT user_id, user_name, user_email, pwf_code FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_GET["activate"]));
			if (!$pwf_result) raise_error('database_error', mysqli_error($connid));
			$field = mysqli_fetch_array($pwf_result);
			mysqli_free_result($pwf_result);
			if (trim($field['pwf_code']) != '' && $field['user_id'] == $_GET['activate'] && is_pw_correct($_GET['code'],$field['pwf_code'])) {
				// generate new password:
				if ($settings['min_pw_length'] < 8) $pwl = 8;
				else $pwl = $settings['min_pw_length'];
				$new_pw = random_string($pwl);
				$pw_hash = generate_pw_hash($new_pw);
				$update_result = mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, registered = registered, user_pw = '". mysqli_real_escape_string($connid, $pw_hash) ."', pwf_code = '' WHERE user_id = ". intval($field["user_id"]) ." LIMIT 1");

				// send new password:
				$smarty->configLoad($settings['language_file'], 'emails');
				$lang = $smarty->getConfigVars();

				$lang['new_pw_email_txt'] = str_replace("[name]", $field['user_name'], $lang['new_pw_email_txt']);
				$lang['new_pw_email_txt'] = str_replace("[password]", $new_pw, $lang['new_pw_email_txt']);
				$lang['new_pw_email_txt'] = str_replace("[login_link]", $settings['forum_address'].basename($_SERVER['PHP_SELF'])."?mode=login&username=". urlencode($field['user_name']) ."&userpw=".$new_pw, $lang['new_pw_email_txt']);
				$lang['new_pw_email_txt'] = $lang['new_pw_email_txt'];

				if (my_mail($field['user_email'], $lang['new_pw_email_sj'], $lang['new_pw_email_txt'])) {
					header("location: index.php?mode=login&login_message=pw_sent");
					exit;
				} else {
					echo $lang['mail_error'];
					exit;
				}
			} else {
				header("location: index.php?mode=login&login_message=code_invalid");
				exit;
			}
		} else {
			header("location: index.php?mode=login&login_message=code_invalid");
			exit;
		}
	break;
}

$smarty->assign('action',$action);

switch($action) {
	case "login":
		$smarty->assign('subnav_location', 'subnav_login');
		$smarty->assign('subtemplate', 'login.inc.tpl');
		if (isset($login_message)) $smarty->assign('login_message', $login_message);
		if (isset($_REQUEST['id'])) $smarty->assign('id', intval($_REQUEST['id']));
		if (isset($_REQUEST['back'])) $smarty->assign('back', htmlspecialchars($_REQUEST['back']));
		$template = 'main.tpl';
	break;
	case "pw_forgotten":
		$breadcrumbs[0]['link'] = 'index.php?mode=login';
		$breadcrumbs[0]['linkname'] = 'subnav_login';
		$smarty->assign('breadcrumbs', $breadcrumbs);
		$smarty->assign('subnav_location', 'subnav_pw_forgotten');
		$smarty->assign('subtemplate', 'login_pw_forgotten.inc.tpl');
		$template = 'main.tpl';
	break;
	case "ip_temporarily_blocked":
		$smarty->assign('ip_temporarily_blocked', true);
		$smarty->assign('subnav_location', 'subnav_login');
		$smarty->assign('subtemplate', 'login.inc.tpl');
		$template = 'main.tpl';
	break;
	case "show_dps":
		$smarty->assign('show_dps_page', true);
		$smarty->assign('subnav_location', 'subnav_accept_dps');
		$smarty->assign('subtemplate', 'user_agreement.inc.tpl');
		$template = 'main.tpl';
	break;
	case "show_tou":
		$smarty->assign('show_tou_page', true);
		$smarty->assign('subnav_location', 'subnav_accept_tou');
		$smarty->assign('subtemplate', 'user_agreement.inc.tpl');
		$template = 'main.tpl';
	break;
}
?>
