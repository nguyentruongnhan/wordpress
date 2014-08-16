<?php

if ( ! function_exists( 'with' ) ) {
    /**
     * This function is used for chaining call
     *
     * @param  object $obj
     *
     * @return object
     */
    function with( $obj ) {
        return $obj;
    }
}

if ( ! function_exists( 'get_avatar_url' ) ) {
    /**
     * @param        $id
     * @param int    $size
     * @param string $default
     *
     * @return string|null
     */
    function get_avatar_url( $id, $size = 100, $default = '' ) {
        $html = get_avatar( $id, $size, $default );
        preg_match( "/src='(.*?)'/i", $html, $matches );
        if ( $matches && count( $matches ) > 1 ) {
            return $matches[1];
        } else {
            return null;
        }
    }
}