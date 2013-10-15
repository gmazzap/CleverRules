<?php
namespace CleverRules\Sanitizers;


class String implements \CleverRules\TypeSanitizerInterface {


    public function sanitize( $value ) {
        return \preg_match( '/^[a-z0-9_\.]+$/', $value ) === 1 ? $value : null;
    }


}