<?php
/**
 * my little forum - a simple PHP and MySQL based internet forum that displays 
 * the messages in classical threaded view
 *
 * @author Mark Alexander Hoschek < alex at mylittleforum dot net >
 * @copyright 2006-2011 Mark Alexander Hoschek
 * @version 2.3 (2011-09-01)
 * @link http://mylittleforum.net/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define('IN_INDEX', true);
define('LANG_DIR', 'lang');
define('THEMES_DIR', 'themes');

session_start();

include('config/db_settings.php');
include('includes/functions.inc.php');
include('includes/main.inc.php');

require('modules/smarty/Smarty.class.php');
$smarty = new Smarty;
$smarty->error_reporting = 'E_ALL & ~E_NOTICE';
$smarty->template_dir = THEMES_DIR;
$smarty->assign('THEMES_DIR', THEMES_DIR);
$smarty->compile_dir = 'templates_c';
$smarty->config_dir = LANG_DIR;
$smarty->config_overwrite = false;
$smarty->config_booleanize = false;
if(isset($_SESSION[$settings['session_prefix'].'usersettings']['language']) && file_exists(LANG_DIR.'/'.$_SESSION[$settings['session_prefix'].'usersettings']['language']))
 {
  $language_file = $_SESSION[$settings['session_prefix'].'usersettings']['language'];
 }
else
 {
  $language_file = $settings['language_file'];
 }
$smarty->assign('language_file', $language_file);
$smarty->configLoad($language_file, 'default');
$lang = $smarty->getConfigVars();

define('CHARSET', $lang['charset']);
if($lang['locale_charset']!=$lang['charset']) define('LOCALE_CHARSET', $lang['locale_charset']);
@ini_set('default_charset', $lang['charset']);
setlocale(LC_ALL, $lang['locale']);

$smarty->assign('settings', $settings);

$smarty->assign('forum_time', format_time($lang['time_format'],TIMESTAMP+intval($time_difference)*60));
if(isset($forum_time_zone)) $smarty->assign('forum_time_zone', htmlspecialchars($forum_time_zone));

if(isset($_SESSION[$settings['session_prefix'].'usersettings'])) $smarty->assign('usersettings', $_SESSION[$settings['session_prefix'].'usersettings']);
#$smarty->assign('category', $category);
if(isset($categories))
 {
  $smarty->assign('categories', $categories);
  $smarty->assign('number_of_categories', count($categories)-1);
 }
if(isset($category_selection))
 {
  $smarty->assign('category_selection', true);
 }

$smarty->assign('total_postings', $total_postings);
$smarty->assign('total_spam', $total_spam);
$smarty->assign('total_threads', $total_threads);
$smarty->assign('registered_users', $registered_users);
if(isset($total_users_online))
 {
  $smarty->assign('total_users_online', $total_users_online);
  $smarty->assign('unregistered_users_online', $unregistered_users_online);
  $smarty->assign('registered_users_online', $registered_users_online);
 }

if(isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_SESSION[$settings['session_prefix'].'user_name']))
 {
  $smarty->assign('user_id', $_SESSION[$settings['session_prefix'].'user_id']);
  $smarty->assign('user', htmlspecialchars($_SESSION[$settings['session_prefix'].'user_name']));
  $smarty->assign('user_type', intval($_SESSION[$settings['session_prefix'].'user_type']));
 }
if(isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type']==1) $smarty->assign('mod', true);
if(isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type']==2) $smarty->assign('admin', true);
if(isset($_SESSION[$settings['session_prefix'].'usersettings']['newtime'])) $smarty->assign('newtime', $_SESSION[$settings['session_prefix'].'usersettings']['newtime']);
if(isset($last_visit)) $smarty->assign('last_visit',$last_visit);
if(isset($menu)) $smarty->assign('menu',$menu);
if(isset($read)) $smarty->assign('read',$read);

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';

if($settings['access_for_users_only'] == 1 && empty($_SESSION[$settings['session_prefix'].'user_id']))
 {
  if(empty($mode) || $mode!='account_locked' && $mode!='register' && $mode!='page' && $mode!='js_defaults') $mode = 'login';
 }
if($settings['forum_enabled']!=1 && (empty($_SESSION[$settings['session_prefix'].'user_type']) || $_SESSION[$settings['session_prefix'].'user_type']<2))
 {
  if(empty($mode) || $mode!='disabled' && $mode!='rss' && $mode!='login' && $mode!='js_defaults') $mode = 'disabled';
 }
if(empty($mode) && isset($_REQUEST['id'])) $mode = 'entry';

if(empty($mode))
 {
  // set user settings to default values if index page is requested
  $_SESSION[$settings['session_prefix'].'usersettings']['page']=1;
  if(isset($_SESSION[$settings['session_prefix'].'usersettings']['category_selection'])) $_SESSION[$settings['session_prefix'].'usersettings']['category']=-1;
  else $_SESSION[$settings['session_prefix'].'usersettings']['category']=0;
  $smarty->assign('top', true);
  $mode = 'index';
 }

switch($mode)
 {
  case 'index':
     include('includes/index.inc.php');
     break;
  case 'admin':
     include('includes/admin.inc.php');
     break;
  case 'contact':
     include('includes/contact.inc.php');
     break;
  case 'delete_cookie':
     include('includes/delete_cookie.inc.php');
     break;
  case 'login':
     include('includes/login.inc.php');
     break;
  case 'posting':
     include('includes/posting.inc.php');
     break;
  case 'register':
     include('includes/register.inc.php');
     break;
  case 'rss':
     include('includes/rss.inc.php');
     break;
  case 'search':
     include('includes/search.inc.php');
     break;
  case 'entry':
     include('includes/entry.inc.php');
     break;
  case 'thread':
     include('includes/thread.inc.php');
     break;
  case 'user':
     include('includes/user.inc.php');
     break;
  case 'page':
     include('includes/page.inc.php');
     break;
  case 'js_defaults':
     include('includes/js_defaults.inc.php');
     break;
  case 'upload_image':
     include('includes/upload_image.inc.php');
     break;
  case 'insert_flash':
     include('includes/insert_flash.inc.php');
     break;
  case 'avatar':
     include('includes/avatar.inc.php');
     break;
  case 'account_locked':
     include('includes/account_locked.inc.php');
     break;
  case 'disabled':
     include('includes/disabled.inc.php');
     break;
  default:
     $mode='index';
     include('includes/index.inc.php');
 }

$smarty->assign('mode', $mode);

if(empty($template))
 {
  header('Location: index.php');
  exit;
 }

if($mode=='rss')
 {
  header("Content-Type: text/xml; charset=".$lang['charset']);
 }
else
 {
  if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')===false)
   {
    // do not send cache-control header to Internet Explorer
    // causes problems when toggeling views or folding threads
    header('Cache-Control: public, max-age=300');
   }
  header('Content-Type: text/html; charset='.$lang['charset']);
 }

if(isset($_SESSION[$settings['session_prefix'].'usersettings']['theme']) && $smarty->templateExists($_SESSION[$settings['session_prefix'].'usersettings']['theme'].'/'.$template)) $theme = $_SESSION[$settings['session_prefix'].'usersettings']['theme'];
else $theme = $settings['theme'];
$smarty->assign('theme',$theme);
$smarty->display($theme.'/'.$template);
?>
