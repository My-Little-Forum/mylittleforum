<?php
if (!defined('IN_INDEX')) {
	header('Location: ../index.php');
	exit;
}

/** config for PHPMailer **/
// Please read https://github.com/PHPMailer/PHPMailer/blob/master/src/PHPMailer.php 
// for further configuration properties
$PHP_MAILER_CONFIG = array(
	'Mailer'		=> 'smtp',				// 'smtp', 'mail', 'sendmail' or 'qmail'
	'Port'			=> '587',				// well-known ports are 25 (default), 587 (TLS) or 465 (SSL)
	'SMTPSecure'	=> 'tls',				// '', 'tls' or 'ssl'
	'ContentType'	=> 'text/plain',		// 'text/plain' or 'text/html'
	'Encoding'		=> 'quoted-printable',	// '8bit', '7bit', 'binary', 'base64', and 'quoted-printable'
	'CharSet'		=> 'utf-8',				// 'iso-8859-1' or 'utf-8'
	'SMTPAuth'		=> true,				// true, for SMTP authentication via username/password
	'Host'			=> 'smtp.example.org',  	
	'Username'		=> 'mail@example.org', 
	'Password'		=> 'secret password'			
);
/** config for PHPMailer **/
?>