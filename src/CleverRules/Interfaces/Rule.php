<?php
namespace CleverRules\Interfaces;


/**
 * Rule Interface
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface Rule {


    /**
     * Constructor
     * 
     * @param CleverRules\Interfaces\RuleSanitizer $sanitizer object of class
     *        implementing RuleSanitizer interface
     * @param CleverRules\Interfaces\Settings $setter object of class implementing Setting interface
     * @return null
     * @access public
     */
    function __construct( RuleSanitizer $sanitizer, Settings $setter );


    /**
     * Register a rule
     * 
     * @param array $args argument to register the rule. should be passed via register_clever_rule
     * @return CleverRules\Interfaces\Rule current rule object
     * @access public
     */
    function register( $args );


    /**
     * Register a group
     * 
     * @param array $args argument to register the group. should be passed via register_clever_group
     * @return CleverRules\Interfaces\Rule current rule object
     * @access public
     */
    function register_group( $args );


    /**
     * Launch the setup for the rules using setter and sanitizer classes
     * 
     * @param array $args the arguments for rule registration
     * @return null
     * @access public
     */
    function setup( $args );


    /**
     * Sanitize the rule arguments of rule
     * 
     * @return null
     * @access public
     */
    function sanitize();


    /**
     * Merge the arguments of rule group if present
     * 
     * @return null
     * @access public
     */
    function merge_group();


    /**
     * Create a paginated version of the rule if required
     * 
     * @return null
     * @access public
     */
    function paginate();


    /**
     * Save the rule in statci rules array in CleverRules\Interfaces\Rules
     * 
     * @return null
     * @access public
     */
    function save();


    /**
     * Retrieve the encrypted id for a rule
     * 
     * @param array|string $args Rule array arguments or id
     * @return string the rule id
     * @access public
     */
    static function get_rule_name( $args = array() );


    /**
     * Retrieve the url for a rule
     * 
     * @param string $id encrypted rule id
     * @param array $args array of replacements for the rule
     * @return string the rule url
     * @access public
     */
    static function get_rule_link( $id = '', $args = array() );


}