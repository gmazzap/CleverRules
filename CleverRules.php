<?php
/**
 * Plugin Name: Clever Rules
 * Plugin URI: http://rules.zoomlab.it/
 * Description: Just a "no surprises" and clever way to handle rewrite rules in WordPress.
 * Version: 0.2.0
 * Author: Giuseppe Mazzapica
 * Requires at least: 3.6
 * Tested up to: 3.6.1
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 *
 */

define( 'CLEVER_RULES_PATH', plugin_dir_path( __FILE__ ) );

require_once CLEVER_RULES_PATH . 'CleverRules/Interfaces/Loader.php';
require_once CLEVER_RULES_PATH . 'CleverRules/Loader.php';
require_once CLEVER_RULES_PATH . 'Api.php';

$loader = \CleverRules\Loader::get_instance();
$loader->load_dir( CLEVER_RULES_PATH . 'CleverRules/Interfaces' );
$loader->load_dir( CLEVER_RULES_PATH . 'CleverRules' );


/**
 * Setup all dependencies and instanziate the \CleverRules\RulesFront class
 * that extends WP class and override global $wp object. Run on setup_theme hook.
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 * @return null
 */
function cleverRulesInit() {
    if ( defined( 'CLEVER_RULES' ) && CLEVER_RULES || ! defined( 'ABSPATH' ) ) return;
    define( 'CLEVER_RULES', 1 );
    global $wp;
    if ( get_class( $wp ) === 'WP' ) {
        $url = new \CleverRules\Url;
        $settings = new \CleverRules\Settings();
        $merger = new \CleverRules\WPMerger( $wp );
        $merger->wp_merge();
        $settings->merge( array( 'vars' => $merger->get_vars() ) );
        $matcher = new \CleverRules\Matcher( $url );
        $parser = new \CleverRules\Parser( $settings, $url );
        $rules = new \CleverRules\Rules( $url, $settings, $matcher, $parser );
        $rules->setup();
        $wp = new \CleverRules\RulesFront( $rules );
    }
}

// let's go clever!
add_action( 'setup_theme', 'cleverRulesInit', 9999 );