<?php

/* 
 * Loads the Options Panel
 *
 * If you're loading from a child theme use stylesheet_directory
 * instead of template_directory
 */
 
if ( !function_exists( 'optionsframework_init' ) ) {
	define( 'OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/inc/' );
	require_once dirname( __FILE__ ) . '/inc/options-framework.php';
}


/**
 * Testing add an option from outside of the core options. I have added 2 do_action() calls. One before all the example
 * options are set and one after. You can add as many do_action() calls as you need in your default theme options panel
 * and then you'll be able to call a function like the one below to add options to those sections (or indeed add entire 
 * sections)
 */

function external_options_test()
{

	global $options;

	$options[] = array(
		'name' => __('External', 'options_framework_theme'),
		'type' => 'heading'
	);

	$options[] = array(
		'name' => __('External Input Text', 'options_framework_theme'),
		'desc' => __('A text input field.', 'options_framework_theme'),
		'id' => 'external_example_text',
		'std' => 'External Value',
		'type' => 'text'
	);

	//wp_die( "<pre>".print_r( $options, true )."</pre>" );

}/* external_options_test() */

add_action( 'of_set_options_before_defaults', 'external_options_test', 10, 1 );