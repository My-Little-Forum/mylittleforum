<?php
if(!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
	$tmp_user_id = $_SESSION[$settings['session_prefix'].'user_id'];
} else {
	$tmp_user_id = 0;
}

if (isset($_SESSION[$settings['session_prefix'].'usersettings']['user_view'])) $user_view = $_SESSION[$settings['session_prefix'].'usersettings']['user_view'];
else $user_view = $settings['default_view'];

if (isset($_SESSION[$settings['session_prefix'].'usersettings']['fold_threads'])) $fold_threads = (boolean) $_SESSION[$settings['session_prefix'].'usersettings']['fold_threads'];
else $fold_threads = (boolean) $settings['fold_threads'];

if (isset($_SESSION[$settings['session_prefix'].'usersettings']['thread_order'])) {
	if ($_SESSION[$settings['session_prefix'].'usersettings']['thread_order'] == 0) $thread_order = 0;
	else $thread_order = 1;
} elseif (isset($_GET['thread_order'])) {
	if (isset($_GET['thread_order'])) $thread_order = 0;
	else $thread_order = 1;
}
else $thread_order = 0;

if (isset($_SESSION[$settings['session_prefix'].'usersettings']['category'])) $category = intval($_SESSION[$settings['session_prefix'].'usersettings']['category']);
elseif (isset($_GET['category'])) $category = intval($_GET['category']);
else $category = 0;

if (isset($_GET['page'])) {
	$page = intval($_GET['page']);
	$_SESSION[$settings['session_prefix'].'usersettings']['page']=$page;
} elseif (isset($_SESSION[$settings['session_prefix'].'usersettings']['page'])) {
	$page = intval($_SESSION[$settings['session_prefix'].'usersettings']['page']);
}
if (empty($page)) $page = 1;

if ($thread_order == 0) $db_thread_order = 'ft.time';
else $db_thread_order = 'ft.last_reply';

$_SESSION[$settings['session_prefix'].'usersettings']['current_page'] = $page;

$descasc = "DESC";
$ul = ($page - 1) * $settings['threads_per_page'];

// database request
if ($categories == false) {
	// no categories defined
	$page_threads_and = "";
}
elseif (is_array($categories)) {
	$page_threads_and = "";
	
	if ($category > 0 && !in_array($category, $category_ids)) {  // $category_ids defined in main.inc.php
		// invalid category
		header('Location: index.php');
		exit;
	}
	// there are categories and only one category should be shown
	elseif ($category > 0 && in_array($category, $category_ids)) {
		// how many entries?
		$page_threads_and = " AND (sticky = 2 OR category = '". mysqli_real_escape_string($connid, $category) ."') ";
	}
	// there are categories and all categories or category selection should be shown
	elseif ($category == -1 && isset($category_selection_query)) { // $category_selection_query defined in main.inc.php
		// category selection
		$category_ids_query = $category_selection_query;           // overwrite $category_ids_query, originally defined in main.inc.php
		$page_threads_and = " AND (sticky = 2 OR category IN (". mysqli_real_escape_string($connid, $category_ids_query) .")) ";
	}
	elseif ($category == 0 && isset($category_ids_query)) { // $category_ids_query defined in main.inc.php
		// show all categories (restricted to user type)
		$page_threads_and = " AND (sticky = 2 OR category IN (". mysqli_real_escape_string($connid, $category_ids_query) .")) ";
	}
	
	if (!empty($page_threads_and)) {
		$pid_result_sql = 
			"SELECT COUNT(*) FROM " . $db_settings['forum_table'] . " AS ft
			LEFT JOIN (SELECT eid AS id FROM " . $db_settings['akismet_rating_table'] . " WHERE " . $db_settings['akismet_rating_table'] . ".spam = 1 UNION SELECT eid AS id FROM " . $db_settings['b8_rating_table'] . " WHERE " . $db_settings['b8_rating_table'] . ".spam = 1) AS spam_list ON spam_list.id = ft.id 
			WHERE pid = 0";
		$display_pid_result = $pid_result_sql . $spam_sql_and . $page_threads_and;  // $spam_sql_and defined in main.inc.php
		
		$pid_result = mysqli_query($connid, $display_pid_result);
		list($total_threads) = mysqli_fetch_row($pid_result);
		mysqli_free_result($pid_result);
	}
}

