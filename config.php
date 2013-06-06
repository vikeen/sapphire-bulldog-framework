<?php
/**
 * Framework
 * Framework loader - acts as a single point of access to the Framework
 *
 * @version 0.1
 * @author John Rake
 */

// first and foremost, start our sessions
session_start();

// setup some definitions
// The applications root path, so we can easily get this path from files located in other folders
define( "APP_PATH", dirname( __FILE__ ) ."/" );

// We will use this to ensure scripts are not called from outside of the framework
define( "FW", true );
?>
