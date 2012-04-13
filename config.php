<?php

/*
 *      Welcome to Hedgehog!
 *
 * 
 * Set Your DB Details Here 
 */
$host = 'localhost';
$username = 'root';
$password = 'root';
$database_name = 'wtf3';

/*
 * Set Your Theme Here
 */
$theme = 'default';

/*
 * Set The Development Environment - False should fine!
 */
$dev_env = 'true';


// Simply add all these variables to an array for later use
$configuration = array(
    'db' => array(
        'host' => $host,
        'user' => $username,
        'pass' => $password,
        'name' => $database_name
    ),
    'dev_env' => $dev_env,
    'theme' => $theme
);