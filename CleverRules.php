<?php
/**
 * Plugin Name: Clever Rules
 *
 * Description: Just a "no surprises" and clever way to handle rewrite rules in WordPress.
 * Version: 0.1.5
 * Author: Giuseppe Mazzapica
 * Requires at least: 3.6
 * Tested up to: 3.6.1
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 *
 */

/**
 * CleverRuleSanitize Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class CleverRuleSanitize {


    /**
     * @var mixed $name The value to be sanitized
     * @access protected
     */
    protected $value;


    /**
     * @var string $type    The type of sanitization to apply
     * @access protected
     */
    protected $type = 'string';


    /**
     * Constructor. Set type and value
     *
     * @param string $type  the type of sanitization to apply
     * @param string $value the value to be sanitized
     * @return mixed    Sanitized value or false
     * @access protected
     */
    function __construct( $type = '', $value = '' ) {
        if ( ! empty( $type ) ) $this->type = $type;
        if ( ! is_null( $value ) ) $this->value = $value;
    }


    /**
     * Do sanization. Return the sanitized value or null if sanitization fails
     *
     * @access protected
     * @return mixed Sanitized value or false
     * @uses Sanitize::sanitize_type
     */
    function sanitize() {
        if ( is_null( $this->value ) ) return null;
        if ( is_callable( $this->type ) ) return call_user_func( $this->type, $this->value );
        if ( ! is_string( $this->type ) ) return null;
        return $this->sanitize_type();
    }


    /**
     * Handle sanitization based on type
     *
     * @return mixed Sanitized value or null
     * @access protected
     */
    protected function sanitize_type() {
        switch ( $this->type ) {
            case 'int' :
                return is_numeric( $this->value ) && intval( $this->value ) ? $this->value : null;
            case 'bool' :
                return (bool) $this->value;
            case 'safe_string' :
                return preg_match( '/^[a-z0-9_\.]+$/', $this->value ) === 1 ? $this->value : null;
            case 'callable' :
                return is_callable( $this->value ) ? $this->value : null;
			case 'array' :
                $this->value = (array) wp_parse_args( $this->value );
                $f_values = array_filter( array_map( 'is_string', array_values( $this->value ) ) );
                if ( empty( $f_values ) ) return null;
                return $this->value;
            case 'string_keyed_array' :
				$this->value = (array) wp_parse_args( $this->value );
                $f_keys = array_filter( array_map( 'is_string', array_keys( $this->value ) ) );
                $f_values = array_filter( array_map( 'is_string', array_values( $this->value ) ) );
                if ( empty( $f_keys ) || empty( $f_values ) ) return null;
                return $this->value;
            case 'route' :
                if ( ! is_string( $this->value ) || ! substr_count( $this->value, '/' ) )
					return null;
				return trim( $this->value );
			case 'file' :
			case 'php_file' :
				$pi = pathinfo( $this->value );
				if ( empty( $pi ) || ! isset( $pi['filename'] ) ) return null;
				if ( $this->type == 'file') return $this->value;
				$ext = str_replace( '_file', '', $this->type );
				return isset( $pi['extension'] ) && ( $pi['extension'] == $ext )
                    ? $this->value
                    : null;
            default :
                return null;
        }
    }


}


/**
 * CleverRule class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 *
 */
class CleverRule {


    /**
     * @staticvar array $_valid Accepted arguments for register_clever_rule.
     *                          Used for sanization and to handle chained methods
     * @access protected
     */
    protected static $_valid = array(
        'after',
		'before',
		'group',
		'id',
		'paginated',
		'priority',
		'qs_merge',
		'query',
		'route',
		'template',
		'vars'
    );


    /**
     * @staticvar array $_valid_sanitize_types    Map the valid arguments to a sanitization type
     * @access protected
     */
    protected static $_valid_sanitize_types = array(
		'after'		=> 'callable',
		'before'	=> 'callable',
		'group'		=> 'safe_string',
		'id'		=> 'safe_string',
		'paginated'	=> 'bool',
		'priority'	=> 'int',
		'qs_merge'	=> 'bool',
		'query'		=> 'string_keyed_array',
        'route'		=> 'route',
		'template'	=> 'php_file',
        'vars'		=> 'array'
    );


    /**
     * @var array $chained_called   The array containing chained methods called.
     *                              Used to avoid repetitions
     * @access protected
     */
    protected $chained_called = array();


