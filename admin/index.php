<?php
/**
 * Framework
 * Framework loader - acts as a single point of access to the Framework
 *
 * @version 0.1
 * @author John Rake
 */

include( '../config.php' );

/**
 * Magic autoload function
 * used to include the appropriate -controller- files when they are needed
 * @param String the name of the class
 */
function __autoload( $class_name ) {
    require_once( APP_PATH . 'controllers/' . $class_name . '/' . $class_name . '.php' );
}

// require our registry
require_once( APP_PATH . 'registry/registry.class.php' );
Registry::singleton();

// change our skin directory to the admin verison
Registry::storeSetting( array( 'skin_dir' => 'admin/skins/' . Registry::getSetting('admin_skin') ) );

#if( $_SESSION['logged_in'] === true ) {
    require_once( 'dashboard.php' );
#} else {
#    require_once( 'login.php' );
#}

exit();
?>
