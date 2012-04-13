<?php

/**
 * Hedgehog's big bootstrapping class.
 * 
 * This includes a custom Zend-style autoloader - classes are named following
 * their directory structure.
 * Ex: Config_DB() is located at app/classes/config/DB.php
 *
 * @author wtfribley <admin@westonfribley.com>
 * 
 */

class Bootstrap {
    
    private $config = array();
    
    public function run()
    {
        if (file_exists(BASE_PATH . '/config.php'))
        {
            require BASE_PATH . '/config.php';
        }
        else { echo "No Config File Present at: " . BASE_PATH; }
        
        $this->config = $configuration;
        
        $this->setEnvironment($this->config['dev_env']);
        $this->removeMagicQuotes();
        
        // Register Autoloader
        spl_autoload_register(array($this, 'hedgehogLoader'));
        
        // Start our session
        Session::start();
        
        // Cofigure the DB class
        DB::SetDBDetails($this->config['db']);
        
        // Run Hedgehog, Run
        Hedgehog::run($this->config['theme']);
    }
    
    private function setEnvironment($dev_env)
    {
        if ($dev_env == true) {
		error_reporting(E_ALL);
		ini_set('display_errors','On');
	} else {
		error_reporting(E_ALL);
		ini_set('display_errors','Off');
		ini_set('log_errors', 'On');
		ini_set('error_log', BASE_PATH . 'tmp/logs/error.log');
	}
    }
    
    private function removeMagicQuotes()
    {
        if (get_magic_quotes_gpc()) {
		$_GET = $this->stripSlashesDeep($_GET);
		$_POST = $this->stripSlashesDeep($_POST);
		$_COOKIE = $this->stripSlashesDeep($_COOKIE);
	}
    }
    
    private function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map(array($this, 'stripSlashesDeep'), $value) : stripslashes($value);
	return $value;
    }
    
    private function hedgehogLoader($classname)
    {
        $classpath = str_replace('_', '/', $classname);
        $classpath = APP_PATH . '/classes/' . $classpath . '.php';
        $libpath = BASE_PATH . '/lib/' . $classname . '.php';
        
        if (file_exists($classpath))
        {
            include $classpath;
        }
        elseif (file_exists($libpath))
        {
            include $libpath;
        }
        else
        {
            return new Error('ClassNotFound');
        }
         
    }
}