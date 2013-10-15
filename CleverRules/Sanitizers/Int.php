<?php
namespace CleverRules\Sanitizers;


class Int implements \CleverRules\TypeSanitizerInterface {


    public function sanitize( $value ) {
        return ( \is_int( $value ) ) ? $value : null;
    }


}