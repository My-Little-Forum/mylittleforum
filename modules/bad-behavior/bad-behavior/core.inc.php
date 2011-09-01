<?php if (!defined('BB2_CWD')) die("I said no cheating!");

// Bad Behavior entry point is start_bad_behavior().
// If you're reading this, you are probably lost.
// Go read the bad-behavior-generic.php file.

define('BB2_CORE', dirname(__FILE__));
define('BB2_COOKIE', 'bb2_screener_');

require_once(BB2_CORE . "/functions.inc.php");

// Our log table structure
function bb2_table_structure($name)
{
	// It's not paranoia if they really are out to get you.
	$name_escaped = bb2_db_escape($name);
	return "CREATE TABLE IF NOT EXISTS `$name_escaped` (
		`id` INT(11) NOT NULL auto_increment,
		`ip` TEXT NOT NULL,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`request_method` TEXT NOT NULL,
		`request_uri` TEXT NOT NULL,
		`server_protocol` TEXT NOT NULL,
		`http_headers` TEXT NOT NULL,
		`user_agent` TEXT NOT NULL,
		`request_entity` TEXT NOT NULL,
		`key` TEXT NOT NULL,
		INDEX (`ip`(15)),
		INDEX (`user_agent`(10)),
		PRIMARY KEY (`id`) );";	// TODO: INDEX might need tuning
}

// Insert a new record
function bb2_insert($settings, $package, $key)
{
	$ip = bb2_db_escape($package['ip']);
	$date = bb2_db_date();
	$request_method = bb2_db_escape($package['request_method']);
	$request_uri = bb2_db_escape($package['request_uri']);
	$server_protocol = bb2_db_escape($package['server_protocol']);
	$user_agent = bb2_db_escape($package['user_agent']);
	$headers = "$request_method $request_uri $server_protocol\n";
	foreach ($package['headers'] as $h => $v) {
		$headers .= bb2_db_escape("$h: $v\n");
	}
	$request_entity = "";
	if (!strcasecmp($request_method, "POST")) {
		foreach ($package['request_entity'] as $h => $v) {
			$request_entity .= bb2_db_escape("$h: $v\n");
		}
	}
	return "INSERT INTO `" . bb2_db_escape($settings['log_table']) . "`
		(`ip`, `date`, `request_method`, `request_uri`, `server_protocol`, `http_headers`, `user_agent`, `request_entity`, `key`) VALUES
		('$ip', '$date', '$request_method', '$request_uri', '$server_protocol', '$headers', '$user_agent', '$request_entity', '$key')";
}

// Kill 'em all!
function bb2_banned($settings, $package, $key, $previous_key=false)
{
	// Some spambots hit too hard. Slow them down a bit.
	sleep(2);

	require_once(BB2_CORE . "/banned.inc.php");
	bb2_display_denial($settings, $key, $previous_key);
	bb2_log_denial($settings, $package, $key, $previous_key);
	if (is_callable('bb2_banned_callback')) {
		bb2_banned_callback($settings, $package, $key);
	}
	// Penalize the spammers some more
	require_once(BB2_CORE . "/housekeeping.inc.php");
	bb2_housekeeping($settings, $package);
	die();
}

function bb2_approved($settings, $package)
{
	// Dirk wanted this
	if (is_callable('bb2_approved_callback')) {
		bb2_approved_callback($settings, $package);
	}

	// Decide what to log on approved requests.
	if (($settings['verbose'] && $settings['logging']) || empty($package['user_agent'])) {
		bb2_db_query(bb2_insert($settings, $package, "00000000"));
	}
}

// Check the results of a particular test; see below for usage
// Returns FALSE if test passed (yes this is backwards)
function bb2_test($settings, $package, $result)
{
	// Passthrough a value of 1 for whitelisted/bypass items
	if ($result == 1) {
		return true;
	}
	if ($result !== FALSE) {
		bb2_banned($settings, $package, $result);
		return TRUE;
	}
	return FALSE;
}


