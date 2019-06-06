=== Activity Log For MainWP ===
Contributors: WPWhiteSecurity, robert681
Plugin URI: https://www.wpsecurityauditlog.com/activity-log-mainwp-extension/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: activity log, mainwp, mainwp extension, wordpress security, wordpress security audit log, audit log, mainwp user tracking, wordpress activity log, security activity log, wordpress admin, mainwp admin, user tracking
Requires at least: 3.6
Tested up to: 5.2.1
Stable tag: 1.0.4
Requires PHP: 5.5

See the activity logs of all child sites & MainWP in one central location - the MainWP dashboard.

== Description ==

<strong>THE ONLY ACTIVITY LOG PLUGIN FOR MAINWP & CHILD SITES</strong><br />

MainWP is an invaluable tool administrators and agencies use to manage multiple WordPress sites from one central dashboard. Add the [Activity Logs for MainWP extension](https://www.wpsecurityauditlog.com/activity-log-mainwp-extension/) to keep a log of what happened on the MainWP dashboard and also on child sites without having to login to the child sites.

The Activity Log for MainWP extension is developed by [WP White Security](https://www.wpwhitesecurity.com/), the developers of WP Security Audit Log, [the most comprehensive activity log plugin for WordPress](https://www.wpsecurityauditlog.com/).

####Keep a Log of everything that happens on your MainWP network and child sites

Your back is covered with the Activity Log for MainWP extension! Now you can:

* See the activity logs of all child sites in the MainWP dashboard
* Know what your clients changed on their business site
* Keep a log of all changes done to child sites via the MainWP dashboard
* Keep a log of changes done in the MainWP dashboard and other extensions

####No more customer blame games & Guess work!

By keeping an activity log of everything that happens on your clients' sites and the MainWP dashboard you can:

* Ease troubleshooting - no more guess work!
* Better manage and secure your client sites
* Identify suspicious behaviour before it becomes a security issue
* Stop all the customer blame games - have a proof of what happened
* Keep a record of all your work, thus justifying your fees

####The Activity Log for MainWP Can Keep a Log Of:

###Activity Logs on Child Sites

The extension uses the WP Security Audit Log plugin to for the child sites activity log. So refer to the [list of WordPress activity log events & event IDs](https://www.wpsecurityauditlog.com/support-documentation/list-wordpress-audit-trail-alerts/) for a complete list.

###Activity Logs on MainWP Dashboard

The extension also keeps a log of changes users do on the MainWP dashboard, such as:

* Adding, editing or removing child sites
* Logging in or out of the MainWP dashboard
* Installing, activating, deactivating, uninstalling extensions
* Adding or removing extensions to the MainWP menu
* Changes in the MainWP extension Advanced Uptime Monitor

Refer to the [list of MainWP extension event log events](https://www.wpsecurityauditlog.com/support-documentation/list-wordpress-audit-trail-alerts/#mainwp) for a complete list of changes it can keep a log of.

####Get Started Within Minutes!

Follow the [Activity Logs for MainWP getting started guide](https://www.wpsecurityauditlog.com/support-documentation/gettting-started-activity-log-mainwp-extension/) for an easy to follow step by step procedure of how you can start keeping an activity log of all child site changes and view them from the MainWP dashboard.

#### Free Extension Support

Support for the Activity Log for MainWP extension is provided via the WordPress forums or [email](https://www.wpsecurityauditlog.com/contact/) for free.

#### Related Links and Documentation

* [What is a WordPress Activity Log?](https://www.wpsecurityauditlog.com/wordpress-user-monitoring-plugin-documentation/what-is-a-wordpress-audit-trail/)
* [List of WordPress Activity Log events](http://www.wpsecurityauditlog.com/documentation/list-monitoring-wordpress-security-alerts-audit-log/)
* [Activity Log for MainWP Extension Page](https://www.wpsecurityauditlog.com/activity-log-mainwp-extension/)

* [Official WP Security Audit Log Plugin Website](http://www.wpsecurityauditlog.com/)

== Installation ==

=== Install the Activity Log for MainWP extension from within the MainWP dashboard ===

1. Login to your MainWP dashboard
1. Navigate to WP > Plugins
1. Search for 'Activity Log for MainWP'
1. Install and activate the plugin
1. Follow the wizard to add the activity logs of the child sites

=== Install the Activity Log for MainWP extension manually ===

1. Download the plugin
1. Login to your MainWP dashboard
1. Navigate to WP > Plugins
1. Click Add New and then Upload Plugin
1. Browse to the file, select it and click Install Now
1. Click Activate Plugin once prompted.
1. Follow the wizard to add the activity logs of the child sites

== Frequently Asked Questions ==

= Support and Documentation =
Please refer to our [Support & Documentation pages](https://www.wpsecurityauditlog.com/documentation/frequently-asked-questions-faqs) for all the technical information and support documentation on the Activity Log for MainWP extension and the WP Security Audit Log plugin.

== Screenshots ==

1. The audit log viewer in the MainWP dashboard from where you can see the activity log of the MainWP network and also of all the child sites you are managing.
2. You can filter the activity log by child site or the MainWP dashboard by using the site selector drop down menu.
3. The WordPress activity logs in the MainWP dashboard filtered by a single child site.
4. Once you install the plugin you are greeted with a one-time setup wizard.
5. You can use the wizard to select which child sites you'd like to add to the activity log.
6. You can add more child sites or remove any from the central activity log in the MainWP dashboard at any time from the extension's settings.
7. You can also configure what information and how it is displayed in the MainWP audit log from the extension's settings.
8. By default the extension retrieves the child sites' logs every three hours, though you can configure this schedule from the extension settings.

== Changelog ==

= 1.0.4 (2019-06-11) =

* **New Feature**
	* New pruning setting for [MainWP dashboard & network activity log events](https://www.wpsecurityauditlog.com/support-documentation/list-wordpress-audit-trail-alerts/#mainwp).
	* Settings to disable / enable specific MainWP dashboard & network event IDs.

= 1.0.3 (2019-03-19) =

* **New Feature**
	* Infinite Scroll in the activity log viewer (switch between pagination and infinite scroll in the extension settings).
	* Integrated Freemius SDK (preparation for the premium features).
	
* **Performance Update**
	* Improved several queries and improved activity log viewer performance.
	
* **Improvement**
	* New setting to retrieve activity logs when MainWP syncs website data (default: Off).
	
= 1.0.2 (2019-02-08) =

* **Security Fix**
	* Updated npm dev-dependency because used version had a low severity issue ([CVE-2018-16491](https://nvd.nist.gov/vuln/detail/CVE-2018-16491))

= 1.0.1 (2019-01-07) =

* **Improvements**
	* Improved wizard for when WP Security Audit Log is not installed on any child sites.
	* Improved the retrieving events process for when child sites have no activity log events.
	* Updated the help text in the extension settings.
	
* **Bug Fixes**
	* Handling an error for when Activity Log for MainWP is installed on a WordPress site without MainWP.

= 1.0 (2018-11-15) =

Release Notes: [click here](https://www.wpsecurityauditlog.com/releases/XXX/)

	* First release - everything is new!
