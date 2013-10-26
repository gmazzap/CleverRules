<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * RuleSettings Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class RuleSettings implements CRI\Settings {


    protected $args;


    protected $is_group;


    protected $defaults;


    public function __construct( $is_group = false ) {
        $this->is_group = $is_group;
        $this->defaults();
    }


    public function set_all( $args = array() ) {
        $merged = \wp_parse_args( \array_filter($args), $this->defaults );
        $this->args = $this->preserve_group_args( $merged );
    }


    public function get_all() {
        return $this->args;
    }


    public function get( $which = 'route' ) {
        if ( isset( $this->args[$which] ) ) return $this->args[$which];
    }


    public function set( $which = 'route', $value = '/' ) {
        $this->args[$which] = $value;
    }


    public function merge( $args = array() ) {
        $this->args = \wp_parse_args( $this->args, $args );
    }
    

    public function defaults() {
        $rule_def = array('id' => '', 'route' => null, 'query' => array(), 'priority' => 0);
        $def = array(
            'vars' => array(), 'paginated' => false,
            'before' => null, 'after' => null, 'qs_merge' => false
        );
        $merged = ! $this->is_group ? \wp_parse_args( $rule_def, $def ) : $def;
        $filtered = \apply_filters( 'clever_rule_default_args', $merged );
        $this->defaults = $filtered;
    }


    protected function preserve_group_args( $args = array() ) {
        if ( ! $this->is_group ) return $args;
        $preserve = array('route', 'priority', 'query');
        foreach ( $preserve as $key ) {
            if ( isset( $args[$key] ) ) unset( $args[$key] );
        }
        return $args;
    }


}
