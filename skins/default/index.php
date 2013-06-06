<?php
/*
 * Default Skin
 * Index.php - Home page for default skin
 *
 * @author: John Rake
 */

$template = Registry::getObject('template');

// populate our page object from a template file
$template->buildFromTemplates( array( 'main.tpl.php' ) );

// cache a query of our members table
$cache = $registry->getObject('db')->cacheQuery('SELECT first_name, last_name, email FROM sb_member');

// assign this to the members tag
$template->getPage()->addTag('member', array('SQL', $cache) );
$template->getPage()->setTitle('Our members');

// parse it all, and spit it out
$template->parseOutput();
print $template->getPage()->getContent();

?>
