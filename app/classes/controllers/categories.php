<?php

/**
 * Hedgehog's Categories Controller
 *
 * @author wtfribley
 */

class controllers_categories {
        
    private $category, $path;
    
    function __construct($action)
    {
        $this->category = $action;
        $this->path = Template::GetPath();
        Template::SetPagetype('category');
        $this->index();
    }
    
    private function index()
    {
       // if we haven't passed in a category, redirect to home
        if ($this->category == 'index')
        {
            header('Location: /blog');
            exit();
        }
        
        $searchBy = array('categories'=>$this->category);
        $posts = Read::R(array('type'=>'posts','searchBy'=>$searchBy,'orderBy'=>'date','sortBy'=>'DESC'));
                
        if (!$posts)
            require $this->path . '404.phtml';
        else
            require $this->path . 'blog.phtml';
    }   
}