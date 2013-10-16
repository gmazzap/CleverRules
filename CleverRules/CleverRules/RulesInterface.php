<?php
namespace CleverRules;

interface RulesInterface {


    function __construct(
        UrlInterface $u, SettingsInterface $s, MatcherInterface $m, ParserInterface $p
    );


    function unset_rewrite();


    function reset_rewrite();


    function setup();


    function found();


    function match();


    function parse();


}