// Let God sort 'em out!
function bb2_start($settings)
{
	// Gather up all the information we need, first of all.
	$headers = bb2_load_headers();
	// Postprocess the headers to mixed-case
	// FIXME: get the world to stop using PHP as CGI
	$headers_mixed = array();
	foreach ($headers as $h => $v) {
		$headers_mixed[uc_all($h)] = $v;
	}

	// IPv6 - IPv4 compatibility mode hack
	$_SERVER['REMOTE_ADDR'] = preg_replace("/^::ffff:/", "", $_SERVER['REMOTE_ADDR']);
	// We use these frequently. Keep a copy close at hand.
	$ip = $_SERVER['REMOTE_ADDR'];
	$request_method = $_SERVER['REQUEST_METHOD'];
	$request_uri = $_SERVER['REQUEST_URI'];
	if (!$request_uri) $request_uri = $_SERVER['SCRIPT_NAME'];	# IIS
	$server_protocol = $_SERVER['SERVER_PROTOCOL'];
	@$user_agent = $_SERVER['HTTP_USER_AGENT'];

	// Reconstruct the HTTP entity, if present.
	$request_entity = array();
	if (!strcasecmp($request_method, "POST") || !strcasecmp($request_method, "PUT")) {
		foreach ($_POST as $h => $v) {
			$request_entity[$h] = $v;
		}
	}

	$package = array('ip' => $ip, 'headers' => $headers, 'headers_mixed' => $headers_mixed, 'request_method' => $request_method, 'request_uri' => $request_uri, 'server_protocol' => $server_protocol, 'request_entity' => $request_entity, 'user_agent' => $user_agent, 'is_browser' => false);

	// Please proceed to the security checkpoint and have your
	// identification and boarding pass ready.

	// First check the whitelist
	require_once(BB2_CORE . "/whitelist.inc.php");
	if (!bb2_whitelist($package)) {
		// Now check the blacklist
		require_once(BB2_CORE . "/blacklist.inc.php");
		bb2_test($settings, $package, bb2_blacklist($package));

		// Check the http:BL
		require_once(BB2_CORE . "/blackhole.inc.php");
		if (bb2_test($settings, $package, bb2_httpbl($settings, $package))) {
			// Bypass all checks if http:BL says search engine
			bb2_approved($settings, $package);
			return true;
		}

		// Check for common stuff
		require_once(BB2_CORE . "/common_tests.inc.php");
		bb2_test($settings, $package, bb2_protocol($settings, $package));
		bb2_test($settings, $package, bb2_cookies($settings, $package));
		bb2_test($settings, $package, bb2_misc_headers($settings, $package));

		// Specific checks
		@$ua = $headers_mixed['User-Agent'];
		// Search engines first
		if (stripos($ua, "bingbot") !== FALSE || stripos($ua, "msnbot") !== FALSE || stripos($ua, "MS Search") !== FALSE) {
			require_once(BB2_CORE . "/msnbot.inc.php");
			bb2_test($settings, $package, bb2_msnbot($package));
			bb2_approved($settings, $package);
			return true;
		} elseif (stripos($ua, "Googlebot") !== FALSE || stripos($ua, "Mediapartners-Google") !== FALSE || stripos($ua, "Google Web Preview") !== FALSE) {
			require_once(BB2_CORE . "/google.inc.php");
			bb2_test($settings, $package, bb2_google($package));
			bb2_approved($settings, $package);
			return true;
		} elseif (stripos($ua, "Yahoo! Slurp") !== FALSE || stripos($ua, "Yahoo! SearchMonkey") !== FALSE) {
			require_once(BB2_CORE . "/yahoo.inc.php");
			bb2_test($settings, $package, bb2_yahoo($package));
			bb2_approved($settings, $package);
			return true;
		}
		// MSIE checks
		if (stripos($ua, "MSIE") !== FALSE) {
			$package['is_browser'] = true;
			if (stripos($ua, "Opera") !== FALSE) {
				require_once(BB2_CORE . "/opera.inc.php");
				bb2_test($settings, $package, bb2_opera($package));
			} else {
				require_once(BB2_CORE . "/msie.inc.php");
				bb2_test($settings, $package, bb2_msie($package));
			}
		} elseif (stripos($ua, "Konqueror") !== FALSE) {
			$package['is_browser'] = true;
			require_once(BB2_CORE . "/konqueror.inc.php");
			bb2_test($settings, $package, bb2_konqueror($package));
		} elseif (stripos($ua, "Opera") !== FALSE) {
			$package['is_browser'] = true;
			require_once(BB2_CORE . "/opera.inc.php");
			bb2_test($settings, $package, bb2_opera($package));
		} elseif (stripos($ua, "Safari") !== FALSE) {
			$package['is_browser'] = true;
			require_once(BB2_CORE . "/safari.inc.php");
			bb2_test($settings, $package, bb2_safari($package));
		} elseif (stripos($ua, "Lynx") !== FALSE) {
			$package['is_browser'] = true;
			require_once(BB2_CORE . "/lynx.inc.php");
			bb2_test($settings, $package, bb2_lynx($package));
		} elseif (stripos($ua, "MovableType") !== FALSE) {
			require_once(BB2_CORE . "/movabletype.inc.php");
			bb2_test($settings, $package, bb2_movabletype($package));
		} elseif (stripos($ua, "Mozilla") !== FALSE && stripos($ua, "Mozilla") == 0) {
			$package['is_browser'] = true;
			require_once(BB2_CORE . "/mozilla.inc.php");
			bb2_test($settings, $package, bb2_mozilla($package));
		}

		// More intensive screening applies to POST requests
		if (!strcasecmp('POST', $package['request_method'])) {
			require_once(BB2_CORE . "/post.inc.php");
			bb2_test($settings, $package, bb2_post($settings, $package));
		}
	}

	// Last chance screening.
	require_once(BB2_CORE . "/screener.inc.php");
	bb2_screener($settings, $package);

	// And that's about it.
	bb2_approved($settings, $package);
	return true;
}
?>
