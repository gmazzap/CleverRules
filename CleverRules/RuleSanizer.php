<?php
namespace CleverRules;


class RuleSanitizer implements RuleSanitizerInterface {


    protected static $sanitizers = array(
        'after' => '\CleverRules\Sanitizers\Callable',
        'before' => '\CleverRules\Sanitizers\Callable',
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


    public $raw;


    public $sanitized;


    public function __construct() {
        $this->valid = array_keys( self::$sanitizers );
        $this->cbs = array();
    }


    public function setup( $args = array() ) {
        $this->raw = $args;
        $loader = Loader::get_instance();
        $loader->load_dir( CLEVER_RULES_PATH . 'CleverRules/Sanitizers' );
        $this->add_valid();
    }


    public function sanitize( $args = array() ) {
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
        return $group->id;
    }


}