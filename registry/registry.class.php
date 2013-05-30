<?php
/**
 * The Registry object
 * Implements the Registry and Singleton design patterns
 *
 * @version 0.1
 * @author John Rake
 */
class Registry {

    private static $objects = array();
    private static $settings = array();
    private static $frameworkName = ' Framework version 0.1';
    private static $instance;

    private function __construct() {
    }

    private function storeCoreObjects() {
        self::$instance->storeObject( array(
            'db' => 'database',
            'template' => 'template'
            )
        );
    }

    private function storeCoreSettings() {
        $dbh = self::$instance->getObject('db')->newConnection('localhost', 'root', 'qpwo1q2w3eEWQ', 'sb_framework');

        // @TODO:this process is a little redundent with rearranging arrays to get the settings stored.
        //       could use some improvement
        $sql = 'SELECT param_key,
                       param_value
                FROM sb_config'
        ;

        $dbh->executeQuery( $sql );
        $rows = $dbh->getRows();

        $settings = array();
        foreach( $rows as $row ) {
            $settings[ $row['param_key'] ] = $row['param_value'];
        }

        // fill in our config gaps
        $settings['skin_dir'] = 'skins/' . $settings['skin'];
        define( "SKIN_DIR", $settings['skin_dir'] );

        // store all our database here
        self::$instance->storeSetting( $settings );
    }

    /**
     * singleton method used to access the object
     * @access public
     * @return 
     */
    public static function singleton() {
        if( !isset( self::$instance ) ) {
            $obj = __CLASS__;
            self::$instance = new $obj;
        }

        self::$instance->storeCoreObjects();
        self::$instance->storeCoreSettings();

        return self::$instance;
    }

    /**
     * prevent cloning of the object: issues an E_USER_ERROR if this is attempted
     */
    public function __clone() {
        trigger_error( 'Cloning the registry is not permitted', E_USER_ERROR );
    }

    /**
     * Stores an object(s) in the registry
     * @param Array: $objectList - an array of objects to store in the registry
     * @return void
     */
    public function storeObject( $objectList ) {
        foreach ( $objectList as $key => $object ) {
            require_once('objects/' . $object . '.class.php');
            self::$objects[ $key ] = new $object( self::$instance );
        }
    }

    /**
     * Gets an object from the registry
     * @param String $key the array key
     * @return object
     */
    public function getObject( $key ) {
        if( is_object ( self::$objects[ $key ] ) ) {
            return self::$objects[ $key ];
        }
    }

    /**
     * Stores settings in the registry
     * @param Array: $dataList - an array of data to store in the registry
     * @return void
     */
    public function storeSetting( $dataList ) {
        foreach ( $dataList as $key => $data ) {
            self::$settings[ $key ] = $data;
        }
    }

    /**
     * Gets a setting from the registry
     * @param String $key the key in the array
     * @return void
     */
    public function getSetting( $key ) {
        return self::$settings[ $key ];
    }

    /**
     * Gets the frameworks name
     * @return String
     */
    public function getFrameworkName() {
        return self::$frameworkName;
    }
}

?>
