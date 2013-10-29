<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * WPMerger Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class WPMerger implements CRI\WPMerger {


    protected $wp;


    protected $vars;


    public function __construct( \WP $wp ) {
        $this->wp = $wp;
    }


    public function wp_merge() {
        $this->vars = \array_merge( $this->wp->public_query_vars, $this->wp->private_query_vars );
    }


    public function get_vars() {
        return $this->vars;
    }


}