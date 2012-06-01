=== Plugin Name ===
Contributors: jolley_small
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10691945
Tags: download, downloads, monitor, hits, download monitor, tracking, admin, count, counter, files
Requires at least: 3.1
Tested up to: 3.1
Stable tag: 3.3.5.2

Plugin with interface for uploading and managing download files, inserting download links in posts, and monitoring download hits.

== Description ==

Download Monitor is a plugin for uploading and managing downloads, tracking download hits, and displaying links.

Download Monitor requires Wordpress version 2.5 or above. Version 3.0 is a major update and many of the template and post tags have been changed and improved. See the usage page for full details before upgrading.

From version 3.3 a new database structure is being used so ensure you backup your database before upgrading (this was changed to enable multiple category support).

For older versions of wordpress use the older Download Monitor version 2.2.3 which is available from http://wordpress.org/extend/plugins/download-monitor/download/ (tested and working in Wordpress 2.0 and 2.3).

Plugin contains filetype icons from the Fugue Icon Pack by Yusuke Kamiyamane (http://pinvoke.com).

= Features =

*	Built in Download Page function with built in sorting, pagination, and search. This was going to be a paid addon but i'm too nice - so please donate if you use it!
*	Records file download hits but does **not** count downloads by wordpress admin users.
*	Stats on downloads and a download log for viewing who downloaded when.
*	Uses shortcodes (backward compatible with old [download#id] style).
*	Editor button - upload and add a download stright from a post.
*	Custom redirects to downloads.
*	Add downloads to text widgets, the content, excerpts, and custom fields.
*	Mirror support (selected at random) + mirror deadlink checker
*	Download Categories and tags.
*	Member only downloads, can also have a minimum user level using custom fields.
*	Localization support.
*	Admin for managing downloads and also changing hit counts - just in case you change servers or import old downloads that already have stats.
*	Custom URL's/URL hider using mod_rewrite.

= Sustainable Plugin Development - and Your Privacy =

Download Monitor is a participant in the Sustainable Plugins Sponsorship Network (SPSN) - http://pluginsponsors.com/. The SPSN model offers modest sponsorships to plugin authors in return for a small amount of screen real estate on plugin options pages. The SPSN sponsor messages can be switched altogether: just visit the Config page.

IMPORTANT PRIVACY INFORMATION: NO INDIVIDUALLY IDENTIFIABLE DETAILS OF ANY KIND, REGARDING EITHER YOU OR YOUR SITE, will be collected or shared as a result of displaying Sustainable Plugins Sponsorship Network (SPSN) sponsor messages. Sponsors receive only aggregate reports of impressions on a worldwide per-plugin basis, NOT on impressions or on any other activity at any individual site which happens to be using a plugin.

= Localization =

None here yet =) If you create a localisation for download monitor please send me a mail or contact me on twitter (@mikejolley). Ill list it here and include it with the plugin if you so desire.

== Installation ==

= First time installation instructions =

Installation is fast and easy. The following steps will guide get you started:

   1. Unpack the *.zip file and extract the /download-monitor/ folder and the files.
   2. Using an FTP program, upload the /download-monitor/ folder to your WordPress plugins directory (Example: /wp-content/plugins).
   3. Ensure the <code>/wp-content/uploads</code> directory exists and has correct permissions to allow the script to upload files.
   4. Open your WordPress Admin panel and go to the Plugins page. Locate the "Wordpress Download Monitor" plugin and
      click on the "Activate" link.
   5. Once activated, go to the Downloads admin section.
   
Note: If you encounter any problems when downloading files it is likely to be a file permissions issue. Change the file permissions of the download-monitor folder and contents to 755 (check with your host if your not sure how).


== Frequently Asked Questions ==

You can now view the FAQ in the documentation: http://blue-anvil.com/archives/wordpress-download-monitor-3-documentation.


== Screenshots ==

1. Wordpress 2.7 admin screenshot
2. Download page single listing
3. Download page listings
4. More download page listings


== Changelog ==

= 4.0 = 
*	Complete Rewrite
*	Removed Legacy Tags
*	Used custom post types for downloads instead of the old database table system
*	No more need for rewrite rules; we now use the WordPress permalink structure

== Usage ==

Full Usage instructions and documentation can be found here: http://blue-anvil.com/archives/wordpress-download-monitor-3-documentation