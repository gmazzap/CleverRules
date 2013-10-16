<?php
namespace CleverRules;

interface WPMergerInterface {


    function __construct( \WP $wp );


    function wp_merge();


    function get_vars();


}


