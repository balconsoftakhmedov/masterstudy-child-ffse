<?php

function remove_class_action( $action, $class, $method ) {
	global $wp_filter;
	if ( isset( $wp_filter[ $action ] ) ) {
		$len = strlen( $method );
		foreach ( $wp_filter[ $action ] as $pri => $actions ) {
			foreach ( $actions as $name => $def ) {
				if ( substr( $name, - $len ) == $method ) {
					if ( is_array( $def['function'] ) ) {
						if ( get_class( $def['function'][0] ) == $class ) {
							if ( is_object( $wp_filter[ $action ] ) && isset( $wp_filter[ $action ]->callbacks ) ) {
								unset( $wp_filter[ $action ]->callbacks[ $pri ][ $name ] );
							} else {
								unset( $wp_filter[ $action ][ $pri ][ $name ] );
							}
						}
					}
				}
			}
		}
	}
}

function stm_get_category_courses( $course_id ) {

	$taxonomy = 'stm_lms_course_taxonomy';
	$terms    = wp_get_post_terms( $course_id, $taxonomy );
	$term_id  = 0;
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$term    = $terms[0];
		$term_id = $term->term_id;
	}
	$args           = array(
		'post_type' => 'stm-courses',
		'tax_query' => array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		),
	);
	$id             = [];
	$q              = new WP_Query( $args );

	if ( $q->have_posts() ) :
		while ( $q->have_posts() ) :
			global $post;
			$q->the_post();
			$id[] = $post;
		endwhile;
	endif;

	return $id;
}

function stm_lms_register_script_child( $script, $deps = array(), $footer = false, $inline_scripts = '' ) {
	if ( ! stm_lms_is_masterstudy_theme() ) {
		wp_enqueue_script( 'jquery' );
	}
	$handle = "stm-lms-{$script}";
	wp_enqueue_script( $handle, get_stylesheet_directory_uri() . '/assets/js/' . $script . '.js', $deps, stm_lms_custom_styles_v(), $footer );
	if ( ! empty( $inline_scripts ) ) {
		wp_add_inline_script( $handle, $inline_scripts );
	}
}