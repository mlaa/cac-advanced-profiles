<?php

class CACAP_Widget_Academic_Interests extends CACAP_Widget {
	public function __construct() {
		parent::init( array(
			'name' => __( 'Academic Interests', 'cacap' ),
			'slug' => 'academic-interests',
			'content_type' => 'interests',
			'placeholder' => 'Enter your interests.',
		) );
	}

	/**
	 * Save widget instance for a given user.
	 *
	 * Overrides the parent method. Put away user meta and object terms.
	 *
	 * @param array $args
	 */
	public function save_instance_for_user( $args = array() ) {
		$r = wp_parse_args( $args, array(
			'key' => '',
			'user_id' => 0,
			'title' => $this->name,
			'content' => '',
			'visibility' => '',
			'position' => '',
		) );

		if ( ! $r['user_id'] ) {
			return false;
		}

		if ( ! $r['title'] ) {
			$r['title'] = $this->name;
		}

		$term_ids = array();

		// If array add any new keywords.
		if ( is_array( $r['content'] ) ) {
			foreach ( $r['content'] as $term_id ) {
				$term_key = term_exists( $term_id, 'mla_academic_interests' );
				if ( empty( $term_key ) ) {
					$term_key = wp_insert_term( sanitize_text_field( $term_id ), 'mla_academic_interests' );
				}
				if ( ! is_wp_error( $term_key ) ) {
					$term_ids[] = intval( $term_key['term_id'] );
				} else {
					error_log( '*****CAC Academic Interests Error - bad tag*****' . var_export( $term_key, true ) );
				}
			}
			$r['content'] = implode( ', ', $r['content'] );
		}

		// Set object terms for tags.
		$term_taxonomy_ids = wp_set_object_terms( $r['user_id'], $term_ids, 'mla_academic_interests' );

		// Set user meta for theme query.
		delete_user_meta( $r['user_id'], 'academic_interests' );
		foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {
			add_user_meta( $r['user_id'], 'academic_interests', $term_taxonomy_id, $unique = false );
		}

		// Sanitize
		$r['title'] = strip_tags( $r['title'] );

		$meta_value = array(
			'title' => $r['title'],
			'content' => '',
			'visibility' => $r['visibility'],
		);

		$meta_key = 'cacap_widget_instance_' . sanitize_title_with_dashes( $r['title'] );

		update_user_meta( $r['user_id'], $meta_key, $meta_value );

		$field_id = xprofile_get_field_id_from_name( $r['title'] );
		$vis_out = xprofile_set_field_visibility_level( $field_id, $r['user_id'], $r['visibility'] );
		return CACAP_Widget_Instance::format_instance( array(
			'user_id' => $r['user_id'],
			'key' => $r['title'],
			'widget_type' => $this->slug,
			'position' => $r['position'],
		) );
	}

	/**
	 * Get widget instance for a given user.
	 *
	 * Overrides the parent method. Get values from user meta.
	 *
	 * @param array $args
	 */
	public function get_instance_for_user( $args = array() ) {
		$r = wp_parse_args( $args, array(
			'user_id' => 0,
			'key' => null,

		) );

		$settings = get_user_meta( absint( $r['user_id'] ), $r['key'], true );

		$user_term_taxonomy_ids = get_user_meta( $r['user_id'], 'academic_interests', $single = false );
		$term_taxonomy_id_list = array();
		foreach ( $user_term_taxonomy_ids as $term_taxonomy_id ) {
			$term_taxonomy_id_list[] = $term_taxonomy_id;
		}
		return $term_taxonomy_id_list;
	}


	/**
	 * Linkify array of academic interests.
	 *
	 * Overrides the parent method.
	 *
	 * @param array $value
	 */
	public function display_content_markup( $value ) {

		$linked_values = array();

		foreach ( $value as $term_taxonomy_id ) {
			$term = get_term_by( 'term_taxonomy_id', $term_taxonomy_id, 'mla_academic_interests' );
			$search_url = add_query_arg( array( 'academic_interests' => urlencode( $term_taxonomy_id ) ), bp_get_members_directory_permalink() );
			$linked_values[] = '<a href="' . esc_url( $search_url ) . '" rel="nofollow">' . $term->name . '</a>';
		}
		return implode( ', ', $linked_values );
	}

	/**
	 * Edit academic interests.
	 *
	 * Overrides the parent method. Pick from existing taxonomy or add new tag.
	 *
	 * @param array $value
	 * @param array $key
	 */
	public function edit_content_markup( $value, $key ) {

		if ( $this->allow_edit ) {
			$html = '<span class="description">Enter your academic interests, e.g., &quot;Japanese literature, Creative writing, Environmental humanities&quot;. Add new interests if needed.</span><br />';
			$html .= '<select name="' . $key . '[content][]" class="js-basic-multiple-tags ' . $this->content_type . '" multiple="multiple" data-placeholder="' . $this->placeholder . '">';
			$interest_list = Mla_Academic_Interests::mla_academic_interests_list();
			$input_interest_list = array();
			if ( 'academic-interests' === $key ) {
				foreach ( $value as $term_taxonomy_id ) {
					$term = get_term_by( 'term_taxonomy_id', $term_taxonomy_id, 'mla_academic_interests' );
					$input_interest_list[] = $term->name;
				}
			}
			foreach ( $interest_list as $interest_key => $interest_value ) {
				$html .= sprintf('			<option class="level-1" %1$s value="%2$s">%3$s</option>' . "\n",
					( in_array( $interest_key, $input_interest_list ) ) ? 'selected="selected"' : '',
					$interest_key,
					$interest_value
				);
			}
			$html .= '</select>';
			return $html;
		} else {
			return $this->display_content_markup( $value );
		}

	}
}
