<?php
if(!defined('IN_INDEX'))
 {
  header('Location: ../index.php');
  exit;
 }

if($settings['rss_feed'] == 1 && $settings['forum_enabled']==1)
 {
  if(isset($_GET['items']) && $_GET['items']=='thread_starts')
   {
    $query_addition = ' AND pid=0';
    $thread_starts = true;
    $smarty->assign('thread_starts',true);
    if(isset($_GET['category'])) $query_addition .= ' AND category='.intval($_GET['category']);
   }
  elseif(isset($_GET['thread']))
   {
    $query_addition = ' AND tid='.intval($_GET['thread']);
    $smarty->assign('thread',true);
   }
  elseif(isset($_GET['replies']))
   {
    $query_addition = ' AND tid='.intval($_GET['replies']).' AND pid!=0';
    $smarty->assign('replies',true);
   }
  else
   {
    $query_addition = '';
    if(isset($_GET['category'])) $query_addition .= ' AND category='.intval($_GET['category']);
   }
  // database request
  if($categories == false)
   {
    $result = @mysql_query("SELECT id, pid, ".$db_settings['forum_table'].".user_id, UNIX_TIMESTAMP(time + INTERVAL ".$time_difference." MINUTE) AS timestamp, UNIX_TIMESTAMP(time) AS pubdate_timestamp, name, user_name, subject, text, cache_text
                            FROM ".$db_settings['forum_table']."
                            LEFT JOIN ".$db_settings['entry_cache_table']." ON ".$db_settings['entry_cache_table'].".cache_id=id
                            LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id=".$db_settings['forum_table'].".user_id
                            WHERE spam=0".$query_addition."
                            ORDER BY time DESC LIMIT ".$settings['rss_feed_max_items'], $connid) or raise_error('database_error',mysql_error());
    if(!$result) raise_error('database_error',mysql_error());
    }
  else
   {
    $result = @mysql_query("SELECT id, pid, ".$db_settings['forum_table'].".user_id, UNIX_TIMESTAMP(time + INTERVAL ".$time_difference." MINUTE) AS timestamp, UNIX_TIMESTAMP(time) AS pubdate_timestamp, name, user_name, category, subject, text, cache_text
    FROM ".$db_settings['forum_table']."
    LEFT JOIN ".$db_settings['entry_cache_table']." ON ".$db_settings['entry_cache_table'].".cache_id=id
    LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id=".$db_settings['forum_table'].".user_id
    WHERE category IN (".$category_ids_query.") AND spam=0".$query_addition."
    ORDER BY time DESC LIMIT ".$settings['rss_feed_max_items'], $connid) or raise_error('database_error',mysql_error());
   }
  $result_count = mysql_num_rows($result);

  if($result_count > 0)
   {
    $i=0;
    while ($row = mysql_fetch_array($result))
     {
      if($row['pid']!=0) $rss_items[$i]['reply'] = true;
      
      if($row['cache_text']=='') 
       {
        $rss_items[$i]['text'] = html_format($row['text']);
        @mysql_query("DELETE FROM ".$db_settings['entry_cache_table']." WHERE cache_id=".intval($row['id']), $connid);
        @mysql_query("INSERT INTO ".$db_settings['entry_cache_table']." (cache_id, cache_text) VALUES (".intval($row['id']).",'".mysql_real_escape_string($rss_items[$i]['text'])."')", $connid);
       }
      else
       {
        $rss_items[$i]['text'] = $row['cache_text'];
       }
  
      #$text = html_format($row['text']);
      #$rss_items[$i]['text'] = $text;
      
      $rss_items[$i]['title'] = htmlspecialchars(filter_control_characters($row['subject']));

      if($categories!=false && isset($categories[$row['category']]) && $categories[$row['category']]!='') $rss_items[$i]['category'] = $categories[$row['category']];

      if($row['user_id']>0)
       {
        if(!$row['user_name']) $rss_items[$i]['name'] = $lang['unknown_user'];
        else $rss_items[$i]['name'] = htmlspecialchars(filter_control_characters($row['user_name']));
       }
      else $rss_items[$i]['name'] = htmlspecialchars(filter_control_characters($row['name']));

      $rss_items[$i]['link'] = $settings['forum_address']."index.php?id=".$row['id'];
      if(isset($thread_starts)) $rss_items[$i]['commentRss'] = $settings['forum_address']."index.php?mode=rss&amp;replies=".$row['id'];
      $rss_items[$i]['timestamp'] = $row['timestamp'];
      $rss_items[$i]['formated_time'] = format_time($lang['time_format_full'],$row['timestamp']);
      $rss_items[$i]['pubdate'] = gmdate('r', $row['pubdate_timestamp']);
      $i++;
     }
    $smarty->assign("rss_items",$rss_items);
   }
 }
$template = 'rss.tpl';
?>
