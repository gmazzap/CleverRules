<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * Loader Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Loader implements CRI\Loader {


    protected static $instance = null;


    protected static $loaded = array();


    public $main_dir;


    public static function get_instance( $main_dir = '' ) {
        if ( \is_null( self::$instance ) ) {
            $class = __CLASS__;
            self::$instance = new $class( $main_dir );
        }
        return self::$instance;
    }


    public static function set_loaded( $filepath ) {
        $file = basename( $filepath );
        if ( ! self::is_loaded( $file ) ) self::$loaded[] = $file;
    }


    public static function get_loaded() {
        return self::$loaded;
    }


    public static function is_loaded( $file ) {
        return isset( self::$loaded[$file] );
    }


    public static function load( $path = '', $once = true ) {
        self::set_loaded( $path );
        if ( $once ) {
            require_once( $path );
        } else {
            require( $path );
        }
    }


    public function __construct( $main_dir = '' ) {
        if ( empty( $main_dir ) ) $main_dir = plugin_dir_path( __FILE__ );
        $this->main_dir = $main_dir;
    }


    public function set_dir( $dir = '' ) {
        if ( empty( $dir ) ) $dir = plugin_dir_path( __FILE__ );
        $this->main_dir = $dir;
    }


    public function load_dir( $dir = '', $once = true ) {
        if ( empty( $dir ) ) $dir = $this->main_dir;
        $iterator = new \DirectoryIterator( \untrailingslashit( $dir ) );
        foreach ( $iterator as $fileinfo ) {
            if ( ! $fileinfo->isFile() ) continue;
            $path = trailingslashit( $dir ) . $fileinfo->getBasename();
            self::load( $path, $once );
        }
    }


    public function load_file( $which = '', $once = true ) {
        $path = ( \substr_count( $which, $this->main_dir ) ) ? $which : $this->main_dir . $which;
        self::load( $path, $once );
    }


    public function load_class( $which = '', $once = true ) {
        $path = $this->main_dir . $which . '.php';
        $this->load_file( $path, $once );
    }


}