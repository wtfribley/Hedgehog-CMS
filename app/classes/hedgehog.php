<?php
/**
 * This is the Front Controller for Hedgehog
 *
 * @author wtfribley
 */
class Hedgehog {
    
    public static function run($theme)
    {
        
        // Set Template Paths and Charset
        Template::SetFilePath(BASE_PATH . '/pub/themes/' . $theme . '/');
        Template::SetWebPath('/themes/' . $theme . '/');
        Template::SetCharset('utf-8');
        
        // Get Controller
        $controller = 'controllers_' . self::getURIController();
        
        // Get Action
        $action = self::getURIAction();
        
        // Set the Title
        Template::SetTitle($controller, $action);
        
        // Set the Page Count.
        $pageCount = Session::get('pageCount');
        if ($pageCount === false) {
            Session::set('pageCount', 1);
        }
        else
        {
            Session::set('pageCount', ++$pageCount);
        }
        
            // FOR DEVElOPERS: uncomment to reset pageCount
            //                 OR keep pageCount at 1
            // Session::erase('pageCount');
            // Session::set('pageCount', 1);
                
        if (isset($_POST['action']))
        {
            $ajax = new controllers_ajax($_POST['action']);
            exit();
        }
        
        // Display
        if (class_exists($controller))
        {
            $display = new $controller($action);
        }
        else
        {
            $display = new controllers_index('SendToIndex');
        }
        
        
    }
    
    private static function getUri()
    {
        if (isset($_SERVER['PATH_INFO']))
        {
            $uri = $_SERVER['PATH_INFO'];
        }
        elseif (isset($_SERVER['REQUEST_URI']))
        {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if ($uri === false)
                throw new Exception('Request URI is Deformed');
        }
        else
        {
            throw new Exception('Unable to get a requested URI');
        }
        
        return $uri;
    }
    
    private static function getURIController()
    {
        $uri = trim(self::getUri(), '/');
        
        $segments = explode('/', $uri);
        
        $return = false;
        
        (isset($segments[0]) && $segments[0]!='') ? $return = $segments[0] : $return = 'index';
        
        return $return;
    }
    
    private static function getURIAction()
    {
        $uri = trim(self::getUri(), '/');
        
        $segments = explode('/', $uri);
        
        (isset($segments[1])) ? $return = $segments[1] : $return = 'index';
        
        return $return;
    }
}