$display_page_threads = 
	"SELECT DISTINCT ft.tid, ft.sticky, ft.time, ft.last_reply FROM ".$db_settings['forum_table']." AS ft 
	LEFT JOIN (SELECT id, tid FROM " . $db_settings['forum_table'] . " INNER JOIN " . $db_settings['akismet_rating_table'] . " ON " . $db_settings['forum_table'] . ".id = " . $db_settings['akismet_rating_table'] . ".eid WHERE " . $db_settings['akismet_rating_table'] . ".spam = 1 UNION SELECT id, tid FROM " . $db_settings['forum_table'] . " INNER JOIN " . $db_settings['b8_rating_table'] . " ON " . $db_settings['forum_table'] . ".id = " . $db_settings['b8_rating_table'] .".eid WHERE " . $db_settings['b8_rating_table'] . ".spam = 1) spam_list ON spam_list.tid = ft.id 
	WHERE ft.pid = 0";  

if ($show_spam) {
	$page_spam = " AND spam_list.id IS NOT NULL";
} else {
	$page_spam = " AND IFNULL(spam_list.id, 0) <> ft.tid";
}

$page_threads_SQL = $display_page_threads . $page_spam . $page_threads_and . " ORDER BY sticky DESC, ". $db_thread_order ." ". $descasc ." LIMIT ". intval($ul) .", ". intval($settings['threads_per_page']);
$result = mysqli_query($connid, $page_threads_SQL) or raise_error('database_error', mysqli_error($connid));

