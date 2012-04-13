<?php
/**
 * Hedgehog's Effervescent Error class
 * 
 * @todo expand this to handle more errors
 *
 * @author wtfribley
 */
class Error {
    
    function __construct($error)
    {
        ($error == '404') ? $error = 'fourOfour' : $error = $error;
        $this->$error();
    }
    
    public function fourOfour()
    {
        $path = Template::GetPath();
        require $path . '/404.phtml';
    }
    
    public function ClassNotFound()
    {
        return false;
    }
}

?>
