<?php

/**
 * Hedgehog's Capable Categories Class
 *
 * @author wtfribley
 */

class Category {
    
    public $id,
            $categories,
            $color;
    
    public function set($property,$value)
    {
        if (property_exists($this, $property))
            $this->$property = $value;
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