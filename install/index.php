<?php
/*******************************************************************************
* my little forum installation script                                          *
* Copyright (C) 2010 alex@mylittleforum.net                                    *
* http://mylittleforum.net/                                                    *
*******************************************************************************/

/*******************************************************************************
* This program is free software: you can redistribute it and/or modify         *
* it under the terms of the GNU General Public License as published by         *
* the Free Software Foundation, either version 3 of the License, or            *
* (at your option) any later version.                                          *
*                                                                              *
* This program is distributed in the hope that it will be useful,              *
* but WITHOUT ANY WARRANTY; without even the implied warranty of               *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
* GNU General Public License for more details.                                 *
*                                                                              *
* You should have received a copy of the GNU General Public License            *
* along with this program.  If not, see <http://www.gnu.org/licenses/>.        *
*******************************************************************************/

define('IN_INDEX', TRUE);
include('../config/db_settings.php');
include('../includes/functions.inc.php');

$default_settings['forum_name'] = 'my little forum';
$default_settings['forum_address'] = ((isProtocolHTTPS() === true) ? 'https' : 'http') .'://'. $_SERVER['HTTP_HOST'] . substr(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'), 0, strrpos(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'), '/')) . '/';
$default_settings['table_prefix'] = 'mlf2_';

// stripslashes on GPC if get_magic_quotes_gpc is enabled:
if (get_magic_quotes_gpc()) {
	function stripslashes_deep($value) {
		$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
		return $value;
	}
	$_POST = array_map('stripslashes_deep', $_POST);
	$_GET = array_map('stripslashes_deep', $_GET);
	$_COOKIE = array_map('stripslashes_deep', $_COOKIE);
	$_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

function table_exists($table) {
	global $connid;
	$result = @mysqli_query($connid, "SHOW TABLES");
	while ($row = mysqli_fetch_array($result)) {
		if ($table == $row[0]) return true;
		}
	return false;
}

// check version:
if(!file_exists('../config/VERSION')) {
	die('Error in line '.__LINE__.': Missing the file config/VERSION.');
}
$newVersion = trim(file_get_contents('../config/VERSION'));

if (isset($_POST['language_file'])) $language_file = $_POST['language_file'];

// try to connect to the database …
if ($connid = @mysqli_connect($db_settings['host'], $db_settings['user'], $db_settings['password'])) {
	if (@mysqli_select_db($connid, $db_settings['database'])) {
		@mysqli_query($connid, 'SET NAMES utf8');
		if (table_exists($db_settings['forum_table'])) {
			// the forum seems to be installed
			header('Location: ../');
			exit;
		}
	}
}

// language already selected?
if(empty($language_file)) {
	// get available languages:
	$handle = opendir('../lang/');
	while ($file = readdir($handle)) {
		if (strrchr($file, ".") == ".lang") {
			$file_array[] = $file;
		}
	}
	closedir($handle);
	natcasesort($file_array);
	$i = 0;
	foreach ($file_array as $file) {
		$language_files[$i]['file'] = $file;
		$language_files[$i]['language'] = ucfirst(str_replace(".lang","",$file));
		$language_parts = explode('.', $language_files[$i]['language']);
		if (isset($language_parts[1]))
			$language_files[$i]['language'] = $language_parts[0].' ('.$language_parts[1].')';
		$i++;
	}

	if (empty($language_files)) die('No language file available.');
	elseif (count($language_files) == 1) {
		// there's only one language file so take this one:
		$language_file = $language_files[0]['file'];
	} else {
		// there are several language files so let the user choose one:
		$action = 'choose_language';
	}
}

// set provisional language file:
if (empty($language_file)) $language_file = 'english.lang';

if (isset($language_file)) {
	if(!file_exists('../lang/'.$language_file) && isset($language_files[0]['file'])) $language_file = $language_files[0]['file'];
	if(!file_exists('../lang/'.$language_file)) die('Language file not available.');

	// quick & dirty method to get the config vars without smarty (prevents
	// creation of a compiled template which would only be used once for the
	// installation - doesn't get multi-line-strings properly!):
	$config_file = file('../lang/'.$language_file);
	foreach ($config_file as $line) {
		$line = trim($line);
		if ($line != '' && $line[0] != '[') {
			$line_parts = explode('=', $line,2);
			if (isset($line_parts[1])) {
				$key = trim($line_parts[0]);
				if (isset($lang[$key])) {
					if(is_array($lang[$key])) {
						$lang[$key][] = trim($line_parts[1]);
					} else {
						$lang[$key] = array($lang[$key]);
						$lang[$key][] = trim($line_parts[1]);
					}
				} else {
					$lang[$key] = trim($line_parts[1]);
				}
			}
		}
	}
}

if (isset($_POST['install_submit'])) {
	// are all fields filled out?
	foreach ($_POST as $post) {
		if (trim($post) == "") {
			$errors[] = $lang['error_form_uncomplete'];
			break;
		}
	}
	if (empty($errors)) {
		if ($_POST['admin_pw'] != $_POST['admin_pw_conf']) $errors[] = $lang['error_conf_pw'];
	}
	// try to connect the database with posted access data:
	if (empty($errors)) {
		$connid = @mysqli_connect($_POST['host'], $_POST['user'], $_POST['password']);
		if (!$connid) $errors[] = $lang['error_db_connection']." (MySQL: ".mysqli_connect_error().")";
	}
	if (empty($errors)) {
		if (!file_exists('install.sql')) $errors[] = $lang['error_sql_file_doesnt_exist'];
	}

	// overwrite database settings file:
	if(empty($errors) && empty($_POST['dont_overwrite_settings'])) {
		// Keys of database array
		$db_connection_keys = array('host', 'user', 'password', 'database');
		$db_setting_keys = array(
			'settings_table'       => 'settings', 
			'forum_table'          => 'entries',
			'category_table'       => 'categories',
			'userdata_table'       => 'userdata',
			'smilies_table'        => 'smilies', 
			'pages_table'          => 'pages',
			'banlists_table'       => 'banlists',
			'useronline_table'     => 'useronline',
			'login_control_table'  => 'logincontrol',
			'entry_cache_table'    => 'entries_cache',
			'userdata_cache_table' => 'userdata_cache',
			'bookmark_table'       => 'bookmarks',
			'read_status_table'    => 'read_entries',
			'temp_infos_table'     => 'temp_infos',
			'tags_table'           => 'tags',
			'bookmark_tags_table'  => 'bookmark_tags',
			'entry_tags_table'     => 'entry_tags',
			'subscriptions_table'  => 'subscriptions',
			'b8_wordlist_table'    => 'b8_wordlist',
			'b8_rating_table'      => 'b8_rating',
			'akismet_rating_table' => 'akismet_rating',
			'uploads_table'        => 'uploads'
		);
		clearstatcache();
		$chmod = decoct(fileperms("../config/db_settings.php"));
		
		foreach ($db_connection_keys as $key) {
			// Check POST-data and reject data that contains html or php code like <?php
			if (!isset($_POST[$key]) || $_POST[$key] != strip_tags($_POST[$key])) {
				$errors[] = $lang['error_form_uncomplete'];
				break;
			}
			$db_settings[$key] = $_POST[$key];
		}
		// check table_prefix
		if (!isset($_POST['table_prefix']) || $_POST['table_prefix'] != strip_tags($_POST['table_prefix'])) {
			$errors[] = $lang['error_form_uncomplete'];
		}

		if (empty($errors)) {
			$db_settings_file = @fopen("../config/db_settings.php", "w") or $errors[] = str_replace("[CHMOD]", $chmod, $lang['error_overwrite_config_file']);
			flock($db_settings_file, 2);
			fwrite($db_settings_file, "<?php\n");
			foreach ($db_connection_keys as $key) {
				fwrite($db_settings_file, "\$db_settings['".$key."'] = '".addslashes($db_settings[$key])."';\n");
			}
			foreach ($db_setting_keys as $key => $value) {
				$db_settings[$key] = $_POST['table_prefix'] . $value;
				fwrite($db_settings_file, "\$db_settings['".$key."'] = '".addslashes($db_settings[$key])."';\n");
			}
			fwrite($db_settings_file, "?".">\n");
			flock($db_settings_file, 3);
			fclose($db_settings_file);
		}
	}

	if (empty($errors) && isset($_POST['create_database'])) {
		// create database if desired:
		@mysqli_query($connid, "CREATE DATABASE ".$db_settings['database']) or $errors[] = $lang['create_db_error']." (MySQL: ".mysqli_error($connid).")";
	}

	// select database:
	if (empty($errors)) {
		@mysqli_select_db($connid, $db_settings['database']) or $errors[] = $lang['error_db_inexistent']." (MySQL: ".mysqli_error($connid).")";
		@mysqli_query($connid, 'SET NAMES utf8');
	}

	// run installation sql file:
	if(empty($errors)) {
		if (!isset($_POST['table_prefix']) || $_POST['table_prefix'] != strip_tags($_POST['table_prefix'])) {
			$errors[] = $lang['error_form_uncomplete'];
		} else {
			$lines = file('install.sql');
			$cleared_lines = array();
			foreach($lines as $line) {
				$line = str_replace(' mlf2_', ' '.$_POST['table_prefix'], $line);
				$line = trim($line);
				if (my_substr($line, -1, my_strlen($line,$lang['charset']), $lang['charset']) == ';')
					$line = my_substr($line,0,-1,$lang['charset']);
				if($line != '' && my_substr($line,0,1,$lang['charset']) != '#')
					$cleared_lines[] = $line;
			}
			
			@mysqli_query($connid, "START TRANSACTION") or die(mysqli_error($connid));
			foreach ($cleared_lines as $line) {
				if (!@mysqli_query($connid, $line)) {
					$errors[] = $lang['error_sql']." (MySQL: ".mysqli_error($connid).")";
				}
			}
			if (!@mysqli_query($connid, "INSERT INTO " . $db_settings['temp_infos_table'] . " (`name`, `value`) VALUES ('version', '". mysqli_real_escape_string($connid, $newVersion) ."');")) {
				$errors[] = $lang['error_sql']." (MySQL: ".mysqli_error($connid).")";
			}
			@mysqli_query($connid, "COMMIT");
		}
	}

	// insert admin in userdata table:
	if (empty($errors)) {
		$pw_hash = generate_pw_hash($_POST['admin_pw']);
		@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET user_name='".mysqli_real_escape_string($connid, $_POST['admin_name'])."', user_pw = '".mysqli_real_escape_string($connid, $pw_hash)."', user_email = '".mysqli_real_escape_string($connid, $_POST['admin_email'])."' WHERE user_id=1") or $errors[] = $lang['error_create_admin']." (MySQL: ".mysqli_error($connid).")";
	}

	// set forum name, address and email address:
	if (empty($errors)) {
		@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='".mysqli_real_escape_string($connid, $_POST['forum_name'])."' WHERE name='forum_name' LIMIT 1") or $errors[] = $lang['error_update_settings']." (MySQL: ".mysqli_error($connid).")";
		@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='".mysqli_real_escape_string($connid, $_POST['forum_address'])."' WHERE name='forum_address' LIMIT 1") or $errors[] = $lang['error_update_settings']." (MySQL: ".mysqli_error($connid).")";
		@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='".mysqli_real_escape_string($connid, $_POST['forum_email'])."' WHERE name='forum_email' LIMIT 1") or $errors[] = $lang['error_update_settings']." (MySQL: ".mysqli_error($connid).")";
		@mysqli_query($connid, "UPDATE ".$db_settings['settings_table']." SET value='".mysqli_real_escape_string($connid, $_POST['language_file'])."' WHERE name='language_file' LIMIT 1") or $errors[] = $lang['error_update_settings']." (MySQL: ".mysqli_error($connid).")";
	}
	if (empty($errors)) {
		header('Location: ../');
		exit;
	}
}

if (empty($action)) $action = 'install';

header('Content-Type: text/html; charset='.$lang['charset']);

?><!DOCTYPE html>
<html lang="<?php echo $lang['language']; ?>">
<head>
 <meta charset="<?php echo $lang['charset']; ?>">
 <title>my little forum - <?php echo $lang['installation_title']; ?></title>
 <link rel="shortcut icon" href="../themes/default/images/favicon.ico">
 <style type="text/css">
*, ::before, ::after {
  box-sizing: border-box;
}
body {
  color: #000;
  background: #fff;
  margin: 0;
  padding: 0;
  font-family: verdana, arial, sans-serif;
  font-size: 1em;
  font-size: 1rem;
}
p, ul, button {
  font-size: 1em;
  line-height: 145%;
}
header, main {
  margin: 0;
}
header {
  background: #d2ddea;
  background: linear-gradient(to bottom, #d2deec 0%, #edf2f5 100%);
  border-bottom: 1px solid #bacbdf;
}
header h1 {
  margin: 0  auto;
  padding: 0;
  min-width: 28em;
  width: 60vw;
}
section, main > h2 {
  margin: 1rem auto;
  padding: 1rem;
  min-width: 28em;
  width: 60vw;
}
header > h1, main > h2 {
  padding: 0.5rem 1rem;
}
section {
  padding: 1rem;
  border: 1px solid #bacbdf;
  border-radius: 0.5rem;
}
h1, h2, h3 {
  line-height: 140%;
  margin: 0;
  padding: 0;
}
h1 {
  font-size: 1.5em;
  color: #000080;
}
h2 {
  font-size: 1.15em;
}
section h2 {
  border-bottom: 1px solid #bacbdf;
  padding: 0 0 0.5rem 0;
}
section > ul {
  margin: 1rem 0 0 0;
  padding: 0 0 0 1.5rem;
}
.error {
  background-color: #ffb;
  border-color: #c00;
}
.error h2 {
  color: #c00;
  border-color: #c00;
}
h2 + form {
  margin-top: 1rem;
}
form h3, input[type="text"], input[type="password"], input[type="url"], input[type="email"], option {
  font-size: 1em;
}
fieldset {
  border: none;
  padding: 0;
}
fieldset:not(:last-of-type) {
  margin-bottom: 2.5rem;
}
legend {
  display: block;
  width: 100%;
  padding: 0 0 0.5rem 0;
  font-weight: bold;
  font-size: 1.15em;
  line-height: 140%;
  border-bottom: 1px solid #bacbdf;
}
legend + p {
  margin: 0.5rem 0 0.75rem 0;
}
fieldset div:not(:last-of-type) {
  margin: 0 0 0.75rem 0;
}
label {
  cursor: pointer;
}
.button-bar {
  margin: 1rem 0 0 0;
}
#forum-install label:not(.for-selectors), .label-like {
  display: block;
  margin: 0 0 0.5rem 0;
}
#forum-install label h3, #forum-install label p, .label-like h3, .label-like p {
  margin: 0;
  font-size: 1em;
}
#forum-install input:not([type="checkbox"]) {
  display: block;
}
#forum-install input[type="checkbox"] {
  margin: 0 0.5rem 0 0;
}
#lang-select ul {
  padding: 0;
  list-style: none;
  border: 1px solid #bacbdf;
}
#lang-select li:not(:last-child) {
  border-bottom: 1px solid #bacbdf;
}
#lang-select input[type="radio"] {
  display: none;
}
#lang-select label {
  display: inline-block;
  width: 100%;
  padding: 0.35rem;
  background: #fff;
  color: #744;
  cursor: pointer;
}
#lang-select input[type="radio"]:checked ~ label {
  background: #aaffc8;
  color: #000;
}
#lang-select input[type="radio"]:checked ~ label::after {
  font-size: 0.8em;
  content: ' ✔';
}
a {
  color: #0000cc;
  text-decoration: none;
}
a:focus, a:hover {
  color: #0000ff;
  text-decoration: underline;
}
a:active {
  color: #ff0000;
}
 </style>
