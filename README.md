CleverRules
===========

Clever Rules is a plugin that use a completely new way to handle rewrite rules in WordPress.
In effects, this plugin does not do (nor save) any rewrite it just takes an url and convert it in a query.

Plugin require at least PHP 5.3

***

##Register Rules##

Rules are registered via the function `register_clever_rule`.

##Basic Usage##
`register_clever_rule` has several arguments, however only 2 are required, the route and the query.
Rute is the url to handle, query is the query to run for that url.

    $args = array(
        'route' => '/say/%s/to/%s',
        'query' => 'name=[0]-[1]'
    );
    register_clever_rule( $args );
    
Previous example when the url `example.com/say/hello/to/world/` is visited, will opend the post with slug `hello-world`
    
##Advanced Use##
The plugin can some advanced tasks, like setting a specific template for rules, perform actions before and/or after the query is parsed,
set additional query variables, auto create rules for pagination, grouping rules to easy share settings, and so on...
An example for more advanced use:

    register_clever_rule( '/one/%s/foo/%s{3}/' )
        ->query( 'name=[0]&foo=[1]&bar=baz' )
        ->priority( 1 )
        ->paginated( true )
        ->vars( array('foo','bar') )
        ->qs_merge( true)
        ->group('examples');
        
This example use the alternative syntax, where arguments are setted via methods named like the arguments.

***

For complete docs and how-to please visit plugin site [rules.zoomlab.it](http://rules.zoomlab.it/)