    /**
     * @var array $args The final array of valid arguments for a rule object
     * @access public
     */
    protected $args;


    /**
     * @var array $valid    The final array of valid arguments names.
     *                      It's the $_valid static array (maybe) altered by a filter
     * @access protected
     */
    protected $valid;
	
	
	/**
     * @var bool $is_group	If the current instance is a broup or not
     * @access protected
     */
    protected $is_group = false;


    /**
     * Use method overloading to allow chaining function to set argument
     *
     * @param string $name the name of the requested method
     * @param array $args arguments passed to method
     * @return mixed $this->chained result
     * @access public
     */
    function __call( $name, $args ) {
        if ( in_array( $name, self::$_valid ) ) {
			$args = isset( $args[0] ) ? $args[0] : null;
            return $this->chained( $name, $args );
		}
    }


    /**
     * Constructor. Protected, should not be used to create a rule,
     * the register_clever_rule API function should be used instead.
     *
     * @param array $args The argumuments array passe to register_clever_rule
     * @uses CleverRule::add_valid to setup custom callback to sanitize custom arguments
	 * @uses CleverRule::only_valid Method used to remove not valid arguments
     * @uses CleverRule::setup_rule The method that handle the rule setup
     * @access protected
     */
    protected function __construct( $args = array(), $is_group = false ) {
        if ( ! is_array( $args ) || empty( $args ) ) return;
		$sanitize_cbs = $this->add_valid( $args );
		$this->is_group = (bool) $is_group;
        $args = $this->only_valid( $args );		
        $this->sanitize( $args, $sanitize_cbs );
		if ( ! $is_group ) {
        	$this->setup_rule();
		} else {
			$this->setup_group();
		}
    }
	
	
	/**
     * Used to setup custom callback to sanitize custom arguments
     *
	 * @param array $args   The argumuments array passed to register_clever_rule
     * @uses CleverRule::set_cb_sanitize    Used to set custom sanitize callback to custom argument
     * @uses CleverRule::setup_rule The method that handle the rule setup
	 * @return array    The array of custom callbacks
     * @access protected
     */
	protected function add_valid( $args ) {
		$sanitize_cbs = array();
		$this->valid = (array) apply_filters( 'clever_rules_valid_args', array() );
        if ( empty( $this->valid ) ) {
			$this->valid = self::$_valid;
			return $sanitize_cbs;
		}
		foreach ( $this->valid as $add_valid ) {
			$cb = $this->set_sanitize_cb( $add_valid, $args );
			if ( $cb ) $sanitize_cbs['sanitize_' . $add_valid] = $cb;
		}
		$this->valid = array_merge( $this->valid, self::$_valid );
		return $sanitize_cbs;
	}


    /**
     * Check an array of arguments returning only the ones registered as valid
     *
     * @param array $args arguments to check
     * @return array valid arguments
     * @access protected
     */
    protected function only_valid( $args = array() ) {
        $invalid_keys = array_diff( array_keys( $args ), $this->valid );
        if ( ! empty( $invalid_keys ) )
            foreach ( $invalid_keys as $invalid_key )
                unset( $args[$invalid_key] );
        return $args;
    }


    /**
     * Used by contructor to set custom sanitize callback for custom argument
     *
     * @param string $arg The custom argument name
     * @param array $args The array of all argument passsed to constructor
     * @return mixed Custom callable or false on wrong arguments
     * @access protected
     */
    protected function set_sanitize_cb( $arg = '', $args = array() ) {
        $is_sanitize = substr( $arg, 0, 9 ) == 'sanitize_';
        $saved_sanitize = array_key_exists( 'sanitize_' . $arg, $this->valid );
        $sanitize = array_key_exists( 'sanitize_' . $arg, $args );
        if ( $sanitize && ! is_callable( 'sanitize_' . $arg ) ) $sanitize = false;
        if ( ! $is_sanitize && ! $saved_sanitize && $sanitize ) return $args['sanitize_' . $arg];
        return false;
    }


