<?php
defined('RY_PE_VERSION') OR exit('No direct script access allowed');

class RY_PE_admin {
	private static $initiated = FALSE;

	public static function init() {
		if(!self::$initiated) {
			self::$initiated = true;

			self::init_hooks();
		}
	}

	private static function init_hooks() {
		load_plugin_textdomain(RY_PE::$textdomain, false, dirname(RY_PE_PLUGIN_BASENAME) . '/languages' );

		add_action('post_submitbox_misc_actions', array('RY_PE_admin', 'post_submitbox_misc_actions'));
		add_action('save_post', array('RY_PE_admin', 'save_post'), 10, 2);
		add_filter('manage_pages_columns', array('RY_PE_admin', 'pages_columns'));
		add_filter('manage_posts_columns', array('RY_PE_admin', 'posts_columns'), 10, 2);
		add_action('manage_pages_custom_column', array('RY_PE_admin', 'custom_column'), 10, 2);
		add_action('manage_posts_custom_column', array('RY_PE_admin', 'custom_column'), 10, 2);

		add_action('admin_menu', array('RY_PE_admin', 'admin_menu'));
		add_filter('plugin_action_links', array('RY_PE_admin', 'plugin_action_links'), 10, 2);

		wp_register_script('post_exptime', RY_PE_PLUGIN_URL . 'js/post.js', array('post'), false, 1);
	}

	public static function post_submitbox_misc_actions() {
		global $action, $post, $wp_locale;

		if( !in_array($post->post_type, RY_PE::get_option('support_post_type', array())) ) {
			return;
		}

		wp_enqueue_script('post_exptime');
		if( 0 != $post->ID ) {
			$exptime = RY_PE::get_post_meta($post->ID, 'expiration_time', TRUE);
			if( empty($exptime) ) {
				$date = __('Never');
				$edit = 0;
			} else {
				$date = date_i18n(__( 'M j, Y @ G:i' ), strtotime($exptime));
				$edit = 1;
			}
		} else {
			$date = __('Never');
		}	
		?>
		<div class="misc-pub-section curtime misc-pub-exptime">
			<span id="timestamp"><?=__('Expires on: ', RY_PE::$textdomain) ?><b id="exptimestamp"><?=$date; ?></b></span>
			<a href="#edit_timestamp" class="edit-exptimestamp hide-if-no-js">
				<span aria-hidden="true"><?php _e('Edit'); ?></span>
				<span class="screen-reader-text"><?php _e('Edit date and time'); ?></span>
			</a>
			<div id="exptimestampdiv" class="hide-if-js">
				<?php
				$curtime = current_time('timestamp');
				$jj = ($edit) ? mysql2date('d', $exptime, false) : gmdate('d', $curtime);
				$mm = ($edit) ? mysql2date('m', $exptime, false) : gmdate('m', $curtime);
				$aa = ($edit) ? mysql2date('Y', $exptime, false) : gmdate('Y', $curtime);
				$hh = ($edit) ? mysql2date('H', $exptime, false) : gmdate('H', $curtime);
				$mn = ($edit) ? mysql2date('i', $exptime, false) : gmdate('i', $curtime);
				$ss = ($edit) ? mysql2date('s', $exptime, false) : gmdate('s', $curtime);
				$month = '<label for="emm" class="screen-reader-text">' . __('Month') . '</label><select id="emm" name="emm">' . "\n";
				for ($i = 1; $i < 13; $i = $i +1) {
					$monthnum = zeroise($i, 2);
					$month .= '<option value="' . $monthnum . '" ' . selected($monthnum, $mm, false) . '>';
					$month .= $wp_locale->get_month_abbrev($wp_locale->get_month($i)) . "</option>\n";
				}
				$month .= '</select>';
				$day = '<label for="ejj" class="screen-reader-text">' . __('Day') . '</label><input type="text" id="ejj" name="ejj" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';
				$year = '<label for="eaa" class="screen-reader-text">' . __('Year') . '</label><input type="text" id="eaa" name="eaa" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';
				$hour = '<label for="ehh" class="screen-reader-text">' . __('Hour') . '</label><input type="text" id="ehh" name="ehh" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" />';
				$minute = '<label for="emn" class="screen-reader-text">' . __('Minute') . '</label><input type="text" id="emn" name="emn" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" />';
				echo '<div class="timestamp-wrap">';
				printf('%1$s %2$s, %3$s @ %4$s : %5$s', $month, $day, $year, $hour, $minute);
				echo '</div><input type="hidden" id="ess" name="ess" value="' . $ss . '" />';
				echo "\n\n";
				$map = array(
					'emm' => $mm,
					'ejj' => $jj,
					'eaa' => $aa,
					'ehh' => $hh,
					'emn' => $mn,
				);
				foreach ( $map as $timeunit => $value ) {
					echo '<input type="hidden" id="hidden_' . $timeunit . '" name="hidden_' . $timeunit . '" value="' . $value . '" />' . "\n";
				}
				?>
				<p>
					<a href="#edit_timestamp" class="save-exptimestamp hide-if-no-js button"><?=__('OK') ?></a>
					<a href="#edit_timestamp" class="cancel-exptimestamp hide-if-no-js button-cancel"><?=__('Cancel') ?></a>
				</p>
			</div>
			<?php wp_nonce_field('ry_pe_edit_expiration', 'ry_pe_edit_expiration'); ?>
		</div>
		<?php
	}

