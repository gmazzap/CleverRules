<?php
namespace CleverRules\Sanitizers;

use CleverRules\Interfaces as CRI;

/**
 * SimpleArray Class
 * Used to sanitize the array arguments
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class SimpleArray implements CRI\TypeSanitizer {


    public function sanitize( $value ) {
        $value = (array) \wp_parse_args( $value );
        $f_values = \array_filter( \array_map( '\is_string', \array_values( $value ) ) );
        return ( ! empty( $f_values ) ) ? $value : null;
    }


}