    /**
     * Sanitize the arguments an launch the setup
     *
     * @uses Class Sanitize  Class that handle the sanitation
     * @return null
     * @access protected
     */
    protected function sanitize( $args = array(), $scbs = array() ) {
        if ( empty( $args ) || ! is_array( $args ) ) return;
        foreach ( $args as $key => $value ) {
            $type = false;
            if ( in_array( $key, self::$_valid ) ) {
                $type = self::$_valid_sanitize_types[$key];
            } elseif ( in_array( $key, $this->valid ) && isset( $scbs['sanitize_' . $key] ) ) {
                $type = $scbs['sanitize_' . $key];
                $this->valid[] = 'sanitize_' . $key;
            }
            if ( ! $type ) continue;
            $sanitize = new CleverRuleSanitize( $type, $value );
            $sanitized = $sanitize->sanitize();
            if ( ! is_null( $sanitized ) ) $this->args[$key] = $sanitized;
        }
    }


    /**
     * Write the rule argument on the CleverRules::$rules static array.
     * Called on contruct and on every chained method
     *
     * @return null
     * @access protected
     */
    protected function setup_rule() {
        if ( $this->args['route'] === '/' ) $this->args['route'] = '{{home}}';
        $route = isset( $this->args['route'] ) ? $this->get_name() : false;
        if ( $route ) CleverRules::$rules[$route] = $this->args;
    }
	
	
	/**
     * Write the group argument on the CleverRules::$rule_groups static array.
     * Called on contruct for groups
     *
     * @return null
     * @access protected
     */
    protected function setup_group() {
		if ( ! isset( $this->args['id'] ) ) return;
		if ( isset( $this->args['route'] ) ) unset( $this->args['route'] );
		$id = $this->get_name();
        if ( $id ) CleverRules::$rule_groups[$id] = $this->args;
    }


    /**
     * Is the function called by all the chained methods.
     * Check the argument name after the method itself, sanitize it and update the rule in
     * CleverRules::rules static array using setup_rule
     *
     * @param string $which the argument to setup
     * @param mixed $value the argument value
     * @uses CleverRule::setup_rule
     * @uses CleverRule::setup_group
     * @uses CleverRule::sanitize
     * @return object current CleverRule instance
     * @access protected
     */
    protected function chained( $which, $value ) {
		if ( $which == 'id' || $which == 'route' ) return $this;
        if ( in_array( $which, $this->chained_called ) || empty( $this->args ) ) return $this;
        $this->chained_called[] = $which;
        $this->sanitize( array($which => $value) );
		if ( ! $this->is_group ) $this->setup_rule(); else $this->setup_group();
        return $this;
    }


    /**
     * Static method used by register_clever_rule API function to setup args,
     * merging defaults and create a new instance
     *
     * @param array $args the rule arguments array
     * @return object new CleverRule instance
     * @access public
     */
    public static function register( $args = array() ) {
        if ( did_action( 'setup_theme' ) ) {
            $msg = __CLASS__ . '::register should not be called directly, but using '
                . 'register_clever_rule hooken in a function that run before setup_theme hook '
                . 'is fired. plugins_loaded hook is a good place.';
            _doing_it_wrong( __CLASS__ . '::register', $msg, null );
            return;
        }
        $defaults = array(
            'route' => null, 'id' => '', 'query' => array(), 'priority' => 0, 'before' => null,
            'after' => null, 'vars' => array(), 'paginated' => true, 'qs_merge' => false
        );
        $args = apply_filters( 'clever_rule_args', wp_parse_args( $args, $defaults ) );
        return ( ! empty( $args ) && is_array( $args ) ) ? new CleverRule( $args ) : self::obj();
    }
	
	
	/**
     * Static method used by register_clever_group API function to setup args of a rule group
     *
     * @param array $args the group arguments array
     * @return null
     * @access public
     */
    public static function register_group( $args = array() ) {
        if ( did_action( 'setup_theme' ) ) {
            $msg = __CLASS__ . '::group should not be called directly, but using '
                . 'register_clever_rule hooken in a function that run before setup_theme hook '
                . 'is fired. plugins_loaded hook is a good place.';
            _doing_it_wrong( __CLASS__ . '::group', $msg, null );
            return;
        }
        $args = (array) apply_filters( 'clever_rule_group', $args );
        return ( ! empty( $args ) && is_array( $args ) )
            ? new CleverRule( $args, true )
            : self::obj();
    }


