<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * Rule Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Rule implements CRI\Rule {


    public $sanitizer;


    public $setter;


    public $args;


    public $is_group;


    public $is_home;


    protected $chained_called = array();


    function __construct( CRI\RuleSanitizer $sanitizer, CRI\Settings $setter ) {
        $this->sanitizer = $sanitizer;
        $this->setter = $setter;
    }


    function __get( $name ) {
        return isset( $this->args[$name] ) ? $this->args[$name] : null;
    }


    function __call( $name, $args ) {
        if ( in_array( $name, $this->sanitizer->valid ) && ! empty( $args ) )
                return $this->chained( $name, $args[0] );
    }


    public function register( $args ) {
        $this->setup( $args );
        if ( $this->args['route'] === '/' ) $this->is_home = true;
        if ( ! empty( $this->args ) ) $this->save();
        return $this;
    }


    public function register_group( $args ) {
        $this->is_group = true;
        $this->setup( $args );
        $this->save();
        return $this;
    }


    public function setup( $args ) {
        if ( empty( $args ) || ! is_array( $args ) ) return;
        $this->setter->set_all( $args );
        $this->args = $this->setter->get_all();
        if ( ! empty( $this->args['group'] ) ) $this->merge_group();
        $this->sanitize();
        $this->args = $this->sanitizer->sanitized;
    }


    public function merge_group() {
        $group = isset( $this->group ) ? $this->group_exists( $this->group ) : '';
        if ( ! empty( $group ) ) {
            if ( ! $group->sanizer->check_group( $group ) ) return;
            $this->setter->merge_group( $group->args );
            $this->args = $this->setter->get_all();
        }
    }


    public function sanitize() {
        $this->sanitizer->setup( $this->args );
        $this->sanitizer->sanitize();
    }


    protected function save() {
        $id = $this->get_name();
        if ( $this->is_group ) {
            Rules::$groups[$id] = $this;
        } else {
            Rules::$rules[$id] = $this;
        }
    }


    protected function group_exists( $group ) {
        $id = md5( $group );
        if ( array_key_exists( $id, Rules::$groups ) ) return Rules::$groups[$id];
    }


    protected function get_name() {
        $key = $this->id ? 'id' : 'route';
        $value = $this->id ? $this->id : $this->route;
        return self::get_rule_name( array($key => $value) );
    }


    protected function chained( $name, $value ) {
        if ( $name == 'id' || $name == 'route' ) return $this;
        if ( in_array( $name, $this->chained_called ) || empty( $this->args ) ) return $this;
        $this->chained_called[] = $name;
        $this->sanitizer->sanitize( array($name => $value) );
        $sanitized = $this->sanitizer->sanitized[$name];
        if ( ! empty( $sanitized ) ) $this->setup_chained( $name, $sanitized );
        return $this;
    }


    protected function setup_chained( $name, $sanitized ) {
        $id = $this->get_name();
        if ( ! $this->is_group ) {
            Rules::$rules[$id]->args[$name] = $sanitized;
        } else {
            Rules::$groups[$id]->args[$name] = $sanitized;
        }
    }


    public function paginate() {
        if ( $this->paginated === true || $this->paginated === 'single' ) {
            $var = $this->paginated === 'single' ? 'page' : 'paged';
            $paged = '[' . substr_count( $this->route, '%' ) . ']';
            $args = array(
                'route' => \trailingslashit( $this->route ) . 'page/%d/',
                'query' => \wp_parse_args( array($var => $paged), $this->query ),
                'paginated' => false
            );
            if ( ! $this->id ) $args['id'] = $this->id . '.page';
            $newrule = clone $this;
            $newrule->args( \wp_parse_args( $args, $this->args ) );
            $newrule->save();
        }
    }


    static function get_rule_name( $args = array() ) {
        if ( isset( $args['id'] ) && ! empty( $args['id'] ) ) {
            return md5( $args['id'] );
        } elseif ( isset( $args['route'] ) && ! empty( $args['route'] ) ) {
            return md5( $args['route'] );
        }
    }


    static function get_rule_link( $id = '', $args = array() ) {
        if ( array_key_exists( $id, Rules::$rules ) ) {
            $rule = Rules::$rules[$id];
            $part = trim( vsprintf( $rule['route'], $args ), '/\\ ' );
            return trailingslashit( trailingslashit( home_url() ) . $part );
        }
    }


}