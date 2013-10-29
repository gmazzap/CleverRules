<?php
namespace CleverRules\Interfaces;


/**
 * RulesFront Interface
 * Class implementing this interface must extends WP core class and override parse_request method.
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface RulesFront {


    /**
     * Constructor
     * 
     * @param CleverRules\Interfaces\Rules $rules object implementing Rules interface
     * @return null
     * @access public
     */
    function __construct( Rules $rules );


    /**
     * Call the core parse_request method whn no clever rules are registered
     * 
     * @return null
     * @access public
     */
    function to_wp();


    /**
     * Parse the request. Method is called by WordPress on global $wp object.
     * This class extend core WP class and override this function.
     * 
     * @param array $extra_query_vars array of additional vars passed by WP to core method
     * @return null
     * @access public
     */
    function parse_request( $extra_query_vars = '' );


}