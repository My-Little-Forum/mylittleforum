<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// Analyze user agents claiming to be Konqueror

function bb2_konqueror($package)
{
	// CafeKelsa is a dev project at Yahoo which indexes job listings for
	// Yahoo! HotJobs. It identifies as Konqueror so we skip these checks.
	if (stripos($package['headers_mixed']['User-Agent'], "YahooSeeker/CafeKelsa") === FALSE || match_cidr($package['ip'], "209.73.160.0/19") === FALSE) {
		if (!array_key_exists('Accept', $package['headers_mixed'])) {
			return "17566707";
		}
	}
	return false;
}

?>
