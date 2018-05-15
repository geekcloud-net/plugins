=== PHP Settings ===
Contributors: Askupa Software, ykadosh
Tags: admin, php settings, user.ini, ini rules, php.ini, php5.ini, post max size, post_max_size, upload limit, upload max filesize, upload_max_filesize, max_execution_time, max execution time
Requires at least: 3.0
Tested up to: 4.9
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin provides a simple user interface with a code editor to edit your local php.ini settings. 

== Description ==

This plugin provides a simple user interface with a code editor to edit your local `.ini` settings. 
This can be used to change settings like `upload_max_filesize` or `max_execution_time` which are often set to very low values by the hosting companies.
Clicking on "Save Settings" creates 3 files: `.user.ini`, `php.ini` and `php5.ini` and saves your settings asynchronously without reloading the page.

The plugin also features a table containing a list of all the core PHP settings and their active values.
Settings that are successfully overridden become highlighted in the table.
Additionally, you can click on any one of the settings in the table and copy it to the editor. 
This makes it extremely easy to add or remove directives in a local `.ini` file.
Finally, a search box is built into the settings table to allow you to search for PHP directives to easily see their current value or copy them to the editor.

**Features**

* Code editor with syntax highlighting
* A table showing all PHP core settings and their values
* A Search box to search for PHP directives
* Settings that are locally overridden become highlighted in the table
* Click on any setting field in the table to copy it to the editor
* Save the settings to local `.ini` files asynchronously (no page reload)
* Delete local `.ini` files created by the plugin
* Refresh the PHP settings table asynchronously (no page reload)

**Usage**

1. Got to Tools -> PHP Settings
1. Use the editor to manually add PHP settings (For example, `max_execution_time = 120`), or
1. Click on "Settings" and then click on one of the settings in the table to automatically copy it to the editor
1. Click on "Save Settings" to save the editor contents to local `.ini` files. If the files do not exist, they will be created.
1. Click on "Settings" again and then click "Refresh Table" to see the changes take effect. You might need to wait a few minutes before the changes are updated in the system.
1. To delete the `.ini` files created by the program, click on "Delete .ini Files". This will restore the master PHP settings.

**Useful Links**

* [Official Page](http://products.askupasoftware.com/php-settings/)
* [List of changeable ini settings](http://www.php.net/manual/en/ini.list.php)
* [The PHP configuration file](http://php.net/manual/en/configuration.file.php)
* [Local .user.ini files](http://php.net/manual/en/configuration.file.per-user.php)

== Installation ==

1. Download and activate the plugin.
1. Navigate to Tools->PHP Settings
1. Use the INI editor to add/remove php.ini directives
1. Click "Save Settings" to save the contents of the INI editor to a local .ini file

== Frequently Asked Questions ==

= My PHP Settings remained unchanged after saving the changes =

This can happen for multiple reasons.
1. You are trying to set a directive that cannot be locally overridden (see [this table](http://www.php.net/manual/en/ini.list.php))
1. You need to recycle your application pool
1. Your hosting company set your PHP to ignore local .ini files

== Screenshots ==

1. INI Settings Editor
2. PHP Core Setting Table

== Changelog ==

= 1.0.6 =
* (FIX) Fixed an issue that was causing PHP 7.1 to throw a lexical error.

= 1.0.5 =
* (FIX) Fixed the "you do not have sufficient privileges" error

= 1.0.4 =
* (NEW) Added checkboxes to allow bulk copy of directives to editor
* (NEW) Added a search box to search for PHP directives (based on the great list.js script)
* (IMPROVE) Improved security in AJAX calls

= 1.0.3 =
* (NEW) Added a link to the PHP settings page in the plugins page
* (FIX) Locally overridden directives are now copied properly to the editor

= 1.0.2 =
* (FIX) The plugin will gracefully shutdown if the PHP version is too old
* (IMPROVED) Reorganized file structure in a more logical way
* (IMPROVED) Changed naming conventions to prevent class name collisions

= 1.0.1 =
* (NEW) An error message will be shown if PHP does not have sufficient permissions to write files
* (IMPROVED) Error handling & displaying
* (IMPROVED) Scripts/Styles loading

= 1.0.0 =
* Initial release

== Upgrade Notice ==

