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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

add_action('admin_menu', 'cw_admin_menu');

function pw_admin_menu() {
	// Menu page
	add_menu_page('ChatWing', __('ChatWing Congiration Page', 'chatwing'), 'manage_options', 'chatwing-settings', 'cw_settings_page');
}

function cw_settings_page() {	
	?>

	<div class="wrap">
		<h2><?php _e('Settings - Chat Wing', 'chatwing'); ?></h2>

		<div class="liquid-wrap">
			<div class="liquid-left">
				<div class="panel-left">
					<form action="" method="post">
						<table class="form-table">
							
						</table>
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
