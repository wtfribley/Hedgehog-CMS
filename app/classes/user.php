<?php

/**
 * Hedgehog's Ultimate Users class
 *
 * @author wtfribley
 */
class User {
    
    public $realname;
    
    private $username,
            $password,
            $rank;
    
    public static function Verify($rank = 'admin')
    {
        $user_rank = Session::get('user_rank');

        if ($user_rank == $rank)
            return true;
        else
            return false;
    }
    
    public static function Logout()
    {
        Session::destroy();
    }
    
    public function Login($rank = 'all')
    {
        if ($this->rank == $rank || $rank == 'all')
        {
            Session::regenerate();
            Session::set('user_rank', $this->rank);
            Session::set('user_username', $this->username);
            return true;
        }
        else
            return false;
    }
           
    public function set($property,$value)
    {
        if (property_exists($this, $property))
        {
            $this->$property = $value;
        }
    }
    
    public function the($property)
    {
        $property = strtolower($property);
        
        if (property_exists($this, $property))
        {
            return $this->$property;
        }
    }
}