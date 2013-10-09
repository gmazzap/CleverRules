<?php
/**
 * Plugin Name: Clever Rules
 *
 * Description: Just a "no surprises" and clever way to handle rewrite rules in WordPress.
 * Version: 0.1.0
 * Author: Giuseppe Mazzapica
 * Requires at least: 3.6
 * Tested up to: 3.6.1
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 *
 */



/**
 * CleverRule class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 *
 */
class CleverRule {
	
	/**
	 * @static var array $_valid	The allowed params for
         * register_clever_rule use for sanization and to handle chained methods
	 * @access protected
	 *
	 */
	protected static $_valid = array(
            'route',
            'id',
            'query',
            'priority',
            'before',
            'after',
            'vars',
            'paginated',
            'qs_merge'
        );

	
	
	/**
	 * @static var array $chained_called	The array that contain the
         * chained method alredy called to avoid repetitions
	 * @access protected
	 *
	 */
	protected $chained_called = array();
	
	
	
	/**
	 * @var array $args	The final array of args of a rule
	 * @access public
	 *
	 */
	protected $args;



	/**
	 * @var array $valid	The final array of valid arguments for
         * a rule object. It is the $_valid static array (maybe)
         * altered by a filter
	 * @access protected
	 *
	 */
	protected $valid;
	
	
	
	/**
	 * Use method overloading to allow chaining function to set argument
	 *
	 * @param string $name the name of the requested method
	 * @param array $args arguments passed to method
	 * @access public
	 * @return mixed $this->chained result
	 *
	 */
	function __call($name, $args) {
            if ( in_array($name, self::$_valid) && isset($args[0]) )
                return $this->chained($name, $args[0]);
	}
	
	
	
	
	/**
	 * Check an array of arguments returning only the ones registered as valid
	 *
	 * @param array $args arguments to check
	 * @access protected
	 * @return array valid arguments
	 *
	 */
	protected function only_valid( $args = array() ) {	
            $invalid_keys = array_diff( array_keys($args), $this->valid );
            if ( ! empty($invalid_keys) ) {
                foreach( $invalid_keys as $invalid_key)
                    unset($args[$invalid_key]);
            }
            return $args;
	}
	
	
	
	
	/**
	 * Take a valid argument and return it sanitized version
	 *
	 * @param string $which argument name
	 * @param string $value argument value
	 * @param array $args array of all arguments
	 * @access protected
	 * @return mixed the sanitized argument value
	 *
	 */
	protected function sanitize ( $which, $value, $args = array() ) {
            if ( ! in_array($which, $this->valid) ) return false;
            switch ($which) {
                case 'route' :
                    return (
                        is_string($value)
                        && (
                            $value === '/'
                            || (bool) count( array_filter( explode( '/', $value ) ) )
                        )
                    )
                        ? trim($value)
                        : false;
		case 'id' :
                    return ( preg_match('/^[a-z0-9_\.]+$/', $value) === 1 )
                        ? $value
                        : false;
		case 'vars' :
		case 'query' :
                    if ( ! is_array($value) || empty($value) ) return false;
                    if ( $which == 'query' )
                        if ( ! array_filter( array_map( 'is_string', array_keys( $value ) ) ) )
                            return false;
			if ( ! array_filter( array_map( 'is_string', array_values( $value ) ) ) )
                            return false;
			return $value; 
		case 'priority' :
                    return ( is_int($value) ) ? $value : false;
		case 'before' :
		case 'after' :
                    return ( is_callable($value) ) ? $value : false;
		case 'qs_merge' :
		case 'paginated' :
                    return (bool) $value;
		default :
                    if (
                        is_string($which)
                        && ! is_callable($value)
                        && isset( $args['sanitize_' . $which] )
                        && is_callable( $args['sanitize_' . $which] )
                    ) {
                        return call_user_func(
                            $args['sanitize_' . $which],
                            $which,
                            $value
                        ); 
                    } elseif ( is_callable($value) && is_string($which) ) {
			$sanitize_ = substr($which, 0, 9);
			if ( $sanitize_ == 'sanitize_' ) {
                            $arg = str_replace('sanitize_', '', $which);
                            return array_key_exists( $arg, $this->args )
                                ? $value
                                : false;
			}
                    }
                    return false;
		}
	}
	
	
	
	
	/**
	 * Get the encripted id of a rule.
	 *
	 * @param string|array $args the rule to check.
         * Must be a rule id (if present) or a rule route.
         * Can also be a rule array.
	 * @access public
	 * @return string|bool the hashed rule id or false if rule not found
	 *
	 */
	function get_name( $args = array() ) {
            if ( empty($args) ) $args = $this->args ? : array();
            if ( isset($args['id']) && ! empty($args['id']) ) {
                return md5( $args['id'] );
            } elseif ( isset($args['route']) && ! empty($args['route']) ) {
                if ( is_string($args['route']) ) {
                    return md5( $args['route'] );
		} elseif( is_array( $args['route'] ) ) {
                    return md5( '/' . implode('/', $args['route']) . '/' );
		}
            }
            return false;
	}
	
	
		
