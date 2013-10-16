<?php
namespace CleverRules\Interfaces;


/**
 * Matcher Interface
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface Matcher {


    /**
     * Contructor.
     * 
     * @param CleverRules\Interfaces\Url $url A class implementing Url Interface
     * @return null
     * @access public
     */
    function __construct( Url $url );


    /**
     * Takes an array of rules and return the one that match the url
     * 
     * @param array $rules The rules to check
     * @return null|object $rule the rule that match the url, null if no rule match
     */
    function match( $rules = array() );


}


