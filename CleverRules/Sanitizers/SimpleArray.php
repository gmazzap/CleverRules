<?php
namespace CleverRules\Sanitizers;


class SimpleArray implements \CleverRules\TypeSanitizerInterface {


    public function sanitize( $value ) {
        $value = (array) \wp_parse_args( $value );
        $f_values = \array_filter( \array_map( '\is_string', \array_values( $value ) ) );
        return ( ! empty( $f_values ) ) ? $value : null;
    }


}