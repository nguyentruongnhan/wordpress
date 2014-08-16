<?php

class WPChatwing_Shortcode {
    public function render( $atts ) {
        $options = shortcode_atts( array(
            'alias'               => '',
            'width'               => 640,
            'height'              => 480,
            'custom_login_enable' => false,
            'custom_login_secret' => null
        ), $atts, 'chatwing' );

        if ( empty( $options['alias'] ) ) {
            return '';
        }
        global $current_user, $cw_container;

        $extraData = array();

        if ( $options['custom_login_enable'] && $options['custom_login_secret'] && $current_user->ID) {
            // user logged in . Continue processing
            $preparedData = array(
                'id' => $current_user->ID,
                'name' => $current_user->display_name,
                'expire' => round(microtime(true) * 1000) + 60*60*1000,
                'avatar' => get_avatar_url($current_user->ID, 100)
            );
            $extraData['custom_session'] = $preparedData;
        }
        $chatbox = $cw_container['box'];
        $chatbox->setAlias($options['alias']);
        $chatbox->setParams($extraData);
//        $query = http_build_query($extraData);

        // render the iframe code here
        $chatbox_link = $chatbox->getChatboxUrl() ;

        return "<iframe src='{$chatbox_link}' width='{$options['width']}' height='{$options['height']}'></iframe>";
    }
}

add_shortcode( 'chatwing', array( 'WPChatwing_Shortcode', 'render' ) );