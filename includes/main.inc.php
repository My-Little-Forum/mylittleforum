<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

// database connection:
$connid = connect_db($db_settings['host'], $db_settings['user'], $db_settings['password'], $db_settings['database']);

// set current timestamp:
list($timestamp) = mysqli_fetch_row(mysqli_query($connid, "SELECT UNIX_TIMESTAMP(NOW())"));
define('TIMESTAMP', $timestamp);

// get settings:
$settings = get_settings();

// CSRF protection token
if (!isset($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = $uniqid = uniqid('', true);
}

// auto login:
if (!isset($_SESSION[$settings['session_prefix'].'user_id']) && isset($_COOKIE[$settings['session_prefix'].'auto_login']) && isset($settings['autologin']) && $settings['autologin'] == 1) {
	include('includes/auto_login.inc.php');
}

// access permission checks for not registered users:
if ($settings['access_permission_checks'] == 1 && !isset($_SESSION[$settings['session_prefix'].'user_id'])) {
	// look if IP or user agent is banned:
	$ip_result = mysqli_query($connid, "SELECT name, list FROM ".$db_settings['banlists_table']." WHERE name = 'ips' OR name = 'user_agents'") or raise_error('database_error', mysqli_error($connid));
	while ($data = mysqli_fetch_array($ip_result)) {
		if ($data['name'] == 'ips') $ips = $data['list'];
		if ($data['name'] == 'user_agents') $user_agents = $data['list'];
	}
	mysqli_free_result($ip_result);
	if (isset($ips) && !empty($ips) && trim($ips) != '') {
		$banned_ips = explode("\n",$ips);
		if (is_ip_banned($_SERVER['REMOTE_ADDR'], $banned_ips)) raise_error('403');
	}
	if (isset($user_agents) && !empty($user_agents) && trim($user_agents) != '') {
		$banned_user_agents = explode("\n", $user_agents);
		if (is_user_agent_banned($_SERVER['HTTP_USER_AGENT'], $banned_user_agents)) raise_error('403');
	}
}

// look if user blocked:
if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
	$block_result = mysqli_query($connid, "SELECT user_lock FROM ".$db_settings['userdata_table']." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id']) ." LIMIT 1") or raise_error('database_error', mysqli_error($connid));
	$data = mysqli_fetch_array($block_result);
	mysqli_free_result($block_result);
	if ($data['user_lock'] == 1) {
		log_out($_SESSION[$settings['session_prefix'].'user_id'], 'account_locked');
	}
}

// set time zone:
if (function_exists('date_default_timezone_set')) {
	if (isset($_SESSION[$settings['session_prefix'].'usersettings']['time_zone']) && $_SESSION[$settings['session_prefix'].'usersettings']['time_zone'] != '') {
		date_default_timezone_set($_SESSION[$settings['session_prefix'].'usersettings']['time_zone']);
		$forum_time_zone = $_SESSION[$settings['session_prefix'].'usersettings']['time_zone'];
		if (isset($_SESSION[$settings['session_prefix'].'usersettings']['time_difference']) && $_SESSION[$settings['session_prefix'].'usersettings']['time_difference'] != 0) {
			if ($_SESSION[$settings['session_prefix'].'usersettings']['time_difference'] > 0) $uds = '+'; else $uds = '-';
			$udm = abs($_SESSION[$settings['session_prefix'].'usersettings']['time_difference']);
			$udh = floor($udm / 60);
			$udmr = $udm - $udh * 60;
			if ($udmr < 10) $udmr = '0'.$udmr;
			$udf = $uds.$udh.':'.$udmr;
			$forum_time_zone = $_SESSION[$settings['session_prefix'].'usersettings']['time_zone'].' '.$udf;
		} else {
			$forum_time_zone = $_SESSION[$settings['session_prefix'].'usersettings']['time_zone'];
		}
	} elseif($settings['time_zone'] != '') {
		date_default_timezone_set($settings['time_zone']);
		$forum_time_zone = $settings['time_zone'];
	} else {
		date_default_timezone_set('UTC');
		$forum_time_zone = 'UTC';
	}
}

// do daily actions:
//daily_actions(TIMESTAMP);

$categories = get_categories();
$category_ids = get_category_ids($categories);
if ($category_ids != false) $category_ids_query = implode(', ', $category_ids);
if (empty($category)) $category = 0;

// user settings:
if (isset($_COOKIE[$settings['session_prefix'].'usersettings'])) {
	$usersettings_cookie = explode('.', $_COOKIE[$settings['session_prefix'].'usersettings']);
}

if (empty($_SESSION[$settings['session_prefix'].'usersettings'])) {
	if (isset($usersettings_cookie[0])) {
		$usersettings['user_view'] = $usersettings_cookie[0] == 1 ? 1 : 0;
	} else {
		$usersettings['user_view'] = $settings['default_view'];
	}
	$usersettings['thread_order'] = isset($usersettings_cookie[1]) && $usersettings_cookie[1] == 1 ? 1 : 0;
	$usersettings['sidebar'] = isset($usersettings_cookie[2]) && $usersettings_cookie[2] == 0 ? 0 : 1;
	if(isset($usersettings_cookie[3])) {
    $usersettings['fold_threads'] = $usersettings_cookie[3] == 1 ? 1 : 0;
	} else {
		$usersettings['fold_threads'] = $settings['fold_threads'];
	}
	$usersettings['thread_display'] = isset($usersettings_cookie[4]) && $usersettings_cookie[4] == 1 ? 1 : 0;
	$usersettings['page'] = 1;
	$usersettings['category'] = 0;
	$_SESSION[$settings['session_prefix'].'usersettings'] = $usersettings;
	setcookie($settings['session_prefix'].'usersettings', $_SESSION[$settings['session_prefix'].'usersettings']['user_view'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_order'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['sidebar'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['fold_threads'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_display'], cookie_options(TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days'])));
}

if (isset($_REQUEST['toggle_sidebar'])) {
	if (empty($_SESSION[$settings['session_prefix'].'usersettings']['sidebar'])) $_SESSION[$settings['session_prefix'].'usersettings']['sidebar'] = 1;
	else $_SESSION[$settings['session_prefix'].'usersettings']['sidebar'] = 0;
	setcookie($settings['session_prefix'].'usersettings', $_SESSION[$settings['session_prefix'].'usersettings']['user_view'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_order'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['sidebar'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['fold_threads'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_display'], cookie_options(TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days'])));
	// update database for registered users:
	if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
		@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, last_logout = last_logout, registered = registered, sidebar = ". intval($_SESSION[$settings['session_prefix'].'usersettings']['sidebar']) ." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id']));
	}

	if (isset($_POST['toggle_sidebar'])) exit; // AJAX request

	if (isset($_GET['category']) && isset($_GET['page']) && isset($_GET['order'])) $q = '?page='.$_GET['page'].'&category='.$_GET['category'].'&order='.$_GET['order']; else $q = '';
	header('location: index.php'.$q);
	exit;
}

if (isset($_GET['thread_order']) && isset($_SESSION[$settings['session_prefix'].'usersettings']['thread_order'])) {
	$page = 1;
	if ($_GET['thread_order'] == 1) $thread_order = 1;
	else $thread_order = 0;
	if ($thread_order != $_SESSION[$settings['session_prefix'].'usersettings']['thread_order']) {
		$_SESSION[$settings['session_prefix'].'usersettings']['page'] = 1;
		$_SESSION[$settings['session_prefix'].'usersettings']['thread_order'] = $thread_order;
		setcookie($settings['session_prefix'].'usersettings', $_SESSION[$settings['session_prefix'].'usersettings']['user_view'].'.'.$thread_order.'.'.$_SESSION[$settings['session_prefix'].'usersettings']['sidebar'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['fold_threads'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_display'], cookie_options(TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days'])));
		if (isset($_SESSION[$settings['session_prefix'].'user_id'])) @mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, last_logout = last_logout, registered = registered, thread_order = ". intval($thread_order) ." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id']));
	}
}
if (isset($_SESSION[$settings['session_prefix'].'usersettings']) && isset($_GET['toggle_view']) && in_array($_GET['toggle_view'], array(0, 1))) {
	$_SESSION[$settings['session_prefix'].'usersettings']['user_view'] = intval($_GET['toggle_view']);
	setcookie($settings['session_prefix'].'usersettings', $_SESSION[$settings['session_prefix'].'usersettings']['user_view'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_order'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['sidebar'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['fold_threads'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_display'], cookie_options(TIMESTAMP+(3600*24*$settings['cookie_validity_days'])));
	// update database for registered users:
	if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
		@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, last_logout = last_logout, registered = registered, user_view = ". intval($_SESSION[$settings['session_prefix'].'usersettings']['user_view']) ." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or die(mysqli_error($connid));
	}
	if (isset($_GET['category']) && isset($_GET['page']) && isset($_GET['order'])) $q = '&page='.$_GET['page'].'&category='.$_GET['category'].'&order='.$_GET['order']; else $q = '';
	header('location: index.php?mode=index'.$q);
	exit;
}
if (isset($_SESSION[$settings['session_prefix'].'usersettings']) && isset($_GET['toggle_thread_display']) && in_array($_GET['toggle_thread_display'], array(0, 1)) && isset($_GET['id'])) {
	$_SESSION[$settings['session_prefix'].'usersettings']['thread_display'] = intval($_GET['toggle_thread_display']);
	setcookie($settings['session_prefix'].'usersettings', $_SESSION[$settings['session_prefix'].'usersettings']['user_view'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_order'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['sidebar'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['fold_threads'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_display'], cookie_options(TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days'])));
	// update database for registered users:
	if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
		@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, last_logout = last_logout, registered = registered, thread_display = ". intval($_SESSION[$settings['session_prefix'].'usersettings']['thread_display']) ." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or die(mysqli_error($connid));
	}
	header('location: index.php?mode=thread&id='.intval($_GET['id']));
	exit;
}

if (isset($_SESSION[$settings['session_prefix'].'usersettings']) && isset($_GET['fold_threads']) && in_array($_GET['fold_threads'], array(0, 1))) {
	$_SESSION[$settings['session_prefix'].'usersettings']['fold_threads'] = intval($_GET['fold_threads']);
	setcookie($settings['session_prefix'].'usersettings', $_SESSION[$settings['session_prefix'].'usersettings']['user_view'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_order'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['sidebar'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['fold_threads'].'.'.$_SESSION[$settings['session_prefix'].'usersettings']['thread_display'], cookie_options(TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days'])));
	// update database for registered users:
	if (isset($_SESSION[$settings['session_prefix'].'user_id'])) {
		@mysqli_query($connid, "UPDATE ".$db_settings['userdata_table']." SET last_login = last_login, last_logout = last_logout, registered = registered, fold_threads = ". intval($_SESSION[$settings['session_prefix'].'usersettings']['fold_threads']) ." WHERE user_id = ". intval($_SESSION[$settings['session_prefix'].'user_id'])) or die(mysqli_error($connid));
	}
	if (isset($_GET['category']) && isset($_GET['page']) && isset($_GET['order'])) $q = '&page='.$_GET['page'].'&category='.$_GET['category'].'&order='.$_GET['order']; else $q = '';
	if (isset($_GET['ajax'])) exit;
	header('Location: index.php?mode=index'.$q);
	exit;
}

if(isset($_GET['refresh'])) {
	if (isset($_SESSION[$settings['session_prefix'].'usersettings']['newtime'])) {
		$_SESSION[$settings['session_prefix'].'usersettings']['newtime'] = TIMESTAMP;
	}
	setcookie($settings['session_prefix'].'last_visit', TIMESTAMP.".".TIMESTAMP, cookie_options(TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days'])));
	setcookie($settings['session_prefix'].'read', '', cookie_options(0));
	header('location: index.php?mode=index');
	exit;
}

if (isset($_GET['show_spam']) && isset($_SESSION[$settings['session_prefix'].'user_id']) && $_SESSION[$settings['session_prefix'].'user_id'] > 0) {
	if (isset($_SESSION[$settings['session_prefix'].'usersettings']['show_spam'])) unset($_SESSION[$settings['session_prefix'].'usersettings']['show_spam']);
	else $_SESSION[$settings['session_prefix'].'usersettings']['show_spam'] = true;
	header('location: index.php?mode=index');
	exit;
}

// determine last visit:
if (empty($_SESSION[$settings['session_prefix'].'user_id']) && $settings['remember_last_visit'] == 1) {
	if (isset($_COOKIE[$settings['session_prefix'].'last_visit'])) {
		$c_last_visit = explode(".", $_COOKIE[$settings['session_prefix'].'last_visit']);
		if (isset($c_last_visit[0])) $c_last_visit[0] = intval(trim($c_last_visit[0])); else $c_last_visit[0] = TIMESTAMP;
		if (isset($c_last_visit[1])) $c_last_visit[1] = intval(trim($c_last_visit[1])); else $c_last_visit[1] = TIMESTAMP;
		if ($c_last_visit[1] < (TIMESTAMP - 600)) {
			$c_last_visit[0] = $c_last_visit[1];
			$c_last_visit[1] = TIMESTAMP;
			setcookie($settings['session_prefix'].'last_visit', $c_last_visit[0].".".$c_last_visit[1], cookie_options(TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days'])));
		}
	}
	else 
		setcookie($settings['session_prefix'].'last_visit', TIMESTAMP.".".TIMESTAMP, cookie_options(TIMESTAMP + (3600 * 24 * $settings['cookie_validity_days'])));
}

if (isset($c_last_visit)) $last_visit = intval($c_last_visit[0]); else $last_visit = TIMESTAMP;

if (isset($_GET['category'])) {
	$category = intval($_GET['category']);
	$_SESSION[$settings['session_prefix'].'usersettings']['category'] = $category;
	$_SESSION[$settings['session_prefix'].'usersettings']['page'] = 1;
}

if (isset($category_ids) && isset($_SESSION[$settings['session_prefix'].'usersettings']['category_selection'])) {
	$category_selection = filter_category_selection($_SESSION[$settings['session_prefix'].'usersettings']['category_selection'], $category_ids);
	if (!empty($category_selection)) $category_selection_query = implode(', ', $category_selection);
}

// count spam:
$count_spam_sql = 
	"SELECT COUNT(*) FROM " . $db_settings['forum_table'] . " AS ft INNER JOIN (SELECT eid FROM " . $db_settings['akismet_rating_table'] . " WHERE " . $db_settings['akismet_rating_table'] . ".spam = 1 UNION SELECT eid FROM " . $db_settings['b8_rating_table'] . " WHERE " . $db_settings['b8_rating_table'] . ".spam = 1) AS spam_list ON spam_list.eid = ft.id";

$count_spam_result = mysqli_query($connid, $count_spam_sql);
list($total_spam) = mysqli_fetch_row($count_spam_result);
mysqli_free_result($count_spam_result);

// show spam?  NOTE: variables are used in several php files, i.e. index.inc.php, thread.inc.php, entry.inc.php
$show_spam = false;
$spam_sql_and = " AND spam_list.id IS NULL";

if ($total_spam > 0 && isset($_SESSION[$settings['session_prefix'].'usersettings']['show_spam'])) {
	$show_spam = true;
	$spam_sql_and = " AND spam_list.id IS NOT NULL";
}

// count postings, threads, users and users online:
$total_threads_postings_body = 
	"SELECT COUNT(DISTINCT ft.id) FROM " . $db_settings['forum_table'] . " AS ft LEFT JOIN (SELECT ".$db_settings['forum_table'].".id, ".$db_settings['forum_table'].".tid FROM ".$db_settings['forum_table']." INNER JOIN " . $db_settings['akismet_rating_table'] . " ON ".$db_settings['forum_table'].".id = " . $db_settings['akismet_rating_table'] . ".eid WHERE " . $db_settings['akismet_rating_table'] . ".spam = 1 UNION SELECT ".$db_settings['forum_table'].".id, ".$db_settings['forum_table'].".tid FROM ".$db_settings['forum_table']." INNER JOIN " . $db_settings['b8_rating_table'] . " ON ".$db_settings['forum_table'].".id = " . $db_settings['b8_rating_table'] . ".eid WHERE " . $db_settings['b8_rating_table'] . ".spam = 1)";
$threads_check_sql = " AND pid = 0";
if ($show_spam) {
	$spam_totals =  " AS spam_list ON spam_list.tid = ft.tid WHERE spam_list.id IS NOT NULL";
} else {
	$spam_totals = " AS spam_list ON spam_list.id = ft.id WHERE spam_list.id IS NULL";
}
if ($categories == false) {
	$total_postings_category = "";
} else {
	// there are categories
	$total_postings_category = " AND category IN (" . $category_ids_query . ")";		
}
$total_threads_sql = $total_threads_postings_body . $spam_totals . $total_postings_category . $threads_check_sql;
$count_result = mysqli_query($connid, $total_threads_sql);
list($total_threads) = mysqli_fetch_row($count_result);
mysqli_free_result($count_result);

$total_postings_sql = $total_threads_postings_body . $spam_totals . $total_postings_category;
$count_result = mysqli_query($connid, $total_postings_sql);
list($total_postings) = mysqli_fetch_row($count_result);
mysqli_free_result($count_result);

$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM " . $db_settings['userdata_table'] . " WHERE activate_code = ''");
list($registered_users) = mysqli_fetch_row($count_result);

if ($settings['count_users_online'] > 0) {
	user_online($settings['count_users_online']);
	$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['useronline_table']." WHERE user_id > 0");
	list($registered_users_online) = mysqli_fetch_row($count_result);
	$count_result = mysqli_query($connid, "SELECT COUNT(*) FROM ".$db_settings['useronline_table']." WHERE user_id = 0");
	list($unregistered_users_online) = mysqli_fetch_row($count_result);
	$total_users_online = $unregistered_users_online + $registered_users_online;
}
mysqli_free_result($count_result);

if (isset($settings['time_difference'])) $time_difference = intval($settings['time_difference']);
else $time_difference = 0;
if (isset($_SESSION[$settings['session_prefix'].'usersettings']['time_difference'])) $time_difference = $_SESSION[$settings['session_prefix'].'usersettings']['time_difference'] + $time_difference;

// page menu:
if (isset($_SESSION[$settings['session_prefix'].'user_id'])) $menu_result = @mysqli_query($connid, "SELECT id, menu_linkname FROM ".$db_settings['pages_table']." WHERE menu_linkname != '' ORDER BY order_id ASC") or raise_error('database_error', mysqli_error($connid));
else $menu_result = @mysqli_query($connid, "SELECT id, menu_linkname FROM ".$db_settings['pages_table']." WHERE menu_linkname != '' AND access = 0 ORDER BY order_id ASC") or raise_error('database_error', mysqli_error($connid));
if (mysqli_num_rows($menu_result) > 0) {
	$i = 0;
	while ($pages_data = mysqli_fetch_array($menu_result)) {
		$menu[$i]['id'] = $pages_data['id'];
		$menu[$i]['linkname'] = $pages_data['menu_linkname'];
		$i++;
	}
}
mysqli_free_result($menu_result);
?>
