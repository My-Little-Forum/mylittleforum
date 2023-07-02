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
$update['version'] = array('2.4.19.1', '2.4.20', '2.4.21', '2.4.22', '2.4.23', '2.4.24', '2.4.99.0', '2.4.99.1', '2.4.99.2', '2.4.99.3', '20220508.1', '20220509.1', '20220517.1', '20220529.1', '20220803.1');
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


// check version:
if (!file_exists('config/VERSION')) $update['errors'][] = 'Error in line '.__LINE__.': Missing the file config/VERSION. Load it up from your script package (config/VERSION) before proceeding.';

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

