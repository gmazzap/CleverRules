<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * RuleSanitizer Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class RuleSanitizer implements CRI\RuleSanitizer {


    protected static $sanitizers = array(
        'after' => '\CleverRules\Sanitizers\Callback',
        'before' => '\CleverRules\Sanitizers\Callback',
        'group' => '\CleverRules\Sanitizers\String',
        'id' => '\CleverRules\Sanitizers\String',
        'paginated' => '\CleverRules\Sanitizers\Paginated',
        'priority' => '\CleverRules\Sanitizers\Int',
        'qs_merge' => '\CleverRules\Sanitizers\Bool',
        'query' => '\CleverRules\Sanitizers\KeyedArray',
        'route' => '\CleverRules\Sanitizers\Route',
        'template' => '\CleverRules\Sanitizers\PhpFile',
        'vars' => '\CleverRules\Sanitizers\SimpleArray'
    );


    public $valid;


    public $cbs;


    public $is_group;


    public $raw;


    public $sanitized;


    public function __construct( $is_group ) {
        $this->valid = array_keys( self::$sanitizers );
        $this->cbs = array();
        $this->is_group = $is_group;
    }


    public function setup( $args = array() ) {
        if ( $this->is_group && ( ! isset( $args['id'] ) || empty( $args['id'] ) ) ) return;        
        if ( ! $this->is_group && ( ! isset( $args['route'] ) || empty( $args['route'] ) ) ) return;
        $this->raw = $args;
        $loader = Loader::get_instance();
        $loader->load_dir( CLEVER_RULES_PATH . 'CleverRules/Sanitizers' );
        $this->add_valid();
    }


    public function sanitize( $args = array() ) {
        if ( empty( $args ) && empty( $this->raw ) ) return;
        if ( empty( $args ) ) $args = $this->raw;
        foreach ( $args as $key => $value ) {
            if ( in_array( $key, array_keys( self::$sanitizers ) ) ) {
                $val = $this->sanitize_type( self::$sanitizers[$key], $value );
            } elseif ( in_array( $key, $this->valid ) && isset( $this->cbs[$key] ) ) {
                $val = call_user_func( $this->cbs[$key] );
            }
            if ( ! is_null( $val ) ) $this->sanitized[$key] = $val;
        }
    }


    protected function sanitize_type( $type, $value ) {
        $sanitizer = new $type;
        return $sanitizer->sanitize( $value );
    }


    protected function add_valid() {
        $add_valid = (array) \apply_filters( 'clever_rules_valid_args', array() );
        if ( empty( $add_valid ) ) return;
        foreach ( $add_valid as $key ) {
            $this->set_cb( $key, $add_valid );
        }
    }


    protected function set_cb( $key = '', $args = array() ) {
        if ( \substr( $key, 0, 9 ) == 'sanitize_' ) return;
        $setted = \array_key_exists( 'sanitize_' . $key, $args );
        $cb = $setted ? $args['sanitize_' . $key] : false;
        if ( \is_callable( $cb ) ) {
            $this->cbs[$key] = $cb;
            $this->valid[] = $key;
        }
    }


    public function check_rule( $rule ) {
        if ( ! $rule->route || ! $rule->query ) return false;
        return true;
    }


    public function check_group( $group ) {
        $this->remove_group_args( $group );
        return $group->id;
    }


    protected function remove_group_args( $group ) {
        $preserve = array('route', 'priority', 'query');
        foreach ( $preserve as $key ) {
            if ( isset( $group->args[$key] ) ) unset( $group->args[$key] );
        }
    }


}