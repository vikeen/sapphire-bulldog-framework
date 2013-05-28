<?php
/**
 * Framework
 * Framework loader - acts as a single point of access to the Framework
 *
 * @version 0.1
 * @author Michael Peacock
 */

// first and foremost, start our sessions
session_start();

// setup some definitions
// The applications root path, so we can easily get this path from files located in other folders
define( "APP_PATH", dirname( __FILE__ ) ."/" );
// We will use this to ensure scripts are not called from outside of the framework
define( "FW", true );

# @TODO make a setting
#date_default_timezone_set("Europe/London");

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

// store those core objects
$registry->storeCoreObjects();

// create a database connection
$registry->getObject('db')->newConnection('localhost', 'root', 'qpwo1q2w3eEWQ', 'framework');

// set the default skin setting (we will store these in the database later...)
$registry->storeSetting( array(
    'skin' => 'default',
    )
);

// populate our page object from a template file
$registry->getObject('template')->buildFromTemplates('main.tpl.php');

// cache a query of our members table
$cache = $registry->getObject('db')->cacheQuery('SELECT * FROM members');

// assign this to the members tag
$registry->getObject('template')->getPage()->addTag('members', array('SQL', $cache) );

// set the page title
$registry->getObject('template')->getPage()->setTitle('Our members');

// parse it all, and spit it out
$registry->getObject('template')->parseOutput();
print $registry->getObject('template')->getPage()->getContent();

exit();

?>