    /**
     * Get the encripted id of a rule.
     *
     * @param string|array $args    The rule to check. Must be a rule id or a rule route.
     *                              Can also be a rule array.
     * @return string|bool the hashed rule id or false if rule not found
     * @access public
     */
    function get_name( $args = array() ) {
        if ( empty( $args ) ) $args = $this->args ? : array();
        if ( isset( $args['id'] ) && ! empty( $args['id'] ) ) {
            return md5( $args['id'] );
        } elseif ( isset( $args['route'] ) && ! empty( $args['route'] ) ) {
            if ( is_string( $args['route'] ) ) {
                return md5( $args['route'] );
            } elseif ( is_array( $args['route'] ) ) {
                return md5( '/' . implode( '/', $args['route'] ) . '/' );
            }
        }
        return false;
    }


    /**
     * Get the url for a rule
     *
     * @param string $id    the hashed id of a rule
     * @param array $args   arguments to be used as replacement for route jolly chars
     * @return string|null  the url for the rule or null if rule not found
     * @access public
     */
    function get_link( $id = '', $args = array() ) {
        if ( array_key_exists( $id, CleverRules::$rules ) ) {
            $rule = CleverRules::$rules[$id];
			$route = str_replace('{{home}}', '/', $rule['route']);
            $part = trim( vsprintf( $route, $args ), '/\\ ' );
            return trailingslashit( trailingslashit( home_url() ) . $part );
        }
    }


    /**
     * Static method used to create an empty instance of CleverRule class, to have access
     * on dynamic methods without setting another rule
     *
     * @return object new CleverRule instance
     * @access public
     */
    static function obj() {
        $class = __CLASS__;
        return new $class;
    }


}


/**
 * CleverRules class
 *
 * @extends WP
 * @package CleverRules
 * @author Giuseppe Mazzapica
 *
 */
class CleverRules extends WP {


    /**
     * @staticvar array $rules    The array of registered rules
     * @access public
     */
    static $rules = array();
	
	
	/**
     * @staticvar array $rule_groups    The array of registered rule groups
     * @access public
     */
	static $rule_groups = array();


    /**
     * @staticvar array $clever_vars    The array of additiona query variables
     * @access public
     */
    static $clever_vars = array();


    /**
     * @staticvar object $or_wp_rewrite    Objet cloned from global $wp_rewrite
     * @access protected
     */
    protected static $or_wp_rewrite;
	
	
	/**
     * @staticvar string $template	Template file path
     * @access protected
     */
    protected static $template;
	
	
	/**
     * Only function in the package that override a core one.
     * Called on every frontend request and in some admin screens, this function create
     * the query vars based on the url request.
     *
     * @param array $extra_query_vars   additional query vars WordPress pass to core parse_request
     * @uses CleverRules::clever_unset_wp_rewrite
     * @uses CleverRules::get_clever_rules
     * @uses CleverRules::find_clever_rules
     * @uses CleverRules::clever_pieces_match
     * @uses CleverRules::clever_request
     * @uses CleverRules::to_wp
     * @access public
     */
    function parse_request( $extra_query_vars = '' ) {
		if ( ! defined( 'CLEVER_RULES' ) ) return;
        $this->clever_unset_wp_rewrite();
		self::$clever_vars = array_merge( $this->public_query_vars, $this->private_query_vars );
        $the_rules = (array) self::get_clever_rules();
        if ( empty( $the_rules ) ) return $this->to_wp( $extra_query_vars );
        $full_url = add_query_arg( array() );
        $sane_url = explode( '?', $full_url );
        $_qs = array();
        if ( isset( $sane_url[1] ) ) parse_str( $sane_url[1], $_qs );
        $url = $sane_url[0];
        $pieces = array_values( array_filter( explode( '/', $url ) ) );
        $found = self::find_clever_rules( $the_rules, $pieces );
        if ( $found[0] == 'home' )
            return $this->clever_request( $found[1], array(), $_qs, $extra_query_vars );
        if ( empty( $found[1] ) ) return $this->to_wp( $extra_query_vars );
        $match_rule = self::clever_pieces_match( array_values( $found[1] ), $pieces );
        if ( $match_rule === FALSE ) return $this->to_wp( $extra_query_vars );
        return $this->clever_request( $match_rule[0], $match_rule[1], $_qs, $extra_query_vars );
    }


