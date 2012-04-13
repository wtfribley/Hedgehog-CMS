<?php

/**
 * Hedgehog's Tubular Template class
 *
 * @author wtfribley
 */

class Template {
    
    private static $path, $webpath, $charset, $title, $pagetype;

    public static function SetFilePath($path)
    {
        self::$path = $path;
    }
    
    public static function SetWebPath($path)
    {
        self::$webpath = $path;
    }
    
    public static function GetPath($web = false)
    {
        ($web === false) ? $path = self::$path : $path = self::$webpath;
        return $path;
    }
    
    public static function SetCharset($charset)
    {
        self::$charset = $charset;
    }
    
    public static function Charset()
    {
        echo '<meta charset="' . self::$charset . '" />';
    }
    
    public static function SetPagetype($pagetype)
    {
        self::$pagetype = $pagetype;
    }
    
    public static function Pagetype()
    {
        return self::$pagetype;
    }
    
    /**
     * Sets the title, given the controller and action...
     * A better way is (probably) to call a similar type of
     * function from each individual controller.
     * OR run a DB query here to pull up the TRUE post/project title.
     * 
     * @param string $controller
     * @param string $action 
     */
    
    public static function SetTitle($controller, $action)
    {
        $title = '';
        $divider = ' | ';
        
        $controller = str_replace('controllers_', '', $controller);
        
        ($controller == 'index') ? $title = $title : $title = $divider . ucwords($controller);
        
        ($action == 'index') ? $title = $title : $title = $divider . ucwords(str_replace('-', ' ', $action));
        
        self::$title = $title;
    }
    
    public static function Title()
    {
        echo self::$title;
    }
    
    /**
     * Include a file in the header
     * 
     * Default functionality is to include all files in the css and js directories.
     * 
     * OR takes the filename - WITH extension OR an absolute path beginning with 'http://'
     * The function will determine the filetype and echo the appropriate html.
     * 
     * @param string $file
     */
    
    public static function HeadInclude($file = false)
    {
        if ($file === false)
        {
            self::cssInclude($file);
            self::jsInclude($file);
        }
        elseif (is_int(strpos($file, 'http://')))
        {
            if (is_int(strpos($file , 'css')))
            {
                self::cssInclude($file);
            }
            elseif (is_int(strpos($file, 'js')))
            {
                self::jsInclude($file);
            }
            /**
             * @todo include error handling if cannot determine resource type 
             */
        }
        else
        {
            $file = explode('.', $file);
        
            if (count($file) > 1)
            {
                $method = $file[1] . 'Include';
                $file = implode('.', $file);
                self::$method($file);
            }
            else
            {
                $method = $file[0] . 'Include';
                self::$method();
            }
        }
        
    }
    
    private static function cssInclude($file)
    {
        if ($file === false)
        {
            $include = '';
            $cssfiles = array_splice(scandir(self::$path . 'css/'), 2);
            
            foreach($cssfiles as $cssfile)
            {
                if (strpos($cssfile, '.') != 0)
                    $include.= '<link rel="stylesheet" type="text/css" href="' . self::$webpath . 'css/' . $cssfile . '" />';
            }
            
        }
        else
        {
            (is_int(strpos($file, 'http://'))) ? $path = $file : $path = self::$webpath . 'css/' . $file;
            $include = '<link rel="stylesheet" type="text/css" href="' . $path . '" />';   
        }        
        echo $include;
        
    }
    
    private static function jsInclude($file)
    {
        if ($file === false)
        {
            $include = '';
            $jsfiles = array_splice(scandir(self::$path . 'js/'), 2);
            
            foreach($jsfiles as $jsfile)
            {
                if (strpos($jsfile, '.') != 0)
                    $include.= '<script src="' . self::$webpath . 'js/' . $jsfile . '"></script>';
            }
        }
        else
        {
            (is_int(strpos($file, 'http://'))) ? $path = $file : $path = self::$webpath . 'js/' . $file;
            $include = '<script src="' . $path . '"></script>';
        }       
        echo $include;
    }
    
