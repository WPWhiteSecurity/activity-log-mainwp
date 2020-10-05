=== Activity Log For MainWP ===
Contributors: WPWhiteSecurity, robert681
Plugin URI: https://wpactivitylog.com/extensions/mainwp-activity-log/
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.html
Tags: activity log, mainwp, mainwp extension, wordpress security, wordpress security audit log, audit log, mainwp user tracking, wordpress activity log, security activity log, wordpress admin, mainwp admin, user tracking
Requires at least: 3.6
Tested up to: 5.5
Stable tag: 1.5.2
Requires PHP: 5.5

See the activity logs of all child sites & MainWP in one central location - the MainWP dashboard.

== Description ==

<strong>ACTIVITY LOG FOR MAINWP PREMIUM</strong><br />

This is the premium edition of the [Activity Log for MainWP plugin](https://wordpress.org/plugins/activity-log-mainwp/).

#### Related Links and Documentation

* [What is a WordPress Activity Log?](https://wpactivitylog.com/wordpress-activity-log/)
* [List of WordPress Activity Log events](https://wpactivitylog.com/support/kb/list-wordpress-activity-log-event-ids/)
* [Activity Log for MainWP Extension Page](https://wpactivitylog.com/extensions/mainwp-activity-log/)
* [Official WP Activity Log Plugin Website](http://www.wpactivitylog.com/)

== Installation ==

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
Please refer to our [Support & Documentation pages](https://wpactivitylog.com/support/kb/) for all the technical information and support documentation on the Activity Log for MainWP extension and the WP Activity Log plugin.

== Changelog ==

= 1.5.2 (2020-09-21) =

* **Improvement**
	* Renamed all filters / hooks. Now using the new naming convention used in MainWP 4.1.
	
= 1.5.1 (2020-05-20) =

* **Improvement**
	* Updating links to reflect name change.

* **Bug fix**
	* Ensure extension title is only shown in the extensions pages.

= 1.5.0 (2020-02-19) =

Release Notes: [MainWP extension: new search & filters module & improved UX](https://wpactivitylog.com/al4mwp-1-5/)

* **New Features**
	* New activity log search & filters module with much better UX.

* **Improvement**
	* Reports module now supports the latest data format changes used in MainWP.
	* Plugin is now fully translatable.
	* Added the new [activity log objects](https://www.wpactivitylog.com/support/kb/objects-event-types-wordpress-activity-log/) which are used by the main plugin.
	* Improved the responsiveness and UI of the activity log viewer.
	* Added a confirmation for when the activity log is purged from the MainWP database.

* **Bug fixes**
	* Consolidated the Text Domain throughout the plugin.
	* List of child sites in plugin settings incorrectly displaying sites on some setups.
	* Correct plugin name is now displayed in the extention page.
	* Rewritten some of the settings page text.
	* After activating the plugin user is redirected to the wrong page.
	* Fixed some rendering issues in the plugin's pages.

= 1.4.2 (2020-02-21) =

* **Improvement**
	* Improved the add/remove child sites function to handle the new data types.

* **Bug fix**
	* Plugin not processing properly the retrieved list of child sites.

= 1.4.1 (2020-01-21) =

* **Improvement**
	* Updated the plugin to support the latest version of MainWP updates.

= 1.4 (2020-01-14) =

* **New functionality**
	* Support for [activity logs of WP Activity Log version 4](https://wpactivitylog.com/update-4/).

= 1.3 (2019-12-12) =

Release Notes: [New MainWP activity logs retention settings & other updates](https://wpactivitylog.com/activity-log-mainwp-1-3/)

* **New Features**
	* New activity logs retention setting for MainWP dashboard logs.
	* Plugin now automatically fetches logs from child sites upon install (optional).

* **Improvement**
	* Added new compatibility check & notification for WP Activity Log v4 (new activity log).

* **Bug fixes**
	* Plugin tries to retrieve logs from removed child sites.
	* First install wizard not firing in some edge cases.
	* Premium promo banner showing in premium edition.
	* Close link on premium promo banner was not working in some cases.
	* Addressed a compatibility error with PHP 7.4.
	* Removed the Upgrade to Premium button in the extensions page when using premium edition.

= 1.2 (2019-10-28) =

* **Improvement**
	* Plugin retrieves list of child sites in batches to better handle large number of sites on bigger networks.

* **Bug fixes**
	* Child site not removed from activity log database even after being removed by user.
	* Removed unnecessary code for navtabs which was causing a console error.

= 1.1 (2019-09-19) =

* Release Notes: [Announcing Activity Log for MainWP Premium](https://wpactivitylog.com/activity-log-mainwp-premium-release/)

	* First release - everything is new!