    /**
     * Used by parse_request, backup the current global $wp_rewrite object in a static variable,
     * then empty it. This prevents any unwanted redirection.
     *
     * @return null
     * @access protected
     */
    protected function clever_unset_wp_rewrite() {
        global $wp_rewrite;
        self::$or_wp_rewrite = clone $wp_rewrite;
        $wp_rewrite->permalink_structure = '';
        $wp_rewrite->rules = array();
        $wp_rewrite->extra_rules = array();
        $wp_rewrite->extra_rules_top = array();
        $wp_rewrite->non_wp_rules = array();
        $wp_rewrite->extra_permastructs = array();
    }


    /**
     * Reset the global $wp_rewrite object after parse request runned
     *
     * @return null
     * @access protected
     */
    protected function clever_reset_wp_rewrite() {
        global $wp_rewrite;
        $wp_rewrite = self::$or_wp_rewrite;
    }


    /**
     * Run the WordPress core parse_request if no clever rule match the current request
     *
     * @param array $extra_query_vars   vars passed by WordPress to core parse_request method
     * @return null
     * @access protected
     *
     */
    protected function to_wp( $extra_query_vars ) {
        $this->clever_reset_wp_rewrite();
        return parent::parse_request( $extra_query_vars );
    }


    /**
     * Once a rule match the current request this function check its arg and set query vars and
     * also handle all the clever rule favilities
     *
     * @param array $rule   the rule that match current request
     * @param array $pieces url pieces
     * @param array $url_qs array of url query string
     * @param array $extra_qv   array of vars WorpPress pass to parse_request.
     *                          Used for core parse_request call.
     * @uses CleverRules::clever_request_utils
     * @uses CleverRules::clever_reset_wp_rewrite
     * @return null
     * @access protected
     */
    protected function clever_request( $rule, $pieces, $url_qs = array(), $extra_qv = array() ) {
		if ( apply_filters( 'stop_clever_rule_rule', false, $rule, $this ) )
            return $this->to_wp( $extra_qv );
		do_action_ref_array( 'pre_clever_rules_merge_group', $rule );
		$rule = isset( $rule['group'] ) && ! empty($rule['group'])
            ? self::merge_group($rule)
            : $rule;
		do_action_ref_array( 'pre_clever_rule', $rule );
        do_action( 'pre_clever_rules_query_vars' );
		$all_vars = $this->clever_request_vars( $rule, $url_qs );
		$qs = ! empty( $all_vars ) ? $this->clever_request_check( $all_vars, $pieces ) : array();
		if ( empty( $qs ) ) return $this->to_wp( $extra_qv );
        $this->clever_request_utils( $rule );
        $this->query_vars = $qs;
        do_action( 'clever_rules_query_vars', $this->query_vars );
        $this->clever_reset_wp_rewrite();
    }
	
	
	/**
     * Used by clever_request merge rule options with group options
     *
     * @param array $rule   the rule that match current request
     * @return array	array of rule settings
     * @access protected
     */
    protected function merge_group( $rule = array()  ) {
        $ruleObj = CleverRule::obj();
        $g_id = $ruleObj->get_name( array( 'id' => $rule['group'] ) );
        $group = isset( self::$rule_groups[$g_id] ) ? self::$rule_groups[$g_id] : false;
        if ( $group ) {
            if ( isset($group['id']) ) unset($group['id']);
            return wp_parse_args($rule, $group);
        }
		return $rule;
    }
	
	
	/**
     * Used by clever_request to setup the rule query vars to check after
     *
     * @param array $rule   the rule that match current request
	 * @param array $url_qs	array of vars passed via url
     * @return array	array of query vars
     * @access protected
     */
    protected function clever_request_vars( $rule = array(), $url_qs = array()  ) {
		if ( isset( $rule['vars'] ) && is_array( $rule['vars'] ) && ! empty( $rule['vars'] ) ) {
			$rule_vars = array_merge( $rule['vars'], self::$clever_vars );
            self::$clever_vars = $rule_vars;
        }
		$allow_url_qs = apply_filters( 'clever_rules_allow_merge_qs', $rule['qs_merge'] );
		if ( $allow_url_qs && ! empty( $url_qs ) ) {
			$filtered_url_qs = (array) apply_filters( 'clever_rules_merge_qs', $url_qs, $rule );
			return array_merge( $filtered_url_qs, $rule['query'] );
		}
		return $rule['query'];
    }
	
	
	/**
     * Used by clever_request to setup the rule query vars to check after
     *
     * @param array $all_vars   all the query vars for the rule that match request
     * @return array	array of query vars
     * @access protected
     */
    protected function clever_request_check( $all_vars = array(), $pieces = array() ) {
		$qs = array();
		foreach ( $all_vars as $key => $value ) {
			$good = $value && preg_match( '/^[a-z0-9\-_]*(\[([0-9]+)\])*[a-z0-9\-_]*$/', $value );
			if ( ! in_array($key, self::$clever_vars) ) $good = false;
            if ( ! $good ) continue;
			$is_variable = (bool) preg_match( '/^[a-z_]*(\[([0-9]+)\])+[a-z_]*$/', $value );
            if ( $is_variable && ! empty( $pieces ) ) {
                $f = preg_replace_callback( '/\[([0-9]+)\]/', array(__CLASS__, '_rp'), $value );
                $qs[$key] = vsprintf( $f, $pieces );
            } elseif ( ! $is_variable ) {
                $qs[$key] = $value;
            }
        }
		return $qs;
    }


