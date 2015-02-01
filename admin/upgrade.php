<?php
/*
 **	This file is used during activation or upgrading from a prior version.
 **
 **	It uses the PHP function version_compare(). Usage:
 **	version_compare(OLD_VERSION_NUMBER,NEW_VERSION_NUMBER,'<')
 **	=> TRUE, if the old version number is smaller than the new one.
 */


function install_gwolle_gb() {
	global $wpdb;

	// Install the table for the entries

	// Declare database table names
	$wpdb->gwolle_gb_entries = $wpdb->prefix . 'gwolle_gb_entries';
	$wpdb->gwolle_gb_log = $wpdb->prefix . 'gwolle_gb_log';

	$sql = "
		CREATE TABLE
			" . $wpdb->gwolle_gb_entries . "
		(
			id int(10) NOT NULL auto_increment,
			author_name text NOT NULL,
			author_id int(5) NOT NULL default '0',
			author_email text NOT NULL,
			author_origin text NOT NULL,
			author_website text NOT NULL,
			author_ip text NOT NULL,
			author_host text NOT NULL,
			content longtext NOT NULL,
			date varchar(10) NOT NULL,
			ischecked tinyint(1) NOT NULL,
			checkedby int(5) NOT NULL,
			istrash varchar(1) NOT NULL default '0',
			isspam varchar(1) NOT NULL default '0',
			PRIMARY KEY  (id)
		) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
	$result = $wpdb->query($sql);

	$sql = "
		CREATE TABLE
			" . $wpdb->gwolle_gb_log . "
		(
			id int(8) NOT NULL auto_increment,
			subject text NOT NULL,
			entry_id int(5) NOT NULL,
			author_id int(5) NOT NULL,
			date varchar(12) NOT NULL,
			PRIMARY KEY  (id)
		) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci";
	$result = $wpdb->query($sql);

	// Add reCAPTCHA option
	add_option('gwolle_gb-recaptcha-active', 'false');
	add_option('recaptcha-public-key', '');
	add_option('recaptcha-private-key', '');

	//	Add Akismet option
	add_option('gwolle_gb-akismet-active', 'false');

	//	Add moderation option
	add_option('gwolle_gb-moderate-entries', 'true');

	//	Add icon option
	add_option('gwolle_gb-showEntryIcons', 'true');

	//	Add option for the admin mail content (can then be defined by the user)
	add_option('gwolle_gb-adminMailContent', '');

	// Add entries per page option
	add_option('gwolle_gb-entries_per_page', '20');

	//	Add entries per page option
	add_option('gwolle_gb-entriesPerPage', '20');

	//	Add option to toggle the visibility of line breaks
	add_option('gwolle_gb-showLineBreaks', 'false');

	//  Add option to toggle replacing of smilies with graphics.
	add_option('gwolle_gb-showSmilies', 'true');

	//  Add option to toogle linking of author's website
	add_option('gwolle_gb-linkAuthorWebsite', 'true');

	//	Save plugin version to database
	add_option('gwolle_gb_version', GWOLLE_GB_VER);
}


