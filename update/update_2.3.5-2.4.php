<?php
/******************************************************************************
* my little forum                                                             *
* update file to update from version 2.3.5 to version 2.4.*                   *
*                                                                             *
* Update instructions:                                                        *
* - Load up this file into the directory "update"                             *
* - log in as administrator                                                   *
* - go to the Admin --> Update                                                *
* - click on the update file and follow the further instructions.             *
******************************************************************************/

if(!defined('IN_INDEX')) exit;
if(empty($_SESSION[$settings['session_prefix'].'user_type'])) exit;
if($_SESSION[$settings['session_prefix'].'user_type']!=2) exit;

// update data:
$update['version'] = array('2.3.5','2.3.6','2.3.6.1','2.3.7');
$update['download_url'] = 'https://github.com/ilosuna/mylittleforum/releases/latest';
$update['message'] = '';

// changed files at the *end of the list* (folders followed by a slash like this: folder/):
// Note: Do *NOT* add 'break;' to a single case!!!
switch($settings['version']) {
	case '2.3.5':
	case '2.3.5 RC':
		$update['items'][] = 'index.php';
		$update['items'][] = 'themes/default/main.tpl';
		$update['items'][] = 'lang/';
		$update['items'][] = 'index.php';
		$update['items'][] = 'themes/default/main.tpl';
		$update['items'][] = 'lang/';

	case '2.3.6':
	case '2.3.6.1':
		$update['items'][] = 'index.php';
		$update['items'][] = 'includes/admin.inc.php';
		$update['items'][] = 'includes/contact.inc.php';
		$update['items'][] = 'includes/entry.inc.php';
		$update['items'][] = 'includes/login.inc.php';
		$update['items'][] = 'includes/main.inc.php';
		$update['items'][] = 'includes/posting.inc.php';
		$update['items'][] = 'includes/register.inc.php';
		$update['items'][] = 'includes/user.inc.php';
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';
		$update['items'][] = 'themes/default/main.tpl';
		$update['items'][] = 'themes/default/style.css';
		$update['items'][] = 'themes/default/style.min.css';
		$update['items'][] = 'lang/english.lang';
		$update['items'][] = 'modules/bad-behaviour/bad-behaviour/blacklist.inc.php';
		$update['items'][] = 'modules/bad-behaviour/bad-behaviour/core.inc.php';
		$update['items'][] = 'modules/smarty/';
		$update['items'][] = 'modules/stringparser_bbcode/';

	case '2.4.0':
		$update['items'][] = 'index.php';											// #39

		$update['items'][] = 'js/';													// #39, #47

		$update['items'][] = 'includes/';											// #39, #42, #50, #54, #57, #64
		
		$update['items'][] = 'themes/default/main.tpl';								// #39
		$update['items'][] = 'themes/default/style.css';							// #39, #41, #56, #64
		$update['items'][] = 'themes/default/style.min.css';						// #39, #41, #56, #64
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';			// #56
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';			// #39, #47
		$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';		// #39, #50, #64
		$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';			// #39, #64
		$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';			// #39
		$update['items'][] = 'themes/default/subtemplates/contact.inc.tpl';			// #39
		$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';			// #39
		$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';	// #39
		$update['items'][] = 'themes/default/subtemplates/bookmark.inc.tpl';		// #39
		$update['items'][] = 'themes/default/images/bg_sprite_3.png';				// #39
		$update['items'][] = 'themes/default/images/bg_sprite_4.png';				// #64
		
		$update['items'][] = 'lang/';												// #39, #56, #64, #68
	
	
		// !!!Do *NOT* add 'break;' to a single case!!!
		// This is the only break to avoid the use of the default-case!
		break;
	default:
		$update['items'][] = 'includes/';
		$update['items'][] = 'js/';
		$update['items'][] = 'lang/';
		$update['items'][] = 'modules/';
		$update['items'][] = 'templates (not required anymore)/';
		$update['items'][] = 'templates_c (clear)/';
		$update['items'][] = 'themes/';
		$update['items'][] = 'config/time_zones';
		$update['items'][] = 'index.php';
		break;
}

