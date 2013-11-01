<?php
/**
 * Plugin Name: Clever Rules
 * Plugin URI: http://rules.zoomlab.it/
 * Description: Just a "no surprises" and clever way to handle rewrite rules in WordPress.
 * Version: 0.3.0
 * Author: Giuseppe Mazzapica
 * Requires at least: 3.6
 * Tested up to: 3.6.1
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 *
 */
define( 'CLEVER_RULES_PATH', plugin_dir_path( __FILE__ ) );

require CLEVER_RULES_PATH . 'vendor/autoload.php';


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
        $settings->merge( array('vars' => $merger->get_vars()) );
        $matcher = new \CleverRules\Matcher( $url );
        $parser = new \CleverRules\Parser( $settings, $url );
        $rules = new \CleverRules\Rules( $url, $settings, $matcher, $parser );
        $wp = new \CleverRules\RulesFront( $rules );
    }
}


// let's go clever!
add_action( 'setup_theme', 'cleverRulesInit', 9999 );