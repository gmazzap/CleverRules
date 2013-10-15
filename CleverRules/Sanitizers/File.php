<?php
namespace CleverRules\Sanitizers;


class File implements \CleverRules\TypeSanitizerInterface {


    public function sanitize( $value ) {
        $pi = pathinfo( $value );
        return ( ! empty( $pi ) && isset( $pi['filename'] ) ) ? $value : null;
    }


}