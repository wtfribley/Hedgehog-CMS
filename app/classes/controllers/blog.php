<?php

/**
 * Hedgehog's Blog Controller
 *
 * @author wtfribley
 */
class controllers_blog {
        
    private $path;
    
    function __construct($action)
    {
        if ($action != 'index')
        {
            header('Location: /blog');
            exit;
        }
        
        $this->path = Template::GetPath();
        Template::SetPagetype('post');
        $this->index();
    }
    
    private function index()
    {
        // Get all posts
        $posts = Read::R(array('type'=>'posts','orderBy'=>'date','onlyPublished'=>(!User::Verify('admin'))));      
                        
        if (!$posts)
            require $this->path . '404.phtml';
        else
            require $this->path . 'blog.phtml';    
    }
}