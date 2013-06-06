<?php
/**
 * Framework
 * Framework loader - acts as a single point of access to the Framework
 *
 * @version 0.1
 * @author John Rake
 */

include( 'config.php' );

/**
 * Magic autoload function
 * used to include the appropriate -controller- files when they are needed
 * @param String the name of the class
 */
function __autoload( $class_name ) {
    require_once('controllers/' . $class_name . '/' . $class_name . '.php' );
}

// require our registry
require_once('registry/registry.class.php');
$registry = Registry::singleton();

// load our skin's index.php file
require_once( $registry->getSetting('skin_dir') . '/index.php' );

exit();
?>
