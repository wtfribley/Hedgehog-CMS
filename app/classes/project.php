<?php

/**
 * Hedgehog's Purty Project Class
 *
 * @author wtfribley
 */

class Project {
    
    public $id,
            $title,
            $content,
            $thmb,
            $date,
            $slug,
            $description,
            $roles,
            $skills,
            $order,
            $categories = array(),
            $tags = array();
    
    private $hasSlashes = true;
        
    public function set($property,$value)
    {
        if (property_exists($this, $property))
            $this->$property = $value;
    }
    
    public function the($property, $args = null)
    {
        // the first time we access a property, let's strip
        // slashes from all of them. (can't use a constructor
        // because instances are created by PDO::FETCH_CLASS)
        if ($this->hasSlashes)
            $this->stripslashes_all();
        
        $property = strtolower($property);
        
        if (method_exists($this, $property))
            return $this->$property($args);
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
    
    public function categories($args = null)
    {
        $categories = array();
                
        foreach ($this->categories as $c)
        {
            $category = $c->the('categories');
            $id = $c->the('id');
            $color = $c->the('color');
            $categories[$category] = array($id,$color);
        }
        
        return $categories;
    }
    
    public function thumbnail($args = null)
    { ?>
        <li class="thumbnail" data-project-id="<?= $this->id; ?>" data-target="<?= $args['target']; ?>" data-action="echo_project">
            <img src="<?= HOST . '/uploads/images/' . $this->thmb; ?>" title="<?= $this->title; ?>" />
            <div class="overlay" data-project-slug="<?= $this->slug; ?>">
                <h1><?= $this->title; ?></h1>
                <?php if (isset($args['clicktext'])) { ?>
                <h3 class="textcenter"><?= $args['clicktext']; ?></h3>
                <?php } ?>
                <ul class="unstyled">
                    <?php foreach ($this->categories() as $category => $id_color) { ?>
                    <li><h2 style="color: <?= $id_color[1]; ?>;"><?= $category; ?></h2></li>
                    <?php } ?>
                </ul>
            </div>
        </li>
<?php }
    
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