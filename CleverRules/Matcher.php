<?php
namespace CleverRules;


class Matcher implements MatcherInterface {


    protected $url;


    var $match;


    var $replacements;


    public function __construct( UrlInterface $url ) {
        $this->url = $url;
    }


    public function match( $rules = array() ) {
        $good = array();
        while ( ! empty( $rules ) && empty( $this->match ) ) {
            $this->replacements = array();
            $rule = \array_shift( $rules );
            $match = $this->match_rule( $rule );
            if ( $match ) $good[] = $rule;
        }
        if ( ! empty( $good ) && empty( $this->match ) ) $this->match = \array_shift( $good );
    }


    protected function match_rule( $rule ) {
        if ( \apply_filters( 'skip_clever_rule', false, (array)$rule, $this->url->parts ) )
            return false;
        $count = $this->check_rule( $rule );
        if ( $count === \count( $this->url->parts ) ) {
            if ( empty( $this->replacements ) ) $this->match = $rule;
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
                $this->replacements[] = $replaced;
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


}