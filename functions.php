<?php 
	add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
	function theme_enqueue_styles() {

		
		// Theme main stylesheet
		wp_enqueue_style( 'theme-style', get_stylesheet_uri(), null, STM_THEME_VERSION, 'all' );
		
		// FrontEndCustomizer
		
	}
	include_once 'inc/helpers.php';