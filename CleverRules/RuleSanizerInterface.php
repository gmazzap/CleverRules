<?php
namespace CleverRules;

interface RuleSanitizerInterface {
    
    function __construct();
    
    function setup( $args );
    
    function sanitize();
    
    function check_rule( $rule );
    
    function check_group( $group );

}