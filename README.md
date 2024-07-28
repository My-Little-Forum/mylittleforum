# my little forum

[my little forum](https://mylittleforum.net/) is a simple PHP and MySQL based internet forum that displays the messages in classical threaded view (tree structure). It is Open Source licensed under the GNU General Public License. The main claim of this web forum is simplicity. Furthermore it should be easy to install and run on a standard server configuration with PHP and MySQL.

* [More about my little forum](https://github.com/ilosuna/mylittleforum/wiki)
* [Demo and project discussion forum](https://mylittleforum.net/forum/)

## System requirements

- Webserver with PHP >= 7.3
- MySQL >= 5.7.7 or MariaDB >= 10.2.2

## Features

### General

- thread based forum script
- optional restriction of access to writing and/or reading entries to registered users only
- user management
- categories
- forum script is highly configurable
- theming support, using [Smarty](https://www.smarty.net/) as template language
- data storage in a MySQL or MariaDB database
- currently 14 available languages (more or less complete) with the strings for the user interface
    - arabian (beta)
    - simplified chinese
    - traditional chinese
    - croatian
    - danish
    - english (default if not set otherwise during the installation)
    - french
    - german
    - italian
    - norwegian
    - russian
    - spanish
    - swedish
    - tamil
    - turkish
- since version 20220508.1 (2.5.0) the forum can store the whole utf-8-range including emojis 🎉
- formatting of entries with BB-codes, most BB-codes are accessible by buttons, the system is extendable
    - common text formatting (bold, italic, strike through and so on)
    - coloured text, text size
    - links
    - images
    - code exapmles
    - preformatted text
    - mathematical formulas, realised with LaTex (optional with including the MathJax library)

### Main views

- paginated main view with a configurable count of threads per page
- general and user based configuration for a thread view or a table view, second looking more message board like
- optional list of latest X entries
- optional tag cloud
- management functions for administrators and moderators

### Forum entries

- allowing or forbidding creation of forum posts by unregistered users (restricting it to registered users only)
- allowing or forbidding time based editing of forum posts after their initial saving
- displaying the time of the last editing and the editors user name of a posting, optionally hiding it in case of editing by a moderator or administrator

### Entry view

- three possible views of forum entries
    - single entry view with the thread structure shown like in the main views below the entry
    - nested entry view with all entries of the thread indented according to their thread nesting level
    - flat entry view ordered by their posting dates like in a message board

### Categories

- optional creation of categories
- restricting access to certain categories to registered users or to administrators and moderators
- management of categories
    - sorting of the existing categories for the selection in the user interface
    - renaming a category
    - deleting an category
    - changing the access restrictions

### Spam prevention

- optional Bayed based content categorisation as ham or spam for forum posts and/or e-mails, to be sent over the contact form (*local service*)
- optional spam prevention with Bad behavior (*local service*)
- optional bad word list (*local service*)
- optional blacklist for certain IPs and IP-ranges (*local service*)
- optional blacklist for user agents (*local service*)
- optional check of e-mail-addresses during the registration process with Stop Forum Spam (*external service*)
- optional content check of forum posts and/or e-mails, to be sent over the contact form, with Akismet (*external service*)
- perform the activated checks only for content of unregistered visitors or also for content of registered users (if check is applicable)

### User account management

- optional user registration
- options to registering an account by one self or by restricting the registration to be done by an administrator
- enforcement of a consent to the terms of use and/or the privacy policy, date of consent will be saved with the user data
- enforcement of a renewed consent in case of changes in one of these documents
- in general three possible user ranks (beside unregistered visitors) with different permissions and restrictions
    - registered user
    - moderator
    - administrator
- user profile with optional …
    - … avatar
    - … signature
    - … profile information
    - … website
    - … location
    - … birthday
    - … sex/gender
- technical user settings
    - password
    - e-mail-address
    - deleting the account
    - extent of e-mail-contact
        - user is contactable only by the forum team
        - user is contactable by all registered users
        - or the whole forum audience
    - user based category selection (if categories are defined)
    - user based choice of the user interface language
    - user based choice of the time zone
    - user based choice of how links are opened
        - open all links based on the forum setting (set by the forum administrator)
        - open all links in the currently active browser window/tab
        - open only links to external sites in a new browser window/tab
        - open all links in a new browser window/tab
    - for moderators and administrators: e-mail-notification about new forum posts and/or registration of new users
- for administrators: separate user management list with the following functions
    - adding new users
    - editing the data of a single user
    - deleting single users
    - deleting uxsers according to definable criteria
    - reset previous consents to the terms of use and/or the privacy policy because of changes in one or the other document

### Additional pages

- creation of website pages as supplement to the forum, in example a help page, the terms of use or the privacy policy
- formatting the pages content with HTML and the CSS rules of the applied forum theme
- pages have a fix URL and a link can optionally be displayed in the user menu

## Installation

1. Unzip the script package.
2. Upload the complete folder "forum" to your server.
3. Depending on your server configuration the write permissions of the subdirectory templates_c (CHMOD 770, 775 or 777) and the file config/db_settings.php (CHMOD 666) might need to be changed in order that they are writable by the script.
4. Run the installation script by accessing yourdomain.tld/forum/install/ in your web browser and follow the instructions.
5. Remove the directory "install" from your installation of My Little Forum.
6. Change the write permissions for config/db_settings.php to (CHMOD 440), what prevents reading the files content for unauthorised users

## Upgrade

1. Download the new package.
2. Unzip the script package.
3. Upload the folder "update" into the main folder of the forum installation.
4. Upload the file "config/VERSION" to the folder "config" of the forum installation. An existing file VERSION will be overwritten.
5. Login as forum administrator and go to the admin area
6. Open the link "Update", you will see a list of available update script files below the instructions. It is possible, that there are more than one items listed, because old, outdated update files never got deleted from the server.
7. Open the link to the currently valid update script.
8. Insert the password of your administrator account to confirm the run of the update script.
9. On the following page you'll get the success message for step one of the update (database operations) or an error message. In case of success you'll see a list of all script files that changed between your and the new version. You have to load up all the listed files and directories to your webspace (this is because not every file got altered with every version). After loading all changed files and directories of the new version to your webspace, you are done. If you encountered errors, please report it instantaneously [in the project forum](https://mylittleforum.net/forum/) or open an [issue on Github](https://github.com/ilosuna/mylittleforum/issues).

