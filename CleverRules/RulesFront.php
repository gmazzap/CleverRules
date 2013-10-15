<?php
namespace CleverRules;


class RulesFront extends \WP implements RulesFrontInterface {


    private $cr_rules;


    private $cr_extra = '';


    public function __construct( RulesInterface $rules ) {
        $this->cr_rules = $rules;
    }


    function to_wp() {
        return parent::parse_request( $this->cr_extra );
    }


    public function parse_request( $extra_query_vars = '' ) {
        $this->cr_extra = $extra_query_vars;
        $clever = $this->cr_rules->found() && $this->cr_rules->parse();
        if ( $clever ) $this->query_vars = $this->cr_rules->query_vars;
        $this->cr_rules->reset_rewrite();
        if ( ! $clever ) $this->to_wp();
    }


}