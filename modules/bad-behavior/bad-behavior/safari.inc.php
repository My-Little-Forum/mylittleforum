<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// Analyze user agents claiming to be Safari

function bb2_safari($package)
{
	if (!array_key_exists('Accept', $package['headers_mixed'])) {
		return "17566707";
	}
	return false;
}

?>
