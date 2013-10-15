<?php
namespace CleverRules;


class Settings implements SettingsInterface {


    protected $settings = array();


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