<?php
namespace CleverRules\Interfaces;


/**
 * RuleSanitizer Interface
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface RuleSanitizer {


    /**
     * Constructor
     * 
     * @param bool $is_group If true inizialize the sanitizer for groups
     * @return null
     * @access public
     */
    function __construct( $is_group );


    /**
     * Inizialize the sanitizer for given rule/group args
     * 
     * @param array $args the arguments array
     * @return null
     * @access public
     */
    function setup( $args );


    /**
     * Sanitize the arguments
     * 
     * @return null
     * @access public
     */
    function sanitize();


    /**
     * Check a rule object for required arguments
     * 
     * @param CleverRules\Interfaces\Rule $rule a rule object
     * @return bool true if rule pass checking, false otherwise
     */
    function check_rule( $rule );


    /**
     * Check a group object for required arguments
     * 
     * @param CleverRules\Interfaces\Rule $group a group object
     * @return bool true if group pass checking, false otherwise
     */
    function check_group( $group );


}