    private static function utilsInclude()
    {
        $path = HOST . '/js/';
        
        // jQuery
        $jquery = array(
            'jquery-1.7.1.min.js',
            'jquery.color.js',
            'jquery.ba-hashchange.min.js',
            'jquery-ui-1.8.16.custom.min.js',
            'jquery.thmb-gallery.js'
        );               
        foreach($jquery as $file)
        {
            $filepath = $path . 'jquery/' . $file;
            echo '<script src="' . $filepath . '"></script>';            
        }
            echo '<link rel="stylesheet" type="text/css" href="' . $path . 'jquery/jquery-ui-1.8.16.custom.css" />';
        
        // Bootstrap by Twitter
        $bootstrap = array(
            'bootstrap-button.js',
            'bootstrap-carousel.js',
            'bootstrap-collapse.js',
            'bootstrap-dropdown.js',
            'bootstrap-modal.js',
            'bootstrap-transition.js',
            'bootstrap-typeahead.js'
        );       
        foreach ($bootstrap as $file)
        {
            $filepath = $path . 'bootstrap/' . $file;
            echo '<script src="' . $filepath . '"></script>';
        }
        
        // Hedgehog JS Utilities
        $utils = array(
            'hedgehog-form.js',
            'hedgehog-ajax.js'
        );
        if (User::Verify('admin'))
            $utils = array_merge(array('rangy-core.js','rangy-cssclassapplier.js','rangy-selectionsaverestore.js','hedgehog-rte.js', 'hedgehog-posteditor.js','hedgehog-projecteditor.js'),$utils);    

        foreach($utils as $file)
        {
            echo '<script src="' . $path . $file . '"></script>';
        }                    
    }
    
    public static function MakeSlug($title)
    {
        $slug = urlencode(str_replace(' ', '-', strtolower($title)));
        $slug = preg_replace('/%[\w]{2}/', '', $slug);
        
        return $slug;
    }
    
    public static function ReadSlug($slug)
    {
        $title = str_replace('-',' ',urldecode($slug));
        
        return $title;
    }
    
    public static function CategoryList()
    {
        $categories = Read::R(array('type'=>'categories'));
        
        $categoriesArray = array();
        
        foreach ($categories as $c)
        {
            $category = $c->the('categories');
            $count = count(Read::R(array('type'=>'posts','searchBy'=>array('categories'=>$category),'returnFields'=>array('id'))));
            if ($count > 0)
                $categoriesArray[$category] = $count;
        }
        
        arsort($categoriesArray);
        
        return $categoriesArray;
    }
    
    public static function ProjectList()
    {
        $projects = Read::R(array('type'=>'projects','returnFields'=>array('slug','title')));
        
        $projectsArray = array();
        
        foreach ($projects as $p)
        {
            $slug = $p->the('slug');
            $title = $p->the('title');
            $projectsArray[$title] = $slug;
        }
        
        return $projectsArray;
    }
    
    public static function ProjectGallery()
    {
        $projects = Read::Project();
        
        $html = '<ul class="project-gallery unstyled">';
        
        foreach ($projects as $project)
        {
            $html.= '<li class="project-gallery-thmb"><img src="/uploads/images/' . $project->the('thmb') . '" />';
            $html.= '<div class="project-gallery-overlay"><h1>' . $project->the('title') . '</h1>';
            
            if ($project->has('categories'))
            {
                $html.= '<ul class="unstyled">';
                
                foreach ($project->the('categories') as $category => $id)
                {
                    $html.= '<li><h2 data-category-id="' . $id . '">' . $category . '</h2></li>';              
                }
                $html.= '</ul>';
            }
            
            $html.= '</div></li>';
            
            include self::GetPath() . 'includes/project.phtml';
            
            $html.= project($project);
        }
        
        $html.= '</ul>';
        
        echo $html;
    }
}