<?php
/**
 * The file that defines helper function.
 *
 * Helper functions for usage from outside of this plugin.
 *
 * @link       https://www.openwebconcept.nl
 *
 * @package    Klantinteractie_Plugin
 */

if ( ! function_exists( 'klantinteractie_get_berichten' ) ) {
	/**
	 * Get the messages from the klantinteractie API.
	 *
	 * @param string $bsn Optional: The BSN to get the messages for.
	 *
	 * @return array
	 */
	function klantinteractie_get_berichten( $bsn = null ) {
		return \Klantinteractie_Plugin\API\Klantinteractie_API::get_instance()->get_berichten( $bsn );
	}
}

if ( ! function_exists( 'klantinteractie_get_klantcontacten' ) ) {
	/**
	 * Get the contact moments from the klantinteractie API.
	 *
	 * @param string $bsn Optional: The BSN to get the contact moments for.
	 *
	 * @return array
	 */
	function klantinteractie_get_klantcontactmomenten( $bsn = null ) {
		return \Klantinteractie_Plugin\API\Klantinteractie_API::get_instance()->get_klantcontacten( $bsn );
	}
}
