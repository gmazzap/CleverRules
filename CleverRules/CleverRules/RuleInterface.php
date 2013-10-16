<?php
namespace CleverRules;

interface RuleInterface {


    function __construct( RuleSanitizerInterface $sanitizer, SettingsInterface $setter );


    function register( $args );


    function register_group( $args );


    function setup( $args );


    function sanitize();


    function merge_group();


    function paginate();


    static function get_rule_name( $args = array() );


    static function get_rule_link( $id = '', $args = array() );


}