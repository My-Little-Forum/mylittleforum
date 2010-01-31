<?php
if(!defined('IN_INDEX'))
 {
  header('Location: ../index.php');
  exit;
 }

if(isset($_GET['id']))
 {
  $id = intval($_GET['id']);
  $result = @mysql_query("SELECT id, title, content, access FROM ".$db_settings['pages_table']." WHERE id= ".$id." LIMIT 1", $connid) or raise_error('database_error',mysql_error());
   if(mysql_num_rows($result)>0)
    {
     $data = mysql_fetch_array($result);
     if($data['access']==0||isset($_SESSION[$settings['session_prefix'].'user_id']))
      {
       $page['id'] = intval($data['id']);
       $page['access'] = intval($data['access']);
       $page['title'] = $data['title'];
       $page['content'] = $data['content'];
       $smarty->assign('page',$page);
      }
     else $smarty->assign('no_authorisation',true);
    }
   else $smarty->assign('page_doesnt_exist',true);
   mysql_free_result($result);
 }

if(isset($page))
 {
  $smarty->assign('subnav_location','subnav_page');
  $smarty->assign('subnav_location_var',$page['title']);
 }
else $smarty->assign('subnav_location','subnav_page_error');

$smarty->assign('subtemplate','page.inc.tpl');
$template = 'main.tpl';
?>
