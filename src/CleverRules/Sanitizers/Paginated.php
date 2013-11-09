<?php
namespace CleverRules\Sanitizers;

use CleverRules\Interfaces as CRI;

/**
 * Paginated Class
 * Used to sanitize the paginated rule argument
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Paginated implements CRI\TypeSanitizer {


    public function sanitize( $value ) {
        if ( $value === 'single' ) return $value;
        return (bool) $value;
    }


}