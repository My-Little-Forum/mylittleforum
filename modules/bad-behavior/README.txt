=== Bad Behavior ===
Tags: comment,trackback,referrer,spam,robot,antispam
Contributors: error, MarkJaquith, Firas, skeltoac
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=error%40ioerror%2eus&item_name=Bad%20Behavior%20%28From%20WordPress%20Page%29&no_shipping=1&cn=Comments%20about%20Bad%20Behavior&tax=0&currency_code=USD&bn=PP%2dDonationsBF&charset=UTF%2d8
Requires at least: 1.5
Tested up to: 2.9
Stable tag: 2.0.36

Welcome to a whole new way of keeping your blog, forum, guestbook, wiki or
content management system free of link spam. Bad Behavior is a PHP-based
solution for blocking link spam and the robots which deliver it.

Bad Behavior complements other link spam solutions by acting as a gatekeeper,
preventing spammers from ever delivering their junk, and in many cases, from
ever reading your site in the first place. This keeps your site's load down,
makes your site logs cleaner, and can help prevent denial of service
conditions caused by spammers.

Bad Behavior also transcends other link spam solutions by working in a
completely different, unique way. Instead of merely looking at the content of
potential spam, Bad Behavior analyzes the delivery method as well as the
software the spammer is using. In this way, Bad Behavior can stop spam attacks
even when nobody has ever seen the particular spam before.

Bad Behavior is designed to work alongside existing spam prevention services
to increase their effectiveness and efficiency. Whenever possible, you should
run it in combination with a more traditional spam prevention service.

Bad Behavior works on, or can be adapted to, virtually any PHP-based Web
software package. Bad Behavior is available natively for WordPress, MediaWiki,
Drupal, ExpressionEngine, and LifeType, and people have successfully made it
work with Movable Type, phpBB, and many other packages.

Installing and configuring Bad Behavior on most platforms is simple and takes
only a few minutes. In most cases, no configuration at all is needed. Simply
turn it on and stop worrying about spam!

The core of Bad Behavior is free software released under the GNU General
Public License. (On some non-free platforms, special license terms exist for
Bad Behavior's platform connector.)

== Installation ==

*Warning*: If you are upgrading from a 1.x.x version of Bad Behavior,
you must remove it from your system entirely, and delete all of its
database tables, before installing Bad Behavior 2.0.x. You do not need
to remove a 2.0.x version of Bad Behavior before upgrading to this
release.

Bad Behavior has been designed to install on each host software in the
manner most appropriate to each platform. It's usually sufficient to
follow the generic instructions for installing any plugin or extension
for your host software.

On MediaWiki, it is necessary to add a second line to LocalSettings.php
when installing the extension. Your LocalSettings.php should include
the following:

`	include_once( 'includes/DatabaseFunctions.php' );
	include( './extensions/Bad-Behavior/bad-behavior-mediawiki.php' );

For complete documentation and installation instructions, please visit
http://www.bad-behavior.ioerror.us/

== Release Notes ==

= Bad Behavior 2.0 Known Issues =

* Bad Behavior may be unable to protect cached pages on MediaWiki.

* When upgrading from version 2.0.19 or prior on MediaWiki and WordPress,
you must remove the old version of Bad Behavior from your system manually
before manually installing the new version. Other platforms are not
affected by this issue.

* The basic functionality of Bad Behavior on WordPress requires version 1.2
or later. The management page for WordPress, which allows browsing Bad
Behavior's logs, requires version 2.1 or later. Users of older versions should
use phpMyAdmin to browse Bad Behavior's logs, or upgrade WordPress.

* On WordPress when using WordPress Advanced Cache (WP-Cache) or WP-Super
Cache, Bad Behavior requires a patch to WP-Cache or WP-Super Cache in order to
protect Cached pages. Bad Behavior cannot protect Super Cached pages.

  Edit the wp-content/plugins/wp-cache/wp-cache-phase1.php or
wp-content/plugins/wp-super-cache/wp-cache-phase1.php file and find the
following two lines at around line 34 (line 56 in WP-Super Cache):

`	if (! ($meta = unserialize(@file_get_contents($meta_pathname))) )
		return true;`

  Immediately after this, insert the following line:

`	require_once( ABSPATH . 'wp-content/plugins/Bad-Behavior/bad-behavior-generic.php');`

  Then visit your site. Everything should work normally, but spammers will
not be able to access your cached pages either.

* When using Bad Behavior in conjunction with Spam Karma 2, you may see PHP
warnings when Spam Karma 2 displays its internally generated CAPTCHA. This
is a design problem in Spam Karma 2. Contact the author of Spam Karma 2 for
a fix.