function uninstall_gwolle_gb() {
	// Delete the plugin's tables
	global $wpdb;
	$wpdb->query("DROP TABLE " . $wpdb->gwolle_gb_log . "");
	$wpdb->query("DROP TABLE " . $wpdb->gwolle_gb_entries . "");

	// Delete the plugin's preferences and version-no in the wp-options table
	$wpdb->query("
			DELETE
				FROM " . $wpdb->prefix . "options
			WHERE
				option_name LIKE 'gwolle_gb%'
		");

	if (isset($_POST['delete_recaptchaKeys']) && $_POST['delete_recaptchaKeys'] == 'on' || (get_option('recaptcha-public-key') == '' && get_option('recaptcha-private-key') == '')) {
		// Also delete the reCAPTCHA-keys
		$wpdb->query("
				DELETE
					FROM " . $wpdb->prefix . "options
				WHERE
					option_name = 'recaptcha-public-key'
					OR
					option_name = 'recaptcha-private-key'
			");
	}

	// Deactivate ourselves
	deactivate_plugins( GWOLLE_GB_FOLDER . '/gwolle-gb.php' );
}


function upgrade_gwolle_gb() {
	global $wpdb;
	$installed_ver = get_option('gwolle_gb_version');

	if (version_compare($installed_ver, '0.9', '<')) {
		/*
		 * 0.8 -> 0.9
		 * No changes to the database; just added a few options.
		 */
		add_option('recaptcha-active', 'false');
		add_option('recaptcha-public-key', '');
		add_option('recaptcha-private-key', '');
	}

	if (version_compare($installed_ver, '0.9.1', '<')) {
		/*
		 * 0.9 -> 0.9.1
		 * Moved the email notification options to the WP options table.
		 */
		$notifyUser = "
				SELECT *
				FROM
					" . $wpdb -> prefix . "gwolle_gb_settings
				WHERE
					setting_name = 'notify_by_mail'
					AND
					setting_value = '1'
				";
		$notifySettings = $wpdb->get_results($notifyUser, ARRAY_A);
		foreach ( $notifySettings as $notifySetting ) {
			//	Add an option for each notification subscriber.
			add_option('gwolle_gb-notifyByMail-' . $notifySetting['user_id'], 'true');
		}

		// Delete the old settings table.
		$wpdb->query("
				DROP TABLE
					" . $wpdb->prefix . "gwolle_gb_settings
			");
	}

	if (version_compare($installed_ver, '0.9.2', '<')) {
		/*
		 **	0.9.1->0.9.2
		 **	Renamed the option for toggling reCAPTCHA so that we can
		 **	have different plugins using reCAPTCHA.
		 */
		add_option('gwolle_gb-recaptcha-active', get_option('recaptcha-active'));
		delete_option('recaptcha-active');
	}

	if (version_compare($installed_ver, '0.9.3', '<')) {
		/*
		 **	0.9.2->0.9.3
		 **	Added Akismet integration
		 **	Add an option row and a new column to the entry table
		 **	to be able to mark entries as spam automatically.
		 */
		add_option('gwolle_gb-akismet-active', 'false');
		$wpdb->query("
				ALTER
				TABLE " . $wpdb -> gwolle_gb_entries . "
				ADD
					entry_isSpam
						VARCHAR( 1 )
						NOT NULL
						DEFAULT '0'
						AFTER entry_isDeleted
			");
	}

	if (version_compare($installed_ver, '0.9.4', '<')) {
		/*
		 **	0.9.3->0.9.4
		 **	added access-level, no-mail-on-spam, moderate on/off.
		 */
		add_option('gwolle_gb-access-level', '10');
		add_option('gwolle_gb-moderate-entries', 'true');

		$emailNotification = "
				SELECT *
				FROM
					" . $wpdb -> prefix . "options
				WHERE
					option_name LIKE 'gwolle_gb-notifyByMail-%'
				";
		$notifications = $wpdb->get_results($emailNotification, ARRAY_A);
		foreach ( $notifications as $notification ) {
			add_option('gwolle_gb-notifyAll-' . str_replace('gwolle_gb-notifyByMail-', '', $notification['option_name']), 'true');
		}
	}

	if (version_compare($installed_ver, '0.9.4.1', '<')) {
		/*
		 **	0.9.4->0.9.4.1
		 **	Caching the Wordpress API key so that we don't need to
		 **	validate it each time the user opens the settings panel.
		 **	Also, add an option to show icons in the entry list.
		 */
		add_option('gwolle_gb-wordpress-api-key', get_option('wordpress_api_key'));
		add_option('gwolle_gb-showEntryIcons', 'true');
	}

	if (version_compare($installed_ver, '0.9.4.2', '<')) {
		/*
		 **	0.9.4.1->0.9.4.2
		 **	Added the possibility to specify the content of the mail
		 **	a subscriber of the mail notification gets.
		 **	Also, added an option to turn the version-check on/off
		 **	and the possibility to set the numbers of entries per page.
		 */
		add_option('gwolle_gb-adminMailContent', '');
		if (function_exists('file') && get_cfg_var('allow_url_fopen')) {
			$default = 'true';
		} else {
			$default = 'false';
		}
		add_option('gwolle_gb-autoCheckVersion', $default);
		add_option('gwolle_gb-entriesPerPage', '20');
	}

	if (version_compare($installed_ver, '0.9.4.2.1', '<')) {
		/*
		 **	0.9.4.2->0.9.4.2.1
		 **	Removed the version check because of some problems.
		 */
		delete_option('gwolle_gb-autoCheckVersion');
	}

	if (version_compare($installed_ver, '0.9.4.3', '<')) {
		/*
		 **	0.9.4.2.1->0.9.4.3
		 **	Added option to manually set link to the guestbook.
		 */
		add_option('gwolle_gb-guestbookLink');
	}

	if (version_compare($installed_ver, '0.9.4.5', '<')) {
		/*
		 **	0.9.4.2.3->0.9.4.5
		 **	Support for Croation chars.
		 **	Added option to toggle line breaks-visibility.
		 */
		$wpdb->query("
				ALTER
				TABLE " . $wpdb->gwolle_gb_entries . "
					DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
			");
		$wpdb->query("
				ALTER
				TABLE " . $wpdb->gwolle_gb_entries . "
					CHANGE `entry_id` `entry_id` INT(10) NOT NULL AUTO_INCREMENT,
					CHANGE `entry_author_name` `entry_author_name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_authorAdminId` `entry_authorAdminId` INT(5) NOT NULL DEFAULT '0',
					CHANGE `entry_author_email` `entry_author_email` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_author_origin` `entry_author_origin` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_author_website` `entry_author_website` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_author_ip` `entry_author_ip` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_author_host` `entry_author_host` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_content` `entry_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_date` `entry_date` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_isChecked` `entry_isChecked` TINYINT(1) NOT NULL,
					CHANGE `entry_checkedBy` `entry_checkedBy` INT(5) NOT NULL,
					CHANGE `entry_isDeleted` `entry_isDeleted` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
					CHANGE `entry_isSpam` `entry_isSpam` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0'
			");
		add_option('gwolle_gb-showLineBreaks', 'false');
	}

	if (version_compare($installed_ver, '0.9.4.6', '<')) {
		/*
		 **  0.9.4.5->0.9.4.6
		 **  Added option to show/hide text before/after [gwolle_gb] tag.
		 */
		add_option('gwolle_gb-guestbookOnly', 'true');
	}

	if (version_compare($installed_ver, '0.9.5', '<')) {
		/*
		 **  0.9.4.6->0.9.5
		 **  Added option to toggle check for import data.
		 */
		add_option('gwolle_gb-checkForImport', 'true');
	}

	if (version_compare($installed_ver, '0.9.6', '<')) {
		/**
		 * 0.9.5->0.9.6
		 * Added the following options:
		 * - toggle replacing of smilies
		 * - toggle link to author's website
		 */
		add_option('gwolle_gb-showSmilies', 'true');
		add_option('gwolle_gb-linkAuthorWebsite', 'true');
	}

	if (version_compare($installed_ver, '0.9.9.1', '<')) {
		/*
		 *  0.9.8.1->0.9.9.0
		 *  Removed the access level option, use standard WordPress capabilities.
		 *  Save Users that are subcribed to notification mails in an option with the array of user_id's
		 */
		delete_option('gwolle_gb-access-level');

		// Get users from database who have subscribed to the notification service.
		$sql = "
				SELECT *
				FROM
					" . $wpdb->prefix . "options
				WHERE
					option_name LIKE 'gwolle_gb-notifyByMail-%'
				ORDER BY
					option_name
			";
		$notifyUser_result = $wpdb->get_results($sql, ARRAY_A);
		if ( count($notifyUser_result) > 0 ) {
			$user_ids = Array();
			foreach ( $notifyUser_result as $option ) {
				$user_id = (int) str_replace('gwolle_gb-notifyByMail-', '', $option['option_name']);
				$user_info = get_userdata($user_id);
				if ($user_info === FALSE) {
					// Invalid $user_id
					continue;
				}
				if ($user_id > 0) {
					$user_ids[] = $user_id;
				}
			}
			$user_ids = implode(",", $user_ids);
			update_option('gwolle_gb-notifyByMail', $user_ids);
		}
	}

	if (version_compare($installed_ver, '0.9.9.2', '<')) {
		/*
		 *  0.9.9.1->0.9.9.2
		 *  Remove the options of Users that are subcribed to notification mails
		 */
		$wpdb->query("
				DELETE
					FROM " . $wpdb->prefix . "options
				WHERE
					option_name LIKE 'gwolle_gb-notifyByMail-%'
				OR
					option_name LIKE 'gwolle_gb-notifyAll-%'
			");
	}

	if (version_compare($installed_ver, '1.0.5', '<')) {
		/*
		 *  1.0.4->1.0.5
		 *  Remove obsolete options
		 */
		delete_option('gwolle_gb-access-level');
		delete_option('gwolle_gb-checkForImport');
		delete_option('gwolle_gb-post_ID');

		/* Alter table of logs */
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_log
				CHANGE log_id id int(8) NOT NULL auto_increment,
				CHANGE log_subject subject text NOT NULL,
				CHANGE log_subjectId entry_id int(5) NOT NULL,
				CHANGE log_authorId author_id int(5) NOT NULL,
				CHANGE log_date date varchar(12) NOT NULL
			");
	}

	if (version_compare($installed_ver, '1.0.6', '<')) {
		/*
		 * 1.0.5->1.0.6
		 * Alter table of entries
		 */
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_entries
				CHANGE `entry_id` `id` INT(10) NOT NULL AUTO_INCREMENT,
				CHANGE `entry_author_name` `author_name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				CHANGE `entry_authorAdminId` `author_id` INT(5) NOT NULL DEFAULT '0',
				CHANGE `entry_author_email` `author_email` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				CHANGE `entry_author_origin` `author_origin` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				CHANGE `entry_author_website` `author_website` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				CHANGE `entry_author_ip` `author_ip` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				CHANGE `entry_author_host` `author_host` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				CHANGE `entry_content` `content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				CHANGE `entry_date` `date` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				CHANGE `entry_isChecked` `ischecked` TINYINT(1) NOT NULL,
				CHANGE `entry_checkedBy` `checkedby` INT(5) NOT NULL,
				CHANGE `entry_isDeleted` `istrash` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
				CHANGE `entry_isSpam` `isspam` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0'
			");
	}

	if (version_compare($installed_ver, '1.1.3', '<')) {
		/*
		 * 1.1.2->1.1.3
		 */
		delete_option('gwolle_gb-guestbookOnly');
		delete_option('gwolle_gb-defaultMailText');
	}

	// Update the plugin version option
	update_option('gwolle_gb_version', GWOLLE_GB_VER);
}

