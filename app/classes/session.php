<?php

/**
 * A simple wrapper around PHP's session object
 *
 * @author wtfribley
 */


class Session {
    
    public static function start()
    {
        session_start();
    }
    
    public static function regenerate()
    {
        session_regenerate_id();
    }
    
    public static function get($key, $default = false)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
    
    public static function erase($key)
    {
        if(isset($_SESSION[$key])) 
        {
            unset($_SESSION[$key]);
        }
    }
    
    public static function destroy()
    {
        session_destroy();
        $_SESSION = array();
    }
}