    /**
     * Used by clever_request to setup template, and before and after action
     *
     * @param array $rule   the rule that match current request
     * @param array $qs query   string setted by clever_request
     * @return null
     * @access protected
     */
    protected function clever_request_utils( $rule = array() ) {
        if ( isset( $rule['template'] ) ) {
			self::$template = $rule['template'];
			add_filter( 'template_include', array( __CLASS__, 'clever_template' ), 99999 );
		}
		if ( isset( $rule['before'] ) && is_callable( $rule['before'] ) )
			call_user_func( $rule['before'], $rule, $this );
        if ( isset( $rule['after'] ) && is_callable( $rule['after'] ) ) {
            $action = apply_filters( 'clever_rule_after', 'template_redirect', $rule, $this );
            add_action( $action, $rule['after'] );
        }
    }


    /**
     * Static method used to get the rules array. When called before parse_request is runned,
     * it scan all registered rules and register the paginated version if needed
     *
     * @uses CleverRules::verify_rule
     * @uses CleverRules::paginate_rule
     * @return array    all the rules registered
     * @access public
     */
    static function get_clever_rules() {
		if ( ! defined( 'CLEVER_RULES' ) ) return;
        if ( ! empty( self::$rules ) && ! did_action( 'pre_clever_rules_query_vars' ) ) {
            foreach ( self::$rules as $rule ) {
                if ( ! self::verify_rule( $rule ) ) continue;
                self::paginate_rule( $rule );
            }
        }
        return self::$rules;
    }


    /**
     * Static method used to verify if a registered rule has required args
     *
     * @param array $rule   the original rule array
     * @return bool true    if the rule is vaild, false otherwise
     * @access private
     */
    private static function verify_rule( $rule ) {
        if ( ! isset( $rule['route'] ) || ! isset( $rule['query'] ) ) return false;
        return ( ! empty( $rule['route'] ) && ! empty( $rule['route'] ) );
    }


    /**
     * Static method used to create the paginated version of a rule when get_clever_rules is called
     * before pre_clever_rules_query_vars
     *
     * @param array $rules   the original rule array
     * @return null
     * @access private
     */
    private static function paginate_rule( $rule ) {
        if ( isset( $rule['paginated'] ) && $rule['paginated'] ) {
            $id = isset( $rule['id'] ) && ! empty( $rule['id'] ) ? $rule['id'] . '.page' : false;
            $paged = '[' . substr_count( $rule['route'], '%' ) . ']';
            $newrule = array(
                'route' => $rule['route'] . 'page/%d/',
                'query' => array_merge( (array) $rule['query'], array('paged' => $paged) ),
                'paginated' => false
            );
            if ( $id ) $newrule['id'] = $id;
            $key = $id ? md5( $id ) : md5( $rule['route'] . 'page/' );
            self::$rules[$key] = wp_parse_args( $newrule, $rule );
        }
    }


