<?php
namespace CleverRules;

interface SettingsInterface {
    
    function get_all();
    
    function set_all( $settings = array() );
    
    function get( $which );
    
    function set( $which, $value );
    
    function merge( $settings = array() );
    
}