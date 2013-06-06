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
    private static $frameworkName = 'SB Framework version 0.1';
    private static $instance;
    private static $logFile;

    private function __construct() {
    }

    public function logIt( $message ) {
        $message = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
        fwrite( self::$instance->logFile, $message );
    }

    private function storeCoreObjects() {
        $coreObjects = array(
            'bcrypt' => 'bcrypt',
            'db' => 'database',
            'html' => 'html',
            'sanitizor' => 'sanitizor',
            'template' => 'template',
            'validator' => 'validator',
        );

        self::$instance->logIt( 'REGISTRY: Storing ' . count( $coreObjects ) . ' framework objects' );

        self::$instance->storeObject( $coreObjects );
    }

    private function storeCoreSettings() {
        $db = self::$instance->getObject('db');
        $dbh = $db->newConnection('localhost', 'root', 'qpwo1q2w3eEWQ', 'sb_framework'); // create a database connection and return our PDO reference
        $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

        $sql = 'SELECT param_key, param_value FROM sb_config';
        $sth = $dbh->prepare( $sql );
        $sth->setFetchMode(PDO::FETCH_ASSOC);
        $sth->execute();

        $settings = array();
        while( $row = $sth->fetch() ) {
            $settings[ $row['param_key'] ] = $row['param_value'];
        }

        // fill in our config gaps
        $settings['skin_dir'] = 'skins/' . $settings['skin'];
        $settings['site_url'] = $settings['site_url_protocol'] . '://' . $settings['site_url_host'] . '/';

        self::$instance->logIt( 'REGISTRY: Storing ' . count( $settings ) . ' framework settings' );

        // store all our settings
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

        $logFilePath = APP_PATH . 'log/sb-framework-' . date( 'Ymd' ) . '.log';
        self::$instance->logFile = fopen( $logFilePath, 'a' ) or die( "failed to open log file >" . $logFilePath . "<" );
        fwrite( self::$instance->logFile, "\n\n" ); // create some room from the previous run in the logs
        self::$instance->logIt( 'REGISTRY: ' . self::$instance->getFrameworkName() . ' instantiated' );

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
