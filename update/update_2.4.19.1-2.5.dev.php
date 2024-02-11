<?php
/******************************************************************************
* my little forum                                                             *
* update file to update from version 2.4.19.1 to version 2.5.*                *
*                                                                             *
* Update instructions:                                                        *
* - Load up this file into the directory "update"                             *
* - load up the file config/VERSION ti the directory config                   *
* - log in as administrator                                                   *
* - go to the Admin --> Update                                                *
* - click on the update file and follow the further instructions.             *
*******************************************************************************/

if(!defined('IN_INDEX')) exit;
if(empty($_SESSION[$settings['session_prefix'].'user_type'])) exit;
if($_SESSION[$settings['session_prefix'].'user_type']!=2) exit;

// update data:
$update['version'] = array('2.4.19', '2.4.19.1', '2.4.20', '2.4.21', '2.4.22', '2.4.23', '2.4.24', '2.4.99.0', '2.4.99.1', '2.4.99.2', '2.4.99.3', '20220508.1', '20220509.1', '20220517.1', '20220529.1', '20220803.1');
$update['download_url'] = 'https://github.com/ilosuna/mylittleforum/releases/latest';
$update['message'] = '';

/**
 * comparision of version numbers
 *
 * @param array
 * @return bool
 */
function compare_versions($versions) {
	if (!is_array($versions)) return false;
	if (!array_key_exists('old', $versions)) return false;
	if (!array_key_exists('new', $versions)) return false;
	$test['old'] = explode('.', $versions['old']);
	$test['new'] = explode('.', $versions['new']);
	$cntOld = count($test['old']);
	$cntNew = count($test['new']);
	if ($cntOld < $cntNew) {
		$c = $cntNew - $cntOld;
		for ($i = 0; $i < $c; $i++) {
			$test['old'][] = '0';
		}
	}
	if ($cntNew < $cntOld) {
		$c = $cntOld - $cntNew;
		for ($i = 0; $i < $c; $i++) {
			$test['new'][] = '0';
		}
	}
	$c = count($test['old']);
	for ($i = 0; $i < $c; $i++) {
		if ($test['new'][$i] > $test['old'][$i]) return true;
	}
	return false;
}

function reorderUpgradeFiles($update_files) {
	// Remove duplicate entries in array
	$update_files = array_unique($update_files);
	// Look for unique folders
	$folders = array_unique(preg_grep("/\w+\/$/i", $update_files));
	if (!empty($folders)) {
		// Remove folders from list to keep the order (files, folders)
		$update_files = array_diff($update_files, $folders);
		
		// Remove sub-folders from folder list
		$tmp = $folders;
		foreach ($folders as $folder) {
			$removeFolders = preg_grep("/^".preg_quote($folder, "/").".+/i", $folders);
			if (empty($removeFolders))
				continue;
			$tmp = array_diff($tmp, $removeFolders);
		}
		$folders = $tmp;
		sort($folders);
		
		// Remove single files from list if and only if the complete folder is in list
		foreach ($folders as $folder) {
			$removeFiles = preg_grep("/^".preg_quote($folder, "/").".+/i", $update_files);
			if (empty($removeFiles))
				continue;
			$update_files = array_diff($update_files, $removeFiles);
		}
		sort($update_files);
		
		// Add folders at the end of the files list to keep the order (files, folders)
		$update_files = array_merge($update_files, $folders);
	}
	return $update_files;
}

