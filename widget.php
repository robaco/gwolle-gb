<?php
	/*
	 * widget.php
	 * Gwolle-GB widget
	 */

if (function_exists('register_sidebar') && class_exists('WP_Widget')) {
	class GwolleGB_Widget extends WP_Widget {

		/* Constructor */
		function GwolleGB_Widget() {
			/* Widget settings. */
			$widget_ops = array( 'classname' => 'gwolle_gb', 'description' => __('Displays the recent guestbook entries.',GWOLLE_GB_TEXTDOMAIN));

			/* Widget control settings. */
			$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'gwolle_gb-widget' );

			/* Create the widget. */
			$this->WP_Widget( 'gwolle_gb-widget', 'Gwolle-GB', $widget_ops, $control_ops );
		}

		/** @see WP_Widget::widget */
		function widget($args, $instance) {
			global $wpdb;

			// Get the numbers of entries to be shown.
			$num_entries = (isset($instance['num_entries']) && (int)$instance['num_entries'] > 0) ? (int)$instance['num_entries'] : 5;

			// Get the widget title
			$widget_title = (isset($instance['title']) && strlen(trim($instance['title'])) > 0) ? $instance['title'] : FALSE;

			$link_text = (isset($instance['link_text']) && strlen(trim($instance['link_text'])) > 0) ? $instance['link_text'] : __('Visit guestbook', GWOLLE_GB_TEXTDOMAIN);

			// Init
			$widget_html = '';

			$widget_html .= $before_widget;
			$widget_html .= '<div id="datelist">';
			if ($widget_title !== FALSE) {
				$widget_html .= $before_title.'<h2>'.apply_filters('widget_title', $widget_title).'</h2>'.$after_title;
			}

			// Get the latest $num_entries guestbook entries
			$entries = gwolle_gb_get_entries_old(array(
				'show'            => 'checked',
				'num_entries'     => $num_entries,
				'excerpt_length'  => 20
			));
			if ($entries !== FALSE) {
				foreach($entries as $entry) {
					$widget_html .= $entry['excerpt'].'
						<br />
						';
				}
			}

			// Get the ID of the guestbook post and link it.
			/*
			 * FIXME: bring it back with a dropdown with pages in the Widget admin or in the Settings page
			if ((int) $gwolle_gb_post_id > 0) {
				// Build guestbook link
				$gwolle_gb_link = gwolle_gb_get_link(array(
					'post_id' => $gwolle_gb_post_id
				));
				$widget_html .= '
				<div id="gb_link">
					<a href="'.$gwolle_gb_link.'" target="_self" title="'.__('Click here to get to the guestbook.', GWOLLE_GB_TEXTDOMAIN).'"> '.$link_text.' </a>
				</div>';
			}
			$widget_html .= '</div>'.$after_widget;
			*/

			if ($entries !== FALSE) {
				// Only display widget if there are any entries
				echo $widget_html;
			}
		}

		/** @see WP_Widget::update */
		function update($new_instance, $old_instance) {
			return $new_instance;
		}

		/** @see WP_Widget::form */
		function form($instance) {
			$num_entries  = (int)esc_attr($instance['num_entries']);
			$title        = esc_attr($instance['title']);
			$link_text    = esc_attr($instance['link_text']);

			?>
			<p>
				<label for="<?php echo $this -> get_field_id('title'); ?>" /><?php _e('Title:', GWOLLE_GB_TEXTDOMAIN); ?><
				/label>
				<br />
				<input type="text" id="<?php echo $this -> get_field_id('title'); ?>" value="<?php echo $title; ?>" name="<?php echo $this -> get_field_name('title'); ?>" />
				<br />
				<label for="<?php echo $this -> get_field_id('link_text'); ?>" /><?php _e('Link text:', GWOLLE_GB_TEXTDOMAIN); ?><
				/label>
				<br />
				<input type="text" id="<?php echo $this -> get_field_id('link_text'); ?>" value="<?php echo $link_text; ?>" name="<?php echo $this -> get_field_name('link_text'); ?>" />
				<br />
				<label for="<?php echo $this -> get_field_id('num_entries'); ?>" /><?php _e('Number of entries:', GWOLLE_GB_TEXTDOMAIN); ?><
				/label>
				<br />
				<select id="<?php echo $this -> get_field_id('num_entries'); ?>" name="<?php echo $this -> get_field_name('num_entries'); ?>">
					<?php
					for ($i = 1; $i <= 15; $i++) {
						echo '<option value="' . $i . '"';
						if ($i === $num_entries) {
							echo ' selected="selected"';
						}
						echo '>' . $i . '</option>';
					}
					?>
				</select>
			</p>
			<?php
		}

	}

	// FIXME: Disable for now
	// add_action('widgets_init', create_function('', 'return register_widget("GwolleGB_Widget");'));
}

