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

$default_settings['forum_name'] = 'my little forum';
$default_settings['forum_address'] = 'http://'.$_SERVER['HTTP_HOST'].substr(rtrim(dirname($_SERVER['PHP_SELF']), '/\\'),0,strrpos(rtrim(dirname($_SERVER['PHP_SELF']), '/\\'),'/')).'/';
$default_settings['table_prefix'] = 'mlf2_';

define('IN_INDEX', TRUE);
include('../config/db_settings.php');
include('../includes/functions.inc.php');

// stripslashes on GPC if get_magic_quotes_gpc is enabled:
if(get_magic_quotes_gpc())
 {
  function stripslashes_deep($value)
   {
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
   }
  $_POST = array_map('stripslashes_deep', $_POST);
  $_GET = array_map('stripslashes_deep', $_GET);
  $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
  $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
 }

function table_exists($table)
 {
  global $connid;
  $result = @mysql_query("SHOW TABLES", $connid);
  while($row = mysql_fetch_array($result))
   {
    if($table==$row[0]) return true;
   }
  return false;
 }

if(isset($_POST['language_file'])) $language_file = $_POST['language_file'];

// try to connect to the database...
if($connid = @mysql_connect($db_settings['host'], $db_settings['user'], $db_settings['password']))
 {
  if(@mysql_select_db($db_settings['database'], $connid))
   {
    @mysql_query('SET NAMES utf8', $connid);
    if(table_exists($db_settings['forum_table']))
     {
      // the forum seems to be installed
      header('Location: ../');
      exit;
     }
   }
 }

// language already selected?
if(empty($language_file))
 {
  // get available languages:
  $handle=opendir('../lang/');
  while ($file = readdir($handle))
   {
    if(strrchr($file, ".")==".lang")
     {
      $file_array[] = $file;
     }
   }
  closedir($handle);
  natcasesort($file_array);
  $i=0;
  foreach($file_array as $file)
   {
    $language_files[$i]['file'] = $file;
    $language_files[$i]['language'] = ucfirst(str_replace(".lang","",$file));
    $language_parts = explode('.', $language_files[$i]['language']);
    if(isset($language_parts[1])) $language_files[$i]['language'] = $language_parts[0].' ('.$language_parts[1].')';
    $i++;
   }

  if(empty($language_files)) die('No language file available.');
  elseif(count($language_files)==1)
   {
    // there's only one language file so take this one:
    $language_file = $language_files[0]['file'];
   }
  else
   {
    // there are several language files so let the user choose one:
    $action = 'choose_language';
   }
 }

// set provisional language file:
if(empty($language_file)) $language_file = 'english.lang';

if(isset($language_file))
{

if(!file_exists('../lang/'.$language_file) && isset($language_files[0]['file'])) $language_file = $language_files[0]['file'];
if(!file_exists('../lang/'.$language_file)) die('Language file not available.');

// quick & dirty method to get the config vars without smarty (prevents
// creation of a compiled template which would only be used once for the
// installation - doesn't get multi-line-strings properly!):
$config_file = file('../lang/'.$language_file);
foreach($config_file as $line)
 {
  $line = trim($line);
  if($line!='' && $line[0]!='[')
   {
    $line_parts = explode('=',$line,2);
    if(isset($line_parts[1]))
     {
      $key = trim($line_parts[0]);
      if(isset($lang[$key]))
       {
        if(is_array($lang[$key]))
         {
          $lang[$key][] = trim($line_parts[1]);
         }
        else
         {
          $lang[$key] = array($lang[$key]);
          $lang[$key][] = trim($line_parts[1]);
         }
       }
      else
       {
        $lang[$key] = trim($line_parts[1]);
       }
     }
   }
 }

}

