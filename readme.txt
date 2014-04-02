=== MP Restrict Logins ===
Contributors: johnstonphilip
Donate link: http://moveplugins.com/
Tags: subscribers, limit logins
Requires at least: 3.0.1
Tested up to: 3.5
Stable tag: 1.0.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Prevent 1 subscriber account from being used simultaneously. Perfect for websites that sell subscriptions and want to prevent accounts from being shared.

== Description ==

This plugin allows 1 person to log into 1 WordPress account using 1 application/device at 1 time. If another person tried to log in from a different location, they will get an error message and be unable to access the account until the original user leaves. 

Works even if the original user doesn’t click “log out” before closing. The account will just time out, and then the user can log in in a different location.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the ‘mp-restrict-logins’ folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What do I do with this?  =

No setup is needed! Just install it and go.

== Screenshots ==

== Changelog ==

= 1.0.0.3 = April 2, 2014
Change session cookies to PHP session variables

= 1.0.0.2 = April 1, 2014
* Show error messages even if user isn’t logged out to wp-login page

= 1.0.0.1 = March 1, 2014
* Deliver better error notices and auto redirect user when logout needed.

= 1.0.0.0 = February 27, 2014
* Original release