// Remove duplicate entries in array
$update['items'] = array_unique($update['items']);
// Look for unique folders
$folders = array_unique(preg_grep("/\w+\/$/i", $update['items']));
if (!empty($folders)) {
	// Remove folders from list to keep the order (files, folders)
	$update['items'] = array_diff($update['items'], $folders);
	
	// Remove sub-folders from folder list
	$tmp = $folders;
	foreach ($folders as $folder) {
	    $removeFolders = preg_grep("/^".preg_quote($folder, "/").".+/i", $folders);
		if (empty($removeFolders))
			continue;
		$tmp = array_diff($tmp, $removeFolders);
	}
	$folders = $tmp;
	
	// Remove single files from list if and only if the complete folder is in list
	foreach ($folders as $folder) {
		$removeFiles = preg_grep("/^".preg_quote($folder, "/").".+/i", $update['items']);
		if (empty($removeFiles))
			continue;
		$update['items'] = array_diff($update['items'], $removeFiles);
	}
	
	// Add folders at the end of the files list to keep the order (files, folders)
	$update['items'] = array_merge($update['items'], $folders);
}

// check version:
if(!file_exists('config/VERSION')) {
	$update['errors'][] = 'Error in line '.__LINE__.': Missing the file config/VERSION.';
}
if (empty($update['errors'])) {
	$newVersion = trim(file_get_contents('config/VERSION'));
	if ($newVersion <= $settings['version']) {
		$update['errors'][] = 'Error in line '.__LINE__.': The version you want to install (see string in config/VERSION) must be greater than the current installed version. Current version: '. htmlspecialchars($settings['version']) .', version you want to install: '.  htmlspecialchars($newVersion) .'.';
	}
	if(!in_array($settings['version'], $update['version'])) {
		$update['errors'][] = 'Error in line '.__LINE__.': This update file doesn\'t work with the current version.';
	}
}

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8','2.3'))) {
	if(!@mysqli_query($connid, "INSERT INTO ".$db_settings['settings_table']." VALUES ('stop_forum_spam', '0')")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
}

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8','2.3','2.3.1','2.3.2','2.3.3','2.3.4','2.3.5 RC','2.3.5'))) {
	if(!@mysqli_query($connid, "ALTER TABLE ".$db_settings['userdata_table']." CHANGE last_login last_login timestamp NULL default CURRENT_TIMESTAMP")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
}
 
