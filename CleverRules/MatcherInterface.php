<?php
namespace CleverRules;

interface MatcherInterface {
    
    function __construct( UrlInterface $url );
    
    function match( $rules = array() );
    
}
