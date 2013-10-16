<?php
namespace CleverRules\Interfaces;


/**
 * WPMerger Interface
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface WPMerger {


    /**
     * Constructor
     * 
     * @param WP $wp instance of the core WP class, saved in the global $wp variable
     * @return null
     * @access public
     */
    function __construct( \WP $wp );


    /**
     * Merge all public and private query vars and save them in an array
     * 
     * @return null
     * @access public
     */
    function wp_merge();


    /**
     * Return the array containing all query variables keys
     * 
     * @return array all the allowed query variables keys
     * @access public
     */
    function get_vars();


}


