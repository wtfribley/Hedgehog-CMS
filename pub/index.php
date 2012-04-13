<?php

/**
 *      "Hedgehog" CMS - custom blogging platform and learning endeavor
 * 
 *      Created by me, with help from the Internet. 
 */


// Set Base Path
defined("BASE_PATH")
    || define("BASE_PATH", realpath(dirname(__FILE__) . '/../'));

// Set Application Path
defined("APP_PATH")
    || define("APP_PATH", BASE_PATH . '/app');

// Set Host
defined("HOST")
    || define("HOST", 'http://' . $_SERVER['HTTP_HOST']);

// Block Direct Access
defined("NON_DIRECT")
    || define("NON_DIRECT", true);


// Bootstrap this sh*t

require_once APP_PATH . "/bootstrap.php";

$bootstrap = new Bootstrap;
$bootstrap->run();