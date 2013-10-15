<?php
/**
 * Main API function used to register a new rule.
 * Must be called before setup_theme hook (on plugins_loaded).
 *
 * @package CleverRules
 * @param array $args   arguments to register the rule
 * @uses CleverRules\Rule::register
 * @return object CleverRules\Rule instance
 * @access public
 */
function register_clever_rule( $args = array() ) {
    if ( did_action( 'setup_theme' ) ) {
        $msg = 'register_clever_rule must be called before setup_theme hook is fired.'
            . 'plugins_loaded hook is a good place.';
        _doing_it_wrong( ' register_clever_rule', $msg, null );
    }
    if ( is_string( $args ) ) $args = array( 'route' => $args );
    $sanitizer = new CleverRules\RuleSanizer();
    $setter = new CleverRules\RuleSettings( false );
    $rule = new CleverRules\Rule( $sanitizer, $setter );
    return $rule->register( $args );
}


/**
 * API function used to register a rule group.
 * Must be called before setup_theme hook (on plugins_loaded).
 *
 * @package CleverRules
 * @param array $args   arguments to register the group
 * @uses CleverRules\Rule::register_group
 * @return object CleverRules\Rule instance
 * @access public
 */
function register_clever_group( $args = array() ) {
    if ( did_action( 'setup_theme' ) ) {
        $msg = 'register_clever_group must be called before setup_theme hook is fired.'
            . 'plugins_loaded hook is a good place.';
        _doing_it_wrong( ' register_clever_group', $msg, null );
    }
    if ( is_string( $args ) ) $args = array( 'id' => $args );
    $sanitizer = new CleverRules\RuleSanizer;
    $setter = new CleverRules\RuleSettings( true );
    $rule = new CleverRules\Rule( $sanitizer, $setter );
    return $rule->register_group( $args );
}


/**
 * API function to retrieve the hashed rule id from plain id or from route (if no id is given)
 *
 * @package CleverRules
 * @param string|array $id the rule id or an array containing rule args
 * @uses CleverRules\Rule::obj
 * @uses CleverRules\Rule::get_name
 * @return string|bool	the url or false on fail
 * @access public
 */
function get_the_clever_ruleid( $id = null, $rule = null ) {
    if ( $rule instanceof CleverRules\Rule ) return $rule->get_name();
    $rule_name = false;
    if ( is_string( $id ) ) {
        $key = substr_count( $id, '/' ) ? 'route' : 'id';
        $arg = array( $key => $id );
        $rule_name = CleverRules\Rule::get_rule_name( $arg );
    } elseif ( is_array( $id ) && ( isset( $id['route'] ) || isset( $id['id'] ) ) ) {
        $rule_name = CleverRules\Rule::get_rule_name( $id );
    }
    return $rule_name;
}


/**
 * API function to retrieve the url related to a root and some args. Use get_the_clever_ruleid
 *
 * @package CleverRules
 * @param string|array $id  the rule id or an array containing rule args
 * @param array $args   arguments to be used as replacement for route jolly chars
 * @uses CleverRules\Rule::obj
 * @uses CleverRules\Rule::get_link
 * @uses get_the_clever_ruleid
 * @return string|bool	the url or false on fail
 * @access public
 */
function get_the_cleverlink( $id = null, $args = array() ) {
    $name = get_the_clever_ruleid( $id );
    $link = $name ? CleverRules\Rule::get_rule_link($id, $args) : false;
    return $link
        ? apply_filters( 'get_the_cleverlink', $link, $id, $args )
        : false;
}


/**
 * API function to echo the url related to a root and some args.
 * Echo the result of get_the_cleverlink
 *
 * @package CleverRules
 * @param string|array $id the rule id or an array containing rule args
 * @param array $args   arguments to be used as replacement for route jolly chars
 * @uses get_the_cleverlink
 * @return echo the retrieved link
 * @access public
 */
function the_cleverlink( $id = null, $args = array() ) {
    echo apply_filters( 'the_cleverlink', get_the_cleverlink( $id, $args ), $id, $args );
}