if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8','2.3','2.3.1','2.3.2','2.3.3','2.3.4','2.3.5 RC','2.3.5','2.3.6','2.3.6.1'))) {
	if(!@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET `value` = 10 WHERE `name` = 'temp_block_ip_after_repeated_failed_logins' AND `value` > 0")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
}

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8','2.3','2.3.1','2.3.2','2.3.3','2.3.4','2.3.5 RC','2.3.5','2.3.6','2.3.6.1','2.4.0'))) {
	$table_prefix = preg_replace('/settings$/u', '', $db_settings['settings_table']);
	// add new database table
	if (file_exists("./config/db_settings.php") && is_writable("./config/db_settings.php")) {
		$db_settings['bookmark_table'] = $table_prefix . 'bookmarks';
		$db_settings['read_status_table'] = $table_prefix . 'read_entries';
		$db_settings_file = @fopen("./config/db_settings.php", "w") or $update['errors'][] = str_replace("[CHMOD]",$chmod,$lang['error_overwrite_config_file']);
		if(empty($update['errors'])) {
			flock($db_settings_file, 2);
			fwrite($db_settings_file, "<?php\n");
			fwrite($db_settings_file, "\$db_settings['host']                 = '". addslashes($db_settings['host']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['user']                 = '". addslashes($db_settings['user']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['password']             = '". addslashes($db_settings['password']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['database']             = '". addslashes($db_settings['database']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['settings_table']       = '". addslashes($db_settings['settings_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['forum_table']          = '". addslashes($db_settings['forum_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['category_table']       = '". addslashes($db_settings['category_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['userdata_table']       = '". addslashes($db_settings['userdata_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['smilies_table']        = '". addslashes($db_settings['smilies_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['pages_table']          = '". addslashes($db_settings['pages_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['banlists_table']       = '". addslashes($db_settings['banlists_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['useronline_table']     = '". addslashes($db_settings['useronline_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['login_control_table']  = '". addslashes($db_settings['login_control_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['entry_cache_table']    = '". addslashes($db_settings['entry_cache_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['userdata_cache_table'] = '". addslashes($db_settings['userdata_cache_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['bookmark_table']       = '". addslashes($db_settings['bookmark_table']) ."';\n");
			fwrite($db_settings_file, "\$db_settings['read_status_table']    = '". addslashes($db_settings['read_status_table']) ."';\n");
			fwrite($db_settings_file, "?".">\n");
			flock($db_settings_file, 3);
			fclose($db_settings_file);
			// new tables
			if(!@mysqli_query($connid, "CREATE TABLE ".$db_settings['bookmark_table']." (id int(11) NOT NULL AUTO_INCREMENT,user_id int(11) NOT NULL,posting_id int(11) NOT NULL,time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,subject varchar(255) NOT NULL,order_id int(11) NOT NULL DEFAULT '0',PRIMARY KEY (id),UNIQUE KEY UNIQUE_uid_pid (user_id,posting_id)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "CREATE TABLE ".$db_settings['read_status_table']." (user_id int(11) UNSIGNED NOT NULL, posting_id int(11) UNSIGNED NOT NULL, time timestamp NOT NULL, PRIMARY KEY (user_id, posting_id)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			// rework the settings table, add primary key, restore the settings
			$newSettingsResult = false;
			$tempTableName = $db_settings['settings_table'] ."_tmp";
			if(!@mysqli_query($connid, "ALTER TABLE `". $db_settings['settings_table'] ."` RENAME `". $tempTableName ."`;")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			} else {
				$newSettingsResult = @mysqli_query($connid, "CREATE TABLE `". $db_settings['settings_table'] ."` (`name` varchar(255) NOT NULL, `value` varchar(255) NOT NULL default '', PRIMARY KEY (`name`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;");
				if ($newSettingsResult !== false) {
					$newSettingsResult = @mysqli_query($connid, "INSERT INTO `". $db_settings['settings_table'] ."` (`name`, `value`) SELECT t.`name`, t.`value` FROM `". $tempTableName ."` AS t ON DUPLICATE KEY UPDATE `value` = t.`value`");
					if(!@mysqli_query($connid, "DROP TABLE `". $tempTableName ."`;")) {
						$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
					}
				} else {
					$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				}
			}
			// add a new setting for the duration an entry will be marked as read
			if(!@mysqli_query($connid, "INSERT INTO `".$db_settings['settings_table']."` (`name`, `value`) VALUES ('read_state_expiration_date', '150') ON DUPLICATE KEY UPDATE `value` = `value`")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			// transfer the IDs of the read entries from the user data to the new read status table
			$numberOfStoredEntriesResult = @mysqli_query($connid, "SELECT `value` FROM `".$db_settings['settings_table']."` WHERE `name` = 'max_read_items' LIMIT 1");
			if(mysqli_num_rows($numberOfStoredEntriesResult) == 1) {
				$numberOfStoredEntriesValues = mysqli_fetch_array($numberOfStoredEntriesResult);
				mysqli_free_result($numberOfStoredEntriesResult);
				
				$numberOfStoredEntries = max(200, $numberOfStoredEntriesValues['value']); // Maximum number of stored entries of a single user
				$transferReadStateSQL = "INSERT INTO `".$db_settings['read_status_table']."` (`user_id`, `posting_id`, `time`) SELECT `user_id`, SUBSTRING_INDEX(SUBSTRING_INDEX(`entries_read`, ',', `numbers`.`n`), ',', -1) AS `posting_id`, CURRENT_TIMESTAMP AS `timestamp` FROM (SELECT 1 AS `n` ";
				for ($n = 2; $n <= $numberOfStoredEntries; $n++) {
					$transferReadStateSQL .= " UNION ALL SELECT ".$n;
				}
				$transferReadStateSQL .= " ) `numbers` INNER JOIN `".$db_settings['userdata_table']."` ON CHAR_LENGTH(`entries_read`)-CHAR_LENGTH(REPLACE(`entries_read`, ',', '')) >= `numbers`.`n`-1 WHERE `entries_read` <> '' ORDER BY `user_id`, `n`;";
				if(!@mysqli_query($connid, $transferReadStateSQL)) {
					$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				}
				if(!@mysqli_query($connid, "ALTER TABLE `".$db_settings['userdata_table']."` DROP COLUMN `entries_read`;")) {
					$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				}
			}
		}
	}
	else {
		$update['errors'][] = "File ./config/db_settings.php not found or unwritable. New database table '" . htmlspecialchars($table_prefix) . "bookmarks' could not be added!";
	}
}

if(empty($update['errors'])) {
	if(!@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='". mysqli_real_escape_string($connid, $newVersion) ."' WHERE name = 'version'")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
}

?>