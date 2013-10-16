<?php
namespace CleverRules\Interfaces;


/**
 * Parser Interface
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface Parser {


    /**
     * Contructor
     * 
     * @param CleverRules\Interfaces\Settings $settings object of class implementing Settings interface
     * @param CleverRules\Interfaces\Url $url object of class implementing Url interface
     * @return null
     * @access public
     */
    function __construct( Settings $settings, Url $url );


    /**
     * Take a rule and an array of replacements and set the appropriate query vars
     * 
     * @param object $rule Rule object
     * @param array $replacements The array of replacements
     * @return null
     * @access public
     */
    function parse( $rule, $replacements );


}