    /**
     * Static method used to loop through refistered rules to find one that have same number
     * of pieces of the url, and order tham based on priority
     *
     * @param array $rules    the array of registered rules
     * @param array $pieces  the url pieces
     * @return array    two items array, first is a string 'home' or 'found',
     *                  second is the array of found rules or the home rule array
     * @access private
     */
    private static function find_clever_rules( $rules, $pieces ) {
        $found = array();
        foreach ( $rules as $rule ) {
            $route = array_values( array_filter( explode( '/', $rule['route'] ) ) );
            $query = isset( $rule['query'] ) ? $rule['query'] : false;
            if ( $route[0] == '{{home}}' && ( $pieces === array() ) && $query ) {
                $rule['route'] = '/';
                return array('home', $rule);
            }
			if (  substr_count($route[0], '{{home}}') == 1 )
				$route[0] = str_replace('{{home}}', '', $route[0]);
            if ( ( count( $route ) == count( $pieces ) ) && $query ) {
                $priority = isset( $rule['priority'] )
                    ? intval( $rule['priority'] )
                    : count( $found );
                $rule['route'] = $route;
                $rule['query'] = $query;
                $found[$priority] = $rule;
            }
        }
        return array('found', $found);
    }


    /**
     * Static method used to loop through the found rules to check the one that match with the url
     *
     * @param array $found_rules    the array of rules found
     * @param array $pieces  the url pieces
     * @uses CleverRules::clever_pieces_find
     * @return bool|array   if no rule match return false, otherwise two items array,
     *                      first is the rule that match, second is dynamic query vars for that rule
     * @access private
     */
    private static function clever_pieces_match( $found_rules, $pieces ) {
        ksort( $found_rules );
        foreach ( $found_rules as $rule ) {
            if ( apply_filters( 'skip_clever_rule', false, $rule, $pieces ) ) continue;
            $match = self::clever_pieces_find( $pieces, $rule );
            if ( $match[0] == count( $pieces ) ) return array($rule, $match[1]);
        }
        return false;
    }


    /**
     * Static method used to check the url pieces against the rule pieces
     *
     * @param array $pieces the url pieces
     * @param array $rule   the rule array
     * @uses CleverRules::clever_piece
     * @access private
     * @return array    two items array, first is the number of matches,
     *                  second is dynamic query vars found
     */
    private static function clever_pieces_find( $pieces, $rule ) {
        $match = 0;
        $query_i = array();
        $stop = false;
        $i = 0;
        while ( ! empty( $pieces ) && ($stop == false) ) {
            $piece = array_shift( $pieces );
            if ( $rule['route'][$i] === $piece ) {
                $match ++;
            } elseif ( substr_count( $rule['route'][$i], '%' ) == 1 ) {
                $q = self::clever_piece( $rule['route'][$i], $piece );
                if ( $q ) {
                    $query_i[] = $q;
                    $match ++;
                } else {
                    $stop = true;
                }
            } else {
                $stop = true;
            }
            $i ++;
        }
        return array($match, $query_i);
    }


    /**
     * Static method used to check a single url piece against a single rule piece
     *
     * @param string $route_piece   the rule piece
     * @param string $url_piece the url piece
     * @uses CleverRules::clever_piece_dyn
     * @return string|bool  an url piece or false on wrong arguments
     * @access private
     */
    private static function clever_piece( $route_piece, $url_piece ) {
        if ( ! preg_match( '/^[a-zA-Z0-9\{\}\%]+$/', $route_piece ) ) return false;
        if ( substr_count( $route_piece, '%d' ) == 1 ) {
            return self::clever_piece_dyn( $route_piece, $url_piece, '%d' );
        } elseif ( substr_count( $route_piece, '%s' ) == 1 ) {
            return self::clever_piece_dyn( $route_piece, $url_piece, '%s' );
        }
        return false;
    }


    /**
     * Static method used to check a single url piece against a single rule piece
     *
     * @param string $route_piece   the rule piece
     * @param string $url_piece the url piece
     * @param string $type  the type of piece, can be '%d' or '%s'
     * @return string|bool  an url piece or false on wrong arguments
     * @access private
     */
    private static function clever_piece_dyn( $route_piece, $url_piece, $type = '%d' ) {
        $matches = array();
        $rep = ( $type == '%d' ) ? '[0-9]' : '[a-z0-9\-_]';
        $pattern = str_replace( $type, '(' . $rep . '+)', $route_piece );
        if ( substr_count( $pattern, '(' . $rep . '+){' ) == 1 )
            $pattern = str_replace( '(' . $rep . '+){', '(' . $rep . '){', $pattern );
        if ( preg_match( '/^' . $pattern . '$/', $url_piece, $matches ) == 1 )
            return sprintf( $type, $matches[1] );
        return false;
    }


