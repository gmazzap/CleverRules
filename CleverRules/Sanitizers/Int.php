<?php
namespace CleverRules\Sanitizers;

use CleverRules\Interfaces as CRI;

/**
 * Int Class
 * Used to sanitize integer arguments
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Int implements CRI\TypeSanitize {


    public function sanitize( $value ) {
        return ( \is_int( $value ) ) ? $value : null;
    }


}