<?php

namespace OpenClub;


class CSV_Display {

	/**
	 * @param array $row
	 *  Array(
	 *      [value] =>
	 *      [formatted_value] =>
	 *      [display_default] => 1
	 *  )
	 * @param bool $formatted_value
	 *
	 * @see Output_Data->get_row_data()
	 *
	 * @return string|void
	 */
	public static function get_csv_row( array $row, $formatted_value = true ) {

		if ( empty( $row['data'] ) ) {
			return __( 'empty', 'openclub_csv' );
		}

		$out = array();
		foreach ( $row['data'] as $field_name => $values ) {
			if ( $formatted_value ) {
				$out[] = $values['formatted_value'];
			} else {
				$out[] = $values['value'];
			}
		}

		return implode( ',', $out );

	}


	/**
	 * Default CSV data display function.
	 *
	 * @param $config
	 * @param null $plugin_directory_path e.g.
	 *
	 *      /srv/www/wordpress-develop/public_html/src/wp-content/plugins/ssc/
	 *
	 * @return string
	 */
	public static function get_html( $config, $plugin_directory_path = null ) {

		$out = '';

		try {

			$config = self::set_default_display_template( $config );

			/**
			 * @var $input \OpenClub\Data_Set_Input
			 */
			$input = \OpenClub\Factory::get_data_input_object( $config );

			/**
			 * @var $output \OpenClub\Output_Data
			 */
			$output_data = \OpenClub\Factory::get_output_data( $input );

			/**
			 * Allow late altering of the data in plugins
			 */
			apply_filters( 'openclub_csv_display_data', $output_data, $input );

			if ( ! empty( $output_data ) && $output_data->exists() ) {

				// $config[ 'display' ] is the template name without PHP extension. See examples in:
				// wp-content/plugins/openclub_csv/templates

				$out .= self::template_output(
					array(
						'output_data' => $output_data,
						'config'      => $config
					),
					$config['display'],
					$plugin_directory_path  // defaults to OPENCLUB_CSV_PLUGIN_DIR
				);

			} else {
				$out .= __( 'No data', 'openclub_csv' );
			}

		} catch ( \Exception $e ) {
			$out .= '<p class="openclub_csv_error">' . __( 'Error: ', 'openclub_csv' ) . ': ' . $e->getMessage() . '</p>';
		}

		return $out;
	}

	/**
	 * @param array $config
	 *
	 * post_id              - the post of type openclub-csv
	 * error_messages       - "yes" / "no"
	 * error_lines          -  "yes" / "no"
	 * future_events_only   - "yes" if grouping on a date field display events only in the future
	 * display              - the template name with .php ending. Placed in the theme or plugin templates directory.
	 *                          Theme templates take precedence over plugin templates
	 * fields               - choose which fields to display and order, overrides fields settings
	 * group_by_field       - group on a particular field, the template must be able to work with that
	 *
	 * @return array
	 */
	public static function get_config( $config = array() ) {

		$config = array_replace(
			array(
				'post_id'                 => null,
				'error_messages'          => "yes",
				'error_lines'             => "yes",
				'future_events_only'      => null,
				'display'                 => 'table', // default template file table.php
				'fields'                  => null,
				'group_by_field'          => null,
				'context'                 => null,
				'limit'                   => false,
				'filter'                  => null,
				'show_future_past_toggle' => null,
				'display_config'          => false,
			),
			$config
		);

		// @todo, currently this will affect everything.
		//apply_filters( 'openclub_csv_get_config', $config );

		return $config;

	}

	private static function set_default_display_template( $config ) {

		if ( ! empty( $config['group_by_field'] ) && 'table' === $config['display'] ) {
			$config['display'] = 'grouped_list';
		}

		return $config;

	}

	/**
	 * @param array $config
	 * @param string $name
	 * @param null $plugin_directory_path
	 * @param null $template
	 *
	 * @return string|void
	 *
	 * @see wp-content/plugins/openclub-csv/templates/future_past_toggle.php - the default template
	 */
	public static function get_past_future_toggle_links( array $config, $name = 'items', $plugin_directory_path = null, $template = null ) {

		$out = array();

		if ( empty( $config['show_future_past_toggle'] ) ) {
			return;
		}

		if( empty( $config['future_events_only'] )) {
			return;
		}

		if ( $config['future_events_only'] == "yes" ) {

			$data['current'] = sprintf( esc_html__( 'Showing future %s only', 'openclub_csv' ), $name );
			$data['other']   = sprintf( '<a href="%s" rel="nofollow">%s</a>',
				esc_url( get_the_permalink() . add_query_arg( 'feo', '1' ) ),
				esc_html__( 'Show all', 'openclub_csv' )
			);

		} elseif ( $config['future_events_only'] == "no" ) {

			$data['other']   = sprintf( '<a href="%s" rel="nofollow">%s</a>',
				esc_url( get_the_permalink() . add_query_arg( 'feo', '2' ) ),
				sprintf( esc_html__( 'Hide past %s', 'openclub_csv' ), $name )
			);
			$data['current'] = sprintf( esc_html__( 'Showing all %s', 'openclub_csv' ), $name );
		}

		return self::template_output( $data,
			$template == null ? 'future_past_toggle' : $template,
			$plugin_directory_path
		);

	}


	/**
	 * Generic utility function to return template output.
	 *
	 * @param $data
	 *          data to pass to the template file
	 * @param $plugin_directory_path
	 *          e.g. /srv/www/wordpress-develop/public_html/src/wp-content/plugins/ssc/
	 * @param $template_file
	 *          the template name without PHP extension. See examples in: wp-content/plugins/openclub_csv/templates
	 *
	 * @return string
	 */
	public static function template_output( array $data, $template_file = 'default', $plugin_directory_path = null ) {

		/**
		 * @var $o \WP_Error | boolean
		 */
		$o = self::file_exists_check( $template_file, $plugin_directory_path );
		if ( is_wp_error( $o ) ) {
			return '<p class="openclub_csv_error">Error: ' . $o->get_error_message() . '</p>';
		}

		/**
		 * @var $templates \OpenClub\Template_Loader
		 */
		$templates = \OpenClub\Factory::get_template_loader();

		if ( $plugin_directory_path ) {
			$templates->set_plugin_dir_path( $plugin_directory_path );
		}

		$templates->set_template_data( $data );

		return $templates->get_template( $template_file );
	}


	private static function file_exists_check( $template_file, $plugin_directory_path = null ) {

		if ( null === $plugin_directory_path ) {
			$path = OPENCLUB_CSV_PLUGIN_DIR . 'templates/' . $template_file . '.php';
		} else {
			$path = $plugin_directory_path . 'templates/' . $template_file . '.php';
		}

		if ( ! file_exists( $path ) ) {
			$msg = 'Template file ' . $path . ' does not exist.';

			return new \WP_Error( 1020, $msg );
		}
		if( OPENCLUB_CSV_LOG_TEMPLATE_FILES_LOADED ) {
			error_log( $path );
		}
	}

}