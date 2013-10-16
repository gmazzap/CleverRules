<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * Matcher Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class Matcher implements CRI\Matcher {


    protected $url;


    public $match;


    public $replacements;


    protected $rep;


    protected $current_rule;


    public function __construct( CRI\Url $url ) {
        $this->url = $url;
    }


    public function match( $rules = array() ) {
        $good = array();
        $this->rep = array();
        while ( ! empty( $rules ) && empty( $this->match ) ) {
            $rule = \array_shift( $rules );
            $this->current_rule = Rule::get_rule_name( $rule->args );
            $match = $this->match_rule( $rule );
            if ( $match ) $good[$this->current_rule] = $rule;
        }
        $this->set_matched( $good );
    }


    protected function match_rule( $rule ) {
        if ( \apply_filters( 'skip_clever_rule', false, (array) $rule, $this->url->parts ) )
                return false;
        $count = $this->check_rule( $rule );
        if ( $count === \count( $this->url->parts ) ) {
            if ( empty( $this->rep[$this->current_rule] ) ) $this->match = $rule;
            return true;
        }
    }


    protected function check_rule( $rule ) {
        $count = 0;
        $stop = false;
        $i = 0;
        $url_parts = $this->url->parts;
        $route = \explode( '/', $rule->route );
        while ( ! empty( $url_parts ) && ( $stop === false ) ) {
            $url_part = \array_shift( $url_parts );
            $stop = $this->check_rule_part( $route[$i], $url_part ) !== true;
            $count += $stop ? 0 : 1;
            $i ++;
        }
        return $count;
    }


    protected function check_rule_part( $route_part, $url_part ) {
        if ( $route_part === $url_part ) {
            return true;
        } elseif ( \substr_count( $route_part, '%' ) == 1 ) {
            $replaced = $this->check_rule_part_dyn( $route_part, $url_part );
            if ( ! empty( $replaced ) ) {
                $this->rep[$this->current_rule][] = $replaced;
                return true;
            }
        }
    }


    protected function check_rule_part_dyn( $route_part, $url_part ) {
        $type = \substr_count( $route_part, '%d' ) == 1 && \is_numeric( $url_part ) ? '%d' : '%s';
        $rep = ( $type == '%d' ) ? '[0-9]' : '[a-z0-9\-_]';
        $pattern = \str_replace( $type, '(' . $rep . '+)', $route_part );
        if ( \substr_count( $pattern, '(' . $rep . '+){' ) == 1 )
                $pattern = \str_replace( '(' . $rep . '+){', '(' . $rep . '){', $pattern );
        $matches = array();
        if ( \preg_match( '/^' . $pattern . '$/', $url_part, $matches ) == 1 )
                return \sprintf( $type, $matches[0] );
    }


    protected function set_matched( $good = array() ) {
        if ( ! empty( $good ) && empty( $this->match ) ) {
            $keys = \array_keys( $good );
            $first = \array_shift( $keys );
            $this->replacements = $this->rep[$first];
            $this->match = \array_shift( $good );
        } else {
            $this->replacements = array();
        }
    }


}
