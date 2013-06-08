<?php
/*
 * Admin Dashboard
 * Standard Dashboard for sb_framework backend usage
 *
 * @author: John Rake
 */

$template = Registry::getObject('template');
$sanitizor = Registry::getObject('sanitizor');

// admin js is powered by YUI, lets add it
$template->getPage()->addExternalFile( array(
    'type' => 'js',
    'url' => 'http://yui.yahooapis.com/3.10.1/build/yui/yui-min.js' )
);

// sanitize all needed values, discard the rest
$sanitizor->sanitize( $_GET, array( 'page' => 'nohtml', 'job' => 'nohtml' ) );

// did the user request a specific page?
if( isset($_GET['page']) ) {
    $pageName = $_GET['page'];
    $pageJob = isset($_GET['job']) ? $_GET['job'] : null;

    Registry::storeSetting( array( 'page_job' => $pageJob ) );

    $template->buildFromTemplates( array( $pageName ) );
    $template->getPage()->setTitle( 'Dashboard | ' . ucwords($pageName) );
} else {
    // couldn't find a page to go to ... revert user back to the dasboard
    $template->buildFromTemplates();
    $template->getPage()->setTitle( 'Dashboard' );
}

// parse it all, and spit it out
$template->parseOutput();
print $template->getPage()->getContent();
?>
