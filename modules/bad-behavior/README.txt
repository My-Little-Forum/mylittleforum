=== Bad Behavior ===
Tags: comment,trackback,referrer,spam,robot,antispam
Contributors: error, markjaquith, skeltoac
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=error%40ioerror%2eus&item_name=Bad%20Behavior%20%28From%20WordPress%20Page%29&no_shipping=1&cn=Comments%20about%20Bad%20Behavior&tax=0&currency_code=USD&bn=PP%2dDonationsBF&charset=UTF%2d8
Requires at least: 2.7
Tested up to: 3.2.1
Stable tag: 2.2.0

Bad Behavior prevents spammers from ever delivering their junk, and in many
cases, from ever reading your site in the first place.

== Description ==

Welcome to a whole new way of keeping your blog, forum, guestbook, wiki or
content management system free of link spam. Bad Behavior is a PHP-based
solution for blocking link spam and the robots which deliver it.

Thousands of sites large and small, like SourceForge, GNOME, the U.S.
Department of Education, and many more, trust Bad Behavior to help reduce
incoming link spam and malicious activity.

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
Public License, version 2, or at your option, any later version. (On some
non-free platforms, special license terms exist for Bad Behavior's platform
connector.) The development version of Bad Behavior is free software released
under the GNU Lesser General Public License, version 3, or at your option,
any later version.

== Installation ==

*Warning*: If you are upgrading from a 2.0.x release of Bad Behavior, it is
recommended that you delete the old version from your system before
installing the 2.2.x release, or obsolete files may be left lying around.

*Warning*: If you are upgrading from a 1.x.x version of Bad Behavior,
you must remove it from your system entirely, and delete all of its
database tables, before installing Bad Behavior 2.2.x or 2.0.x. If you are
upgrading from version 2.0.18 or prior, you must delete all of its files
before upgrading, but do not need to delete the database tables.

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
http://bad-behavior.ioerror.us/

== Screenshots ==

1. Most of the time, only spammers see this. In the rare event a human
winds up here, a way out is provided. This may involve removing malicious
software from the user's computer, changing firewall settings or other simple
fixes which will immediately grant access again.

2. Bad Behavior's built in log viewer (WordPress) shows why requests were
blocked and allows you to click on any IP address, user-agent string or
block reason to filter results.

== Release Notes ==

= Bad Behavior 2.0 Known Issues =

* Bad Behavior 2.0 requires MySQL 4.1 or later and PHP 4.3 or later. Bad
Behavior 2.1 requires MySQL 5.0 or later and PHP 5.2 or later.

* Bad Behavior is unable to protect internally cached pages on MediaWiki.
Only form submissions will be protected.

* When upgrading from version 2.0.19 or prior on MediaWiki and WordPress,
you must remove the old version of Bad Behavior from your system manually
before manually installing the new version. Other platforms are not
affected by this issue.

* Bad Behavior on WordPress requires version 2.7 or later. Users of older
versions should upgrade WordPress prior to installing Bad Behavior.

* On WordPress when using WP-Super Cache, Bad Behavior must be enabled in
WP-Super Cache's configuration in order to protect PHP Cached or Legacy
Cached pages. Bad Behavior cannot protect mod_rewrite cached (Super Cached)
pages.

* When using Bad Behavior in conjunction with Spam Karma 2, you may see PHP
warnings when Spam Karma 2 displays its internally generated CAPTCHA. This
is a design problem in Spam Karma 2. Contact the author of Spam Karma 2 for
a fix.

== Upgrade Notice ==

= 2.0.40 =

This release fixes a security issue. Upgrade as soon as possible.
