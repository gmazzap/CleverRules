<?php
namespace CleverRules\Sanitizers;

use CleverRules\Interfaces as CRI;

/**
 * Callback Class
 * Used to sanitize callbacks arguments
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Callback implements CRI\TypeSanitizer {


    public function sanitize( $value ) {
        return ( \is_callable( $value ) ) ? $value : null;
    }


}