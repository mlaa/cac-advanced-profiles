<?php

class CACAP_Widget_Positions extends CACAP_Widget {
	public $colleges = array(
		'*Non-CUNY',
		'Baruch College',
		'Borough of Manhattan Community College',
		'Bronx Community College',
		'Brooklyn College',
		'City College',
		'College of Staten Island',
		'CUNY Central',
		'CUNY Graduate Center',
		'CUNY Graduate School of Journalism',
		'CUNY School of Law',
		'Hostos Community College',
		'Hunter College',
		'Medgar Evers College',
		'Guttman Community College',
		'NYC College of Technology',
		'Queens College',
		'Queensborough Community College',
		'School of Professional Studies',
		'Sophie Davis School of Biomedical Education',
		'Teacher Academy',
		'York College',
	);

	public function __construct() {
		static $setup;

		if ( empty( $setup ) ) {
			$this->register_taxonomies();
		}

		parent::init( array(
			'name' => __( 'Positions', 'cacap' ),
			'slug' => 'positions',
		) );

		$setup = true;
	}

	/**
	 * Saves instance of Positions widget for user
	 *
	 * @since 1.0
	 */
	public function save_instance_for_user( $args = array() ) {
		$r = wp_parse_args( $args, array(
			'key' => '',
			'user_id' => 0,
			'title' => '',
			'content' => '',
		) );

		// @todo better error reporting
		if ( ! $r['user_id'] ) {
			return false;
		}

		$submitted_positions = ! empty( $r['content'] ) ? $r['content'] : array();
		$new_positions = array();

		// Parse the submitted positions and fetch the term ids
		// This will format the $new_positions array, which will be
		// stored as usermeta
		foreach ( $submitted_positions as $submitted_position ) {
			$new_position = array();

			foreach ( array( 'college', 'department', 'title' ) as $type ) {
				$new_position[ $type ] = $this->get_term_for_position( $submitted_position, $type );
			}

			$new_positions[] = $new_position;
		}

		// Now that we have fetched the term ids, we'll save them to
		// the user objects. $new_term_ids is an array of term ids,
		// re-sorted by college/dept/title, rather than by position.
		// This makes it easier to reset the object terms for each
		$new_term_ids = array(
			'college' => array(),
			'department' => array(),
			'title' => array(),
		);

		foreach ( $new_positions as $new_position ) {
			foreach ( $new_position as $np_type => $np_term_id ) {
				$new_term_ids[ $np_type ][] = $np_term_id;
			}
		}

		foreach ( $new_term_ids as $nti_type => $nti_term_ids ) {
			wp_set_object_terms( $r['user_id'], $nti_term_ids, 'cacap_position_' . $nti_type );
		}

		// Save to usermeta. This is what's used to generate profile
		// output
		bp_update_user_meta( $r['user_id'], 'cacap_positions', $new_positions );

		// @todo Store as flat text. This will make display more
		// efficient, but introduces complications for when a tax term
		// is changed and everything needs to be regenerated
		return CACAP_Widget_Instance::format_instance( array(
			'user_id' => $r['user_id'],
			'key' => 'cacap_positions',
			'widget_type' => $this->slug,
		) );
	}

	/**
	 * Format:
	 *
	 * array(
	 *   array(
	 *     'college' => 'College 1',
	 *     'department' => 'Department at College 1',
	 *     'title' => 'Title 1',
	 *   ),
	 *   array(
	 *     'college' => 'College 2',
	 *     'title' => 'Title 2',
	 *   ),
	 * );
	 */
	public function get_user_positions( $user_id ) {
		$positions = bp_get_user_meta( $user_id, 'cacap_positions', true );

		if ( '' == $positions || ! is_array( $positions ) ) {
			$positions = array();
		}

		// Convert term ids to strings
		$formatted_positions = array();
		foreach ( $positions as $position ) {
			$formatted_positions[] = $this->get_text_for_position( $position );
		}

		return $formatted_positions;
	}

