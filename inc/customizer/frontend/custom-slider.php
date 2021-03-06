<?php 
/***
 * Custom Slider Parameters
 *
 * Passing Variables from custom Theme Options to the javascript files
 * of featured content slider. 
 *
 */

// Passing Variables to Featured Content Slider ( js/slider.js)
add_action( 'wp_enqueue_scripts', 'smartline_custom_jscript_slider' );

if ( ! function_exists( 'smartline_custom_jscript_slider' ) ) :

function smartline_custom_jscript_slider() { 
	
	// Get Theme Options from Database
	$theme_options = smartline_theme_options();
	
	// Set Parameters array
	$params = array();
	
	// Define Slider Animation
	$params['animation'] = $theme_options['slider_animation'];
	
	// Define Slider Speed
	$params['speed'] = $theme_options['slider_speed'];
	
	// Passing Parameters to Javascript
	wp_localize_script( 'smartline-lite-jquery-frontpage_slider', 'smartline_slider_params', $params );
}

endif;