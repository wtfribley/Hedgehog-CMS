<?php

/**
 * Hedgehog's Artisinal Ajax Class
 *
 * @author wtfribley
 */

class controllers_ajax {
    
    function __construct($action)
    {
        unset($_POST['action']);
        $this->$action();
    }
    
    // USER / AUTHENTICATION METHODS
    ////////////////////////////////////
    
    private function login()
    {          
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        // Get the Users credentials from the DB
        $searchBy = array('username'=>$username);
        $user = Read::R(array('type'=>'user','searchBy'=>$searchBy));
        
        // Create the Response variable.
        $response = array('handler'=>'warning','data'=>null);
        
        if(empty($user))
            $response['data'] = "I think you have the wrong Username.";
        else
        {
            $hasher = $this->hasher();
            
            // We'll only be logging in one user at a time
            $user = $user[0];
            
            // Get the password hash for authentication, then remove it from the user object
            $stored_hash = $user->the('password');
            $user->set('password',null);
            
            if ($hasher->CheckPassword($password, $stored_hash))
            {
                if ($user->Login('admin'))
                    $response['handler'] = 'reload';
                else
                    $response['data'] = 'The area you\'re trying to access is, well, above your pay grade.';
            }
            else
                $response['data'] = "You seem to have given the wrong password.";
            
            unset($hasher);
        }
        
        echo json_encode($response);
    }
    
    private function register()
    {
        $hasher = $this->hasher();
        
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $hash = $hasher->HashPassword($password);
        unset($hasher);
        
        // create $userData array.
        $userData = array('username'=>$username,'password'=>$hash,'rank'=>'admin');
        
        // create response.
        $response = array('handler'=>'warning','data'=>null);
        
        if (Create::C('users', $userData))       
            $response['handler'] = 'reload';
        else
            $response['data'] = 'error messages';
        
        echo json_encode($response);
    }
    
    private function hasher()
    {
        // Hashing config variables.
        $hash_cost_log2 = 12;
        $hash_portable = false;      
        
        return new PasswordHash($hash_cost_log2, $hash_portable);
    }
    
    // PUBLISHING METHODS
    //////////////////////////////
    
    private function publish_post()
    {       
        // SANITIZE ALL INCOMING DATA
        ////////////////////////////////
        $data = array();
        foreach ($_POST as $field => $value)
            $data[mysql_real_escape_string($field)] = mysql_real_escape_string($value);
        
        // FORMAT / CREATE NEEDED DATA
        /////////////////////////////////
        
        // Slug
        if (isset($data['title']))
            $data['slug'] = Template::MakeSlug($data['title']);
        
        // Categories
        if (isset($data['categories']))
        {       
            $data['categories'] = split(',', $data['categories']);
        
        // CREATE CATEGORY CONNECTIONS
        ////////////////////////////////
        
            Delete::D('posts_categories', array('postsid'=>$data['id']));

            foreach($data['categories'] as $categoriesid)
            {
                // if we've created a NEW category, lets add it to the categories table
                if (!is_numeric($categoriesid))
                {
                    $colors = array('#d84646','#4695d8','#5e9b30');
                    $color_key = rand_array($colors); 
                    Create::C('categories',array('categories'=>$categoriesid,'color'=>$colors[$color_key]));
                    $category = Read::R(array('type'=>'categories','searchBy'=>array('categories'=>$categoriesid),'returnFields'=>array('id')));
                    $categoriesid = $category[0]->the('id');
                }

                Create::C('posts_categories', array('postsid'=>$data['id'],'categoriesid'=>$categoriesid));
            }

            unset($data['categories']);
        }
        
        // @todo: create tag connections
        
        // UPDATE
        ///////////
        
        // Create Response
        $response = array('handler'=>'post_published');
        
        $searchBy = array('id'=>$data['id']);
        $post = Read::R(array('type'=>'posts','searchBy'=>$searchBy,'returnFields'=>array('title','status'),'onlyPublished'=>false));
        
        // Update the Post, set response to true or false depending on success        
        $response['data'] = Update::U('posts', $data, $searchBy);

        // If the Post's title has changed, reload the page
        if (isset($data['title']) && $post[0]->the('title') != $data['title'] && $response['data'] === true)
        {
            $response['handler'] = 'reload';
            $response['data'] = '/posts/' . $data['slug'];
        }
        
        // If the Post's status has changed to or from 'trash', then reload to admin/posts
        if (($data['status'] == 'trash' || ($post[0]->the('status') == 'trash' && $data['status'] != 'trash')) && $response['data'] === true)
        {           
            $response['handler'] = 'reload';            
            $response['data'] = '/admin/posts';
        }
        
        echo json_encode($response);
    }
    
    private function publish_project()
    {
        // SANITIZE ALL INCOMING DATA
        ////////////////////////////////
        $data = array();
        foreach ($_POST as $field => $value)
            $data[mysql_real_escape_string($field)] = mysql_real_escape_string($value);
        
        // FORMAT / CREATE NEEDED DATA
        /////////////////////////////////
        
        // Slug
        if (isset($data['title']))
            $data['slug'] = Template::MakeSlug($data['title']);
        
        // UPDATE
        ///////////
        
        // Create Response
        $response = array('handler'=>'project_published');
        
        $searchBy = array('id'=>$data['id']);
        $project = Read::R(array('type'=>'project','searchBy'=>$searchBy,'returnFields'=>array('title')));
        
        // Update the Post, set response to true or false depending on success        
        $response['data'] = Update::U('projects', $data, $searchBy);
        
        // If the Project's title has changed, reload the page
        if (isset($data['title']) && $project[0]->the('title') != $data['title'] && $response['data'] === true)
        {
            $response['handler'] = 'reload';
            $response['data'] = '/projects/' . $data['slug'];
        }
        
        echo json_encode($response);
    }
    
    // DELETING METHODS
    ///////////////////////
    
    private function delete_post()
    {
        // Get an array of ids to delete
        $ids = split(',', $_POST['ids']);
        
        // Create Response
        $response = array('handler'=>'reload','data'=>null);
        
        // Delete each post in the array
        foreach ($ids as $id)
        {
            Delete::D('posts', array('id'=>$id));
        }
        
        echo json_encode($response);
    }
    
    // TYPEAHEAD METHODS
    //////////////////////////////
    
    private function typeahead_postedit()
    {
        // Create Response
        $response['handler'] = 'save';
        
        $categories = Read::R(array('type'=>'categories'));
        $projects = Read::R(array('type'=>'projects','returnFields'=>array('slug','title')));
        
        $response['data'] = array($categories,$projects);

        echo json_encode($response);
    }
    
    // ECHO PROJECT CONTENT
    /////////////////////////////
    
    private function echo_project()
    {
        $searchBy = array('id'=>$_POST['id']);
        
        $project = Read::R(array('type'=>'projects','searchBy'=>$searchBy));
        $project = $project[0];
        
        // Output Buffer FTW
        ob_start();
        include Template::GetPath() . 'includes/project.phtml';              
        $response['data'] = ob_get_clean();
        
        $response['handler'] = 'test';
         
        echo json_encode($response);
    }
}