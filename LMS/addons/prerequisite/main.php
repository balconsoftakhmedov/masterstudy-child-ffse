<?php

class STM_LMS_Prerequisites_Child {

	function __construct() {
		if ( class_exists( 'STM_LMS_Prerequisites' ) ) {
			remove_class_action( 'stm_lms_pro_show_button', 'STM_LMS_Prerequisites', 'is_prerequisite' );
			remove_class_action( 'stm_lms_pro_instead_buttons', 'STM_LMS_Prerequisites', 'instead_buy_buttons' );
			add_filter( 'stm_lms_pro_show_button', array( $this, 'is_prerequisite' ), 100, 2 );
			add_action( 'stm_lms_pro_instead_buttons', array( $this, 'instead_buy_buttons' ) );
		}
	}

	public static function get_prereq_courses( $course_id ) {
		$preq_course = get_post_meta( $course_id, 'prerequisites', true );
		$user          = STM_LMS_User::get_current_user();
		$user_id       = ( ! empty( $user['id'] ) ) ? $user['id'] : 0;
		//	update_user_meta( $user_id, 'prerequisites_' . $course_id, ''  );
		$preq_course .= (get_user_meta( $user_id, 'prerequisites_' . $course_id, true ))? ','.get_user_meta( $user_id, 'prerequisites_' . $course_id, true ):'';

		return $preq_course;
	}

	public static function prereq_passed( $prereq, $course_id ) {
		if ( empty( $prereq ) ) {
			return true;
		}
		$prereq        = explode( ',', $prereq );
		$passing_value = get_post_meta( $course_id, 'prerequisite_passing_level', true );
		$passing_value = ( ! empty( $passing_value ) ) ? $passing_value : 0;
		$user          = STM_LMS_User::get_current_user();
		$user_id       = ( ! empty( $user['id'] ) ) ? $user['id'] : 0;
		foreach ( $prereq as $course ) {
			$user_course = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( $user_id, $course, array( 'progress_percent' ) ) );
			/*Student do not have this course*/
			if ( empty( $user_course ) ) {
				return false;
			}
			$progress = ( ! empty( $user_course['progress_percent'] ) ) ? $user_course['progress_percent'] : 0;
			if ( $progress < $passing_value ) {
				return false;
			}
		}

		return true;
	}

	public static function is_prerequisite( $show, $course_id ) {
		$prereq = self::get_prereq_courses( $course_id );
		$passed = self::prereq_passed( $prereq, $course_id );

		return $passed;
	}

	function instead_buy_buttons( $course_id ) {
		if ( ! $this->is_prerequisite( true, $course_id ) ) {
			$prereq       = explode( ',', $this->get_prereq_courses( $course_id ) );
			$user         = STM_LMS_User::get_current_user();
			$user_id      = ( ! empty( $user['id'] ) ) ? $user['id'] : 0;
			$user_courses = array();
			foreach ( $prereq as $course ) {
				$user_course    = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( $user_id, $course, array( 'course_id', 'progress_percent' ) ) );
				$user_courses[] = ( ! empty( $user_course ) )
					? $user_course
					: array(
						'course_id'        => $course,
						'progress_percent' => 0
					);
			}
			STM_LMS_Templates::show_lms_template( 'global/prerequisite', array( 'courses' => $user_courses ) );
		}
	}

}

function my_init_callback() {

	new STM_LMS_Prerequisites_Child();
}

// add the callback function to the init hook
add_action( 'init', 'my_init_callback' );

