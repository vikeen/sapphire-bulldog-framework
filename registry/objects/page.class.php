<?php

/**
 * This is our page object
 * It is a seperate object to allow some interesting extra functionality to be added
 * Some ideas: passwording pages, adding page specific css/js files, etc
 */
class Page {

    private $registry;

    // room to grow later?
    private $css = array();
    private $js = array();

    // future functionality?
    private $authorized = true;
    private $password = '';

    // page elements
    private $title = '';
    private $tags = array();
    private $postParseTags = array();
    private $bits = array();
    private $content = "";

    /**
     * Constructor...
     */
    function __construct( $registryObj ) {
        $this->registry = $registryObj;

        // set default css files
        $this->addExternalFile( array(
            'type' => 'css',
            'url' => 'http://yui.yahooapis.com/pure/0.1.0/pure-min.css' )
        );
    }

    public function setCoreTags() {
        $skinDir = $this->registry->getSetting('skin_dir');

        // add our skin's css files
        $this->addExternalFile( array(
            'type' => 'css',
            'url' => $skinDir . '/css/style.css' )
        );

        $cssTag = '';
        foreach ( $this->css as $cssHTML ) {
            $cssTag .= $cssHTML . "\n";
        }
        $this->addTag( 'css', $cssTag);

        // add our skin's js files
        $this->addExternalFile( array(
            'type' => 'js',
            'url' => $skinDir . '/js/script.js' )
        );

        $jsTag = '';
        foreach ( $this->js as $jsHTML ) {
            $jsTag .= $jsHTML . "\n";
        }
        $this->addTag( 'js', $jsTag );

        return;
    }

    public function getTitle() {
        return $this->title;
    }

    /*
     * Add an external file to the page
     * Example: JS / CSS
     * @param Array: $params - an array of the parameters used for this file
     *  - type
     *  - url
     * @return void
     */
    public function addExternalFile( $params ) {
        if( isset($params['type']) ) {
            $url = $params['url'];
            if ( stripos($url, 'http://')  !== 0 && stripos($url, 'https://') !== 0 ) {
               $url = $this->registry->getSetting('site_url') . $url;
            }

            $type = $params['type'];
            if( $type === 'js' ) {
                array_push( $this->js, '<script src="' . $url . '"></script>' );
            }
            else if( $type === 'css' ) {
                array_push( $this->css, '<link rel="stylesheet" href="' . $url . '">' ); // yui pure css
            } else {
                // issue error here
            }
        }
    }

    public function setPassword( $password ) {
        $this->password = $password;
    }

    public function setTitle( $title ) {
        $this->title = $title;
    }

    public function setContent( $content ) {
        $this->content = $content;
    }

    public function addTag( $key, $data ) {
        $this->tags[$key] = $data;
    }

    /*
     * Add a little extra text by default here because we know it's a form error
     */
    public function addErrorTag( $key, $data ) {
        $data = '<div class="sbf-form-errors">' . $data . '</div>';
        $this->tags[$key . '_error'] = $data;
    }

    public function getTags() {
        return $this->tags;
    }

    public function addPPTag( $key, $data ) {
        $this->postParseTags[$key] = $data;
    }

    /**
     * Get tags to be parsed after the first batch have been parsed
     * @return array
     */
    public function getPPTags() {
        return $this->postParseTags;
    }

    /**
     * Add a template bit to the page, doesnt actually add the content just yet
     * @param String the tag where the template is added
     * @param String the template file name
     * @return void
     */
    public function addTemplateBit( $tag, $bit ) {
        $this->bits[ $tag ] = $bit;
    }

    /**
     * Get the template bits to be entered into the page
     * @return array the array of template tags and template file names
     */
    public function getBits() {
        return $this->bits;
    }

    /**
     * Gets a chunk of page content
     * @param String the tag wrapping the block ( <!-- START tag --> block <!-- END tag --> )
     * @return String the block of content
     */
    public function getBlock( $tag ) {
        preg_match ('#<!-- START '. $tag . ' -->(.+?)<!-- END '. $tag . ' -->#si', $this->content, $tor);

        $tor = str_replace ('<!-- START '. $tag . ' -->', "", $tor[0]);
        $tor = str_replace ('<!-- END '  . $tag . ' -->', "", $tor);

        return $tor;
    }

    public function getContent() {
        return $this->content;
    }
}
?>
