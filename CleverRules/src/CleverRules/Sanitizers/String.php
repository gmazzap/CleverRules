<?php
namespace CleverRules\Sanitizers;

use CleverRules\Interfaces as CRI;

/**
 * String Class
 * Used to sanitize the string arguments
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class String implements CRI\TypeSanitizer {


    public function sanitize( $value ) {
        return \preg_match( '/^[a-z0-9_\.]+$/', $value ) === 1 ? $value : null;
    }


}