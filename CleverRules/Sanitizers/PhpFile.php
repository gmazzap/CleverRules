<?php
namespace CleverRules\Sanitizers;


class PhpFile implements \CleverRules\TypeSanitizerInterface {


    public function sanitize( $value ) {
        $pi = pathinfo( $value );
        if ( empty( $pi ) || ! isset( $pi['filename'] ) )return null;
        return isset( $pi['extension'] ) && ( $pi['extension'] == 'php' ) ? $value : null;
    }


}