<?php
namespace CleverRules\Sanitizers;


class Callable implements \CleverRules\TypeSanitizerInterface {


    public function sanitize( $value ) {
        return ( \is_callable( $value ) ) ? $value : null;
    }


}