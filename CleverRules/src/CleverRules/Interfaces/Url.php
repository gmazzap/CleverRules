<?php
namespace CleverRules\Interfaces;


/**
 * Url Interface
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface Url {


    /**
     * Contructor
     * 
     * @return null
     * @access public
     */
    function __construct();


    /**
     * Save the current full url in a variable
     * @return null
     * @access public
     */
    function set_url();


    /**
     * Save the current query string (if present) as array
     * 
     * @return null
     * @access public
     */
    function set_vars();


    /**
     * Set the parts (every part is delimited by '/') of the url in an array
     * 
     * @return null
     * @access public
     */
    function set_parts();


}