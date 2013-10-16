<?php
namespace CleverRules\Sanitizers;

use CleverRules\Interfaces as CRI;

/**
 * Bool Class
 * Used to sanitize bool arguments
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Bool implements CRI\TypeSanitizer {


    public function sanitize( $value ) {
        return (bool) $value;
    }


}