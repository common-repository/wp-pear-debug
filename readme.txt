=== Plugin Name ===
Contributors: Garvin Casimir
Tags: debug, pear, php_debug, debugging, database debug, performance debug, performance
Requires at least: 2.8
Tested up to: 4.3.1
Stable tag: 1.5

This plugin incorporates the pear php_debug library into wordpress.

== Description ==

This plugin incorporates the pear php_debug library into wordpress. 

I started creating a new website around wordpress and noticed that there was no way to quickly access debug information so I integrated this class
into wordpress for quick easy debugging when developing or experiencing problems on your wordpress website.

Please feel free to ping me on twitter if you have a question.

- Features 

This plugin unlike most operates from within a class.
There are several options which can be set in the admin section

1. Debug Status: Overall
	this option allows you to enable and disable debugging entirely
2. Display Debugging for: Guests
	This option allows you to enable debugging when no user is logged in
3. The rest are a list of roles found in the sytem, eg. editor, contributor, administrator
	For each you have the following options: Admin & Front End, Admin Only, Front End Only, Disable
4. The plugin shows queries that were run by wordpress
        Please note that some queries run before the plugin is initialized. to ensure most if not all queries get recorded see step 4 in installation section. v1.4.1 shows not only the query but the time taken by the query and the function which called the query.
5. You can easly add debug information to the debugger by making use of several functions

	`<?php` 
		`//For advanced use. `
		`//Direct access to instance of debugger`
		`$oDebug = wp_pear_debug::get();`		
		`$oDebug->add($variable); //add variable to debug`
		`$oDebug->dump($object,$varname); //var_dump an array or object. $varname optional`
		`$oDebug->queryRel($info); //add query related info`
		`$oDebug->error($info); //add user error`
		`//more options available in the pear::php_debug documentation`
		
		`//With v1.2 you have access to several wrapper functions`
		`//Enough for most people`
                `wp_pear_debug::add();`
                `wp_pear_debug::dump();`
                `wp_pear_debug::error();`
		`//if you run this query with wpdb`
		`//It will probably appear anyways.`
                `wp_pear_debug::query();`

	
	 `?>`

6. Debug information appears in a neat panel controlled by javascript. The options expand and collapse. The debug panel also has
 a close button to completely remove the debug panel.

7. Note that the debug bar floats at the top right and will not disrupt your layout.

8. To ensure layout stability the plugin is hard coded to use only the HTML Div Renderer

9. displaying server and response varables.
    * Request
    * Response
    * Setings
    * Globals
    * Php
    * Files
    * Database queries
    * Execution time
    * Errors and messages
    * Link to w3c validator
10. With v1.2 you can add debug information via shortcode from within your post. `[wp_pear_debug]foo bar[/wp_pear_debug]` or you may use:
 `[wp_pear-debug foo="bar" foo1="bar2"]`

11. Internationalization support has been added with version 1.4.6. A rough spanish translation has been provided in the hope that someone can take it and create a better translation.

== Installation ==

Automatic Install:

1. Use the wordpress online installer 
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set the appropriate option under settings->Debugger admin menu
4. Please note that the above options must be saved at least once before the debugger can appear
5. The following step is optional but recommended. Add the following code to wp-config.php `define('SAVEQUERIES',true);`

Manual Upload:

File list

* lang (language files)
* lib (containing debugger and helper libraries)
* wp-pear-debug.php (Main plugin file)

1. UPload folder `wp-pear-debug`  to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set the appropriate option under settings->Debugger admin menu
4. Please note that the above options must be saved at least once before the debugger can appear
5. The following step is optional but recommended. Add the following code to wp-config.php `define('SAVEQUERIES',true);`


== Frequently Asked Questions ==

= Who is this for =

I imagine this plugin will be most valuable to wordpress developers 
It will also be good for people who are seeing errors on their site.
This plugin also measures script execution time so It is also good for people who want to measure the performance of
their entire site or a specific part of their script.

= Can I see the library in action? = 
You can see the pear demo [here:](http://www.php-debug.com/www/PHP_Debug_HTML_Div_test.php) Pear Demo.
 

== Interesting Points ==
This plugin requires no 3rd party dependencies such as jQuery. 


