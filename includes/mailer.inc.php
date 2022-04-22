<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
//use PHPMailer\PHPMailer\OAuthTokenProvider;
//use PHPMailer\PHPMailer\OAuth;
//use PHPMailer\PHPMailer\POP3;

// include php resources
require 'modules/phpmailer/PHPMailer.php';
require 'modules/phpmailer/Exception.php';
require 'modules/phpmailer/SMTP.php';
//require 'modules/phpmailer/OAuthTokenProvider.php';
//require 'modules/phpmailer/OAuth.php';
//require 'modules/phpmailer/POP3.php';

// include config
require 'config/php_mailer.php';

// create instance
$PHP_MAILER = new PHPMailer();

// add specified properties
foreach($PHP_MAILER_CONFIG as $key => $value) {
	$PHP_MAILER->set($key, $value);
}
?>