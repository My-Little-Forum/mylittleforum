<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

if (isset($_SESSION[$settings['session_prefix'] . 'user_id'])) {
	$tmp_user_id = $_SESSION[$settings['session_prefix'] . 'user_id'];
} else {
	$tmp_user_id = 0;
}

if (isset($_GET['list_spam']) && isset($_SESSION[$settings['session_prefix'] . 'user_type']) && $_SESSION[$settings['session_prefix'] . 'user_type'] > 0) {
	// list spam postings:
	$count_spam_result = mysqli_query($connid, "SELECT COUNT(*) FROM " . $db_settings['forum_table'] . " 
												LEFT JOIN " . $db_settings['akismet_rating_table'] . " ON " . $db_settings['akismet_rating_table'] . ".eid = " . $db_settings['forum_table'] . ".id 
												LEFT JOIN " . $db_settings['b8_rating_table'] . " ON " . $db_settings['b8_rating_table'] . ".eid = " . $db_settings['forum_table'] . ".id 
												WHERE (" . $db_settings['akismet_rating_table'] . ".spam = 1 OR " . $db_settings['b8_rating_table'] . ".spam = 1)");
	list($search_results_count) = mysqli_fetch_row($count_spam_result);
	$total_pages = ceil($search_results_count / $settings['search_results_per_page']);
	if (isset($_GET['page']))
		$page = intval($_GET['page']);
	else
		$page = 1;
	if ($page < 1)
		$page = 1;
	if ($page > $total_pages)
		$page = $total_pages;
	$ul                            = ($page - 1) * $settings['search_results_per_page'];
	// data for browse navigation:
	$page_browse['page']           = $page;
	$page_browse['total_items']    = $search_results_count;
	$page_browse['items_per_page'] = $settings['search_results_per_page'];
	$page_browse['browse_array'][] = 1;
	if ($page > 5)
		$page_browse['browse_array'][] = 0;
	for ($browse = $page - 3; $browse < $page + 4; $browse++) {
		if ($browse > 1 && $browse < $total_pages)
			$page_browse['browse_array'][] = $browse;
	}
	if ($page < $total_pages - 4)
		$page_browse['browse_array'][] = 0;
	if ($total_pages > 1)
		$page_browse['browse_array'][] = $total_pages;
	if ($page < $total_pages)
		$page_browse['next_page'] = $page + 1;
	else
		$page_browse['next_page'] = 0;
	if ($page > 1)
		$page_browse['previous_page'] = $page - 1;
	else
		$page_browse['previous_page'] = 0;
	$smarty->assign('page_browse', $page_browse);
	if ($search_results_count > 0) {
		$result = @mysqli_query($connid, "SELECT id, pid, tid, " . $db_settings['forum_table'] . ".user_id, UNIX_TIMESTAMP(time) AS time, UNIX_TIMESTAMP(time + INTERVAL " . $time_difference . " MINUTE) AS timestamp, UNIX_TIMESTAMP(last_reply) AS last_reply, name, user_name, subject, IF(text='',true,false) AS no_text, category, marked, sticky
			FROM " . $db_settings['forum_table'] . "
			LEFT JOIN " . $db_settings['userdata_table'] .       " ON " . $db_settings['userdata_table'] . ".user_id = " . $db_settings['forum_table'] . ".user_id
			LEFT JOIN " . $db_settings['akismet_rating_table'] . " ON " . $db_settings['akismet_rating_table'] . ".`eid` = `".$db_settings['forum_table']."`.`id` 
			LEFT JOIN " . $db_settings['b8_rating_table'] .      " ON " . $db_settings['b8_rating_table'] . ".`eid` = `".$db_settings['forum_table']."`.`id` 
			WHERE (" . $db_settings['akismet_rating_table'] . ".spam = 1 OR " . $db_settings['b8_rating_table'] . ".spam = 1)
			ORDER BY tid DESC, time ASC LIMIT " . $ul . ", " . $settings['search_results_per_page']) or die(mysqli_error($connid));
		$i = 0;
		while ($row = mysqli_fetch_array($result)) {
			$search_results[$i]['id']  = intval($row['id']);
			$search_results[$i]['pid'] = intval($row['pid']);
			
			if ($row['user_id'] > 0) {
				if (!$row['user_name'])
					$search_results[$i]['name'] = $lang['unknown_user'];
				else
					$search_results[$i]['name'] = htmlspecialchars($row['user_name']);
			} else
				$search_results[$i]['name'] = htmlspecialchars($row['name']);
			$search_results[$i]['subject']       = htmlspecialchars($row['subject']);
			$search_results[$i]['timestamp']     = $row['timestamp'];
			$search_results[$i]['no_text']       = $row['no_text'];
			$search_results[$i]['formated_time'] = format_time($lang['time_format'], $row['timestamp']);
			if (isset($categories[$row["category"]]) && $categories[$row['category']] != '') {
				$search_results[$i]['category']      = $row["category"];
				$search_results[$i]['category_name'] = $categories[$row["category"]];
			}
			$i++;
		}
		mysqli_free_result($result);
	}
	$smarty->assign('search_results_count', $search_results_count);
	if (isset($search_results))
		$smarty->assign('search_results', $search_results);
	$smarty->assign('list_spam', true);
	$smarty->assign('subnav_location', 'subnav_list_spam');
} elseif (isset($_GET['search'])) {
	// regular serach:
	$search = urldecode($_GET['search']);
	if (isset($_GET['p_category']))
		$p_category = intval($_GET['p_category']);
	else
		$p_category = 0;
	if (isset($_GET['method']) && $_GET['method'] == 'tags')
		$method = 'tags';
	elseif (isset($_GET['method']) && $_GET['method'] == 'fulltext_or')
		$method = 'fulltext_or';
	else
		$method = 'fulltext';
	$search = trim($search);
	
	// split search query at spaces, but not between double quotes:
	$help_pattern = '[!/*/~/?]'; // pattern to hide spaces between quotes
	$x_search     = preg_replace_callback("#\"(.+?)\"#is", create_function('$string', 'global $help_pattern; return str_replace(" ",$help_pattern,$string[1]);'), $search);
	
	$x_search_array = explode(' ', my_strtolower($x_search, $lang['charset']));
	foreach ($x_search_array as $item) {
		$search_array[] = mysqli_real_escape_string($connid, str_replace($help_pattern, ' ', $item));
	}
	$search_array = array_filter(array_map('trim', $search_array), function($value) { return $value !== ''; });
	
	// limit to 3 words:
	if (count($search_array) > 3) {
		for ($i = 0; $i < 3; ++$i) {
			$stripped_search_array[] = $search_array[$i];
		}
		$search_array = $stripped_search_array;
	}
	foreach ($search_array as $item) {
		if (my_strpos($item, ' ', 0, CHARSET)) {
			$item = '"' . $item . '"';
		}
		$search_string_array[] = $item;
	}
	$search = implode(' ', $search_string_array);
	
	// search...
	$ham_filter = " (`" . $db_settings['akismet_rating_table'] . "`.`spam` = 0 AND `" . $db_settings['b8_rating_table'] . "`.`spam` = 0) ";
	if ($method == 'fulltext_or') {
		if (isset($p_category) && $p_category != 0)
			$search_string = "category = " . $p_category . " AND " . $ham_filter . " AND CONCAT(LOWER(`subject`), LOWER(`name`), LOWER(`text`), IFNULL(LOWER(`tag`), '')) LIKE '%" . implode("%' OR `category` = " . $p_category . " AND " . $ham_filter . " AND CONCAT(LOWER(`subject`), LOWER(`name`), LOWER(`text`), IFNULL(LOWER(`tag`), '')) LIKE '%", $search_array) . "%'";
		else
			$search_string = $ham_filter . " AND CONCAT(LOWER(`subject`), LOWER(`name`), LOWER(`text`), IFNULL(LOWER(`tag`), '')) LIKE '%" . implode("%' OR " . $ham_filter . " AND CONCAT(LOWER(`subject`), LOWER(`name`), LOWER(`text`), IFNULL(LOWER(`tag`), '')) LIKE '%", $search_array) . "%'";
	} elseif ($method == 'tags') {
		if (isset($p_category) && $p_category != 0)
			$search_string = "(IFNULL(LOWER(`tag`), '') LIKE '%" . implode("%' OR IFNULL(LOWER(`tag`), '') LIKE '%", $search_array) . "%') AND `category` = " . $p_category . " AND " . $ham_filter;
		else
			$search_string = "(IFNULL(LOWER(`tag`), '') LIKE '%" . implode("%' OR IFNULL(LOWER(`tag`), '') LIKE '%", $search_array) . "%') AND " . $ham_filter;
	} else {
		// fulltext
		if (isset($p_category) && $p_category != 0)
			$search_string = "CONCAT(LOWER(`subject`), LOWER(`name`), LOWER(`text`), IFNULL(LOWER(`tag`), '')) LIKE '%" . implode("%' AND CONCAT(LOWER(`subject`), LOWER(`name`), LOWER(`text`), IFNULL(LOWER(`tag`), '')) LIKE '%", $search_array) . "%' AND `category` = " . $p_category . " AND " . $ham_filter;
		else
			$search_string = "CONCAT(LOWER(`subject`), LOWER(`name`), LOWER(`text`), IFNULL(LOWER(`tag`), '')) LIKE '%" . implode("%' AND CONCAT(LOWER(`subject`), LOWER(`name`), LOWER(`text`), IFNULL(LOWER(`tag`), '')) LIKE '%", $search_array) . "%' AND " . $ham_filter;
	}
	
	// count results:
	if ($search != '') {
		$sql = "SELECT COUNT(DISTINCT `" . $db_settings['forum_table'] . "`.`id`) 
				FROM `" . $db_settings['forum_table'] . "`		
				LEFT JOIN `" . $db_settings['entry_tags_table'] .     "` ON `" . $db_settings['entry_tags_table'] . "`.`bid`     = `" . $db_settings['forum_table'] . "`.`id`
				LEFT JOIN `" . $db_settings['tags_table'] .           "` ON `" . $db_settings['entry_tags_table'] . "`.`tid`     = `" . $db_settings['tags_table']  . "`.`id` 
				LEFT JOIN `" . $db_settings['akismet_rating_table'] . "` ON `" . $db_settings['akismet_rating_table'] . "`.`eid` = `" . $db_settings['forum_table'] . "`.`id` 
				LEFT JOIN `" . $db_settings['b8_rating_table'] .      "` ON `" . $db_settings['b8_rating_table'] . "`.`eid`      = `" . $db_settings['forum_table'] . "`.`id` 
				WHERE ";

		if ($categories != false)
			$count_result = @mysqli_query($connid, $sql . $search_string . " AND `category` IN (" . $category_ids_query . ")");
		else
			$count_result = @mysqli_query($connid, $sql . $search_string);

		list($search_results_count) = mysqli_fetch_row($count_result);
	} 
	else
		$search_results_count = 0;
	
	$total_pages = ceil($search_results_count / $settings['search_results_per_page']);
	
	if (isset($_GET['page']))
		$page = intval($_GET['page']);
	else
		$page = 1;
	if ($page < 1)
		$page = 1;
	if ($page > $total_pages)
		$page = $total_pages;
	$ul                            = ($page - 1) * $settings['search_results_per_page'];
	// data for browse navigation:
	$page_browse['page']           = $page;
	$page_browse['total_items']    = $search_results_count;
	$page_browse['items_per_page'] = $settings['search_results_per_page'];
	$page_browse['browse_array'][] = 1;
	if ($page > 5)
		$page_browse['browse_array'][] = 0;
	for ($browse = $page - 3; $browse < $page + 4; $browse++) {
		if ($browse > 1 && $browse < $total_pages)
			$page_browse['browse_array'][] = $browse;
	}
	if ($page < $total_pages - 4)
		$page_browse['browse_array'][] = 0;
	if ($total_pages > 1)
		$page_browse['browse_array'][] = $total_pages;
	if ($page < $total_pages)
		$page_browse['next_page'] = $page + 1;
	else
		$page_browse['next_page'] = 0;
	if ($page > 1)
		$page_browse['previous_page'] = $page - 1;
	else
		$page_browse['previous_page'] = 0;
	$smarty->assign('page_browse', $page_browse);

	if ($search_results_count > 0) {
		if ($categories != false) {
			$result = @mysqli_query($connid, "SELECT DISTINCT ft.id, ft.pid, ft.tid, ft.user_id, UNIX_TIMESTAMP(ft.time) AS time, UNIX_TIMESTAMP(ft.time + INTERVAL " . $time_difference . " MINUTE) AS timestamp, UNIX_TIMESTAMP(last_reply) AS last_reply, name, user_name, subject, IF(text='',true,false) AS no_text, category, marked, sticky, rst.user_id AS req_user
				FROM " . $db_settings['forum_table'] . " AS ft
				LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id = ft.user_id
				LEFT JOIN " . $db_settings['read_status_table'] . " AS rst ON rst.posting_id = ft.id AND rst.user_id = " . intval($tmp_user_id) . "
				LEFT JOIN `" . $db_settings['entry_tags_table'] . "` ON `" . $db_settings['entry_tags_table'] . "`.`bid` = `ft`.`id`
				LEFT JOIN `" . $db_settings['tags_table'] . "` ON `" . $db_settings['entry_tags_table'] . "`.`tid` = `" . $db_settings['tags_table'] . "`.`id`
				LEFT JOIN `" . $db_settings['akismet_rating_table'] . "` ON `" . $db_settings['akismet_rating_table'] . "`.`eid` = `ft`.`id` 
				LEFT JOIN `" . $db_settings['b8_rating_table'] .      "` ON `" . $db_settings['b8_rating_table'] . "`.`eid`      = `ft`.`id` 
				WHERE " . $search_string . " AND category IN (" . $category_ids_query . ")
				ORDER BY tid DESC, time ASC LIMIT " . $ul . ", " . $settings['search_results_per_page']) or die(mysqli_error($connid));
		} else {
			$result = @mysqli_query($connid, "SELECT DISTINCT ft.id, ft.pid, ft.tid, ft.user_id, UNIX_TIMESTAMP(ft.time) AS time, UNIX_TIMESTAMP(ft.time + INTERVAL " . $time_difference . " MINUTE) AS timestamp, UNIX_TIMESTAMP(last_reply) AS last_reply, name, user_name, subject, IF(text='',true,false) AS no_text, category, marked, sticky, rst.user_id AS req_user
				FROM " . $db_settings['forum_table'] . " AS ft
				LEFT JOIN " . $db_settings['userdata_table'] . " ON " . $db_settings['userdata_table'] . ".user_id = ft.user_id
				LEFT JOIN " . $db_settings['read_status_table'] . " AS rst ON rst.posting_id = ft.id AND rst.user_id = " . intval($tmp_user_id) . "
				LEFT JOIN `" . $db_settings['entry_tags_table'] . "` ON `" . $db_settings['entry_tags_table'] . "`.`bid` = `ft`.`id`
				LEFT JOIN `" . $db_settings['tags_table'] . "` ON `" . $db_settings['entry_tags_table'] . "`.`tid` = `" . $db_settings['tags_table'] . "`.`id`
				LEFT JOIN `" . $db_settings['akismet_rating_table'] . "` ON `" . $db_settings['akismet_rating_table'] . "`.`eid` = `ft`.`id` 
				LEFT JOIN `" . $db_settings['b8_rating_table'] .      "` ON `" . $db_settings['b8_rating_table'] . "`.`eid`      = `ft`.`id` 
				WHERE " . $search_string . "
				ORDER BY tid DESC, time ASC LIMIT " . $ul . ", " . $settings['search_results_per_page']) or die(mysqli_error($connid));
		}

		$i = 0;
		while ($row = mysqli_fetch_array($result)) {
			$search_results[$i]['id']  = intval($row['id']);
			$search_results[$i]['pid'] = intval($row['pid']);
			
			if ($row['user_id'] > 0) {
				if (!$row['user_name'])
					$search_results[$i]['name'] = $lang['unknown_user'];
				else
					$search_results[$i]['name'] = htmlspecialchars($row['user_name']);
			} else
				$search_results[$i]['name'] = htmlspecialchars($row['name']);
			
			$search_results[$i]['subject']       = htmlspecialchars($row['subject']);
			$search_results[$i]['timestamp']     = $row['timestamp'];
			$search_results[$i]['no_text']       = $row['no_text'];
			$search_results[$i]['formated_time'] = format_time($lang['time_format'], $row['timestamp']);
			if (isset($categories[$row["category"]]) && $categories[$row['category']] != '') {
				$search_results[$i]['category']      = $row["category"];
				$search_results[$i]['category_name'] = $categories[$row["category"]];
			}
			if ($row['req_user'] !== NULL and is_numeric($row['req_user'])) {
				$search_results[$i]['is_read'] = true;
			} else {
				$search_results[$i]['is_read'] = false;
			}
			$i++;
		}
		mysqli_free_result($result);
	}

	$smarty->assign('search_results_count', $search_results_count);
	if (isset($search_results))
		$smarty->assign('search_results', $search_results);
	
	$smarty->assign('search', htmlspecialchars($_GET['search']));
	$smarty->assign('search_encoded', urlencode($search));
	$smarty->assign('p_category', $p_category);
	$smarty->assign('method', $method);
	$smarty->assign('subnav_location', 'subnav_search');
} else {
	$smarty->assign('p_category', 0);
	$smarty->assign('method', 'fulltext');
}

$smarty->assign('subtemplate', 'search.inc.tpl');
$template = 'main.tpl';
?>