function write_new_version_string_2_db($connid, $new_version) {
	global $db_settings;
	$updated_version = false;
	// write the new version number to the database
	if (!@mysqli_query($connid, "UPDATE ".$db_settings['temp_infos_table']." SET value='". mysqli_real_escape_string($connid, $new_version) ."' WHERE name = 'version'")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	else {
		// Set new Version for update script output, taken from VERSION file.
		$updated_version = $new_version;
	}
	return $updated_version;
}


// check version:
if (!file_exists('config/VERSION')) $update['errors'][] = 'Error in line '.__LINE__.': Missing the file config/VERSION. Load it up from your script package (config/VERSION) before proceeding.';


// check the MySQL- or MariaDB-version
// minimal versions: MySQL 5.7.7 or MariaDB 10.2.2
$resDatabaseVersion = mysqli_query($connid, "SHOW VARIABLES WHERE variable_name IN('version', 'version_comment');");
if ($resDatabaseVersion === false)  $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
if (empty($update['errors'])) {
	$databaseVersion = mysqli_fetch_assoc($resDatabaseVersion);
	// read the value for the server type (MySQL or MariaDB)
	$rawDBType = $databaseVersion['version_comment'];
	if (preg_match("/MariaDB/gu", $rawDBType)) {
		$serverType = "MariaDB";
	} else {
		$serverType = "MySQL";
	}
	// read the provided version string
	if (!empty($databaseVersion['version']) and substr_count($databaseVersion['version'], "-") > 0) {
		$rawDBVersion = explode("-", $databaseVersion['version']);
		$serverVersion = $rawDBVersion[0];
	} else {
		$serverVersion = $databaseVersion['version'];
	}
	mysqli_free_result($resDatabaseVersion);
	unset($databaseVersion, $rawDBType, $rawDBVersion);
	
	if (!empty($serverVersion) and preg_match("/\d{1,2}.\d{1,2}(\.\d{1,3})?/gu", $serverVersion)) {
		if ($serverType == "MariaDB" and version_compare($dbVersion, "10.2.2", "<") === true) {
			$update['errors'][] = "The version of MariaDB that runs on your server (". htmlspecialchars($dbVersion) .") is to old. You need to run your database server at least with MariaDB 10.2.2.";
		}
		if ($serverType == "MySQL" and version_compare($dbVersion, "5.7.7", "<") === true) {
			$update['errors'][] = "The version of MySQL that runs on your server (". htmlspecialchars($dbVersion) .") is to old. You need to run your database server at least with MySQL 5.7.7.";
		}
	} else {
		// there is nothing returned from the database
		$update['errors'][] = "The script was not able to interpret the server type and version of your database. Please report this in the project forum with this exact error message.";
	}
}


// check if all e-mail-addresses in the userdata table are unique
$resEmailMultiUse = mysqli_query($connid, "SELECT `user_id`, `user_name`, `user_email` FROM `" . $db_settings['userdata_table'] . "` WHERE `user_email` IN(SELECT `user_email` FROM `" . $db_settings['userdata_table'] . "` GROUP BY `user_email` HAVING COUNT(`user_email`) > 1) ORDER BY `user_email` ASC, `user_name` ASC");
if ($resEmailMultiUse === false) {
	$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
} else {
	if (mysqli_num_rows($resEmailMultiUse) > 0) {
		// list the doubled user names
		$update['errors'][]  = '<h3><strong>Attention</strong>: Found non-unique e-mail-addresses in the user accounts!</h3>';
		$update['errors'][] .= '<p>Please make the e-mail-addresses unique and inform the users in question about the changes. <em>Tip:</em> Open the links to the user edit forms in a new browser tab and solve the issues. After editing all listed users start the update process again.</p>';
		$update['errors'][] .= '<table>';
		$update['errors'][] .= '<tr><th>E-mail-address</th><th>Username</th></tr>';
		while ($row = mysqli_fetch_assoc($resEmailMultiUse)) {
			$update['errors'][] .= '<tr><td>'. htmlspecialchars($row['user_email']) .'</td><td><a href="?mode=admin&amp;edit_user='. intval($row['user_id']) .'">'. htmlspecialchars($row['user_name']) .'</a></td></tr>'."\n";
		}
		$update['errors'][] .= '</table>';
	}
	mysqli_free_result($resEmailMultiUse);
}


// check if the version number is greater than the current version
if (empty($update['errors'])) {
	$newVersion = file_get_contents('config/VERSION');
	if (empty($newVersion)) die('Error in line '.__LINE__.': No value for the script version in the file config/VERSION.');
	else $newVersion = trim($newVersion);
	if (compare_versions(array('old' => $settings['version'], 'new' => $newVersion)) !== true) {
		$update['errors'][] = 'Error in line '.__LINE__.': The version you want to install (see string in config/VERSION) must be greater than the current installed version. Current version: '. htmlspecialchars($settings['version']) .', version you want to install: '.  htmlspecialchars($newVersion) .'. Please check also if you uploaded the actual file version of config/VERSION. Search therefore for the file date and compare it with the date from the installation package.';
	}
	if (!in_array($settings['version'], $update['version'])) {
		$update['errors'][] = 'Error in line '.__LINE__.': This update file doesn\'t work with the current version.';
	}
}


// disable the forum until database update is done
if (empty($update['errors'])) {
	if(!@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET  value = '0' WHERE name =  'forum_enabled'")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
}


// upgrade from any stable 2.4-version with the exception of the pre releases of the 2.5-branch (2.4.99.x)
if (empty($update['errors']) && in_array($settings['version'], array('2.4.19', '2.4.19.1', '2.4.20', '2.4.21', '2.4.22', '2.4.23', '2.4.24'))) {
// The database request succeeded
	if (!file_exists("./config/db_settings.php") || !is_writable("./config/db_settings.php")) {
		$update['errors'][] .= 'The config file does not exist or is not writable. Please ensure the file config/db_settings.php to exist and to be writable.';
	}
	
	if (empty($errors)) {
		$table_prefix = preg_replace('/settings$/u', '', $db_settings['settings_table']);
		// enlive the config file template
		$db_settings_file  = "<?php\r\n";
		$db_settings_file .= "\$db_settings['host']                 = '{#ph-dbhost}';\r\n";
		$db_settings_file .= "\$db_settings['user']                 = '{#ph-dbuser}';\r\n";
		$db_settings_file .= "\$db_settings['password']             = '{#ph-dbpass}';\r\n";
		$db_settings_file .= "\$db_settings['database']             = '{#ph-dbname}';\r\n";
		$db_settings_file .= "\r\n";
		$db_settings_file .= "\$db_settings['settings_table']       = '{#ph-tbl-settings}';\r\n";
		$db_settings_file .= "\$db_settings['forum_table']          = '{#ph-tbl-entries}';\r\n";
		$db_settings_file .= "\$db_settings['category_table']       = '{#ph-tbl-categories}';\r\n";
		$db_settings_file .= "\$db_settings['userdata_table']       = '{#ph-tbl-userdata}';\r\n";
		$db_settings_file .= "\$db_settings['smilies_table']        = '{#ph-tbl-smilies}';\r\n";
		$db_settings_file .= "\$db_settings['pages_table']          = '{#ph-tbl-pages}';\r\n";
		$db_settings_file .= "\$db_settings['banlists_table']       = '{#ph-tbl-banlists}';\r\n";
		$db_settings_file .= "\$db_settings['useronline_table']     = '{#ph-tbl-online}';\r\n";
		$db_settings_file .= "\$db_settings['login_control_table']  = '{#ph-tbl-login-ctrl}';\r\n";
		$db_settings_file .= "\$db_settings['entry_cache_table']    = '{#ph-tbl-cache-entries}';\r\n";
		$db_settings_file .= "\$db_settings['userdata_cache_table'] = '{#ph-tbl-cache-userdata}';\r\n";
		$db_settings_file .= "\$db_settings['bookmark_table']       = '{#ph-tbl-bookmarks}';\r\n";
		$db_settings_file .= "\$db_settings['bookmark_tags_table']  = '{#ph-tbl-tags-bookmarks}';\r\n";
		$db_settings_file .= "\$db_settings['entry_tags_table']     = '{#ph-tbl-tags-entries}';\r\n";
		$db_settings_file .= "\$db_settings['tags_table']           = '{#ph-tbl-tags}';\r\n";
		$db_settings_file .= "\$db_settings['read_status_table']    = '{#ph-tbl-read-status}';\r\n";
		$db_settings_file .= "\$db_settings['temp_infos_table']     = '{#ph-tbl-temp-infos}';\r\n";
		$db_settings_file .= "\$db_settings['subscriptions_table']  = '{#ph-tbl-subscriptions}';\r\n";
		$db_settings_file .= "\$db_settings['b8_wordlist_table']    = '{#ph-tbl-b8-wordlist}';\r\n";
		$db_settings_file .= "\$db_settings['b8_rating_table']      = '{#ph-tbl-b8-rating}';\r\n";
		$db_settings_file .= "\$db_settings['akismet_rating_table'] = '{#ph-tbl-akismet-rating}';\r\n";
		$db_settings_file .= "\$db_settings['uploads_table']        = '{#ph-tbl-uploads}';\r\n";
		// add new database tables to the array $db_settings
		$db_settings['b8_wordlist_table'] = $table_prefix . 'b8_wordlist';
		$db_settings['b8_rating_table'] = $table_prefix . 'b8_rating';
		$db_settings['akismet_rating_table'] = $table_prefix . 'akismet_rating';
		$db_settings['uploads_table'] = $table_prefix . 'uploads';
		//replace the placeholders
		$db_settings_file = str_replace('{#ph-dbhost}', addslashes($db_settings['host']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-dbuser}', addslashes($db_settings['user']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-dbpass}', addslashes($db_settings['password']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-dbname}', addslashes($db_settings['database']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-settings}', addslashes($db_settings['settings_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-entries}', addslashes($db_settings['forum_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-categories}', addslashes($db_settings['category_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-userdata}', addslashes($db_settings['userdata_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-smilies}', addslashes($db_settings['smilies_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-pages}', addslashes($db_settings['pages_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-banlists}', addslashes($db_settings['banlists_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-online}', addslashes($db_settings['useronline_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-login-ctrl}', addslashes($db_settings['login_control_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-cache-entries}', addslashes($db_settings['entry_cache_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-cache-userdata}', addslashes($db_settings['userdata_cache_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-bookmarks}', addslashes($db_settings['bookmark_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-tags-bookmarks}', addslashes($db_settings['bookmark_tags_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-tags-entries}', addslashes($db_settings['entry_tags_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-tags}', addslashes($db_settings['tags_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-read-status}', addslashes($db_settings['read_status_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-temp-infos}', addslashes($db_settings['temp_infos_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-subscriptions}', addslashes($db_settings['subscriptions_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-b8-wordlist}', addslashes($db_settings['b8_wordlist_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-b8-rating}', addslashes($db_settings['b8_rating_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-akismet-rating}', addslashes($db_settings['akismet_rating_table']), $db_settings_file);
		$db_settings_file = str_replace('{#ph-tbl-uploads}', addslashes($db_settings['uploads_table']), $db_settings_file);
		// open the file to overwrite its content
		$db_settings_fp = @fopen("./config/db_settings.php", "w") or $update['errors'][] = str_replace("[CHMOD]", $chmod, $lang['error_overwrite_config_file']);
		if (empty($update['errors'])) {
			// write the new content to the config file
			flock($db_settings_fp, 2);
			fwrite($db_settings_fp, $db_settings_file);
			flock($db_settings_fp, 3);
			fclose($db_settings_fp);
			// drop possibly existing new tables from previous tests with a pre release
			if (!@mysqli_query($connid, "DROP TABLE IF EXISTS `" . $db_settings['akismet_rating_table'] . "`")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			if (!@mysqli_query($connid, "DROP TABLE IF EXISTS `" . $db_settings['b8_rating_table'] . "`")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			if (!@mysqli_query($connid, "DROP TABLE IF EXISTS `" . $db_settings['b8_wordlist_table'] . "`")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			if (!@mysqli_query($connid, "DROP TABLE IF EXISTS `" . $db_settings['uploads_table'] . "`")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			// new tables
			if (!@mysqli_multi_query($connid, "CREATE TABLE IF NOT EXISTS `" . $db_settings['akismet_rating_table'] . "` (`eid` int(11) NOT NULL, `spam` tinyint(1) NOT NULL DEFAULT '0', `spam_check_status` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`eid`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_multi_query($connid, "CREATE TABLE IF NOT EXISTS `" . $db_settings['b8_rating_table'] . "` (`eid` int(11) NOT NULL, `spam` tinyint(1) NOT NULL DEFAULT '0', `training_type` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`eid`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_multi_query($connid, "CREATE TABLE IF NOT EXISTS `" . $db_settings['b8_wordlist_table'] . "` (`token` varchar(128) character set utf8mb4 collate utf8mb4_bin NOT NULL DEFAULT '', `count_ham` int unsigned default NULL, `count_spam` int unsigned default NULL, PRIMARY KEY (`token`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_bin;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `" . $db_settings['uploads_table'] . "` (`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, `uploader` int(10) UNSIGNED NULL, `filename` varchar(64) NULL, `tstamp` datetime NULL, PRIMARY KEY (id)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_bin;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// change the table engine from MyISAM to InnoDB for the previously existing tables
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['settings_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['category_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['pages_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['banlists_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_cache_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_cache_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			// all newer tables (starting with 2.3.99.x) was alredy created with engine InnoDB
			
			
			// changes in the setting table
			$uaa = ($settings['user_area_public'] == 0) ? 2 : 1;
			if (!@mysqli_query($connid, "INSERT INTO `" . $db_settings['settings_table'] . "` (`name`, `value`) VALUES ('uploads_per_page', '20'), ('bbcode_latex', '0'), ('bbcode_latex_uri', 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js'), ('b8_entry_check', '1'), ('b8_auto_training', '1'), ('b8_spam_probability_threshold', '80'), ('user_area_access', ". intval($uaa) ."), ('b8_mail_check', '0'), ('php_mailer', '0'),  ('delete_inactive_users', '30'), ('notify_inactive_users', '3'), ('link_open_target', '');")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET `name`='spam_check_registered' WHERE `name`='akismet_check_registered';")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "` WHERE name IN('bbcode_flash', 'bbcode_tex', 'flash_default_height', 'flash_default_width', 'user_area_public');")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// changes in the new tables
			if (!@mysqli_query($connid, "INSERT INTO `" . $db_settings['b8_wordlist_table'] . "` (`token`, `count_ham`, `count_spam`) VALUES ('b8*dbversion', '3', NULL), ('b8*texts', '0', '0');")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "INSERT INTO `" . $db_settings['akismet_rating_table'] . "` (`eid`, `spam`, `spam_check_status`) SELECT `id`, `spam`, `spam_check_status` FROM `" . $db_settings['forum_table'] ."`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "INSERT INTO `" . $db_settings['b8_rating_table'] . "` (`eid`, `spam`, `training_type`) SELECT `id`, `spam`, 0 FROM `" . $db_settings['forum_table'] ."`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "` ADD KEY `B8_spam` (`spam`), ADD KEY `B8_training_type` (`training_type`);")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// changes in the user data table
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` CHANGE `user_name` `user_name` VARCHAR(128) NOT NULL, CHANGE `user_email` `user_email` VARCHAR(255);")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` CHANGE `user_name` `user_name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL, CHANGE `user_email` `user_email` VARCHAR(255) CHARACTER SET utf8 NOT NULL;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` DROP INDEX `user_name`, ADD UNIQUE KEY `key_user_name` (`user_name`), ADD UNIQUE KEY `key_user_email` (`user_email`);")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` ADD `inactivity_notification` BOOLEAN NOT NULL DEFAULT FALSE, ADD `browser_window_target` tinyint(4) NOT NULL DEFAULT '0' AFTER `user_lock`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			
			// changes in the user data cache table
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_cache_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// changes in the forum/entries table
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` DROP `spam`, DROP `spam_check_status`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."` LIKE 'email_notification'");
			if (mysqli_num_rows($rEN_exists) > 0) {
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` DROP `email_notification`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// changes in the entry cache table
			if(!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_cache_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// changes in the banlist table
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['banlists_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// changes in the bookmarks table
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// changes in the categories table
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['category_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// changes in the pages table
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['pages_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			// changes in the tags table
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "` CHANGE `tag` `tag` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			
			// write the new version number to the database
			if (empty($update['errors'])) {
			$new_version_set = write_new_version_string_2_db($connid, $newVersion);
				if ($new_version_set === false) {
					$update['errors'][] = 'Database error, could not write the new version string to the database.';
				}
			}
			// collect the file and directory names to upgrade
			if (empty($update['errors'])) {
				$update['items'][] = 'config/php_mailer.php';                                       // #498
				$update['items'][] = 'config/b8_config.php';                                        // #493, #557, #561, #562
				
				$update['items'][] = 'includes/admin.inc.php';                                      // #364, #367, #390, #410, #427, #456, #478, #489, #523, #565, #575, #589, #594, #623, #659
				$update['items'][] = 'includes/auto_login.inc.php';                                 // #526, #575, #594
				$update['items'][] = 'includes/avatar.inc.php';                                     // #554
				$update['items'][] = 'includes/b8.inc.php';                                         // #557, #561, #562
				$update['items'][] = 'includes/bookmark.inc.php';                                   // #560
				$update['items'][] = 'includes/contact.inc.php';                                    // #489, #501, #505, #594
				$update['items'][] = 'includes/entry.inc.php';                                      // #505, #509, #536, #585, #611
				$update['items'][] = 'includes/functions.inc.php';                                  // #377, #390, #410, #467, #478, #498, #499, #512, #519. #520, #524, #526, #528, #540, #554, #571, #587, #589, #593, #594, #595, #603, #606, #619, #623, #647, #650, #667
				$update['items'][] = 'includes/index.inc.php';                                      // #472, #521, #554, #611
				$update['items'][] = 'includes/insert_flash.inc.php (remove)';                      // #390
				$update['items'][] = 'includes/js_defaults.inc.php';                                // #377, #390, #550, #575, #578, #589
				$update['items'][] = 'includes/login.inc.php';                                      // #526, #550, #554, #594
				$update['items'][] = 'includes/mailer.inc.php';                                     // #498, #583
				$update['items'][] = 'includes/main.inc.php';                                       // #510, #526, #553, #594
				$update['items'][] = 'includes/posting.inc.php';                                    // #410, #469, #471, #491, #494, #505, #522, #528, #554, #557, #561, #562, #570, #594, #627, #653
				$update['items'][] = 'includes/register.inc.php';                                   // #594
				$update['items'][] = 'includes/rss.inc.php';                                        // #476
				$update['items'][] = 'includes/search.inc.php';                                     // #560, #594, #622
				$update['items'][] = 'includes/thread.inc.php';                                     // #505, #554
				$update['items'][] = 'includes/upload_image.inc.php';                               // #451, #454, #462, #554, #623
				$update['items'][] = 'includes/user.inc.php';                                       // #470, #478, #505, #525, #527, #550, #551, #566, #575, #594, #595, #654, #657
				$update['items'][] = 'includes/';                                                   // #427
				
				$update['items'][] = 'includes/classes/ (remove)';                                  // #554, #589
				
				$update['items'][] = 'index.php';                                                   // #390, #498
				
				$update['items'][] = 'js/posting.js';                                               // #390, #450, #475, #543, #545
				$update['items'][] = 'js/posting.min.js';                                           // #390, #450, #475, #580
				$update['items'][] = 'js/admin.js (remove)';                                        // #466, #543, #545, #589
				$update['items'][] = 'js/admin.min.js (remove)';                                    // #466, #579, #589
				$update['items'][] = 'js/main.js';                                                  // #543, #544, #545, #546, #573, #578, #589
				$update['items'][] = 'js/main.min.js';                                              // #574, #578, #589
				$update['items'][] = 'js/';
				
				$update['items'][] = 'lang/';                                                       // #364, #390, #427, #470, #471, #489, #501, #514, #523, #526, #530, #532, #539, #548, #549, #550, #566, #569, #572, #575, #577, #587, #589, #599, #611, #647, #652, #665
				
				$update['items'][] = 'modules/b8/';                                                 // #427, #463, #557, #561, #562
				$update['items'][] = 'modules/bad-behavior/';                                       // #559
				$update['items'][] = 'modules/geshi/';                                              // #581
				$update['items'][] = 'modules/phpmailer/';                                          // #498, #555, #583
				$update['items'][] = 'modules/smarty/';                                             // #582, #584, #586
				$update['items'][] = 'modules/stringparser_bbcode/';                                // #558
				$update['items'][] = 'modules/';                                                    // #511, #518
				
				$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';                   // #364, #390, #470, #489, #575, #589, #612, #645, #652, #656, #659
				$update['items'][] = 'themes/default/subtemplates/bookmarks.inc.tpl';               // #656
				$update['items'][] = 'themes/default/subtemplates/contact.inc.tpl';                 // #489, #501, #505, #656
				$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';                   // #469, #470, #530, #536, #542, #611, #620, #624, #656
				$update['items'][] = 'themes/default/subtemplates/errors.inc.tpl';                  // #656
				$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';                   // #469, #471, #611, #652
				$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';             // #469, #471, #611, #612, #652
				$update['items'][] = 'themes/default/subtemplates/login.inc.tpl';                   // #645, #656
				$update['items'][] = 'themes/default/subtemplates/page.inc.tpl';                    // #656
				$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';                 // #377, #390, #471, #494, #620, #656
				$update['items'][] = 'themes/default/subtemplates/posting_delete.inc.tpl';          // #656
				$update['items'][] = 'themes/default/subtemplates/posting_delete_marked.inc.tpl';   // #656
				$update['items'][] = 'themes/default/subtemplates/posting_delete_spam.inc.tpl';     // #656
				$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';        // #626, #653, #656
				$update['items'][] = 'themes/default/subtemplates/posting_manage_postings.inc.tpl'; // #656
				$update['items'][] = 'themes/default/subtemplates/posting_move.inc.tpl';            // #656
				$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';     // #626, #653, #656
				$update['items'][] = 'themes/default/subtemplates/posting_unsubscribe.inc.tpl';     // #656
				$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';                // #645, #656
				$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';                  // #469, #470, #530, #537, #620, #624
				$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';           // #469, #470, #530, #537, #620, #624
				$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';                    // #612, #652
				$update['items'][] = 'themes/default/subtemplates/user_edit.inc.tpl';               // #550, #656
				$update['items'][] = 'themes/default/subtemplates/user_edit_email.inc.tpl';         // #645, #656
				$update['items'][] = 'themes/default/subtemplates/user_edit_pw.inc.tpl';            // #645, #656
				$update['items'][] = 'themes/default/subtemplates/user_postings.inc.tpl';           // #652, #657
				$update['items'][] = 'themes/default/subtemplates/user_profile.inc.tpl';            // #654, #656
				$update['items'][] = 'themes/default/subtemplates/user_remove_account.inc.tpl';     // #645, #656
				$update['items'][] = 'themes/default/images/backup.png (remove)';                   // #589
				$update['items'][] = 'themes/default/images/bg_gradient_x.png (remove)';            // #612
				$update['items'][] = 'themes/default/images/bg_gradient_y.png (remove)';            // #612
				$update['items'][] = 'themes/default/images/image.png';                             // #364
				$update['items'][] = 'themes/default/images/keep_eye_on.png';                       // #611
				$update['items'][] = 'themes/default/main.tpl';                                     // #377, #470, #503, #504, #530, #531, #540, #547, #588, #589, #598, #608, #637
				$update['items'][] = 'themes/default/insert_flash.inc.tpl (remove)';                // #390
				$update['items'][] = 'themes/default/js_config.ini';                                // #390
				$update['items'][] = 'themes/default/style.css';                                    // #390, #461, #477, #530, #531, #533, #534, #537, #538, #598, #608, #612, #620, #624, #625, #626, #630, #640, #652, #656
				$update['items'][] = 'themes/default/style.min.css';                                // #390, #461, #477, #530, #531, #533, #534, #537, #598, #608, #612, #620, #624, #625, #626, #630, #640, #652, #656
				$update['items'][] = 'themes/default/';                                             // #427
				
				$update['items'] = reorderUpgradeFiles($update['items']);
			}
		}
	}
}

// upgrade from version 2.4.99.0
if (empty($update['errors']) && in_array($settings['version'], array('2.4.99.0'))) {
	$resEmailMultiUse = mysqli_query($connid, "SELECT `user_id`, `user_name`, `user_email` FROM `" . $db_settings['userdata_table'] . "` WHERE `user_email` IN(SELECT `user_email` FROM `" . $db_settings['userdata_table'] . "` GROUP BY `user_email` HAVING COUNT(`user_email`) > 1) ORDER BY `user_email` ASC, `user_name` ASC");
	if ($resEmailMultiUse === false) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	
	// The database request succeeded
	if (empty($update['errors'])) {
		if (mysqli_num_rows($resEmailMultiUse) > 0) {
			// list the doubled user names
			$update['errors'][]  = '<h3><strong>Attention</strong>: Found non-unique e-mail-addresses in the user accounts!</h3>';
			$update['errors'][] .= '<p>Please make the e-mail-addresses unique and inform the users in question about the changes. <em>Tip:</em> Open the links to the user edit forms in a new browser tab and solve the issues. After editing all listed users start the update process again.</p>';
			$update['errors'][] .= '<table>';
			$update['errors'][] .= '<tr><th>E-mail-address</th><th>Username</th></tr>';
			while ($row = mysqli_fetch_assoc($resEmailMultiUse)) {
				$update['errors'][] .= '<tr><td>'. htmlspecialchars($row['user_email']) .'</td><td><a href="?mode=admin&amp;edit_user='. intval($row['user_id']) .'">'. htmlspecialchars($row['user_name']) .'</a></td></tr>'."\n";
			}
			$update['errors'][] .= '</table>';
			mysqli_free_result($resEmailMultiUse);
		} else {
			if (empty($errors)) {
				// change the table engine from MyISAM to InnoDB for the previously existing tables
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['settings_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['category_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['pages_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['banlists_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_cache_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_cache_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				// all newer tables (starting with 2.3.99.x) was alredy created with engine InnoDB
				
				
				// further changes of the table definitions
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "` CHANGE `token` `token` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the banlist table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['banlists_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the bookmarks table
				if(!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the categories table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['category_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the forum/entries table
				$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."` LIKE 'email_notification'");
				if (mysqli_num_rows($rEN_exists) > 0) {
					if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` DROP `email_notification`;")) {
						$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
					}
				}
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the entry cache table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_cache_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the pages table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['pages_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
					// changes in the tags table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "` CHANGE `tag` `tag` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
					// changes in the user data table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` CHANGE `user_name` `user_name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL, CHANGE `user_email` `user_email` VARCHAR(255);")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` CHANGE `user_email` `user_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` DROP INDEX `user_name`, ADD UNIQUE KEY `key_user_name` (`user_name`), ADD UNIQUE KEY `key_user_email` (`user_email`);")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` ADD `inactivity_notification` BOOLEAN NOT NULL DEFAULT FALSE, ADD `browser_window_target` tinyint(4) NOT NULL DEFAULT '0' AFTER `user_lock`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
					// changes in the user data cache table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_cache_table'] . "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
					// changes in the settings table
				$uaa = ($settings['user_area_public'] == 0) ? 2 : 1;
				if (!@mysqli_query($connid, "INSERT INTO `" . $db_settings['settings_table'] . "` (`name`, `value`) VALUES ('user_area_access', ". intval($uaa) ."), ('b8_mail_check', '0'), ('php_mailer', '0'), ('delete_inactive_users', '30'), ('notify_inactive_users', '3'), ('link_open_target', '');")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "` WHERE name = 'user_area_public';")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET `bbcode_latex_uri` = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js';")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
					// changes in the B8-rating table)
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "` ADD KEY `B8_spam` (`spam`), ADD KEY `B8_training_type` (`training_type`);")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// write the new version number to the database
				if (empty($update['errors'])) {
					$new_version_set = write_new_version_string_2_db($connid, $newVersion);
					if ($new_version_set === false) {
						$update['errors'][] = 'Database error, could not write the new version string to the database.';
					}
				}
				// collect the file and directory names to upgrade
				if (empty($update['errors'])) {
					$update['items'][] = 'config/php_mailer.php';                                       // #498, #575, #589, #612, #645, #652, #656, #659
					$update['items'][] = 'config/b8_config.php';                                        // #493, #557, #561, #562
					
					$update['items'][] = 'includes/admin.inc.php';                                      // #478, #489, #575, #589, #612, #645, #652, #656, #659
					$update['items'][] = 'includes/contact.inc.php';                                    // #489, #501, #505, #594
					$update['items'][] = 'includes/entry.inc.php';                                      // #505, #509, #536, #585, #611
					$update['items'][] = 'includes/functions.inc.php';                                  // #467, #478, #498, #499, #512, #519. #520, #524, #526, #528, #540, #554, #571, #587, #589, #593, #594, #595, #603, #606, #619, #623, #647, #650, #667
					$update['items'][] = 'includes/index.inc.php';                                      // #472, #611
					$update['items'][] = 'includes/mailer.inc.php';                                     // #498
					$update['items'][] = 'includes/posting.inc.php';                                    // #469, #471, #491, #494, #505, #522, #528, #554, #557, #561, #562, #570, #594, #627, #653
					$update['items'][] = 'includes/rss.inc.php';                                        // #476
					$update['items'][] = 'includes/search.inc.php';                                     // #560, #594, #622
					$update['items'][] = 'includes/thread.inc.php';                                     // #505
					$update['items'][] = 'includes/upload_image.inc.php';                               // #462, #554, #623
					$update['items'][] = 'includes/user.inc.php';                                       // #470, #478, #505
					$update['items'][] = 'includes/';
					
					$update['items'][] = 'index.php';                                                   // #498
					
					$update['items'][] = 'js/admin.js (remove)';                                        // #466
					$update['items'][] = 'js/admin.min.js (remove)';                                    // #466
					$update['items'][] = 'js/posting.js';                                               // #475
					$update['items'][] = 'js/posting.min.js';                                           // #475
					$update['items'][] = 'js/';
					
					$update['items'][] = 'lang/';                                                       // #470, #471, #489, #501, #514, #523, #526, #530, #532, #539, #548, #549, #550, #566, #569, #572, #575, #577, #587, #589, #599, #611
					
					$update['items'][] = 'modules/b8/';                                                 // #463
					$update['items'][] = 'modules/phpmailer/';                                          // #498
					$update['items'][] = 'modules/';
					
					$update['items'][] = 'themes/default/images/bg_gradient_x.png (remove)';            // #612
					$update['items'][] = 'themes/default/images/bg_gradient_y.png (remove)';            // #612
					$update['items'][] = 'themes/default/images/keep_eye_on.png';                       // #611
					$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';                   // #470, #489, #612
					$update['items'][] = 'themes/default/subtemplates/contact.inc.tpl';                 // #489, #501, #505
					$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';                   // #469, #470, #530, #536, #542, #611, #620, #624, #656
					$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';                   // #469, #471, #611
					$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';             // #469, #471, #611, #612
					$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';                 // #471, #494, #620, #656
					$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';        // #626, #653, #656
					$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';     // #626, #653, #656
					$update['items'][] = 'themes/default/subtemplates/posting_unsubscribe.inc.tpl';     // #656
					$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';                  // #469, #470, #530, #537, #620, #624
					$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';           // #469, #470, #530, #537, #620, #624
					$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';                    // #612, #652
					$update['items'][] = 'themes/default/main.tpl';                                     // #470, #503, #504, #530, #531, #540, #547, #588, #589, #598, #608, #637
					$update['items'][] = 'themes/default/style.css';                                    // #461, #477, #530, #531, #533, #534, #537, #538, #598, #608, #612, #620, #624, #625, #626, #630, #640, #652, #656
					$update['items'][] = 'themes/default/style.min.css';                                // #461, #477, #530, #531, #533, #534, #537, #598, #608, #612, #620, #624, #625, #626, #630, #640, #652, #656
					$update['items'][] = 'themes/default/';
					
					$update['items'] = reorderUpgradeFiles($update['items']);
				}
			}
		}
	}
}

// upgrade from version 2.4.99.1
if (empty($update['errors']) && in_array($settings['version'], array('2.4.99.1'))) {
	$resEmailMultiUse = mysqli_query($connid, "SELECT `user_id`, `user_name`, `user_email` FROM `" . $db_settings['userdata_table'] . "` WHERE `user_email` IN(SELECT `user_email` FROM `" . $db_settings['userdata_table'] . "` GROUP BY `user_email` HAVING COUNT(`user_email`) > 1) ORDER BY `user_email` ASC, `user_name` ASC");
	if ($resEmailMultiUse === false) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	
	// The database request succeeded
	if (empty($update['errors'])) {
		if (mysqli_num_rows($resEmailMultiUse) > 0) {
			// list the doubled user names
			$update['errors'][]  = '<h3><strong>Attention</strong>: Found non-unique e-mail-addresses in the user accounts!</h3>';
			$update['errors'][] .= '<p>Please make the e-mail-addresses unique and inform the users in question about the changes. <em>Tip:</em> Open the links to the user edit forms in a new browser tab and solve the issues. After editing all listed users start the update process again.</p>';
			$update['errors'][] .= '<table>';
			$update['errors'][] .= '<tr><th>E-mail-address</th><th>Username</th></tr>';
			while ($row = mysqli_fetch_assoc($resEmailMultiUse)) {
				$update['errors'][] .= '<tr><td>'. htmlspecialchars($row['user_email']) .'</td><td><a href="?mode=admin&amp;edit_user='. intval($row['user_id']) .'">'. htmlspecialchars($row['user_name']) .'</a></td></tr>'."\n";
			}
			$update['errors'][] .= '</table>';
			mysqli_free_result($resEmailMultiUse);
		} else {
			if (empty($errors)) {
				// change the table engine from MyISAM to InnoDB for the previously existing tables
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['banlists_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['category_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['pages_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_cache_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_cache_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the settings table
				if (!@mysqli_query($connid, "INSERT INTO `" . $db_settings['settings_table'] . "` (`name`, `value`) VALUES ('b8_mail_check', '0'), ('php_mailer', '0'), ('delete_inactive_users', '30'), ('notify_inactive_users', '3'), ('link_open_target', '');")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the entries tables
				$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."` LIKE 'email_notification'");
				if (mysqli_num_rows($rEN_exists) > 0) {
					if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` DROP `email_notification`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				}
				
				// changes in the B8 wordlist table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "` CHANGE `token` `token` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the B8 rating table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "` ADD KEY `B8_spam` (`spam`), ADD KEY `B8_training_type` (`training_type`);")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the tags table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "` CHANGE `tag` `tag` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				// changes in the userdata table
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` CHANGE `user_name` `user_name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL, CHANGE `user_email` `user_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` DROP INDEX `user_name`, ADD UNIQUE KEY `key_user_name` (`key_user_name`), ADD UNIQUE KEY `key_user_email` (`user_email`);")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` ADD `inactivity_notification` BOOLEAN NOT NULL DEFAULT FALSE, ADD `browser_window_target` tinyint(4) NOT NULL DEFAULT '0' AFTER `user_lock`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
				
				
				// write the new version number to the database
				if (empty($update['errors'])) {
					$new_version_set = write_new_version_string_2_db($connid, $newVersion);
					if ($new_version_set === false) {
						$update['errors'][] = 'Database error, could not write the new version string to the database.';
					}
				}
				// collect the file and directory names to upgrade
				if (empty($update['errors'])) {
					$update['items'][] = 'config/php_mailer.php';                                       // #498, #575, #589, #612, #645, #652, #656, #659
					$update['items'][] = 'config/b8_config.php';                                        // #493, #557, #561, #562
					
					$update['items'][] = 'includes/admin.inc.php';                                      // #489, #575, #589, #612, #645, #652, #656, #659
					$update['items'][] = 'includes/contact.inc.php';                                    // #489, #501, #505, #594
					$update['items'][] = 'includes/entry.inc.php';                                      // #505, #509, #536, #585, #611
					$update['items'][] = 'includes/functions.inc.php';                                  // #498, #499, #512, #519. #520, #524, #526, #528, #540, #554, #571, #587, #589, #593, #594, #595, #603, #606, #619, #623, #647, #650, #667
					$update['items'][] = 'includes/index.inc.php';                                      // #611
					$update['items'][] = 'includes/mailer.inc.php';                                     // #498
					$update['items'][] = 'includes/posting.inc.php';                                    // #491, #494, #505, #522, #528, #554, #557, #561, #562, #570, #594, #627, #653
					$update['items'][] = 'includes/search.inc.php';                                     // #560, #594, #622
					$update['items'][] = 'includes/thread.inc.php';                                     // #505
					$update['items'][] = 'includes/upload_image.inc.php';                               // #554, #623
					$update['items'][] = 'includes/user.inc.php';                                       // #505
					$update['items'][] = 'includes/';
					
					$update['items'][] = 'index.php';                                                   // #498
					
					$update['items'][] = 'js/';
					
					$update['items'][] = 'lang/';                                                       // #489, #501, #514, #523, #526, #530, #532, #539, #548, #549, #550, #566, #569, #572, #575, #577, #587, #589, #599, #611
					
					$update['items'][] = 'modules/phpmailer/';                                          // #498
					$update['items'][] = 'modules/';
					
					$update['items'][] = 'themes/default/images/bg_gradient_x.png (remove)';            // #612
					$update['items'][] = 'themes/default/images/bg_gradient_y.png (remove)';            // #612
					$update['items'][] = 'themes/default/images/keep_eye_on.png';                       // #611
					$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';                   // #489, #612
					$update['items'][] = 'themes/default/subtemplates/contact.inc.tpl';                 // #489, #501, #505
					$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';                   // #530, #536, #542, #611, #620, #624, #656
					$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';                   // #611
					$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';             // #611, #612
					$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';                 // #494, #620, #656
					$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';        // #626, #653, #656
					$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';     // #626, #653, #656
					$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';                  // #530, #537, #620, #624
					$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';           // #530, #537, #620, #624
					$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';                    // #612, #652
					$update['items'][] = 'themes/default/main.tpl';                                     // #503, #504, #530, #531, #540, #547, #588, #589, #598, #608, #637
					$update['items'][] = 'themes/default/style.css';                                    // #530, #531, #533, #534, #537, #538, #598, #608, #612, #620, #624, #625, #626, #630, #640, #652, #656
					$update['items'][] = 'themes/default/style.min.css';                                // #530, #531, #533, #534, #537, #538, #598, #608, #612, #620, #624, #625, #626, #630, #640, #652, #656
					$update['items'][] = 'themes/default/';
					
					$update['items'] = reorderUpgradeFiles($update['items']);
				}
			}
		}
	}
}

// upgrade from version 2.4.99.2 and 2.4.99.3
if (empty($update['errors']) && in_array($settings['version'], array('2.4.99.2', '2.4.99.3'))) {
	// changed tables
	$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."` LIKE 'email_notification'");
	if (mysqli_num_rows($rEN_exists) > 0) {
		if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` DROP `email_notification`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
	
	if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` ADD `inactivity_notification` BOOLEAN NOT NULL DEFAULT FALSE, ADD `browser_window_target` tinyint(4) NOT NULL DEFAULT '0' AFTER `user_lock`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	
	if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` CHANGE `user_email` `user_email` VARCHAR(255) CHARACTER SET utf8 NOT NULL;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	
	$rObsoleteIndexes = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS obsolete_key
	FROM information_schema.STATISTICS 
	WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."' AND
	TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."' AND 
	INDEX_NAME LIKE 'user_%';");
	if (mysqli_num_rows($rObsoleteIndexes) > 0) {
		while ($row  = mysqli_fetch_assoc($rObsoleteIndexes)) {
			if (!@mysqli_query($connid, "DROP INDEX ". $row['obsolete_key'] ." ON " . $db_settings['userdata_table'] .";")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
		}
	}
	
	if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "` ADD KEY `B8_spam` (`spam`), ADD KEY `B8_training_type` (`training_type`);")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	
	if (!@mysqli_query($connid, "INSERT INTO `" . $db_settings['settings_table'] . "` (`name`, `value`) VALUES ('delete_inactive_users', '30'), ('notify_inactive_users', '3'), ('link_open_target', '');")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	
	if (!@mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET `bbcode_latex_uri` = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js';")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	
	// write the new version number to the database
	if (empty($update['errors'])) {
		$new_version_set = write_new_version_string_2_db($connid, $newVersion);
		if ($new_version_set === false) {
			$update['errors'][] = 'Database error, could not write the new version string to the database.';
		}
	}
	
	// collect the file and directory names to upgrade
	if (empty($update['errors'])) {
		$update['items'][] = 'config/b8_config.php';
		
		$update['items'][] = 'includes/admin.inc.php';                               // without pull request
		$update['items'][] = 'includes/entry.inc.php';                               // #509
		$update['items'][] = 'includes/index.inc.php';
		$update['items'][] = 'includes/functions.inc.php';
		$update['items'][] = 'includes/posting.inc.php';
		$update['items'][] = 'includes/search.inc.php';
		$update['items'][] = 'includes/upload_image.inc.php';
		$update['items'][] = 'includes/';
		
		$update['items'][] = 'index.php';                                            // without pull request
		
		$update['items'][] = 'js/';
		
		$update['items'][] = 'lang/';
		
		$update['items'][] = 'modules/';
		
		$update['items'][] = 'themes/default/images/keep_eye_on.png';
		$update['items'][] = 'themes/default/images/bg_gradient_x.png (remove)';
		$update['items'][] = 'themes/default/images/bg_gradient_y.png (remove)';
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';
		$update['items'][] = 'themes/default/main.tpl';
		$update['items'][] = 'themes/default/style.css';
		$update['items'][] = 'themes/default/style.min.css';
		$update['items'][] = 'themes/default/';
		
		$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';
		
		$update['items'] = reorderUpgradeFiles($update['items']);
	}
}

// upgrade from version 20220508.1 (2.5.0) and 20220509.1 (2.5.1)
if (empty($update['errors']) && in_array($settings['version'], array('20220508.1', '20220509.1'))) {
	// changed tables
	$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."` LIKE 'email_notification'");
	if (mysqli_num_rows($rEN_exists) > 0) {
		if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` DROP `email_notification`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
	
	if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "` CHANGE `user_email` `user_email` VARCHAR(255) CHARACTER SET utf8 NOT NULL;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	
	$rObsoleteIndexes = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS obsolete_key
	FROM information_schema.STATISTICS 
	WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."' AND
	TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."' AND 
	INDEX_NAME LIKE 'user_%';");
	if (mysqli_num_rows($rObsoleteIndexes) > 0) {
		while ($row  = mysqli_fetch_assoc($rObsoleteIndexes)) {
			if (!@mysqli_query($connid, "DROP INDEX ". $row['obsolete_key'] ." ON " . $db_settings['userdata_table'] .";")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
		}
	}
	
	// write the new version number to the database
	if (empty($update['errors'])) {
		$new_version_set = write_new_version_string_2_db($connid, $newVersion);
		if ($new_version_set === false) {
			$update['errors'][] = 'Database error, could not write the new version string to the database.';
		}
	}
	
	// collect the file and directory names to upgrade
	if (empty($update['errors'])) {
		$update['items'][] = 'includes/admin.inc.php';
		$update['items'][] = 'includes/entry.inc.php';
		$update['items'][] = 'includes/index.inc.php';
		$update['items'][] = 'includes/functions.inc.php';
		$update['items'][] = 'includes/posting.inc.php';
		$update['items'][] = 'includes/search.inc.php';
		$update['items'][] = 'includes/upload_image.inc.php';
		
		$update['items'][] = 'lang/';
		
		$update['items'][] = 'themes/default/images/keep_eye_on.png';
		$update['items'][] = 'themes/default/images/bg_gradient_x.png (remove)';
		$update['items'][] = 'themes/default/images/bg_gradient_y.png (remove)';
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';
		$update['items'][] = 'themes/default/main.tpl';
		$update['items'][] = 'themes/default/style.css';
		$update['items'][] = 'themes/default/style.min.css';
		
		$update['items'] = reorderUpgradeFiles($update['items']);
	}
}

// upgrade from version 20220517.1 (2.5.2) and 20220529.1 (2.5.3)
if (empty($update['errors']) && in_array($settings['version'], array('20220517.1', '20220529.1'))) {
	// changed tables
	$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."` LIKE 'email_notification'");
	if (mysqli_num_rows($rEN_exists) > 0) {
		if (!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` DROP `email_notification`;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
	
	$rObsoleteIndexes = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS obsolete_key
	FROM information_schema.STATISTICS 
	WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."' AND
	TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."' AND 
	INDEX_NAME LIKE 'user_%';");
	if (mysqli_num_rows($rObsoleteIndexes) > 0) {
		while ($row  = mysqli_fetch_assoc($rObsoleteIndexes)) {
			if (!@mysqli_query($connid, "DROP INDEX ". $row['obsolete_key'] ." ON " . $db_settings['userdata_table'] .";")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
		}
	}
	
	if (!@mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET `bbcode_latex_uri` = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js';")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	
	// write the new version number to the database
	if (empty($update['errors'])) {
		$new_version_set = write_new_version_string_2_db($connid, $newVersion);
		if ($new_version_set === false) {
			$update['errors'][] = 'Database error, could not write the new version string to the database.';
		}
	}
	
	// collect the file and directory names to upgrade
	if (empty($update['errors'])) {
		$update['items'][] = 'includes/admin.inc.php';
		$update['items'][] = 'includes/entry.inc.php';
		$update['items'][] = 'includes/index.inc.php';
		$update['items'][] = 'includes/functions.inc.php';
		$update['items'][] = 'includes/posting.inc.php';
		$update['items'][] = 'includes/search.inc.php';
		$update['items'][] = 'includes/upload_image.inc.php';
		
		$update['items'][] = 'lang/';
		
		$update['items'][] = 'themes/default/images/keep_eye_on.png';
		$update['items'][] = 'themes/default/images/bg_gradient_x.png (remove)';
		$update['items'][] = 'themes/default/images/bg_gradient_y.png (remove)';
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';
		$update['items'][] = 'themes/default/main.tpl';
		$update['items'][] = 'themes/default/style.css';
		$update['items'][] = 'themes/default/style.min.css';
		
		$update['items'] = reorderUpgradeFiles($update['items']);
	}
}

