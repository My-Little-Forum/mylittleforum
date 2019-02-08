<?php
/******************************************************************************
* my little forum                                                             *
* update file to update from version 2.4.19.1 to version 2.5.*                *
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
$update['version'] = array('2.4.19.1');
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

// changed files at the *end of the list* (folders followed by a slash like this: folder/):
// Note: Do *NOT* add 'break;' to a single case!!!
switch($settings['version']) {
	case '2.4.8':
		$update['items'][] = 'includes/admin.inc.php';                               // #352, #353
		$update['items'][] = 'lang/';                                                // #352, #362, #370
		$update['items'][] = 'themes/default/main.tpl';                              // #357
		$update['items'][] = 'js/main.js';                                           // #360
		$update['items'][] = 'js/main.min.js';                                       // #360
		$update['items'][] = 'lang/german.lang';                                     // #363
		$update['items'][] = 'themes/default/style.css';                             // #365
		$update['items'][] = 'themes/default/style.min.css';                         // #370
		$update['items'][] = 'includes/functions.inc.php';                           // #366, #368, #369
	case '2.4.9':
		$update['items'][] = 'lang/';                                                // #371, #375
		$update['items'][] = 'includes/functions.inc.php';                           // #372, #373
		$update['items'][] = 'modules/bad-behavior';                                 // #374
		$update['items'][] = 'modules/geshi';                                        // #374
		$update['items'][] = 'modules/smarty';                                       // #374
		$update['items'][] = 'includes/admin.inc.php';                               // #375
		$update['items'][] = 'includes/login.inc.php';                               // #375
		$update['items'][] = 'includes/posting.inc.php';                             // #375
		$update['items'][] = 'includes/register.inc.php';                            // #375, #378
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';            // #375
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';          // #375
		$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';         // #375
		$update['items'][] = 'themes/default/subtemplates/user_agreement.inc.tpl';   // #375
		$update['items'][] = 'themes/default/main.tpl';                              // #378
		$update['items'][] = 'themes/default/style.css';                             // #379
		$update['items'][] = 'themes/default/style.min.css';                         // #379
	case '2.4.10':
	case '2.4.11':
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';            // #380
		$update['items'][] = 'themes/default/subtemplates/user_agreement.inc.tpl';   // #381
	case '2.4.12':
		$update['items'][] = 'js/main.js';                                           // #384, #392, #393
		$update['items'][] = 'js/main.min.js';                                       // #384, #395
		$update['items'][] = 'includes/posting.inc.php';                             // #387, #388
		$update['items'][] = 'includes/register.inc.php';                            // #387
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';          // #387
		$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';         // #387
		$update['items'][] = 'includes/functions.inc.php';                           // #389, #391, #394
		$update['items'][] = 'lang/german.lang';                                     // #393
	case '2.4.13':
		$update['items'][] = 'includes/admin.inc.php';                               // #396, #397
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';            // #396
		$update['items'][] = 'includes/functions.inc.php';                           // #399
		$update['items'][] = 'includes/posting.inc.php';                             // #399
		$update['items'][] = 'lang/';                                                // #399, #400, #401, #403, #404, #405
		$update['items'][] = 'themes/default/subtemplates/posting_unsubscribe.inc.tpl.'; // #399
		$update['items'][] = 'includes/js_defaults.inc.php';                         // #403, #404
		$update['items'][] = 'includes/register.inc.php';                            // #403
		$update['items'][] = 'js/admin.js';                                          // #403
		$update['items'][] = 'js/main.js';                                           // #403, #404
		$update['items'][] = 'js/main.min.js';                                       // #403, #404
		$update['items'][] = 'js/posting.js';                                        // #403
		$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';         // #403
		$update['items'][] = 'includes/upload_image.inc.php';                        // no pull request
	case '2.4.14':
		$update['items'][] = 'includes/admin.inc.php';                               // no pull request
		$update['items'][] = 'includes/functions.inc.php';                           // no pull request
		$update['items'][] = 'includes/posting.inc.php';                             // no pull request
	case '2.4.15':
		$update['items'][] = 'includes/bookmark.inc.php';                            // #419
		$update['items'][] = 'includes/posting.inc.php';                             // #421
	case '2.4.16':
		$update['items'][] = 'includes/contact.inc.php';                             // #413, #423
		$update['items'][] = 'includes/functions.inc.php';                           // #413
		$update['items'][] = 'includes/posting.inc.php';                             // #413, #423
		$update['items'][] = 'includes/register.inc.php';                            // #413, #423, #438, #439
		$update['items'][] = 'index.php';                                            // #429
		$update['items'][] = 'includes/admin.inc.php';                               // #430
		$update['items'][] = 'lang/';                                                // #430, #434, #435, #438
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';            // #430, #437
		$update['items'][] = 'js/posting.js';                                        // #432
		$update['items'][] = 'js/posting.min.js';                                    // #432
		$update['items'][] = 'includes/user.inc.php';                                // #434, #435, #438, #439
		$update['items'][] = 'themes/default/subtemplates/user_edit.inc.tpl';        // #434
		$update['items'][] = 'themes/default/subtemplates/user_remove_account.inc.tpl';// #434
		$update['items'][] = 'themes/default/subtemplates/user_edit_pw.inc.tpl';     // #435, #438
		$update['items'][] = 'js/admin.js';                                          // #437
		$update['items'][] = 'js/admin.min.js';                                      // #437
		$update['items'][] = 'themes/default/subtemplates/register.inc.tpl';         // #438
	case '2.4.17':
		$update['items'][] = 'themes/default/subtemplates/posting_unsubscribe.inc.tpl.'; // #399
		$update['items'][] = 'js/';                                                  // #411, no pull request
		$update['items'][] = 'includes/register.inc.php';                            // #441
		$update['items'][] = 'includes/user.inc.php';                                // #441
		$update['items'][] = 'themes/default/style.css';                             // #442
		$update['items'][] = 'themes/default/style.min.css';                         // #442
		$update['items'][] = 'includes/contact.inc.php';                             // #443
		$update['items'][] = 'lang/english.lang';                                    // #445
		$update['items'][] = 'lang/german.lang';                                     // #445
		$update['items'][] = 'includes/functions.inc.php';                           // #446
	case '2.4.18':
		$update['items'][] = 'themes/default/style.css';                             // #447
		$update['items'][] = 'themes/default/style.min.css';                         // #447
		$update['items'][] = 'lang/english.lang';                                    // #448
		$update['items'][] = 'lang/german.lang';                                     // #448
		$update['items'][] = 'includes/admin.inc.php';                               // #449
		$update['items'][] = 'includes/functions.inc.php';                           // #449
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';            // #449
		$update['items'][] = 'includes/posting.inc.php';                             // #455
	case '2.4.18.1':
		$update['items'][] = 'themes/default/style.css';                             // #447
		$update['items'][] = 'themes/default/style.min.css';                         // #447
		$update['items'][] = 'lang/english.lang';                                    // #448
		$update['items'][] = 'lang/german.lang';                                     // #448
		$update['items'][] = 'includes/admin.inc.php';                               // #449
		$update['items'][] = 'includes/functions.inc.php';                           // #449
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';            // #449
		$update['items'][] = 'includes/posting.inc.php';                             // #455
	case '2.4.19':
		$update['items'][] = 'includes/admin.inc.php';                               // #458
		$update['items'][] = 'includes/functions.inc.php';                           // #458
	case '2.4.19.1':
		$update['items'][] = 'includes/admin.inc.php';                               // #364, #367, #390, #410, #427, #456
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';            // #364, #390
		$update['items'][] = 'themes/default/images/image.png';                      // #364
		$update['items'][] = 'lang/';                                                // #364, #390, #427
		$update['items'][] = 'includes/functions.inc.php';                           // #377, #390, #410
		$update['items'][] = 'includes/js_defaults.inc.php';                         // #377, #390
		$update['items'][] = 'themes/default/main.tpl';                              // #377
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';          // #377, #390
		$update['items'][] = 'includes/insert_flash.inc.php (remove)';               // #390
		$update['items'][] = 'index.php';                                            // #390
		$update['items'][] = 'js/posting.js';                                        // #390, #450
		$update['items'][] = 'js/posting.min.js';                                    // #390, #450
		$update['items'][] = 'themes/default/insert_flash.inc.tpl (remove)';         // #390
		$update['items'][] = 'themes/default/js_config.ini';                         // #390
		$update['items'][] = 'themes/default/style.css';                             // #390
		$update['items'][] = 'themes/default/style.min.css';                         // #390
		$update['items'][] = 'includes/posting.inc.php';                             // #410
		$update['items'][] = 'includes/';                                            // #427
		$update['items'][] = 'modules/b8/';                                          // #427
		$update['items'][] = 'themes/default/';                                      // #427
		$update['items'][] = 'includes/upload_image.inc.php';                        // #451, #454
		
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
	sort($folders);
	
	// Remove single files from list if and only if the complete folder is in list
	foreach ($folders as $folder) {
		$removeFiles = preg_grep("/^".preg_quote($folder, "/").".+/i", $update['items']);
		if (empty($removeFiles))
			continue;
		$update['items'] = array_diff($update['items'], $removeFiles);
	}
	sort($update['items']);
	
	// Add folders at the end of the files list to keep the order (files, folders)
	$update['items'] = array_merge($update['items'], $folders);
}

// check version:
if (!file_exists('config/VERSION')) {
	$update['errors'][] = 'Error in line '.__LINE__.': Missing the file config/VERSION. Load it up from your script package (config/VERSION) before proceeding.';
}
if (empty($update['errors'])) {
	$newVersion = trim(file_get_contents('config/VERSION'));
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
// changes for version 2.5
if (empty($update['errors']) && in_array($settings['version'], array('2.4.8'))) {
	if(!@mysqli_query($connid, "INSERT INTO `".$db_settings['settings_table']."` (`name`, `value`) VALUES ('uploads_per_page', '20');")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
}
/** !!!TODO: Version array is not correct!!! **/
if (empty($update['errors']) && in_array($settings['version'], array('2.4.8', '2.4.9'))) {
	$table_prefix = preg_replace('/settings$/u', '', $db_settings['settings_table']);
	// add new database table
	if (file_exists("./config/db_settings.php") && is_writable("./config/db_settings.php")) {
		$db_settings['subscriptions_table'] = $table_prefix . 'subscriptions';
		$db_settings_file = @fopen("./config/db_settings.php", "w") or $update['errors'][] = str_replace("[CHMOD]",$chmod,$lang['error_overwrite_config_file']);
		if (empty($update['errors'])) {
			flock($db_settings_file, 2);
			fwrite($db_settings_file, "<?php\r\n");
			fwrite($db_settings_file, "\$db_settings['host']                 = '". addslashes($db_settings['host']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['user']                 = '". addslashes($db_settings['user']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['password']             = '". addslashes($db_settings['password']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['database']             = '". addslashes($db_settings['database']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['settings_table']       = '". addslashes($db_settings['settings_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['forum_table']          = '". addslashes($db_settings['forum_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['category_table']       = '". addslashes($db_settings['category_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['userdata_table']       = '". addslashes($db_settings['userdata_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['smilies_table']        = '". addslashes($db_settings['smilies_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['pages_table']          = '". addslashes($db_settings['pages_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['banlists_table']       = '". addslashes($db_settings['banlists_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['useronline_table']     = '". addslashes($db_settings['useronline_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['login_control_table']  = '". addslashes($db_settings['login_control_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['entry_cache_table']    = '". addslashes($db_settings['entry_cache_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['userdata_cache_table'] = '". addslashes($db_settings['userdata_cache_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['bookmark_table']       = '". addslashes($db_settings['bookmark_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['bookmark_tags_table']  = '". addslashes($db_settings['bookmark_tags_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['entry_tags_table']     = '". addslashes($db_settings['entry_tags_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['tags_table']           = '". addslashes($db_settings['tags_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['read_status_table']    = '". addslashes($db_settings['read_status_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['temp_infos_table']     = '". addslashes($db_settings['temp_infos_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['subscriptions_table']  = '". addslashes($db_settings['subscriptions_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['b8_wordlist_table']    = '". addslashes($db_settings['b8_wordlist_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['b8_rating_table']      = '". addslashes($db_settings['b8_rating_table']) ."';\r\n");
			fwrite($db_settings_file, "\$db_settings['akismet_rating_table'] = '". addslashes($db_settings['akismet_rating_table']) ."';\r\n");
			fwrite($db_settings_file, "?".">\r\n");
			flock($db_settings_file, 3);
			fclose($db_settings_file);
			
			// new tables
			if(!@mysqli_query($connid, "CREATE TABLE `" . $db_settings['akismet_rating_table'] . "` (`eid` int(11) NOT NULL, `spam` tinyint(1) NOT NULL DEFAULT '0', `spam_check_status` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`eid`)) CHARSET=utf8 COLLATE=utf8_general_ci;")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "CREATE TABLE `" . $db_settings['b8_rating_table'] . "` (`eid` int(11) NOT NULL, `spam` tinyint(1) NOT NULL DEFAULT '0', `training_type` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`eid`)) CHARSET=utf8 COLLATE=utf8_general_ci;")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "CREATE TABLE `" . $db_settings['b8_wordlist_table'] . "` (`token` varchar(255) character set utf8 collate utf8_bin NOT NULL, `count_ham` int unsigned default NULL, `count_spam` int unsigned default NULL, PRIMARY KEY (`token`)) CHARSET=utf8 COLLATE=utf8_general_ci;")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			
			// changed tables
			if(!@mysqli_query($connid, "INSERT INTO `" . $db_settings['settings_table'] . "` (`name`, `value`) VALUES ('bbcode_latex', '0'), ('bbcode_latex_uri', 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS_CHTML.js'), ('min_posting_time', '5'), ('min_register_time', '5'), ('min_email_time', '5'),  ('max_posting_time', '10800'), ('max_register_time', '10800'), ('max_email_time', '10800'), ('b8_entry_check', '1'), ('b8_auto_training', '1'), ('b8_spam_probability_threshold', '80'), ('min_pw_digits', '0'), ('min_pw_lowercase_letters', '0'), ('min_pw_uppercase_letters', '0'), ('min_pw_special_characters', '0');")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "` WHERE name = 'bbcode_tex';")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "` WHERE name = 'bbcode_flash';")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "` WHERE name = 'flash_default_width';")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "DELETE FROM `" . $db_settings['settings_table'] . "` WHERE name = 'flash_default_height';")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "INSERT INTO `" . $db_settings['b8_wordlist_table'] . "` (`token`, `count_ham`, `count_spam`) VALUES ('b8*dbversion', '3', NULL), ('b8*texts', '0', '0');")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "INSERT INTO `" . $db_settings['akismet_rating_table'] . "` (`eid`, `spam`, `spam_check_status`) SELECT `id`, `spam`, `spam_check_status` FROM `" . $db_settings['forum_table'] ."`;")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "INSERT INTO `" . $db_settings['b8_rating_table'] . "` (`eid`, `spam`, `training_type`) SELECT `id`, `spam`, 0 FROM `" . $db_settings['forum_table'] ."`;")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "UPDATE `" . $db_settings['settings_table'] . "` SET `name`='spam_check_registered' WHERE `name`='akismet_check_registered';")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
			if(!@mysqli_query($connid, "ALTER TABLE `" . $db_settings['forum_table'] . "` DROP `spam`, DROP `spam_check_status`;")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
		}
	}
}

if(empty($update['errors'])) {
	if(!@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='". mysqli_real_escape_string($connid, $newVersion) ."' WHERE name = 'version'")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
	else {
		// Set new Version, taken from VERSION file.
		$update['new_version'] = $newVersion;
		// reenable the forum after database update is done
		if (empty($update['errors'])) {
			if(!@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value = '1' WHERE name =  'forum_enabled'")) {
				$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
			}
		}
	}
}

?>