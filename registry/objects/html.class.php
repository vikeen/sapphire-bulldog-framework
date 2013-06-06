<?php

/**
 * Reuable HTML
 * Single point for html code that is abundantly used
 */
class Html {

    private $registry;
    private $validAttributes = array(
        'button' => array( 'class', 'id', 'tabindex', 'type' ),
        'input' => array( 'autocomplete', 'class', 'id', 'maxlength', 'name', 'placeholder', 'tabindex', 'type', 'value' ),
        'label' => array( 'class', 'id', 'for', 'name', 'tabindex' ),
        'table' => array( 'class', 'id', 'tabindex' ),
    );

    public function __construct( $registryObj ) {
        $this->registry = $registryObj;
    }

    public function generateElement( $atr ) {
        $html = $this->$atr['element']( $atr );

        // if this field requires a confirmation field then create that now
        if( isset($atr['confirmation']) ) {
            $confirm_atr = $atr;
            $confirm_atr['id']    = isset($confirm_atr['id'])    ? 'confirm_' . $confirm_atr['id'] : '';
            $confirm_atr['name']  = isset($confirm_atr['name'])  ? 'confirm_' . $confirm_atr['name'] : '';
            $confirm_atr['label'] = isset($confirm_atr['label']) ? 'Confirm ' . $confirm_atr['label'] : '';
            $confirm_atr['value'] = isset($confirm_atr['confirmation']['value']) ? $confirm_atr['confirmation']['value'] : '';
            $confirm_atr['class'] = isset($confirm_atr['class']) ? $confirm_atr['class'] . ' confirmation-field' : 'confirmation-field';

            $fieldSeparator = isset($atr['confirmation']['fieldSeparator']) ? $atr['confirmation']['fieldSeparator'] : '</br>';
            $html .= $fieldSeparator;

            $html .= $this->$atr['element']( $confirm_atr );
        }

        // if this fields needs validating then add our placeholder text for a possible error
        if( isset($atr['validate']) and is_array($atr['validate']) ) {
            $html .= '[[sb.' . $atr['id'] . '_error]]';
        }

        return $html;
    }

    /*
     * Generate the Html for a field's attributes
     * An array of attributes associated with a element will be checked against valid attributes for that fieldType
     * If the attribute is valid then it will be added to the html string output for the element
     * @param String: $fieldType - the type of field ( i.e. input or button )
     * @param Array: $elementAttributes - an array of attributes to be checked for this element
     */
    private function getAttributesHtml( $fieldType, $elementAttributes ) {
        $html = '';

        foreach( $elementAttributes as $elementAttributeKey => $elementAttributeValue  ) {
            foreach( $this->validAttributes[$fieldType] as $validAttribute ) {
                if( $elementAttributeKey === $validAttribute ) {
                    $html .= ( isset($elementAttributeValue) and
                              !empty($elementAttributeValue) )
                        ? " $elementAttributeKey=\"$elementAttributeValue\"" : '';
                }
            }
        }

        return $html;
    }

    private function input( $atr ) {
        $html = '';

        // is there a label associated with this field?
        if( isset($atr['label'] ) ) {
            $html .= $this->label($atr);
        }

        $html .= '<input';
        $html .= $this->getAttributesHtml( $atr['element'], $atr );
        $html .= '/>' . "\n";
        return $html;
    }

    private function label( $atr ) {
        if( isset($atr['name']) ) {
            $atr['for'] = $atr['name'];
        }

        $html = '<label';
        $html .= $this->getAttributesHtml( 'label', $atr );
        $html .= '>' . $atr['label'] . '</label>' . "\n";
        return $html;

    }

    private function button( $atr ) {
        $html = '<button';
        $html .= $this->getAttributesHtml( 'button', $atr );
        $html .= '>' . $atr['text'] . '</button>' . "\n";
        return $html;
    }

    private function table( $atr ) {
        if( ! isset($atr['data']) ) {
            Registry::logIt( 'HTML: error creating table element. Missing data for element' );
            return '<table></table>';
        }

        $html = '<table ' . $this->getAttributesHtml( 'table', $atr ) . ">\n";
        $data = $atr['data'];

        // assume that the first row will be the standard for all the data's header and structure
        $headers = array_keys($data[0]);

        $html .= "<tr>\n";
        foreach( $headers as $header ) {
            $html .= "<th>$header</th>\n";
        }
        $html .= "</tr>\n";

        foreach( $data as $row ) {
            $html .= "<tr>\n";
            foreach( $headers as $header ) {
                $html .= '<td>' . $row[$header] . '</td>';
            }
            $html .= "</tr>\n";
        }

        $html .= '</table>';
        return $html;
    }
}
