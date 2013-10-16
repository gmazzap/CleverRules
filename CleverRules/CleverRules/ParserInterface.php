<?php
namespace CleverRules;

interface ParserInterface {


    function __construct( SettingsInterface $settings, UrlInterface $url );


    function parse( $rule, $replacements );


}


