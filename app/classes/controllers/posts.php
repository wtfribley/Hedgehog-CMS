<?php
/**
 * Hedgehog's Posts Controller
 *
 * @author wtfribley
 */
class controllers_posts {
        
    private $slug, $path;
    
    function __construct($action)
    {
        $this->slug = $action;
        $this->path = Template::GetPath();
        Template::SetPagetype('post');
        $this->index();
    }
    
    private function index()
    {
        if ($this->slug == 'index')
        {
            header('Location: /blog');
            exit();
        }
        
        if (User::Verify('admin'))
        {
            // Get ALL posts by slug
            $posts = Read::R(array('type'=>'post','searchBy'=>array('slug'=>$this->slug),'onlyPublished'=>false));
            
            Template::SetPagetype('post-edit');
            $pagepath = BASE_PATH . '/pub/admin/post-edit.phtml';
        }
        else
        {
            // Get ONLY PUBLISHED posts by slug
            $posts = Read::R(array('type'=>'post','searchBy'=>array('slug'=>$this->slug)));
            
            $pagepath = $this->path . 'blog.phtml';
        }
        
        
        if (!$posts)
            require $this->path . '404.phtml';
        else
            require $pagepath;
    }
}