<?php

/**
 * Display the page where the slugs could be regenerated or replaced
 */
class Permalink_Manager_Debug {

	public function __construct() {
		add_filter( 'permalink_manager_sections', array( $this, 'add_debug_section' ), 4 );
	}

	/**
	 * Add a new section to the Permalink Manager UI
	 *
	 * @param array $admin_sections
	 *
	 * @return array
	 */
	public function add_debug_section( $admin_sections ) {
		$admin_sections['debug'] = array(
			'name'     => __( 'Debug', 'permalink-manager' ),
			'function' => array( 'class' => 'Permalink_Manager_Debug', 'method' => 'output' )
		);

		return $admin_sections;
	}

	/**
	 * Get a URL pointing to the "Debug" tab in Permalink Manager UI
	 *
	 * @param string $field
	 *
	 * @return string
	 */
	public function get_remove_settings_url( $field = '' ) {
		return add_query_arg( array(
			'section'                           => 'debug',
			'remove-permalink-manager-settings' => $field,
			'permalink-manager-nonce'           => wp_create_nonce( 'permalink-manager' )
		), Permalink_Manager_Admin_Functions::get_admin_url() );
	}

	/**
	 * Define and display HTML output of a new section with the "Debug" data
	 *
	 * @return string
	 */
	public function output() {
		global $permalink_manager_options, $permalink_manager_permastructs, $permalink_manager_redirects, $permalink_manager_external_redirects;

		// Get the permalinks array & count permalinks and calculate the size of array
		$custom_permalinks = Permalink_Manager_URI_Functions::get_all_uris();
		list ( $custom_permalinks_size, $custom_permalinks_count ) = Permalink_Manager_URI_Functions::get_all_uris( true );

		if ( $custom_permalinks_count >= 1 ) {
			$custom_permalinks_size  = ( is_numeric( ( $custom_permalinks_size ) ) ) ? round( $custom_permalinks_size / 1024, 2 ) . __( 'kb', 'permalink-manager' ) : '-';
			/* translators: 1: Custom permalinks count, 2: Custom permalinks array size */
			$custom_permalinks_stats = sprintf( __( 'Custom permalinks count: <strong>%1$s</strong> | Custom permalinks array size in DB: <strong>%2$s</strong>', 'permalink-manager' ), esc_html( $custom_permalinks_count ), esc_html( $custom_permalinks_size ) );
		} else {
			$custom_permalinks_stats = __( 'The custom permalinks array has not been stored in the database yet.', 'permalink-manager' );
		}

		$sections_and_fields = apply_filters( 'permalink_manager_debug_fields', array(
			'debug-data' => array(
				'section_name' => __( 'Debug data', 'permalink-manager' ),
				'fields'       => array(
					'uris'               => array(
						'type'        => 'textarea',
						'description' => sprintf( '%s<br />%s<br /><strong><a class="pm-confirm-action" href="%s">%s</a></strong>', __( 'List of the URIs generated by this plugin.', 'permalink-manager' ), $custom_permalinks_stats, $this->get_remove_settings_url( 'uris' ), __( 'Remove all custom permalinks', 'permalink-manager' ) ),
						'label'       => __( 'Array with URIs', 'permalink-manager' ),
						'input_class' => 'short-textarea widefat',
						'value'       => ( $custom_permalinks ) ? print_r( $custom_permalinks, true ) : ''
					),
					'custom-redirects'   => array(
						'type'        => 'textarea',
						'description' => sprintf( '%s<br /><strong><a class="pm-confirm-action" href="%s">%s</a></strong>', __( 'List of custom redirects set-up by this plugin.', 'permalink-manager' ), $this->get_remove_settings_url( 'redirects' ), __( 'Remove all custom redirects', 'permalink-manager' ) ),
						'label'       => __( 'Array with redirects', 'permalink-manager' ),
						'input_class' => 'short-textarea widefat',
						'value'       => ( $permalink_manager_redirects ) ? print_r( $permalink_manager_redirects, true ) : ''
					),
					'external-redirects' => array(
						'type'        => 'textarea',
						'description' => sprintf( '%s<br /><strong><a class="pm-confirm-action" href="%s">%s</a></strong>', __( 'List of external redirects set-up by this plugin.', 'permalink-manager' ), $this->get_remove_settings_url( 'external-redirects' ), __( 'Remove all external redirects', 'permalink-manager' ) ),
						'label'       => __( 'Array with external redirects', 'permalink-manager' ),
						'input_class' => 'short-textarea widefat',
						'value'       => ( $permalink_manager_external_redirects ) ? print_r( array_filter( $permalink_manager_external_redirects ), true ) : ''
					),
					'permastructs'       => array(
						'type'        => 'textarea',
						'description' => sprintf( '%s<br /><strong><a class="pm-confirm-action" href="%s">%s</a></strong>', __( 'List of permastructures set-up by this plugin.', 'permalink-manager' ), $this->get_remove_settings_url( 'permastructs' ), __( 'Remove all permastructures settings', 'permalink-manager' ) ),
						'label'       => __( 'Array with permastructures', 'permalink-manager' ),
						'input_class' => 'short-textarea widefat',
						'value'       => ( $permalink_manager_permastructs ) ? print_r( $permalink_manager_permastructs, true ) : ''
					),
					'settings'           => array(
						'type'        => 'textarea',
						'description' => sprintf( '%s<br /><strong><a class="pm-confirm-action" href="%s">%s</a></strong>', __( 'List of plugin settings.', 'permalink-manager' ), $this->get_remove_settings_url( 'settings' ), __( 'Remove all plugin settings', 'permalink-manager' ) ),
						'label'       => __( 'Array with settings used in this plugin.', 'permalink-manager' ),
						'input_class' => 'short-textarea widefat',
						'value'       => print_r( $permalink_manager_options, true )
					)
				)
			)
		) );

		// Now get the HTML output
		$output = '';
		foreach ( $sections_and_fields as $section_id => $section ) {
			$output .= ( isset( $section['section_name'] ) ) ? "<h3>{$section['section_name']}</h3>" : "";
			$output .= ( isset( $section['description'] ) ) ? "<p class=\"description\">{$section['description']}</p>" : "";
			$output .= "<table class=\"form-table fixed-table\">";

			// Loop through all fields assigned to this section
			foreach ( $section['fields'] as $field_id => $field ) {
				$field_name         = "{$section_id}[$field_id]";
				$field['container'] = 'row';

				$output .= Permalink_Manager_UI_Elements::generate_option_field( $field_name, $field );
			}

			// End the section
			$output .= "</table>";

		}

		return $output;
	}

}
