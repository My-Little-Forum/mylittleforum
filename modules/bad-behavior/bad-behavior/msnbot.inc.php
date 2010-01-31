<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// Analyze user agents claiming to be msnbot

function bb2_msnbot($package)
{
	if (match_cidr($package['ip'], "207.46.0.0/16") === FALSE && match_cidr($package['ip'], "65.52.0.0/14") === FALSE && match_cidr($package['ip'], "207.68.128.0/18") === FALSE && match_cidr($package['ip'], "207.68.192.0/20") === FALSE && match_cidr($package['ip'], "64.4.0.0/18") === FALSE) {
		return "e4de0453";
	}
	return false;
}

?>