</head>
<body>
 <header>
  <h1>my little forum - Installation</h1>
 </header>
 <main>
<?php
switch($action):
case 'install': ?>
  <h2><?php echo $lang['installation_title']; ?></h2>
  <section>
   <ul>
<?php foreach ($lang['installation_instructions'] as $instruction): ?>
    <li><?php echo $instruction; ?></li>
<?php endforeach; ?>
   </ul>
  </section>
<?php if (isset($errors)): ?>
  <section class="error">
   <h2><?php echo $lang['error_headline']; ?></h2>
   <ul>
<?php foreach($errors as $error): ?>
    <li><?php echo $error; ?></li>
<?php endforeach; ?>
   </ul>
  </section>
<?php endif; ?>
  <section>
   <form action="index.php" method="post" id="forum-install">
    <input type="hidden" name="language_file" value="<?php echo $language_file; ?>">
    <fieldset>
     <legend><?php echo $lang['inst_basic_settings']; ?></legend>
     <p><?php echo $lang['inst_main_settings_desc']; ?></p>
     <div>
      <label for="id-forum-name">
       <h3><?php echo $lang['forum_name']; ?></h3>
       <p><?php echo $lang['forum_name_desc']; ?></p>
      </label>
      <input type="text" id="id-forum-name" name="forum_name" value="<?php if (isset($_POST['forum_name'])) echo $_POST['forum_name']; else echo $default_settings['forum_name']; ?>" size="40">
     </div>
     <div>
      <label for="id-forum-address">
       <h3><?php echo $lang['forum_address']; ?></h3>
       <p><?php echo $lang['forum_address_desc']; ?></p>
      </label>
      <input type="url" id="id-forum-address" name="forum_address" value="<?php if (isset($_POST['forum_address'])) echo $_POST['forum_address']; else { if ($default_settings['forum_address'] != "") echo $default_settings['forum_address']; } ?>" size="40">
     </div>
     <div>
      <label for="id-forum-email">
       <h3><?php echo $lang['forum_email']; ?></h3>
       <p><?php echo $lang['forum_email_desc']; ?></p>
      </label>
      <input type="email" id="id-forum-email" name="forum_email" value="<?php if (isset($_POST['forum_email'])) echo $_POST['forum_email']; else echo "@"; ?>" size="40">
     </div>
    </fieldset>
    <fieldset>
     <legend><?php echo $lang['inst_admin_settings']; ?></legend>
     <p><?php echo $lang['inst_admin_settings_desc']; ?></p>
     <div>
      <label for="id-admin-name">
       <h3><?php echo $lang['inst_admin_name']; ?></h3>
       <p><?php echo $lang['inst_admin_name_desc']; ?></p>
      </label>
      <input type="text" id="id-admin-name" name="admin_name" value="<?php if (isset($_POST['admin_name'])) echo $_POST['admin_name']; ?>" size="40">
     </div>
     <div>
      <label for="id-admin-email">
       <h3><?php echo $lang['inst_admin_email']; ?></h3>
       <p><?php echo $lang['inst_admin_email_desc']; ?></p>
      </label>
      <input type="email" id="id-admin-email" name="admin_email" value="<?php if (isset($_POST['admin_email'])) echo $_POST['admin_email']; else echo "@"; ?>" size="40">
     </div>
     <div>
      <label for="id-admin-pw">
       <h3><?php echo $lang['inst_admin_pw']; ?></h3>
       <p><?php echo $lang['inst_admin_pw_desc']; ?></p>
      </label>
      <input type="password" id="id-admin-pw" name="admin_pw" value="" size="40">
     </div>
     <div>
      <label for="id-admin-pw-conf">
       <h3><?php echo $lang['inst_admin_pw_conf']; ?></h3>
       <p><?php echo $lang['inst_admin_pw_conf_desc']; ?></p>
      </label>
      <input type="password" id="id-admin-pw-conf" name="admin_pw_conf" value="" size="40">
     </div>
    </fieldset>
    <fieldset>
     <legend><?php echo $lang['inst_db_settings']; ?></legend>
     <p><?php echo $lang['inst_db_settings_desc']; ?></p>
     <div>
      <label for="id-db-host">
       <h3><?php echo $lang['inst_db_host']; ?></h3>
       <p><?php echo $lang['inst_db_host_desc']; ?></p>
      </label>
      <input type="text" id="id-db-host" name="host" value="<?php if (isset($_POST['host'])) echo $_POST['host']; else echo $db_settings['host']; ?>" size="40">
     </div>
     <div>
      <label for="id-db-name">
       <h3><?php echo $lang['inst_db_name']; ?></h3>
       <p><?php echo $lang['inst_db_name_desc']; ?></p>
      </label>
      <input type="text" id="id-db-name" name="database" value="<?php if (isset($_POST['database'])) echo $_POST['database']; else echo $db_settings['database']; ?>" size="40">
     </div>
     <div>
      <label for="id-db-user">
       <h3><?php echo $lang['inst_db_user']; ?></h3>
       <p><?php echo $lang['inst_db_user_desc']; ?></p>
      </label>
      <input type="text" id="id-db-user" name="user" value="<?php if (isset($_POST['user'])) echo $_POST['user']; else echo $db_settings['user']; ?>" size="40">
     </div>
     <div>
      <label for="id-db-password">
       <h3><?php echo $lang['inst_db_pw']; ?></h3>
       <p><?php echo $lang['inst_db_pw_desc']; ?></p>
      </label>
      <input type="password" id="id-db-password" name="password" value="<?php /*if(isset($_POST['password'])) echo $_POST['password'];*/ ?>" size="40">
     </div>
     <div>
      <label for="id-table-prefix">
       <h3><?php echo $lang['inst_table_prefix']; ?></h3>
       <p><?php echo $lang['inst_table_prefix_desc']; ?></p>
      </label>
      <input type="text" id="id-table-prefix" name="table_prefix" value="<?php if (isset($_POST['table_prefix'])) echo $_POST['table_prefix']; else echo $default_settings['table_prefix']; ?>" size="40">
     </div>
    </fieldset>
    <fieldset>
     <legend><?php echo $lang['inst_advanced_options']; ?></legend>
     <p><?php echo $lang['inst_advanced_options_desc']; ?></p>
     <div>
      <div class="label-like">
       <h3><?php echo $lang['inst_advanced_database']; ?></h3>
       <p><?php echo $lang['inst_advanced_database_desc']; ?></p>
      </div>
      <input id="create_database" type="checkbox" name="create_database" value="true"<?php if (isset($_POST['create_database'])) echo ' checked'; ?>><label for="create_database" class="for-selectors"><?php echo $lang['create_database']; ?></label>
     </div>
     <div>
      <div class="label-like">
       <h3><?php echo $lang['inst_advanced_conf_file']; ?></h3>
       <p><?php echo $lang['inst_advanced_conf_file_desc']; ?></p>
      </div>
      <input id="dont_overwrite_settings" type="checkbox" name="dont_overwrite_settings" value="true"<?php if (isset($_POST['dont_overwrite_settings'])) echo ' checked'; ?>><label for="dont_overwrite_settings" class="for-selectors"><?php echo $lang['dont_overwrite_settings']; ?></label>
     </div>
    </fieldset>
    <p class="button-bar"><button name="install_submit" value="<?php echo $lang['forum_install_ok']; ?>"><?php echo $lang['forum_install_ok']; ?></button></p>
   </form>
  </section>
<?php
break;
case 'choose_language':
?>
  <section>
   <h2><?php echo $lang['label_choose_language']; ?></h2>
   <form action="index.php" method="post" id="lang-select">
    <ul>
<?php foreach ($language_files as $file): ?>
     <li><input id="id_<?php echo $file['language']; ?>" name="language_file" value="<?php echo $file['file']; ?>" type="radio"><label for="id_<?php echo $file['language']; ?>"><?php echo $file['language']; ?></label></li>
<?php endforeach; ?>
    </ul>
    <p class="button-bar"><button name="submit"><?php echo $lang['submit_button_ok']; ?></button></p>
   </form>
  </section>
<?php
break;
endswitch
?>
 </main>
</body>
</html>
