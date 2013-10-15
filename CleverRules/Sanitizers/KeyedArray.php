<?php
namespace CleverRules\Sanitizers;


class KeyedArray implements \CleverRules\TypeSanitizerInterface {


    public function sanitize( $value ) {
        $val = (array) \wp_parse_args( $value );
        $f_values = \array_filter( \array_map( '\is_string', \array_values( $val ) ) );
        $f_keys = \array_filter( \array_map( '\is_string', \array_keys( $val ) ) );
        return ( ! empty( $f_values ) && ! empty( $f_keys ) ) ? $val : null;
    }


}