<?php
/**
 * @author  chatwing
 * @package Chatwing_SDK
 */

$cw_container = new \Pimple\Container();

$cw_container['api'] = function ( \Pimple\Container $c ) {
    $api = new \Chatwing\Api( CW_CLIENT_ID );
    $api->setEnv( CW_DEBUG ? CW_ENV_DEVELOPMENT : CW_ENV_PRODUCTION );
    if ( isset( $c['cw_token'] ) ) {
        $api->setAccessToken( $c['cw_token'] );
    }

    return $api;
};


$cw_container['box'] = $cw_container->factory( function ( \Pimple\Container $c ) {
    return new \Chatwing\Chatbox( $c['api'] );
} );

// register the container to global variables table
$GLOBALS['cw_container'] = $cw_container;