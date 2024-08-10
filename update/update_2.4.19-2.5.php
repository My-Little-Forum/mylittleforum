<?php
/******************************************************************************
* my little forum                                                             *
* update file to update from version 2.4.19 to version 2.5.*                  *
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
$update['version'] = array('2.4.19', '2.4.19.1', '2.4.20', '2.4.21', '2.4.22', '2.4.23', '2.4.24', '2.4.99.0', '2.4.99.1', '2.4.99.2', '2.4.99.3', '20220508.1', '20220509.1', '20220517.1', '20220529.1', '20220803.1', '20240308.1');
$update['download_url'] = 'https://github.com/My-Little-Forum/mylittleforum/releases/latest';
$update['message'] = '';

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

if (!function_exists('str_ends_with')) {
	function str_ends_with($str, $end) {
		return (@substr_compare($str, $end, -mb_strlen($end)) == 0);
	}
}


// check version:
if (!file_exists('config/VERSION')) $update['errors'][] = 'Error in line '.__LINE__.': Missing the file config/VERSION. Load it up from your script package (config/VERSION) before proceeding.';


// check the MySQL- or MariaDB-version
// minimal versions: MySQL 5.7.7 or MariaDB 10.2.2
$resDatabaseVersion = mysqli_query($connid, "SHOW VARIABLES WHERE variable_name IN('version', 'version_comment');");
if ($resDatabaseVersion === false)  $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
if (empty($update['errors'])) {
	while ($rawV = mysqli_fetch_assoc($resDatabaseVersion)) {
		$versionInfo[$rawV['Variable_name']] = $rawV['Value'];
	}
	// read the value for the server type (MySQL or MariaDB)
	if (preg_match("/MariaDB/ui", $versionInfo['version_comment'])) {
		$serverType = "MariaDB";
	} else {
		$serverType = "MySQL";
	}
	// read the provided version string
	if (!empty($versionInfo['version']) and substr_count($versionInfo['version'], "-") > 0) {
		$rawDBVersion = explode("-", $versionInfo['version']);
		$serverVersion = $rawDBVersion[0];
	} else {
		$serverVersion = $versionInfo['version'];
	}
	mysqli_free_result($resDatabaseVersion);
	unset($versionInfo, $rawDBVersion);
	
	if (!empty($serverVersion) and preg_match("/\d{1,2}.\d{1,2}(\.\d{1,3})?/u", $serverVersion)) {
		if ($serverType == "MariaDB" and version_compare($serverVersion, "10.2.2", "<") === true) {
			$update['errors'][] = "The version of MariaDB that runs on your server (". htmlspecialchars($serverVersion) .") is to old. You need to run your database server at least with MariaDB 10.2.2.";
		}
		if ($serverType == "MySQL" and version_compare($serverVersion, "5.7.7", "<") === true) {
			$update['errors'][] = "The version of MySQL that runs on your server (". htmlspecialchars($serverVersion) .") is to old. You need to run your database server at least with MySQL 5.7.7.";
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
	if (version_compare($settings['version'], $newVersion, '<') !== true) {
		$update['errors'][] = 'Error in line '.__LINE__.': The version you want to install (see string in config/VERSION) must be greater than the current installed version. Current version: '. htmlspecialchars($settings['version']) .', version you want to install: '.  htmlspecialchars($newVersion) .'. Please check also if you uploaded the actual file version of config/VERSION. Search therefore for the file date and compare it with the date from the installation package.';
	}
	if (!in_array($settings['version'], $update['version'])) {
		$update['errors'][] = 'Error in line '.__LINE__.': This update file doesn\'t work with the current version.';
	}
}


/**
 * change the table engine from MyISAM to InnoDB for the previously existing tables
 *
 * Check for the database engine types of ALL tables.
 * tables in newer installations might be of type InnoDB
 * because this was the default engine at the time of installation.
 * Older instances might be a mix of MyISAM and InnoDB
 * depending on the creation time of a specific table.
 *
 * So let's read the type of every single table and loop
 * through the result on the search for tables, which are
 * not of engine type InnoDB. Set the type to InnoDB only
 * where it is actually necessary.
 */
