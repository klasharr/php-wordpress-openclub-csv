<?php

namespace OpenClub;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( OPENCLUB_CSV_PLUGIN_DIR . '/inc/interface-filter.php' );

class Null_Filter implements Filter {

	/**
	 * @param DTO $dto
	 *
	 * @return bool
	 */
	public function is_filtered_out( DTO $dto ) {

		return false;

	}

}