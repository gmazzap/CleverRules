<?php
namespace CleverRules\Sanitizers;

use CleverRules\Interfaces as CRI;

/**
 * KeyedArray Class
 * Used to sanitize array arguments with all string keys and values
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class KeyedArray implements CRI\TypeSanitize {


    public function sanitize( $value ) {
        $val = (array) \wp_parse_args( $value );
        $f_values = \array_filter( \array_map( '\is_string', \array_values( $val ) ) );
        $f_keys = \array_filter( \array_map( '\is_string', \array_keys( $val ) ) );
        return ( ! empty( $f_values ) && ! empty( $f_keys ) ) ? $val : null;
    }


}