<?php
/**
 * Hedgehog's Index Controller
 * 
 * The Index Controller has only one view - if it receives any other arguments
 * it simply redirects back to Index. 
 *
 * @author wtfribley
 */
class controllers_index {
    
    /*
     * This stores the template path.
     */
    private $path;
    
    function __construct($action)
    {
        $this->path = Template::GetPath();
        
        if (method_exists($this, $action))
        {
            $this->$action();
        }
        else
        {
            $this->SendToIndex();
        }
    }
    
    public function index()
    {
        // Get Projects
        $projects = Read::R(array('type'=>'projects'));
        
        require $this->path . '/index.phtml';
    }
    
    public function SendToIndex()
    {
        header('Location: /');
        exit();
    }
}