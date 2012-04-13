<?php

/**
 * Hedgehog's Admin Controller
 *
 * @author wtfribley
 */
class controllers_admin {
        
    private $posts,
            $projects,
            $user;
    
    private $path;
    
    function __construct($action)
    {   
        $this->path = BASE_PATH . '/pub/admin/';
        Template::SetPagetype('admin');
        
        if ($action == 'login')
        {
            $this->login();
            exit();
        }
        
        if (User::Verify('admin'))
        {
            $this->setupAdmin();
            $this->$action();
            exit();
        }           
        
        $this->index();
    }
    
    /*
     * Main Admin Page
     */
    
    private function index()
    {
        $searchBy = array('rank'=>'admin');
        $user = Read::R(array('type'=>'user','searchBy'=>$searchBy));
        
        if (empty($user))
            header('Location: /admin/register');
        elseif (User::Verify('admin'))
            header('Location: /admin/dashboard');
        else
            header('Location: /admin/login');
    }
    
    private function dashboard()
    {        
        // We're on the dashboard page
        $page = 'dashboard';

        require $this->path . 'admin.phtml';       
    }
    
    private function posts()
    {        
        // We're on the posts page
        $page = 'posts';

        require $this->path . 'admin.phtml';
    }
    
    private function projects()
    {
        $page = 'projects';
        require $this->path . 'admin.phtml';
    }
    
    private function register()
    {
        // If there is already an admin, go straight to login
        $searchBy = array('rank'=>'admin');
        $user = Read::R(array('type'=>'user','searchBy'=>$searchBy));
        if (empty($user))
        {
            require $this->path . 'register.phtml';
        }
        else
        {
            header('Location: /admin/login');
        }
    }
    
    private function login()
    {
        if (User::Verify('admin'))
        {
            // Redirect to Dashboard
            header('Location: /admin/dashboard');
        }
        else           
            require $this->path . 'login.phtml';
    }
    
    private function logout()
    {
        User::Logout();
        header('Location: /');
    }
    
    private function newpost()
    {
        $defaults = array(
            'title'=>'New Post',
            'content'=>'<p class=\"indent\">Write the next great blog post...</p>',
            'date'=>time(),
            'slug'=>'new-post',
            'author'=> $this->user->the('realname'),
            'status'=>'draft'          
        );
        
        Create::C('posts', $defaults);
        header('Location: /posts/new-post');
    }
    
    private function newproject()
    {
        $defaults = array(
            'title'=>'New Project',
            'content'=>'<p>Drag and drop to place media related to the project.</p>',
            'slug'=>'new-project',
            'description'=>'<p>Here\'s a description...</p>',
            'roles'=>'your role in this project?',
            'skills'=>'skills involved in this project?'
        );
        
        Create::C('projects', $defaults);
        header('Location: /projects/new-project');
    }
    
    private function setupAdmin()
    {
        // Get Posts
        $this->posts = Read::R(array('type'=>'posts','orderBy'=>'date','onlyPublished'=>false));
        
        // Get Projects
        $this->projects = Read::R(array('type'=>'projects'));

        // Get User Information
        $searchBy = array('username'=>Session::get('user_username'));
        $user = Read::R(array('type'=>'user','searchBy'=>$searchBy));

        // We'll only be dealing with one user at a time
        $this->user = $user[0];
    }
}