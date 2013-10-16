<?php
namespace CleverRules\Sanitizers;

use CleverRules\Interfaces as CRI;

/**
 * PhpFile Class
 * Used to sanitize php files arguments
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class PhpFile implements CRI\TypeSanitize {


    public function sanitize( $value ) {
        $pi = pathinfo( $value );
        if ( empty( $pi ) || ! isset( $pi['filename'] ) ) return null;
        return isset( $pi['extension'] ) && ( $pi['extension'] == 'php' ) ? $value : null;
    }


}