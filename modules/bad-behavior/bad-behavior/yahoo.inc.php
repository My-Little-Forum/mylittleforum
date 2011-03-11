<?php if (!defined('BB2_CORE')) die('I said no cheating!');

// Analyze user agents claiming to be Yahoo!

function bb2_yahoo($package)
{
	if (match_cidr($package['ip'], array("202.160.176.0/20", "67.195.0.0/16", "203.209.252.0/24", "72.30.0.0/16", "98.136.0.0/14")) === FALSE) {
		return "71436a15";
	}
	return false;
}
