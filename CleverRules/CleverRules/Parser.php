<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * Parser Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Parser implements CRI\Parser {


    protected $settings;


    protected $url;


    protected $rule;


    protected $replacements;


    protected $vars;


    public $qv;


    public static $template;


    public function __construct( CRI\Settings $settings, CRI\Url $url ) {
        $this->settings = $settings;
        $this->url = $url;
    }


    public function parse( $rule, $replacements ) {
        if ( ! $rule->sanitizer->check_rule( $rule ) ) return;
        if ( ! empty( $rule->args['group'] ) ) $rule->merge_group();
        $this->rule = \apply_filters( 'clever_rule_matched_args', $rule->args, $this->url->parts );
        $this->replacements = $replacements;
        if ( $this->pre_hooks() !== false ) $this->do_parse();
    }


    public function do_parse() {
        $this->qv = array();
        $this->vars();
        $this->merge_qs();
        $this->qv();
        if ( ! empty( $this->qv ) ) {
            \do_action( 'clever_rules_query_vars', $this->qv );
            $this->utils();
        }
    }


    protected function pre_hooks() {
        if ( \apply_filters( 'stop_clever_rule_rule', false, $this->rule ) ) return false;
        \do_action( 'pre_clever_rule', $this->rule, $this->url->parts );
    }


    protected function vars() {
        $vars = $this->settings->get( 'vars' );
        if ( ! empty( $this->rule['vars'] ) ) $vars = \array_merge( $this->rule['vars'], $vars );
        $this->vars = $vars;
    }


    protected function merge_qs() {
        $allow = \apply_filters( 'clever_rules_allow_merge_qs', $this->rule['qs_merge'] );
        if ( $allow && ! empty( $this->url->qs ) ) {
            $filtered = \apply_filters( 'clever_rules_merge_qs', $this->url->qs, $this->rule );
            $this->rule['query'] = \wp_parse_args( (array) $filtered, $this->rule['query'] );
        }
    }


    protected function qv() {
        foreach ( $this->rule['query'] as $key => $value ) {
            if ( ! $this->check_var( $key, $value ) ) continue;
            $is_variable = (bool) substr_count( $value, '[' );
            if ( $is_variable && ! empty( $this->replacements ) ) {
                $this->qv[$key] = $this->qv_key( $value );
            } elseif ( ! $is_variable ) {
                $this->qv[$key] = $value;
            }
        }
    }


    protected function check_var( $key, $value ) {
        $good = false;
        if ( ! empty( $value ) && in_array( $key, $this->vars ) ) {
            $r = preg_replace( '/\[[0-9]\]/', '', $value );
            $good = (bool) preg_match( '/^[a-z0-9\-_]*$/i', $r );
        }
        return $good;
    }


    protected function qv_key( $value ) {
        $f = \preg_replace_callback( '/\[([0-9]+)\]/',
            function( $m ) { return '%' . ( \intval( $m[1] ) + 1 ) . '$s'; }
            , $value );
        return vsprintf( $f, $this->replacements );
    }


    protected function utils() {
        $this->template();
        $this->before();
        $this->after();
    }


    protected function template() {
        if ( isset( $this->rule['template'] ) ) {
            self::$template = $this->rule['template'];
            add_filter( 'template_include', array(__CLASS__, 'set_template'), 99999 );
        }
    }


    protected function before() {
        if ( isset( $this->rule['before'] ) && is_callable( $this->rule['before'] ) )
                call_user_func( $this->rule['before'], $this->rule );
    }


    protected function after() {
        if ( isset( $this->rule['after'] ) && is_callable( $this->rule['after'] ) ) {
            $action = apply_filters( 'clever_rule_after', 'template_redirect', $this->rule );
            add_action( $action, $this->rule['after'] );
        }
    }


    public static function set_template( $template ) {
        if ( empty( self::$template ) ) return $template;
        $replace = \locate_template( self::$template, false, false );
        return $replace ? $replace : $template;
    }


}