$result_count = @mysqli_num_rows($result);
if ($result_count > 0) {
	while ($zeile = mysqli_fetch_array($result)) {

		if ($show_spam) $thread_spam = "";
		else $thread_spam = " AND spam_list.id IS NULL";

		$thread_result_sql = 
			"SELECT DISTINCT ft.id, ft.pid, ft.tid, ft.user_id, user_type, ft.time AS rawtime, UNIX_TIMESTAMP(ft.time) AS time, UNIX_TIMESTAMP(ft.time + INTERVAL ".intval($time_difference)." MINUTE) AS timestamp, UNIX_TIMESTAMP(last_reply) AS last_reply, name, user_name, subject, IF(text='',true,false) AS no_text, category, views, marked, locked, sticky, rst.user_id AS req_user, " . $db_settings['akismet_rating_table'] . ".spam AS akismet_spam, " . $db_settings['b8_rating_table'] . ".spam AS b8_spam, " . $db_settings['akismet_rating_table'] . ".spam_check_status AS akismet_checked, " . $db_settings['b8_rating_table'] . ".training_type AS b8_checked 
			FROM ".$db_settings['forum_table']." AS ft LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id = ft.user_id LEFT JOIN ".$db_settings['read_status_table']." AS rst ON rst.posting_id = ft.id AND rst.user_id = ". intval($tmp_user_id) ." LEFT JOIN " . $db_settings['akismet_rating_table'] . " ON " . $db_settings['akismet_rating_table'] . ".eid = ft.id LEFT JOIN " . $db_settings['b8_rating_table'] . " ON " . $db_settings['b8_rating_table'] . ".eid = ft.id 
			LEFT JOIN (SELECT ".$db_settings['forum_table'].".id, ".$db_settings['forum_table'].".tid FROM ".$db_settings['forum_table']." INNER JOIN " . $db_settings['akismet_rating_table'] . " ON ".$db_settings['forum_table'].".id = " . $db_settings['akismet_rating_table'] . ".eid WHERE " . $db_settings['akismet_rating_table'] . ".spam = 1 UNION SELECT ".$db_settings['forum_table'].".id, ".$db_settings['forum_table'].".tid FROM ".$db_settings['forum_table']." INNER JOIN " . $db_settings['b8_rating_table'] . " ON ".$db_settings['forum_table'].".id = " . $db_settings['b8_rating_table'] . ".eid WHERE " . $db_settings['b8_rating_table'] . ".spam = 1) AS spam_list ON spam_list.id = ft.id 
			WHERE ft.tid = ".$zeile['tid'] . $thread_spam . " 
			ORDER BY rawtime ASC";
			$thread_result = @mysqli_query($connid, $thread_result_sql) or raise_error('database_error', mysqli_error($connid));

		// put result into arrays:
		while ($data = mysqli_fetch_array($thread_result)) {
			// count replies:
			if (!isset($replies[$data['tid']])) $replies[$data['tid']] = 0;
			else ++$replies[$data['tid']];
			// count number of views of single posting, if option is enabled
			if ($settings['count_views'] != 0) {
				$data['views'] = max($data['views']-1, 0); // this subtracts the first view by the author after posting and prevents negative number of views
				// count total number of views of thread
				if (!isset($total_views[$data['tid']])) $total_views[$data['tid']] = 0;
				$total_views[$data['tid']] += $data['views'];
			}

			if ($data['user_id'] > 0) {
				if (!$data['user_name']) $data['name'] = $lang['unknown_user'];
				else $data['name'] = htmlspecialchars($data['user_name']);
			}
			else 
				$data['name'] = htmlspecialchars($data['name']);

			$data['subject'] = htmlspecialchars($data['subject']);
			if (isset($categories[$data['category']]) && $categories[$data['category']] != '') 
				$data['category_name']=$categories[$data['category']];
			// set read or new status of messages
			$data = getMessageStatus($data, $last_visit, $fold_threads);
			// convert formated time to a utf-8:
			$data['formated_time'] = format_time($lang['time_format'], $data['timestamp']);
			$data['ISO_time'] = format_time('YYYY-MM-dd HH:mm:ss', $data['time']);
			// set key 'not_classified_spam_ham' to decide, if an mod or admin should get notified about need of classification with an icon
			if ((isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type'] >= 1) && (($settings['akismet_entry_check'] == 1 && $data['akismet_checked'] == 0) || ($settings['b8_entry_check'] == 1 && $data['b8_checked'] == 0))) {
			  $data['not_classified_spam_ham'] = 1;
			} else {
			  $data['not_classified_spam_ham'] = 0;
			}
			// flag spam
			$data['spam'] = $data['akismet_spam'] || $data['b8_spam'] ? 1 : 0;
			if ($data['pid'] == 0) $threads[] = $data['id'];
			$data_array[$data['id']] = $data;
			$child_array[$data['pid']][] =  $data['id'];
		}
		mysqli_free_result($thread_result);
	}
	@mysqli_free_result($result);
}

// latest postings:
if ($settings['latest_postings'] > 0) {
	$latest_postings_body_sql = 
		"SELECT ft.id, ft.pid, ft.tid, name, user_name, ft.user_id, UNIX_TIMESTAMP(ft.time) AS time, UNIX_TIMESTAMP(ft.time + INTERVAL ".intval($time_difference)." MINUTE) AS timestamp, UNIX_TIMESTAMP(last_reply) AS last_reply, subject, category, rst.user_id AS req_user, rst.time AS read_time
		FROM ".$db_settings['forum_table']." AS ft LEFT JOIN ".$db_settings['userdata_table']." ON ".$db_settings['userdata_table'].".user_id = ft.user_id LEFT JOIN ".$db_settings['read_status_table']." AS rst ON rst.posting_id = ft.id AND rst.user_id = ". intval($tmp_user_id) ."
		LEFT JOIN (SELECT eid AS id FROM " . $db_settings['akismet_rating_table'] . " WHERE " . $db_settings['akismet_rating_table'] . ".spam = 1 UNION SELECT eid AS id FROM " . $db_settings['b8_rating_table'] . " WHERE " . $db_settings['b8_rating_table'] . ".spam = 1) AS spam_list ON spam_list.id = ft.id WHERE spam_list.id IS NULL";
	$latest_postings_order_by_sql = " ORDER BY ft.time DESC LIMIT " . $settings['latest_postings'];		
	if ($categories == false) {
		$latest_postings_category_sql = "";		
	} else {
		if ($category > 0) {
			$latest_postings_category_sql = " AND category = " . intval($category);		
		} else {
			$latest_postings_category_sql = " AND category IN (". mysqli_real_escape_string($connid, $category_ids_query) .")";		
		}
	}
	$latest_postings_sql = $latest_postings_body_sql . $latest_postings_category_sql . $latest_postings_order_by_sql;
	$latest_postings_result = @mysqli_query($connid, $latest_postings_sql) or raise_error('database_error', mysqli_error($connid));

	if (mysqli_num_rows($latest_postings_result) > 0) {
		$i = 0;
		while ($latest_postings_data = mysqli_fetch_array($latest_postings_result)) {
			$latest_postings[$i]['id'] = intval($latest_postings_data['id']);
			$latest_postings[$i]['tid'] = intval($latest_postings_data['tid']);
			$latest_postings[$i]['pid'] = intval($latest_postings_data['pid']);
			$latest_postings[$i]['subject'] = htmlspecialchars($latest_postings_data['subject']);

			if ($latest_postings_data['user_id'] > 0) {
				if (!$latest_postings_data['user_name']) $latest_postings[$i]['name'] = $lang['unknown_user'];
				else $latest_postings[$i]['name'] = htmlspecialchars($latest_postings_data['user_name']);
			} else {
				$latest_postings[$i]['name'] = htmlspecialchars($latest_postings_data['name']);
			}
			if ($latest_postings_data['req_user'] !== NULL and is_numeric($latest_postings_data['req_user'])) {
				$latest_postings[$i]['is_read'] = true;
			} else {
				$latest_postings[$i]['is_read'] = false;
			}

			$latest_postings[$i]['timestamp'] = $latest_postings_data['timestamp'];
			$latest_postings[$i]['formated_time'] = format_time($lang['time_format'], $latest_postings_data['timestamp']);
			if (isset($categories[$latest_postings_data['category']]) && $categories[$latest_postings_data['category']] != '') $latest_postings[$i]['category_name'] = $categories[$latest_postings_data['category']];

			$ago['days'] = floor((TIMESTAMP - $latest_postings_data['time']) / 86400);
			$ago['hours'] = floor(((TIMESTAMP - $latest_postings_data['time']) / 3600) - ($ago['days'] * 24));
			$ago['minutes'] = floor(((TIMESTAMP - $latest_postings_data['time']) / 60) - ($ago['hours'] * 60 + $ago['days'] * 1440));
			if ($ago['hours'] > 12) $ago['days_rounded'] = $ago['days'] + 1;
			else $ago['days_rounded'] = $ago['days'];
			$latest_postings[$i]['ago'] = $ago;
			$i++;
		}
		$smarty->assign('latest_postings',$latest_postings);
	}
	mysqli_free_result($latest_postings_result);
}

// Check for unlock users
if (isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_SESSION[$settings['session_prefix'].'user_type']) && $_SESSION[$settings['session_prefix'].'user_type'] >= 1) {
	// Pruefe, ob es registrierte aber bisher nicht freigeschaltete Accounts gibt (Anz. der Logins == 0 && Lock-Status == TRUE)
	$unlocked_user_result = mysqli_query($connid, "SELECT count(*) AS 'non_activated_users' FROM ".$db_settings['userdata_table']." WHERE logins = 0 AND user_lock = 1 AND activate_code = '' AND (SELECT TRUE FROM ".$db_settings['settings_table']." WHERE name = 'register_mode' AND value = 1) = TRUE") or raise_error('database_error', mysqli_error($connid));
	if (mysqli_num_rows($unlocked_user_result) > 0 && $row = mysqli_fetch_assoc($unlocked_user_result)) {
		$smarty->assign('number_of_non_activated_users', intval($row['non_activated_users']));
	}
	mysqli_free_result($unlocked_user_result);
}