	/**
	 * For a submitted position and type, get a term ID
	 *
	 * Will create one if not found
	 *
	 * @param array $position Should contain 'college', 'deparment',
	 *   and 'title'. Each should have a string value - we'll convert to id
	 * @param string $type 'college', 'department', or 'title'. The
	 *   position term type we're looking for
	 * @return int Term id
	 */
	public function get_term_for_position( $position, $type ) {
		$term_id = null;

		if ( ! empty( $position[ $type ] ) ) {
			$value = $position[ $type];
			$tax   = 'cacap_position_' . $type;

			$term = get_term_by( 'name', $value, $tax );

			// No term found. Create one
			if ( empty( $term ) ) {
				$term = wp_insert_term( $value, $tax );

				if ( ! empty( $term ) ) {
					$term_id = $term['term_id'];
				}
			} else {
				$term_id = $term->term_id;
			}
		}

		return intval( $term_id );
	}

	/**
	 * Convert a position array of term IDs to array of strings
	 *
	 * @param array $position Term ids for college, dept, title
	 * @param array $formatted_position Term names
	 */
	public function get_text_for_position( $position ) {
		$formatted_position = array();
		foreach ( (array) $position as $type => $term_id ) {
			$term = get_term( $term_id, 'cacap_position_' . $type );
			if ( isset( $term->name ) ) {
				$formatted_position[ $type ] = $term->name;
			}
		}

		return $formatted_position;
	}

	public function get_instance_for_user( $args = array() ) {
		$r = wp_parse_args( $args, array(
			'user_id' => 0,
		) );

		$positions = get_user_meta( $r['user_id'], 'cacap_positions' );

		if ( '' == $positions || ! is_array( $positions ) ) {
			$positions = array();
		}

		return $positions;
		return array(
			'college'    => wp_get_object_terms( $r['user_id'], 'cacap_position_college' ),
			'department' => wp_get_object_terms( $r['user_id'], 'cacap_position_department' ),
			'title'      => get_user_meta( $r['user_id'], 'cacap_position_title' ),
		);
	}

	public function get_display_value_from_value( $value ) {
		return $value['content'];
	}

	public function edit_content_markup( $value, $key ) {
		$markup = '';

		// not enough - need a blank one for a prototype
		if ( ! empty( $value ) && is_array( $value ) ) {
			foreach ( $value as $position ) {
				$markup  = '<ul>';

				$markup .=   '<li>';
				$markup .=     '<label for="' . esc_attr( $key ) . '_college">' . __( 'College', 'cacap' ) . '</label>';
				$markup .=     '<select name="' . esc_attr( $key ) . '[content][][college]" id="' . esc_attr( $key ) . '_college">';

				foreach ( $this->colleges as $college ) {
					$markup .= '<option value="' . esc_attr( $college ) . '" ' . selected( $college, $current_college, false ) . '>' . esc_attr( $college ) . '</option>';
				}

				$markup .=     '</select>';
				$markup .=   '</li>';

				$markup .=   '<li>';
				$markup .=     '<label for="' . esc_attr( $key ) . '_department">' . __( 'Department', 'cacap' ) . '</label>';
				$markup .=     '<input class="cacap-edit-input" name="' . esc_attr( $key ) . '[content][][department]" id="' . esc_attr( $key ) . '_department" val="' . esc_attr( $content ) . '" />';
				$markup .=   '</li>';

				$markup .=   '<li>';
				$markup .=     '<label for="' . esc_attr( $key ) . '_title">' . __( 'Title', 'cacap' ) . '</label>';
				$markup .=     '<input class="cacap-edit-input" name="' . esc_attr( $key ) . '[content][][title]" id="' . esc_attr( $key ) . '_title" val="' . esc_attr( $content ) . '" />';
				$markup .=   '</li>';
				$markup .= '</ul>';
			}
		}

		return $markup;
	}

	public function register_taxonomies() {
		register_taxonomy( 'cacap_position_college', 'user', array(
			'hierarchical' => false,
			'show_ui' => true,
		) );
		register_taxonomy( 'cacap_position_department', 'user', array(
			'hierarchical' => false,
			'show_ui' => true,
		) );
		register_taxonomy( 'cacap_position_title', 'user', array(
			'hierarchical' => false,
			'show_ui' => true,
		) );
	}

	public static function taxonomy_setup() {

	}
}