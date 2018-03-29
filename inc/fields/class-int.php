<?php

namespace OpenClub\Fields;

use OpenClub\Data_Set_Input;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once( 'class-base.php' );
require_once( 'interface-field-validator.php' );

class IntField extends Base_Field implements Field_Validator {

	public function __construct( $data, Data_Set_Input $input ) {
		parent::__construct( $data, $input );
	}

	public function validate( $value ) {
		parent::_validate( $value );

		return $this->is_valid_int( $value );
	}

	private function is_valid_int( $value ) {

		return is_numeric( $value ) ? true : false;
	}

}