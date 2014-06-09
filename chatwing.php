<?php
/**
 * @package ChatWing
 */
/*
Plugin Name: ChatWing
Plugin URI: http://chatwing.com/
Description: Provide widgets easily to config, serve ChatWing boxes
Version: 1.0
Author: ChatWing
Author URI: http://chatwing.com/wordpress-plugin/
License: GPLv2 or later
Text Domain: chatwing
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there! I\'m just a plugin, not much I can do when called directly.';
	exit;
}

// load encrypt key if it exists
@include_once('key.php');

define('CHATWING_ENVIRONMENT', 'development'); // production
if ( !defined('CHATWING_ENCRYPT_KEY') ) {
	define('CHATWING_ENCRYPT_KEY', 'CHATWING2014');
}
if ( defined('CHATWING_ENVIRONMENT') && CHATWING_ENVIRONMENT == 'developement') {
	define('CHATWING_DOMAIN', 'chatwing.com');
} else {
	define('CHATWING_DOMAIN', 'staging.chatwing.com');
}

require_once('lib/class.chatwing.php');
require_once('lib/class.encryption.php');
require_once('chatwing-widgets.php');

/**
 * Init
 */
function cw_init() {
	// initialize chatWING encryption
	$GLOBALS['cw_encryption'] = new Encryption(CHATWING_ENCRYPT_KEY);
}
add_action('init', 'cw_init', 1);

/**
 * Insert chatWING script to HTML head
 * @return type
 */
function cw_insert_chatwing_script() {
	// Not implement Javascript embed for now
	return ;

	if ( !cw_check_valid_token() ) return ;
	?>
<script type="text/javascript">
(function(d) {
	var cwjs, id='chatwing-js';
	if (d.getElementById(id)) return;
	cwjs = d.createElement('script');
	cwjs.type = 'text/javascript';
	cwjs.async = true;
	cwjs.id = id;
	cwjs.src = "//<?php echo CHATWING_DOMAIN ?>/code///<?php echo cw_get_token() ?>/embedded";
	d.getElementsByTagName('head')[0].appendChild(cwjs);
})(document);
</script>
	<?php
}
add_action('wp_head', 'cw_insert_chatwing_script');

/**
 * Handle chatWING shortcode
 *
 * @param array/string $atts
 * @return string
 */
function cw_shortcode($atts) {
	$config = cw_get_config('display');

	$atts = shortcode_atts(array(
		'chatbox' => false,
		'width' => $config['width'],
		'height' => $config['height'],
	), $atts);

	extract($atts, EXTR_SKIP);

	if ( !$chatbox || !cw_check_valid_token() ) return '';
	$chatbox_data = cw_get_chatbox($chatbox);
	if ( empty($chatbox_data) ) return CHATWING_ENVIRONMENT == 'development' ? __('Empty chatbox data', 'chatwing') : '';

	$atts['token'] = cw_get_token();
	$atts['chatbox_url'] = $chatbox_data['urls']['view'];

	$html = '<!-- Begin chatwing.com chatbox -->
<iframe src="{{chatbox_url}}" width="{{width}}" height="{{height}}" frameborder="0" scrolling="0">Please contact us at info@chatwing.com if you cant embed the chatbox</iframe>
<!-- End chatwing.com chatbox -->';
	$html = cw_template_render($html, $atts);

	return $html;
}
add_shortcode('chatwing', 'cw_shortcode');

/**
 * chatWING settings page
 */
function cw_admin_menu() {
	// Menu page
	add_menu_page(__('ChatWing', 'chatwing'), __('ChatWing Config', 'chatwing'), 'manage_options', 'chatwing-settings', 'cw_settings_page');
}
add_action('admin_menu', 'cw_admin_menu');

/**
 * ChatWing enqueue scripts
 */
function cw_admin_print_scripts() {
//	wp_enqueue_script('jquery-ui-slider');
	wp_enqueue_script('chatwing-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery', 'jquery-ui-slider'), '1.0', true);
}
add_action('admin_print_scripts', 'cw_admin_print_scripts');

/**
 * ChatWing enqueue styles
 */
function cw_admin_print_styles() {
	wp_enqueue_style('chatwing-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin.css', array());
}

add_action('admin_print_styles', 'cw_admin_print_styles');

/**
 * Display settings page
 */
