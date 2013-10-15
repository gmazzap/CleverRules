<?php
namespace CleverRules;

interface RulesFrontInterface {


    function __construct( RulesInterface $rules );


    function to_wp();


    function parse_request( $extra_query_vars = '' );


}


