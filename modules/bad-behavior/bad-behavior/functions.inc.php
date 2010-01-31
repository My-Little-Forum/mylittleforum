<?php if (!defined('BB2_CORE')) die("I said no cheating!");

// Miscellaneous helper functions.

// stripos() needed because stripos is only present on PHP 5
if (!function_exists('stripos')) {
	function stripos($haystack,$needle,$offset = 0) {
		return(strpos(strtolower($haystack),strtolower($needle),$offset));
	}
}

// str_split() needed because str_split is only present on PHP 5
if (!function_exists('str_split')) {
	function str_split($string, $split_length=1)
	{
		if ($split_length < 1) {
			return false;
		}

		for ($pos=0, $chunks = array(); $pos < strlen($string); $pos+=$split_length) {
			$chunks[] = substr($string, $pos, $split_length);
		}
		return $chunks;
	}
}

// Convert a string to mixed-case on word boundaries.
function uc_all($string) {
	$temp = preg_split('/(\W)/', str_replace("_", "-", $string), -1, PREG_SPLIT_DELIM_CAPTURE);
	foreach ($temp as $key=>$word) {
		$temp[$key] = ucfirst(strtolower($word));
	}
	return join ('', $temp);
}

// Determine if an IP address resides in a CIDR netblock or netblocks.
function match_cidr($addr, $cidr) {
	$output = false;

	if (is_array($cidr)) {
		foreach ($cidr as $cidrlet) {
			if (match_cidr($addr, $cidrlet)) {
				$output = true;
			}
		}
	} else {
		@list($ip, $mask) = explode('/', $cidr);
		if (!$mask) $mask = 32;
		$mask = pow(2,32) - pow(2, (32 - $mask));
		$output = ((ip2long($addr) & $mask) == (ip2long($ip) & $mask));
	}
	return $output;
}

// Obtain all the HTTP headers.
// NB: on PHP-CGI we have to fake it out a bit, since we can't get the REAL
// headers. Run PHP as Apache 2.0 module if possible for best results.
function bb2_load_headers() {
	if (!is_callable('getallheaders')) {
		$headers = array();
		foreach ($_SERVER as $h => $v)
			if (ereg('HTTP_(.+)', $h, $hp))
				$headers[str_replace("_", "-", uc_all($hp[1]))] = $v;
	} else {
		$headers = getallheaders();
	}
	return $headers;
}

?>