function cw_settings_page() {

	if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
		$post = stripslashes_deep($_POST);

		if ( isset($post['save-changes']) ) {

			if ( !empty($post['cw_token']) ) { // update token

				if ( cw_check_valid_token($post['cw_token']) ) {
					cw_set_token($post['cw_token']);
					cw_show_message(__('Token updated', 'chatwing'), 'updated fade');
				} else {
					cw_show_message(__('Invalid chatWING token. Please try again!', 'chatwing'), 'error');
				}

			}

			if ( !empty($post['cw_settings']) ) {
				update_option('cw_options', stripslashes_deep($post['cw_settings']));
				cw_show_message(__('Settings updated', 'chatwing'), 'updated fade');
			}

		} elseif ( isset($post['delete-token']) ) {

			cw_set_token(false);
		}

	}

	$settings = cw_get_config();
	?>

	<?php if ( !cw_check_valid_token() ) : ?>
	<div id="message" class="error"><p><?php _e('Please input your ChatWing token first!', 'chatwing') ?></p></div>
	<?php endif; ?>

	<div class="wrap">
		<h2><?php _e('ChatWing Settings', 'chatwing'); ?></h2>

		<div class="liquid-wrap">
			<div class="liquid-left">
				<div class="panel-left">
					<form action="" method="post">

						<?php if ( cw_check_valid_token() ) :

							$chatboxes = cw_get_chatbox();
							$n_chatboxes = count($chatboxes);
							?>
						<h3><?php _e('Chatboxes', 'chatwing') ?></h3>
						<p><small><?php printf(_n('We found %d chatbox', 'We found %d chatboxes', $n_chatboxes, 'chatwing'), $n_chatboxes) ?></small></p>
						<table class="widefat fixed">
							<thead>
								<tr>
									<th width="5%"><?php _e('ID', 'chatwing') ?></th>
									<th width="15%"><?php _e('Name', 'chatwing') ?></th>
									<th width="15%"><?php _e('Alias', 'chatwing') ?></th>
									<th><?php _e('Key', 'chatwing') ?></th>
									<th width="20%">&nbsp;</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ((array)$chatboxes as $chatbox) : ?>
								<tr>
									<td><?php echo $chatbox['id'] ?></td>
									<td><?php echo $chatbox['name'] ?></td>
									<td><?php echo $chatbox['alias'] ?></td>
									<td nowrap="nowrap"><code><?php echo $chatbox['key'] ?></code></td>
									<td>
										<a target="_blank" href="//<?php echo CHATWING_DOMAIN . $chatbox['urls']['use']; ?>"><?php _e('Use chatbox', 'chatwing') ?></a>
										| <a target="_blank" href="//<?php echo CHATWING_DOMAIN . $chatbox['urls']['customize']; ?>"><?php _e('Customize', 'chatwing') ?></a>
										| <a target="_blank" href="<?php echo $chatbox['urls']['view']; ?>"><?php _e('View', 'chatwing') ?></a>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<?php endif; ?>

						<h3><?php _e('Plugin Configuration', 'chatwing') ?></h3>
						<table class="form-table">
							<tr>
								<th><?php _e('Token', 'chatwing') ?></th>
								<td>
									<?php if ( !cw_check_valid_token() ) : ?>
										<input type="text" class="code regular-text" name="cw_token" value="" />
									<?php else: ?>
										<input type="submit" class="button-secondary" name="delete-token" onclick="return confirm('<?php _e('Are you sure to delete this token?') ?>');" value="<?php _e('Delete token') ?>" />
									<?php endif; ?>
								</td>
							</tr>
						</table>

						<?php if ( cw_check_valid_token() ) : ?>
						<h3><?php _e('Default Display Configuration', 'chatwing') ?></h3>
						<table class="form-table">
							<tr>
								<th><?php _e('Width', 'chatwing') ?></th>
								<td>
									<input type="text" class="cw-slider small-text" value="<?php echo $settings['display']['width'] ?>" id="cw_display_width" min="200" max="1000" step="1" name="cw_settings[display][width]">
								</td>
							</tr>
							<tr>
								<th><?php _e('Height', 'chatwing') ?></th>
								<td>
									<input type="text" class="cw-slider small-text" value="<?php echo $settings['display']['height'] ?>" id="cw_display_height" min="200" max="1000" step="1" name="cw_settings[display][height]">
								</td>
							</tr>
						</table>
						<?php endif; ?>

						<p class="submit">
							<input type="submit" class="button-primary" name="save-changes" value="<?php _e('Save Changes', 'chatwing'); ?>" />
						</p>
					</form>
				</div>
			</div>

			<div class="liquid-right">
				<div class="panel-right">
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Run when the plugin is activated
 */
