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
$update['new_version'] = '2.4.0';
$update['download_url'] = 'https://github.com/ilosuna/mylittleforum/releases/latest';
$update['message'] = '<p><strong>Please note:</strong> Manual edit <code>config/db_settings.php</code> and add the new entry <code>$db_settings[\'bookmark_table\'] = \'mlf2_bookmarks\';</code> at the end of the file.</p>';

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
		$update['items'][] = 'index.php';
	
		$update['items'][] = 'js/admin.js';
		$update['items'][] = 'js/admin.min.js';
		$update['items'][] = 'js/main.js';
		$update['items'][] = 'js/main.min.js';
		$update['items'][] = 'js/posting.js';
		$update['items'][] = 'js/posting.min.js';
		
		$update['items'][] = 'includes/function.inc.php';
		$update['items'][] = 'includes/bookmark.inc.php';
		$update['items'][] = 'includes/js_defaults.inc.php';
		$update['items'][] = 'includes/posting.inc.php';
		$update['items'][] = 'includes/thread.inc.php';
		$update['items'][] = 'includes/entry.inc.php';
		$update['items'][] = 'includes/admin.inc.php';
		
		$update['items'][] = 'themes/default/main.tpl';
		$update['items'][] = 'themes/default/style.css';
		$update['items'][] = 'themes/default/style.min.css';
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/index_table.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/index.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/entry.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/contact.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
		$update['items'][] = 'themes/default/subtemplates/bookmark.inc.tpl';
		$update['items'][] = 'themes/default/images/bg_sprite_3.png';
		
		$update['items'][] = 'lang/english.lang';
		$update['items'][] = 'lang/german.lang';
	
	
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

	// Remove single files from list if and only if the complete folder is in list
	foreach ($folders as $key=>$val) {
		$files = preg_grep("/".preg_quote($val, "/").".+/i", $update['items']);
		if (empty($files))
			continue;
		$update['items'] = array_diff($update['items'], $files);
	}
	
	// Add folders at the end of the files list to keep the order (files, folders)
	$update['items'] = array_merge($update['items'], $folders);
}
 
// check version:
if(!in_array($settings['version'], $update['version']))
 {
  $update['errors'][] = 'Error in line '.__LINE__.': This update file doesn\'t work with the current version.';
 }

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8','2.3')))
 {
  if(!@mysqli_query($connid, "INSERT INTO ".$db_settings['settings_table']." VALUES ('stop_forum_spam', '0')"))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
   }
 }

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8','2.3','2.3.1','2.3.2','2.3.3','2.3.4','2.3.5 RC','2.3.5')))
 {
  if(!@mysqli_query($connid, "ALTER TABLE ".$db_settings['userdata_table']." CHANGE last_login last_login timestamp NULL default CURRENT_TIMESTAMP"))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
   }
 }
 
if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8','2.3','2.3.1','2.3.2','2.3.3','2.3.4','2.3.5 RC','2.3.5','2.3.6','2.3.6.1')))
 {
  if(!@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET `value` = 10 WHERE `name` = 'temp_block_ip_after_repeated_failed_logins' AND `value` > 0"))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
   }
 }

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8','2.3','2.3.1','2.3.2','2.3.3','2.3.4','2.3.5 RC','2.3.5','2.3.6','2.3.6.1','2.4.0')))
 {
	 
  if(!@mysqli_query($connid, "CREATE TABLE mlf2_bookmarks (id int(11) NOT NULL AUTO_INCREMENT,user_id int(11) NOT NULL,posting_id int(11) NOT NULL,time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,subject varchar(255) NOT NULL,order_id int(11) NOT NULL DEFAULT '0',PRIMARY KEY (id),UNIQUE KEY UNIQUE_uid_pid (user_id,posting_id))"))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
   }
 }

if(empty($update['errors']))
 {
  if(!@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='".$update['new_version']."' WHERE name = 'version'"))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
   }
 }


?>
