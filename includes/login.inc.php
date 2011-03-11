<?php
if(!defined('IN_INDEX'))
 {
  header('Location: ../index.php');
  exit;
 }

if(isset($_REQUEST['action'])) $action = $_REQUEST['action'];
if(isset($_POST['pwf_submit'])) $action = 'pw_forgotten_submitted';

// import posted or got username and password:
if(isset($_POST['username']) && trim($_POST['username'])!='') $request_username = $_POST['username'];
elseif(isset($_GET['username']) && trim($_GET['username'])!='') $request_username = $_GET['username'];
if(isset($_POST['userpw']) && trim($_POST['userpw'])!='') $request_userpw = $_POST['userpw'];
elseif(isset($_GET['userpw']) && trim($_GET['userpw'])!='') $request_userpw = $_GET['userpw'];

// look if session is active, if not: login
if(isset($_SESSION[$settings['session_prefix'].'user_id']) && empty($action))
 {
  $action = "logout";
 }
elseif(empty($_SESSION[$settings['session_prefix'].'user_id']) && isset($request_username) && isset($request_userpw))
 {
  $action = "do_login";
 }
elseif(empty($_SESSION[$settings['session_prefix'].'user_id']) && empty($action) && empty($_GET['activate']))
 {
  $action = "login";
 }
elseif(empty($_SESSION[$settings['session_prefix'].'user_id']) && empty($action) && isset($_GET['activate']))
 {
  $action = "activate";
 }

if(isset($_GET['login_message'])) $smarty->assign('login_message',$_GET['login_message']);

// clear failed logins and check if there are failed logins from this ip:
if($settings['temp_block_ip_after_repeated_failed_logins']==1)
 {
  @mysql_query("DELETE FROM ".$db_settings['login_control_table']." WHERE time < (NOW()-INTERVAL 10 MINUTE)", $connid);
  $failed_logins_result = @mysql_query("SELECT logins FROM ".$db_settings['login_control_table']." WHERE ip='".mysql_real_escape_string($_SERVER["REMOTE_ADDR"])."'", $connid);
  if(mysql_num_rows($failed_logins_result)==1)
   {
    $data = mysql_fetch_array($failed_logins_result);
    if($data['logins']>=3) $action = 'ip_temporarily_blocked';
   }
  mysql_free_result($failed_logins_result);
 }

