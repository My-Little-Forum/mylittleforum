/*
This file documentates the database changes in the different versions of my
little forum. The relevant parts can be used for manual database updates.
*/
-- 2.0 beta 1 to 2.0 beta 4
/*
ALTER TABLE mlf2_entries ADD tags varchar(255) NOT NULL AFTER text;
ALTER TABLE mlf2_entries ADD spam tinyint(4) DEFAULT '0';
ALTER TABLE mlf2_entries ADD spam_check_status tinyint(4) DEFAULT '0';
INSERT INTO mlf2_settings VALUES ('save_spam', '1');
INSERT INTO mlf2_settings VALUES ('auto_delete_spam', '168');
*/
--------------------------------------------------------------------------------
-- 2.0 beta 4 to 2.0 beta 5
/*
CREATE TABLE mlf2_pages (id int(11) NOT NULL AUTO_INCREMENT, order_id int(11) NOT NULL, title varchar(255) NOT NULL, content text NOT NULL, menu_linkname varchar(255) NOT NULL, access tinyint(4) NOT NULL DEFAULT '0', PRIMARY KEY (id));
*/
--------------------------------------------------------------------------------
-- 2.0 beta 5 to 2.0 beta 6
/*
ALTER TABLE mlf2_entries CHANGE last_answer last_reply timestamp NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE mlf2_entries CHANGE fixed sticky tinyint(4) DEFAULT '0';
ALTER TABLE mlf2_entries CHANGE place location varchar(255) NOT NULL;
ALTER TABLE mlf2_entries CHANGE email_notify email_notification tinyint(4) DEFAULT '0';
ALTER TABLE mlf2_userdata user_place user_location varchar(255) NOT NULL;
*/
--------------------------------------------------------------------------------
-- 2.0 beta 6 to 2.0 beta 7
/*
ALTER TABLE mlf2_userdata ADD auto_login_code varchar(255) NOT NULL AFTER user_lock;
*/
--------------------------------------------------------------------------------
-- 2.0 beta 7 to 2.0 beta 10
/*
CREATE TABLE mlf2_logincontrol (time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, ip varchar(255) NOT NULL, logins int(11) NOT NULL DEFAULT '0');
INSERT INTO mlf2_settings VALUES ('temp_block_ip_after_repeated_failed_logins', '1');
*/
--------------------------------------------------------------------------------
-- 2.0 beta 10 to 2.0 beta 11
/*
CREATE TABLE mlf2_entries_cache (cache_id int(11) NOT NULL, cache_text text NOT NULL, PRIMARY KEY (cache_id));
CREATE TABLE mlf2_userdata_cache (cache_id int(11) NOT NULL, cache_signature text NOT NULL, cache_profile text NOT NULL, PRIMARY KEY (cache_id));
*/
--------------------------------------------------------------------------------
-- 2.0 beta 11 to 2.0 beta 14
/*
INSERT INTO mlf2_settings VALUES ('avatars', '0');
INSERT INTO mlf2_settings VALUES ('avatar_max_filesize','20');
INSERT INTO mlf2_settings VALUES ('avatar_max_width','80');
INSERT INTO mlf2_settings VALUES ('avatar_max_height','80');
UPDATE mlf2_settings SET value = '2.0 beta 14' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 11 to 2.0 beta 14
/*
UPDATE mlf2_settings SET name = 'email_notification_unregistered' WHERE name = 'email_notification';
ALTER TABLE mlf2_userdata ADD thread_display TINYINT NOT NULL DEFAULT '0' AFTER fold_threads;
UPDATE mlf2_settings SET value = '2.0 beta 15' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 15 to 2.0 beta 16
/*
ALTER TABLE mlf2_userdata CHANGE time_difference time_difference SMALLINT NULL DEFAULT '0';
UPDATE mlf2_userdata SET time_difference = time_difference*60;
UPDATE mlf2_settings SET value = value*60 WHERE name = 'time_difference';
INSERT INTO mlf2_settings VALUES ('captcha_posting', '0');
INSERT INTO mlf2_settings VALUES ('captcha_email', '0');
INSERT INTO mlf2_settings VALUES ('captcha_register', '0');
ALTER TABLE mlf2_entries ADD edit_key varchar(255) NOT NULL default '';
UPDATE mlf2_settings SET value = '2.0 beta 16' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 16 to 2.0 beta 17
/*
UPDATE mlf2_settings SET name = 'forum_enabled', value = '1' WHERE name = 'forum_disabled';
UPDATE mlf2_settings SET name = 'register_mode', value = '0' WHERE name = 'register_by_admin_only';
UPDATE mlf2_settings SET value = '2.0 beta 17' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 17 to 2.0 beta 18
/*
INSERT INTO mlf2_settings VALUES ('user_edit_if_no_replies', '0');
UPDATE mlf2_settings SET value = '2.0 beta 18' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 18 to 2.0 beta 19
/*
INSERT INTO mlf2_settings VALUES ('edit_min_time_period', '5');
INSERT INTO mlf2_settings VALUES ('edit_max_time_period', '60');
UPDATE mlf2_settings SET value = '2.0 beta 19' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 19 to 2.0 beta 20
-- CAUTION: edited_by values will be lost! Use update script to keep them.
/*
ALTER TABLE mlf2_entries CHANGE edited_by edited_by VARCHAR(255) NULL;
UPDATE mlf2_entries SET time=time,last_reply=last_reply,edited=edited,edited_by=NULL;
ALTER TABLE mlf2_entries CHANGE edited_by edited_by int(11) default NULL;
INSERT INTO mlf2_settings VALUES ('forum_disabled_message', '');
UPDATE mlf2_settings SET value = '2.0 beta 20' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 20 to 2.0 beta 21
/*
INSERT INTO mlf2_settings VALUES ('default_email_contact', '0');
UPDATE mlf2_settings SET value = '2.0 beta 21' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 21 to 2.0 beta 22
/*
UPDATE mlf2_settings SET value = '2.0 beta 22' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 22 to 2.0 beta 23
/*
UPDATE mlf2_settings SET value = '2.0 beta 23' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 23 to 2.0 beta 24
/*
UPDATE mlf2_settings SET value = '2.0 beta 24' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 24 to 2.0 beta 25
/*
INSERT INTO mlf2_settings VALUES ('min_pw_length', '8');
UPDATE mlf2_settings SET value = '2.0 beta 25' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 25 to 2.0 beta 26
/*
UPDATE mlf2_settings SET value = '2.0 beta 26' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 beta 26 to 2.0 RC 1
/*
UPDATE mlf2_settings SET value = '2.0 RC 1' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 RC 1 to 2.0 RC 2
/*
UPDATE mlf2_settings SET value = '2.0 RC 2' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 RC 2 to 2.0 RC 3
/*
UPDATE mlf2_settings SET value = '10' WHERE name = 'count_users_online';
UPDATE mlf2_settings SET value = '2.0 RC 3' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 RC 3 to 2.0 RC 4
/*
INSERT INTO mlf2_settings VALUES ('email_subject_maxlength', '100');
INSERT INTO mlf2_settings VALUES ('email_text_maxlength', '10000');
UPDATE mlf2_settings SET value = '2.0 RC 4' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 RC 4 to 2.0 RC 5
/*
INSERT INTO mlf2_settings VALUES ('forum_readonly', '0');
INSERT INTO mlf2_settings VALUES ('tags', '1');
UPDATE mlf2_settings SET value = '2.0 RC 5' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 RC 5 to 2.0 RC 6
/*
UPDATE mlf2_settings SET value = '2.0 RC 6' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 RC 6 to 2.0 RC 7
/*
UPDATE mlf2_settings SET value = '2.0 RC 7' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 RC 7 to 2.0 RC 8
/*
UPDATE mlf2_settings SET value = '2.0 RC 8' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 RC 8 to 2.0
/*
UPDATE mlf2_settings SET value = '2.0' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0 to 2.0.1
/*
UPDATE mlf2_settings SET value = '2.0.1' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0.1 to 2.0.2
/*
UPDATE mlf2_settings SET value = '2.0.2' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.0.* to 2.1
/*
ALTER TABLE mlf2_userdata ADD category_selection VARCHAR(255) NULL DEFAULT NULL AFTER registered;
ALTER TABLE mlf2_entries_cache CHANGE cache_text cache_text MEDIUMTEXT NOT NULL;
INSERT INTO mlf2_settings VALUES ('timezone', '');
INSERT INTO mlf2_settings VALUES ('bbcode_flash', '0');
INSERT INTO mlf2_settings VALUES ('flash_default_width', '425');
INSERT INTO mlf2_settings VALUES ('flash_default_height', '344');
INSERT INTO mlf2_settings VALUES ('cookie_validity_days', '30');
INSERT INTO mlf2_settings VALUES ('access_permission_checks', '1');
INSERT INTO mlf2_settings VALUES ('daily_actions_time', '3:30');
INSERT INTO mlf2_settings VALUES ('next_daily_actions', '0');
INSERT INTO mlf2_settings VALUES ('auto_lock_old_threads', '0');
UPDATE mlf2_settings SET value = '2.1' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.1 to 2.1.1
/*
UPDATE mlf2_settings SET value = '2.1.1' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.1.1 to 2.1.2
/*
UPDATE mlf2_settings SET value = '2.1.2' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.1.2 to 2.1.3
/*
UPDATE mlf2_settings SET value = '2.1.3' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.1.3 to 2.1.4
/*
UPDATE mlf2_settings SET value = '2.1.4' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.1.4 to 2.2
/*
ALTER TABLE mlf2_userdata DROP time_difference;
ALTER TABLE mlf2_userdata ADD language VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE mlf2_userdata ADD time_zone VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE mlf2_userdata ADD time_difference smallint(4) default '0';
ALTER TABLE mlf2_userdata ADD theme VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE mlf2_userdata ADD entries_read TEXT NOT NULL;
INSERT INTO mlf2_settings VALUES ('max_read_items', '200');
INSERT INTO mlf2_settings VALUES ('delete_ips', '0');
INSERT INTO mlf2_settings VALUES ('bbcode_tex', '0');
INSERT INTO mlf2_settings VALUES ('last_changes', '0');
INSERT INTO mlf2_settings VALUES ('ajax_preview', '1');
UPDATE mlf2_settings SET name = 'theme' WHERE name = 'template';
UPDATE mlf2_settings SET name = 'time_zone' WHERE name = 'timezone';
UPDATE mlf2_settings SET value = '0' WHERE name = 'time_difference';
UPDATE mlf2_settings SET value = '2.2' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.2 to 2.2.1
/*
UPDATE mlf2_settings SET value = '2.2.1' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.2.1 to 2.2.2
/*
UPDATE mlf2_settings SET value = '2.2.2' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.2.2 to 2.2.3
/*
UPDATE mlf2_settings SET value = '2.2.3' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.2.3 to 2.2.4
/*
UPDATE mlf2_settings SET value = '2.2.4' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.2.4 to 2.2.5
/*
UPDATE mlf2_settings SET value = '2.2.5' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.2.5 to 2.2.6
/*
UPDATE mlf2_settings SET value = '2.2.6' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.2.6 to 2.2.7
/*
UPDATE mlf2_settings SET value = '2.2.7' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.2.7 to 2.2.8
/*
UPDATE mlf2_settings SET value = '2.2.8' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.2.8 to 2.3
/*
INSERT INTO mlf2_settings VALUES ('akismet_check_registered', '0');
UPDATE mlf2_settings SET value = '2.3' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.3 to 2.3.1
/*
INSERT INTO mlf2_settings VALUES ('stop_forum_spam', '0');
UPDATE mlf2_settings SET value = '2.3.1' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.3.1 to 2.3.2
/*
UPDATE mlf2_settings SET value = '2.3.2' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.3.2 to 2.3.3
/*
UPDATE mlf2_settings SET value = '2.3.3' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.3.3 to 2.3.4
/*
UPDATE mlf2_settings SET value = '2.3.4' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.3.4 to 2.3.5 RC
/*
UPDATE mlf2_settings SET value = '2.3.5 RC' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.3.5 to 2.3.6.1
/*
ALTER TABLE mlf2_userdata CHANGE last_login last_login timestamp NULL default CURRENT_TIMESTAMP;
UPDATE mlf2_settings SET value = '2.3.6.1' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.3.6.1 to 2.3.7
/*
UPDATE mlf2_settings SET value = 10 WHERE name = 'temp_block_ip_after_repeated_failed_logins' AND value > 0
UPDATE mlf2_settings SET value = '2.3.7' WHERE name = 'version';
*/

