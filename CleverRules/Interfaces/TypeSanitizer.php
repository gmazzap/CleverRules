<?php
namespace CleverRules\Interfaces;


/**
 * TypeSanitizer Interface
 * Implemented by all the rule field sanitizer classes
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface TypeSanitizer {


    /**
     * Perform the sanitization of the given value
     * 
     * @param mixed $value The value to sanitize
     * @return mixed sanitied value or null on wrong values
     * @access public
     */
    function sanitize( $value );


}


