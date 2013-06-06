<?php
$template = Registry::getObject('template');
$template->buildFromTemplates( array( 'login' ), false, false );
$template->getPage()->setTitle( 'Login' );

// parse it all, and spit it out
$template->parseOutput();
print $template->getPage()->getContent();
?>
