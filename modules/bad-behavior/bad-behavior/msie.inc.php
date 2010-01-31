<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// Analyze user agents claiming to be MSIE

function bb2_msie($package)
{
	if (!array_key_exists('Accept', $package['headers_mixed'])) {
		return "17566707";
	}

	// MSIE does NOT send "Windows ME" or "Windows XP" in the user agent
	if (strpos($package['headers_mixed']['User-Agent'], "Windows ME") !== FALSE || strpos($package['headers_mixed']['User-Agent'], "Windows XP") !== FALSE || strpos($package['headers_mixed']['User-Agent'], "Windows 2000") !== FALSE || strpos($package['headers_mixed']['User-Agent'], "Win32") !== FALSE) {
		return "a1084bad";
	}

	// MSIE does NOT send Connection: TE but Akamai does
	// Bypass this test when Akamai detected
	// The latest version of IE for Windows CE also uses Connection: TE
	if (!array_key_exists('Akamai-Origin-Hop', $package['headers_mixed']) && strpos($package['headers_mixed']['User-Agent'], "IEMobile") === FALSE && @preg_match('/\bTE\b/i', $package['headers_mixed']['Connection'])) {
		return "2b90f772";
	}

	return false;
}

?>
