#### 4.0.0: January 20, 2018
* Tweak: Made plugin compatible with Download Monitor 4.0
* Tweak: Replaced custom autoloader with Composer class map.

### 1.4.1: May 10, 2017
* Tweak: Fixed a timezone bug that caused imported downloads to be scheduled instead of published.
* Tweak: Check if data is set per row for given headers, preventing notices.
* Tweak: Updated extension register method for better update support.

### 1.4.0: May 27, 2016
* Feature: Downloads can now be overridden/updated based on download ID.
* Tweak: Downloads can no longer be overridden/updated based on download title.

### 1.3.0: May 3, 2016
* Feature: Downloads can now be overridden/updated if the CSV title equals an existing download title.
* Tweak: Properly clearing transients after importing now, fixes bug where downloads needed a re-save after importing.

### 1.2.4: March 4, 2016
* Tweak: Don't parse files on import because they're parsed on download.
* Tweak: Set total version download as download count on import.

### 1.2.3: July 1, 2015
* Tweak: Added the ability to have download and version in 1 row if the file only has a URL.

### 1.2.2: June 23, 2015
* Tweak: Fixed asset enqueue bug.

### 1.2.1: June 22, 2015
* Tweak: Added checks to non mandatory version data, fixes notices.
* Tweak: Now displaying example version data in mapping screen as well.

### 1.2.0: June 10, 2015
* Feature: Added possibility to add custom meta fields to downloads.

### 1.1.0: February 22, 2015
* Feature: Added possibility to import (multiple) versions with all the version meta. Note to existing user: CSV format changed, see: https://www.download-monitor.com/documentation/csv-importer/
* Tweak: Now displaying the amount of rows found at mapping screen.

### 1.0.1: February 6, 2015
* Tweak: Improved mandatory title column check
* Tweak: Fixed missing csv column data checks

### 1.0.0: February 4, 2015
* Initial Release