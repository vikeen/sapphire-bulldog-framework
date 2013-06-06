<?php

/**
 * Validation Tool
 * This will be used to validate form processed data to meet certain criteria
 */
class Validator {

    public function __construct() {
    }

    /**
     * Takes an array of fields to validate
     * @param Array: $fields - an array of fields and validation criteria
     *   - name - the field name to use for display in error messages
     *   - value - the field value to validate
     *   - validations - an array of validation types to run for this field / value
     ** @return void
     */
    public function validate( $fields ) {
        $fieldErrorMessages = array();

        foreach( $fields as $key => $fieldData ) {
            if( isset($fieldData['validate']) ) {
                $result = $this->validateField( $key,
                                                $fieldData['validate']['name'],
                                                $fieldData['validate']['value'],
                                                $fieldData['validate']['validations'] );
                if( is_array($result) ) {
                    $fieldErrorMessages[$key] = $result;
                }
            }
        }

        Registry::logIt( 'VALIDATOR: ' . count($fieldErrorMessages) . ' fields failed validation' );
        return $fieldErrorMessages;
    }

    /*
     * Validate a field
     * validates a field for each validation type passed in
     * @param String: $key - the index key for any array of this field
     * @param String: $name - the name of this field
     * @param String: $value - the value of this field to validate
     * @param String: $validations - an array of validation types to use
     * @return - True if validation passed
     *         - Array of error messages if validation failed
     */
    private function validateField( $key, $name, $value, $validations ) {
        $errorMessages = array();

        foreach( $validations as $validation ) {
            $errorMessage;

            //Registry::logIt( "VALIDATOR: validating >$key< for >$validation< validation" );

            switch( $validation ) {
                case 'required':
                    if( ! $this->requiredCheck( $value ) ) {
                        $errorMessage = $this->createErrorMessage( $key, $name, $value, $validation );
                    }
                    break;
                default:
                    Registry::logIt( "VALIDATOR: invalid validation type >$validation<" );
            }

            // add our error message to the output array
            if( isset($errorMessage) ) {
                array_push( $errorMessages, $errorMessage );
            }
        }

        if( count($errorMessages) > 0 ) {
            return $errorMessages;
        }
        return true; // no issues found
    }

    /*
     * Required check for validation
     */
    private function requiredCheck( $value ) {
        if( strlen($value) > 0 ) {
            return true;
        }
        return false;
    }

    /*
     * Create an error message for a given field
     */
    private function createErrorMessage( $key, $name, $value, $validation ) {
        $errorMessage = "$name failed $validation validation";;
        Registry::logIt( "VALIDATOR: field >$key< failed validation >$validation<" );
        return $errorMessage;
    }
}
