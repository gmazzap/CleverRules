<?php
namespace CleverRules;


class RuleSettings implements SettingsInterface {


    protected $args;


    protected $is_group;


    protected $defaults;


    public function __construct( $is_group = false ) {
        $this->is_group = $is_group;
        $this->defaults();
    }


    public function set_all( $args = array() ) {
        $args = array_filter( $args );
        $merged = wp_parse_args( $args, $this->defaults );
        $filtered = apply_filters( 'clever_rule_args', $merged );
        $this->args = is_array($filtered) && ! empty($filtered) ? $filtered : $merged;
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


    public function merge_group( $args = array() ) {
        \do_action_ref_array( 'pre_clever_rules_merge_group', $this->args );
        $this->merge( $args );
    }


    public function defaults() {
        $rule_def = array('route' => null, 'query' => array(), 'priority' => 0);
        $def = array(
            'id' => '', 'vars' => array(), 'paginated' => false,
            'before' => null, 'after' => null, 'qs_merge' => false
        );
        $this->defaults = ( ! $this->is_group ) ? \array_merge( $def, $rule_def ) : $def;
    }


}