	public static function save_post($post_ID, $post) {
		if( !in_array($post->post_type, RY_PE::get_option('support_post_type', array())) ) {
			return;
		}

		if( !isset($_POST['ry_pe_edit_expiration']) || !wp_verify_nonce($_POST['ry_pe_edit_expiration'], 'ry_pe_edit_expiration') ) {
			return;
		}

		$aa = (int) $_POST['eaa'];
		$mm = (int) $_POST['emm'];
		$jj = (int) $_POST['ejj'];
		$hh = (int) $_POST['ehh'];
		$mn = (int) $_POST['emn'];
		$ss = (int) $_POST['ess'];
		$aa = ($aa <= 0 ) ? date('Y') : $aa;
		$mm = ($mm <= 0 ) ? date('n') : $mm;
		$jj = ($jj > 31 ) ? 31 : $jj;
		$jj = ($jj <= 0 ) ? date('j') : $jj;
		$hh = ($hh > 23 ) ? $hh - 24 : $hh;
		$mn = ($mn > 59 ) ? $mn - 60 : $mn;
		$ss = ($ss > 59 ) ? $ss - 60 : $ss;
		$expiration_date = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $aa, $mm, $jj, $hh, $mn, $ss);
		if( !wp_checkdate($mm, $jj, $aa, $expiration_date) ) {
			$expiration_date = '';
		}
		if( empty($expiration_date) ) {
			RY_PE::delete_post_meta($post_ID, 'expiration_time');
		} else {
			RY_PE::update_post_meta($post_ID, 'expiration_time', $expiration_date);
		}
	}

	public static function pages_columns($columns) {
		return self::posts_columns($columns, 'page');
	}

	public static function posts_columns($columns, $post_type) {
		if( in_array($post_type, RY_PE::get_option('support_post_type', array())) ) {
			$columns = array_merge($columns, array(
				'exp_date' => __('Expires Date', RY_PE::$textdomain)
			));
		}
		return $columns;   
		 
	}

	public static function custom_column($column, $post_ID) {
		global $post;
		if( !in_array($post->post_type, RY_PE::get_option('support_post_type', array())) ) {
			return;
		}

		if( $column == 'exp_date' ) {
			$exptime = RY_PE::get_post_meta($post_ID, 'expiration_time', TRUE);

			if( empty($exptime) ) {
				_e('Never');
			} else {
				$show_exptime = mysql2date(__('Y/m/d H:s'), $exptime);
				echo '<abbr title="' . $show_exptime . '">' . $show_exptime . '</abbr>';
				echo '<br />';
				if( time() > mysql2date('U', $exptime) ) {
					_e('Hide');
				} else {
					_e('Show');
				}
			}
		}
	}

	public static function admin_menu() {
		add_options_page('RY Post Expiration', __('Post Expiration', RY_PE::$textdomain), 'manage_options', 'ry_pe_expiration', array('RY_PE_admin', 'setting_page'));
	}

	public static function plugin_action_links($links, $file) {
		if( $file == RY_PE_PLUGIN_BASENAME ) {
			$settings_link = '<a href="options-general.php?page=ry_pe_expiration">' . __('Settings') . '</a>';
			$links = array_merge(array($settings_link), $links);
		}
		return $links;
	}

	public static function setting_page() {
		self::update_setting();
		$post_types = get_post_types(array(
			'show_ui' => 1
		));
		?>
		<div class="wrap">
			<h2><?=esc_html(__('General Settings')); ?></h2>
			<form method="post" action="" novalidate="novalidate">
			<?php settings_fields('ry_pe_expiration'); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label><?=__('support post type', RY_PE::$textdomain); ?></label></th>
						<td><?php self::make_post_type_setting(); ?></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	private static function update_setting() {
		if( isset($_POST['option_page']) && $_POST['option_page'] == 'ry_pe_expiration' ) {
			if( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'ry_pe_expiration-options') ) {
				$post_types = get_post_types(array(
					'show_ui' => 1
				));
				$support_type = array();
				foreach( $post_types as $type ) {
					if( isset($_POST['exp_' . $type]) ) {
						$support_type[] = $type;
					}
				}
				RY_PE::update_option('support_post_type', $support_type);
			}
		}
	}

	private static function make_post_type_setting() {
		$support_type = RY_PE::get_option('support_post_type', array());
		$post_types = get_post_types(array(
			'show_ui' => 1
		));
		if( count($post_types) ) {
			?>
			<ul>
			<?php
			foreach( $post_types as $type ) {
				$type_info = get_post_type_object($type);
				?>
				<li>
					<input name="exp_<?=$type ?>" type="checkbox" id="exp_<?=$type ?>" value="1" <?=(in_array($type, $support_type) ? 'checked': '') ?> />
					<label for="exp_<?=$type ?>"><?=$type_info->labels->name; ?> ( <?=$type ?> )</label>
				</li>
				<?php
			}
			?>
			</ul>
			<?php
		}
	}
}
