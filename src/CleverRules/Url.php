<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * Url Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Url implements CRI\Url {


    public $full;


    public $sane_array;


    public $sane;


    public $parts = array();


    public $qs;


    public function __construct() {
        $this->set_url();
        $this->set_vars();
        $this->set_parts();
    }


    public function set_url() {
        $home_path = \trim( \parse_url( \home_url(), \PHP_URL_PATH ), '/' );
        $this->full = \str_replace( $home_path, '', \add_query_arg( array() ) );
        $this->sane_array = \explode( '?', $this->full );
    }


    public function set_vars() {
        $qs = array();
        if ( isset( $this->sane_array[1] ) ) \parse_str( $this->sane_array[1], $qs );
        $this->sane = trim( $this->sane_array[0], '/\\' );
        $this->qs = $qs;
    }


    public function set_parts() {
        $this->parts = \array_filter( \explode( '/', $this->sane ) );
    }


}


