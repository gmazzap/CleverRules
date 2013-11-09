<?php
namespace CleverRules\Interfaces;


/**
 * Settings Interface
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface Settings {


    /**
     * Return the settings array
     * 
     * @return array settings array
     * @access public
     */
    function get_all();


    /**
     * Set the given array as the whole settings array
     * 
     * @return null
     * @access public
     */
    function set_all( $settings = array() );


    /**
     * Get a specific setting value
     * 
     * @param string $which the setting to retrieve
     * @return mixed the saved value for wanted setting
     * @access public
     */
    function get( $which );


    /**
     * Set a specific value in the settings array
     * 
     * @param string $which the name of the setting to save
     * @param mixed $value the value to set
     * @return null
     * @access public
     */
    function set( $which, $value );


    /**
     * Merge the array passed as argument in the current settings array.
     * Overwrites settings with same name
     * 
     * @param array $settings the setting to merge
     * @return null
     * @access public
     */
    function merge( $settings = array() );


}