if (empty($update['errors'])) {
	$qTypeOfTables = "SELECT `TABLE_NAME`, `ENGINE`
	FROM `information_schema`.`TABLES`
	WHERE `TABLE_SCHEMA`='". $db_settings['database'] ."'
	AND `TABLE_NAME` IN(
		'". $db_settings['settings_table'] ."',
		'". $db_settings['forum_table'] ."',
		'". $db_settings['category_table'] ."',
		'". $db_settings['userdata_table'] ."',
		'". $db_settings['smilies_table'] ."',
		'". $db_settings['pages_table'] ."',
		'". $db_settings['banlists_table'] ."',
		'". $db_settings['useronline_table'] ."',
		'". $db_settings['login_control_table'] ."',
		'". $db_settings['entry_cache_table'] ."',
		'". $db_settings['userdata_cache_table'] ."',
		'". $db_settings['bookmark_table'] ."',
		'". $db_settings['read_status_table'] ."',
		'". $db_settings['temp_infos_table'] ."',
		'". $db_settings['tags_table'] ."',
		'". $db_settings['bookmark_tags_table'] ."',
		'". $db_settings['entry_tags_table'] ."',
		'". $db_settings['subscriptions_table'] ."',
		'". $db_settings['b8_wordlist_table'] ."',
		'". $db_settings['b8_rating_table'] ."',
		'". $db_settings['akismet_rating_table'] ."',
		'". $db_settings['uploads_table'] ."')
	AND ENGINE != 'InnoDB';";
	
	$resTypeOfTables = @mysqli_query($connid, $qTypeOfTables);
	// if reading the database failed
	if ($resTypeOfTables === false) {
		$update['errors'][] = 'Database error in line '. (__LINE__.-2) .': '. mysqli_error($connid);
	}
	// if reading the database succeeded AND resulted in more than zero rows
	if (empty($update['errors']) and mysqli_num_rows($resTypeOfTables) > 0) {
		while ($row = mysqli_fetch_assoc($resTypeOfTables)) {
			if (!@mysqli_query($connid, "ALTER TABLE `" . $row['TABLE_NAME'] . "` ENGINE=InnoDB;")) $update['errors'][] = 'Database error in line '.__LINE__.' (affected table ' . $row['TABLE_NAME'] . '): ' . mysqli_error($connid);
		}
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
	
	if (empty($update['errors'])) {
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
			if (!@mysqli_multi_query($connid, "CREATE TABLE IF NOT EXISTS `" . $db_settings['akismet_rating_table'] . "` (`eid` int(11) NOT NULL, `spam` tinyint(1) NOT NULL DEFAULT '0', `spam_check_status` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`eid`), KEY `akismet_spam` (`spam`), KEY spam_check_status (spam_check_status)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_multi_query($connid, "CREATE TABLE IF NOT EXISTS `" . $db_settings['b8_rating_table'] . "` (`eid` int(11) NOT NULL, `spam` tinyint(1) NOT NULL DEFAULT '0', `training_type` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`eid`), KEY `b8_spam` (`spam`), KEY `B8_training_type` (`training_type`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_multi_query($connid, "CREATE TABLE IF NOT EXISTS `" . $db_settings['b8_wordlist_table'] . "` (`token` varchar(255) character set utf8mb4 collate utf8mb4_bin NOT NULL DEFAULT '', `count_ham` int unsigned default NULL, `count_spam` int unsigned default NULL, PRIMARY KEY (`token`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_bin;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			if (!@mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `" . $db_settings['uploads_table'] . "` (`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, `uploader` int(10) UNSIGNED NULL, `filename` varchar(64) NULL, `tstamp` datetime NULL, PRIMARY KEY (id)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_bin;")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			
			
			/**
			 * From here on everything can be done as a transaction in one step
			 */
			if (empty($update['errors'])) {
				mysqli_autocommit($connid, false);
				mysqli_begin_transaction($connid);
				try {
					// changes in the setting table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['settings_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
					
					$uaa = ($settings['user_area_public'] == 0) ? 2 : 1;
					mysqli_query($connid, "INSERT INTO `" . $db_settings['settings_table'] . "` (`name`, `value`)
					VALUES
						('uploads_per_page', '20'),
						('bbcode_latex', '0'),
						('bbcode_latex_uri', 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js'),
						('b8_entry_check', '1'),
						('b8_auto_training', '1'),
						('b8_spam_probability_threshold', '80'),
						('user_area_access', ". intval($uaa) ."),
						('b8_mail_check', '0'),
						('php_mailer', '0'),
						('delete_inactive_users', '30'),
						('notify_inactive_users', '3'),
						('link_open_target', '');");
					
					mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET
						`name`='spam_check_registered'
					WHERE `name`='akismet_check_registered';");
					
					mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "`
					WHERE name IN(
						'bbcode_flash',
						'bbcode_tex',
						'flash_default_height',
						'flash_default_width',
						'user_area_public',
						'bad_behavior');");
					
					
					// changes in the new introduced tables
					mysqli_query($connid, "INSERT INTO `" . $db_settings['b8_wordlist_table'] . "` (`token`, `count_ham`, `count_spam`)
					VALUES
						('b8*dbversion', '3', NULL),
						('b8*texts', '0', '0');");
					
					mysqli_query($connid, "INSERT INTO `" . $db_settings['akismet_rating_table'] . "` (`eid`, `spam`, `spam_check_status`)
					SELECT `id`, `spam`, `spam_check_status` FROM `" . $db_settings['forum_table'] ."`;");
					
					mysqli_query($connid, "INSERT INTO `" . $db_settings['b8_rating_table'] . "` (`eid`, `spam`, `training_type`)
					SELECT `id`, `spam`, 0 FROM `" . $db_settings['forum_table'] ."`;");
					
					
					// changes in the user data table
					/* is the following query really necessary? */
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
					CHANGE `user_name` `user_name` VARCHAR(128) NOT NULL,
					CHANGE `user_email` `user_email` VARCHAR(255) NOT NULL,
					CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL,
					CHANGE `last_logout` `last_logout` TIMESTAMP NULL DEFAULT NULL,
					CHANGE `registered` `registered` TIMESTAMP NULL DEFAULT NULL;");
					
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
					CHANGE `user_name` `user_name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;");
					
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
					DROP INDEX `user_type`,
					DROP INDEX `user_name`,
					ADD KEY `key_user_type` (`user_type`),
					ADD UNIQUE KEY `key_user_name` (`user_name`),
					ADD UNIQUE KEY `key_user_email` (`user_email`);");
					
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
					ADD `inactivity_notification` BOOLEAN NOT NULL DEFAULT FALSE,
					ADD `browser_window_target` tinyint(4) NOT NULL DEFAULT '0' AFTER `user_lock`;");
					
					mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
					`birthday` = NULL
					WHERE `birthday` = '0000-00-00';");
					
					mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
					`last_logout` = NULL
					WHERE `last_logout` = '0000-00-00 00:00:00';");
					
					mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
					`registered` = NULL
					WHERE `registered` = '0000-00-00 00:00:00';");
					
					
					// changes in the user data cache table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_cache_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the forum/entries table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					DROP `spam`,
					DROP `spam_check_status`;");
					
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					CHANGE `last_reply` `last_reply` TIMESTAMP NULL DEFAULT NULL,
					CHANGE `edited` `edited` TIMESTAMP NULL DEFAULT NULL;");
					
					$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."`
					LIKE 'email_notification';");
					if (mysqli_num_rows($rEN_exists) > 0) {
						mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
						DROP `email_notification`;");
					}
					
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
					`last_reply` = NULL
					WHERE `last_reply` = '0000-00-00 00:00:00';");
					
					mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
					`edited` = NULL
					WHERE `edited` = '0000-00-00 00:00:00';");
					
					
					// changes in the banlist table
					mysqli_query($connid, "RENAME TABLE `". $db_settings['banlists_table'] ."`
					TO `". $db_settings['banlists_table'] ."_old`;");
					
					mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `". $db_settings['banlists_table'] ."` (`name` varchar(255) NOT NULL, `list` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
					
					mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
					SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
					FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'ips';");
					
					mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
					SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
					FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'user_agents';");
					
					mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
					SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
					FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'words';");
					
					mysqli_query($connid, "DROP TABLE IF EXISTS `". $db_settings['banlists_table'] ."_old`;");
					
					
					// changes in the bookmarks table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the bookmark tags table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_tags_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the categories table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['category_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the entry cache table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_cache_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the entry tags table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_tags_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the login control table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
					CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
					
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the pages table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['pages_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the read entries table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['read_status_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the smilies table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the subscriptions table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['subscriptions_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the tags table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "`
					CHANGE `tag` `tag` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;");
					
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					
					// changes in the temporary information table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['temp_infos_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
					
					
					// changes in the uploads table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['uploads_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
					
					
					// changes in the user online table
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
					CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
					
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
					CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
					
					mysqli_commit($connid);
				} catch (mysqli_sql_exception $exception) {
					mysqli_rollback($connid);
					$update['errors'][] = mysqli_errno($connid) .", ". mysqli_error($connid). ", " . $exception->getMessage();
					//throw $exception;
				}
				mysqli_autocommit($connid, true);
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
				// #493, #507, #557, #561, #562
				$update['items'][] = 'config/b8_config.php';
				// #498, #507, #575, #589, #612, #645, #652, #656, #659
				$update['items'][] = 'config/php_mailer.php';
				
				// #364, #367, #390, #410, #427, #456, #478, #489, #523, #565, #575, #589,
				// #594, #612, #623, #645, #652, #656, #659
				$update['items'][] = 'includes/admin.inc.php';
				// #526, #575, #594
				$update['items'][] = 'includes/auto_login.inc.php';
				// #554
				$update['items'][] = 'includes/avatar.inc.php';
				// #507, #557, #561, #562
				$update['items'][] = 'includes/b8.inc.php';
				// #560
				$update['items'][] = 'includes/bookmark.inc.php';
				// #489, #501, #505, #507, #594
				$update['items'][] = 'includes/contact.inc.php';
				// #705
				$update['items'][] = 'includes/delete_cookie.inc.php';
				// #505, #509, #536, #585, #611
				$update['items'][] = 'includes/entry.inc.php';
				// #377, #390, #410, #467, #478, #498, #499, #512, #519. #520, #524, #526,
				// #528, #540, #554, #571, #587, #589, #593, #594, #595, #603, #606, #619,
				// #623, #647, #650, #667
				$update['items'][] = 'includes/functions.inc.php';
				// #472, #521, #554, #611
				$update['items'][] = 'includes/index.inc.php';
				// #390
				$update['delete'][] = 'includes/insert_flash.inc.php (remove)';
				// #377, #390, #550, #575, #578, #589
				$update['items'][] = 'includes/js_defaults.inc.php';
				// #526, #550, #554, #594
				$update['items'][] = 'includes/login.inc.php';
				// #498, #507, #583
				$update['items'][] = 'includes/mailer.inc.php';
				// #510, #526, #553, #594
				$update['items'][] = 'includes/main.inc.php';
				// #410, #469, #471, #491, #494, #505, #507, #522, #528, #554, #557, #561,
				// #562, #570, #594, #627, #653
				$update['items'][] = 'includes/posting.inc.php';
				// #594
				$update['items'][] = 'includes/register.inc.php';
				// #476
				$update['items'][] = 'includes/rss.inc.php';
				// #560, #594, #622
				$update['items'][] = 'includes/search.inc.php';
				// #505, #554
				$update['items'][] = 'includes/thread.inc.php';
				// #451, #454, #462, #554, #623
				$update['items'][] = 'includes/upload_image.inc.php';
				// #470, #478, #505, #525, #527, #550, #551, #566, #575, #594, #595, #654,
				// #657
				$update['items'][] = 'includes/user.inc.php';
				// #427
				$update['items'][] = 'includes/';
				// #554, #589
				$update['delete'][] = 'includes/classes/ (remove)';
				
				// #390, #498, #507
				$update['items'][] = 'index.php';
				
				// #466, #543, #545, #579, #589
				$update['delete'][] = 'js/admin.js (remove)';
				$update['delete'][] = 'js/admin.min.js (remove)';
				// #543, #544, #545, #546, #573, #578, #589
				$update['items'][] = 'js/main.js';
				// #574, #578, #589
				$update['items'][] = 'js/main.min.js';
				// #390, #450, #475, #543, #545
				$update['items'][] = 'js/posting.js';
				// #390, #450, #475, #580
				$update['items'][] = 'js/posting.min.js';
				$update['items'][] = 'js/';
				
				// #364, #390, #427, #470, #471, #489, #501, #514, #523, #526, #530, #532,
				// #539, #548, #549, #550, #566, #569, #572, #575, #577, #587, #589, #599,
				// #611, #647, #652, #665
				$update['items'][] = 'lang/';
				
				// #427, #463, #557, #561, #562
				$update['items'][] = 'modules/b8/';
				// #511, #518, #559
				$update['delete'][] = 'modules/bad-behavior/ (remove)';
				// #581
				$update['items'][] = 'modules/geshi/';
				// #498, #555, #583
				$update['items'][] = 'modules/phpmailer/';
				// #556, #582, #584, #586
				$update['items'][] = 'modules/smarty/';
				// #558
				$update['items'][] = 'modules/stringparser_bbcode/';
				// #511, #518
				$update['items'][] = 'modules/';
				
				// #589
				$update['delete'][] = 'themes/default/images/backup.png (remove)';
				// #612
				$update['delete'][] = 'themes/default/images/bg_gradient_x.png (remove)';
				// #612
				$update['delete'][] = 'themes/default/images/bg_gradient_y.png (remove)';
				// #364
				$update['items'][] = 'themes/default/images/image.png';
				// #611
				$update['items'][] = 'themes/default/images/keep_eye_on.png';
				// #364, #390, #470, #489, #575, #589, #612, #645, #652, #656, #659
				$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';
				// #656
				$update['items'][] = 'themes/default/subtemplates/bookmarks.inc.tpl';
				// #489, #501, #505, #656
				$update['items'][] = 'themes/default/subtemplates/contact.inc.tpl';
				// #469, #470, #530, #536, #542, #611, #620, #624, #656
				$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';
				// #656
				$update['items'][] = 'themes/default/subtemplates/errors.inc.tpl';
				// #469, #471, #611, #652
				$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';
				// #469, #471, #611, #612, #652
				$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';
				// #645, #656
				$update['items'][] = 'themes/default/subtemplates/login.inc.tpl';
				// #656
				$update['items'][] = 'themes/default/subtemplates/page.inc.tpl';
				// #377, #390, #471, #494, #620, #656
				$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
				// #656
				$update['items'][] = 'themes/default/subtemplates/posting_delete.inc.tpl';
				// #656
				$update['items'][] = 'themes/default/subtemplates/posting_delete_marked.inc.tpl';
				// #656
				$update['items'][] = 'themes/default/subtemplates/posting_delete_spam.inc.tpl';
				// #626, #653, #656
				$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';
				// #656
				$update['items'][] = 'themes/default/subtemplates/posting_manage_postings.inc.tpl';
				// #656
				$update['items'][] = 'themes/default/subtemplates/posting_move.inc.tpl';
				// #626, #653, #656
				$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';
				// #656
				$update['items'][] = 'themes/default/subtemplates/posting_unsubscribe.inc.tpl';
				// #645, #656
				$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';
				// #469, #470, #530, #537, #620, #624
				$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';
				// #469, #470, #530, #537, #620, #624
				$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
				// #612, #652
				$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';
				// #550, #656
				$update['items'][] = 'themes/default/subtemplates/user_edit.inc.tpl';
				// #645, #656
				$update['items'][] = 'themes/default/subtemplates/user_edit_email.inc.tpl';
				// #645, #656
				$update['items'][] = 'themes/default/subtemplates/user_edit_pw.inc.tpl';
				// #652, #657
				$update['items'][] = 'themes/default/subtemplates/user_postings.inc.tpl';
				// #654, #656
				$update['items'][] = 'themes/default/subtemplates/user_profile.inc.tpl';
				// #645, #656
				$update['items'][] = 'themes/default/subtemplates/user_remove_account.inc.tpl';
				// #377, #470, #503, #504, #530, #531, #540, #547, #588, #589, #598, #608,
				// #637
				$update['items'][] = 'themes/default/main.tpl';
				// #390
				$update['delete'][] = 'themes/default/insert_flash.inc.tpl (remove)';
				// #390
				$update['items'][] = 'themes/default/js_config.ini';
				// #390, #461, #477, #530, #531, #533, #534, #537, #538, #598, #608, #612,
				// #620, #624, #625, #626, #630, #640, #652, #656
				$update['items'][] = 'themes/default/style.css';
				// #390, #461, #477, #530, #531, #533, #534, #537, #598, #608, #612, #620,
				// #624, #625, #626, #630, #640, #652, #656
				$update['items'][] = 'themes/default/style.min.css';
				// #427
				$update['items'][] = 'themes/default/';
				
				$update['items'] = array_merge(reorderUpgradeFiles($update['items']), reorderUpgradeFiles($update['delete']));
			}
		}
	}
}

// upgrade from version 2.4.99.0
if (empty($update['errors']) && in_array($settings['version'], array('2.4.99.0'))) {
	/**
	 * From here on everything can be done as a transaction in one step
	 */
	if (empty($update['errors'])) {
		mysqli_autocommit($connid, false);
		if (empty($update['errors'])) {
			mysqli_begin_transaction($connid);
			try {
				// changes in the setting table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['settings_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				$uaa = ($settings['user_area_public'] == 0) ? 2 : 1;
				@mysqli_query($connid, "INSERT INTO `" . $db_settings['settings_table'] . "` (`name`, `value`)
				VALUES
					('user_area_access', ". intval($uaa) ."),
					('b8_mail_check', '0'),
					('php_mailer', '0'),
					('delete_inactive_users', '30'),
					('notify_inactive_users', '3'),
					('link_open_target', '');");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET
				value = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js'
				WHERE name = 'bbcode_latex_uri';");
				
				mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "`
				WHERE name IN(
					'user_area_public',
					'bad_behavior');");
				
				
				// changes in the new introduced tables
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				$rIndex_akismet_spam = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'akismet_spam';");
				if (mysqli_num_rows($rIndex_akismet_spam) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `akismet_spam` (`spam`);");
				}
				
				$rIndex_spam_check_status = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'spam_check_status';");
				if (mysqli_num_rows($rIndex_spam_check_status) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `spam_check_status` (`spam`);");
				}
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "`
				ADD KEY `B8_spam` (`spam`),
				ADD KEY `B8_training_type` (`training_type`);");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CHANGE `token` `token` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';");
				
				
				// changes in the user data table
				// ???
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CHANGE `user_name` `user_name` VARCHAR(128) NOT NULL,
				CHANGE `user_email` `user_email` VARCHAR(255) NOT NULL,
				CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL,
				CHANGE `last_logout` `last_logout` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `registered` `registered` TIMESTAMP NULL DEFAULT NULL;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CHANGE `user_name` `user_name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				DROP INDEX `user_type`,
				DROP INDEX `user_name`,
				ADD KEY `key_user_type` (`user_type`),
				ADD UNIQUE KEY `key_user_name` (`user_name`),
				ADD UNIQUE KEY `key_user_email` (`user_email`);");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				ADD `inactivity_notification` BOOLEAN NOT NULL DEFAULT FALSE,
				ADD `browser_window_target` tinyint(4) NOT NULL DEFAULT '0' AFTER `user_lock`;");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`birthday` = NULL
				WHERE `birthday` = '0000-00-00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`last_logout` = NULL
				WHERE `last_logout` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`registered` = NULL
				WHERE `registered` = '0000-00-00 00:00:00';");
				
				
				// changes in the user data cache table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_cache_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the forum/entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
				CHANGE `last_reply` `last_reply` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `edited` `edited` TIMESTAMP NULL DEFAULT NULL;");
				
				$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."`
				LIKE 'email_notification';");
				if (mysqli_num_rows($rEN_exists) > 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					DROP `email_notification`;");
				}
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`last_reply` = NULL
				WHERE `last_reply` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`edited` = NULL
				WHERE `edited` = '0000-00-00 00:00:00';");
				
				
				// changes in the banlist table
				mysqli_query($connid, "RENAME TABLE `". $db_settings['banlists_table'] ."`
				TO `". $db_settings['banlists_table'] ."_old`;");
				
				mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `". $db_settings['banlists_table'] ."` (`name` varchar(255) NOT NULL, `list` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'ips';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'user_agents';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'words';");
				
				mysqli_query($connid, "DROP TABLE IF EXISTS `". $db_settings['banlists_table'] ."_old`;");
				
				
				// changes in the bookmarks table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the bookmark tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the categories table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['category_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the entry cache table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_cache_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the entry tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the login control table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the pages table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['pages_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the read entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['read_status_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the smilies table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the subscriptions table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['subscriptions_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "`
				CHANGE `tag` `tag` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the temporary information table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['temp_infos_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the uploads table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['uploads_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the user online table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_commit($connid);
			} catch (mysqli_sql_exception $exception) {
				mysqli_rollback($connid);
				$update['errors'][] = mysqli_errno($connid) .", ". mysqli_error($connid). ", " . $exception->getMessage();
				//throw $exception;
			}
		}
		mysqli_autocommit($connid, true);
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
		// #493, #507, #557, #561, #562
		$update['items'][] = 'config/b8_config.php';
		// #498, #507, #575, #589, #612, #645, #652, #656, #659
		$update['items'][] = 'config/php_mailer.php';
		
		// #478, #489, #523, #565, #575, #589, #594, #612, #623, #645, #652, #656, #659
		$update['items'][] = 'includes/admin.inc.php';
		// #526, #575, #594
		$update['items'][] = 'includes/auto_login.inc.php';
		// #554
		$update['items'][] = 'includes/avatar.inc.php';
		// #507, #557, #561, #562
		$update['items'][] = 'includes/b8.inc.php';
		// #560
		$update['items'][] = 'includes/bookmark.inc.php';
		// #489, #501, #505, #507, #594
		$update['items'][] = 'includes/contact.inc.php';
		// #705
		$update['items'][] = 'includes/delete_cookie.inc.php';
		// #505, #509, #536, #585, #611
		$update['items'][] = 'includes/entry.inc.php';
		// #467, #478, #498, #499, #512, #519. #520, #524, #526, #528, #540, #554, #571,
		// #587, #589, #593, #594, #595, #603, #606, #619, #623, #647, #650, #667
		$update['items'][] = 'includes/functions.inc.php';
		// #472, #521, #554, #611
		$update['items'][] = 'includes/index.inc.php';
		// #550, #575, #578, #589
		$update['items'][] = 'includes/js_defaults.inc.php';
		// #526, #550, #554, #594
		$update['items'][] = 'includes/login.inc.php';
		// #498, #507, #583
		$update['items'][] = 'includes/mailer.inc.php';
		// #510, #526, #553, #594
		$update['items'][] = 'includes/main.inc.php';
		// #469, #471, #491, #494, #505, #507, #522, #528, #554, #557, #561, #562, #570,
		// #594, #627, #653
		$update['items'][] = 'includes/posting.inc.php';
		// #594
		$update['items'][] = 'includes/register.inc.php';
		// #476
		$update['items'][] = 'includes/rss.inc.php';
		// #560, #594, #622
		$update['items'][] = 'includes/search.inc.php';
		// #505, #554
		$update['items'][] = 'includes/thread.inc.php';
		// #462, #554, #623
		$update['items'][] = 'includes/upload_image.inc.php';
		// #470, #478, #505, #525, #527, #550, #551, #566, #575, #594, #595, #654, #657
		$update['items'][] = 'includes/user.inc.php';
		$update['items'][] = 'includes/';
		// #554, #589
		$update['delete'][] = 'includes/classes/ (remove)';
		
		// #498, #507
		$update['items'][] = 'index.php';
		
		// #466, #543, #545, #589
		$update['delete'][] = 'js/admin.js (remove)';
		// #466, #579, #589
		$update['delete'][] = 'js/admin.min.js (remove)';
		// #543, #544, #545, #546, #573, #578, #589
		$update['items'][] = 'js/main.js';
		// #574, #578, #589
		$update['items'][] = 'js/main.min.js';
		// #475, #543, #545
		$update['items'][] = 'js/posting.js';
		// #475, #580
		$update['items'][] = 'js/posting.min.js';
		$update['items'][] = 'js/';
		
		// #514, #523, #526, #530, #532, #539, #548, #549, #550, #566, #569, #572, #575,
		// #577, #587, #589, #599, #611, #647, #652, #665
		$update['items'][] = 'lang/';
		
		// #463, #557, #561, #562
		$update['items'][] = 'modules/b8/';
		// #511, #518, #559
		$update['delete'][] = 'modules/bad-behavior/ (remove)';
		// #581
		$update['items'][] = 'modules/geshi/';
		// #498, #555, #583
		$update['items'][] = 'modules/phpmailer/';
		// #556, #582, #584, #586
		$update['items'][] = 'modules/smarty/';
		// #558
		$update['items'][] = 'modules/stringparser_bbcode/';
		// #511, #518
		$update['items'][] = 'modules/';
		
		// #589
		$update['delete'][] = 'themes/default/images/backup.png (remove)';
		// #612
		$update['delete'][] = 'themes/default/images/bg_gradient_x.png (remove)';
		// #612
		$update['delete'][] = 'themes/default/images/bg_gradient_y.png (remove)';
		// #611
		$update['items'][] = 'themes/default/images/keep_eye_on.png';
		// #470, #489, #575, #589, #612, #645, #652, #656, #659
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/bookmarks.inc.tpl';
		// #489, #501, #505, #656
		$update['items'][] = 'themes/default/subtemplates/contact.inc.tpl';
		// #469, #470, #530, #536, #542, #611, #620, #624, #656
		$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/errors.inc.tpl';
		// #469, #471, #611, #652
		$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';
		// #469, #471, #611, #612, #652
		$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/login.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/page.inc.tpl';
		// #471, #494, #620, #656
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_delete.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_delete_marked.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_delete_spam.inc.tpl';
		// #626, #653, #656
		$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_manage_postings.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_move.inc.tpl';
		// #626, #653, #656
		$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_unsubscribe.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';
		// #469, #470, #530, #537, #620, #624
		$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';
		// #469, #470, #530, #537, #620, #624
		$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
		// #612, #652
		$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';
		// #550, #656
		$update['items'][] = 'themes/default/subtemplates/user_edit.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/user_edit_email.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/user_edit_pw.inc.tpl';
		// #652, #657
		$update['items'][] = 'themes/default/subtemplates/user_postings.inc.tpl';
		// #654, #656
		$update['items'][] = 'themes/default/subtemplates/user_profile.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/user_remove_account.inc.tpl';
		// #470, #503, #504, #530, #531, #540, #547, #588, #589, #598, #608, #637
		$update['items'][] = 'themes/default/main.tpl';
		// #461, #477, #530, #531, #533, #534, #537, #538, #598, #608, #612, #620, #624,
		// #625, #626, #630, #640, #652, #656
		$update['items'][] = 'themes/default/style.css';
		// #461, #477, #530, #531, #533, #534, #537, #598, #608, #612, #620, #624, #625,
		// #626, #630, #640, #652, #656
		$update['items'][] = 'themes/default/style.min.css';
		$update['items'][] = 'themes/default/';
		
		$update['items'] = array_merge(reorderUpgradeFiles($update['items']), reorderUpgradeFiles($update['delete']));
	}
}

// upgrade from version 2.4.99.1
if (empty($update['errors']) && in_array($settings['version'], array('2.4.99.1'))) {
	/**
	 * From here on everything can be done as a transaction in one step
	 */
	if (empty($update['errors'])) {
		mysqli_autocommit($connid, false);
		if (empty($update['errors'])) {
			mysqli_begin_transaction($connid);
			try {
				// changes in the setting table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['settings_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "INSERT INTO `" . $db_settings['settings_table'] . "` (`name`, `value`)
				VALUES
					('b8_mail_check', '0'),
					('php_mailer', '0'),
					('delete_inactive_users', '30'),
					('notify_inactive_users', '3'),
					('link_open_target', '');");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET
				value = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js'
				WHERE name = 'bbcode_latex_uri';");
				
				mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "`
				WHERE name = 'bad_behavior';");
				
				// changes in the new introduced tables
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				$rIndex_akismet_spam = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'akismet_spam';");
				if (mysqli_num_rows($rIndex_akismet_spam) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `akismet_spam` (`spam`);");
				}
				
				$rIndex_spam_check_status = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'spam_check_status';");
				if (mysqli_num_rows($rIndex_spam_check_status) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `spam_check_status` (`spam`);");
				}
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "`
				ADD KEY `B8_spam` (`spam`),
				ADD KEY `B8_training_type` (`training_type`);");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CHANGE `token` `token` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';");
				
				
				// changes in the user data table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CHANGE `user_name` `user_name` VARCHAR(128) NOT NULL,
				CHANGE `user_email` `user_email` VARCHAR(255) NOT NULL,
				CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL,
				CHANGE `last_logout` `last_logout` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `registered` `registered` TIMESTAMP NULL DEFAULT NULL;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CHANGE `user_name` `user_name` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				DROP INDEX `user_type`,
				DROP INDEX `user_name`,
				ADD KEY `key_user_type` (`user_type`),
				ADD UNIQUE KEY `key_user_name` (`user_name`),
				ADD UNIQUE KEY `key_user_email` (`user_email`);");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				ADD `inactivity_notification` BOOLEAN NOT NULL DEFAULT FALSE,
				ADD `browser_window_target` tinyint(4) NOT NULL DEFAULT '0' AFTER `user_lock`;");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`birthday` = NULL
				WHERE `birthday` = '0000-00-00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`last_logout` = NULL
				WHERE `last_logout` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`registered` = NULL
				WHERE `registered` = '0000-00-00 00:00:00';");
				
				
				// changes in the forum/entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
				CHANGE `last_reply` `last_reply` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `edited` `edited` TIMESTAMP NULL DEFAULT NULL;");
				
				$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."`
				LIKE 'email_notification'");
				if (mysqli_num_rows($rEN_exists) > 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					DROP `email_notification`;");
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`last_reply` = NULL
				WHERE `last_reply` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`edited` = NULL
				WHERE `edited` = '0000-00-00 00:00:00';");
				
				
				// changes in the banlist table
				mysqli_query($connid, "RENAME TABLE `". $db_settings['banlists_table'] ."`
				TO `". $db_settings['banlists_table'] ."_old`;");
				
				mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `". $db_settings['banlists_table'] ."` (`name` varchar(255) NOT NULL, `list` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'ips';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'user_agents';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'words';");
				
				mysqli_query($connid, "DROP TABLE IF EXISTS `". $db_settings['banlists_table'] ."_old`;");
				
				
				// changes in the bookmark tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the entry tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the login control table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the read entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['read_status_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the smilies table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the subscriptions table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['subscriptions_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['tags_table'] . "`
				CHANGE `tag` `tag` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;");
				
				
				// changes in the temporary information table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['temp_infos_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the uploads table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['uploads_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the user online table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_commit($connid);
			} catch (mysqli_sql_exception $exception) {
				mysqli_rollback($connid);
				$update['errors'][] = mysqli_errno($connid) .", ". mysqli_error($connid). ", " . $exception->getMessage();
				//throw $exception;
			}
		}
		mysqli_autocommit($connid, true);
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
		// #493, #507, #557, #561, #562
		$update['items'][] = 'config/b8_config.php';
		// #498, #507, #575, #589, #612, #645, #652, #656, #659
		$update['items'][] = 'config/php_mailer.php';
		
		// #478, #489, #523, #565, #575, #589, #594, #612, #623, #645, #652, #656, #659
		$update['items'][] = 'includes/admin.inc.php';
		// #526, #575, #594
		$update['items'][] = 'includes/auto_login.inc.php';
		// #554
		$update['items'][] = 'includes/avatar.inc.php';
		// #507, #557, #561, #562
		$update['items'][] = 'includes/b8.inc.php';
		// #560
		$update['items'][] = 'includes/bookmark.inc.php';
		// #489, #501, #505, #507, #594
		$update['items'][] = 'includes/contact.inc.php';
		// #705
		$update['items'][] = 'includes/delete_cookie.inc.php';
		// #505, #509, #536, #585, #611
		$update['items'][] = 'includes/entry.inc.php';
		// #467, #478, #498, #499, #512, #519. #520, #524, #526, #528, #540, #554, #571,
		// #587, #589, #593, #594, #595, #603, #606, #619, #623, #647, #650, #667
		$update['items'][] = 'includes/functions.inc.php';
		// #472, #521, #554, #611
		$update['items'][] = 'includes/index.inc.php';
		// #550, #575, #578, #589
		$update['items'][] = 'includes/js_defaults.inc.php';
		// #526, #550, #554, #594
		$update['items'][] = 'includes/login.inc.php';
		// #498, #507, #583
		$update['items'][] = 'includes/mailer.inc.php';
		// #510, #526, #553, #594
		$update['items'][] = 'includes/main.inc.php';
		// #469, #471, #491, #494, #505, #507, #522, #528, #554, #557, #561, #562, #570,
		// #594, #627, #653
		$update['items'][] = 'includes/posting.inc.php';
		// #594
		$update['items'][] = 'includes/register.inc.php';
		// #476
		$update['items'][] = 'includes/rss.inc.php';
		// #560, #594, #622
		$update['items'][] = 'includes/search.inc.php';
		// #505, #554
		$update['items'][] = 'includes/thread.inc.php';
		// #462, #554, #623
		$update['items'][] = 'includes/upload_image.inc.php';
		// #470, #478, #505, #525, #527, #550, #551, #566, #575, #594, #595, #654, #657
		$update['items'][] = 'includes/user.inc.php';
		$update['items'][] = 'includes/';
		// #554, #589
		$update['delete'][] = 'includes/classes/ (remove)';
		
		// #498, #507
		$update['items'][] = 'index.php';
		
		// #466, #543, #545, #589
		$update['delete'][] = 'js/admin.js (remove)';
		// #466, #579, #589
		$update['delete'][] = 'js/admin.min.js (remove)';
		// #543, #544, #545, #546, #573, #578, #589
		$update['items'][] = 'js/main.js';
		// #574, #578, #589
		$update['items'][] = 'js/main.min.js';
		// #475, #543, #545
		$update['items'][] = 'js/posting.js';
		// #475, #580
		$update['items'][] = 'js/posting.min.js';
		$update['items'][] = 'js/';
		
		// #514, #523, #526, #530, #532, #539, #548, #549, #550, #566, #569, #572, #575,
		// #577, #587, #589, #599, #611, #647, #652, #665
		$update['items'][] = 'lang/';
		
		// #463, #557, #561, #562
		$update['items'][] = 'modules/b8/';
		// #511, #518, #559
		$update['delete'][] = 'modules/bad-behavior/ (remove)';
		// #581
		$update['items'][] = 'modules/geshi/';
		// #498, #555, #583
		$update['items'][] = 'modules/phpmailer/';
		// #556, #582, #584, #586
		$update['items'][] = 'modules/smarty/';
		// #558
		$update['items'][] = 'modules/stringparser_bbcode/';
		// #511, #518
		$update['items'][] = 'modules/';
		
		// #589
		$update['delete'][] = 'themes/default/images/backup.png (remove)';
		// #612
		$update['delete'][] = 'themes/default/images/bg_gradient_x.png (remove)';
		// #612
		$update['delete'][] = 'themes/default/images/bg_gradient_y.png (remove)';
		// #611
		$update['items'][] = 'themes/default/images/keep_eye_on.png';
		// #470, #489, #575, #589, #612, #645, #652, #656, #659
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/bookmarks.inc.tpl';
		// #489, #501, #505, #656
		$update['items'][] = 'themes/default/subtemplates/contact.inc.tpl';
		// #469, #470, #530, #536, #542, #611, #620, #624, #656
		$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/errors.inc.tpl';
		// #469, #471, #611, #652
		$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';
		// #469, #471, #611, #612, #652
		$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/login.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/page.inc.tpl';
		// #471, #494, #620, #656
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_delete.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_delete_marked.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_delete_spam.inc.tpl';
		// #626, #653, #656
		$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_manage_postings.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_move.inc.tpl';
		// #626, #653, #656
		$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_unsubscribe.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';
		// #469, #470, #530, #537, #620, #624
		$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';
		// #469, #470, #530, #537, #620, #624
		$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
		// #612, #652
		$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';
		// #550, #656
		$update['items'][] = 'themes/default/subtemplates/user_edit.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/user_edit_email.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/user_edit_pw.inc.tpl';
		// #652, #657
		$update['items'][] = 'themes/default/subtemplates/user_postings.inc.tpl';
		// #654, #656
		$update['items'][] = 'themes/default/subtemplates/user_profile.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/user_remove_account.inc.tpl';
		// #470, #503, #504, #530, #531, #540, #547, #588, #589, #598, #608, #637
		$update['items'][] = 'themes/default/main.tpl';
		// #461, #477, #530, #531, #533, #534, #537, #538, #598, #608, #612, #620, #624,
		// #625, #626, #630, #640, #652, #656
		$update['items'][] = 'themes/default/style.css';
		// #461, #477, #530, #531, #533, #534, #537, #598, #608, #612, #620, #624, #625,
		// #626, #630, #640, #652, #656
		$update['items'][] = 'themes/default/style.min.css';
		$update['items'][] = 'themes/default/';
		
		$update['items'] = array_merge(reorderUpgradeFiles($update['items']), reorderUpgradeFiles($update['delete']));
	}
}

// upgrade from version 2.4.99.2 and 2.4.99.3
if (empty($update['errors']) && in_array($settings['version'], array('2.4.99.2', '2.4.99.3'))) {
	/**
	 * From here on everything can be done as a transaction in one step
	 */
	if (empty($update['errors'])) {
		mysqli_autocommit($connid, false);
		if (empty($update['errors'])) {
			mysqli_begin_transaction($connid);
			try {
				// changes in the setting table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['settings_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "INSERT INTO `" . $db_settings['settings_table'] . "` (`name`, `value`)
				VALUES
					('delete_inactive_users', '30'),
					('notify_inactive_users', '3'),
					('link_open_target', '');");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET
				value = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js'
				WHERE name = 'bbcode_latex_uri';");
				
				mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "`
				WHERE name = 'bad_behavior';");
				
				
				// changes in the new introduced tables
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				$rIndex_akismet_spam = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'akismet_spam';");
				if (mysqli_num_rows($rIndex_akismet_spam) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `akismet_spam` (`spam`);");
				}
				
				$rIndex_spam_check_status = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'spam_check_status';");
				if (mysqli_num_rows($rIndex_spam_check_status) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `spam_check_status` (`spam`);");
				}
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "`
				ADD KEY `B8_spam` (`spam`),
				ADD KEY `B8_training_type` (`training_type`);");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CHANGE `token` `token` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';");
				
				
				// changes in the user data table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CHANGE `user_email` `user_email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL,
				CHANGE `last_logout` `last_logout` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `registered` `registered` TIMESTAMP NULL DEFAULT NULL;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				ADD `inactivity_notification` BOOLEAN NOT NULL DEFAULT FALSE,
				ADD `browser_window_target` tinyint(4) NOT NULL DEFAULT '0' AFTER `user_lock`;");
				
				$rIndex_user_type = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_type';");
				if (mysqli_num_rows($rIndex_user_type) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_type` (`user_type`);");
				}
				
				$rIndex_user_name = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_name';");
				if (mysqli_num_rows($rIndex_user_name) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_name` (`user_name`);");
				}
				
				$rIndex_user_email = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_email';");
				if (mysqli_num_rows($rIndex_user_email) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_email` (`user_email`);");
				}
				
				$rObsoleteIndexes = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS obsolete_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."' AND
				TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."' AND 
				INDEX_NAME IN('user_type', 'user_name');");
				if (mysqli_num_rows($rObsoleteIndexes) > 0) {
					while ($row  = mysqli_fetch_assoc($rObsoleteIndexes)) {
						if (!@mysqli_query($connid, "DROP INDEX ". $row['obsolete_key'] ." ON " . $db_settings['userdata_table'] .";")) $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
					}
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`birthday` = NULL
				WHERE `birthday` = '0000-00-00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`last_logout` = NULL
				WHERE `last_logout` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`registered` = NULL
				WHERE `registered` = '0000-00-00 00:00:00';");
				
				
				// changes in the forum/entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
				CHANGE `last_reply` `last_reply` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `edited` `edited` TIMESTAMP NULL DEFAULT NULL;");
				
				$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."`
				LIKE 'email_notification'");
				if (mysqli_num_rows($rEN_exists) > 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					DROP `email_notification`;");
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`last_reply` = NULL
				WHERE `last_reply` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`edited` = NULL
				WHERE `edited` = '0000-00-00 00:00:00';");
				
				
				// changes in the banlist table
				mysqli_query($connid, "RENAME TABLE `". $db_settings['banlists_table'] ."`
				TO `". $db_settings['banlists_table'] ."_old`;");
				
				mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `". $db_settings['banlists_table'] ."` (`name` varchar(255) NOT NULL, `list` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'ips';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'user_agents';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'words';");
				
				mysqli_query($connid, "DROP TABLE IF EXISTS `". $db_settings['banlists_table'] ."_old`;");
				
				
				// changes in the bookmark tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the entry tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the login control table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the read entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['read_status_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the smilies table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the subscriptions table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['subscriptions_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the temporary information table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['temp_infos_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the uploads table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['uploads_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the user online table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_commit($connid);
			} catch (mysqli_sql_exception $exception) {
				mysqli_rollback($connid);
				$update['errors'][] = mysqli_errno($connid) .", ". mysqli_error($connid). ", " . $exception->getMessage();
				//throw $exception;
			}
		}
		mysqli_autocommit($connid, true);
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
		// #507, #557, #561, #562
		$update['items'][] = 'config/b8_config.php';
		// #507, #575, #589, #612, #645, #652, #656, #659
		$update['items'][] = 'config/php_mailer.php';
		
		// #508, #575, #589, #612, #645, #652, #656, #659
		$update['items'][] = 'includes/admin.inc.php';
		// #526, #575, #594
		$update['items'][] = 'includes/auto_login.inc.php';
		// #554
		$update['items'][] = 'includes/avatar.inc.php';
		// #507, #557, #561, #562
		$update['items'][] = 'includes/b8.inc.php';
		// #560
		$update['items'][] = 'includes/bookmark.inc.php';
		// #507, #594
		$update['items'][] = 'includes/contact.inc.php';
		// #705
		$update['items'][] = 'includes/delete_cookie.inc.php';
		// #509, #536, #585, #611
		$update['items'][] = 'includes/entry.inc.php';
		// #512, #519. #520, #524, #526, #528, #540, #554, #571, #587, #589, #593, #594,
		// #595, #603, #606, #619, #623, #647, #650, #667
		$update['items'][] = 'includes/functions.inc.php';
		// #611
		$update['items'][] = 'includes/index.inc.php';
		// #550, #575, #578, #589
		$update['items'][] = 'includes/js_defaults.inc.php';
		// #526, #550, #554, #594
		$update['items'][] = 'includes/login.inc.php';
		// #507, #583
		$update['items'][] = 'includes/mailer.inc.php';
		// #510, #526, #553, #594
		$update['items'][] = 'includes/main.inc.php';
		// #507, #522, #528, #554, #557, #561, #562, #570, #594, #627, #653
		$update['items'][] = 'includes/posting.inc.php';
		// #594
		$update['items'][] = 'includes/register.inc.php';
		// #476
		$update['items'][] = 'includes/rss.inc.php';
		// #560, #594, #622
		$update['items'][] = 'includes/search.inc.php';
		// #505, #554
		$update['items'][] = 'includes/thread.inc.php';
		// #554, #623
		$update['items'][] = 'includes/upload_image.inc.php';
		// #505, #525, #527, #550, #551, #566, #575, #594, #595, #654, #657
		$update['items'][] = 'includes/user.inc.php';
		$update['items'][] = 'includes/';
		
		// #508
		$update['items'][] = 'index.php';
		
		// #543, #545, #589
		$update['delete'][] = 'js/admin.js (remove)';
		// #579, #589
		$update['delete'][] = 'js/admin.min.js (remove)';
		// #543, #544, #545, #546, #573, #578, #589
		$update['items'][] = 'js/main.js';
		// #574, #578, #589
		$update['items'][] = 'js/main.min.js';
		// #543, #545
		$update['items'][] = 'js/posting.js';
		// #580
		$update['items'][] = 'js/posting.min.js';
		$update['items'][] = 'js/';
		
		$update['items'][] = 'lang/';
		
		$update['delete'][] = 'modules/bad-behavior (remove)';
		$update['items'][] = 'modules/';
		
		// #589
		$update['delete'][] = 'themes/default/images/backup.png (remove)';
		// #612
		$update['delete'][] = 'themes/default/images/bg_gradient_x.png (remove)';
		// #612
		$update['delete'][] = 'themes/default/images/bg_gradient_y.png (remove)';
		// #611
		$update['items'][] = 'themes/default/images/keep_eye_on.png';
		
		// #508, #575, #589, #612, #645, #652, #656, #659
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/bookmarks.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/contact.inc.tpl';
		// #530, #536, #542, #611, #620, #624, #656
		$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';
		// #611, #652
		$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';
		// #611, #612, #652
		$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';
		// #620, #656
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_delete.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_delete_marked.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_delete_spam.inc.tpl';
		// #626, #653, #656
		$update['items'][] = 'themes/default/subtemplates/posting_flag_ham.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_manage_postings.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_move.inc.tpl';
		// #626, #653, #656
		$update['items'][] = 'themes/default/subtemplates/posting_report_spam.inc.tpl';
		// #656
		$update['items'][] = 'themes/default/subtemplates/posting_unsubscribe.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';
		// #530, #537, #620, #624
		$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';
		// #530, #537, #620, #624
		$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
		// #612, #652
		$update['items'][] = 'themes/default/subtemplates/user.inc.tpl';
		// #550, #656
		$update['items'][] = 'themes/default/subtemplates/user_edit.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/user_edit_email.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/user_edit_pw.inc.tpl';
		// #652, #657
		$update['items'][] = 'themes/default/subtemplates/user_postings.inc.tpl';
		// #654, #656
		$update['items'][] = 'themes/default/subtemplates/user_profile.inc.tpl';
		// #645, #656
		$update['items'][] = 'themes/default/subtemplates/user_remove_account.inc.tpl';
		// #530, #531, #540, #547, #588, #589, #598, #608, #637
		$update['items'][] = 'themes/default/main.tpl';
		// #530, #531, #533, #534, #537, #538, #598, #608, #612, #620, #624, #625, #626,
		// #630, #640, #652, #656
		$update['items'][] = 'themes/default/style.css';
		// #530, #531, #533, #534, #537, #598, #608, #612, #620, #624, #625, #626, #630,
		// #640, #652, #656
		$update['items'][] = 'themes/default/style.min.css';
		$update['items'][] = 'themes/default/';
		
		$update['items'] = array_merge(reorderUpgradeFiles($update['items']), reorderUpgradeFiles($update['delete']));
	}
}

// upgrade from version 20220508.1 (2.5.0) and 20220509.1 (2.5.1)
if (empty($update['errors']) && in_array($settings['version'], array('20220508.1', '20220509.1'))) {
	/**
	 * From here on everything can be done as a transaction in one step
	 */
	if (empty($update['errors'])) {
		mysqli_autocommit($connid, false);
		if (empty($update['errors'])) {
			mysqli_begin_transaction($connid);
			try {
				// changes in the settings table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['settings_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET
				value = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js'
				WHERE name = 'bbcode_latex_uri';");
				
				mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "`
				WHERE name = 'bad_behavior';");
				
				
				// changes in the new introduced tables
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				$rIndex_akismet_spam = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'akismet_spam';");
				if (mysqli_num_rows($rIndex_akismet_spam) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `akismet_spam` (`spam`);");
				}
				
				$rIndex_spam_check_status = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'spam_check_status';");
				if (mysqli_num_rows($rIndex_spam_check_status) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `spam_check_status` (`spam`);");
				}
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CHANGE `token` `token` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';");
				
				
				// changes in the user data table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CHANGE `user_email` `user_email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL,
				CHANGE `last_logout` `last_logout` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `registered` `registered` TIMESTAMP NULL DEFAULT NULL;");
				
				$rIndex_user_type = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_type';");
				if (mysqli_num_rows($rIndex_user_type) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_type` (`user_type`);");
				}
				
				$rIndex_user_name = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_name';");
				if (mysqli_num_rows($rIndex_user_name) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_name` (`user_name`);");
				}
				
				$rIndex_user_email = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_email';");
				if (mysqli_num_rows($rIndex_user_email) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_email` (`user_email`);");
				}
				
				$rObsoleteIndexes = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS obsolete_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME IN('user_type', 'user_name');");
				if (mysqli_num_rows($rObsoleteIndexes) > 0) {
					while ($row  = mysqli_fetch_assoc($rObsoleteIndexes)) {
						mysqli_query($connid, "DROP INDEX ". $row['obsolete_key'] ."
						ON " . $db_settings['userdata_table'] .";");
					}
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`birthday` = NULL
				WHERE `birthday` = '0000-00-00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`last_logout` = NULL
				WHERE `last_logout` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`registered` = NULL
				WHERE `registered` = '0000-00-00 00:00:00';");
				
				
				// changes in the forum/entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
				CHANGE `last_reply` `last_reply` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `edited` `edited` TIMESTAMP NULL DEFAULT NULL;");
				
				$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."`
				LIKE 'email_notification'");
				if (mysqli_num_rows($rEN_exists) > 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					DROP `email_notification`;");
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`last_reply` = NULL
				WHERE `last_reply` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`edited` = NULL
				WHERE `edited` = '0000-00-00 00:00:00';");
				
				
				// changes in the banlist table
				mysqli_query($connid, "RENAME TABLE `". $db_settings['banlists_table'] ."`
				TO `". $db_settings['banlists_table'] ."_old`;");
				
				mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `". $db_settings['banlists_table'] ."` (`name` varchar(255) NOT NULL, `list` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'ips';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'user_agents';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'words';");
				
				mysqli_query($connid, "DROP TABLE IF EXISTS `". $db_settings['banlists_table'] ."_old`;");
				
				
				// changes in the bookmark tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the entry tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the login control table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the read entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['read_status_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the smilies table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the subscriptions table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['subscriptions_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the temporary information table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['temp_infos_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the uploads table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['uploads_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the user online table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_commit($connid);
			} catch (mysqli_sql_exception $exception) {
				mysqli_rollback($connid);
				$update['errors'][] = mysqli_errno($connid) .", ". mysqli_error($connid). ", " . $exception->getMessage();
				//throw $exception;
			}
		}
		mysqli_autocommit($connid, true);
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
		$update['items'][] = 'includes/auto_login.inc.php';
		$update['items'][] = 'includes/delete_cookie.inc.php';
		$update['items'][] = 'includes/entry.inc.php';
		$update['items'][] = 'includes/functions.inc.php';
		$update['items'][] = 'includes/index.inc.php';
		$update['items'][] = 'includes/login.inc.php';
		$update['items'][] = 'includes/main.inc.php';
		$update['items'][] = 'includes/posting.inc.php';
		$update['items'][] = 'includes/search.inc.php';
		$update['items'][] = 'includes/thread.inc.php';
		$update['items'][] = 'includes/upload_image.inc.php';
		$update['items'][] = 'includes/user.inc.php';
		
		$update['items'][] = 'index.php';
		
		$update['items'][] = 'lang/';
		
		$update['delete'][] = 'modules/bad-behavior (remove if present)';
		$update['items'][] = 'modules/';
		
		$update['delete'][] = 'themes/default/images/bg_gradient_x.png (remove if present)';
		$update['delete'][] = 'themes/default/images/bg_gradient_y.png (remove if present)';
		$update['items'][] = 'themes/default/';
		
		$update['items'] = array_merge(reorderUpgradeFiles($update['items']), reorderUpgradeFiles($update['delete']));
	}
}

// upgrade from version 20220517.1 (2.5.2) and 20220529.1 (2.5.3)
if (empty($update['errors']) && in_array($settings['version'], array('20220517.1', '20220529.1'))) {
	/**
	 * From here on everything can be done as a transaction in one step
	 */
	if (empty($update['errors'])) {
		mysqli_autocommit($connid, false);
		if (empty($update['errors'])) {
			mysqli_begin_transaction($connid);
			try {
				// changes in the settings table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['settings_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET
				value = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js'
				WHERE name = 'bbcode_latex_uri';");
				
				mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "`
				WHERE name = 'bad_behavior';");
				
				
				// changes in the new introduced tables
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				$rIndex_akismet_spam = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'akismet_spam';");
				if (mysqli_num_rows($rIndex_akismet_spam) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `akismet_spam` (`spam`);");
				}
				
				$rIndex_spam_check_status = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'spam_check_status';");
				if (mysqli_num_rows($rIndex_spam_check_status) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `spam_check_status` (`spam`);");
				}
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CHANGE `token` `token` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';");
				
				
				// changes in the user data table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CHANGE `user_email` `user_email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL,
				CHANGE `last_logout` `last_logout` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `registered` `registered` TIMESTAMP NULL DEFAULT NULL;");
				
				$rIndex_user_type = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_type';");
				if (mysqli_num_rows($rIndex_user_type) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_type` (`user_type`);");
				}
				
				$rIndex_user_name = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_name';");
				if (mysqli_num_rows($rIndex_user_name) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_name` (`user_name`);");
				}
				
				$rIndex_user_email = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_email';");
				if (mysqli_num_rows($rIndex_user_email) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_email` (`user_email`);");
				}
				
				$rObsoleteIndexes = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS obsolete_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME IN('user_type', 'user_name');");
				if (mysqli_num_rows($rObsoleteIndexes) > 0) {
					while ($row  = mysqli_fetch_assoc($rObsoleteIndexes)) {
						mysqli_query($connid, "DROP INDEX ". $row['obsolete_key'] ."
						ON " . $db_settings['userdata_table'] .";");
					}
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`birthday` = NULL
				WHERE `birthday` = '0000-00-00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`last_logout` = NULL
				WHERE `last_logout` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`registered` = NULL
				WHERE `registered` = '0000-00-00 00:00:00';");
				
				
				// changes in the forum/entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
				CHANGE `last_reply` `last_reply` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `edited` `edited` TIMESTAMP NULL DEFAULT NULL;");
				
				$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."`
				LIKE 'email_notification'");
				if (mysqli_num_rows($rEN_exists) > 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					DROP `email_notification`;");
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`last_reply` = NULL
				WHERE `last_reply` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`edited` = NULL
				WHERE `edited` = '0000-00-00 00:00:00';");
				
				
				// changes in the banlist table
				mysqli_query($connid, "RENAME TABLE `". $db_settings['banlists_table'] ."`
				TO `". $db_settings['banlists_table'] ."_old`;");
				
				mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `". $db_settings['banlists_table'] ."` (`name` varchar(255) NOT NULL, `list` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'ips';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'user_agents';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'words';");
				
				mysqli_query($connid, "DROP TABLE IF EXISTS `". $db_settings['banlists_table'] ."_old`;");
				
				
				// changes in the bookmark tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the entry tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the login control table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the read entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['read_status_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the smilies table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the subscriptions table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['subscriptions_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the temporary information table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['temp_infos_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the uploads table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['uploads_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the user online table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_commit($connid);
			} catch (mysqli_sql_exception $exception) {
				mysqli_rollback($connid);
				$update['errors'][] = mysqli_errno($connid) .", ". mysqli_error($connid). ", " . $exception->getMessage();
				//throw $exception;
			}
		}
		mysqli_autocommit($connid, true);
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
		$update['items'][] = 'includes/auto_login.inc.php';
		$update['items'][] = 'includes/delete_cookie.inc.php';
		$update['items'][] = 'includes/entry.inc.php';
		$update['items'][] = 'includes/functions.inc.php';
		$update['items'][] = 'includes/index.inc.php';
		$update['items'][] = 'includes/login.inc.php';
		$update['items'][] = 'includes/main.inc.php';
		$update['items'][] = 'includes/posting.inc.php';
		$update['items'][] = 'includes/search.inc.php';
		$update['items'][] = 'includes/thread.inc.php';
		$update['items'][] = 'includes/upload_image.inc.php';
		$update['items'][] = 'includes/user.inc.php';
		
		$update['items'][] = 'index.php';
		
		$update['items'][] = 'lang/';
		
		$update['delete'][] = 'modules/bad-behavior (remove if present)';
		$update['items'][] = 'modules/';
		
		$update['delete'][] = 'themes/default/images/bg_gradient_x.png (remove if present)';
		$update['delete'][] = 'themes/default/images/bg_gradient_y.png (remove if present)';
		$update['items'][] = 'themes/default/';
		
		$update['items'] = array_merge(reorderUpgradeFiles($update['items']), reorderUpgradeFiles($update['delete']));
	}
}

// upgrade from version 20220803.1 (2.5.4)
if (empty($update['errors']) && in_array($settings['version'], array('20220803.1'))) {
	/**
	 * From here on everything can be done as a transaction in one step
	 */
	if (empty($update['errors'])) {
		mysqli_autocommit($connid, false);
		if (empty($update['errors'])) {
			mysqli_begin_transaction($connid);
			try {
				// changes in the settings table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['settings_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET
				value = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js'
				WHERE name = 'bbcode_latex_uri';");
				
				mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "`
				WHERE name = 'bad_behavior';");
				
				
				// changes in the new introduced tables
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				$rIndex_akismet_spam = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'akismet_spam';");
				if (mysqli_num_rows($rIndex_akismet_spam) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `akismet_spam` (`spam`);");
				}
				
				$rIndex_spam_check_status = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['akismet_rating_table'] ."'
				AND INDEX_NAME = 'spam_check_status';");
				if (mysqli_num_rows($rIndex_spam_check_status) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['akismet_rating_table'] ."`
					ADD KEY `spam_check_status` (`spam`);");
				}
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_rating_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['b8_wordlist_table'] . "`
				CHANGE `token` `token` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '';");
				
				
				// changes in the user data table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CHANGE `user_email` `user_email` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL,
				CHANGE `last_logout` `last_logout` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `registered` `registered` TIMESTAMP NULL DEFAULT NULL;");
				
				$rIndex_user_type = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_type';");
				if (mysqli_num_rows($rIndex_user_type) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_type` (`user_type`);");
				}
				
				$rIndex_user_name = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_name';");
				if (mysqli_num_rows($rIndex_user_name) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_name` (`user_name`);");
				}
				
				$rIndex_user_email = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS missing_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME = 'key_user_email';");
				if (mysqli_num_rows($rIndex_user_email) === 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] ."`
					ADD KEY `key_user_email` (`user_email`);");
				}
				
				$rObsoleteIndexes = mysqli_query($connid, "SELECT DISTINCT INDEX_NAME AS obsolete_key
				FROM information_schema.STATISTICS 
				WHERE TABLE_SCHEMA LIKE '". $db_settings['database'] ."'
				AND TABLE_NAME LIKE '" . $db_settings['userdata_table'] ."'
				AND INDEX_NAME IN('user_type', 'user_name');");
				if (mysqli_num_rows($rObsoleteIndexes) > 0) {
					while ($row  = mysqli_fetch_assoc($rObsoleteIndexes)) {
						mysqli_query($connid, "DROP INDEX ". $row['obsolete_key'] ."
						ON " . $db_settings['userdata_table'] .";");
					}
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`birthday` = NULL
				WHERE `birthday` = '0000-00-00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`last_logout` = NULL
				WHERE `last_logout` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`registered` = NULL
				WHERE `registered` = '0000-00-00 00:00:00';");
				
				
				// changes in the forum/entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
				CHANGE `last_reply` `last_reply` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `edited` `edited` TIMESTAMP NULL DEFAULT NULL;");
				
				$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."`
				LIKE 'email_notification'");
				if (mysqli_num_rows($rEN_exists) > 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					DROP `email_notification`;");
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`last_reply` = NULL
				WHERE `last_reply` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`edited` = NULL
				WHERE `edited` = '0000-00-00 00:00:00';");
				
				
				// changes in the banlist table
				mysqli_query($connid, "RENAME TABLE `". $db_settings['banlists_table'] ."`
				TO `". $db_settings['banlists_table'] ."_old`;");
				
				mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `". $db_settings['banlists_table'] ."` (`name` varchar(255) NOT NULL, `list` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'ips';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'user_agents';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'words';");
				
				mysqli_query($connid, "DROP TABLE IF EXISTS `". $db_settings['banlists_table'] ."_old`;");
				
				
				// changes in the bookmark tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['bookmark_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the entry tags table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['entry_tags_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the login control table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['login_control_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the read entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['read_status_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the smilies table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['smilies_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the subscriptions table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['subscriptions_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				
				// changes in the temporary information table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['temp_infos_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the uploads table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['uploads_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;");
				
				
				// changes in the user online table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CHANGE `ip` `ip` VARCHAR(128) NOT NULL default '';");
				
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['useronline_table'] . "`
				CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
				
				mysqli_commit($connid);
			} catch (mysqli_sql_exception $exception) {
				mysqli_rollback($connid);
				$update['errors'][] = mysqli_errno($connid) .", ". mysqli_error($connid). ", " . $exception->getMessage();
				//throw $exception;
			}
		}
		mysqli_autocommit($connid, true);
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
		$update['items'][] = 'includes/auto_login.inc.php';
		$update['items'][] = 'includes/delete_cookie.inc.php';
		$update['items'][] = 'includes/entry.inc.php';
		$update['items'][] = 'includes/functions.inc.php';
		$update['items'][] = 'includes/index.inc.php';
		$update['items'][] = 'includes/login.inc.php';
		$update['items'][] = 'includes/main.inc.php';
		$update['items'][] = 'includes/posting.inc.php';
		$update['items'][] = 'includes/search.inc.php';
		$update['items'][] = 'includes/thread.inc.php';
		$update['items'][] = 'includes/user.inc.php';
		
		$update['items'][] = 'index.php';
		
		$update['items'][] = 'lang/';
		
		$update['delete'][] = 'modules/bad-behavior (remove if present)';
		$update['items'][] = 'modules/';
		
		$update['delete'][] = 'themes/default/images/bg_gradient_x.png (remove if present)';
		$update['delete'][] = 'themes/default/images/bg_gradient_y.png (remove if present)';
		$update['items'][] = 'themes/default/';
		
		$update['items'] = array_merge(reorderUpgradeFiles($update['items']), reorderUpgradeFiles($update['delete']));
	}
}

// upgrade from version 20240308.1 (2.5.5.alpha)
if (empty($update['errors']) && in_array($settings['version'], array('20240308.1'))) {
	/**
	 * From here on everything can be done as a transaction in one step
	 */
	if (empty($update['errors'])) {
		mysqli_autocommit($connid, false);
		if (empty($update['errors'])) {
			mysqli_begin_transaction($connid);
			try {
				// changes in the settings table
				mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "`
				WHERE name = 'bad_behavior';");
				
				
				// changes in the user data table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['userdata_table'] . "`
				CHANGE `birthday` `birthday` DATE NULL DEFAULT NULL,
				CHANGE `last_logout` `last_logout` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `registered` `registered` TIMESTAMP NULL DEFAULT NULL;");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`birthday` = NULL
				WHERE `birthday` = '0000-00-00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`last_logout` = NULL
				WHERE `last_logout` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['userdata_table'] . "` SET
				`registered` = NULL
				WHERE `registered` = '0000-00-00 00:00:00';");
				
				
				// changes in the forum/entries table
				mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
				CHANGE `last_reply` `last_reply` TIMESTAMP NULL DEFAULT NULL,
				CHANGE `edited` `edited` TIMESTAMP NULL DEFAULT NULL;");
				
				$rEN_exists = mysqli_query($connid, "SHOW COLUMNS FROM `". $db_settings['forum_table'] ."`
				LIKE 'email_notification'");
				if (mysqli_num_rows($rEN_exists) > 0) {
					mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "`
					DROP `email_notification`;");
				}
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`last_reply` = NULL
				WHERE `last_reply` = '0000-00-00 00:00:00';");
				
				mysqli_query($connid, "UPDATE `" . $db_settings['forum_table'] . "` SET
				`edited` = NULL
				WHERE `edited` = '0000-00-00 00:00:00';");
				
				
				// changes in the banlist table
				mysqli_query($connid, "RENAME TABLE `". $db_settings['banlists_table'] ."`
				TO `". $db_settings['banlists_table'] ."_old`;");
				
				mysqli_query($connid, "CREATE TABLE IF NOT EXISTS `". $db_settings['banlists_table'] ."` (`name` varchar(255) NOT NULL, `list` text NOT NULL, PRIMARY KEY (`name`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'ips';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'user_agents';");
				
				mysqli_query($connid, "INSERT INTO `". $db_settings['banlists_table'] ."`(`name`, `list`)
				SELECT `name`, GROUP_CONCAT(`list` SEPARATOR '\n') AS `list`
				FROM `". $db_settings['banlists_table'] ."_old` WHERE `name` = 'words';");
				
				mysqli_query($connid, "DROP TABLE IF EXISTS `". $db_settings['banlists_table'] ."_old`;");
				
				mysqli_commit($connid);
			} catch (mysqli_sql_exception $exception) {
				mysqli_rollback($connid);
				$update['errors'][] = mysqli_errno($connid) .", ". mysqli_error($connid). ", " . $exception->getMessage();
				//throw $exception;
			}
		}
		mysqli_autocommit($connid, true);
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
		$update['items'][] = 'includes/auto_login.inc.php';
		$update['items'][] = 'includes/delete_cookie.inc.php';
		$update['items'][] = 'includes/entry.inc.php';
		$update['items'][] = 'includes/functions.inc.php';
		$update['items'][] = 'includes/index.inc.php';
		$update['items'][] = 'includes/login.inc.php';
		$update['items'][] = 'includes/main.inc.php';
		$update['items'][] = 'includes/posting.inc.php';
		$update['items'][] = 'includes/search.inc.php';
		$update['items'][] = 'includes/thread.inc.php';
		$update['items'][] = 'includes/user.inc.php';
		
		$update['items'][] = 'index.php';
		
		$update['items'][] = 'lang/';
		
		$update['delete'][] = 'modules/bad-behavior (remove if present)';
		$update['items'][] = 'modules/';
		
		$update['delete'][] = 'themes/default/images/bg_gradient_x.png (remove if present)';
		$update['delete'][] = 'themes/default/images/bg_gradient_y.png (remove if present)';
		$update['items'][] = 'themes/default/';
		
		$update['items'] = array_merge(reorderUpgradeFiles($update['items']), reorderUpgradeFiles($update['delete']));
	}
}
