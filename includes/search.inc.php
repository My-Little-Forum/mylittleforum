<?php
if(!defined('IN_INDEX'))
 {
  header('Location: ../index.php');
  exit;
 }

if(isset($_GET['search']))
 {
  $search = urldecode($_GET['search']);
  if(isset($_GET['p_category'])) $p_category = intval($_GET['p_category']);
  else $p_category = 0;
  if(isset($_GET['method']) && $_GET['method']=='tags') $method = 'tags';
  elseif(isset($_GET['method']) && $_GET['method']=='fulltext_or') $method = 'fulltext_or';
  else $method = 'fulltext';
  $search = trim($search);

  // split search query at spaces, but not between double quotes:
  $help_pattern = '[!/*/~/?]'; // pattern to hide spaces between quotes
  #$search = str_replace($help_pattern,$search);
  $x_search = preg_replace_callback("#\"(.+?)\"#is", create_function('$string','global $help_pattern; return str_replace(" ",$help_pattern,$string[1]);'), $search);

  $x_search_array = explode(' ', my_strtolower($x_search, $lang['charset']));
  foreach($x_search_array as $item)
   {
    $search_array[] = mysql_real_escape_string(str_replace($help_pattern,' ',$item));
   }

  // limit to 3 words:
  if(count($search_array)>3)
   {
    for($i=0;$i<3;++$i)
     {
      $stripped_search_array[] = $search_array[$i];
     }
    $search_array = $stripped_search_array;
   }
  foreach($search_array as $item)
   {
    if(my_strpos($item, ' ', 0, CHARSET))
     {
      $item = '"'.$item.'"';
     }
    $serch_string_array[] = $item;
   }
  $search = implode(' ',$serch_string_array);

  // search...
  if($method == 'fulltext_or')
   {
    if(isset($p_category) && $p_category != 0) $search_string = "category=".$p_category." AND spam=0 AND concat(lower(subject), lower(name), lower(text), lower(tags)) LIKE '%".implode("%' OR category=".$p_category." AND spam=0 AND concat(lower(subject), lower(name), lower(text), lower(tags)) LIKE '%",$search_array)."%'";
    else $search_string = "spam=0 AND concat(lower(subject), lower(name), lower(text), lower(tags)) LIKE '%".implode("%' OR spam=0 AND concat(lower(subject), lower(name), lower(text), lower(tags)) LIKE '%",$search_array)."%'";
   }
  elseif ($method == 'tags')
   {
    if(isset($p_category) && $p_category != 0) $search_string = "lower(tags) LIKE '%;".implode(";%' AND lower(tags) LIKE '%",$search_array)."%' AND category=".$p_category." AND spam=0";
    else $search_string = "lower(tags) LIKE '%;".implode(";%' AND lower(tags) LIKE '%;",$search_array).";%' AND spam=0";
   }
  else // fulltext
   {
    if(isset($p_category) && $p_category != 0) $search_string = "concat(lower(subject), lower(name), lower(text), lower(tags)) LIKE '%".implode("%' AND concat(lower(subject), lower(name), lower(text), lower(tags)) LIKE '%",$search_array)."%' AND category=".$p_category." AND spam=0";
    else $search_string = "concat(lower(subject), lower(name), lower(text), lower(tags)) LIKE '%".implode("%' AND concat(lower(subject), lower(name), lower(text), lower(tags)) LIKE '%",$search_array)."%' AND spam=0";
   }

  // count results:
  if($search!='')
   {
    if($categories!=false) $count_result = mysql_query("SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE ".$search_string." AND category IN (".$category_ids_query.")", $connid);
    else $count_result = mysql_query("SELECT COUNT(*) FROM ".$db_settings['forum_table']." WHERE ".$search_string, $connid);
    list($search_results_count) = mysql_fetch_row($count_result);
   }
  else $search_results_count = 0;

     $total_pages = ceil($search_results_count / $settings['search_results_per_page']);
     if(isset($_GET['page'])) $page = intval($_GET['page']); else $page = 1;
     if($page < 1) $page = 1;
     if($page > $total_pages) $page = $total_pages;
     $ul = ($page-1) * $settings['search_results_per_page'];
     // data for browse navigation:
     $page_browse['page'] = $page;
     $page_browse['total_items'] = $search_results_count;
     $page_browse['items_per_page'] = $settings['search_results_per_page'];
     $page_browse['browse_array'][] = 1;
     if($page > 5) $page_browse['browse_array'][] = 0;
     for($browse=$page-3; $browse<$page+4; $browse++)
      {
       if ($browse > 1 && $browse < $total_pages) $page_browse['browse_array'][] = $browse;
      }
     if($page < $total_pages-4) $page_browse['browse_array'][] = 0;
     if($total_pages > 1) $page_browse['browse_array'][] = $total_pages;
     if($page < $total_pages) $page_browse['next_page'] = $page + 1; else $page_browse['next_page'] = 0;
     if($page > 1) $page_browse['previous_page'] = $page - 1; else $page_browse['previous_page'] = 0;
     $smarty->assign('page_browse',$page_browse);

  if($search_results_count>0)
   {
    if($categories!=false)
     {
      $result = @mysql_query("SELECT id, pid, tid, ".$db_settings['forum_table'].".user_id, UNIX_TIMESTAMP(time) AS time, UNIX_TIMESTAMP(time + INTERVAL ".$time_difference." MINUTE) AS timestamp, UNIX_TIMESTAMP(last_reply) AS last_reply, name, user_name, subject, IF(text='',true,false) AS no_text, category, marked, sticky
                              FROM ".$db_settings['forum_table']."
                              LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id=".$db_settings['forum_table'].".user_id
                              WHERE ".$search_string." AND category IN (".$category_ids_query.")
                              ORDER BY tid DESC, time ASC LIMIT ".$ul.", ".$settings['search_results_per_page'], $connid) or die(mysql_error());
     }
    else
     {
      $result = @mysql_query("SELECT id, pid, tid, ".$db_settings['forum_table'].".user_id, UNIX_TIMESTAMP(time) AS time, UNIX_TIMESTAMP(time + INTERVAL ".$time_difference." MINUTE) AS timestamp, UNIX_TIMESTAMP(last_reply) AS last_reply, name, user_name, subject, IF(text='',true,false) AS no_text, category, marked, sticky
                              FROM ".$db_settings['forum_table']."
                              LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id=".$db_settings['forum_table'].".user_id
                              WHERE ".$search_string."
                              ORDER BY tid DESC, time ASC LIMIT ".$ul.", ".$settings['search_results_per_page'], $connid) or die(mysql_error());
     }
    $i=0;
    while($row = mysql_fetch_array($result))
     {
      $search_results[$i]['id'] = intval($row['id']);
      $search_results[$i]['pid'] = intval($row['pid']);

      if($row['user_id']>0)
       {
        if(!$row['user_name']) $search_results[$i]['name'] = $lang['unknown_user'];
        else $search_results[$i]['name'] = htmlspecialchars($row['user_name']);
       }
      else $search_results[$i]['name'] = htmlspecialchars($row['name']);

      $search_results[$i]['subject'] = htmlspecialchars($row['subject']);
      $search_results[$i]['timestamp'] = $row['timestamp'];
      $search_results[$i]['no_text'] = $row['no_text'];
      $search_results[$i]['formated_time'] = format_time($lang['time_format'],$row['timestamp']);
      if(isset($categories[$row["category"]]) && $categories[$row['category']]!='')
       {
        $search_results[$i]['category']=$row["category"];
        $search_results[$i]['category_name']=$categories[$row["category"]];
       }
      $i++;
     }
    mysql_free_result($result);
   }

  $smarty->assign('search_results_count',$search_results_count);
  if(isset($search_results)) $smarty->assign('search_results',$search_results);

  $smarty->assign('search',htmlspecialchars($_GET['search']));
  $smarty->assign('search_encoded',urlencode($search));
  $smarty->assign('p_category',$p_category);
  $smarty->assign('method',$method);
 }
else
 {
  $smarty->assign('p_category',0);
  $smarty->assign('method','fulltext');
 }

$smarty->assign('subnav_location','subnav_search');
$smarty->assign('subtemplate','search.inc.tpl');
$template = 'main.tpl';
?>
