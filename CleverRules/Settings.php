<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * Settings Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Settings implements CRI\Settings {


    public $settings = array();


    public function get_all() {
        return $this->settings;
    }


    public function set_all( $settings = array() ) {
        $this->settings = $settings;
    }


    public function get( $which = -1 ) {
        if ( isset( $this->settings[$which] ) ) return $this->settings[$which];
    }


    public function set( $which = -1, $value = null ) {
        $this->settings[$which] = $value;
    }


    public function merge( $settings = array() ) {
        $this->settings = array_merge( $this->settings, $settings );
    }


}