function cw_plugin_activation() {
	$random_key = wp_generate_password();
	cw_store_encryption_key($random_key);
}
register_activation_hook(__FILE__, 'cw_plugin_activation');

/* FUNCTIONS */

/**
 * Store encryption key to file
 */
function cw_store_encryption_key($key) {
	$dir = plugin_dir_path(__FILE__);

	if ( !is_writeable($dir) ) return false; // plugin directory not writable
	if (file_exists($dir . 'key.php') ) return true; // key file exists

	$content = sprintf("<?php define('CHATWING_ENCRYPT_KEY', '%s'); ?>", $key);

	$fp = fopen($dir . 'key.php', 'w');
	fputs($fp, $content);
	fclose($fp);

	return true;
}

/**
 * Get ChatWing API instance
 *
 * @return ChatWing
 */
function cw_get_api_instance() {

	$token = cw_get_token();
	$cw_api = ChatWing::getInstance($token);
	$cw_api->setEnvironment(CHATWING_ENVIRONMENT);

	return $cw_api;
}

/**
 * Get chatbox / Get chatbox list
 *
 * @param string $name If $name is empty, will return a list of chatboxes
 * @return array
 */
function cw_get_chatbox($name = '') {
	$cw_api = cw_get_api_instance();
	$chatboxes = $cw_api->list();

	if ( !empty($name) ) {
		$_chatbox = false;
		foreach ((array)$chatboxes as $chatbox) {
			if ( $chatbox['id'] == $name ) $_chatbox = $chatbox;
			elseif ( $chatbox['key'] == $name ) $_chatbox = $chatbox;
			elseif ( $chatbox['alias'] == $name ) $_chatbox = $chatbox;
			elseif ( $chatbox['name'] == $name ) $_chatbox = $chatbox;

			if ( $_chatbox ) break;
		}
		$chatboxes = $_chatbox;
		unset($_chatbox);
	}

	return $chatboxes;
}

/**
 * Simple template render engine
 * 
 * @param string $template
 * @param array $vars
 * @return string
 */
function cw_template_render($template, $vars) {
	if ( !empty($vars) ) {
		foreach ($vars as $k => $v) {
			$template = str_replace('{{'.$k.'}}', $v, $template);
		}
	}

	// replace all missing template keys to blank
	$template = preg_replace('{{\w}}', '', $template);

	return $template;
}

/**
 * Get chatWING config
 *
 * @param string $config
 * @return array
 */
function cw_get_config($config = null) {
	$settings = get_option('cw_options', array());

	// default settings
	foreach (array('display') as $_cf) {
		if ( empty($settings[$_cf]) ) {
			switch($_cf) {
				case 'display':
					$setting = array(
						'width' => 240,
						'height' => 370,
					);
					break;
			}

			$settings[$_cf] = $setting;
		}
	}

	if ( !empty($config) && isset($settings[$config]) ) return $settings[$config];

	return $settings;
}

/**
 * Show WP message
 *
 * @param string $message
 * @param string $type
 * @param boolean $echo
 * @return string
 */
function cw_show_message($message, $type, $echo = true) {

	$html = sprintf('<div id="message" class="%s"><p>%s</p></div>', $type, $message);

	if ( $echo )
		echo $html;

	return $html;
}

/**
 * Get chatWING token from database
 *
 * @global Encryption $cw_encryption
 * @param string $context
 * @return string
 */
function cw_get_token($context = 'raw') {
	global $cw_encryption;

	$cw_token = get_option('_cw_token', false);
	$token = $cw_encryption->decode($cw_token);

	return $token;
}

/**
 * Save chatWING token to database
 *
 * @global Encryption $cw_encryption
 * @param string $token
 */
function cw_set_token($token) {
	global $cw_encryption;

	if ( $token !== false ) {
		$cw_token = $cw_encryption->encode($token);
		update_option('_cw_token', $cw_token);
	} else {
		delete_option('_cw_token');
	}
}

/**
 * Check if chatWING token is valid
 *
 * @param mixed $token
 * @return boolean
 */
function cw_check_valid_token($token = null) {
	if ( $token === null ) {
		$cw_token = cw_get_token();
	} else {
		$cw_token = $token;
	}

	return $cw_token != '';
}
