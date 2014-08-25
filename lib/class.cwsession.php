<?php

/**
 * @author  chatwing
 * @package Chatwing_Wordpress
 */

class CW_Session
{
    static $instance = null;

    private function __construct()
    {
        if (! session_id()) {
            session_start();

        }
        if (! isset( $_SESSION['cw.data'] )) {
            $_SESSION['cw.data'] = array();
        }
        if (! isset( $_SESSION['cw.flash'] )) {
            $_SESSION['cw.flash'] = array();
        }
    }

    public static function getInstance()
    {
        if (is_null( self::$instance )) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function put( $name, $message = '' )
    {
        $_SESSION["cw.data"][$name] = $message;
    }

    /**
     * @param        $name
     * @param string|array|mixed $message
     */
    public function flash( $name, $message = '' )
    {
        $this->put( $name, $message );
        $_SESSION['cw.flash'][$name] = true;
    }

    public function get( $name )
    {
        if (isset( $_SESSION['cw.data'][$name] )) {
            $value = $_SESSION['cw.data'][$name];
            if (isset( $_SESSION['cw.flash'][$name] )) {
                unset( $_SESSION['cw.data'][$name] );
                unset( $_SESSION['cw.flash'][$name] );
            }
            return $value;
        }
        return false;
    }
}