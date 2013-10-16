<?php
namespace CleverRules;

interface LoaderInterface {


    static function get_instance();


    static function set_loaded( $filepath );


    static function get_loaded();


    static function is_loaded( $file );


    static function load( $path = '', $once = true );


    function __construct( $main_dir = '' );


    function set_dir( $dir = '' );


    function load_dir( $dir = '', $once = true );


    function load_file( $which = '', $once = true );


    function load_class( $which = '', $once = true );


}


