<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// Analyze user agents claiming to be Mozilla

function bb2_mozilla($package)
{
	// First off, workaround for Google Desktop, until they fix it FIXME
	// Google Desktop fixed it, but apparently some old versions are
	// still out there. :(
	// Always check accept header for Mozilla user agents
	if (strpos($package['headers_mixed']['User-Agent'], "Google Desktop") === FALSE && strpos($package['headers_mixed']['User-Agent'], "PLAYSTATION 3") === FALSE) {
		if (!array_key_exists('Accept', $package['headers_mixed'])) {
			return "17566707";
		}
	}
	return false;
}

?>