    /**
     * Static method used as callback for preg_replace_callback in CleverRules::clever_request
     *
     * @param array $matches    Matches coming from preg_replace_callback
     * @return string   replaced matches
     * @access protected
     */
    protected static function _rp( $matches ) {
        return '%' . ( intval( $matches[1] ) + 1 ) . '$s';
    }
	
	
	
	/**
     * Static method used as callback for preg_replace_callback in CleverRules::clever_request
     *
     * @param string $template	Template path passed by template_include filter
     * @return string   Template path to include
     * @access public
     */
    static function clever_template( $template ) {
		if ( ! defined( 'CLEVER_RULES' ) ) return;
		if ( empty( self::$template ) ) return $template;
		$replace = locate_template( self::$template, false, false );
        return $replace ? $replace : $template;
    }


}


/**
 * Init the CleverRules class that overwrite global $wp object. Run on setup_theme hook.
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 * @return null
 */
function cleverRulesInit() {
    if ( defined( 'CLEVER_RULES' ) && CLEVER_RULES ) return;
    define( 'CLEVER_RULES', 1 );
    global $wp;
    if ( get_class( $wp ) === 'WP' ) $wp = new CleverRules();
}


/**
 * Main API function used to register a new rule.
 * Must be called before setup_theme hook (on plugins_loaded).
 *
 * @package CleverRules
 * @param array $args   arguments to register the rule
 * @uses CleverRule::register
 * @return object CleverRule instance
 * @access public
 */
function register_clever_rule( $args = array() ) {
    if ( did_action( 'setup_theme' ) ) {
        $msg = 'register_clever_rule must be called before setup_theme hook is fired.'
            . 'plugins_loaded hook is a good place.';
        _doing_it_wrong( ' register_clever_rule', $msg, null );
    }
    if ( is_string( $args ) ) $args = array( 'route' => $args );
    return CleverRule::register( $args );
}



/**
 * API function used to register a rule group.
 * Must be called before setup_theme hook (on plugins_loaded).
 *
 * @package CleverRules
 * @param array $args   arguments to register the group
 * @uses CleverRule::register_group
 * @return object CleverRule instance
 * @access public
 */
function register_clever_group( $args = array() ) {
    if ( did_action( 'setup_theme' ) ) {
        $msg = 'register_clever_group must be called before setup_theme hook is fired.'
            . 'plugins_loaded hook is a good place.';
        _doing_it_wrong( ' register_clever_group', $msg, null );
    }
	if ( is_string( $args ) ) $args = array( 'id' => $args );
    return CleverRule::register_group( $args );
}


/**
 * API function to retrieve the hashed rule id from plain id or from route (if no id is given)
 *
 * @package CleverRules
 * @param string|array $id the rule id or an array containing rule args
 * @uses CleverRule::obj
 * @uses CleverRule::get_name
 * @return string|bool	the url or false on fail
 * @access public
 */
function get_the_clever_ruleid( $id = null, $rule = null ) {
    if ( empty( $rule ) || ! ( $rule instanceof CleverRule ) ) $rule = CleverRule::obj();
    $rule_name = false;
    if ( is_string( $id ) ) {
        $key = substr_count( $id, '/' ) ? 'route' : 'id';
        $arg = array($key => $id);
        $rule_name = $rule->get_name( $arg );
    } elseif ( is_array( $id ) && ( isset( $id['route'] ) || isset( $id['id'] ) ) ) {
        $rule_name = $rule->get_name( $id );
    }
    return $rule_name;
}


/**
 * API function to retrieve the url related to a root and some args. Use get_the_clever_ruleid
 *
 * @package CleverRules
 * @param string|array $id  the rule id or an array containing rule args
 * @param array $args   arguments to be used as replacement for route jolly chars
 * @uses CleverRule::obj
 * @uses CleverRule::get_link
 * @uses get_the_clever_ruleid
 * @return string|bool	the url or false on fail
 * @access public
 */
function get_the_cleverlink( $id = null, $args = array() ) {
    $rule = CleverRule::obj();
    $name = get_the_clever_ruleid( $id, $rule );
    return $name
        ? apply_filters( 'get_the_cleverlink', $rule->get_link( $name, $args ), $id, $args )
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


// let's go clever!
add_action( 'setup_theme', 'cleverRulesInit', 999 );