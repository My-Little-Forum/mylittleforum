<?php if (!defined('BB2_CORE')) die('I said no cheating!');

function bb2_housekeeping($settings, $package)
{
	// FIXME Yes, the interval's hard coded (again) for now.
	$query = "DELETE FROM `" . $settings['log_table'] . "` WHERE `date` < DATE_SUB('" . bb2_db_date() . "', INTERVAL 7 DAY)";
	bb2_db_query($query);

	// Waste a bunch more of the spammer's time, sometimes.
	if (rand(1,1000) == 1) {
		$query = "OPTIMIZE TABLE `" . $settings['log_table'] . "`";
		bb2_db_query($query);
	}
}

?>
