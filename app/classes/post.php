<?php

/**
 * Hedgehog's New and Improved Post Class
 *
 * @author wtfribley
 */
class Post {
    
    public $id,
            $title,
            $content,
            $thmb,
            $date,
            $slug,
            $author,
            $status,
            $comments,
            $project,
            $categories = array(),
            $tags = array();
    
    private $excerptMarker = '<!-- more -->',
            $hasSlashes = true;
    
    public function set($property,$value)
    {
        if (property_exists($this, $property))
            $this->$property = $value;
    }
    
    public function the($property)
    {
        // the first time we access a property, let's strip
        // slashes from all of them. (can't use a constructor
        // because instances are created by PDO::FETCH_CLASS)
        if ($this->hasSlashes)
            $this->stripslashes_all();
        
        $property = strtolower($property);
        
        if (method_exists($this, $property))
            return $this->$property();
        elseif (property_exists($this, $property))
            return $this->$property;
        else
            throw new Exception('The post property you requested does not exist.');
    }
    
    public function has($property)
    {
        $property = $this->$property;
        
        if(is_array($property) && !empty($property))
        {
            return true;
        }
        elseif (is_int($property) && $property != 0)
        {
            return true;
        }
        elseif (is_string($property) && $property != '')
        {
            return true;
        }
        elseif ($property === true)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function excerpt()
    {
        $excerpt = explode($this->excerptMarker, $this->content);
        
        $excerpt = $excerpt[0];
        
        return $excerpt;  
    }
    
    public function categories()
    {
        $categories = array();
                
        foreach ($this->categories as $c)
        {
            $category = $c->the('categories');
            $id = $c->the('id');
            $categories[$category] = $id;
        }
        
        return $categories;
    }
    
    public function projects()
    {
        $projects = array();
                
        foreach ($this->project as $p)
        {
            $title = $p->the('title');
            $slug = $p->the('slug');
            $projects[$title] = $slug;
        }
        
        return $projects;
    }    
    
    public function rawdatetime()
    {
        return $this->date;
    }
    
    public function date($format = 'M. j, Y')
    {
        $date = date($format, $this->date);
        
        return $date;
    }
    
    public function time($format = 'g:i a')
    {
        $time = date($format, $this->date);
        
        return $time;
    }
    
    public function statuspicker()
    {
        $statuses = array('published','draft','trash');
        unset($statuses[array_search($this->status, $statuses)]);
        
        $select = '<select class="post-status">';
        $select.= '<option>' . $this->status . '</option>';
        foreach ($statuses as $status)
            $select.= '<option>' . $status . '</option>';
        $select.= '</select>';

        return $select;
    }
    
    private function stripslashes_all()
    {
        $properties = get_object_vars($this);
        
        foreach ($properties as $field => $value)
        {
            if (is_string($value) || is_array($value))
                $value = $this->stripslashes_deep($value);
            
            $this->$field = $value;
        }
        
        $this->hasSlashes = false;
    }
    
    private function stripslashes_deep($value)
    {
        $value = is_array($value) ? array_map(array($this,'stripslashes_deep'),$value) : stripslashes($value);
        return $value;
    }
}