	/**
	 * Get the url for a rule
	 *
	 * @param string $id the hashed id of a rule
	 * @param array $args arguments to be used as replacement
         * for route jolly chars
	 * @access public
	 * @return string|null the url for the rule or null if rule not found
	 *
	 */
	function get_link( $id = '', $args = array() ) {
            if ( array_key_exists($id, CleverRules::$clever_rules) ) {
		$rule = CleverRules::$clever_rules[$id]; 
		$part = trim( vsprintf($rule['route'], $args), '/\\ ');
		return trailingslashit(
                    trailingslashit( home_url() ) . $part
                );
            }
	}
	
	
	
	/**
	 * Write the rule argument on the CleverRules $clever_rules static array.
         * Called on contruct and on every chained method
	 *
	 * @access protected
	 * @return null
	 *
	 */
	protected function setup_rule() {
            if ( $this->args['route'] === '/' )
                $this->args['route'] = '{{home}}';
            $route = isset($this->args['route']) ? $this->get_name() : false;
            if ( $route ) CleverRules::$clever_rules[$route]  = $this->args;
	}
        
	
	
	
	/**
	 * Constructor. Protected, should not be used to create a rule,
         * the register_clever_rule API function should be used instead.
	 *
	 * @access protected
	 * @return object CleverRule instance
	 *
	 */
	protected function __construct( $args = array() ) {
            if ( is_array($args) && ! empty($args) ) {
                $this->valid = (array) apply_filters(
                    'clever_rules_valid_args',
                    array()
                );
		if ( ! empty($this->valid) ) {
                    foreach ( $this->valid as $add_valid ) {
                        if (
                            ( substr($add_valid, 0, 9) != 'sanitize_' )
                             && ! array_key_exists(
                                 'sanitize_' . $add_valid,
                                 $this->valid
                             )
                        )
                            $this->valid[] = 'sanitize_' . $add_valid;
                    }
                }
		$this->valid = array_merge( $this->valid, self::$_valid);
		$args = $this->only_valid($args);
		if ( ! empty($args) ) {
                    foreach ( $args as $key => $value ) {
                        $sanitized = $this->sanitize(
                            $key,
                            $value,
                            $args
                         );
			if ($sanitized) $this->args[ $key ] = $sanitized;
                    }    
                 }
		$this->setup_rule();
            }
	}
	
	
	
	
	/**
	 * Is the function called by all the chained methods.
         * Check the argume named after the method itself,
         * sanitize it and updated the rule in CleerRules rules
         * array using setup_rule
	 *
	 * @param string $which the argument to setup
	 * @param mixed $value the argument value
	 * @access protected
	 * @return object present CleverRule instance
	 *
	 */
	protected function chained ( $which, $value ) {
            if (
                in_array($which, $this->chained_called)
                || empty($this->args)
            )
                return $this;
            $this->chained_called[] = $which;
            $sanitized = $this->sanitize( $which, $value );
            if ( ! is_null($sanitized) )
                $this->args = array_merge(
                    $this->args,
                    array( $which => $sanitized )
                );
            $this->setup_rule();
            return $this;
	}
	
	
	
	
	/**
	 * Static method used by register_clever_rule API function to setup args,
         * merging defaults and create a new instance
	 *
	 * @param array $args the rule arguments array
	 * @access public
	 * @return object new CleverRule instance
	 *
	 */
	public static function register( $args = array() ) {
		if ( did_action('setup_theme') ) {
			_doing_it_wrong(
                            __CLASS__.'::register',
                            __CLASS__.' register should not be called directly,'
                                . 'but using register_clever_rule hooken in a'
                                . 'function that run before setup_theme hook '
                                . 'is fired. plugins_loaded hook is a good place.', 
                            null );
			return;
		}
		$defaults = array(
			'route' => null,
			'id' => '',
			'query' => array(),
			'priority' => 0,
			'before' => null,
			'after' => null,
			'vars' => array(),
			'paginated' => true,
			'qs_merge' => true
		);
		$args = apply_filters(
                        'clever_rule_args', wp_parse_args($args, $defaults) 
                );
		return ( ! empty($args) && is_array($args) ) ?
                    new CleverRule( $args ) : self::obj();
	}
	
	
	
	
	/**
	 * Static method used to create an empty object of CleverRule class,
         * to have access on dynamic methods without setting another rule
	 *
	 * @access public
	 * @return object new CleverRule instance
	 *
	 */
	public static function obj() {
		$class = get_class();
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
	
	
	
	static $clever_rules = array();
	
	
	
	static $clever_add_vars = array();
	
	
	
	protected static $original_wp_rewrite;
	
	
	
	
	protected static function _rp( $matches ) {
            return '%' . (intval($matches[1]) + 1) . '$s';
        }
	
	
	
	
	/**
	 * Used by parse_request, backup the current global $wp_rewrite object
         * in a static variable, then empty it. This prevent any unwanted
         * canonical redirection
	 *
	 * @access protected
	 * @return anull
	 *
	 */
	protected function clever_unset_wp_rewrite() {
		global $wp_rewrite;
		self::$original_wp_rewrite = clone $wp_rewrite;
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
	 * @access protected
	 * @return null
	 *
	 */
	protected function clever_reset_wp_rewrite() {
		global $wp_rewrite;
		$wp_rewrite = self::$original_wp_rewrite;
	}
	
	
	
	/**
	 * Run the WordPress core parse_request if no clever rule match the
         * current request
	 *
	 * @param array $extra_query_vars vars passed by WordPress to
         * parse_request method
	 * @access protected
	 * @return null
	 *
	 */
	protected function clever_parse_request_wp( $extra_query_vars ) {
		$this->clever_reset_wp_rewrite();
		return parent::parse_request( $extra_query_vars );
	}
	
	
	
	
	/**
	 * Once a rule match the current request this function check its arg
         * and set query vars and also handle all the clever rule favilities
	 *
	 * @param array $rule the rule that match current request
	 * @param array $pieces url pieces
	 * @param array $url_qs array of url quesry string
	 * @param array $extra_query_vars array of vars WorpPress pass to
         *  parse_request. Used only for the failback core parse_request call.
	 * @access protected
	 * @return null
	 *
	 */
	protected function parse_request_clever(
            $rule,
            $pieces,
            $url_qs,
            $extra_query_vars
        ) {
            do_action('pre_clever_rules_query_vars');			
            $qs = array();
            foreach( $rule['query'] as $key => $value  ) {
		$open = (int)substr_count($value, '[');
		$closed = (int)substr_count($value, ']');
		$is_variable = ($open == $closed) && $open > 0;
		if ( $is_variable && ! empty($pieces) ) {
                    $format = preg_replace_callback('/\[([0-9]+)\]/',
                        array('CleverRules', '_rp'), $value
                    );
                    $qs[$key] = vsprintf( $format, $pieces );
		} elseif ( preg_match('/[a-z0-9_\-]/', $value) === 1 ) {
                    $qs[$key] = $value;
		}
            }		
		if (
                    apply_filters(
                        'stop_clever_rule_rule',
                        false,
                        $rule,
                        $this
                    )
                )
                    return $this->clever_parse_request_wp( $extra_query_vars );
		if (
                    isset($rule['vars'])
                    && is_array( $rule['vars'] )
                    && ! empty( $rule['vars'] )
                ) {
                    self::$clever_add_vars = $rule['vars'];
                    add_filter(
                        'query_vars',
                        array('CleverRules', 'clever_set_vars')
                    );
		}
		if ( isset($rule['before']) && is_callable( $rule['before'] ) )
                    call_user_func($rule['before'], $rule, $this );
		if ( isset($rule['after']) &&  is_callable( $rule['after'] ) )
                    add_action('template_redirect', $rule['after']);
		$allow = apply_filters(
                    'clever_rules_allow_merge_qs',
                    $rule['qs_merge']
                );
		if ( $allow )
                    $qs = apply_filters(
                        'clever_rules_merge_qs',
                        array_merge($qs, $url_qs), $qs, $url_qs, $rule
                    );
		$this->query_vars = $qs ? : array();
		do_action('clever_rules_query_vars', $this->query_vars);
		$this->clever_reset_wp_rewrite();
	}
	
	
	
	/**
	 * Static method used to check a single url piece against
         * a single rule piece
	 *
	 * @param string $route the rule piece
	 * @param string $piece the url piece
	 * @access protected
	 * @return mixed a query value or false if no match
	 *
	 */
	protected function clever_check_piece( $route, $piece ) {
		$valid = array_merge(
                    range('a', 'z'),
                    range(0, 9),
                    array('{','}', '%')
                );
                if (
                    ( substr_count($route, '{') > 1 ) ||
                    ( substr_count($route, '}') > 1 ) ||
                    ( str_replace( $valid, '', $route) != '' )
	  	) return false;
	  	if ( substr_count($route, '%d') == 1 ) {
                    // dynamic numeric piece
                    $pattern = str_replace('%d', '([0-9]+)', $route );
                    if ( substr_count($pattern, '([0-9]+){') == 1 )
                        $pattern = str_replace('([0-9]+){', '([0-9]){', $pattern);
                    if ( preg_match( '/^' . $pattern . '$/', $piece, $matches ) == 1) {
                        return sprintf('%d', $matches[1]);
                    }
                    return false;
	  	} elseif ( substr_count($route, '%s') == 1 ) {
                    // dynamic string piece
                    $pattern = str_replace('%s', '([a-z0-9\-_]+)', $route );
                    if ( substr_count($pattern, '([a-z0-9\-_]+){') == 1 )
                        $pattern = str_replace('([a-z0-9\-_]+){', '([a-z0-9\-_]){', $pattern);
                    if (
                        preg_match( '/^' . $pattern . '$/', $piece, $matches ) == 1
                    ) {
                        return sprintf('%s', $matches[1]);
                    }
                    return false;
	  	}
		return false;
	}
	
	
	
	
	/**
	 * Static method used to get the rules array.
         * When called before parse_request is runned,
         * it scan all registered rules and registered
         * the paginated version if needed
	 *
	 * @access public
	 * @return array all the rules registered
	 *
	 */
	public static function get_clever_rules() {
		if ( ! empty(self::$clever_rules)
                    && ! did_action('pre_clever_rules_query_vars')
                ) {
                    foreach ( self::$clever_rules as $rule) {
                        if (
                            ! isset($rule['route']) ||
                            ! isset($rule['query']) ||
                            empty($rule['route']) ||
                            empty($rule['query'])
                        ) continue;
                        if ( isset($rule['paginated']) && $rule['paginated']) {
                            $id = isset($rule['id']) && ! empty($rule['id'])
                                ? $rule['id'] . '.page'
                                : false;
                            $paged = '[' . substr_count($rule['route'],'%') . ']';
                            $newrule = array(
                                'route' => $rule['route'] . 'page/%d/',
                                'query' => array_merge(
                                    (array)$rule['query'],
                                    array ( 'paged' => $paged )
                                ),
                                'paginated' => false
                            );
                            if ($id) $newrule['id'] = $id;
                            $key = $id
                                ? md5($id)
                                : md5($rule['route'] . 'page/');
                            self::$clever_rules[ $key ] = wp_parse_args(
                                $newrule,
                                $rule
                            );
                        }
                    }
		}
		return self::$clever_rules;
	}
	
	
	
	
	/**
	 * Save in the static $clever_add_vars array, the variable that the
         * matching rule pass as 'vars' argument.
         * Those vars are merged to the WordPress query vars
         * via query_vars filter.
	 *
	 * @param array $vars variables to register
	 * @access public
	 * @return array additional query vars to register
	 *
	 */
	static function clever_set_vars( $vars ) {
            return ( ! empty( self::$clever_add_vars ) )
                ? array_merge( $vars, self::$clever_add_vars ) : $vars;
	}
	
	
	
	
	/**
	 * Only function in the package that override a core one.
         * Called on every frontend request and in some admin screens,
         * this function create the query vars based on the url request.
	 *
	 * @access public
	 * @return array additional query vars WorPress pass to core
         * parse_request function. Used only for failback call
         * the core parse_request.
	 *
	 */
	function parse_request( $extra_query_vars = '' ) {
		// clean the global wp rewrite to avoin unwanted redirects
		$this->clever_unset_wp_rewrite();
		$the_rules = (array) self::get_clever_rules();
		// if not rules let WordPress do its work
		if ( empty($the_rules) )
                    return $this->clever_parse_request_wp( $extra_query_vars );
		// get the current url with home url already stripped
		$url = add_query_arg( array() );
		$sane_url = explode('?', $url);
		$_qs = array();
		if ( isset($sane_url[1]) ) parse_str($sane_url[1], $_qs);
		$url = $sane_url[0];
		$pieces = array_values( array_filter( explode('/', $url) ) );
		$found = array();
		// search rules that have the same number of pieces of url
                // and put into $found array using priority as key
		foreach ( $the_rules as $rule ) {
                    $route = array_values(
                        array_filter( explode('/', $rule['route'] ) )
                    );		
			$query = isset($rule['query']) ? $rule['query'] : false;
			if (
                            $route[0] == '{{home}}'
                            && ( $pieces === array() )
                            && $query
                        ) {
                            $rule['route'] = '/';
                            return $this->parse_request_clever(
                                $rule,
                                $extra_query_vars
                            );
			}
			if (
                            ( count($route) == count($pieces) )
                            || ( $route[0] == '{{home}}' && $pieces === array() )
                            && $query
                        ) {
                            // setup the rule array for before inserting
                            // in found array with priority as key.
                            // 'route' is now an array of pieces
                            $priority = isset($rule['priority'])
                                ? intval($rule['priority'])
                                : count($found);
                                // if no priority setted for rule,
                                // use the first index available
                            $rule['route'] = $route;
                            $rule['query'] = $query;
                            $found[$priority] = $rule;
			}
		}
		// if no rules found let WordPress do its work
		if ( empty($found) )
                    return $this->clever_parse_request_wp( $extra_query_vars );
		// order the found rules by priority ( setted as key )
		ksort($found);
		$found = array_values($found);
		// set all pieces for use in filter and to check
                // the total number of pieces
		$all_pieces = $pieces;
		// loop the found rules and check every rule piece with
                // the related url piece
		foreach ( $found as $rule ) {
			// a filter that allow skip a rule based on url
                        // and/or rule pieces 
			if ( apply_filters(
                                'skip_clever_rule',
                                false, $rule, $all_pieces)
                            )
                            continue;
			$pieces = $all_pieces;
			$match = 0;
			$query_i = array();
			$stop = false;
			$i = 0;
			while ( ! empty($pieces) && ($stop == false) ) {
				$piece = array_shift($pieces);
				if ( $rule['route'][$i] === $piece )  {
                                    // static piece
                                    $match++;
				} elseif (
                                    substr_count($rule['route'][$i], '%') == 1
                                ) { // dynamic piece
                                    $q = self::clever_check_piece(
                                        $rule['route'][$i],
                                        $piece
                                    );
                                    if ( $q ) {
                                        $query_i[] = $q; $match++;
                                    } else {
                                        $stop = true;
                                    }
				} else {
					// if one of the pieces does not match
                                        // stop the while cycle
					$stop = true;
				}
				$i++;
			}
			// a match is found go settings the query vers
                        // and other stuff
			if ( $match == count($all_pieces) )
                            return $this->parse_request_clever(
                                $rule, $query_i, $_qs, $extra_query_vars
                            );
		}
		// as failback, let WordPress do its work
		return $this->clever_parse_request_wp( $extra_query_vars );
	}
	
	
	
	
}




/**
 * Init the CleverRules class that overwrite global $wp object.
 * Run on setup_theme hook.
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 * @return null
 *
 */
function cleverRulesInit() {
	if ( defined('CLEVER_RULES') && CLEVER_RULES ) return;
	define('CLEVER_RULES', 1);
	global $wp;
	if ( get_class($wp) === 'WP' ) $wp = new CleverRules();
}




/**
 * Main API function used to register a new rule.
 * Must be called before setup_theme hook (on plugins_loaded).
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 * @param array args
 * @return object CleverRule 
 *
 */
function register_clever_rule( $args = array() ) {
	if ( did_action('setup_theme') )
            _doing_it_wrong(
                'register_clever_rule',
                'register_clever_rule must be called'
                . 'before setup_theme hook is fired.'
                . 'plugins_loaded hook is a good place.',
                null
            );
	if ( is_string($args) ) $args = array( 'route' => $args );
	return CleverRule::register( $args );
}




/**
 * API function to retrieve the hashed rule id from the plain one
 * or from a route (if no id is given)
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 * @param string|array $id the rule id or an array containing rule args
 * @return string|bool	the url or false on fail
 *
 */
function get_the_clever_ruleid( $id = null, $rule = null ) {
	if ( empty($rule) || ! is_a($rule, 'CleverRule') )
            $rule = CleverRule::obj();
	$rule_name = false;
	if ( is_string($id) ) {
		$key = substr_count($id, '/') ? 'route' : 'id';
		$arg = array($key => $id);
		$rule_name = $rule->get_name( $arg );
	} elseif (
            is_array($id)
            && ( isset($id['route'])
            || isset($id['id']) )
        ) {
            $rule_name = $rule->get_name( $id );
	}
	return $rule_name;
}




/**
 * API function to retrieve the url related to a root and some args.
 * Use get_the_clever_ruleid
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 * @param string|array $id the rule id or an array containing rule args
 * @return string|bool	the url or false on fail
 *
 */
function get_the_cleverlink( $id = null, $args = array() ) {
	$rule = CleverRule::obj();
	$rule_name = get_the_clever_ruleid( $id, $rule );
	return $rule_name
            ? apply_filters(
                'get_the_cleverlink',
                $rule->get_link($rule_name, $args), $id, $args )
            : false;
}




/**
 * API function to echo the url related to a root and some args.
 * Echo the result of get_the_cleverlink
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 * @param string|array $id the rule id or an array containing rule args
 * @return null
 *
 */
function the_cleverlink( $id = null, $args = array() ) {
	echo apply_filters(
            'the_cleverlink',
            get_the_cleverlink($id, $args), $id, $args
        );
}




// let's go clever!
add_action('setup_theme', 'cleverRulesInit', 999);