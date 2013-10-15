<?php
namespace CleverRules\Sanitizers;


class Paginated implements \CleverRules\TypeSanitizerInterface {


    public function sanitize( $value ) {
        if ( $value === 'single' ) return $value;
        return (bool) $value;
    }


}