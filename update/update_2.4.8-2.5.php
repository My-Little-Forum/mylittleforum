<?php
/******************************************************************************
* my little forum                                                             *
* update file to update from version 2.4.8 to version 2.5.*                   *
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
$update['version'] = array('2.4.8', '2.4.9');
$update['download_url'] = 'https://github.com/ilosuna/mylittleforum/releases/latest';
$update['message'] = '';

// changed files at the *end of the list* (folders followed by a slash like this: folder/):
// Note: Do *NOT* add 'break;' to a single case!!!
switch($settings['version']) {
	case '2.4.8':
		$update['items'][] = 'includes/admin.inc.php';                                 #364
		$update['items'][] = 'themes/default/subtemplates/admin.inc.tpl';              #364
		$update['items'][] = 'themes/default/images/image.png';                        #364
		$update['items'][] = 'lang/';                                                  #364
		
	case '2.4.9':
		$update['items'][] = 'includes/functions.inc.php';                             #377
		$update['items'][] = 'includes/js_defaults.inc.php';                           #377
		$update['items'][] = 'themes/default/main.tpl';                                #377
		$update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';            #377
		
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
if(!file_exists('config/VERSION')) {
	$update['errors'][] = 'Error in line '.__LINE__.': Missing the file config/VERSION. Load it up from your script package (config/VERSION) before proceeding.';
}
if (empty($update['errors'])) {
	$newVersion = trim(file_get_contents('config/VERSION'));
	if ($newVersion <= $settings['version']) {
		$update['errors'][] = 'Error in line '.__LINE__.': The version you want to install (see string in config/VERSION) must be greater than the current installed version. Current version: '. htmlspecialchars($settings['version']) .', version you want to install: '.  htmlspecialchars($newVersion) .'. Please check also if you uploaded the actual file version of config/VERSION. Search therefore for the file date and compare it with the date from the installation package.';
	}
	if (!in_array($settings['version'], $update['version'])) {
		$update['errors'][] = 'Error in line '.__LINE__.': This update file doesn\'t work with the current version.';
	}
}

if (empty($update['errors']) && in_array($settings['version'], array('2.4.8'))) {
	if(!@mysqli_query($connid, "INSERT INTO `".$db_settings['settings_table']."` (`name`, `value`) VALUES ('uploads_per_page', '20');")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
}

if (empty($update['errors']) && in_array($settings['version'], array('2.4.8', '2.4.9'))) {
	if(!@mysqli_query($connid, "INSERT INTO `".$db_settings['settings_table']."` (`name`, `value`) VALUES ('bbcode_latex', '0'), ('bbcode_latex_uri', 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS_CHTML.js');")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
	if(!@mysqli_query($connid, "DELETE FROM `".$db_settings['settings_table']."` WHERE name = 'bbcode_tex';")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
}

if(empty($update['errors'])) {
	if(!@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='". mysqli_real_escape_string($connid, $newVersion) ."' WHERE name = 'version'")) {
		$update['errors'][] = 'Database error in line '.__LINE__.': ' . mysqli_error($connid);
	}
	else {
		// Set new Version, taken from VERSION file.
		$update['new_version'] = $newVersion;
	}
}

?>
