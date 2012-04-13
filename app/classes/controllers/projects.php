<?php

/**
 * Hedgehog's Projects Controller
 *
 * @author wtfribley
 */

class controllers_projects {
        
    private $slug, $path;
    
    function __construct($action)
    {
        $this->slug = $action;
        $this->path = Template::GetPath();
        Template::SetPagetype('project');
        $this->index();
    }
    
    private function index()
    {
        // if we haven't passed in a project slug, redirect to home
        if ($this->slug == 'index')
        {
            header('Location: /');
            exit();
        }
        
        // Get posts by the project slug
        $searchBy = array('project'=>$this->slug);
        $posts = Read::R(array('type'=>'post','searchBy'=>$searchBy,'orderBy'=>'date','onlyPublished'=>(!User::Verify('admin'))));
                
        // Get project by its slug
        $searchBy = array('slug'=>$this->slug);
        $project = Read::R(array('type'=>'project','searchBy'=>$searchBy));
        
        if (!$project)
            require $this->path . '/404.phtml';
        else
        {   
            // This page only shows a single project - so free it from its array.
            $project = $project[0];
            
            if (User::Verify('admin'))
                require BASE_PATH . '/pub/admin/project-edit.phtml';
            else
                require $this->path . '/project.phtml';
        }
    }
}