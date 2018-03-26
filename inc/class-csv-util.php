<?php

namespace OpenClub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( 'class-factory.php' );

class CSV_Util {

	public static function get_csv_post( $post_id ) {

		if( !is_numeric( $post_id ) ) {
			throw new \Exception (
				sprintf( '$post_id %s must be a numeric value.', $post_id )
			);
		}

		/** @var $post \WP_Post  */
		$post = get_post( $post_id );

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
			throw new \Exception (
				sprintf( '$post_id %d does not return a post object.', $post_id )
			);
		}

		if ( $post->post_type != 'openclub-csv' ) {
			throw new \Exception (
				sprintf( '$post_id %d does not return a post object of post type openclub-csv.', $post_id )
			);
		}

		if ( $post->status == 'auto-draft' ) {
			throw new \Exception (
				sprintf( '$post_id %d returns an openclub-csv post type auto-draft.', $post_id )
			);
		}

		if ( $fields = get_post_meta( $post_id, 'fields', true ) ) {
			$post->field_settings = parse_ini_string( $fields, true );
		} else {
			throw new \Exception (
				sprintf( '$post_id %d does not have a fields post meta set.', $post_id )
			);
		}
		return $post;

	}


	/**
	 * @param $post_id
	 * @param null $filter
	 *
	 * @return Data_Set
	 * @throws \Exception
	 */
	public static function get_data_set( $post_id, $filter = null, $group_on = null ) {

		$parser = Factory::get_parser();

		$parser->init( CSV_Util::get_csv_post( $post_id ) );

		$filter = ( $filter == null ) ? Factory::get_null_filter() : $filter;

		return  $parser->get_data( $filter );

	}


	public static function get_csv_table_row( DTO $line_data, Field_Validator_Manager $field_Validator_Manager ) {

		$class = '';
		if( $line_data->has_validation_error()){
			$class = 'openclub_csv_error';
		}

		$out = '<tr class="'.$class.'">';

		foreach( $line_data->get_data() as $key => $value ) {

			if( $field_Validator_Manager->get_validator( $key )->displayField() ) {
				$out .= '<td>'.$value.'</td>';
			}
		}
		$out .= "</tr>\n";

		return $out;

	}

	public static function get_csv_table_header( Field_Validator_Manager $field_Validator_Manager )  {
		return '<tr><th>' .
		       implode('</th><th>', $field_Validator_Manager->getDisplayFields() ) .
		       "</tr>\n";
	}

	public static function get_formatted_csv_line_error_message( $error_message ){
		return '<span class="openclub_csv_error">'.$error_message.'</span><br/>';
	}

}