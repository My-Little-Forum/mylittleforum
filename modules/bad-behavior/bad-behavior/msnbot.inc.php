<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// Analyze user agents claiming to be msnbot

function bb2_msnbot($package)
{
	if (match_cidr($package['ip'], array("207.46.0.0/16", "65.52.0.0/14", "207.68.128.0/18", "207.68.192.0/20", "64.4.0.0/18", "157.54.0.0/15", "157.60.0.0/16", "157.56.0.0/14")) === FALSE) {
		return "e4de0453";
	}
	return false;
}

?>
