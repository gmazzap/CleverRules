<?php
namespace CleverRules\Sanitizers;


class Bool implements \CleverRules\TypeSanitizerInterface {


    public function sanitize( $value ) {
        return (bool) $value;
    }


}