if(isset($_POST['install_submit']))
 {
  // are all fields filled out?
  foreach ($_POST as $post)
   {
    if(trim($post) == "")
     {
      $errors[] = $lang['error_form_uncomplete'];
      break;
     }
   }

  if(empty($errors))
   {
    if($_POST['admin_pw'] != $_POST['admin_pw_conf']) $errors[] = $lang['error_conf_pw'];
   }

  // try to connect the database with posted access data:
  if(empty($errors))
   {
    $connid = @mysql_connect($_POST['host'], $_POST['user'], $_POST['password']);
    if(!$connid) $errors[] = $lang['error_db_connection']." (MySQL: ".mysql_error().")";
   }

  if(empty($errors))
   {
    if(!file_exists('install.sql')) $errors[] = $lang['error_sql_file_doesnt_exist'];
   }

  // overwrite database settings file:
  if(empty($errors) && empty($_POST['dont_overwrite_settings']))
   {
    clearstatcache();
    $chmod = decoct(fileperms("../config/db_settings.php"));

    $db_settings['host'] = $_POST['host'];
    $db_settings['user'] = $_POST['user'];
    $db_settings['password'] = $_POST['password'];
    $db_settings['database'] = $_POST['database'];
    $db_settings['settings_table'] = $_POST['table_prefix'].'settings';
    $db_settings['forum_table'] = $_POST['table_prefix'].'entries';
    $db_settings['category_table'] = $_POST['table_prefix'].'categories';
    $db_settings['userdata_table'] = $_POST['table_prefix'].'userdata';
    $db_settings['smilies_table'] = $_POST['table_prefix'].'smilies';
    $db_settings['pages_table'] = $_POST['table_prefix'].'pages';
    $db_settings['banlists_table'] = $_POST['table_prefix'].'banlists';
    $db_settings['useronline_table'] = $_POST['table_prefix'].'useronline';
    $db_settings['login_control_table'] = $_POST['table_prefix'].'logincontrol';
    $db_settings['entry_cache_table'] = $_POST['table_prefix'].'entries_cache';
    $db_settings['userdata_cache_table'] = $_POST['table_prefix'].'userdata_cache';

    $db_settings_file = @fopen("../config/db_settings.php", "w") or $errors[] = str_replace("[CHMOD]",$chmod,$lang['error_overwrite_config_file']);
    if(empty($errors))
     {
      flock($db_settings_file, 2);
      fwrite($db_settings_file, "<?php\n");
      fwrite($db_settings_file, "\$db_settings['host'] = '".$db_settings['host']."';\n");
      fwrite($db_settings_file, "\$db_settings['user'] = '".$db_settings['user']."';\n");
      fwrite($db_settings_file, "\$db_settings['password'] = '".$db_settings['password']."';\n");
      fwrite($db_settings_file, "\$db_settings['database'] = '".$db_settings['database']."';\n");
      fwrite($db_settings_file, "\$db_settings['settings_table'] = '".$db_settings['settings_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['forum_table'] = '".$db_settings['forum_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['category_table'] = '".$db_settings['category_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['userdata_table'] = '".$db_settings['userdata_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['smilies_table'] = '".$db_settings['smilies_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['pages_table'] = '".$db_settings['pages_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['banlists_table'] = '".$db_settings['banlists_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['useronline_table'] = '".$db_settings['useronline_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['login_control_table'] = '".$db_settings['login_control_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['entry_cache_table'] = '".$db_settings['entry_cache_table']."';\n");
      fwrite($db_settings_file, "\$db_settings['userdata_cache_table'] = '".$db_settings['userdata_cache_table']."';\n");
      fwrite($db_settings_file, "?".">\n");
      flock($db_settings_file, 3);
      fclose($db_settings_file);
     }
   }

  if(empty($errors) && isset($_POST['create_database']))
   {
    // create database if desired:
    @mysql_query("CREATE DATABASE ".$db_settings['database'], $connid) or $errors[] = $lang['create_db_error']." (MySQL: ".mysql_error($connid).")";
   }

  // select database:
  if(empty($errors))
   {
    @mysql_select_db($db_settings['database'], $connid) or $errors[] = $lang['error_db_inexistent']." (MySQL: ".mysql_error($connid).")";
    @mysql_query('SET NAMES utf8', $connid);
   }

  // run installation sql file:
  if(empty($errors))
   {
    $lines = file('install.sql');
    $cleared_lines = array();
    foreach($lines as $line)
     {
      $line = str_replace(' mlf2_', ' '.$_POST['table_prefix'], $line);
      $line = trim($line);
      if(my_substr($line, -1, my_strlen($line,$lang['charset']), $lang['charset'])==';') $line = my_substr($line,0,-1,$lang['charset']);
      if($line != '' && my_substr($line,0,1,$lang['charset'])!='#') $cleared_lines[] = $line;
     }

    @mysql_query("START TRANSACTION", $connid) or die(mysql_error());
    foreach($cleared_lines as $line)
     {
      if(!@mysql_query($line, $connid))
       {
        $errors[] = $lang['error_sql']." (MySQL: ".mysql_error($connid).")";
        #break;
      }
     }
    @mysql_query("COMMIT", $connid);
   }

   // insert admin in userdata table:
   if(empty($errors))
    {
     $pw_hash = generate_pw_hash($_POST['admin_pw']);
     @mysql_query("UPDATE ".$db_settings['userdata_table']." SET user_name='".mysql_real_escape_string($_POST['admin_name'])."', user_pw = '".mysql_real_escape_string($pw_hash)."', user_email = '".mysql_real_escape_string($_POST['admin_email'])."' WHERE user_id=1", $connid) or $errors[] = $lang['error_create_admin']." (MySQL: ".mysql_error($connid).")";
    }

   // set forum name, address and email address:
   if(empty($errors))
    {
     @mysql_query("UPDATE ".$db_settings['settings_table']." SET value='".mysql_real_escape_string($_POST['forum_name'])."' WHERE name='forum_name' LIMIT 1", $connid) or $errors[] = $lang['error_update_settings']." (MySQL: ".mysql_error($connid).")";
     @mysql_query("UPDATE ".$db_settings['settings_table']." SET value='".mysql_real_escape_string($_POST['forum_address'])."' WHERE name='forum_address' LIMIT 1", $connid) or $errors[] = $lang['error_update_settings']." (MySQL: ".mysql_error($connid).")";
     @mysql_query("UPDATE ".$db_settings['settings_table']." SET value='".mysql_real_escape_string($_POST['forum_email'])."' WHERE name='forum_email' LIMIT 1", $connid) or $errors[] = $lang['error_update_settings']." (MySQL: ".mysql_error($connid).")";
     @mysql_query("UPDATE ".$db_settings['settings_table']." SET value='".mysql_real_escape_string($_POST['language_file'])."' WHERE name='language_file' LIMIT 1", $connid) or $errors[] = $lang['error_update_settings']." (MySQL: ".mysql_error($connid).")";
    }

   if(empty($errors))
    {
     header('Location: ../');
     exit;
    }
 }

if(empty($action)) $action = 'install';

header('Content-Type: text/html; charset='.$lang['charset']);

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang['language']; ?>">
<head>
<title>my little forum - <?php echo $lang['installation_title']; ?></title>
<meta http-equiv="content-type" content="text/html; charset=<?php echo $lang['charset']; ?>" />
<style type="text/css">
<!--
body              { color:#000; background:#fff; margin:0; padding:0; font-family: verdana, arial, sans-serif; font-size:100.1%; }
h1                { font-size:1.25em; }
p,ul              { font-size:0.82em; line-height:1.45em; }
#top              { margin:0; padding:0 20px 0 20px; color:#000000; background:#d2ddea; border-bottom: 1px solid #bacbdf; }
#top h1           { font-size:2.2em; line-height:2em; margin:0; padding:0; color:#000080; }
#content          { padding:20px; }
table.admintab    { border: 1px solid #bacbdf; }
td.admintab-hl    { width: 100%; vertical-align: top; font-family: verdana, arial, sans-serif; font-size: 13px; background:#e1eaf3; }
td.admintab-hl h2 { margin: 3px 0px 3px 0px; font-size: 15px; font-weight: bold; }
td.admintab-hl p  { font-size: 11px; line-height: 16px; margin: 0px 0px 3px 0px; padding: 0px; }
td.admintab-l     { width: 50%; vertical-align: top; font-family: verdana, arial, sans-serif; font-size: 13px; background: #f5f5f5; }
td.admintab-r     { width: 50%; vertical-align: top; font-family: verdana, arial, sans-serif; font-size: 13px; background: #f5f5f5; }
.caution          { color: red; font-weight: bold; }
.small            { font-size: 11px; line-height:16px; }
a:link            { color: #0000cc; text-decoration: none; }
a:visited         { color: #0000cc; text-decoration: none; }
a:hover           { color: #0000ff; text-decoration: underline; }
a:active          { color: #ff0000; text-decoration: none; }

-->
</style>
</head>

<body>

<div id="top">
<h1>my little forum</h1>
</div>
<div id="content"><h1><?php echo $lang['installation_title']; ?></h1><?php
switch($action)
 {
  case 'install':
   ?><ul><?php
   foreach($lang['installation_instructions'] as $instruction)
    {
     ?><li><?php echo $instruction; ?></li><?php
    }
   ?></ul><?php
   if(isset($errors))
    {
     ?><p class="caution" style="margin-top: 10px;"><?php echo $lang['error_headline']; ?><ul><?php foreach($errors as $error) { ?><li><?php echo $error; ?></li><?php } ?></ul></p><p>&nbsp;</p><?php
    }
   ?><form action="index.php" method="post">
       <table class="admintab" border="0" cellpadding="5" cellspacing="1">
       <tr>
       <td class="admintab-hl" colspan="2"><h2><?php echo $lang['inst_basic_settings']; ?></h2><p><?php echo $lang['inst_main_settings_desc']; ?></p></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['forum_name']; ?></b><br /><span class="small"><?php echo $lang['forum_name_desc']; ?></span></td>
       <td class="admintab-r"><input type="text" name="forum_name" value="<?php if (isset($_POST['forum_name'])) echo $_POST['forum_name']; else echo $default_settings['forum_name']; ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['forum_address']; ?></b><br /><span class="small"><?php echo $lang['forum_address_desc']; ?></span></td>
       <td class="admintab-r"><input type="text" name="forum_address" value="<?php if (isset($_POST['forum_address'])) echo $_POST['forum_address']; else { if ($default_settings['forum_address'] != "") echo $default_settings['forum_address']; } ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['forum_email']; ?></b><br /><span class="small"><?php echo $lang['forum_email_desc']; ?></span></td>
       <td class="admintab-r"><input type="text" name="forum_email" value="<?php if (isset($_POST['forum_email'])) echo $_POST['forum_email']; else echo "@"; ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-hl" colspan="2"><h2><?php echo $lang['inst_admin_settings']; ?></h2><p><?php echo $lang['inst_admin_settings_desc']; ?></p></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_admin_name']; ?></b><br /><span class="small"><?php echo $lang['inst_admin_name_desc']; ?></span></td>
       <td class="admintab-r"><input type="text" name="admin_name" value="<?php if (isset($_POST['admin_name'])) echo $_POST['admin_name']; ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_admin_email']; ?></b><br /><span class="small"><?php echo $lang['inst_admin_email_desc']; ?></span></td>
       <td class="admintab-r"><input type="text" name="admin_email" value="<?php if (isset($_POST['admin_email'])) echo $_POST['admin_email']; else echo "@"; ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_admin_pw']; ?></b><br /><span class="small"><?php echo $lang['inst_admin_pw_desc']; ?></span></td>
       <td class="admintab-r"><input type="password" name="admin_pw" value="" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_admin_pw_conf']; ?></b><br /><span class="small"><?php echo $lang['inst_admin_pw_conf_desc']; ?></span></td>
       <td class="admintab-r"><input type="password" name="admin_pw_conf" value="" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-hl" colspan="2"><h2><?php echo $lang['inst_db_settings']; ?></h2><p><?php echo $lang['inst_db_settings_desc']; ?></p></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_db_host']; ?></b><br /><span class="small"><?php echo $lang['inst_db_host_desc']; ?></span></td>
       <td class="admintab-r"><input type="text" name="host" value="<?php if (isset($_POST['host'])) echo $_POST['host']; else echo $db_settings['host']; ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_db_name']; ?></b><br /><span class="small"><?php echo $lang['inst_db_name_desc']; ?></span></td>
       <td class="admintab-r"><input type="text" name="database" value="<?php if (isset($_POST['database'])) echo $_POST['database']; else echo $db_settings['database']; ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_db_user']; ?></b><br /><span class="small"><?php echo $lang['inst_db_user_desc']; ?></span></td>
       <td class="admintab-r"><input type="text" name="user" value="<?php if (isset($_POST['user'])) echo $_POST['user']; else echo $db_settings['user']; ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_db_pw']; ?></b><br /><span class="small"><?php echo $lang['inst_db_pw_desc']; ?></span></td>
       <td class="admintab-r"><input type="password" name="password" value="<?php /*if(isset($_POST['password'])) echo $_POST['password'];*/ ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_table_prefix']; ?></b><br /><span class="small"><?php echo $lang['inst_table_prefix_desc']; ?></span></td>
       <td class="admintab-r"><input type="text" name="table_prefix" value="<?php if (isset($_POST['table_prefix'])) echo $_POST['table_prefix']; else echo $default_settings['table_prefix']; ?>" size="40" /></td>
       </tr>
       <tr>
       <td class="admintab-hl" colspan="2"><h2><?php echo $lang['inst_advanced_options']; ?></h2><p><?php echo $lang['inst_advanced_options_desc']; ?></p></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_advanced_database']; ?></b><br /><span class="small"><?php echo $lang['inst_advanced_database_desc']; ?></span></td>
       <td class="admintab-r"><input id="create_database" type="checkbox" name="create_database" value="true"<?php if (isset($_POST['create_database'])) echo ' checked="checked"'; ?> /> <label for="create_database"><?php echo $lang['create_database']; ?></label></td>
       </tr>
       <tr>
       <td class="admintab-l"><b><?php echo $lang['inst_advanced_conf_file']; ?></b><br /><span class="small"><?php echo $lang['inst_advanced_conf_file_desc']; ?></span></td>
       <td class="admintab-r"><input id="dont_overwrite_settings" type="checkbox" name="dont_overwrite_settings" value="true"<?php if (isset($_POST['dont_overwrite_settings'])) echo ' checked="checked"'; ?> /> <label for="dont_overwrite_settings"><?php echo $lang['dont_overwrite_settings']; ?></label></td>
       </tr>
       <tr>
       <td class="admintab-hl" colspan="2"><input type="submit" name="install_submit" value="<?php echo $lang['forum_install_ok']; ?>" /><input type="hidden" name="language_file" value="<?php echo $language_file; ?>" /></td>
       </tr>
       </table>
       </form><?php
   break;
  case 'choose_language':
   ?><p><label for="language_file"><?php echo $lang['label_choose_language']; ?></label></p>
   <form action="index.php" method="post">
   <p><select id="language_file" name="language_file" size="1"><?php
   foreach($language_files as $file)
    {
     ?><option value="<?php echo $file['file']; ?>"<?php if($language_file==$file['file']) echo " selected=\"selected\""; ?>><?php echo $file['language']; ?></option><?php
    }
   ?></select>
   <input type="submit" value="<?php echo $lang['submit_button_ok']; ?>" /></p>
   </form><?php
   break;
 }
?></div>
</body>
</html>
