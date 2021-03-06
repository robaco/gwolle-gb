<?php
 /**
 * welcome.php
 * Shows the overview screen with the widget-like windows.
 * Thanks to Alex Rabe for writing clean and understandable code!
 * Also thanks to http://andrewferguson.net/2008/09/26/using-add_meta_box/ !
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}

function gwolle_gb_overview(){

	// Calculate the number of entries
	$count = Array();
	$count['checked']    = gwolle_gb_get_entry_count(array(
			'checked' => 'checked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		));
	$count['unchecked'] = gwolle_gb_get_entry_count(array(
			'checked' => 'unchecked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		));
	$count['spam']    = gwolle_gb_get_entry_count(array( 'spam'  => 'spam'  ));
	$count['trash']   = gwolle_gb_get_entry_count(array( 'trash' => 'trash' ));
	$count['all']     = gwolle_gb_get_entry_count(array( 'all'   => 'all'   ));
	?>

	<div class="table table_content gwolle_gb">
		<h3><?php _e('Overview',GWOLLE_GB_TEXTDOMAIN); ?></h3>

		<table>
			<tbody>
				<tr class="first">
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php">
							<?php echo $count['all']; ?>
						</a>
					</td>

					<td class="t" style="color:#0000f0;">
						<?php
							if ($count['all']==1) {
								_e('Entry total',GWOLLE_GB_TEXTDOMAIN);
							}
							else {
								_e('Entries total',GWOLLE_GB_TEXTDOMAIN);
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=checked">
						<?php echo $count['checked']; ?>
					</a></td>
					<td class="t" style="color:#008000;">
						<?php
							if ($count['checked'] == 1) {
								_e('Unlocked entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('Unlocked entries',GWOLLE_GB_TEXTDOMAIN);;
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=unchecked">
						<?php echo $count['unchecked']; ?>
					</a></td>
					<td class="t" style="color:#ff6f00;">
						<?php
							if ($count['unchecked'] == 1) {
								_e('New entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('New entries',GWOLLE_GB_TEXTDOMAIN);
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=spam">
						<?php echo $count['spam']; ?>
					</a></td>
					<td class="t" style="color:#FF0000;">
						<?php
							if ($count['spam'] == 1) {
								_e('Spam entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('Spam entries',GWOLLE_GB_TEXTDOMAIN);
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=trash">
						<?php echo $count['trash']; ?>
					</a></td>
					<td class="t" style="color:#FF0000;">
						<?php
							if ($count['trash'] == 1) {
								_e('Trashed entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('Trashed entries',GWOLLE_GB_TEXTDOMAIN);
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

			</tbody>
		</table>
	</div><!-- Table-DIV -->
	<div class="versions">
		<p>
			<a class="button rbutton button button-primary" href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/editor.php"><?php _e('Write admin entry',GWOLLE_GB_TEXTDOMAIN); ?></a>
		</p>
	</div>
<?php }


function gwolle_gb_notification() {

	// FIXME: also make it possible for admins to add editors to the list.

	// Check if function mail() exists. If not, display a hint to the user.
	if (!function_exists('mail')) {
		echo '<p class="setting-description">' .
			__('Sorry, but the function <code>mail()</code> required to notify you by mail is not enabled in your PHP configuration. You might want to install a WordPress plugin that uses SMTP instead of <code>mail()</code>. Or you can contact your hosting provider to change this.',GWOLLE_GB_TEXTDOMAIN)
			. '</p>';
	}
	$current_user_id = get_current_user_id();;
	$currentUserNotification = false;
	$user_ids = get_option('gwolle_gb-notifyByMail' );
	if ( strlen($user_ids) > 0 ) {
		$user_ids = explode( ",", $user_ids );
		if ( is_array($user_ids) && !empty($user_ids) ) {
			foreach ( $user_ids as $user_id ) {
				if ( $user_id == $current_user_id ) {
					$currentUserNotification = true;
				}
			}
		}
	} ?>
	<p>
		<form name="gwolle_gb_welcome" method="post" action="">
			<?php
			settings_fields( 'gwolle_gb_options' );
			do_settings_sections( 'gwolle_gb_options' );
			?>
			<input name="notify_by_mail" type="checkbox" id="notify_by_mail" <?php
				if ( $currentUserNotification ) {
					echo 'checked="checked"';
				} ?> >
			<label for="notify_by_mail" class="setting-description"><?php _e('Send me an e-mail when a new entry has been posted.', GWOLLE_GB_TEXTDOMAIN); ?></label>
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save setting', GWOLLE_GB_TEXTDOMAIN); ?>" />
			</p>
		</form>
	</p>
	<div>
		<?php _e('The following users have subscribed to this service:', GWOLLE_GB_TEXTDOMAIN);

		if ( is_array($user_ids) && !empty($user_ids) ) {
			echo '<ul style="font-size:10px;font-style:italic;list-style-type:disc;padding-left:14px;">';
			foreach ( $user_ids as $user_id ) {
				$user_info = get_userdata($user_id);
				if ($user_info === FALSE) {
					// Invalid $user_id
					continue;
				}
				echo '<li>';
				if ( $user_info->ID == get_current_user_id() ) {
					echo '<strong>' . __('You', GWOLLE_GB_TEXTDOMAIN) . '</strong>';
				} else {
					echo $user_info->first_name . ' ' . $user_info->last_name;
				}
				echo ' (' . $user_info->user_email . ')';
				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo '<br /><i>(' . __('No subscriber yet', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
		}
		?>
	</div>
	<?php
}


function gwolle_gb_overview_thanks() {
	echo '
	<ul class="settings">
		<li><a href="http://akismet.com/tos/" target="_blank">Akismet</a></li>
		<li><a href="http://philipwilson.de/" target="_blank">'.__('Icons by',GWOLLE_GB_TEXTDOMAIN).' Philip Wilson</a></li>
		<li><a href="http://www.google.com/recaptcha/intro/index.html" target="_blank">reCAPTCHA</a></li>
	</ul>';
}


function gwolle_gb_overview_help() {
	echo '<h3>
	'.__('This is how you can get your guestbook displayed on your website:', GWOLLE_GB_TEXTDOMAIN).'</h3>
	<ul class="ul-disc">
		<li>'.__('Create a new page.', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__("Choose a title and set &quot;[gwolle_gb]&quot; (without the quotes) as the content.", GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__("It is probably a good idea to disable comments on that page; otherwise, your visitors might get a little confused.",GWOLLE_GB_TEXTDOMAIN).'</li>
	</ul>';
}


function gwolle_gb_overview_help_more() {
	echo '<h3>
	'.__('These entries will be visible for your visitors:', GWOLLE_GB_TEXTDOMAIN).'</h3>
	<ul class="ul-disc">
		<li>'.__('Marked as Checked.', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__('Not marked as Spam.', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__('Not marked as Trash.',GWOLLE_GB_TEXTDOMAIN).'</li>
	</ul>';

	echo '<h3>
	'.__('The Main Menu counter counts the following entries:', GWOLLE_GB_TEXTDOMAIN).'</h3>
	<ul class="ul-disc">
		<li>'.__('Marked as Unchecked (You might want to moderate them).', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__('Not marked as Spam (You might want to check them).', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__('Not marked as Trash (You decide what goes to the trash).',GWOLLE_GB_TEXTDOMAIN).'</li>
	</ul>';
}


function gwolle_gb_donate() {
	echo '
		<div id="gwolle_gb_eff"></div>
		<h3>
		' . __('Donate to the EFF.', GWOLLE_GB_TEXTDOMAIN) . '</h3>
		';

	echo '<p>
		' . __('The Electronic Frontier Foundation is one of the few organisations that wants to keep the internet a free place.', GWOLLE_GB_TEXTDOMAIN) . '</p>
		<p><a href="https://supporters.eff.org/donate" target="_blank" title="' . __('Please donate to the EFF.', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Please donate to the EFF.', GWOLLE_GB_TEXTDOMAIN) . '</a></p>
		';

	echo '
		<h3>
		' . __('Donate to the maintainer.', GWOLLE_GB_TEXTDOMAIN) . '</h3>
		';
	echo '<p>
		' . __('If you rather want to donate to the maintainer of the plugin, you can donate through PayPal.', GWOLLE_GB_TEXTDOMAIN) . '</p>
		<p><a href="https://www.paypal.com" target="_blank" title="' . __('Donate to the maintainer.', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Donate through PayPal to', GWOLLE_GB_TEXTDOMAIN) . '</a> marcel@timelord.nl</p>
		';
}


/* Show the page */
function gwolle_gb_welcome() {

	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		die(__('Cheatin&#8217; uh?', GWOLLE_GB_TEXTDOMAIN));
	}

	/* Save notification setting */
	$saved = false;
	if ( isset( $_POST['option_page']) &&  $_POST['option_page'] == 'gwolle_gb_options' ) {

		// E-mail notification option
		if ( isset($_POST['notify_by_mail']) && $_POST['notify_by_mail'] == 'on' ) {
			// Turn the notification ON for the current user.
			$user_id = get_current_user_id();
			$user_ids = Array();

			$user_ids_old = get_option('gwolle_gb-notifyByMail' );
			if ( strlen($user_ids_old) > 0 ) {
				$user_ids_old = explode( ",", $user_ids_old );
				foreach ( $user_ids_old as $user_id_old ) {
					if ( $user_id_old == $user_id ) {
						continue; // will be added again below the loop
					}
					if ( is_numeric($user_id_old) ) {
						$user_ids[] = $user_id_old;
					}
				}
			}
			$user_ids[] = $user_id;

			$user_ids = implode(",", $user_ids);
			update_option('gwolle_gb-notifyByMail', $user_ids);

			$saved = true;
		} elseif ( !isset($_POST['notify_by_mail']) ) {
			// Turn the notification OFF for the current user
			$user_id = get_current_user_id();
			$user_ids = Array();

			$user_ids_old = get_option('gwolle_gb-notifyByMail' );
			if ( strlen($user_ids_old) > 0 ) {
				$user_ids_old = explode( ",", $user_ids_old );
				foreach ( $user_ids_old as $user_id_old ) {
					if ( $user_id_old == $user_id ) {
						continue;
					}
					if ( is_numeric($user_id_old) ) {
						$user_ids[] = $user_id_old;
					}
				}
			}

			$user_ids = implode(",", $user_ids);
			update_option('gwolle_gb-notifyByMail', $user_ids);
			$saved = true;
		}

	}


	if (get_option('gwolle_gb_version', false) === false) {
		gwolle_gb_installSplash();
	} else {
		add_meta_box('dashboard_right_now', __('Welcome to the Guestbook!',GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview', 'gwolle_gb_welcome', 'left', 'core');
		add_meta_box('gwolle_gb_notification', __('E-mail Notifications', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_notification', 'gwolle_gb_welcome', 'left', 'core');
		add_meta_box('gwolle_gb_thanks', __('This plugin uses the following scripts/programs/images:',GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_thanks', 'gwolle_gb_welcome', 'left', 'core');
		add_meta_box('gwolle_gb_help', __('Help', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_help', 'gwolle_gb_welcome', 'right', 'core');
		add_meta_box('gwolle_gb_help_more', __('Help', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_help_more', 'gwolle_gb_welcome', 'right', 'core');
		add_meta_box('gwolle_gb_donate', __('Donate', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_donate', 'gwolle_gb_welcome', 'right', 'core');

		?>
		<div class="wrap gwolle_gb-wrap">
			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php _e('Gwolle Guestbook', GWOLLE_GB_TEXTDOMAIN); ?></h2>

			<?php
			if ( $saved ) {
				echo '
					<div id="message" class="updated fade">
						<p>' . __('Changes saved.', GWOLLE_GB_TEXTDOMAIN) . '</p>
					</div>';
			} ?>

			<div id="dashboard-widgets-wrap" class="gwolle_gb_welcome">
				<div id="dashboard-widgets" class="metabox-holder">
					<div class="postbox-container" style="width:49%;">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<?php do_meta_boxes('gwolle_gb_welcome', 'left', null); ?>
						</div>
					</div>
					<div class="postbox-container" style="width:49%;">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div id="dashboard-widgets-main-content" class="has-sidebar-content">
								<?php do_meta_boxes('gwolle_gb_welcome', 'right', ''); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}



