<?php

/**
 * @author  chatwing
 * @package Chatwing_Wordpress
 */

/*
Plugin Name: WPChatwing
Plugin URI: http://chatwing.com/
Description: ChatWing plugin for Wordpress
Version: 1.0
Author: ChatWing
Author URI: http://chatwing.com
License: MIT
Text Domain: wpchatwing
*/

if ( ! function_exists( 'add_action' ) ) {
    die( "Hi there! I'm just a plugin, not much I can do when called directly." );
}

// define some constant
defined( 'DS' ) or define( 'DS', DIRECTORY_SEPARATOR );
define( 'CW_PLG_VERSION', '1.0' );
define( 'CW_TEXTDOMAIN', 'wpchatwing' ); // text domain, used for translation
define( 'CW_DIR', plugin_dir_path( __FILE__ ) );
define( 'CW_TEMPLATE_DIR', CW_DIR . 'templates/' );
define( 'CW_CLIENT_ID', 'wordpress' );
define( 'CW_DEBUG', WP_DEBUG );
define( 'CW_USE_STAGING', true );

require_once( CW_DIR . 'lib/class.encryption.php' );
require_once( CW_DIR . 'lib/class.cwsession.php' );
require_once( CW_DIR . 'lib/class.wpchatwing.php' );
require_once( CW_DIR . 'lib/class.wpchatwing-widget.php' );
require_once( CW_DIR . 'lib/class.wpchatwing-shortcode.php' );
require_once( CW_DIR . 'lib/helpers.php' );

// include encryption key file if exists.
if ( file_exists( CW_DIR . 'key.php' ) ) {
    include( 'key.php' );
}


/**
 * Plugin activation hook. This function only runs  when plugin is activated,
 * or be explicit called
 *
 * @param  boolean $force_create_key
 *
 * @return void
 */
function wpcw_plugin_activation( $force_create_key = false ) {
    // check if key file exist
    $keyFile = CW_DIR . 'key.php';
    if ( ! file_exists( $keyFile ) || $force_create_key ) {
        // we gonna create new key file
        if ( ! wp_is_writable( CW_DIR ) ) {
            wp_die(
                "[WPChatwing] Plugin directory is not writable. Please recheck your server's settings! Plugin activation failed !"
            );
        }
        $random_key = wp_generate_password( 16 );
        $content    = sprintf( "<?php define('CW_ENCRYPT_KEY', '%s'); ?>", $random_key );
        $fp         = fopen( CW_DIR . 'key.php', 'w' );
        fputs( $fp, $content );
        fclose( $fp );
    }
}

register_activation_hook( __FILE__, 'wpcw_plugin_activation' );

require_once __DIR__ . '/bootstrap.php';

// init app
new WPChatwing();