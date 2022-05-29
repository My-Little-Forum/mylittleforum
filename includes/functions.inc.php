<?php
if(!defined('IN_INDEX'))
 {
  header('Location: ../index.php');
  exit;
 }

/**
 * connects to the MySQL database
 *
 * @param string $host
 * @param string $user
 * @param string $pw
 * @param string $db
 * @return resource
 */
function connect_db($host, $user, $pw, $db) {
	if (empty($db))
		raise_error('mysql_connect', mysqli_connect_error());
	$connid = @mysqli_connect($host, $user, $pw) or raise_error('mysql_connect', mysqli_connect_error());
	@mysqli_select_db($connid, $db) or raise_error('mysql_select_db', mysqli_error($connid));
	@mysqli_query($connid, 'SET NAMES utf8mb4');
	return $connid;
}

/**
 * logs a user out, saves log out time and removes user from user online table
 *
 * @param int $user_id
 * @param string $mode
 */
function log_out($user_id,$mode='')
 {
  global $connid, $settings, $db_settings;
  if(isset($_SESSION[$settings['session_prefix'].'usersettings']['newtime'])) setcookie($settings['session_prefix'].'last_visit',$_SESSION[$settings['session_prefix'].'usersettings']['newtime'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['newtime'],$_SESSION[$settings['session_prefix'].'usersettings']['newtime']+(3600*24*$settings['cookie_validity_days']));
  session_destroy();
  $update_result = @mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login=last_login, last_logout=NOW(), registered=registered, inactivity_notification = FALSE WHERE user_id=".intval($user_id)); // auto_login_code=''
  setcookie($settings['session_prefix'].'auto_login','',0);
  if($db_settings['useronline_table'] != '') @mysqli_query($connid, "DELETE FROM ".$db_settings['useronline_table']." WHERE ip = 'uid_".intval($user_id)."'");
  if($mode!='') header('Location: index.php?mode='.$mode);
  else header('Location: index.php');
  exit;
 }

/**
 * counts failed logins in order to prevent brute-force attacs
 */
function count_failed_logins()
 {
  global $db_settings, $connid;
  $result = @mysqli_query($connid, "SELECT logins FROM ".$db_settings['login_control_table']." WHERE ip='".mysqli_real_escape_string($connid, $_SERVER["REMOTE_ADDR"])."'");
  if(mysqli_num_rows($result)==1)
   {
    @mysqli_query($connid, "UPDATE ".$db_settings['login_control_table']." SET logins=logins+1 WHERE ip='".mysqli_real_escape_string($connid, $_SERVER["REMOTE_ADDR"])."'");
   }
  else
   {
    @mysqli_query($connid, "INSERT INTO ".$db_settings['login_control_table']." (time,ip,logins) VALUES (NOW(),'".mysqli_real_escape_string($connid, $_SERVER["REMOTE_ADDR"])."',1)");
   }
  mysqli_free_result($result);
 }

/**
 * fetches settings from database
 */
function get_settings()
 {
  global $connid, $db_settings;
  $qGetSettings = "SELECT name, value FROM " . $db_settings['settings_table'] . "
  UNION SELECT name, value FROM " . $db_settings['temp_infos_table'] . "
  WHERE name IN('access_permission_checks', 'version')";
  $result = mysqli_query($connid, $qGetSettings) or raise_error('database_error',mysqli_error($connid));
  while($line = mysqli_fetch_array($result))
   {
    $settings[$line['name']] = $line['value'];
   }
  mysqli_free_result($result);
  return $settings;
 }

/**
 * performs daily actions (clearing up database etc.)
 *
 * @param int $current_time
 */
function daily_actions($current_time=0) {
	global $settings, $db_settings, $connid;
	$rNDA = mysqli_query($connid, "SELECT value FROM " . $db_settings['temp_infos_table'] . " WHERE name = 'next_daily_actions'");
	$nda = mysqli_fetch_assoc($rNDA);
	if($current_time==0)
		$current_time = TIMESTAMP;
	if ($current_time > intval($nda['value'])) {
		// clear up expired auto_login_codes:
		if($settings['autologin'] == 1) {
			@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET auto_login_code='' WHERE auto_login_code != '' AND last_login < (NOW() - INTERVAL ".$settings['cookie_validity_days']." DAY)");
		}
		// remove read state on-lock and lock old threads:
		if($settings['auto_lock_old_threads'] > 0) {
			if ($settings['read_state_expiration_method'] == 3) {
				// remove read state by auto locking:
				@mysqli_query($connid, "DELETE FROM `".$db_settings['read_status_table']."` WHERE `posting_id` IN (SELECT `id` FROM `".$db_settings['forum_table']."` WHERE `last_reply` < (NOW() - INTERVAL ". intval($settings['auto_lock_old_threads']) ." DAY))");
			}
			@mysqli_query($connid, "UPDATE ".$db_settings['forum_table']." SET locked=1 WHERE locked=0 AND last_reply < (NOW() - INTERVAL ".intval($settings['auto_lock_old_threads'])." DAY)");
		}
		// delete IPs in old entries and user accounts:
		if($settings['delete_ips'] > 0) {
			@mysqli_query($connid, "UPDATE ".$db_settings['forum_table']." SET ip='' WHERE ip!='' AND time < (NOW() - INTERVAL ".intval($settings['delete_ips'])." HOUR)");
			@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET user_ip='' WHERE user_ip!='' AND last_login < (NOW() - INTERVAL ".intval($settings['delete_ips'])." HOUR)");
		}
		// remove read state by time:
		if ($settings['read_state_expiration_method'] == 2 and $settings['read_state_expiration_value'] > 0) {
			@mysqli_query($connid, "DELETE FROM `".$db_settings['read_status_table']."` WHERE `time` < (NOW() - INTERVAL ". intval($settings['read_state_expiration_value']) ." DAY)");
		}
		
		// auto delete spam:
		if ($settings['auto_delete_spam'] > 0) {
			//@mysqli_query($connid, "DELETE FROM `". $db_settings['forum_table'] ."` WHERE `time` < (NOW() - INTERVAL ". intval($settings['auto_delete_spam']) ." HOUR) AND 
			//						`id` IN (SELECT `". $db_settings['akismet_rating_table'] ."`.`eid` 
			//						FROM `". $db_settings['akismet_rating_table'] ."` 
			//						JOIN `". $db_settings['b8_rating_table'] ."` ON `". $db_settings['akismet_rating_table'] ."`.`eid` = `". $db_settings['b8_rating_table'] ."`.`eid` 
			//						WHERE `". $db_settings['akismet_rating_table'] ."`.`spam` = 1 OR `". $db_settings['b8_rating_table'] ."`.`spam` = 1); ");
			//						
			// delete dependent entries - see, e.g., delete_posting_recursive()
			//@mysqli_query($connid, "DELETE FROM `". $db_settings['entry_cache_table'] ."`    WHERE `cache_id`   NOT IN (SELECT `id` FROM `". $db_settings['forum_table'] ."`); ");									
			//@mysqli_query($connid, "DELETE FROM `". $db_settings['bookmark_table'] ."`       WHERE `posting_id` NOT IN (SELECT `id` FROM `". $db_settings['forum_table'] ."`); ");
			//@mysqli_query($connid, "DELETE FROM `". $db_settings['read_status_table'] ."`    WHERE `posting_id` NOT IN (SELECT `id` FROM `". $db_settings['forum_table'] ."`); ");
			//@mysqli_query($connid, "DELETE FROM `". $db_settings['entry_tags_table'] ."`     WHERE `bid`        NOT IN (SELECT `id` FROM `". $db_settings['forum_table'] ."`); ");
			//@mysqli_query($connid, "DELETE FROM `". $db_settings['subscriptions_table'] ."`  WHERE `eid`        NOT IN (SELECT `id` FROM `". $db_settings['forum_table'] ."`); ");
			//@mysqli_query($connid, "DELETE FROM `". $db_settings['akismet_rating_table'] ."` WHERE `eid`        NOT IN (SELECT `id` FROM `". $db_settings['forum_table'] ."`); ");
			//@mysqli_query($connid, "DELETE FROM `". $db_settings['b8_rating_table'] ."`      WHERE `eid`        NOT IN (SELECT `id` FROM `". $db_settings['forum_table'] ."`); ");
					
			$spam_ids_result = mysqli_query($connid, "SELECT `id` FROM `" . $db_settings['forum_table'] ."` WHERE `time` < (NOW() - INTERVAL ". intval($settings['auto_delete_spam']) ." HOUR) AND 
									`id` IN (SELECT `". $db_settings['akismet_rating_table'] ."`.`eid` 
									FROM `". $db_settings['akismet_rating_table'] ."` 
									JOIN `". $db_settings['b8_rating_table'] ."` ON `". $db_settings['akismet_rating_table'] ."`.`eid` = `". $db_settings['b8_rating_table'] ."`.`eid` 
									WHERE `". $db_settings['akismet_rating_table'] ."`.`spam` = 1 OR `". $db_settings['b8_rating_table'] ."`.`spam` = 1); ");
									
			while ($spam_ids_data = mysqli_fetch_array($spam_ids_result)) {
				delete_posting_recursive(intval($spam_ids_data['id']));
			}
		}

		// if possible, load new version info from Github
		if (isset($settings) && isset($settings['version'])) {
			// select stored version number from temp_infos_table (instead of the use of installed version)
			$result = @mysqli_query($connid, "SELECT `value` FROM ".$db_settings['temp_infos_table']." WHERE `name` = 'last_version_check' LIMIT 1");
			$lastCheckedVersion = '0.0';
			if ($result && mysqli_num_rows($result) > 0) {
				$data = mysqli_fetch_array($result);
				$lastCheckedVersion = is_null($data['value']) ? $lastCheckedVersion : $data['value'];
				mysqli_free_result($result);
			}
			$latestRelease = checkUpdate($lastCheckedVersion);
			if ($latestRelease !== false) {
				@mysqli_query($connid, "INSERT INTO ".$db_settings['temp_infos_table']." (`name`, `value`, `time`) VALUES ('last_version_check', '" . mysqli_real_escape_string($connid, $latestRelease->version) . "', NOW()) ON DUPLICATE KEY UPDATE `value` = '" . mysqli_real_escape_string($connid, $latestRelease->version) . "', `time` = NOW();");
				@mysqli_query($connid, "INSERT INTO ".$db_settings['temp_infos_table']." (`name`, `value`) VALUES ('last_version_uri', '" . mysqli_real_escape_string($connid, $latestRelease->uri) . "') ON DUPLICATE KEY UPDATE `value` = '" . mysqli_real_escape_string($connid, $latestRelease->uri) . "';");
			}
		}
		
		if (isset($settings) && isset($settings['notify_inactive_users']) && isset($settings['delete_inactive_users']) && $settings['notify_inactive_users'] > 0 && $settings['delete_inactive_users'] > 0)
			handleInactiveUsers();
		
		// set time of next daily actions:
		if($today_beginning = mktime(0,0,0, date("n"), date("j"), date("Y"))) {
			$time_parts = explode(':',$settings['daily_actions_time']);
			$hours = intval($time_parts[0]);
			if(isset($time_parts[1])) $minutes = intval($time_parts[1]);
			else $minutes = 0;
			$delay = $hours * 3600 + $minutes * 60;
			$next_daily_actions = $today_beginning + $delay + 86400;
		}
		else {
			$next_daily_actions = $current_time + 86400;
		}
		@mysqli_query($connid, "UPDATE " . $db_settings['temp_infos_table'] . " SET value='" . intval($next_daily_actions) . "', time = NOW() WHERE name='next_daily_actions'");
	}
}

function handleInactiveUsers() {
	global $settings, $db_settings, $lang, $connid;

	// delete inactive users
	$result = mysqli_query($connid, "SELECT `user_id`, `user_name` FROM `".$db_settings['userdata_table']."` WHERE `user_lock` = 0 AND `user_type` = 0 AND `inactivity_notification` = TRUE AND (`last_login` - (NOW() - INTERVAL ". intval($settings['notify_inactive_users']) ." YEAR - INTERVAL ". intval($settings['delete_inactive_users']) ." DAY)) < 0");
	if (!$result)
		return; // daily action no need to raise an error message

	while ($user = mysqli_fetch_array($result)) {
		$user_id      = $user['user_id'];
		$display_name = $user['user_name'];

		deleteUser($user_id, $display_name);
	}
	
	// notify inactive users; restrict the number of users to 20
	$result = mysqli_query($connid, "SELECT `user_id`, `user_name`, `user_email` FROM `".$db_settings['userdata_table']."` WHERE `user_lock` = 0 AND `user_type` = 0 AND `inactivity_notification` = FALSE AND (`last_login` - (NOW() - INTERVAL ". intval($settings['notify_inactive_users']) ." YEAR)) < 0 ORDER BY `last_login` ASC LIMIT 5;");
	if (!$result)
		return; // daily action no need to raise an error message
	
	while ($user = mysqli_fetch_array($result)) {
		$user_id = $user['user_id'];
		$name    = $user['user_name'];
		$email   = $user['user_email'];

		$emailbody = str_replace("[name]", $name, $lang['email_notify_inactive_user_text']);
		$emailbody = str_replace("[inactive_time_span]", $settings['notify_inactive_users'], $emailbody); 
		$emailbody = str_replace("[days_until_delete]", $settings['delete_inactive_users'], $emailbody); 
		$emailbody = str_replace("[forum_address]", $settings['forum_address'], $emailbody);
	
		$emailsubject = str_replace("[name]", $name, $lang['email_notify_inactive_user_subject']);

		if (my_mail($email, $emailsubject, $emailbody))
			@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET `last_login` = (NOW() - INTERVAL ". intval($settings['notify_inactive_users']) ." YEAR), `last_logout` = (NOW() - INTERVAL ". intval($settings['notify_inactive_users']) ." YEAR), `inactivity_notification` = TRUE WHERE `user_id` = " . intval($user_id) . " AND `user_lock` = 0 AND `user_type` = 0 AND `inactivity_notification` = FALSE");
	}
	mysqli_free_result($result);
}

function deleteUser($user_id = 0, $display_name = 'Unnamed') {
	global $settings, $db_settings, $connid;
	
	// set "edited_by" in own edited postings to 0:
	@mysqli_query($connid, "UPDATE `".$db_settings['forum_table']."` SET `edited_by` = 0 WHERE `edited_by` = ". intval($user_id));
	// save user name in forum table (like unregistered users) and set user_id = 0:
	@mysqli_query($connid, "UPDATE `".$db_settings['forum_table']."` SET `user_id` = 0, `name` = '". mysqli_real_escape_string($connid, $display_name) ."', email_notification = 0 WHERE `user_id` = ". intval($user_id));
	
	@mysqli_query($connid, "DELETE FROM `".$db_settings['userdata_table']."`       WHERE `user_id`  = ". intval($user_id));
	@mysqli_query($connid, "DELETE FROM `".$db_settings['userdata_cache_table']."` WHERE `cache_id` = ". intval($user_id));
	@mysqli_query($connid, "DELETE FROM `".$db_settings['bookmark_table']."`       WHERE `user_id`  = ". intval($user_id));
	@mysqli_query($connid, "DELETE FROM `".$db_settings['read_status_table']."`    WHERE `user_id`  = ". intval($user_id));
	
	// delete avatar:
	$avatarInfo = getAvatar(intval($user_id));
	$avatar['image'] = $avatarInfo === false ? false : $avatarInfo[2];
	if (isset($avatar) && $avatar['image'] !== false && file_exists($avatar['image'])) {
		@chmod($avatar['image'], 0777);
		@unlink($avatar['image']);
	}
}

/**
 * returns all available categories
 *
 * @return array
 */
function get_categories()
 {
  global $settings, $connid, $db_settings;
  $count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['category_table']);
  list($category_count) = mysqli_fetch_row($count_result);
  mysqli_free_result($count_result);

  if($category_count > 0)
   {
    if (empty($_SESSION[$settings['session_prefix'].'user_id']))
     {
      $result = mysqli_query($connid, "SELECT id, category FROM ".$db_settings['category_table']." WHERE accession = 0 ORDER BY order_id ASC");
     }
    elseif (isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type'] == 0)
     {
      $result = mysqli_query($connid, "SELECT id, category FROM ".$db_settings['category_table']." WHERE accession = 0 OR accession = 1 ORDER BY order_id ASC");
     }
    elseif (isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_SESSION[$settings['session_prefix'].'user_type']) && ($_SESSION[$settings['session_prefix'].'user_type'] == 1 || $_SESSION[$settings['session_prefix'].'user_type'] == 2))
     {
      $result = mysqli_query($connid, "SELECT id, category FROM ".$db_settings['category_table']." WHERE accession = 0 OR accession = 1 OR accession = 2 ORDER BY order_id ASC");
     }
    if(!$result) raise_error('database_error',mysqli_error($connid));
    $categories[0]='';
    while ($line = mysqli_fetch_array($result))
     {
      $categories[$line['id']] = htmlspecialchars($line['category']);
     }
    mysqli_free_result($result);
    return $categories;
   }
  else return false;
 }

/**
 * returns all available catgory ids
 *
 * @return array
 */
function get_category_ids($categories) {
	return $categories != false ? array_keys($categories) : false;
}

/**
 * filters not accessible category ids from category selection
 *
 * @return array
 */
function filter_category_selection($categories, $allowed_categories)
 {
  if(!is_array($allowed_categories)) return array();
  $filtered_categories = array();
  foreach($categories as $category)
   {
    if(in_array($category,$allowed_categories))
     {
      $filtered_categories[] = $category;
     }
   }
  return $filtered_categories;
 }

/**
 * saves the status "entry read" to the database table "mlf2_read_entries"
 * restrict the number of saved entries, if "max_read_items" > 0
 *
 * @param resource $connid, ID of the database connection
 * @param int $user_id, ID of the registered, actually acting user
 * @param int $entry_id, ID of the entry itself
 *
 * @return boolean $ret, success: true, else: false
 */
function save_read_status($connid, $user_id, $entry_id) {
	global $settings, $db_settings;
	$ret = false;
	$entry_id = intval($entry_id);
	$user_id = intval($user_id);
	if (!is_numeric($entry_id))
		return false;
	if (intval($_SESSION[$settings['session_prefix'].'user_id']) === $user_id and $entry_id > 0) {
		$ret = @mysqli_query($connid, "INSERT INTO ". $db_settings['read_status_table'] ." (user_id, posting_id, time) VALUES (". $user_id .", ". $entry_id .", NOW()) ON DUPLICATE KEY UPDATE time = NOW()");
		if ($ret && ($settings['read_state_expiration_method'] == 1 and intval($settings['read_state_expiration_value']) > 0)) {
			@mysqli_query($connid, "DELETE FROM ". $db_settings['read_status_table'] ." WHERE `user_id` = ". $user_id ." AND `posting_id` NOT IN (SELECT `posting_id` FROM (SELECT `posting_id` FROM ". $db_settings['read_status_table'] ." WHERE `user_id` = ". $user_id ." ORDER BY `time` DESC LIMIT 0," . intval($settings['read_state_expiration_value']). ") AS `dummy`)");
		}
	}
	return $ret;
}

/**
 * generates an array of thread items for the navigation within a thread
 *
 * @param array $child_array
 * @param int $id
 * @param int $current
 */
function get_thread_items($child_array, $id, $current)
 {
  global $thread_items;
  $thread_items[] = $id;  
  if(isset($child_array[$id]) && is_array($child_array[$id]))
   {
    foreach($child_array[$id] as $child)
     {
      get_thread_items($child_array, $child, $current);
     }
   }
 }

/**
 * returns an array for the page navigation
 *
 * @param int $page_count : number of pages
 * @param int $page : current page
 * @param int $browse_range
 * @param int $page
 * @param int $show_last
 * @return array
 */
function pagination($page_count,$page,$browse_range=3,$show_last=1)
 {
  if($page_count>1)
   {
    $xpagination['current'] = $page;
    if($page_count > $page)
     {
      $xpagination['next'] = $page+1;
     }
    else
     {
      $xpagination['next'] = 0;
     }
    if($page > 1)
     {
      $xpagination['previous'] = $page-1;
     }
    else
     {
      $xpagination['previous'] = 0;
     }
    $xpagination['items'][] = 1;
    if ($page > $browse_range+1) $xpagination['items'][] = 0;
    $n_range = $page-($browse_range-1);
    $p_range = $page+$browse_range;
    for($page_browse=$n_range; $page_browse<$p_range; $page_browse++)
     {
      if($page_browse > 1 && $page_browse <= $page_count) $xpagination['items'][] = $page_browse;
     }
    if($show_last)
     {
      if($page < $page_count-($browse_range)) $xpagination['items'][] = 0;
      if(!in_array($page_count,$xpagination['items'])) $xpagination['items'][] = $page_count;
     }
    return $xpagination;
   }
  return false;
 }

/**
 * replaces urls with links
 *
 * @param string $string
 * @return string
 */
function make_link($string)
 {
  $string = ' ' . $string;
  $string = preg_replace_callback("#(^|[\n ])([\w]+?://.*?[^ \"\n\r\t<]*)#is", "shorten_link", $string);
  $string = preg_replace("#(^|[\n ])((www|ftp)\.[\w\-]+\.[\w\-.\~]+(?:/[^ \"\t\n\r<]*)?)#is", "$1<a href=\"http://$2\">$2</a>", $string);
  $string = my_substr($string, 1, my_strlen($string, CHARSET), CHARSET);
  return $string;
 }

/**
 * unifies line breaks
 *
 * @param string $string
 * @return string
 */
function convertlinebreaks($string)
 {
  return preg_replace ("/\015\012|\015|\012/", "\n", $string);
 }

/**
 * strips everything except new line symbol
 *
 * @param string $string
 * @return string
 */
function bbcode_stripcontents($string)
 {
  return preg_replace ("/[^\n]/", '', $string);
 }

/**
 * makes inlinecode replacements
 */
function parse_inlinecode($string)
 {
  $string = nl2br(htmlspecialchars($string));
  $string = str_replace("  ", "&nbsp; ", $string);
  $string = str_replace("  ", " &nbsp;", $string);
  return $string;
 }

/**
 * makes inlinecode replacements
 */
function parse_monospace($string)
 {
  $string = nl2br(htmlspecialchars($string));
  $string = str_replace("  ", "&nbsp; ", $string);
  $string = str_replace("  ", " &nbsp;", $string);
  return $string;
 }

/**
 * checks if a url is valid
 *
 * @param string $url
 * @return bool
 */
function is_valid_url($url)
 {
  if(!preg_match("/^.+\..+$/", $url))
   {
    return false;
   }
  if(contains_invalid_string($url))
   {
    return false;
   }
  return true;
 }

/**
 * checks if a email address is valid
 *
 * @param string $email
 * @return bool
 */
function is_valid_email($email) {
	if (!preg_match("/^([\w\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,}|[0-9]{1,3})(\]?)$/", $email)) {
		return false;
	}
	if (contains_invalid_string($email)) {
		return false;
	}
	return true;
}

/**
 * help function for is_valid_url() and is_valid_email()
 *
 * @param string $string
 * @return bool
 */
function contains_invalid_string($string)
 {
  $invalid_strings = array('<','>','\'','"','expression(');
  if(preg_match('/^(javascript|file|data|jar)\:/i', $string)) return true;
  foreach($invalid_strings as $invalid_string) if(strpos(strtolower($string), $invalid_string)!==false) return true;
  return false;
 }

/**
 * processes BBCode links
 */
function do_bbcode_url ($action, $attributes, $content, $params, $node_object)
 {
  // 1) the code is validated
  if ($action == 'validate')
   {
    // the code has been eneterd like this: [url]http://.../[/url]
    if (!isset($attributes['default']))
     {
      // is it a valid url?
      return is_valid_url ($content);
     }
    // the code has been eneterd like this: [url=http://.../]Text[/url]
    // is it a valid url?
    return is_valid_url ($attributes['default']);
   }
  // 2) the code is outputed
  else
   {
    // the code has been eneterd like this: [url]http://.../[/url]
    if(!isset ($attributes['default'])) return '<a href="'.htmlspecialchars($content).'">'.htmlspecialchars(shorten_url($content)).'</a>';
    // the code has been eneterd like this: [url=http://.../]Text[/url]
    return '<a href="'.htmlspecialchars ($attributes['default']).'">'.$content.'</a>';
   }
 }

/**
 * processes BBCode message links
 */
function do_bbcode_msg($action, $attributes, $content, $params, $node_object)
 {
  if ($action == 'validate')
   {
    if(!isset($attributes['default']))
     {
      if(intval($content)>0) return true;
     }
    if(intval($attributes['default'])>0) return true;
   }
  else
   {
    if(!isset ($attributes['default'])) return '<a href="index.php?id='.intval($content).'" class="internal">'.intval($content).'</a>';
    return '<a href="index.php?id='.intval($attributes['default']).'" class="internal">'.$content.'</a>';
   }
 }

/**
 * processes BBCode img
 */
function do_bbcode_img($action, $attributes, $content, $params, $node_object) {
	if ($action == 'validate') {
		if (!is_valid_url($content)) {
			return false;
		} else {
			// [img]image[/img]
			if (!isset($attributes['default'])) return true;
			// [img=xxx]image[/img]
			elseif (isset($attributes['default']) && ($attributes['default'] == 'left' || $attributes['default'] == 'right' || $attributes['default'] == 'thumbnail' || $attributes['default'] == 'thumbnail-left' || $attributes['default'] == 'thumbnail-right')) return true;
			else return false;
		}
	} else {
		// [img=xxx]image[/img]
		$strSize = '';
		if (strpos($content, 'images/uploaded/', 0) !== false) {
			$size = @getimagesize($content);
			if ($size !== false && (is_numeric($size[0]) && $size[0] > 0) && (is_numeric($size[1]) && $size[1] > 0)) {
				$strSize = $size[3];
			}
		}
		if (isset($attributes['default']) && $attributes['default'] == 'left') return '<img src="'. htmlspecialchars($content) .'" class="left" alt="[image]" '. $strSize .' />';
		if (isset($attributes['default']) && $attributes['default'] == 'right') return '<img src="'. htmlspecialchars($content) .'" class="right" alt="[image]" '. $strSize .' />';
		if (isset($attributes['default']) && $attributes['default'] == 'thumbnail') return '<a rel="thumbnail" href="'. htmlspecialchars($content) .'"><img src="'.htmlspecialchars($content).'" class="thumbnail" alt="[image]" '. $strSize .' /></a>';
		if (isset($attributes['default']) && $attributes['default'] == 'thumbnail-left') return '<a rel="thumbnail" href="'. htmlspecialchars($content) .'"><img src="'. htmlspecialchars($content) .'" class="thumbnail left" alt="[image]" '. $strSize .' /></a>';
		if (isset($attributes['default']) && $attributes['default'] == 'thumbnail-right') return '<a rel="thumbnail" href="'. htmlspecialchars($content) .'"><img src="'. htmlspecialchars($content) .'" class="thumbnail right" alt="[image]" '. $strSize .' /></a>';
		// [img]image[/img]
		return '<img src="'. htmlspecialchars($content) .'" alt="[image]" '. $strSize .' />';
	}
}

/**
 * processes BBCode latex
 */
function do_bbcode_tex($action, $attributes, $content, $params, $node_object) {
	//global $settings;
	if ($action == 'validate')
		return true;
	else
		return '<span class="tex2jax_process">$' . $content . '$</span>';
}

/**
 * processes BBCode color
 */
function do_bbcode_color($action, $attributes, $content, $params, $node_object)
 {
  if($action == 'validate')
   {
    $valid_colors = array('#fff','#ccc','#999','#666','#333','#000',
                          '#fcc','#f66','#f00','#c00','#900','#600','#300',
                          '#fc9','#f96','#f90','#f60','#c60','#930','#630',
                          '#ff9','#ff6','#fc6','#fc3','#c93','#963','#633',
                          '#ffc','#ff3','#ff0','#fc0','#990','#660','#330',
                          '#9f9','#6f9','#3f3','#3c0','#090','#060','#030',
                          '#9ff','#3ff','#6cc','#0cc','#399','#366','#033',
                          '#cff','#6ff','#3cf','#36f','#33f','#009','#006',
                          '#ccf','#99f','#66c','#63f','#60c','#339','#309',
                          '#fcf','#f9f','#c6c','#c3c','#939','#636','#303',
                          'aqua','#00ffff','gray','grey','#808080','navy','#000080',
                          'silver','#c0c0c0','black','#000000','green','#008000',
                          'olive','#808000','teal','#008080','blue','#0000ff',
                          'lime','#00ff00','purple','#800080','white','#ffffff',
                          'fuchsia','#ff00ff','maroon','#800000','red','#ff0000',
                          'yellow','#ffff00');
    if(in_array(strtolower($attributes['default']),$valid_colors))
     {
      return true;
     }
    else
     {
      return false;
     }
   }
  return '<span style="color:'.htmlspecialchars($attributes['default']).';">'.$content.'</span>';
 }

/**
 * processes BBCode size
 */
function do_bbcode_size($action, $attributes, $content, $params, $node_object)
 {
  // font size definitions:
  // $size['tiny'] = 'x-small';
  $size['small'] = 'smaller';
  $size['large'] = 'large';
  // $size['huge'] = 'x-large';
  // end font size definitions

  if($action == 'validate')
   {
    if(isset($size[$attributes['default']])) return true;
    else return false;
   }
  return '<span style="font-size:'.$size[$attributes['default']].';">'.$content.'</span>';
 }

// processes BBCode links for e-mail notifications (plain text)
function do_bbcode_url_email($action, $attributes, $content, $params, $node_object)
 {
  if ($action == 'validate')
   {
    if(!isset ($attributes['default'])) return is_valid_url ($content);
    return is_valid_url ($attributes['default']);
   }
  else
   {
    if(!isset ($attributes['default'])) return $content;
    return $content.' --> '.$attributes['default'];
   }
 }

// processes BBCode msg code for e-mail notifications (plain text)
function do_bbcode_msg_email($action, $attributes, $content, $params, $node_object)
 {
  global $settings;
  if($action == 'validate')
   {
    if(!isset($attributes['default']))
     {
      if(intval($content)>0) return true;
     }
    if(intval($attributes['default'])>0) return true;
   }
  else
   {
    if(!isset ($attributes['default'])) return $settings['forum_address'].'index.php?id='.$content;
    return $content.' --> '.$settings['forum_address'].'index.php?id='.$attributes['default'];
   }
 }

/**
 * processes BBCode img for e-mail notifications (plain text)
 */
function do_bbcode_img_email ($action, $attributes, $content, $params, $node_object)
 {
  if($action == 'validate')
   {
    if(!is_valid_url($content))
     {
      return false;
     }
    else
     {
      // [img]image[/img]
      if(!isset($attributes['default'])) return true;
      // [img=xxx]image[/img]
      elseif(isset($attributes['default']) && ($attributes['default']=='left' || $attributes['default']=='right' || $attributes['default']=='thumbnail' || $attributes['default']=='thumbnail-left' || $attributes['default']=='thumbnail-right')) return true;
      else return false;
     }
   }
  else
   {
    return '['.$content.']';
   }
 }

/**
 * processes BBCode tex for e-mail notifications (plain text)
 */
function do_bbcode_tex_email($action, $attributes, $content, $params, $node_object)
 {
  global $settings;
  if ($action == 'validate')
   {
    return true;
   }
  else
   {
    return $content;
   }
 }

/**
 * processes BBCode colors for e-mail notifications (plain text)
 */
function do_bbcode_color_email($action, $attributes, $content, $params, $node_object)
 {
  if($action == 'validate') return true;
  return $content;
 }

/**
 * processes BBCode sizes for e-mail notifications (plain text)
 */
function do_bbcode_size_email($action, $attributes, $content, $params, $node_object)
 {
  if($action == 'validate') return true;
  return $content;
 }

/**
 * processes bbcode code
 */
function do_bbcode_code($action, $attributes, $content, $params, $node_object)
 {
  global $settings;
  if ($action == 'validate')
   {
    // [code]...[/code]
    // if(!isset($attributes['default'])) return true;
    // [code=lang]image[/code]
    // if(in_array(strtolower($attributes['default']),explode(',',$settings['syntax_highlighter_languages']))) return true;
    return true;
   }
  else
   {
    // [code]...[/code]
    if(!isset($attributes['default'])) return '<pre><code>'.htmlspecialchars($content).'</code></pre>';
    // [code=lang]...[/code]
    if($settings['syntax_highlighter']==1)
     {
      include_once('modules/geshi/geshi.php');
      $geshi = new GeSHi($content, $attributes['default']);
      // $geshi->set_header_type(GESHI_HEADER_NONE);
      // $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 1);
      // $geshi->set_line_style('background:#f5f5f5;', 'background:#f9f9f9;');
      $geshi->enable_keyword_links(false);
      $geshi->set_overall_class(false);
      return $geshi->parse_code();
     }
    else
     {
      return '<pre><code>'.htmlspecialchars($content).'</code></pre>';
     }
   }
 }

/**
 * removes [code] and [/code] in email texts
 */
function do_bbcode_code_email($action, $attributes, $content, $params, $node_object)
 {
  if ($action == 'validate')
   {
    return true;
   }
  else
   {
    // [code]...[/code]
    if(!isset($attributes['default'])) return $content;
    // [code=lang]...[/code]
    return $content;
   }
 }

/**
 * replaces
 *   "> hi,
 *    > how are you?
 *    I'm fine, thank you!"
 * by
 *   "[quote]hi,
 *    how are you?[/quote]
 *    I'm fine, thank you!"
  * also nested:
 *   "> > text
 *    > > text
 * is replaces by
 *   "[quote][quote]text
 *    text[/quote][/quote]"
 *
 * @param string $string
 * @return string
 */
function quote($string)
 {
  global $settings;
  $string = preg_replace ("/\015\012|\015|\012/", "\n", $string);
  $string_array = explode("\n", $string);

  // check which lines begin with how many quote symbols:
  $line_nr=0;
  foreach($string_array as $line)
   {
    $q=0; // quote symbol counter
    // if line begins with a quote symbol...
    if(my_substr($line, 0, 1, CHARSET) == $settings['quote_symbol'])
     {
      $len=strlen($line);
      for($i=0;$i<$len;$i++)
       {
        // strip quote symbols and spaces and increment quote symbol counter
        if(my_substr($line, 0, 1, CHARSET) == $settings['quote_symbol'] || my_substr($line, 0, 1, CHARSET)==' ')
         {
          if(my_substr($line, 0, 1, CHARSET) == $settings['quote_symbol']) $q++;
          $line = my_substr($line, 1, my_strlen($line, CHARSET), CHARSET);
         }
        else break; // leave the loop if reached other character than quote symbol or space
       }

     }
    // create array without quote symbols:
    $stripped_string_array[] = $line;
    // maximum 10 nested quotes:
    if($q>10) $q = 10;
    // save number of quote symbols per line:
    $quotes_array[$line_nr] = $q;
    $line_nr++;
   }

  // if you want to keep the quote symbols delete or comment the following line:
  $string_array = $stripped_string_array;

  // add [quote]...[/quote] around quotes:
  $l=0;
  foreach($quotes_array as $quotes)
   {
    if($quotes > 0)
     {
      $start_tag = '';
      $end_tag = '';
      // nest tags:
      for($q_nr=0;$q_nr<$quotes;$q_nr++)
       {
        $start_tag .= '[quote]';
        $end_tag .= '[/quote]';
       }
      // add start and end tags to quotes belonging together:
      if(empty($quotes_array[$l-1]) || $quotes!=$quotes_array[$l-1]) $string_array[$l] = $start_tag.$string_array[$l];
      if(empty($quotes_array[$l+1]) || $quotes!=$quotes_array[$l+1]) $string_array[$l] = $string_array[$l].$end_tag;
     }
    $l++;
   }

  $string = implode("\n",$string_array);

  return $string;
 }

/**
 * filters control characters
 *
 * @param string $string
 * @return string
 */
function filter_control_characters($string)
 {
  $char = array(array(), array());
  $char['char'][0] = chr(0);
  $char['repl'][0] = '';
  $char['char'][1] = chr(1);
  $char['repl'][1] = '';
  $char['char'][2] = chr(2);
  $char['repl'][2] = '';
  $char['char'][3] = chr(3);
  $char['repl'][3] = '';
  $char['char'][4] = chr(4);
  $char['repl'][4] = '';
  $char['char'][5] = chr(5);
  $char['repl'][5] = '';
  $char['char'][6] = chr(6);
  $char['repl'][6] = '';
  $char['char'][7] = chr(7);
  $char['repl'][7] = '';
  $char['char'][8] = chr(8);
  $char['repl'][8] = '';
  $char['char'][9] = chr(9);
  $char['repl'][9] = ' ';
  $char['char'][10] = chr(10);
  $char['repl'][10] = chr(10);
  $char['char'][11] = chr(11);
  $char['repl'][11] = '';
  $char['char'][12] = chr(12);
  $char['repl'][12] = '';
  $char['char'][13] = chr(13);
  $char['repl'][13] = chr(13);
  $char['char'][14] = chr(14);
  $char['repl'][14] = '';
  $char['char'][15] = chr(15);
  $char['repl'][15] = '';
  $char['char'][16] = chr(16);
  $char['repl'][16] = '';
  $char['char'][17] = chr(17);
  $char['repl'][17] = '';
  $char['char'][18] = chr(18);
  $char['repl'][18] = '';
  $char['char'][19] = chr(19);
  $char['repl'][19] = '';
  $char['char'][20] = chr(20);
  $char['repl'][20] = '';
  $char['char'][21] = chr(21);
  $char['repl'][21] = '';
  $char['char'][22] = chr(22);
  $char['repl'][22] = '';
  $char['char'][23] = chr(23);
  $char['repl'][23] = '';
  $char['char'][24] = chr(24);
  $char['repl'][24] = '';
  $char['char'][25] = chr(25);
  $char['repl'][25] = '';
  $char['char'][26] = chr(26);
  $char['repl'][26] = '';
  $char['char'][27] = chr(27);
  $char['repl'][27] = '';
  $char['char'][28] = chr(28);
  $char['repl'][28] = '';
  $char['char'][29] = chr(29);
  $char['repl'][29] = '';
  $char['char'][30] = chr(30);
  $char['repl'][30] = '';
  $char['char'][31] = chr(31);
  $char['repl'][31] = '';
  $string = str_replace($char['char'], $char['repl'], $string);
  return $string;
}

/**
 * formats posting texts into HTML using the stringparser bbcode class
 * http://www.christian-seiler.de/projekte/php/bbcode/
 *
 * @param string $string
 * @return string
 */
function html_format($string){
	global $settings;
	require_once('modules/stringparser_bbcode/stringparser_bbcode.class.php');
	$string = filter_control_characters($string);
	$bbcode = new StringParser_BBCode();
	$bbcode->addFilter (STRINGPARSER_FILTER_PRE, 'convertlinebreaks');
	$bbcode->addFilter (STRINGPARSER_FILTER_PRE, 'quote');
	$bbcode->addParser (array ('block', 'inline', 'link', 'listitem', 'quote', 'pre', 'rtl', 'ltr'), 'htmlspecialchars');
	$bbcode->addParser (array ('block', 'inline', 'link', 'listitem', 'quote', 'rtl', 'ltr'), 'nl2br');
	
	if($settings['smilies'] == 1) {
		$bbcode->addParser (array ('block', 'inline', 'listitem', 'quote', 'rtl', 'ltr'), 'smilies');
	}
	if($settings['autolink'] == 1) {
		$bbcode->addParser (array ('block', 'inline', 'listitem', 'quote', 'rtl', 'ltr'), 'make_link');
	}
	
	$bbcode->addCode ('quote', 'simple_replace', null, array ('start_tag' => '<blockquote>', 'end_tag' => '</blockquote>'), 'quote', array ('block','quote'), array ());
	$bbcode->setCodeFlag ('quote', 'paragraphs', true);
	$bbcode->setCodeFlag ('quote', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
	$bbcode->setCodeFlag ('quote', 'closetag.after.newline', BBCODE_NEWLINE_IGNORE);
	$bbcode->setCodeFlag ('quote', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
	$bbcode->setCodeFlag ('quote', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
	// $bbcode->setCodeFlag ('quote', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
	
	$bbcode->addCode ('rtl', 'simple_replace', null, array ('start_tag' => '<div dir="rtl">', 'end_tag' => '</div>'), 'rtl', array ('block','rtl'), array ('ltr'));
	$bbcode->setCodeFlag ('rtl', 'paragraphs', true);
	$bbcode->setCodeFlag ('rtl', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
	$bbcode->setCodeFlag ('rtl', 'closetag.after.newline', BBCODE_NEWLINE_IGNORE);
	$bbcode->setCodeFlag ('rtl', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
	$bbcode->setCodeFlag ('rtl', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
	
	$bbcode->addCode ('ltr', 'simple_replace', null, array ('start_tag' => '<div dir="ltr">', 'end_tag' => '</div>'), 'ltr', array ('block','ltr'), array ('rtl'));
	$bbcode->setCodeFlag ('ltr', 'paragraphs', true);
	$bbcode->setCodeFlag ('ltr', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
	$bbcode->setCodeFlag ('ltr', 'closetag.after.newline', BBCODE_NEWLINE_IGNORE);
	$bbcode->setCodeFlag ('ltr', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
	$bbcode->setCodeFlag ('ltr', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
	
	if($settings['bbcode'] == 1) {
		$bbcode->setGlobalCaseSensitive(false);
		
		$bbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '<strong>', 'end_tag' => '</strong>'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<em>', 'end_tag' => '</em>'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('u', 'simple_replace', null, array ('start_tag' => '<span class="underline">', 'end_tag' => '</span>'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('url', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'), 'link', array ('listitem', 'block', 'inline', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ('link'));
		$bbcode->addCode ('link', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'), 'link', array ('listitem', 'block', 'inline', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ('link'));
		$bbcode->addCode ('msg', 'usecontent?', 'do_bbcode_msg', array ('usecontent_param' => 'default'), 'link', array ('listitem', 'block', 'inline', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ('link'));
		// $bbcode->setOccurrenceType ('img', 'image');
		// $bbcode->setMaxOccurrences ('image', 2);
		// $bbcode->addCode ('code', 'simple_replace', null, array ('start_tag' => '<pre><code>', 'end_tag' => '</code></pre>'), 'code', array ('block','quote'), array ());
		
		$bbcode->addParser ('list', 'bbcode_stripcontents');
		$bbcode->addCode ('list', 'simple_replace', null, array ('start_tag' => '<ul>', 'end_tag' => '</ul>'), 'list', array ('block', 'listitem', 'quote', 'rtl', 'ltr'), array ());
		$bbcode->setCodeFlag ('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
		$bbcode->setCodeFlag ('list', 'closetag.after.newline', BBCODE_NEWLINE_IGNORE);
		$bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
		$bbcode->setCodeFlag ('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
		$bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'), 'listitem', array ('list'), array ());
		$bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
		// $bbcode->setCodeFlag ('*', 'paragraphs', true);
	
		if($settings['bbcode_code']==1) {
			$bbcode->addCode ('code', 'usecontent', 'do_bbcode_code', array (), 'code', array ('block', 'quote', 'rtl', 'ltr'), array ());
			$bbcode->setCodeFlag ('code', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
			$bbcode->addParser ('inlinecode', 'parse_inlinecode');
			$bbcode->addParser ('monospace', 'parse_monospace');
			$bbcode->addCode('inlinecode', 'simple_replace', null, array ('start_tag' => '<code>', 'end_tag' => '</code>'), 'inlinecode', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
			$bbcode->addCode('monospace', 'simple_replace', null, array ('start_tag' => '<code class="monospace">', 'end_tag' => '</code>'), 'monospace', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
			$bbcode->addCode('pre', 'simple_replace', null, array ('start_tag' => '<pre>', 'end_tag' => '</pre>'), 'pre', array ('block', 'quote', 'rtl', 'ltr'), array ());
			$bbcode->setCodeFlag('pre', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
			// $bbcode->addCode('inlinepre', 'simple_replace', null, array ('start_tag' => '<pre class="inline">', 'end_tag' => '</pre>'), 'inlinepre', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		}
		if($settings['bbcode_img']==1) {
			$bbcode->addCode ('img', 'usecontent', 'do_bbcode_img', array (), 'image', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		}
		if($settings['bbcode_color']==1) {
			$bbcode->addCode ('color', 'callback_replace', 'do_bbcode_color', array ('usecontent_param' => 'default'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ());
		}
		if($settings['bbcode_size']==1) {
			$bbcode->addCode ('size', 'callback_replace', 'do_bbcode_size', array ('usecontent_param' => 'default'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		}
		if($settings['bbcode_latex']==1 && !empty($settings['bbcode_latex_uri'])) {
			$bbcode->addCode ('tex', 'usecontent', 'do_bbcode_tex', array (), 'tex', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		}
	}

	$bbcode->setRootParagraphHandling(true);
	$string = $bbcode->parse($string);
	return $string;
}

/**
 * formats signatures into HTML using the stringparser bbcode class
 * http://www.christian-seiler.de/projekte/php/bbcode/
 *
 * @param string $string
 * @return string
 */
function signature_format($string) {
	global $settings;
	require_once('modules/stringparser_bbcode/stringparser_bbcode.class.php');
	$string = filter_control_characters($string);
	$bbcode = new StringParser_BBCode();
	$bbcode->addFilter (STRINGPARSER_FILTER_PRE, 'convertlinebreaks');
	$bbcode->addParser (array ('block', 'inline', 'link', 'listitem', 'code', 'quote', 'rtl', 'ltr'), 'htmlspecialchars');
	$bbcode->addParser (array ('block', 'inline', 'link', 'listitem', 'quote', 'rtl', 'ltr'), 'nl2br');
	
	if($settings['smilies'] == 1) {
		$bbcode->addParser (array ('block', 'inline', 'listitem', 'quote', 'rtl', 'ltr'), 'smilies');
	}
	if($settings['autolink'] == 1) {
		$bbcode->addParser (array ('block', 'inline', 'listitem', 'quote', 'rtl', 'ltr'), 'make_link');
	}
	
	if($settings['bbcode'] == 1) {
		$bbcode->setGlobalCaseSensitive(false);
		$bbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '<strong>', 'end_tag' => '</strong>'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<em>', 'end_tag' => '</em>'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('u', 'simple_replace', null, array ('start_tag' => '<span class="underline">', 'end_tag' => '</span>'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('url', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'), 'link', array ('listitem', 'block', 'inline', 'quote', 'rtl', 'ltr'), array ('link'));
		$bbcode->addCode ('link', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'), 'link', array ('listitem', 'block', 'inline', 'quote', 'rtl', 'ltr'), array ('link'));
		$bbcode->addCode ('color', 'callback_replace', 'do_bbcode_color', array ('usecontent_param' => 'default'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		// $bbcode->setOccurrenceType ('img', 'image');
		// $bbcode->setMaxOccurrences ('image', 2);
		if($settings['bbcode_img'] == 1) {
			$bbcode->addCode ('img', 'usecontent', 'do_bbcode_img', array (), 'image', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		}
	}
	// $bbcode->setRootParagraphHandling(true);
	$string = $bbcode->parse($string);
	return $string;
}

/**
 * formats posting texts into plain text for e-mail notifications using the stringparser bbcode class
 * http://www.christian-seiler.de/projekte/php/bbcode/
 *
 * @param string $string
 * @return string
 */
function email_format($string) {
	global $settings;
	require_once('modules/stringparser_bbcode/stringparser_bbcode.class.php');
	$bbcode = new StringParser_BBCode();
	$bbcode->setGlobalCaseSensitive(false);
	$bbcode->addCode ('quote', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
	if($settings['bbcode'] == 1) {
		$bbcode->addParser ('list', 'bbcode_stripcontents');
		$bbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '*', 'end_tag' => '*'), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('u', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('url', 'usecontent?', 'do_bbcode_url_email', array ('usecontent_param' => 'default'), 'link', array ('listitem', 'block', 'inline', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ('link'));
		$bbcode->addCode ('link', 'usecontent?', 'do_bbcode_url_email', array ('usecontent_param' => 'default'), 'link', array ('listitem', 'block', 'inline', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ('link'));
		$bbcode->addCode ('msg', 'usecontent?', 'do_bbcode_msg_email', array ('usecontent_param' => 'default'), 'link', array ('listitem', 'block', 'inline', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ('link'));
		if($settings['bbcode_img'] == 1) {
			$bbcode->addCode ('img', 'usecontent', 'do_bbcode_img_email', array (), 'image', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		}
		$bbcode->addCode ('color', 'callback_replace', 'do_bbcode_color_email', array ('start_tag' => '', 'end_tag' => ''), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'pre', 'monospace', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('size', 'callback_replace', 'do_bbcode_size_email', array ('start_tag' => '', 'end_tag' => ''), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('list', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''), 'list', array ('block', 'listitem', 'rtl', 'ltr'), array ());
		$bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '* ', 'end_tag' => ''), 'listitem', array ('list'), array ());
		$bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
		// $bbcode->addCode ('code', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''), 'code', array ('block', 'inline'), array ());
		if($settings['bbcode_code']==1){
			$bbcode->addCode('code', 'usecontent', 'do_bbcode_code_email', array (), 'code', array ('block', 'quote', 'rtl', 'ltr'), array ());
			$bbcode->addCode ('pre', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''), 'pre', array ('block', 'quote', 'rtl', 'ltr'), array ());
			$bbcode->addCode ('inlinecode', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
			$bbcode->addCode ('monospace', 'simple_replace', null, array ('start_tag' => '', 'end_tag' => ''), 'inline', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		}
		if($settings['bbcode_latex']==1 && !empty($settings['bbcode_latex_uri'])) {
			$bbcode->addCode ('tex', 'usecontent', 'do_bbcode_tex_email', array (), 'tex', array ('listitem', 'block', 'inline', 'link', 'quote', 'rtl', 'ltr'), array ());
		}
	}
	$string = $bbcode->parse($string);
	return $string;
}

/**
 * adds the quote symbol (">") before each line for textareas in replies
 *
 * @param string $string
 * @return string
 */
function quote_reply($string)
 {
  global $settings;
  if(!empty($string)) return preg_replace("/^/m", $settings['quote_symbol']." ", $string);
  else return '';
 }

/**
 * shortens links
 *
 * @param string $string
 * @return string
 */
function shorten_link($string)
 {
  global $settings;
  if(is_array($string))
   {
    if(count($string) == 2) { $pre = ""; $url = $string[1]; }
    else { $pre = $string[1]; $url = $string[2]; }
    $shortened_url = $url;
    if (strlen($url) > $settings['text_word_maxlength']) $shortened_url = my_substr($url, 0, $settings['text_word_maxlength']-3, CHARSET) . '...';
    return $pre.'<a href="'.$url.'">'.$shortened_url.'</a>';
   }
 }

/**
 * shortens urls
 *
 * @param string $url
 * @return string
 */
function shorten_url($url)
 {
  global $settings;
  if(strlen($url) > $settings['text_word_maxlength']) $url_short = my_substr($url, 0, $settings['text_word_maxlength']-3, CHARSET) . '...';
  else $url_short = $url;
  return $url_short;
 }

/**
 * replaces text smilies by images
 *
 * @param string $string
 * @return string
 */
function smilies($string)
 {
  global $connid, $db_settings;
  $result = mysqli_query($connid, "SELECT file, code_1, code_2, code_3, code_4, code_5, title FROM ".$db_settings['smilies_table']);
  while($data = mysqli_fetch_array($result))
   {
    if($data['title']!='') $title = ' title="'.$data['title'].'"'; else $title='';
    if($data['code_1']!='') $string = str_replace($data['code_1'], "<img src=\"images/smilies/".$data['file']."\" alt=\"".$data['code_1']."\"".$title." />", $string);
    if($data['code_2']!='') $string = str_replace($data['code_2'], "<img src=\"images/smilies/".$data['file']."\" alt=\"".$data['code_2']."\"".$title." />", $string);
    if($data['code_3']!='') $string = str_replace($data['code_3'], "<img src=\"images/smilies/".$data['file']."\" alt=\"".$data['code_3']."\"".$title." />", $string);
    if($data['code_4']!='') $string = str_replace($data['code_4'], "<img src=\"images/smilies/".$data['file']."\" alt=\"".$data['code_4']."\"".$title." />", $string);
    if($data['code_5']!='') $string = str_replace($data['code_5'], "<img src=\"images/smilies/".$data['file']."\" alt=\"".$data['code_5']."\"".$title." />", $string);
   }
  mysqli_free_result($result);
  return($string);
 }

/**
 * counts the users that are online
 */
function user_online($user_online_period=10)
 {
  global $connid, $db_settings, $settings;
  if (isset($_SESSION[$settings['session_prefix'].'user_id'])) $user_id = $_SESSION[$settings['session_prefix'].'user_id']; else $user_id = 0;
  $diff = TIMESTAMP-($user_online_period*60);
  if (isset($_SESSION[$settings['session_prefix'].'user_id'])) $ip = "uid_".$_SESSION[$settings['session_prefix'].'user_id'];
  else $ip = $_SERVER['REMOTE_ADDR'];
  @mysqli_query($connid, "DELETE FROM ".$db_settings['useronline_table']." WHERE time < ".$diff);
  list($is_online) = @mysqli_fetch_row(@mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['useronline_table']." WHERE ip= '".$ip."'"));
  if ($is_online > 0) @mysqli_query($connid, "UPDATE ".$db_settings['useronline_table']." SET time='".TIMESTAMP."', user_id='".$user_id."' WHERE ip='".$ip."'");
  else @mysqli_query($connid, "INSERT INTO ".$db_settings['useronline_table']." SET time='".TIMESTAMP."', ip='".$ip."', user_id='".$user_id."'");
 }

/**
 * checks strings for too long words
 * @param String $text
 * @param int $word_maxlength (No check if <= 0)
 * @param string $delimiters (single character list e.g. ".?!;,", ctrl chars like \n will ignored because of preg_quote-function)
 */
function too_long_word($text,$word_maxlength,$delimiters = ' ') {
	if ($word_maxlength <= 0)
		return false;
	$delimiters .= " \n";
	$text = preg_replace("/\015\012|\015|\012/", " ", $text);
	$text = preg_replace("/[".preg_quote($delimiters, '/')."]/", " ", $text);
	$words = preg_split('/\s+/',$text);

	foreach($words as $word) {
		if (!empty($word)) {
			$length = my_strlen(trim($word), CHARSET);
			if($length > $word_maxlength) {
				$too_long_word = htmlspecialchars(my_substr($word,0,$word_maxlength, CHARSET))."...";
				break;
			}
		}
	}
	if(isset($too_long_word)) 
		return $too_long_word;
	else
		return false;
}

/**
 * deletes a posting and all its replies
 *
 * @param int $id : the id of the posting
 */
function delete_posting_recursive($id) {
	global $db_settings, $connid;
	$id = intval($id);
	$result = mysqli_query($connid, "SELECT pid, tid FROM " . $db_settings['forum_table'] . " WHERE id = " . $id) or raise_error('database_error', mysqli_error($connid));
	$field = mysqli_fetch_array($result);
	$tid   = $field['tid'];
	mysqli_free_result($result);
	if ($field["pid"] == 0) {
		// it's a thread starting posting - delete whole thread:
		// clear cache:
		$ids_result = mysqli_query($connid, "SELECT id FROM " . $db_settings['forum_table'] . " WHERE tid = " . intval($id));
		while ($ids_data = mysqli_fetch_array($ids_result)) {
			@mysqli_query($connid, "DELETE FROM " . $db_settings['entry_cache_table'] .   " WHERE cache_id   = " . intval($ids_data['id']));
			@mysqli_query($connid, "DELETE FROM " . $db_settings['bookmark_table'] .      " WHERE posting_id = " . intval($ids_data['id']));
			@mysqli_query($connid, "DELETE FROM " . $db_settings['read_status_table'] .   " WHERE posting_id = " . intval($ids_data['id']));
			@mysqli_query($connid, "DELETE FROM " . $db_settings['entry_tags_table'] .    " WHERE `bid`      = " . intval($ids_data['id']));
			@mysqli_query($connid, "DELETE FROM " . $db_settings['subscriptions_table'] . " WHERE `eid`      = " . intval($ids_data['id']));
			@mysqli_query($connid, "DELETE FROM " . $db_settings['akismet_rating_table'] ." WHERE `eid`      = " . intval($ids_data['id']));
			@mysqli_query($connid, "DELETE FROM " . $db_settings['b8_rating_table'] .     " WHERE `eid`      = " . intval($ids_data['id']));
		}
		mysqli_free_result($ids_result);
		// end clear cache
		@mysqli_query($connid, "DELETE FROM " . $db_settings['forum_table'] . " WHERE tid = " . intval($id));
	} else {
		// it's a posting within the thread - delete posting and child postings:
		$child_ids = get_child_ids($id);
		@mysqli_query($connid, "DELETE FROM " . $db_settings['forum_table'] .          " WHERE id         = " . intval($id));
		@mysqli_query($connid, "DELETE FROM " . $db_settings['entry_cache_table'] .    " WHERE cache_id   = " . intval($id));
		@mysqli_query($connid, "DELETE FROM " . $db_settings['bookmark_table'] .       " WHERE posting_id = " . intval($id));
		@mysqli_query($connid, "DELETE FROM " . $db_settings['read_status_table'] .    " WHERE posting_id = " . intval($id));
		@mysqli_query($connid, "DELETE FROM " . $db_settings['entry_tags_table'] .     " WHERE `bid`      = " . intval($id));
		@mysqli_query($connid, "DELETE FROM " . $db_settings['subscriptions_table'] .  " WHERE `eid`      = " . intval($id));
		@mysqli_query($connid, "DELETE FROM " . $db_settings['akismet_rating_table'] . " WHERE `eid`      = " . intval($id));
		@mysqli_query($connid, "DELETE FROM " . $db_settings['b8_rating_table'] .      " WHERE `eid`      = " . intval($id));
		if (isset($child_ids) && is_array($child_ids)) {
			foreach ($child_ids as $child_id) {
				@mysqli_query($connid, "DELETE FROM " . $db_settings['forum_table'] .          " WHERE id         = " . intval($child_id));
				@mysqli_query($connid, "DELETE FROM " . $db_settings['entry_cache_table'] .    " WHERE cache_id   = " . intval($child_id));
				@mysqli_query($connid, "DELETE FROM " . $db_settings['bookmark_table'] .       " WHERE posting_id = " . intval($child_id));
				@mysqli_query($connid, "DELETE FROM " . $db_settings['read_status_table'] .    " WHERE posting_id = " . intval($child_id));
				@mysqli_query($connid, "DELETE FROM " . $db_settings['entry_tags_table'] .     " WHERE `bid`      = " . intval($child_id));
				@mysqli_query($connid, "DELETE FROM " . $db_settings['subscriptions_table'] .  " WHERE `eid`      = " . intval($child_id));
				@mysqli_query($connid, "DELETE FROM " . $db_settings['akismet_rating_table'] . " WHERE `eid`      = " . intval($child_id));
				@mysqli_query($connid, "DELETE FROM " . $db_settings['b8_rating_table'] .      " WHERE `eid`      = " . intval($child_id));
			}
		}
		// set last reply time:
		$result = @mysqli_query($connid, "SELECT time FROM " . $db_settings['forum_table'] . " WHERE tid = " . intval($tid) . " ORDER BY time DESC LIMIT 1") or raise_error('database_error', mysqli_error($connid));
		$field = mysqli_fetch_array($result);
		mysqli_free_result($result);
		@mysqli_query($connid, "UPDATE " . $db_settings['forum_table'] . " SET time=time, last_reply='" . $field['time'] . "' WHERE tid=" . intval($tid));
	}
	deleteTags();
}

/**
 * returns child ids of a posting
 * required by the function delete_posting_recursive
 */
function get_child_ids($id)
 {
  global $db_settings, $connid, $child_ids;
  $result = @mysqli_query($connid, "SELECT tid FROM ".$db_settings['forum_table']." WHERE id = ".intval($id)." LIMIT 1") or raise_error('database_error',mysqli_error($connid));
  $data = mysqli_fetch_array($result);
  mysqli_free_result($result);
  $tid = $data['tid'];
  $result = @mysqli_query($connid, "SELECT id, pid FROM ".$db_settings['forum_table']." WHERE tid = ".intval($tid)) or raise_error('database_error',mysqli_error($connid));
  while($tmp = mysqli_fetch_array($result))
   {
    $child_array[$tmp["pid"]][] = $tmp["id"];
   }
  mysqli_free_result($result);
  child_ids_recursive($id, $child_array);
  if(isset($child_ids) && is_array($child_ids)) return($child_ids);
  else return false;
 }

/**
 * help function for get_child_ids
 */
function child_ids_recursive($id, $child_array)
 {
  global $child_ids;
  if(isset($child_array[$id]) && is_array($child_array[$id]))
   {
    foreach($child_array[$id] as $child)
     {
      $child_ids[] = $child;
      child_ids_recursive($child, $child_array);
     }
   }
 }

/**
 * checks if birthday is formed like DD.MM.YYYY and age is betwenn 0 and 150 years
 */
function is_valid_birthday($birthday) {
	$date_invalid = (strlen($birthday) != 10 || my_substr($birthday,4,1,CHARSET)!='-' || my_substr($birthday,7,1, CHARSET)!='-');
	if (!$date_invalid) {
		$year  = intval(my_substr($birthday, 0, 4, CHARSET));
		$month = intval(my_substr($birthday, 5, 2, CHARSET));
		$day   = intval(my_substr($birthday, 8, 2, CHARSET));
		$date_invalid = !checkdate($month,$day,$year);
   }
	if (!$date_invalid) {
		if ($month >= 1 && $month <= 9) 
			$monthstr = '0'.$month; 
		else 
			$monthstr = $month;
		if ($day >= 1 && $day <= 9) 
			$daystr = '0'.$day; 
		else 
			$daystr = $day;
		$years = intval(strrev(my_substr(strrev(intval(date("Ymd")) - intval($year.$monthstr.$daystr)),4, NULL, CHARSET)));
		$date_invalid = ($years < 0 || $years > 150);
	}
	return !$date_invalid;
}

/**
 * sends an e-mail notification to the parent posting author if a reply was
 * posted and a notification was requested
 *
 * @param int $id : the id of the reply
 * @param bool $delayed : true adds a delayed message (when postibg was activated manually)
 */
function emailNotification2ParentAuthor($id, $delayed = false) {
	global $settings, $db_settings, $lang, $connid;
	$id = intval($id);
	// get data of posting:
	$queryNewPosting = "SELECT pid, tid, name, user_name, ".$db_settings['forum_table'].".user_id, subject, text
		FROM ".$db_settings['forum_table']."
		LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id = ".$db_settings['forum_table'].".user_id
		WHERE id = ".intval($id)." LIMIT 1";
	$resultNewPosting = @mysqli_query($connid, $queryNewPosting);
	$data = mysqli_fetch_assoc($resultNewPosting);
	mysqli_free_result($resultNewPosting);
	// overwrite $data['name'] with $data['user_name'] if registered user:
	if ($data['user_id'] > 0) {
		if (!$data['user_name']) $data['name'] = $lang['unknown_user'];
		else $data['name'] = $data['user_name'];
	}
	// if it's a reply (pid!=0) check if notification was desired by parent posting author:
	if ($data['pid'] != 0) {
		$parentSubscriptions = mysqli_query($connid, "SELECT user_id, eid, unsubscribe_code, 'child' AS type FROM " . $db_settings['subscriptions_table'] . " WHERE eid = " . intval($data['pid']) . " UNION SELECT user_id, eid, unsubscribe_code, 'opener' AS type FROM " . $db_settings['subscriptions_table'] . " WHERE eid = " . intval($data['tid']));
		while ($parent_data = mysqli_fetch_assoc($parentSubscriptions)) {
			if ($parent_data['user_id'] > 0) {
				// user_id is greater than 0 and therefore we handle a subscription of a registered user
				$queryEmailData = "SELECT user_id, user_name, user_email FROM " . $db_settings['userdata_table'] . " WHERE user_id = " . intval($parent_data['user_id']);
			} else {
				// user_id is 0 and therefore we handle a subscription of an unregistered user
				$queryEmailData = "SELECT user_id, name AS user_name, email AS user_email FROM ".$db_settings['forum_table']." WHERE id = " . intval($parent_data['eid']) . " LIMIT 1";
			}
			$resultEmailData = @mysqli_query($connid, $queryEmailData);
			if ($resultEmailData !== false) {
				$emailData = mysqli_fetch_assoc($resultEmailData);
				if ($parent_data['user_id'] > 0 || $settings['email_notification_unregistered'] == 1) {
					if ($parent_data['type'] == 'opener') {
						$queryParentPosting = "SELECT pid, user_id, name, email, subject, text
						FROM " . $db_settings['forum_table'] . "
						WHERE id = " . intval($data['tid']) . " LIMIT 1";
					} else {
						$queryParentPosting = "SELECT pid, user_id, name, email, subject, text
						FROM " . $db_settings['forum_table'] . "
						WHERE id = " . intval($data['pid']) . " LIMIT 1";
					}
					$resultParentPosting = mysqli_query($connid, $queryParentPosting);
					$parentPosting = mysqli_fetch_assoc($resultParentPosting);
					$name = $data['name'];
					$subject = $data['subject'];
					$text = email_format($data['text']);
					$parent_text = email_format($parentPosting["text"]);
					$emailbody = str_replace("[recipient]", $emailData['user_name'], $lang['email_text']);
					$emailbody = str_replace("[unsubscribe_address]", $settings['forum_address']."index.php?mode=posting&unsubscribe=". $parent_data['unsubscribe_code'], $emailbody);
					$emailbody = str_replace("[name]", $name, $emailbody);
					$emailbody = str_replace("[subject]", $subject, $emailbody);
					$emailbody = str_replace("[text]", $text, $emailbody);
					$emailbody = str_replace("[posting_address]", $settings['forum_address']."index.php?id=".$id, $emailbody);
					$emailbody = str_replace("[original_subject]", $parentPosting['subject'], $emailbody);
					$emailbody = str_replace("[original_text]", $parent_text, $emailbody);
					$emailbody = str_replace("[forum_address]", $settings['forum_address'], $emailbody);
					if ($delayed == true) $emailbody = $emailbody . "\n\n" . $lang['email_text_delayed_addition'];
					$recipient = $emailData['user_email'];
					$subject = str_replace("[original_subject]", $parentPosting['subject'], $lang['email_subject']);
					my_mail($recipient, $subject, $emailbody);
				}
			}
		}
	}
}

/**
 * sends an e-mail notification to all admins and mods who have activated
 * e-mail notification
 *
 * @param int $id : the id of the posting
 * @param bool $delayed : true adds a delayed message (when postibg was activated manually)
 */
function emailNotification2ModsAndAdmins($id, $delayed=false)
 {
  global $settings, $db_settings, $lang, $connid;
  $id=intval($id);
  // data of posting:
  $result = @mysqli_query($connid, "SELECT pid, name, user_name, ".$db_settings['forum_table'].".user_id, subject, text
                         FROM ".$db_settings['forum_table']."
                         LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id=".$db_settings['forum_table'].".user_id
                         WHERE id = ".intval($id)." LIMIT 1");
  $data = mysqli_fetch_array($result);
  mysqli_free_result($result);
  // overwrite $data['name'] with $data['user_name'] if registered user:
  if($data['user_id']>0)
   {
    if(!$data['user_name']) $data['name'] = $lang['unknown_user'];
    else $data['name'] = $data['user_name'];
   }
  $name = $data['name'];
  $subject = $data['subject'];
  $text = email_format($data['text']);
  if($data['pid'] > 0) $emailbody = str_replace("[name]", $name, $lang['admin_email_text_reply']); else $emailbody = str_replace("[name]", $name, $lang['admin_email_text']);
  $emailbody = str_replace("[subject]", $subject, $emailbody);
  $emailbody = str_replace("[text]", $text, $emailbody);
  $emailbody = str_replace("[posting_address]", $settings['forum_address']."index.php?id=".$id, $emailbody);
  $emailbody = str_replace("[forum_address]", $settings['forum_address'], $emailbody);
  if($delayed==true) $emailbody = $emailbody . "\n\n" . $lang['email_text_delayed_addition'];
  $lang['admin_email_subject'] = str_replace("[subject]", $subject, $lang['admin_email_subject']);
  // who gets an E-mail notification?
  $recipient_result = @mysqli_query($connid, "SELECT user_name, user_email FROM ".$db_settings['userdata_table']." WHERE user_type > 0 AND new_posting_notification=1") or raise_error('database_error',mysqli_error($connid));
  while($admin_array = mysqli_fetch_array($recipient_result))
   {
    $ind_emailbody = str_replace("[admin]", $admin_array['user_name'], $emailbody);
    $recipient = $admin_array['user_email'];
    my_mail($recipient, $lang['admin_email_subject'], $ind_emailbody);
   }
  mysqli_free_result($recipient_result);
 }

/**
 * function for the up/down buttons in the admin area in case JavaScript
 * isn't available
 *
 * @param string $table : name of database table
 * @param int $id : id of the item
 * @param string $direction : 'up' or 'down'
 */
function move_item($table, $id, $direction)
 {
  global $connid;
  if($direction=='up')
   {
    $result = mysqli_query($connid, "SELECT order_id FROM ".$table." WHERE id = ".intval($id)." LIMIT 1") or die(mysqli_error($connid));
    $data = mysqli_fetch_array($result);
    mysqli_free_result($result);
    if($data['order_id'] > 1)
     {
      mysqli_query($connid, "UPDATE ".$table." SET order_id=0 WHERE order_id=".$data['order_id']."-1");
      mysqli_query($connid, "UPDATE ".$table." SET order_id=order_id-1 WHERE order_id=".$data['order_id']);
      mysqli_query($connid, "UPDATE ".$table." SET order_id=".$data['order_id']." WHERE order_id=0");
     }
   }
  else // down
   {
    list($item_count) = mysqli_fetch_row(mysqli_query($connid, "SELECT COUNT(*) FROM ".$table));
    $result = mysqli_query($connid, "SELECT order_id FROM ".$table." WHERE id = ".intval($id)." LIMIT 1") or die(mysqli_error($connid));
    $data = mysqli_fetch_array($result);
    mysqli_free_result($result);
    if ($data['order_id'] < $item_count)
     {
      mysqli_query($connid, "UPDATE ".$table." SET order_id=0 WHERE order_id=".$data['order_id']."+1");
      mysqli_query($connid, "UPDATE ".$table." SET order_id=order_id+1 WHERE order_id=".$data['order_id']);
      mysqli_query($connid, "UPDATE ".$table." SET order_id=".$data['order_id']." WHERE order_id=0");
     }
   }
 }

/**
 * resizes uploaded images
 *
 * @param string $uploaded_file : uploaded file
 * @param string $file : destination file
 * @param int $new_width : new width
 * @param int $new_height : new height
 * @param int $compression : compression rate
 * @return bool
 */
function resize_image($uploaded_file, $file, $new_width, $new_height, $compression=80)
 {
  if(file_exists($file))
   {
    @chmod($file, 0777);
    @unlink($file);
   }

  $image_info = getimagesize($uploaded_file);
  if(!is_array($image_info) || $image_info[2] != 1 && $image_info[2] != 2 && $image_info[2] != 3) $error = true;
  if(empty($error))
  {
  if($image_info[2]==1) // GIF
   {
    $current_image = @imagecreatefromgif($uploaded_file) or $error = true;
    if(empty($error)) $new_image = @imagecreate($new_width,$new_height) or $error = true;
    if(empty($error)) @imagecopyresampled($new_image,$current_image,0,0,0,0,$new_width,$new_height,$image_info[0],$image_info[1]) or $error=true;
    if(empty($error)) @imagegif($new_image, $file) or $error = true;
   }
  elseif($image_info[2]==2) // JPG
   {
    $current_image = @imagecreatefromjpeg($uploaded_file) or $error = true;
    if(empty($error)) $new_image=@imagecreatetruecolor($new_width,$new_height) or $error = true;
    if(empty($error)) @imagecopyresampled($new_image,$current_image,0,0,0,0,$new_width,$new_height,$image_info[0],$image_info[1]) or $error = true;
    if(empty($error)) @imagejpeg($new_image, $file, $compression) or $error = true;
   }
  elseif($image_info[2]==3) // PNG
   {
    $current_image=imagecreatefrompng($uploaded_file) or $error = true;
    if(empty($error)) $new_image=imagecreatetruecolor($new_width,$new_height) or $error = true;
    if(empty($error)) imagecopyresampled($new_image,$current_image,0,0,0,0,$new_width,$new_height,$image_info[0],$image_info[1]) or $error = true;
    if(empty($error)) imagepng($new_image, $file) or $error = $true;
   }
  }
  if(empty($error)) return true;
  else return false;
 }

/**
 * returns an array with recent tags
 *
 * @param int $days : period in days
 * @param int $scale_min : frequency mimimum scale
 * @param int $scale_max : frequency maximun scale
 * @return array
 */
function tag_cloud($days, $scale_min, $scale_max) {
	global $category, $categories, $category_ids_query, $db_settings, $connid;
	$sql = "SELECT `tag` FROM `" . $db_settings['entry_tags_table'] . "` JOIN `" . $db_settings['forum_table'] . "` ON `" . $db_settings['forum_table'] . "`.`id` = `" . $db_settings['entry_tags_table'] . "`.`bid` JOIN `" . $db_settings['tags_table'] . "` ON `" . $db_settings['tags_table'] . "`.`id` = `" . $db_settings['entry_tags_table'] . "`.`tid` WHERE `time` > (NOW() - INTERVAL " . intval($days) . " DAY) ";
	if ($categories == false)
		$result = @mysqli_query($connid, $sql);
	elseif ($category > 0)
		$result = @mysqli_query($connid, $sql . " AND `category` = " . intval($category) );
	else
		$result = @mysqli_query($connid, $sql . " AND `category` IN (" . $category_ids_query . ")");

	if (mysqli_num_rows($result) > 0) {
		$tags_array = [];
		while ($data = mysqli_fetch_array($result)) {
			$tag = $data['tag'];
			if (isset($tags_array[$tag]))
				$tags_array[$tag]++;
			else 
				$tags_array[$tag] = 1;
		}
		
		ksort($tags_array, SORT_NATURAL | SORT_FLAG_CASE);
		// minimum and maximum value:
		foreach ($tags_array as $tag) {
			if (empty($max))
				$max = $tag;
			elseif ($tag > $max)
				$max = $tag;
			if (empty($min))
				$min = $tag;
			elseif ($tag < $min)
				$min = $tag;
		}
		reset($tags_array);
		
		if ($max - $min < 1)
			$d = 1;
		else
			$d = $max - $min;
		
		$m = ($scale_max - $scale_min) / $d;
		$t = $scale_min - $m * $min;
		
		$i = 0;
		foreach ($tags_array as $key => $val) {
			if (my_strpos($key, ' ', 0, CHARSET))
				$tag_escaped = '"' . $key . '"';
			else
				$tag_escaped = $key;
			$tags[$i]['tag']       = $key;
			$tags[$i]['escaped']   = urlencode($tag_escaped);
			$tags[$i]['frequency'] = round($m * $val + $t, 0);
			$i++;
		}
	}
	
	mysqli_free_result($result);
	
	if (isset($tags))
		return $tags;
	else
		return false;
}

/**
 * converts a unix timestamp into a formated date string
 *
 * @param string $format : like parameter for DateTime::format() - https://www.php.net/manual/de/datetime.format.php
 * @param int $timestamp : UNIX timestamp
 * @return string
 */
function format_time($format, $timestamp = 0) {
	if ($timestamp == 0) 
		$timestamp = TIMESTAMP;
	
	if (defined('LOCALE_CHARSET')) {
		return iconv(LOCALE_CHARSET,CHARSET, date($format,$timestamp));
	}
	else {
		return date($format, $timestamp);
	}
}

/**
 * checks permission to edit a posting
 *
 * @return int : 0 = not authorized, 1 = edit period expired, 2 = locked, 3 = posting has replies, 4 = no replies
 */
function get_edit_authorization($id, $posting_user_id, $edit_key, $time, $locked)
 {
  global $settings, $db_settings, $connid;

  $authorization['edit'] = false;
  $authorization['delete'] = false;

  $reply_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE pid = ".intval($id));
  list($replies) = mysqli_fetch_row($reply_result);

  if($settings['edit_min_time_period'] != 0 && (TIMESTAMP - $settings['edit_min_time_period']*60) < $time) $edit_min_time_period_expired = false;
  else $edit_min_time_period_expired = true;

  if($settings['edit_max_time_period'] == 0 || (TIMESTAMP - $settings['edit_max_time_period']*60) < $time) $edit_max_time_period_expired = false;
  else $edit_max_time_period_expired = true;

  if($locked == 0) $locked = false;
  else $locked = true;

  if(isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_SESSION[$settings['session_prefix'].'user_type'])) // registered user
   {
    if($_SESSION[$settings['session_prefix'].'user_type'] > 0) // admin or mod
     {
      $authorization['edit'] = true;
      $authorization['delete'] = true;
     }
    elseif($_SESSION[$settings['session_prefix'].'user_type']==0)
     {
      if($posting_user_id == $_SESSION[$settings['session_prefix'].'user_id'] && $settings['user_edit'] > 0 && $edit_max_time_period_expired==false && $locked==false)
       {
        if($settings['user_edit_if_no_replies']==0 || ($settings['user_edit_if_no_replies']==1 && ($replies==0 || $edit_min_time_period_expired==false)))
         {
          $authorization['edit'] = true;
         }
        if($replies==0)
         {
          $authorization['delete'] = true;
         }
       }
     }
   }
  elseif($settings['user_edit']>1 && isset($_SESSION[$settings['session_prefix'].'edit_keys']))
   {
    if(isset($_SESSION[$settings['session_prefix'].'edit_keys'][$id]) && is_pw_correct($_SESSION[$settings['session_prefix'].'edit_keys'][$id],$edit_key) && trim($edit_key)!='' && $edit_max_time_period_expired==false && $locked==false)
     {
      if($settings['user_edit_if_no_replies']==0 || ($settings['user_edit_if_no_replies']==1 && ($replies==0 || $edit_min_time_period_expired==false)))
       {
        $authorization['edit'] = true;
       }
      if($replies==0)
       {
        $authorization['delete'] = true;
       }
     }
   }
  return $authorization;
 }

/**
 * checks file names
 *
 * @param string $filename
 * @return bool
 */
function check_filename($filename)
 {
  if(preg_match('/^[a-zA-Z0-9._\-]+$/', $filename)) return true;
  else return false;
 }

/**
 * generates a random string
 *
 * @param int $length
 * @param string $characters
 * @return string
 */
function random_string($length=8,$characters='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
 {
  $random_string = '';
  $characters_length = strlen($characters);
  for($i=0;$i<$length;$i++)
   {
    $random_string .= $characters[mt_rand(0, $characters_length - 1)];
   }
  return $random_string;
 }

/**
 * generates password hash
 *
 * @param string $pw
 * @return string
 */
function generate_pw_hash($pw)
 {
  $salt = random_string(10,'0123456789abcdef');
  $salted_hash = sha1($pw.$salt);
  $hash_with_salt = $salted_hash.$salt;
  return $hash_with_salt;
 }

/**
 * checks password comparing it with the hash
 *
 * @param string $pw
 * @param string $hash
 * @return bool
 */
function is_pw_correct($pw,$hash)
 {
  if(strlen($hash)==50) // salted sha1 hash with salt
   {
    $salted_hash = substr($hash,0,40);
    $salt = substr($hash,40,10);
    if(sha1($pw.$salt)==$salted_hash) return true;
    else return false;
   }
  elseif(strlen($hash)==32) // md5 hash generated in an older version
   {
    if($hash == md5($pw)) return true;
    else return false;
   }
  else return false;
 }

/**
 * add "http://" to url if given without protocol
 *
 * @param string $url
 * @return string
 */
function add_http_if_no_protocol($url)
 {
  if(my_substr($url,0,7,CHARSET) != 'http://' && my_substr($url,0,8,CHARSET) != 'https://' && my_substr($url,0,6,CHARSET) != 'ftp://' && my_substr($url,0,9,CHARSET) != 'gopher://' && my_substr($url,0,7,CHARSET) != 'news://')
   {
    $url = 'http://'.$url;
   }
  return $url;
 }

/**
 * determine string length using mb_strlen if available or strlen if not
 *
 * @param string $string
 * @param string $encoding
 * @return int
 */
function my_strlen($string, $encoding='utf-8')
 {
  if(function_exists('mb_strlen'))
   {
    return mb_strlen($string, $encoding);
   }
  else
   {
    return strlen($string);
   }
 }

/**
 * returns string with all alphabetic characters converted to lowercase
 * using mb_strtolower if available or strtolower if not
 *
 * @param string $string
 * @param string $encoding
 * @return string
 */
function my_strtolower($string, $encoding='utf-8')
 {
  if(function_exists('mb_strtolower'))
   {
    return mb_strtolower($string, $encoding);
   }
  else
   {
    return strtolower($string);
   }
 }

/**
 * gets part of string using mb_substr if available or substr if not
 *
 * @param string $string
 * @param string $encoding
 * @return string
 */
function my_substr($string, $start, $length, $encoding='utf-8')
 {
 	if (empty($string)) return $string;
  if(function_exists('mb_substr'))
   {
    return mb_substr($string, $start, $length, $encoding);
   }
  else
   {
    return substr($string, $start, $length);
   }
 }

/**
 * find position of first occurrence of string in a string using mb_strpos
 * if available or strpos if not
 *
 * @param string $haystack
 * @param mixed $needle
 * @param int $offset
 * @param string $encoding
 * @return string
 */
function my_strpos($haystack, $needle, $offset=0, $encoding='utf-8')
 {
  if(function_exists('mb_strpos'))
   {
    return mb_strpos($haystack, $needle, $offset, $encoding);
   }
  else
   {
    return strpos($haystack, $needle, $offset);
   }
 }

/**
 * encodes sender or recipient name
 *
 * @param string $name
 * @return string
 */
function encode_mail_name($name, $charset=CHARSET, $linefeed="\r\n")
 {
  $name = str_replace('"', '\\"', $name);
  if(preg_match("/(\.|\;|\")/", $name))
   {
    return '"'.my_mb_encode_mimeheader($name, $charset, "Q", $linefeed).'"';
   }
  else
   {
    return my_mb_encode_mimeheader($name, $charset, "Q", $linefeed);
   }
 }

/**
 * removes line breaks to avoid e-mail header injections
 *
 * @param string $string
 * @return string
 */
 function mail_header_filter($string)
   {
    return preg_replace("/(\015\012|\015|\012)/", '', $string);
   }

/**
 * encodes a given string by the MIME header encoding scheme using
 * mb_encode_mimeheader if available or base64_encode if not
 *
 * @param string $string
 * @param string $encoding
 * @param string $transfer_encoding
 * @return string
 */
function my_mb_encode_mimeheader($string, $charset, $transfer_encoding, $linefeed="\r\n")
 {
  if(function_exists('mb_internal_encoding') && function_exists('mb_encode_mimeheader'))
   {
    mb_internal_encoding($charset);
    $string = mb_encode_mimeheader($string, $charset, $transfer_encoding, $linefeed);
    return $string;
   }
  else
   {
    return '=?'.$charset.'?B?'.base64_encode($string).'?=';
   }
 }

/**
 * Encode string to quoted-printable.
 * Original written by Andy Prevost http://phpmailer.sourceforge.net
 * and distributed under the Lesser General Public License (LGPL) http://www.gnu.org/copyleft/lesser.html
 *
 * @return string
 */
function my_quoted_printable_encode($input, $line_max=76, $space_conv = false ) {
	$hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
	$lines = preg_split('/(?:\r\n|\r|\n)/', $input);
	$eol = "\n";
	$escape = '=';
	$output = '';
	//while(list(, $line) = each($lines)) {
	foreach ($lines as $line) {
		$linlen = strlen($line);
		$newline = '';
		for($i = 0; $i < $linlen; $i++) {
			$c = substr($line, $i, 1);
			$dec = ord( $c );
			if(($i == 0) && ($dec == 46)) { // convert first point in the line into =2E
				$c = '=2E';
			}
			if($dec == 32) {
				if($i==($linlen-1)) { // convert space at eol only
					$c = '=20';
				}
				elseif($space_conv) {
					$c = '=20';
				}
			}
			elseif(($dec == 61) || ($dec < 32) || ($dec > 126)) { // always encode "\t", which is *not* required
				$h2 = floor($dec/16);
				$h1 = floor($dec%16);
				$c = $escape.$hex[$h2].$hex[$h1];
			}
			if((strlen($newline) + strlen($c)) >= $line_max) { // CRLF is not counted
				$output .= $newline.$escape.$eol; //  soft line break; " =\r\n" is okay
				$newline = '';
				if($dec == 46) { // check if newline first character will be point or not
					$c = '=2E';
				}
			}
			$newline .= $c;
		} // end of for
		$output .= $newline.$eol;
	} // end of while
	return $output;
}

/**
 * sends an email
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $headers
 * @return string
 */
function my_mail($to, $subject, $message, $from='') {
	global $settings, $PHP_MAILER;

	if (isset($settings['php_mailer']) && $settings['php_mailer'] == 1 && isset($PHP_MAILER)) {
		$PHP_MAILER->clearAllRecipients();
		$PHP_MAILER->setFrom($settings['forum_email'], $settings['forum_name']);
		$PHP_MAILER->addAddress($to, '');
		
		if ($from != '') 
			$PHP_MAILER->addReplyTo($from, '');
		
		$PHP_MAILER->Subject = $subject;
		$PHP_MAILER->Body    = $message;
		if ($PHP_MAILER->ContentType != $PHP_MAILER::CONTENT_TYPE_PLAINTEXT)
			$PHP_MAILER->AltBody = strip_tags($message);

		$isSend = $PHP_MAILER->send();
		$PHP_MAILER->clearAllRecipients();
		
		//send the message, check for errors
		if  (!$isSend) {
			// remove comment for debugging and config e.g. 'SMTPDebug' => {1,2,3,4}
			//echo $PHP_MAILER->ErrorInfo;
		} 
		else {
			return true;
		}
	}
	else {
		$mail_header_separator = "\n"; // "\r\n" complies with RFC 2822 but might cause problems in some cases (see http://php.net/manual/en/function.mail.php)
	
		$mail_charset = defined(CHARSET) ? strtoupper(CHARSET) : 'UTF-8';
	
		$to = mail_header_filter($to);
		$subject = my_mb_encode_mimeheader(mail_header_filter($subject), $mail_charset, "Q", $mail_header_separator);
		$message = my_quoted_printable_encode($message);
		
		$headers = "From: " . encode_mail_name($settings['forum_name'], $mail_charset, $mail_header_separator)." <".$settings['forum_email'].">". $mail_header_separator;
		if ($from != '') 
			$headers .= "Reply-to: " . mail_header_filter($from) . $mail_header_separator;
		
		$headers .= "MIME-Version: 1.0" . $mail_header_separator;
		$headers .= "X-Sender-IP: ". $_SERVER['REMOTE_ADDR'] . $mail_header_separator;
		$headers .= "Content-Type: text/plain; charset=" . $mail_charset . $mail_header_separator;
		$headers .= "Content-Transfer-Encoding: quoted-printable";
	
		if ($settings['mail_parameter'] != '') {
			if (@mail($to, $subject, $message, $headers, $settings['mail_parameter'])) {
				return true;
			}
		}
		else {
			if (@mail($to, $subject, $message, $headers)) {
				return true;
			}
		}
	}
	return false;
}

/**
 * checks if the IP of the user is banned
 *
 * @author Nico Hoffmann <oxensepp at gmx dot de>
 * @param string $ip
 * @param array $banned_ips
 * @reurn bool
 */
function is_ip_banned($ip, $banned_ips)
 {
  foreach($banned_ips as $banned_ip) // go through every $banned_ip
   {
    if(strpos($banned_ip,'*')!==false) // $banned_ip contains "*" = > IP range
     {
      $ip_range = substr($banned_ip, 0, strpos($banned_ip, '*')); // fetch part before "*"
      if(strpos($ip, $ip_range)===0) // check if IP begins with part before "*"
       {
        return true;
       }
     }
    elseif(strpos($banned_ip,'/')!==false && preg_match("/(([0-9]{1,3}\.){3}[0-9]{1,3})\/([0-9]{1,2})/", $banned_ip, $regs)) // $banned_ip contains "/" => CIDR notation (the regular expression is only used if $banned_ip contains "/")
     {
      // convert IP into bit pattern:
      $n_user_leiste = '00000000000000000000000000000000'; // 32 bits
      $n_user_ip = explode('.',trim($ip));
      for ($i = 0; $i <= 3; $i++) // go through every byte
       {
        for ($n_j = 0; $n_j < 8; $n_j++) // ... check every bit
         {
          if($n_user_ip[$i] >= pow(2, 7-$n_j)) // set to 1 if necessary
           {
            $n_user_ip[$i] = $n_user_ip[$i] - pow(2, 7-$n_j);
            $n_user_leiste[$n_j + $i*8] = '1';
           }
         }
       }
      // analyze prefix length:
      $n_byte_array = explode('.',trim($regs[1])); // IP -> 4 Byte
      $n_cidr_bereich = $regs[3]; // prefix length
      // bit pattern:
      $n_bitleiste = '00000000000000000000000000000000';
      for ($i = 0; $i <= 3; $i++) // go through every byte
       {
        if ($n_byte_array[$i] > 255) // invalid
         {
          $n_cidr_bereich = 0;
         }
        for ($n_j = 0; $n_j < 8; $n_j++) // ... check every bit
         {
          if($n_byte_array[$i] >= pow(2, 7-$n_j)) // set to 1 if necessary
           {
            $n_byte_array[$i] = $n_byte_array[$i] - pow(2, 7-$n_j);
            $n_bitleiste[$n_j + $i*8] = '1';
           }
         }
       }
      // check if bit patterns match on the first n chracters:
      if (strncmp($n_bitleiste, $n_user_leiste, $n_cidr_bereich) == 0 && $n_cidr_bereich > 0)
       {
        return true;
       }
     }
    else // neither "*" nor "/" => simple comparison:
     {
      if($ip == $banned_ip)
       {
        return true;
       }
     }
   }
  return false;
 }

/**
 * checks if the user agent is banned
 *
 * @param array $banned_user_agents
 * @reurn bool
 */
function is_user_agent_banned($user_agent, $banned_user_agents)
 {
  foreach($banned_user_agents as $banned_user_agent)
   {
    if(strpos($user_agent,$banned_user_agent)!==false) // case sensitive, faster
     {
      return true;
     }
   }
  return false;
 }

/**
 * searches for banned words
 *
 * @param string $string
 * @reurn mixed
 */
function get_not_accepted_words($string)
 {
  global $db_settings, $connid;
  // check for not accepted words:
  $result=mysqli_query($connid, "SELECT list FROM ".$db_settings['banlists_table']." WHERE name = 'words' LIMIT 1");
  if(!$result) raise_error('database_error',mysqli_error($connid));
  $data = mysqli_fetch_array($result);
  mysqli_free_result($result);
  if (!empty($data['list']) && trim($data['list']) != '')
   {
    $not_accepted_words = explode("\n",$data['list']);
    foreach($not_accepted_words as $not_accepted_word)
     {
      if($not_accepted_word!='' && my_strpos($string, my_strtolower($not_accepted_word, CHARSET), 0, CHARSET)!==false)
       {
        $found_not_accepted_words[] = $not_accepted_word;
       }
     }
   }
  if(isset($found_not_accepted_words))
   {
    return $found_not_accepted_words;
   }
  else
   {
    return false;
   }
 }
 
/**
 * Returns the content of an external page
 * Using curl, file_get_contents and fsockopen
 *
 * If connection failed or the content is empty,
 * this function returns false
 *
 * @param string $url
 * @return mix $content
 */ 
function getExternalResource($url) {
	$content = false;
	// curl
	if (function_exists('curl_init')) {
		$options = array(
			CURLOPT_RETURNTRANSFER => true,     		// return web page
			CURLOPT_HEADER         => false,    		// no headers
			CURLOPT_FOLLOWLOCATION => true,     		// follow redirects
			CURLOPT_ENCODING       => "",       		// handle all encodings
			CURLOPT_USERAGENT      => "mylittleforum",	// user agent
			CURLOPT_AUTOREFERER    => false,    		// no redirect referer
			CURLOPT_CONNECTTIMEOUT => 15,       		// timeout on connect
			CURLOPT_TIMEOUT        => 15,       		// timeout on response
			CURLOPT_MAXREDIRS      =>  2,       		// allow max two redirects
			CURLOPT_SSL_VERIFYPEER => false,    		// Disabled SSL Cert checks
			CURLOPT_URL => $url                 		// URL to page
		);

		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$content = curl_exec($ch);
		if(curl_errno($ch)) 
			$content = false;
		curl_close($ch);
	}
	
	// file_get_content
	if (empty($content) && @file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
		$content = @file_get_contents($url);
	}
	
	// fsockopen
	if (empty($content)) {
		$url_parsed = @parse_url($url);
	
		if ($url_parsed === false || !isset($url_parsed["host"]))
			return false;
		
		switch ($url_parsed['scheme']) {
			case 'https':
				$scheme = 'ssl://';
				$port = 443;
				break;
			case 'http':
			default:
				$scheme = '';
				$port = 80;
			break;
		}
	
		$host = $url_parsed["host"];
		$path = isset($url_parsed["path"])  && !empty($url_parsed["path"])  ? $url_parsed["path"] : "/";
		$port = isset($url_parsed["port"])  && !empty($url_parsed["port"])  ? $url_parsed["port"] : $port;
		$path = isset($url_parsed["query"]) && !empty($url_parsed["query"]) ? $path . "?" . $url_parsed["query"] : $path;
	
		$out = "GET " . $path . " HTTP/1.0\r\nHost: " . $host . "\r\nConnection: Close\r\n\r\n";
		$fp = @fsockopen($scheme . $host, $port, $errno, $errstr, 15);
		if (!$fp)
			$content = false;
		else {
			@fwrite($fp, $out);
			$body = false;
			$content = "";
			while (!feof($fp)) {
				$str = fgets($fp, 1024);
				if($body) 
					$content .= $str;
				if($str == "\r\n") 
					$body = true;
			}
			@fclose($fp);
		}
	}
	
	if (empty($content))
		return false;
	return $content;
} 

/**
 * Check email via http://www.stopforumspam.com and returns true, 
 * if the adress is infamous otherwise false
 *
 * @param String $email
 * @return boolean $isInfamous
 */
function isInfamousEmail($email) {
	$url = "http://www.stopforumspam.com/api?email=" . urlencode(iconv('GBK', 'UTF-8', $email));
	$resource = getExternalResource($url);
	if ($resource) {
		$xml = new SimpleXMLElement($resource);
		return $xml->appears == 'yes';
	}
	return false;
}

/**
 * Check for new version of mylittleforum via ATOM-feed
 * https://github.com/ilosuna/mylittleforum/releases.atom
 *
 * returns the description of the latest version, if the current
 * version is not the latest one otherwise false
 *
 * @param String $currentVersion
 * @return mix $releaseInfo
 */
function checkUpdate($currentVersion = '0.0') {
	$baseURI = "https://github.com";
	$atomURI = $baseURI . "/ilosuna/mylittleforum/releases.atom";
	$resource = getExternalResource($atomURI);
	if ($resource) {
		$xml = new SimpleXMLElement($resource);
		$len = count($xml->entry);
		if ($len <= 0)
			return false;
		$xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');
		$updateDateOfActualVersion    = $xml->xpath("//atom:entry[1]/atom:updated");
		$lastVersion                  = $xml->xpath("//atom:entry[1]/atom:id");
		$updateDateOfInstalledVersion = $xml->xpath("//atom:entry/atom:id[substring(text(), string-length(text()) - string-length('" . htmlspecialchars($currentVersion) . "') + 1) = '" . htmlspecialchars($currentVersion) . "']/../atom:updated");	
		$updateDateOfActualVersion = strtotime($updateDateOfActualVersion[0]);
		$lastVersion = preg_replace("/.*?([^\/v?]+$)/u", "$1", $lastVersion[0]);

		if (empty($updateDateOfInstalledVersion))
			$updateDateOfInstalledVersion = 0;
		else
			$updateDateOfInstalledVersion = strtotime($updateDateOfInstalledVersion[0]);
		
		if ($lastVersion != $currentVersion && $updateDateOfActualVersion > $updateDateOfInstalledVersion) {
			$title      = $xml->xpath("//atom:entry[1]/atom:title/text()");
			$content    = $xml->xpath("//atom:entry[1]/atom:content/text()");
			$releaseURI = $xml->xpath("//atom:entry[1]/atom:link/@href");
			
			$release = (object) array(
				'title'   => (string)$title[0],
				'content' => (string)$content[0],
				'version' => (string)$lastVersion,
				'uri'     => (string)$releaseURI[0]->href
			);
			return $release;
		}
	}
	return false;
}

/**
 * checks for invalid characters, used for username checks
 *
 * @param string $string
 * @reurn bool
 */
function contains_special_characters($string) {
	if(preg_match("/([[:cntrl:]]|\p{Cf})/u", $string)) 
		return true; // control characters and soft hyphen
	if(preg_match("/(\x{200b})/u", $string)) 
		return true; // zero width space
	return false;
}

/**
 * gets available timezones
 *
 * @reurn array
 */
function get_timezones()
 {
  if(!$timezones_raw = @file('config/time_zones')) return false;
  if (!empty($timezones_raw)) {
   foreach($timezones_raw as $line)
    {
     $line = trim($line);
     if(!empty($line))
      {
       $timezones[] = $line;
      }
    }
   }
  if(isset($timezones)) return $timezones;
  else return false;
 }

/**
 * gets available languages
 *
 * @reurn array
 */
function get_languages($titles=false)
 {
  $handle=opendir('./'.LANG_DIR.'/');
  while($file = readdir($handle))
   {
    if(strrchr($file, '.')=='.lang')
     {
      $language_files[] = $file;
     }
   }
  closedir($handle);
  if(isset($language_files))
   {
    if(!$titles)
     {
      return $language_files;
     }
    else
     {
      natcasesort($language_files);
      $i=0;
      foreach($language_files as $file)
       {
        $t_language_files[$i]['identifier'] = $file;
        $t_language_files[$i]['title'] = ucfirst(str_replace('.lang','',$file));
        $title_parts = explode('.', $t_language_files[$i]['title']);
        if(isset($title_parts[1])) $t_language_files[$i]['title'] = $title_parts[0].' ('.$title_parts[1].')';      
        ++$i;
       }
      return $t_language_files;
     }
   }
  return false;
 }

/**
 * gets available themes
 *
 * @reurn array
 */
function get_themes($titles=false)
 {
  $handle=opendir('./'.THEMES_DIR.'/');
  while($dir = readdir($handle))
   {
    if($dir != '.' && $dir != '..' && is_dir('./'.THEMES_DIR.'/'.$dir) && file_exists('./'.THEMES_DIR.'/'.$dir.'/main.tpl'))
     {
      $themes[] = $dir;
     }
   }
  if(isset($themes))
   {
    if(!$titles)
     {
      return $themes;
     }
    else
     {
      natcasesort($themes);
      $i=0;
      foreach($themes as $t)
       {
        $t_themes[$i]['identifier'] = $t;
        $t_themes[$i]['title'] = str_replace('_',' ', $t);
        ++$i;
       }
      return $t_themes;
     }
   }
  else return false;
 }
 
/**
 * Returns the avatar image by user id
 *
 * @param int user_id
 * @return array [path, filename, path/filename] or false if not exists
 */ 
function getAvatar($user_id) {
	$avatar_images_path = 'images/avatars/';
	$fileList = glob( $avatar_images_path . intval($user_id) . "[_.]*{png,jpg,jpeg,gif,bmp}" , GLOB_BRACE);
	if (isset($fileList) && count($fileList) > 0) {
		foreach ($fileList as $file) {
			if (preg_match("/^(" . preg_quote($avatar_images_path, '/') . ")(" . preg_quote(intval($user_id)) . "(_\d+)?\.(jpg|gif|png|jpeg,bmp))$/", $file, $matches)) {
			//var_dump($matches);
				$filename = $matches[2];
				if (file_exists($avatar_images_path.$filename))
					return array($avatar_images_path, $filename, $avatar_images_path.$filename);
			}
		}
	}
	return false;
}

/**
 * check, if HTTPS was used for the current request
 *
 * @return boolean
 */
function isProtocolHTTPS() {
	if ((!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== 'off') or (!empty($_SERVER['SERVER_PORT']) and $_SERVER['SERVER_PORT'] == 443)) {
		return true;
	}
	return false;
}

/**
 * set the read or new status for a thread message
 *
 * @param array
 * @param integer
 * @return array
 */
function getMessageStatus($data, $lastVisit, $folded = false) {
	global $settings;
	if ($data['req_user'] !== NULL and is_numeric($data['req_user'])) {
		$data['is_read'] = true;
		$data['new'] = false;
	} else {
		$data['is_read'] = false;
		$data['new'] = false;
		if (($data['pid'] == 0 && $folded !== false) && ((isset($_SESSION[$settings['session_prefix'].'usersettings']['newtime']) && isset($data['last_reply']) && $_SESSION[$settings['session_prefix'].'usersettings']['newtime'] < $data['last_reply']) || ($lastVisit && $data['last_reply'] > $lastVisit))) {
			$data['new'] = true;
		} else if ((isset($_SESSION[$settings['session_prefix'].'usersettings']['newtime']) && isset($data['time']) && $_SESSION[$settings['session_prefix'].'usersettings']['newtime'] < $data['time']) || ($lastVisit && $data['time'] > $lastVisit)) {
			$data['new'] = true;
		}
	}
	return $data;
}

function deleteTags() {
	global $db_settings, $connid;
	// Entferne alle TAGS, die keine Referenz mehr besitzen.
	@mysqli_query($connid, "DELETE FROM `".$db_settings['tags_table']."` WHERE `id` NOT IN (
	SELECT `tid` FROM `".$db_settings['bookmark_tags_table']."`
	UNION ALL
	SELECT `tid` FROM `".$db_settings['entry_tags_table']."`
	)") or raise_error('database_error', mysqli_error($connid));
}

function setEntryTags($pid, $tags) {
	global $db_settings;
	setTags($pid, $tags, $db_settings['entry_tags_table']);
}

function setBookmarkTags($bid, $tags) {
	global $db_settings;
	setTags($bid, $tags, $db_settings['bookmark_tags_table']);
}

function setTags($id, $tags, $table_name) {
	global $db_settings, $connid;
	$tags = array_filter(array_map('trim', $tags), function($value) { return $value !== ''; });
	$escapedTags = array_map(function($tag) use($connid) { return mysqli_real_escape_string($connid, trim($tag)); }, array_values($tags));
	// Fuege neue TAGS hinzu, sofern die noch nicht vorhanden sind.
	@mysqli_query($connid, "INSERT IGNORE INTO `".$db_settings['tags_table']."` (`tag`) VALUES ('". implode("'), ('", $escapedTags) ."')") or raise_error('database_error', mysqli_error($connid));
	// Entferne die TAGS von der Tabelle
	@mysqli_query($connid, "DELETE FROM `".mysqli_real_escape_string($connid, $table_name)."` WHERE `bid` = " . intval($id) . " AND `tid` IN (SELECT `id` FROM `".$db_settings['tags_table']."` WHERE `tag` NOT IN ('" . implode("', '",  $escapedTags) . "') )  ") or raise_error('database_error', mysqli_error($connid));
	// Speichere die neuen TAGS zur ID in der Tabelle
	@mysqli_query($connid, "INSERT IGNORE INTO `".mysqli_real_escape_string($connid, $table_name)."` (`bid`, `tid`) (SELECT " . intval($id) . " AS `bid`, `id` FROM `".$db_settings['tags_table']."` WHERE `tag` IN ('" . implode("', '",  $escapedTags) . "') )  ") or raise_error('database_error', mysqli_error($connid));
	deleteTags();
}

function getEntryTags($id) {
	global $db_settings;
	return getTags($id, $db_settings['entry_tags_table']);
}

function getBookmarkTags($bid) {
	global $db_settings;
	return getTags($bid, $db_settings['bookmark_tags_table']);
}

function getTags($bid, $table_name) {
	global $db_settings, $connid;

	$result = mysqli_query($connid, "SELECT `tag` FROM `".$db_settings['tags_table']."` JOIN `".mysqli_real_escape_string($connid, $table_name)."` ON `".$db_settings['tags_table']."`.`id` = `tid` WHERE `bid` = " . intval($bid) . "") or raise_error('database_error', mysqli_error($connid));
	if (mysqli_num_rows($result) == 0) 
		return false;
	
	$tags = [];
	while($line = mysqli_fetch_array($result))
		$tags[] = $line['tag'];
	mysqli_free_result($result);
	return $tags;	
}

function deleteBookmark($id) {
	global $db_settings, $connid;
	
	@mysqli_query($connid, "DELETE FROM ".$db_settings['bookmark_table']."      WHERE `id`  = ". intval($id) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
	@mysqli_query($connid, "DELETE FROM ".$db_settings['bookmark_tags_table']." WHERE `bid` = ". intval($id)) or raise_error('database_error', mysqli_error($connid));
	
	deleteTags();
}

function addBookmark($user_id, $posting_id) {
	global $db_settings, $connid;
	
	@mysqli_query($connid, "INSERT INTO " . $db_settings['bookmark_table'] . "(order_id, user_id, posting_id, subject)
					SELECT COALESCE(MAX(`order_id`), 0) + 1, " . intval($user_id) . ", " . intval($posting_id) . ",
					(SELECT `subject` FROM " . $db_settings['forum_table'] . " WHERE " . $db_settings['forum_table'] . ".id = " . intval($posting_id) . ")
					FROM " . $db_settings['bookmark_table'] . " WHERE user_id = " . intval($user_id)) or raise_error('database_error', mysqli_error($connid));
					
	$bookmark_id = mysqli_insert_id($connid);

	// transfer tags from entry to saved bookmark
	if ($bookmark_id > 0) {
		@mysqli_query($connid, "INSERT INTO `" . $db_settings['bookmark_tags_table'] . "` (`bid`, `tid`) 
					(SELECT " . intval($bookmark_id) . " AS `bid`, `tid` FROM `" . $db_settings['entry_tags_table'] . "` 
					WHERE `" . $db_settings['entry_tags_table'] . "`.`bid` = " . intval($posting_id) . ")") or raise_error('database_error', mysqli_error($connid));
	}
}

function setReceiptTimestamp($offset = 0) {
	global $settings;
	// SPAM protection (Time (difference) between page reload)
	if (isset($_SESSION[$settings['session_prefix'] . 'receipt_timestamp']) && intval($_SESSION[$settings['session_prefix'] . 'receipt_timestamp']) > 0)
		$_SESSION[$settings['session_prefix'] . 'receipt_timestamp_difference'] = $_SERVER['REQUEST_TIME'] + $offset - $_SESSION[$settings['session_prefix'] . 'receipt_timestamp'];
	$_SESSION[$settings['session_prefix'] . 'receipt_timestamp'] = $_SERVER['REQUEST_TIME'];
}

/**
 * sends a status code, displays an error message and halts the script
 *
 * @param string $status_code
 */
function raise_error($error,$error_message='') {
	global $settings, $lang;
	if(empty($lang['language'])) 
		$lang['language'] ='en';
	if(empty($lang['charset'])) 
		$lang['charset'] ='utf-8';
	if(empty($lang['db_error'])) 
		$lang['db_error'] = 'Database error';
	if(empty($settings['forum_name'])) 
		$settings['forum_name'] = 'my little forum';
	$title = 'Error';
	$message = '';
	switch($error){
		case '403':
			header($_SERVER['SERVER_PROTOCOL'] . " 403 Forbidden");
			header("Status: 403 Forbidden");
			$title = '403 Forbidden';
			$message = 'You don\'t have permission to access this page.';
		break;
		case 'mysql_connect':
			header($_SERVER['SERVER_PROTOCOL'] . " 503 Service Unavailable");
			header("Status: 503 Service Unavailable");
			$title = 'Database error';
			$message = 'Could not connect to the MySQL database. The forum is probably not installed yet.';
			if($error_message!='') 
				$message .= '<br />MySQL error message: '.$error_message;
			break;
		case 'mysql_select_db':
			header($_SERVER['SERVER_PROTOCOL'] . " 503 Service Unavailable");
			header("Status: 503 Service Unavailable");
			$title = 'Database error';
			$message = 'The Database could not be selected. The script is probably not installed yet.';
			if($error_message!='') 
				$message .= '<br />MySQL error message: '.$error_message;
			break;
		case 'database_error':
			header($_SERVER['SERVER_PROTOCOL'] . " 503 Service Unavailable");
			header("Status: 503 Service Unavailable");
			$title = $lang['db_error'];
			if($error_message!='') 
				$message = $error_message;
			break;
		default:
			header($_SERVER['SERVER_PROTOCOL'] . " 503 Service Unavailable");
			header("Status: 503 Service Unavailable");
		break;
	}
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang['language']; ?>">
	<head>
	<title><?php echo $settings['forum_name'].' - '.$title; ?></title>
	<meta http-equiv="content-type" content="text/html; charset=<?php echo $lang['charset']; ?>" />
	<style type="text/css">
	<!--
	body               { color:#000; background:#fff; margin:0; padding:0; font-family: verdana, arial, sans-serif; font-size:100.1%; }
	h1                 { font-size:1.25em; }
	p,ul               { font-size:0.82em; line-height:1.45em; }
	//top              { margin:0; padding:0 20px 0 20px; height:4.4em; color:#000000; background:#d2ddea; border-bottom: 1px solid #bacbdf; line-height:4.4em;}
	//top h1           { font-size:1.7em; margin:0; padding:0; color:#000080; }
	//content          { padding:20px; }
	-->
	</style>
	</head>
	<body>
	<div id="top"><h1><?php echo $settings['forum_name']; ?></h1></div>
	<div id="content">
	<h1><?php echo $title; ?></h1>
	<p><?php echo $message; ?></p>
	</div>
	</body>
	</html><?php
	exit;
 }

?>
