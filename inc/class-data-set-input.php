<?php

namespace OpenClub;

require_once( 'class-csv-util.php' );

class Data_Set_Input {

	/**
	 * @var $post \WP_Post
	 */
	private $post;

	/**
	 * @var
	 */
	private $filter;

	/**
	 * @var $post_id int
	 */
	private $post_id;

	/**
	 * @var string
	 */
	private $group_by_field;

	/**
	 * @var string
	 */
	private $overridden_fields;

	/**
	 * @param $post_id int
	 *
	 * @throws \Exception
	 */
	public function __construct( $post_id ) {

		if ( ! is_numeric( $post_id ) ) {
			throw new \Exception( '$post_id is not numeric' );
		}

		$this->post_id            = $post_id;
		$this->post               = CSV_Util::get_csv_post( $post_id );
		$this->raw_display_fields = $this->post->field_settings;
	}

	/**
	 * @param Filter $filter
	 */
	public function set_filter( Filter $filter ) {

		$this->filter = $filter;
	}

	/**
	 * @return \WP_Post
	 */
	public function get_post() {

		return $this->post;
	}

	/**
	 * @return Filter|Null_Filter
	 */
	public function get_filter() {

		if ( empty( $this->filter ) ) {
			return Factory::get_null_filter();
		}

		return $this->filter;
	}

	/**
	 * @return int
	 */
	public function get_post_id() {
		return $this->post_id;
	}

	public function set_group_by_field( $group_by_field ) {

		if ( empty( trim( $group_by_field ) ) ) {
			throw new \Exception( '$group_by_field cannot be empty' );
		}

		$this->group_by_field = $group_by_field;
	}

	public function get_group_by_field() {

		return $this->group_by_field;
	}


	public function has_group_by_field() {
		return $this->group_by_field ? true : false;
	}

	public function set_fields_override( $overridden_fields ) {

		if( empty($overridden_fields )) return false;

		$fields = explode( ',' , $overridden_fields );

		foreach( $fields as $field_name ) {
			if( !array_key_exists( $field_name, $this->raw_display_fields ) ) {
				throw new \Exception('Field override: ' . $field_name . ' is invalid. Check the shortcode' );
			}
		}
		$this->overridden_fields = $overridden_fields;
	}

	/**
	 * @return array
	 */
	public function get_overridden_fields() {
		return explode( ',' , $this->overridden_fields );
	}

	public function has_overridden_fields() {
		return !empty( $this->overridden_fields );
	}
}