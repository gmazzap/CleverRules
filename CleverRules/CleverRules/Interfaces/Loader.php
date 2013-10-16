<?php
namespace CleverRules\Interfaces;


/**
 * Loader Interface
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
interface Loader {


    /**
     * Get an instance of Loader as Singleton
     * 
     * @param string $main_dir Optionally set a main directory for the loader
     * @return object instance of loader
     * @access public
     */
    static function get_instance( $main_dir = '' );


    /**
     * Save a filename in an array after loading
     * 
     * @param string $filepath The path of the file
     * @return null
     * @access public
     */
    static function set_loaded( $filepath );


    /**
     * Return the array of file loaded
     * 
     * @return array The arry of file names
     * @access public
     */
    static function get_loaded();


    /**
     * Check if a file was loaded lookin into the array of loaded files
     * 
     * @param string $file the file name to check
     * @return bool true if the file was loaded, false otherwise
     * @access publi
     */
    static function is_loaded( $file );


    /**
     * Load a file
     * 
     * @param string $path the path of the file to load
     * @param bool $once if true load the file using require_once, require is used otherwise
     * @return null
     * @access public
     */
    static function load( $path = '', $once = true );


    /**
     * Constructor. Should be not used, use get_instance instead
     * 
     * @param string $main_dir Optionally set a main dir for loader instance
     * @return null
     * @access public
     */
    function __construct( $main_dir = '' );


    /**
     * Set the main dir for the loader instance. Should be used before calling
     * load_class or load_file to set in which folder load the file or the class
     * 
     * @param string $dir the directory path
     * @return null
     * @access public
     */
    function set_dir( $dir = '' );


    /**
     * Load all the files in a directory
     * 
     * @param string $dir the directory path
     * @param bool $once if true load the files using require_once, require is used otherwise
     * @return null
     * @access public
     */
    function load_dir( $dir = '', $once = true );


    /**
     * Load a specific file. A directory for the instance must be setted before.
     * Is possible set a directory via get_instance or via set_dir
     * 
     * @param string $which the file name to load
     * @param bool $once if true load the files using require_once, require is used otherwise
     * @return null
     * @access public
     */
    function load_file( $which = '', $once = true );


    /**
     * Load a class. Filename for the class is setted appending '.php' to class name.
     * A directory for the instance must be setted before.
     * Is possible set a directory via get_instance or via set_dir
     * 
     * @param string $which The class name
     * @param bool $once if true load the files using require_once, require is used otherwise
     * @return null
     * @access public
     */
    function load_class( $which = '', $once = true );


}