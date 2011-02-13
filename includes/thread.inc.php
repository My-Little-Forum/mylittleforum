<?php
if(!defined('IN_INDEX'))
 {
  header('Location: ../index.php');
  exit;
 }

if(isset($_REQUEST['id']))
 {
  $id = intval($_REQUEST['id']);
 }

if(empty($id))
 {
  header('location: index.php');
  exit;
 }

if(isset($_SESSION[$settings['session_prefix'].'usersettings']['thread_display'])) $thread_display = $_SESSION[$settings['session_prefix'].'usersettings']['thread_display'];
else $thread_display = 0;

if(isset($_GET['page'])) $page = intval($_GET['page']);
else $page = 1;

if(isset($_GET['order']) && $_GET['order']=='last_reply') $order = 'last_reply';
else $order = 'time';

// tid, subject and category of starting posting:
  $result=mysql_query("SELECT tid, subject, category FROM ".$db_settings['forum_table']." WHERE id = ".intval($id)." LIMIT 1", $connid) or raise_error('database_error',mysql_error());
  if(mysql_num_rows($result)!=1)
   {
    header('Location: index.php');
    exit;
   }
  else
   {
    $data = mysql_fetch_array($result);
    mysql_free_result($result);
   }

  // category of this posting accessible by user?
  if(is_array($category_ids) && !in_array($data['category'], $category_ids))
   {
    header("location: index.php");
    exit;
   }
  elseif($data['tid'] != $id)
   {
    // it wasn't the id of the thread start
    header('Location: index.php?mode=thread&id='.$data['tid'].'#p'.$id);
    exit;
   }
  else
   {
    $tid = $data['tid'];
    $smarty->assign("tid",$tid);

    if(isset($settings['count_views']) && $settings['count_views'] == 1) @mysql_query("UPDATE ".$db_settings['forum_table']." SET time=time, last_reply=last_reply, edited=edited, views=views+1 WHERE tid=".$id, $connid);

    $smarty->assign('page_title',htmlspecialchars($data['subject']));
    $smarty->assign('category_name',$categories[$data["category"]]);

    // get all postings of thread:
    $result = mysql_query("SELECT id, pid, tid, ".$db_settings['forum_table'].".user_id, UNIX_TIMESTAMP(time + INTERVAL ".intval($time_difference)." MINUTE) AS disp_time,
                           UNIX_TIMESTAMP(time) AS time, UNIX_TIMESTAMP(edited + INTERVAL ".intval($time_difference)." MINUTE) AS e_time,
                           UNIX_TIMESTAMP(edited - INTERVAL ".$settings['edit_delay']." MINUTE) AS edited_diff, edited_by, name, email,
                           subject, hp, location, ip, text, cache_text, tags, show_signature, views, spam, spam_check_status, category, locked, ip,
                           user_name, user_type, user_email, email_contact, user_hp, user_location, signature, cache_signature, edit_key
                           FROM ".$db_settings['forum_table']."
                           LEFT JOIN ".$db_settings['entry_cache_table']." ON ".$db_settings['entry_cache_table'].".cache_id=id
                           LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id=".$db_settings['forum_table'].".user_id
                           LEFT JOIN ".$db_settings['userdata_cache_table']." ON ".$db_settings['userdata_cache_table'].".cache_id=".$db_settings['userdata_table'].".user_id
                           WHERE tid = ".$tid.$display_spam_query_and." ORDER BY time ASC", $connid) or raise_error('database_error',mysql_error());

    if(mysql_num_rows($result) > 0)
     {
      while($data = mysql_fetch_array($result))
      {
       $new_read[] = $data['id'];

      // tags:
      unset($tags_array);
      $tags = $data['tags'];
      if($tags!='')
       {
        $tags_help_array = explode(';',$tags);
        $i=0;
        foreach($tags_help_array as $tag)
         {
          if($tag!='')
           {
            if(my_strpos($tag, ' ', 0, $lang['charset'])) $tag_escaped='"'.$tag.'"';
            else $tag_escaped = $tag;
            $tags_array[$i]['escaped'] = urlencode($tag_escaped);
            $tags_array[$i]['display'] = htmlspecialchars($tag);
            $i++;
           }
         }
        if(isset($tags_array)) $data['tags'] = $tags_array;
       }

      $data['formated_time'] = format_time($lang['time_format_full'],$data['disp_time']);
      
      if(isset($_SESSION[$settings['session_prefix'].'usersettings']['newtime']) && $_SESSION[$settings['session_prefix'].'usersettings']['newtime']<$data['time'] || $last_visit && $data['time'] > $last_visit) $data['new'] = true;
      else $data['new'] = false;
      
      $ago['days'] = floor((TIMESTAMP - $data['time'])/86400);
      $ago['hours'] = floor(((TIMESTAMP - $data['time'])/3600)-($ago['days']*24));
      $ago['minutes'] = floor(((TIMESTAMP - $data['time'])/60)-($ago['hours']*60+$ago['days']*1440));
      if($ago['hours']>12) $ago['days_rounded'] = $ago['days'] + 1;
      else $ago['days_rounded'] = $ago['days'];
      $data['ago'] = $ago;

      if($data['user_id']>0)
       {
        if(!$data['user_name']) $data['name'] = $lang['unknown_user'];
        else $data['name'] = htmlspecialchars($data['user_name']);
       }
      else $data['name'] = htmlspecialchars($data['name']);


      $data['subject'] = htmlspecialchars($data['subject']);

      $authorization = get_edit_authorization($data['id'], $data['user_id'], $data['edit_key'], $data['time'], $data['locked']);
      if($authorization['edit']==true) $data['options']['edit']=true;
      if($authorization['delete']==true) $data['options']['delete']=true;

      if($data['user_id'] > 0)
       {
        $data['email'] = $data['user_email'];
        $data['location'] = $data['user_location'];
        $data['hp'] = $data['user_hp'];
        if($settings['avatars']==2)
         {
          if(file_exists('images/avatars/'.$data['user_id'].'.jpg')) $avatar['image'] = 'images/avatars/'.$data['user_id'].'.jpg';
          elseif(file_exists('images/avatars/'.$data['user_id'].'.png')) $avatar['image'] = 'images/avatars/'.$data['user_id'].'.png';
          elseif(file_exists('images/avatars/'.$data['user_id'].'.gif')) $avatar['image'] = 'images/avatars/'.$data['user_id'].'.gif';
          if(isset($avatar))
           {
            $image_info = getimagesize($avatar['image']);
            $avatar['width'] = $image_info[0];
            $avatar['height'] = $image_info[1];
            $data['avatar'] = $avatar;
            unset($avatar);
           }
         }
       }
      else
       {
        $data["email_contact"]=1;
       }

      if($data['edited_diff'] > 0 && $data["edited_diff"] > $data["time"] && $settings['show_if_edited'] == 1)
       {
        $data['edited'] = true;
        $data['formated_edit_time'] = format_time($lang['time_format_full'],$data['e_time']);
        if($data['user_id'] == $data['edited_by']) $data['edited_by'] = $data['name'];
        else
         {
          $edited_result = @mysql_query("SELECT user_name
                                         FROM ".$db_settings['userdata_table']."
                                         WHERE user_id = ".intval($data['edited_by'])." LIMIT 1", $connid);
          $edited_data = mysql_fetch_array($edited_result);
          @mysql_free_result($edited_result);
          if(!$edited_data['user_name']) $data['edited_by'] = $lang['unknown_user'];
          else $data['edited_by'] = htmlspecialchars($edited_data['user_name']);
         }
       }

      if($data['cache_text']=='')
       {
        // no cached text so parse it and cache it:
        $data['posting'] = html_format($data['text']);
        // make sure not to make a double entry:
        @mysql_query("DELETE FROM ".$db_settings['entry_cache_table']." WHERE cache_id=".intval($data['id']), $connid);
        @mysql_query("INSERT INTO ".$db_settings['entry_cache_table']." (cache_id, cache_text) VALUES (".intval($data['id']).",'".mysql_real_escape_string($data['posting'])."')", $connid);
       }
      else
       {
        $data['posting'] = $data['cache_text'];
       }

      #if(isset($data['signature']) && $data['signature'] != '' && $data["show_signature"]==1) $data['signature'] = signature_format($data['signature']);
      #else unset($data['signature']);

      if(isset($data['signature']) && $data['signature'] != '' && $data["show_signature"]==1)
       {
        // user has a signature and wants it to be displaed in this posting. Check if it's already cached:
        if($data['cache_signature']!='')
        {
         $data['signature'] = $data['cache_signature'];
        }
        else
        {
         $s_result = @mysql_query("SELECT cache_signature FROM ".$db_settings['userdata_cache_table']." WHERE cache_id=".intval($data['user_id'])." LIMIT 1", $connid);
         $s_data = mysql_fetch_array($s_result);
         if($s_data['cache_signature']!='')
          {
           $data['signature'] = $s_data['cache_signature'];
          }
         else
          {
           $data['signature'] = signature_format($data['signature']);
           // cache signature:
           $xxx = mysql_query("SELECT COUNT(*) FROM ".$db_settings['userdata_cache_table']." WHERE cache_id=".intval($data['user_id']), $connid) or die(mysql_error());
           list($row_count) = mysql_fetch_row($xxx);
           #echo 'row count: '.$row_count.' user_id: '.$data['user_id'].'<br />';
           if($row_count==1)
            {
             @mysql_query("UPDATE ".$db_settings['userdata_cache_table']." SET cache_signature='".mysql_real_escape_string($data['signature'])."' WHERE cache_id=".intval($data['user_id']), $connid);
            }
           else
            {
             @mysql_query("DELETE FROM ".$db_settings['userdata_cache_table']." WHERE cache_id=".intval($data['user_id']), $connid);
             @mysql_query("INSERT INTO ".$db_settings['userdata_cache_table']." (cache_id, cache_signature, cache_profile) VALUES (".intval($data['user_id']).",'".mysql_real_escape_string($data['signature'])."','')", $connid);
            }
          }
        }
      }
      else
      {
       unset($data['signature']);
      }
      if(empty($data["email_contact"])) $data["email_contact"]=0;
      if($data['hp']!='')
       {
        $data['hp'] = add_http_if_no_protocol($data['hp']);
       }

      if($data['email']!='' && $data['email_contact']==1) $data['email']=true;
      else $data['email']=false;
      if($data['location'] != '') $data['location']=htmlspecialchars($data['location']);
      if(isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type']>0)
       {
        $data['options']['move']=true;
        $data['options']['lock'] = true;
       }
      if(isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type']>0 && $settings['akismet_key']!='' && $settings['akismet_entry_check']==1 && $data['spam']==0 && $data['spam_check_status']>0) $data['options']['report_spam']=true;
      if(isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type']>0 && $data['spam']==1) $data['options']['flag_ham']=true;

      if($settings['count_views'] == 1)
       {
        $views = $data['views']-1; // this subtracts the first view by the author after posting
        if($views<0) $views=0; // prevents negative number of views
        $data['views'] = $views;
       }
      else $data['views']=0;

      $data_array[$data["id"]] = $data;
      $child_array[$data["pid"]][] =  $data["id"];
     }
    mysql_free_result($result);

    save_read(set_read($new_read));
    }
    else
     {
      header("location: index.php");
      exit;
     }

    $subnav_link = array('mode'=>'index', 'name'=>'thread_entry_back_link', 'title'=>'thread_entry_back_title');
    $smarty->assign('id',$id);
    $smarty->assign('data',$data_array);
    if(isset($child_array)) $smarty->assign('child_array', $child_array);
    $smarty->assign('subnav_link',$subnav_link);
    $smarty->assign('page',$page);
    $smarty->assign('order',$order);
    $smarty->assign('category',$category);
    if($thread_display==0) $smarty->assign('subtemplate','thread.inc.tpl');
    else $smarty->assign('subtemplate','thread_linear.inc.tpl');
    $template = 'main.tpl';
   }
?>
