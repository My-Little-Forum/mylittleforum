<?php
/******************************************************************************
* my little forum                                                             *
* update file to update from version 2.* to version 2.3                       *
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
$update['version'] = array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8');
$update['new_version'] = '2.3';
#$update['download_url'] = 'http://downloads.sourceforge.net/mylittleforum/my_little_forum_2.2.7.zip';
#$update['message'] = '<p>HTML formated message...</p>';

// changed files (folders followed by a slash like this: folder/):
switch($settings['version'])
 {
  case '2.2.8':
     $update['items'][] = 'includes/';
     $update['items'][] = 'js/';
     $update['items'][] = 'lang/';
     $update['items'][] = 'modules/bad-behavior/';
     $update['items'][] = 'modules/geshi/';
     $update['items'][] = 'modules/smarty/';
     $update['items'][] = 'themes/default/main.tpl';
     $update['items'][] = 'themes/default/subtemplats/admin.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/entry.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index_table.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/thread.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user_profile.inc.tpl';
     $update['items'][] = 'index.php';
     break;
  case '2.2.7':
     $update['items'][] = 'includes/';
     $update['items'][] = 'index.php';
     $update['items'][] = 'js/';
     $update['items'][] = 'lang/';
     $update['items'][] = 'modules/bad-behavior/';
     $update['items'][] = 'modules/geshi/';
     $update['items'][] = 'modules/smarty/';
     $update['items'][] = 'modules/stringparser_bbcode/';
     $update['items'][] = 'themes/default/main.tpl';
     $update['items'][] = 'themes/default/subtemplats/entry.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index_table.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/thread.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user_profile.inc.tpl';     
     $update['items'][] = 'themes/default/upload_image.tpl';
     break;
  case '2.2.6':
     $update['items'][] = 'includes/';     
     $update['items'][] = 'index.php';
     $update['items'][] = 'js/';
     $update['items'][] = 'lang/';
     $update['items'][] = 'modules/bad-behavior/';
     $update['items'][] = 'modules/geshi/';
     $update['items'][] = 'modules/smarty/';   
     $update['items'][] = 'modules/stringparser_bbcode/';
     $update['items'][] = 'themes/default/main.tpl';
     $update['items'][] = 'themes/default/style.css';
     $update['items'][] = 'themes/default/style.min.css';
     $update['items'][] = 'themes/default/subtemplats/entry.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index_table.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/thread.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user_profile.inc.tpl';
     $update['items'][] = 'themes/default/upload_image.tpl';
     break;
  case '2.2.5':
     $update['items'][] = 'includes/';     
     $update['items'][] = 'index.php';
     $update['items'][] = 'js/';
     $update['items'][] = 'lang/';
     $update['items'][] = 'modules/bad-behavior/';
     $update['items'][] = 'modules/geshi/';
     $update['items'][] = 'modules/smarty/';   
     $update['items'][] = 'modules/stringparser_bbcode/';
     $update['items'][] = 'themes/default/main.tpl';
     $update['items'][] = 'themes/default/style.css';
     $update['items'][] = 'themes/default/style.min.css';
     $update['items'][] = 'themes/default/subtemplats/entry.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index_table.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/thread.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user_profile.inc.tpl';
     $update['items'][] = 'themes/default/upload_image.tpl';
     break;
  case '2.2.4':
     $update['items'][] = 'includes/';     
     $update['items'][] = 'index.php';
     $update['items'][] = 'js/';
     $update['items'][] = 'lang/';
     $update['items'][] = 'modules/bad-behavior/';
     $update['items'][] = 'modules/geshi/';
     $update['items'][] = 'modules/smarty/';   
     $update['items'][] = 'modules/stringparser_bbcode/';
     $update['items'][] = 'themes/default/main.tpl';
     $update['items'][] = 'themes/default/style.css';
     $update['items'][] = 'themes/default/style.min.css';
     $update['items'][] = 'themes/default/subtemplats/entry.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index_table.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/thread.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user_profile.inc.tpl';
     $update['items'][] = 'themes/default/upload_image.tpl';
     break;
  case '2.2.3':
     $update['items'][] = 'includes/';     
     $update['items'][] = 'index.php';
     $update['items'][] = 'js/';
     $update['items'][] = 'lang/';
     $update['items'][] = 'modules/bad-behavior/';
     $update['items'][] = 'modules/geshi/';
     $update['items'][] = 'modules/smarty/';   
     $update['items'][] = 'modules/stringparser_bbcode/';
     $update['items'][] = 'themes/default/main.tpl';
     $update['items'][] = 'themes/default/style.css';
     $update['items'][] = 'themes/default/style.min.css';
     $update['items'][] = 'themes/default/subtemplats/entry.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index_table.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/thread.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/user_postings.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user_profile.inc.tpl';
     $update['items'][] = 'themes/default/upload_image.tpl';
     break;
  case '2.2.2':
     $update['items'][] = 'includes/';     
     $update['items'][] = 'index.php';
     $update['items'][] = 'js/';
     $update['items'][] = 'lang/';
     $update['items'][] = 'modules/bad-behavior/';
     $update['items'][] = 'modules/geshi/';
     $update['items'][] = 'modules/smarty/';   
     $update['items'][] = 'modules/stringparser_bbcode/';
     $update['items'][] = 'themes/default/main.tpl';
     $update['items'][] = 'themes/default/style.css';
     $update['items'][] = 'themes/default/style.min.css';
     $update['items'][] = 'themes/default/subtemplats/entry.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index_table.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/thread.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/user_postings.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user_profile.inc.tpl';
     $update['items'][] = 'themes/default/upload_image.tpl';
     break;
  case '2.2.1':
     $update['items'][] = 'includes/';     
     $update['items'][] = 'index.php';
     $update['items'][] = 'js/';
     $update['items'][] = 'lang/';
     $update['items'][] = 'modules/bad-behavior/';
     $update['items'][] = 'modules/geshi/';
     $update['items'][] = 'modules/smarty/';   
     $update['items'][] = 'modules/stringparser_bbcode/';
     $update['items'][] = 'themes/default/main.tpl';
     $update['items'][] = 'themes/default/style.css';
     $update['items'][] = 'themes/default/style.min.css';
     $update['items'][] = 'themes/default/subtemplats/entry.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index_table.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/thread.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/user_postings.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user_profile.inc.tpl';
     $update['items'][] = 'themes/default/upload_image.tpl';
     break;
  case '2.2':
     $update['items'][] = 'includes/';     
     $update['items'][] = 'index.php';
     $update['items'][] = 'js/';
     $update['items'][] = 'lang/';
     $update['items'][] = 'modules/bad-behavior/';
     $update['items'][] = 'modules/geshi/';
     $update['items'][] = 'modules/smarty/';   
     $update['items'][] = 'modules/stringparser_bbcode/';
     $update['items'][] = 'themes/default/main.tpl';
     $update['items'][] = 'themes/default/style.css';
     $update['items'][] = 'themes/default/style.min.css';
     $update['items'][] = 'themes/default/subtemplats/entry.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/index_table.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/posting.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/thread.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/thread_linear.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user.inc.tpl';
     $update['items'][] = 'themes/default/subtemplates/user_postings.inc.tpl';
     $update['items'][] = 'themes/default/subtemplats/user_profile.inc.tpl';
     $update['items'][] = 'themes/default/upload_image.tpl';
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
 }

// check version:
if(!in_array($settings['version'], $update['version']))
 {
  $update['errors'][] = 'Error in line '.__LINE__.': This update file doesn\'t work with the current version.';
 }

// update to 2.0 RC 3:
if(in_array($settings['version'],array('2.0 RC 1','2.0 RC 2')))
 {
  if(empty($update['errors']))
   {
    if(!@mysql_query("UPDATE ".$db_settings['settings_table']." SET value = '10' WHERE name = 'count_users_online'", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
 }

// update to 2.0 RC 4:
if(in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3')))
 {
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('email_subject_maxlength', '100')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('email_text_maxlength', '10000')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
 }

// update to 2.0 RC 5:
if(in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4')))
 {
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('forum_readonly', '0')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('tags', '1')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
 }

// update to 2.1:
if(in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2')))
 {
  if(empty($update['errors']))
   {
    if(!@mysql_query("ALTER TABLE ".$db_settings['userdata_table']." ADD category_selection VARCHAR(255) NULL DEFAULT NULL AFTER registered", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('bbcode_flash', '0')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('flash_default_width', '425')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('flash_default_height', '344')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
 }
if(in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4')))
 {
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('timezone', '')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('cookie_validity_days', '30')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('access_permission_checks', '1')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('daily_actions_time', '3:30')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('next_daily_actions', '0')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  if(empty($update['errors']))
   {
    if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('auto_lock_old_threads', '0')", $connid))
     {
      $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
     }
   }
  // reoder banlists:
  if(empty($update['errors']))
   {
    $banlists_result = mysql_query("SELECT name, list FROM ".$db_settings['banlists_table'], $connid);
    while($data = mysql_fetch_array($banlists_result))
     {
      $list_array = explode(',',trim($data['list']));
      natcasesort($list_array);
      $list = implode("\n",$list_array);
      if(!@mysql_query("UPDATE ".$db_settings['banlists_table']." SET list = '".mysql_real_escape_string($list)."' WHERE name='".mysql_real_escape_string($data['name'])."'", $connid))
       {
        $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
       }
     }
    mysql_free_result($banlists_result);
   }
 }

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7')))
 {
  // strip slashes:
  if(!@mysql_query("UPDATE ".$db_settings['settings_table']." SET value = replace(value,'\\\\','')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("UPDATE ".$db_settings['category_table']." SET category = replace(category,'\\\\',''), description = replace(description,'\\\\','')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("UPDATE ".$db_settings['forum_table']." SET name = replace(name,'\\\\',''), subject = replace(subject,'\\\\',''), location = replace(location,'\\\\',''), text = replace(text,'\\\\',''), tags = replace(tags,'\\\\','')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("UPDATE ".$db_settings['userdata_table']." SET user_real_name = replace(user_real_name,'\\\\',''), user_location = replace(user_location,'\\\\',''), signature = replace(signature,'\\\\',''), profile = replace(profile,'\\\\','')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("UPDATE ".$db_settings['pages_table']." SET title = replace(title,'\\\\',''), content = replace(content,'\\\\',''), menu_linkname = replace(menu_linkname,'\\\\','')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  @mysql_query("TRUNCATE TABLE ".$db_settings['entry_cache_table'], $connid);
  @mysql_query("TRUNCATE TABLE ".$db_settings['userdata_cache_table'], $connid);
 }

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8')))
 {
  if(!@mysql_query("ALTER TABLE ".$db_settings['entry_cache_table']." CHANGE cache_text cache_text MEDIUMTEXT NOT NULL", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
 }

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4')))
 {
  if(!@mysql_query("ALTER TABLE ".$db_settings['userdata_table']." DROP time_difference", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("ALTER TABLE ".$db_settings['userdata_table']." ADD language VARCHAR(255) NOT NULL DEFAULT ''", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("ALTER TABLE ".$db_settings['userdata_table']." ADD time_zone VARCHAR(255) NOT NULL DEFAULT ''", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("ALTER TABLE ".$db_settings['userdata_table']." ADD time_difference smallint(4) default '0'", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("ALTER TABLE ".$db_settings['userdata_table']." ADD theme VARCHAR(255) NOT NULL DEFAULT ''", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("ALTER TABLE ".$db_settings['userdata_table']." ADD entries_read TEXT NOT NULL", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("UPDATE ".$db_settings['settings_table']." SET name = 'theme', value = 'default' WHERE name = 'template'", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("UPDATE ".$db_settings['settings_table']." SET name = 'time_zone' WHERE name = 'timezone'", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("UPDATE ".$db_settings['settings_table']." SET value = '0' WHERE name = 'time_difference'", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('max_read_items', '200')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('delete_ips', '0')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('bbcode_tex', '0')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('last_changes', '0')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
  if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('ajax_preview', '1')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
 }

if(empty($update['errors']) && in_array($settings['version'],array('2.0 RC 1','2.0 RC 2','2.0 RC 3','2.0 RC 4','2.0 RC 5','2.0 RC 6','2.0 RC 7','2.0 RC 8','2.0','2.0.1','2.0.2','2.1 beta 1','2.1 beta 2','2.1 beta 3','2.1 beta 4','2.1 beta 5','2.1 beta 6','2.1 beta 7','2.1 beta 8','2.1','2.1.1','2.1.2','2.1.3','2.1.4','2.2','2.2.1','2.2.2','2.2.3','2.2.4','2.2.5','2.2.6','2.2.7','2.2.8')))
 {
  if(!@mysql_query("INSERT INTO ".$db_settings['settings_table']." VALUES ('akismet_check_registered', '0')", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }  
 }

if(empty($update['errors']))
 {
  if(!@mysql_query("UPDATE ".$db_settings['settings_table']." SET value='".$update['new_version']."' WHERE name = 'version'", $connid))
   {
    $update['errors'][] = 'Database error in line '.__LINE__.': ' . mysql_error();
   }
 }
?>
