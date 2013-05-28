<?php

// prevent this file being called directly
if ( !defined( 'FW' ) ) {
    echo 'This file can only be called via the main index.php file, and not directly';
    exit();
}

/**
 * Template manager class
 */
class Template {

    private $page;

    public function __construct()  {
        include( APP_PATH . '/registry/objects/page.class.php');
        $this->page = new Page();
    }

    /**
     * Add a template bit onto our page
     * @param String $tag the tag where we insert the template e.g. {hello}
     * @param String $bit the template bit filename
     * @return void
     */
    public function addTemplateBit( $tag, $bit ) {
        $bitFilePath = 'skins/' . Registry::getSetting('skin') . '/templates/' . $bit;
        $this->page->addTemplateBit( $tag, $bitFilePath );
    }

    /**
     * Put the template bits into our page content
     * Updates the pages content
     * @return void
     */
    private function replaceBits() {
        $bits = $this->page->getBits();
        foreach( $bits as $tag => $template ) {
            $templateContent = file_get_contents( $bit );
            $newContent = str_replace( '{' . $tag . '}', $templateContent, $this->page->getContent() );
            $this->page->setContent( $newContent );
        }
    }

    /**
     * Replace tags in our page with content
     * @return void
     */
    private function replaceTags() {
        // create core tags first
        $this->page->setCoreTags();

        $tags = $this->page->getTags();
        foreach( $tags as $tag => $data ) {
            if( is_array( $data ) ) {

                $dataType = $data[0];

                if( $dataType == 'SQL' ) {
                    $this->replaceDBTags( $tag, $data[1] );
                }
                elseif( $dataType == 'DATA' ) {
                    $this->replaceDataTags( $tag, $data[1] );
                }
            } else {
                $newContent = str_replace( '{' . $tag . '}', $data, $this->page->getContent() );
                $this->page->setContent( $newContent );
            }
        }
    }

    /**
     * Replace content on the page with data from the DB
     * @param String $tag the tag defining the area of content
     * @param int $cacheId the queries ID in the query cache
     * @return void
     */
    private function replaceDBTags( $tag, $cacheId ) {
        $block = '';
        $blockOld = $this->page->getBlock( $tag );

        // foreach record relating to the query...
        while ($tags = Registry::getObject('db')->resultsFromCache( $cacheId ) ) {
            $blockNew = $blockOld;

            // create a new block of content with the results replaced into it
            foreach ($tags as $ntag => $data) {
                $blockNew = str_replace("{" . $ntag . "}", $data, $blockNew); 
            }
            $block .= $blockNew;
        }

        $pageContent = $this->page->getContent();
        $newContent = str_replace( '<!-- START ' . $tag . ' -->' . $blockOld . '<!-- END ' . $tag . ' -->', $block, $pageContent );
        $this->page->setContent( $newContent );
    }

    /**
     * Replace content on the page with data from the cache
     * @param String $tag the tag defining the area of content
     * @param int $cacheId the datas ID in the data cache
     * @return void
     */
    private function replaceDataTags( $tag, $cacheId ) {
        $block = $this->page->getBlock( $tag );
        $blockOld = $block;

        while ($tags = Registry::getObject('db')->dataFromCache( $cacheId ) ) {
            foreach ($tags as $tag => $data) {
                $blockNew = $blockOld;
                $blockNew = str_replace("{" . $tag . "}", $data, $blockNew); 
            }
            $block .= $blockNew;
        }

        $pageContent = $this->page->getContent();
        $newContent = str_replace( $blockOld, $block, $pageContent );
        $this->page->setContent( $newContent );
    }

    /**
     * Get the page object
     * @return Object 
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Set the content of the page based on the templates provided
     * @param Array: $bits - Array of templates to combine together. These create the page's content
     * @return void
     */
    public function buildFromTemplates( $bits ) {
        $content = "";
        $bitPath = 'skins/' . Registry::getSetting('skin') . '/templates/';

        // Append our header template if it exists
        if( file_exists( $bitPath . 'header.tpl.php' ) == true ) {
            $content .= file_get_contents( $bitPath . 'header.tpl.php' );
        }

        // Fill in our function supplied template bits
        foreach( $bits as $bitName ) {
            if( file_exists( $bitPath . $bitName ) == true ) {
                $content .= file_get_contents( $bitPath . $bitName );
            }
        }

        // Append our footer template if it exists
        if( file_exists( $bitPath . 'footer.tpl.php' ) == true ) {
            $content .= file_get_contents( $bitPath . 'footer.tpl.php' );
        }

        $this->page->setContent( $content );
    }

    /**
     * Convert an array of data (i.e. a db row?) to some tags
     * @param array the data 
     * @param string a prefix which is added to field name to create the tag name
     * @return void
     */
    public function dataToTags( $data ) {
        foreach( $data as $key => $content ) {
            $this->page->addTag( $key, $content);
        }
    }

    public function parseTitle() {
        $newContent = str_replace('<title>', '<title>'. $this->page->getTitle(), $this->page->getContent() );
        $this->page->setContent( $newContent );
    }

    /**
     * Parse the page object into some output
     * @return void
     */
    public function parseOutput() {
        $this->replaceBits();
        $this->replaceTags();
        $this->parseTitle();
    }
}
?>
