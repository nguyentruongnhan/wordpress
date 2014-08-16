<?php

class WPChatwing_Widget extends WP_Widget {
    function __construct() {
        $id      = 'chatwing-widget';
        $name    = __( 'Chatwing Widget', CW_TEXTDOMAIN );
        $options = array( 'classname' => 'cw-widget', 'description' => __( 'Chatwing Widget - Display ChatWing chat box', CW_TEXTDOMAIN ) );
        parent::__construct( $id, $name, $options );
    }

    function form( $instance ) {
        if ( ! WPChatwing::get_instance()->has_access_key() ) {
            ?>
            <p>
                <?php printf( __( 'Please <a href="%s" title="Chatwing settings">insert Chatwing API token</a> to use this widget', CW_TEXTDOMAIN ), admin_url( 'admin.php?page=wpchatwing-settings' ) ); ?>
            </p>
            <?php
            return;
        }

        $chatboxes = WPChatwing::get_instance()->get_chatboxes();
        if ( ! count( $chatboxes ) ) {
            ?>
            <p>
                <?php _e( 'No chatbox found! Please check your ChatWing API token or create new chatbox', CW_TEXTDOMAIN ); ?>
            </p>
            <?php
            return;
        }

        $default_configs = array_merge( get_option( '__cw_options', array( 'width' => 640, 'height' => 480 ) ), array( 'title' => '', 'custom_login_enabled' => 0, 'custom_login_secret' => '' ) );
        $instance        = wp_parse_args( (array) $instance, $default_configs );

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ) ?>">
                <?php _e( 'Title', CW_TEXTDOMAIN ); ?>
                <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" placeholder="<?php echo $instance['title'] ? $instance['title'] : __( 'Widget title', CW_TEXTDOMAIN ) ?>" value="<?php echo $instance['title']; ?>">
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'chatbox_view' ); ?>">
                <?php _e( 'Chatbox' ); ?>
                <select name="<?php echo $this->get_field_name( 'chatbox_view' ) ?>" id="<?php echo $this->get_field_id( 'chatbox_view' ); ?>">
                    <?php foreach ( $chatboxes as $cb ): ?>
                        <option value="<?php echo $cb['alias'] ?>"><?php echo $cb['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'width' ) ?>">
                <?php _e( 'Width', CW_TEXTDOMAIN ) ?>
                <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'width' ) ?>" id="<?php echo $this->get_field_id( 'width' ); ?>" value="<?php echo $instance['width'] ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'height' ) ?>">
                <?php _e( 'Height', CW_TEXTDOMAIN ) ?>
                <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'height' ) ?>" id="<?php echo $this->get_field_id( 'height' ); ?>" value="<?php echo $instance['height'] ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'custom_login_enabled' ) ?>">
                <?php _e( 'Enable custom login', CW_TEXTDOMAIN ); ?>
                <select name="<?php echo $this->get_field_name( 'custom_login_enabled' ) ?>" id="<?php echo $this->get_field_id( 'custom_login_enabled' ) ?>">
                    <option value="0"><?php _e( 'No', CW_TEXTDOMAIN ); ?></option>
                    <option value="1" <?php if ( $instance['custom_login_enabled'] == '1' ) {
                        echo 'selected="selected"';
                    } ?> ><?php _e( 'Yes', CW_TEXTDOMAIN ); ?></option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'custom_login_secret' ) ?>">
                <?php _e( 'Custom login secret', CW_TEXTDOMAIN ) ?>
                <input type="text" class="widefat" name="<?php echo $this->get_field_name( 'custom_login_secret' ) ?>" id="<?php echo $this->get_field_id( 'custom_login_secret' ); ?>" value="<?php echo $instance['custom_login_secret'] ?>" />
            </label>
        </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        $old_instance['title']                = $new_instance['title'];
        $old_instance['chatbox_view']         = $new_instance['chatbox_view'];
        $old_instance['width']                = $new_instance['width'];
        $old_instance['height']               = $new_instance['height'];
        $old_instance['custom_login_enabled'] = $new_instance['custom_login_enabled'];
        $old_instance['custom_login_secret']  = $new_instance['custom_login_secret'];

        return $old_instance;
    }

    public function widget( $args, $instance ) {
        $wps = new WPChatwing_Shortcode();
        $cw_options = get_option('__cw_options', array());

        echo $args['before_widget'];
        if ( isset( $instance['title'] ) ) {
            echo $args['before_title'] . $instance['title'] . $args['after_title'];
        }
        echo $wps->render( array(
            'alias' => $instance['chatbox_view'],
            'width' =>  isset($instance['width']) ? $instance['width'] : $cw_options['width'],
            'height' => isset($instance['height']) ? $instance['height'] : $cw_options['height'],
            'custom_login_enabled' => $instance['custom_login_enabled'],
            'custom_login_secret' => $instance['custom_login_secret']
        ) );
        echo $args['after_widget'];
    }
}

/**
 * Init ChatWing widget
 */
function wpcw_widgets_init() {
    register_widget( 'WPChatwing_Widget' );
}

add_action( 'widgets_init', 'wpcw_widgets_init' );