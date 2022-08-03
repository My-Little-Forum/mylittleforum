my little forum
===============

<a href="https://mylittleforum.net/">my little forum</a> is a simple PHP and MySQL based internet forum that displays the messages in classical threaded view (tree structure). It is Open Source licensed under the GNU General Public License. The main claim of this web forum is simplicity. Furthermore it should be easy to install and run on a standard server configuration with PHP and MySQL.

* [More about my little forum](https://github.com/ilosuna/mylittleforum/wiki)
* [Demo and project discussion forum](https://mylittleforum.net/forum/)

System requirements
-------------------

- Webserver with PHP >= 7.3
- MySQL >= 5.5.3 or MariaDB

Installation
------------

1. Unzip the script package.
2. Upload the complete folder "forum" to your server.
3. Depending on your server configuration the write permissions of the subdirectory templates_c (CHMOD 770, 775 or 777) and the file config/db_settings.php (CHMOD 666) might need to be changed in order that they are writable by the script.
4. Run the installation script by accessing yourdomain.tld/forum/install/ in your web browser and follow the instructions.
5. Remove the directory "install" from your installation of My Little Forum.
6. Change the write permissions for config/db_settings.php to (CHMOD 440), what prevents reading the files content for unauthorised users

Upgrade
--------

1. Download the new package.
2. Unzip the script package.
3. Upload the folder "update" into the main folder of the forum installation.
4. Upload the file "config/VERSION" to the folder "config" of the forum installation.
5. Login as forum administrator and go to the admin area
6. Open the link "Update", you will see a list of available update script files below the instructions. It is possible, that there are more than one items listed, because old, outdated update files never got deleted from the server.
7. Open the link to the currently valid update script.
8. Insert the password of your administrator account to confirm the run of the update script.
9. On the following page you'll get the success message for step one of the update (database operations) or an error message. In case of success you'll see a list of all script files that changed between your and the new version. You have to load up all the listed files and directories to your webspace (this is because not every file got altered with every version). After loading all changed files and directories of the new version to your webspace, you are done. If you encountered errors, please report it instantaneously [in the project forum](https://mylittleforum.net/forum/) or open an [issue on Github](https://github.com/ilosuna/mylittleforum/issues).