// tag cloud:
if ($settings['tag_cloud'] == 1) $smarty->assign("tag_cloud", tag_cloud($settings['tag_cloud_day_period'], $settings['tag_cloud_scale_min'], $settings['tag_cloud_scale_max']));

$page_count = ceil($total_threads / $settings['threads_per_page']);

$subnav_link = array('mode'=>'posting', 'title'=>'new_topic_link_title', 'name'=>'new_topic_link');

if (isset($data_array)) $smarty->assign('data', $data_array);
if (isset($threads)) {
	$smarty->assign("threads", $threads);
	$smarty->assign('replies', $replies);
	if (isset($total_views))
		$smarty->assign('total_views', $total_views);
}
if(isset($child_array)) $smarty->assign("child_array",$child_array);

$smarty->assign("page",$page);
$smarty->assign("category",$category);
$smarty->assign("page_count",$page_count);
$smarty->assign('pagination_top', pagination($page_count,$page,$settings['page_browse_range'],$settings['page_browse_show_last']));
$smarty->assign('pagination', pagination($page_count,$page,3));
$smarty->assign("item_count",$total_threads);
$smarty->assign("items_per_page",$settings['threads_per_page']);
$smarty->assign("thread_order",$thread_order);
$smarty->assign("descasc",$descasc);
$smarty->assign('fold_threads',$fold_threads);

if ($category != 0) $cqsa = '&amp;category='.$category;
else $cqsa = '';
if ($page > 1) {
	$smarty->assign('link_rel_first', 'index.php?mode=index&amp;page=1'.$cqsa);
	$smarty->assign('link_rel_prev', 'index.php?mode=index&amp;page='.($page-1).$cqsa);
}
if ($page < $page_count) {
	$smarty->assign('link_rel_next', 'index.php?mode=index&amp;page='.($page+1).$cqsa);
	$smarty->assign('link_rel_last', 'index.php?mode=index&amp;page='.$page_count.$cqsa);
}

// check if SPAM exists and show a link to switch between HAM and SPAM threads
if ($total_spam > 0) {
	if (!isset($_SESSION[$settings['session_prefix'].'usersettings']['show_spam'])) 
		$smarty->assign('show_spam_link', true);
	else //if(isset($_SESSION[$settings['session_prefix'].'usersettings']['show_spam'])) 
		$smarty->assign('hide_spam_link',true);
	$smarty->assign('delete_spam_link', true);
}
// if no SPAM exists but the option to show SPAM threads is enabled, remove this option
elseif(isset($_SESSION[$settings['session_prefix'].'usersettings']['show_spam'])) {
	unset($_SESSION[$settings['session_prefix'].'usersettings']['show_spam']);
}

$smarty->assign("subnav_link", $subnav_link);
if ($user_view == 1) $smarty->assign('subtemplate', 'index_table.inc.tpl');
else $smarty->assign('subtemplate', 'index.inc.tpl');
$template = 'main.tpl';
?>
