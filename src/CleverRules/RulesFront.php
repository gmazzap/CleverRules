<?php
namespace CleverRules;

use CleverRules\Interfaces as CRI;


/**
 * RulesFront Class
 *
 * @package CleverRules
 * @author Giuseppe Mazzapica
 */
class RulesFront extends \WP implements CRI\RulesFront {


    protected $cr_rules;


    protected $cr_extra = '';


    public function __construct( CRI\Rules $rules ) {
        $this->cr_rules = $rules;
    }


    function to_wp() {
        return parent::parse_request( $this->cr_extra );
    }


    public function parse_request( $extra_query_vars = '' ) {
        $this->cr_extra = $extra_query_vars;
        $clever = $this->cr_rules->found() && $this->cr_rules->parse();
        if ( $clever ) {
            $this->query_vars = $this->cr_rules->query_vars;
            \remove_filter( 'template_redirect', 'redirect_canonical' );
        }
        if ( ! $clever ) $this->to_wp();
    }


}