switch ($action)
 {
  case "do_login":
   if(isset($request_username) && isset($request_userpw))
    {
     $result = mysql_query("SELECT user_id, user_name, user_pw, user_type, UNIX_TIMESTAMP(last_login) AS last_login, UNIX_TIMESTAMP(last_logout) AS last_logout, thread_order, user_view, sidebar, fold_threads, thread_display, category_selection, auto_login_code, activate_code, language, time_zone, time_difference, theme, entries_read FROM ".$db_settings['userdata_table']." WHERE lower(user_name) = '".mysql_real_escape_string(my_strtolower($request_username, $lang['charset']))."'", $connid) or raise_error('database_error',mysql_error());
     if (mysql_num_rows($result) == 1)
      {
       $feld = mysql_fetch_array($result);

       if(is_pw_correct($request_userpw,$feld['user_pw']))
        {
         if(trim($feld["activate_code"]) != '')
          {
           header("location: index.php?mode=login&login_message=account_not_activated");
           exit;
          }

         if(isset($_POST['autologin_checked']) && isset($settings['autologin']) && $settings['autologin'] == 1)
          {
           if(strlen($feld['auto_login_code'])!=50)
            {
             $auto_login_code = random_string(50);
            }
           else
            {
             $auto_login_code = $feld['auto_login_code'];
            }
           $auto_login_code_cookie = $auto_login_code . intval($feld['user_id']);
           setcookie($settings['session_prefix'].'auto_login',$auto_login_code_cookie,TIMESTAMP+(3600*24*$settings['cookie_validity_days']));
           $save_auto_login = true;
          }
         else
          {
           setcookie($settings['session_prefix'].'auto_login','',0);
          }
         $user_id = $feld["user_id"];
         $user_name = $feld["user_name"];
         $user_type = $feld["user_type"];
         #$usersettings['view'] = $feld["user_view"];
         $usersettings['newtime'] = $feld['last_logout'];
         $usersettings['user_view'] = $feld['user_view'];
         $usersettings['thread_order'] = $feld['thread_order'];
         $usersettings['sidebar'] = $feld['sidebar'];
         $usersettings['fold_threads'] = $feld['fold_threads'];
         $usersettings['thread_display'] = $feld['thread_display'];
         $usersettings['page'] = 1;
         $usersettings['category'] = 0;
         $usersettings['latest_postings'] = 1;
         if(empty($feld['entries_read'])) $usersettings['read'] = array();
         else $usersettings['read'] = explode(',',$feld['entries_read']);
         if(!is_null($feld['category_selection']))
          {
           $category_selection = explode(',',$feld['category_selection']);
           $usersettings['category_selection'] = $category_selection;
          }
         if($feld['language']!='')
          {
           $languages = get_languages();
           if(isset($languages) && in_array($feld['language'], $languages))
            {
             $usersettings['language'] = $feld['language'];
             $language_update = $feld['language'];
            }
          }
         if(empty($language_update)) $language_update = '';

         if($feld['theme']!='')
          {
           $themes = get_themes();
           if(isset($themes) && in_array($feld['theme'], $themes))
            {
             $usersettings['theme'] = $feld['theme'];
             $theme_update = $feld['theme'];
            }
          }
         if(empty($theme_update)) $theme_update = '';

         if($feld['time_zone']!='')
          {
           if(function_exists('date_default_timezone_set') && $time_zones = get_timezones())
            {
             if(in_array($feld['time_zone'], $time_zones))
              {
               $usersettings['time_zone'] = $feld['time_zone'];
               $time_zone_update = $feld['time_zone'];
              }
            }
          }
         if(empty($time_zone_update)) $time_zone_update = '';

         if(!empty($feld['time_difference'])) $usersettings['time_difference'] = $feld['time_difference'];

         if(isset($read)) $read_before_logged_in = $read; // get read postings from cookie (read before logged in)

         $_SESSION[$settings['session_prefix'].'user_id'] = $user_id;
         $_SESSION[$settings['session_prefix'].'user_name'] = $user_name;
         $_SESSION[$settings['session_prefix'].'user_type'] = $user_type;
         $_SESSION[$settings['session_prefix'].'usersettings'] = $usersettings;

         $read = set_read($usersettings['read']);
         if(isset($read_before_logged_in)) $read = set_read($read_before_logged_in); // get read postings from cookie (read before logged in)
         save_read(false);

         if(isset($save_auto_login))
          {
           @mysql_query("UPDATE ".$db_settings['userdata_table']." SET logins=logins+1, last_login=NOW(), last_logout=NOW(), user_ip='".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', auto_login_code='".mysql_real_escape_string($auto_login_code)."', pwf_code='', language='".mysql_real_escape_string($language_update)."', time_zone='".mysql_real_escape_string($time_zone_update)."', theme='".mysql_real_escape_string($theme_update)."', entries_read='".mysql_real_escape_string(implode(',',$read))."' WHERE user_id=".intval($user_id), $connid);
          }
         else
          {
           @mysql_query("UPDATE ".$db_settings['userdata_table']." SET logins=logins+1, last_login=NOW(), last_logout=NOW(), user_ip='".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', pwf_code='', language='".mysql_real_escape_string($language_update)."', time_zone='".mysql_real_escape_string($time_zone_update)."', theme='".mysql_real_escape_string($theme_update)."', entries_read='".mysql_real_escape_string(implode(',',$read))."' WHERE user_id=".intval($user_id), $connid);
          }

         // auto delete spam:
         if($user_type>0 && $settings['auto_delete_spam']>0) @mysql_query("DELETE FROM ".$db_settings['forum_table']." WHERE time < (NOW() - INTERVAL ".$settings['auto_delete_spam']." HOUR) AND spam=1", $connid);

         if ($db_settings['useronline_table'] != "")
          {
           @mysql_query("DELETE FROM ".$db_settings['useronline_table']." WHERE ip = '".$_SERVER['REMOTE_ADDR']."'", $connid);
          }

         if(isset($_POST['back']) && isset($_POST['id']))
          {
           $redir_qs = '?mode='.$_POST['back'].'&id='.$_POST['id'].'&back=entry';;
          }
         elseif(isset($_POST['back']))
          {
           $redir_qs = '?mode='.$_POST['back'];
          }
         elseif(isset($_POST['id']))
          {
           $redir_qs = '?id='.$_POST['id'].'&back=entry';
          }
         else
          {
           $redir_qs = '';
          }

         header('Location: index.php'.$redir_qs);
         exit;
        }
       else
        {
         if($settings['temp_block_ip_after_repeated_failed_logins']==1) count_failed_logins();
         #header("location: index.php?mode=login&login_message=login_failed");
         #exit;
         $action = 'login';
         $login_message='login_failed';
        }
     }
    else
     {
      if($settings['temp_block_ip_after_repeated_failed_logins']==1) count_failed_logins();
      #header("location: index.php?mode=login&login_message=login_failed");
      #exit;
      $action = 'login';
      $login_message='login_failed';
     }
    }
   else
    {
     if($settings['temp_block_ip_after_repeated_failed_logins']==1) count_failed_logins();
     #header("location: index.php?mode=login&login_message=login_failed");
     #exit;
     $action = 'login';
     $login_message='login_failed';
    }
  break;

  case "logout":
   log_out($_SESSION[$settings['session_prefix'].'user_id']);
   header("location: index.php");
   exit;
  break;

  case "pw_forgotten_submitted":
   if(trim($_POST['pwf_email'])=='') $error=true;
   if(empty($error))
    {
     $pwf_result = @mysql_query("SELECT user_id, user_name, user_email FROM ".$db_settings['userdata_table']." WHERE user_email = '".mysql_real_escape_string($_POST['pwf_email'])."' LIMIT 1", $connid) or raise_error('database_error',mysql_error());
     if(mysql_num_rows($pwf_result)!=1) $error=true;
     else $field = mysql_fetch_array($pwf_result);
     mysql_free_result($pwf_result);
    }
   if(empty($error))
    {
     $pwf_code = random_string(20);
     $pwf_code_hash = generate_pw_hash($pwf_code);
     $update_result = mysql_query("UPDATE ".$db_settings['userdata_table']." SET last_login=last_login, registered=registered, pwf_code='".mysql_real_escape_string($pwf_code_hash)."' WHERE user_id = ".intval($field['user_id'])." LIMIT 1", $connid);
     // send mail with activating link:
     $smarty->configLoad($settings['language_file'], 'emails');
     $lang = $smarty->getConfigVars();
     $lang['pwf_activating_email_txt'] = str_replace("[name]", $field["user_name"], $lang['pwf_activating_email_txt']);
     $lang['pwf_activating_email_txt'] = str_replace("[forum_address]", $settings['forum_address'], $lang['pwf_activating_email_txt']);
     $lang['pwf_activating_email_txt'] = str_replace("[activating_link]", $settings['forum_address'].basename($_SERVER['PHP_SELF'])."?mode=login&activate=".$field["user_id"]."&code=".$pwf_code, $lang['pwf_activating_email_txt']);

     if(my_mail($field["user_email"], $lang['pwf_activating_email_sj'], $lang['pwf_activating_email_txt']))
      {
       header("location: index.php?mode=login&login_message=mail_sent");
       exit;
      }
     else
      {
       header("Location: index.php?mode=login&login_message=mail_error");
       exit;
      }
    }
   header("Location: index.php?mode=login&login_message=pwf_failed");
   exit;
  break;

  case "activate":
  if(isset($_GET['activate']) && trim($_GET['activate']) != "" && isset($_GET['code']) && trim($_GET['code']) != "")
   {
    $pwf_result = mysql_query("SELECT user_id, user_name, user_email, pwf_code FROM ".$db_settings['userdata_table']." WHERE user_id = '".intval($_GET["activate"])."'", $connid);
    if (!$pwf_result) raise_error('database_error',mysql_error());
    $field = mysql_fetch_array($pwf_result);
    mysql_free_result($pwf_result);
    if(trim($field['pwf_code'])!='' && $field['user_id'] == $_GET['activate'] && is_pw_correct($_GET['code'],$field['pwf_code']))
     {
      // generate new password:
      if($settings['min_pw_length']<8) $pwl = 8;
      else $pwl = $settings['min_pw_length'];
      $new_pw = random_string($pwl);
      $pw_hash = generate_pw_hash($new_pw);
      $update_result = mysql_query("UPDATE ".$db_settings['userdata_table']." SET last_login=last_login, registered=registered, user_pw='".mysql_real_escape_string($pw_hash)."', pwf_code='' WHERE user_id='".$field["user_id"]."' LIMIT 1", $connid);

      // send new password:
      $smarty->configLoad($settings['language_file'], 'emails');
      $lang = $smarty->getConfigVars();

      $lang['new_pw_email_txt'] = str_replace("[name]", $field['user_name'], $lang['new_pw_email_txt']);
      $lang['new_pw_email_txt'] = str_replace("[password]", $new_pw, $lang['new_pw_email_txt']);
      $lang['new_pw_email_txt'] = str_replace("[login_link]", $settings['forum_address'].basename($_SERVER['PHP_SELF'])."?mode=login&username=".urlencode($field['user_name'])."&userpw=".$new_pw, $lang['new_pw_email_txt']);
      $lang['new_pw_email_txt'] = $lang['new_pw_email_txt'];

      #$header = "From: ".my_mb_encode_mimeheader($settings['forum_name'], CHARSET, "Q")." <".$settings['forum_email'].">". MAIL_HEADER_SEPARATOR;
      #$header .= "Content-Type: text/plain; charset=" . CHARSET . MAIL_HEADER_SEPARATOR;
      #$header .= "Content-transfer-encoding: 8bit". MAIL_HEADER_SEPARATOR;

      if(my_mail($field['user_email'], $lang['new_pw_email_sj'], $lang['new_pw_email_txt']))
       {
        header("location: index.php?mode=login&login_message=pw_sent");
        exit;
       }
      else
       {
        echo $lang['mail_error'];
        exit;
       }
     }
    else
     {
      header("location: index.php?mode=login&login_message=code_invalid");
      exit;
     }
   }
   else
    {
     header("location: index.php?mode=login&login_message=code_invalid");
     exit;
    }
  break;
 }

$smarty->assign('action',$action);

switch($action)
 {
  case "login":
   $smarty->assign('subnav_location','subnav_login');
   $smarty->assign('subtemplate','login.inc.tpl');
   if(isset($login_message)) $smarty->assign('login_message',$login_message);
   if(isset($_REQUEST['id'])) $smarty->assign('id',intval($_REQUEST['id']));
   if(isset($_REQUEST['back'])) $smarty->assign('back',htmlspecialchars($_REQUEST['back']));
   $template = 'main.tpl';
  break;
  case "pw_forgotten":
   $breadcrumbs[0]['link'] = 'index.php?mode=login';
   $breadcrumbs[0]['linkname'] = 'subnav_login';
   $smarty->assign('breadcrumbs',$breadcrumbs);
   $smarty->assign('subnav_location','subnav_pw_forgotten');
   $smarty->assign('subtemplate','login_pw_forgotten.inc.tpl');
   $template = 'main.tpl';
  break;
  case "ip_temporarily_blocked":
   $smarty->assign('ip_temporarily_blocked',true);
   $smarty->assign('subnav_location','subnav_login');
   $smarty->assign('subtemplate','login.inc.tpl');
   $template = 'main.tpl';
  break;
 }
?>
