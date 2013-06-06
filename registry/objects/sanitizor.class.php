<?php

/**
 * Sanitizor Tool
 * Use this class to help ensure data is properly sanitized before using.
 */
class Sanitizor {

    public function __construct() {
    }

    /**
     * Sanitize only one variable.
     * Returns the variable sanitized according to the desired type or true/false
     * for certain data types if the variable does not correspond to the given data type.
     *
     * NOTE: True/False is returned only for telephone, pin, id_card data types
     *
     * @param mixed The variable itself
     * @param string A string containing the desired variable type
     * @return The sanitized variable or true/false
     */
    public function sanitizeOne( $var, $type ) {
        switch( $type ) {
            case 'int': // integer
                return (int)$var;

            case 'str': // trim string
                return trim($var);

            case 'nohtml': // trim string, no HTML allowed
                return htmlspecialchars(trim($var));

            case 'plain': // trim string, no HTML allowed, plain text
                return strip_tags(trim($var));

            case 'upper_word': // trim string, upper case words
                return ucwords(strtolower(trim($var)));

            case 'ucfirst': // trim string, upper case first word
                return ucfirst(strtolower(trim($var)));

            case 'lower': // trim string, lower case words
                return strtolower(trim($var));

            case 'urle': // trim string, url encoded
                return urlencode(trim($var));

            case 'trim_urle': // trim string, url decoded
                return urldecode(trim($var));

            case 'telephone': // True/False for a telephone number
                $size = strlen($var) ;
                for( $x = 0; $x < $size; $x++ ) {
                    if( ! ( (ctype_digit($var[$x]) || ($var[$x]=='+') || ($var[$x]=='*') || ($var[$x]=='p')) ) ) {
                        return false;
                    }
                }
                return true;

            case 'pin': // True/False for a PIN
                if( (strlen($var) != 13) || (ctype_digit($var)!=true) ) {
                    return false;
                }
                return true;

            case 'id_card': // True/False for an ID CARD
                if( (ctype_alpha(substr($var , 0 , 2)) != true ) || (ctype_digit(substr($var , 2 , 6) ) != true ) || (strlen($var) != 8)) {
                    return false;
                }
                return true;

            case 'sql': // True/False if the given string is SQL injection safe
                //  insert code here, I usually use ADODB -> qstr() but depending on your needs you can use mysql_real_escape();
                return mysql_real_escape_string($var);

            default:
                Registry::logIt( "SANITIZOR: Invalid sanitization type provided >$type<" );
                break;
        }
        return false;
    }

    /**
     * Sanitize an array.
     *
     * sanitize($_POST, array('id'=>'int', 'name' => 'str'));
     * sanitize($customArray, array('id'=>'int', 'name' => 'str'));
     *
     * @param array $data
     * @param array $whatToKeep
     */
    public function sanitize( &$data, $whatToKeep ) {
        $data = array_intersect_key( $data, $whatToKeep );

        foreach( $data as $key => $value ) {
            $data[$key] = $this->sanitizeOne( $data[$key], $whatToKeep[$key] );
        }
    }

    public function sanitizePOST( $key, $type ) {
        if( isset($_POST[$key]) ) {
            return $this->sanitizeOne( $_POST[$key], $type);
        }
        return '';
    }
}
