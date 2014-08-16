<?php

/**
 * @author  chatwing
 * @package Chatwing_Wordpress
 */
class WPChatwing {
    private static $instance = null;

    private $api_key = null;
    private $plugin_settings = array();

    public function __construct() {
        // register hook
        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'init', array( $this, 'init' ) );

        if ( is_admin() ) {
            add_action( 'admin_menu', array( $this, 'admin_register_menu' ) );

            // add handler for form
            add_action( 'admin_action_cw_token_save', array( $this, 'admin_handle_save_token' ) );
            add_action( 'admin_action_cw_setting_save', array( $this, 'admin_handle_save_settings' ) );
        }

        self::$instance = $this;
    }

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function load_plugin_textdomain() {
        load_textdomain( CW_TEXTDOMAIN, CW_DIR . 'languages' );
    }

    public function init() {
        if ( ! defined( 'CW_ENCRYPT_KEY' ) ) {
            wpcw_plugin_activation( true );
            Encryption::get_instance( CW_ENCRYPT_KEY );
        } else {
            $this->api_key         = Encryption::get_instance( CW_ENCRYPT_KEY )->decode( get_option( '__cw_token' ) );
            $this->plugin_settings = get_option( '__cw_options', array() );
        }
        CW_Session::getInstance(); // create cw_session instance here , so session will be started before any output

    }

    /**
     * Return chat box list
     * @return array
     */
    public function get_chatboxes() {
        if ( $this->has_access_key() ) {
            global $cw_container;
            try {
                $cw_container['api']->setAccessToken( $this->get_access_key() );
                $response = $cw_container['api']->call( 'user/chatbox/list' );

                if ( $response['success'] ) {
                    return $response['data'];
                } else {
                    throw new \Chatwing\Exception\ChatwingException( $response['error'] );
                }

            } catch ( \Chatwing\Exception\ChatwingException $cwe ) {
                if ( CW_DEBUG ) {
                    $message = $cwe->getMessage();
                } else {
                    $message = __( 'Cannot retrieve chat box list', CW_TEXTDOMAIN );
                }
                CW_Session::getInstance()->flash( 'error', $message );
            }
        }

        return array();
    }

    public function admin_register_menu() {
        /**
         * Add menu Chatwing settings
         *
         * @see build_settings_page()
         */
        add_menu_page(
            __( 'Chatwing plugin settings', CW_TEXTDOMAIN ),
            __( 'Chatwing Settings', CW_TEXTDOMAIN ),
            'manage_options',
            'wpchatwing-settings',
            array( $this, 'admin_display_settings_page' )
        );
    }

    public function admin_display_settings_page() {
        if ( ! WPChatwing::get_instance()->has_access_key() ) {
            $this->render( 'add_key', array(), 'admin' );
        } else {
            $data          = array();
            $data['boxes'] = $this->get_chatboxes();
            $this->render( 'settings', $data, 'admin' );
        }
    }

    public function admin_handle_save_token() {
        check_admin_referer( 'cw-token-update' );

        if ( ! empty( $_POST ) ) {
            $task           = array_key_exists( 'task', $_POST ) ? $_POST['task'] : '';
            $message        = null;
            $action_success = false;

            switch ( $task ) {
                case 'add':
                case 'update':
                    if ( array_key_exists( 'cw_token', $_POST ) && ! empty( $_POST['cw_token'] ) ) {
                        $encoded_token = Encryption::get_instance()->encode( $_POST['cw_token'] );
                        if ( update_option( '__cw_token', $encoded_token ) ) {
                            $message        = __( 'Token was updated successfully!', CW_TEXTDOMAIN );
                            $action_success = true;
                        } else {
                            $message = __( 'Cannot update token!', CW_TEXTDOMAIN );
                        }
                    } else {
                        $message = __( 'Invalid Chatwing Token', CW_TEXTDOMAIN );
                    }

                    break;

                case 'delete':
                    if ( delete_option( '__cw_token' ) ) {
                        $message        = __( 'Deleted token successfully!!', CW_TEXTDOMAIN );
                        $action_success = true;
                    } else {
                        $message = __( 'Couldn\'t delete token!!', CW_TEXTDOMAIN );
                    }
                    break;

                default:
                    $message = __( 'Invalid task!!', CW_TEXTDOMAIN );
                    break;
            }

            if ( $message ) {
                CW_Session::getInstance()->flash( 'message', array(
                    'content' => $message,
                    'type'    => $action_success ? 'message' : 'error'
                ) );
            }
        }

        wp_redirect( 'admin.php?page=wpchatwing-settings' );
    }

    public function admin_handle_save_settings() {
        check_admin_referer( 'cw-settings-update' );

        $data = array(
            'width'  => isset( $_POST['width'] ) ? $_POST['width'] : 640,
            'height' => isset( $_POST['height'] ) ? $_POST['height'] : 480
        );
        update_option( '__cw_options', $data );

        wp_redirect( 'admin.php?page=wpchatwing-settings' );
    }

    /**
     * Check if API access token is set
     *
     * @return bool
     */
    public function has_access_key() {
        return (bool) $this->api_key;
    }

    public function get_access_key() {
        return $this->api_key;
    }

    /**
     * @param        $file
     * @param        $params
     * @param string $prefix
     */
    public function render( $file, $params = array(), $prefix = '' ) {
        $fileName           = ( $prefix ? $prefix . '.' . $file : $file ) . '.php';
        $template_file_path = CW_TEMPLATE_DIR . $fileName;
        if ( file_exists( $template_file_path ) ) {
            if ( ! empty( $params ) ) {
                extract( $params );
            }
            ob_start();
            include $template_file_path;
            $content = ob_get_clean();
            echo $content;
        } else {
            wp_die( __( "Template file {$fileName} does not exist", CW_TEXTDOMAIN ) );
        }
    }
}