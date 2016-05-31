my little forum
===============

<a href="http://mylittleforum.net/">my little forum</a> is a simple PHP and MySQL based internet forum that displays the messages in classical threaded view (tree structure). It is Open Source licensed under the GNU General Public License. The main claim of this web forum is simplicity. Furthermore it should be easy to install and run on a standard server configuration with PHP and MySQL.

System requirements
-------------------

* Webserver with PHP >= 5.2 and MySQL >= 4.1

Installation
------------

1. Unzip the script package.
2. Upload the complete folder "forum" to your server.
3. Depending on your server configuration the write permissions of the subdirectory templates_c (CHMOD 770, 775 or 777) and the file config/db_settings.php (CHMOD 666) might need to be changed in order that they are writable by the script.
4. Run the installation script by accessing http://yourdomain.tld/forum/install/ in your web browser and follow the instructions.

1. Load up the script files to your server
2. Depending on your server configuration you may need to change the write permissions of the following files/directories:
     * **cms/data** - directory of the SQLite database files, needs to be writable by the webserver
     * **content.sqlite**, **entries.sqlite** and **userdata.sqlite** - SQLite database files, need to be writable by the webserver
     * **cms/cache** - cache directory, needs to be writable if you want to use the caching feature
     * **cms/media** and **cms/files** - need to be writable if you want to use the file uploader
3. Ready! You should now be able to access the index page by browsing to the address you uploaded phpSQLiteCMS (e.g. http://example.org/path/to/phpsqlitecms/). To administrate the page, go to http://example.org/path/to/phpsqlitecms/cms/. The default admin userdata is: username: admin, password: admin.
