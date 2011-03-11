<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// All tests which apply specifically to POST requests
function bb2_post($settings, $package)
{
	// Check blackhole lists for known spam/malicious activity
	// require_once(BB2_CORE . "/blackhole.inc.php");
	// bb2_test($settings, $package, bb2_blackhole($package));

	// MovableType needs specialized screening
	if (stripos($package['headers_mixed']['User-Agent'], "MovableType") !== FALSE) {
		if (strcmp($package['headers_mixed']['Range'], "bytes=0-99999")) {
			return "7d12528e";
		}
	}

	// Trackbacks need special screening
	$request_entity = $package['request_entity'];
	if (isset($request_entity['title']) && isset($request_entity['url']) && isset($request_entity['blog_name'])) {
		require_once(BB2_CORE . "/trackback.inc.php");
		return bb2_trackback($package);
	}

	// Catch a few completely broken spambots
	foreach ($request_entity as $key => $value) {
		$pos = strpos($key, "	document.write");
		if ($pos !== FALSE) {
			return "dfd9b1ad";
		}
	}

	// If Referer exists, it should refer to a page on our site
	if (!$settings['offsite_forms'] && array_key_exists('Referer', $package['headers_mixed']) && stripos($package['headers_mixed']['Referer'], $package['headers_mixed']['Host']) === FALSE) {
		return "cd361abb";
	}

	// Screen by cookie/JavaScript form add
	if (isset($_COOKIE[BB2_COOKIE])) {
		$screener1 = explode(" ", $_COOKIE[BB2_COOKIE]);
	} else {
		$screener1 = array(0);
	}
	if (isset($_POST[BB2_COOKIE])) {
		$screener2 = explode(" ", $_POST[BB2_COOKIE]);
	} else {
		$screener2 = array(0);
	}
	$screener = max($screener1[0], $screener2[0]);

	if ($screener > 0) {
		// Posting too fast? 5 sec
		// FIXME: even 5 sec is too intrusive
		// if ($screener + 5 > time())
		//	return "408d7e72";
		// Posting too slow? 48 hr
		if ($screener + 172800 < time())
			return "b40c8ddc";

		// Screen by IP address
		$ip = ip2long($package['ip']);
		$ip_screener = ip2long($screener[1]);
//		FIXME: This is b0rked, but why?
//		if ($ip && $ip_screener && abs($ip_screener - $ip) > 256)
//			return "c1fa729b";

		if (!empty($package['headers_mixed']['X-Forwarded-For'])) {
			$ip = $package['headers_mixed']['X-Forwarded-For'];
		}
		// Screen for user agent changes
		// User connected previously with blank user agent
//		$q = bb2_db_query("SELECT `ip` FROM " . $settings['log_table'] . " WHERE (`ip` = '" . $package['ip'] . "' OR `ip` = '" . $screener[1] . "') AND `user_agent` != '" . $package['user_agent'] . "' AND `date` > DATE_SUB('" . bb2_db_date() . "', INTERVAL 5 MINUTE)");
		// Damnit, too many ways for this to fail :(
//		if ($q !== FALSE && $q != NULL && bb2_db_num_rows($q) > 0)
//			return "799165c2";
	}

	return false;
}

?>