-- 2.3.7 to 2.4.0
/*
CREATE TABLE mlf2_bookmarks (id int(11) NOT NULL AUTO_INCREMENT,user_id int(11) NOT NULL,posting_id int(11) NOT NULL,time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,subject varchar(255) NOT NULL,order_id int(11) NOT NULL DEFAULT '0',PRIMARY KEY (id),UNIQUE KEY UNIQUE_uid_pid (user_id,posting_id));
CREATE TABLE mlf2_read_entries (user_id int(11) UNSIGNED NOT NULL, posting_id int(11) UNSIGNED NOT NULL, time timestamp NOT NULL, PRIMARY KEY (user_id, posting_id)) CHARSET=utf8 COLLATE=utf8_general_ci;
CREATE TABLE mlf2_temp_infos (name varchar(50) NOT NULL, value varchar(255) NOT NULL, time timestamp NULL DEFAULT NULL, PRIMARY KEY (name)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;
UPDATE mlf2_settings SET value = '2.4.0' WHERE name = 'version';
*/
--------------------------------------------------------------------------------
-- 2.4.2 to 2.4.3
/*
ALTER TABLE mlf2_userdata MODIFY `user_name` varchar(255) COLLATE utf8_bin;
ALTER TABLE mlf2_entries ADD INDEX(`spam`);
ALTER TABLE mlf2_entries ADD INDEX(`user_id`);
ALTER TABLE mlf2_entries ADD INDEX(`time`);
ALTER TABLE mlf2_entries ADD INDEX(`last_reply`);
ALTER TABLE mlf2_read_entries ADD INDEX(`user_id`);
ALTER TABLE mlf2_read_entries ADD INDEX(`posting_id`);
ALTER TABLE mlf2_userdata ADD INDEX(`user_type`);
ALTER TABLE mlf2_userdata ADD INDEX(`user_name`);
INSERT INTO mlf2_settings VALUES (`read_state_expiration_value`, '500');
INSERT INTO mlf2_settings VALUES (`read_state_expiration_method`, '0');
DELETE FROM mlf2_settings WHERE name = 'max_read_items'
DELETE FROM mlf2_settings WHERE name = 'read_state_expiration_date'
*/
--------------------------------------------------------------------------------
-- 2.4.6 to 2.4.7
/*
CREATE TABLE mlf2_bookmark_tags (`bid` int(11) NOT NULL, `tid` int(11) NOT NULL, PRIMARY KEY (`bid`,`tid`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;
CREATE TABLE mlf2_entry_tags (`bid` int(11) NOT NULL, `tid` int(11) NOT NULL, PRIMARY KEY (`bid`,`tid`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;
CREATE TABLE mlf2_tags (`id` int(11) NOT NULL AUTO_INCREMENT, `tag` varchar(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE KEY `tag` (`tag`)) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_general_ci;
*/
--------------------------------------------------------------------------------
-- 2.4.8 to 2.4.9
/*
*/
--------------------------------------------------------------------------------
-- 2.4.9 to 2.4.10
/*
ALTER TABLE mlf2_userdata ADD tou_accepted DATETIME NULL DEFAULT NULL, ADD dps_accepted DATETIME NULL DEFAULT NULL;
INSERT INTO mlf2_settings VALUES ('data_privacy_agreement', '0');
INSERT INTO mlf2_settings VALUES ('data_privacy_statement_url', '');
*/
--------------------------------------------------------------------------------
-- 2.4.13 to 2.4.14
/*
CREATE TABLE `mlf2_subscriptions` (`user_id` int(12) UNSIGNED NOT NULL, `eid` int(12) UNSIGNED NOT NULL, `unsubscribe_code` varchar(36) NOT NULL, `tstamp` datetime DEFAULT NULL, PRIMARY KEY `user_thread` (`user_id`,`eid`), KEY `hash` (`unsubscribe_code`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE mlf2_entries DROP COLUMN `email_notification`;
*/
--------------------------------------------------------------------------------
-- 2.4.16 to 2.4.17
/*
INSERT INTO `mlf2_settings` (`name`, `value`) VALUES ('min_posting_time', '5'), ('min_register_time', '5'), ('min_email_time', '5'), ('max_posting_time', '10800'), ('max_register_time', '10800'), ('max_email_time', '10800');
*/
--------------------------------------------------------------------------------
-- 2.4.17 to 2.4.18
/*
ALTER TABLE `mlf2_subscriptions` DROP PRIMARY KEY;
ALTER TABLE `mlf2_subscriptions` CHANGE `user_id` `user_id` int UNSIGNED NULL;
ALTER TABLE `mlf2_subscriptions` ADD UNIQUE INDEX `user_thread` (`user_id`, `eid`);
ALTER TABLE `mlf2_subscriptions` ADD INDEX `entry` (`eid`);
*/
--------------------------------------------------------------------------------
-- 2.4.18 or 2.4.18.1 to 2.4.19
/*
INSERT INTO `mlf2_temp_infos` (`name`, `value`) VALUES ('access_permission_checks', '0'), ('last_changes', '0'), ('next_daily_actions', '0'), ('version', '2.4.19'); --combined query for relocated settings and for new version string 
DELETE FROM `mlf2_settings` WHERE name IN('access_permission_checks', 'last_changes', 'next_daily_actions', 'version')
*/
-- to 2.5
/*
INSERT INTO mlf2_settings (`name`, `value`) VALUES ('uploads_per_page', '20');
INSERT INTO `mlf2_settings` (`name`, `value`) VALUES ('bbcode_latex', '0'), ('bbcode_latex_uri', 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS_CHTML.js');
*/
