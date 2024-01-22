<?php
/**
 * Communicate with the Klantinteractie API.
 *
 * @link       https://www.openwebconcept.nl
 * @since      1.0.0
 *
 * @package    Klantinteractie_Plugin
 * @subpackage Klantinteractie_Plugin/API
 */

namespace Klantinteractie_Plugin\API;

use Firebase\JWT\JWT;
use function Yard\DigiD\Foundation\Helpers\resolve;
use function Yard\DigiD\Foundation\Helpers\decrypt;

/**
 * Communicate with the Klantinteractie API.
 *
 * @package    Klantinteractie_Plugin
 * @subpackage Klantinteractie_Plugin/API
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Klantinteractie_API {

	/**
	 * The singleton instance of this class.
	 *
	 * @access private
	 * @var    Klantinteractie_API|null $instance The singleton instance of this class.
	 */
	private static $instance = null;

	/**
	 * The API domain.
	 *
	 * @access private
	 * @var    string $api_domain The API domain.
	 */
	private $api_domain;

	/**
	 * The API client id.
	 *
	 * @access private
	 * @var    string $client_id The API client id.
	 */
	private $client_id;

	/**
	 * The API client secret.
	 *
	 * @access private
	 * @var    string $client_secret The API client secret.
	 */
	private $client_secret;

	/**
	 * The user data.
	 *
	 * @access private
	 * @var    array $user_data The user data.
	 */
	private $user_data;

	/**
	 * An array containing the available contact fields.
	 *
	 * @var array[] $contact_fields The contact fields.
	 */
	private $contact_fields = [
		'emailadres' => [
			'soortDigitaalAdres' => 'emailadres',
			'omschrijving'       => 'email-adres',
		],
		'telefoon'   => [
			'soortDigitaalAdres' => 'telefoon',
			'omschrijving'       => 'telefoonnummer',
		],
	];

	/**
	 * Initialize the class and set its properties.
	 */
	private function __construct() {
		$this->api_domain    = get_option( 'klantinteractie_plugin_api_domain' );
		$this->client_id     = get_option( 'klantinteractie_plugin_client_id' );
		$this->client_secret = get_option( 'klantinteractie_plugin_client_secret' );
	}

	/**
	 * Get the singleton instance of this class.
	 *
	 * @return Klantinteractie_API The singleton instance of this class.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new Klantinteractie_API();
		}

		return self::$instance;
	}

	/**
	 * Retrieve the user data from the Klantinteractie API.
	 *
	 * @param string $bsn The BSN of the user.
	 *
	 * @return array An array containing the user data.
	 */
	public function get_user_data( $bsn = null ) {

		if ( ! $bsn ) {
			$bsn = decrypt( resolve( 'session' )->getSegment( 'digid' )->get( 'bsn' ) );
		}

		if ( ! $bsn ) {
			return [];
		}

		if ( ! isset( $this->user_data[ $bsn ] ) ) {
			$this->user_data[ $bsn ] = $this->remote_get( 'api/kic/v1/partijen?externeIdentificaties.partijIdentificator.objectId=' . $bsn );

			// Since there is a caching issue with the embedded objects, we have to fetch the data again.
			foreach ( $this->user_data[ $bsn ]['results'] as &$result ) {
				foreach ( $result['embedded']['verstrekteAdressen'] as &$adres ) {
					$adres = $this->remote_get( 'api/kic/v1/digitaaladressen/' . $adres['_self']['id'] );
				}
			}
		}

		return $this->user_data[ $bsn ];
	}

	/**
	 * Get data from the Klantinteractie API.
	 *
	 * @param string $path The path to the API endpoint.
	 *
	 * @return array The data from the API.
	 */
	private function remote_get( $path ) {
		$response = wp_remote_get(
			trailingslashit( $this->api_domain ) . $path,
			[
				'headers' => [
					'Authorization' => 'Bearer ' . $this->get_token(),
				],
			]
		);

		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return [];
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}

	/**
	 * Post data to the Klantinteractie API.
	 *
	 * @param string $path The path to the API endpoint.
	 * @param mixed  $body The body to send to the API.
	 * @param string $method The HTTP method to use.
	 *
	 * @return false|array An array containing the response from the API, or false if the request failed.
	 */
	private function remote_post( $path, $body, $method = 'POST' ) {
		$response = wp_remote_post(
			trailingslashit( $this->api_domain ) . $path,
			[
				'method'  => $method,
				'headers' => [
					'Authorization' => 'Bearer ' . $this->get_token(),
					'Content-Type'  => 'application/json',
				],
				'body'    => wp_json_encode( $body ),
			]
		);

		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}

	/**
	 * Build the JWT token.
	 *
	 * @return string The token.
	 */
	private function get_token() {
		$payload = [
			'iss'                 => $this->client_id,
			'iat'                 => time(),
			'client_id'           => $this->client_id,
			'user_id'             => $this->client_id,
			'user_representation' => $this->client_id,
		];

		return JWT::encode( $payload, $this->client_secret, 'HS256' );
	}

	/**
	 * Set the user data.
	 *
	 * @param array  $user_data The user data.
	 * @param string $bsn The BSN of the user.
	 *
	 * @return bool|\WP_Error True if the user data was set, \WP_Error otherwise.
	 */
	private function set_user_data( $user_data, $bsn = null ) {
		if ( ! $bsn ) {
			$bsn = decrypt( resolve( 'session' )->getSegment( 'digid' )->get( 'bsn' ) );
		}

		if ( ! $bsn ) {
			return new \WP_Error( 'no_bsn', __( 'No BSN found.', 'klantinteractie' ) );
		}

		$this->user_data[ $bsn ] = $user_data;

		return true;
	}

	/**
	 * Get the value of a specific field.
	 *
	 * @param string $field The field to get the value of.
	 * @param string $bsn The BSN of the user.
	 *
	 * @return false|string The value of the field, or false if the field was not found.
	 */
	public function get_field( $field, $bsn = null ) {
		if ( array_key_exists( $field, $this->contact_fields ) ) {
			return $this->get_contact_field( $field, $bsn );
		} elseif ( 'voorkeurskanaal' === $field ) {
			return $this->get_preference_field( $bsn );
		}

		return false;
	}

	/**
	 * Get the value of the preference field.
	 *
	 * @param string $bsn The BSN of the user.
	 *
	 * @return string The value of the preference field.
	 */
	private function get_preference_field( $bsn = null ) {
		$user_data = $this->get_user_data( $bsn );

		if ( ! isset( $user_data['results'] ) ) {
			return '';
		}

		foreach ( $user_data['results'] as $result ) {
			if ( ! isset( $result['embedded']['voorkeurskanaal']['soortDigitaalAdres'] ) ) {
				continue;
			}

			return $result['embedded']['voorkeurskanaal']['soortDigitaalAdres'];
		}

		return '';
	}

	/**
	 * Get the value of a specific contact field.
	 *
	 * @param string $field The field to get the value of.
	 * @param string $bsn The BSN of the user.
	 *
	 * @return string The value of the field.
	 */
	private function get_contact_field( $field, $bsn = null ) {
		$user_data = $this->get_user_data( $bsn );

		if ( ! isset( $user_data['results'] ) ) {
			return '';
		}

		foreach ( $user_data['results'] as $result ) {
			if ( ! isset( $result['embedded']['verstrekteAdressen'] ) ) {
				continue;
			}
			foreach ( $result['embedded']['verstrekteAdressen'] as $contact_option ) {
				if ( $contact_option['soortDigitaalAdres'] === $field ) {
					return $contact_option['adres'];
				}
			}
		}

		return '';
	}

	/**
	 * Set the value of a specific field.
	 *
	 * @param string $field The field to set the value of.
	 * @param string $value The value to set.
	 * @param string $bsn The BSN of the user.
	 *
	 * @return bool|\WP_Error True if the field was set, \WP_Error otherwise.
	 */
	public function set_field( $field, $value, $bsn = null ) {
		if ( array_key_exists( $field, $this->contact_fields ) ) {
			return $this->set_contact_field( $field, $value, $bsn );
		} elseif ( 'voorkeurskanaal' === $field ) {
			return $this->set_preference_field( $value, $bsn );
		}

		// translators: %s: field name.
		return new \WP_Error( 'field_not_found', sprintf( __( 'The field (%s) was not found.', 'klantinteractie' ), $field ) );
	}

	/**
	 * Set the value of the preference field.
	 *
	 * @param string $value The value to set.
	 * @param string $bsn The BSN of the user.
	 *
	 * @return bool|\WP_Error True if the field was set, \WP_Error otherwise.
	 */
	private function set_preference_field( $value, $bsn = null ) {
		$user_data = $this->get_user_data( $bsn );

		if ( ! isset( $user_data['results'] ) ) {
			return new \WP_Error( 'no_user_data', __( 'No user data found.', 'klantinteractie' ) );
		}

		foreach ( $user_data['results'] as &$original_result ) {
			if ( $original_result['embedded']['voorkeurskanaal']['soortDigitaalAdres'] !== $value ) {
				foreach ( $original_result['embedded']['verstrekteAdressen'] as $contact_option ) {
					if ( $contact_option['soortDigitaalAdres'] === $value ) {
						$result = $this->remote_post(
							'api/kic/v1/partijen/?id=' . $original_result['id'],
							[
								'voorkeurskanaal' => $contact_option['_self']['id'],
							],
							'PATCH'
						);

						if ( ! $result ) {
							// translators: %s: field name.
							return new \WP_Error( 'field_not_set', sprintf( __( 'The field (%s) could not be set.', 'klantinteractie' ), 'voorkeurskanaal' ) );
						}
						$original_result['voorkeurskanaal']             = $contact_option['_self']['self'];
						$original_result['embedded']['voorkeurskanaal'] = $contact_option;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Set the value of a specific contact field.
	 *
	 * @param string $field The field to set the value of.
	 * @param string $value The value to set.
	 * @param string $bsn The BSN of the user.
	 *
	 * @return bool|\WP_Error True if the field was set, \WP_Error otherwise.
	 */
	private function set_contact_field( $field, $value, $bsn = null ) {
		$user_data = $this->get_user_data( $bsn );

		if ( ! isset( $user_data['results'] ) ) {
			return new \WP_Error( 'no_user_data', __( 'No user data found.', 'klantinteractie' ) );
		}

		foreach ( $user_data['results'] as &$result ) {
			$field_found = false;
			if ( isset( $result['embedded']['verstrekteAdressen'] ) ) {
				foreach ( $result['embedded']['verstrekteAdressen'] as &$contact_option ) {
					if ( $contact_option['soortDigitaalAdres'] === $field && $value !== $contact_option['adres'] ) {
						$this->update_contact_field( $contact_option, $value );
						$contact_option['adres'] = $value;
						$field_found             = true;
					} elseif ( $contact_option['soortDigitaalAdres'] === $field && $value === $contact_option['adres'] ) {
						// The field is already set to the correct value.
						$field_found = true;
					}
				}
			}

			if ( ! $field_found ) {
				$return = $this->add_contact_field( $field, $value, $result );
				if ( is_wp_error( $return ) ) {
					return $return;
				}
			}
		}

		return $this->set_user_data( $user_data, $bsn );
	}

	/**
	 * Add a new contact field.
	 *
	 * @param string $field The field to add.
	 * @param string $value The value to set.
	 * @param array  $user_data The user data.
	 *
	 * @return bool|\WP_Error True if the field was added, \WP_Error otherwise.
	 */
	private function add_contact_field( $field, $value, &$user_data ) {
		$body          = $this->contact_fields[ $field ];
		$body['adres'] = $value;

		$result = $this->remote_post(
			'api/kic/v1/partijen/?id=' . $user_data['id'],
			[
				'verstrekteAdressen' => [ $body ],
			],
			'PATCH'
		);

		if ( $result ) {
			$user_data = $result;

			return true;
		}

		// translators: %s: field name.
		return new \WP_Error( 'field_not_added', sprintf( __( 'The field (%s) could not be added.', 'klantinteractie' ), $field ) );
	}

	/**
	 * Update an existing contact field.
	 *
	 * @param array  $contact_option The contact field to update.
	 * @param string $value The value to set.
	 *
	 * @return bool True if the field was updated, false otherwise.
	 */
	private function update_contact_field( $contact_option, $value ) {
		$result = $this->remote_post(
			'api/kic/v1/digitaaladressen/' . $contact_option['_self']['id'],
			[
				'adres' => $value,
			],
			'PATCH'
		);

		return ( false !== $result );
	}

	/**
	 * Get klantcontacten for the given BSN.
	 *
	 * @param string $bsn The BSN of the user.
	 *
	 * @return array An array containing the klantcontacten.
	 */
	public function get_klantcontacten( $bsn = null ) {
		if ( ! $bsn ) {
			$bsn = decrypt( resolve( 'session' )->getSegment( 'digid' )->get( 'bsn' ) );
		}

		if ( ! $bsn ) {
			return [];
		}

		$partijen = $this->remote_get( 'api/kic/v1/partijen?externeIdentificaties.partijIdentificator.objectId=' . $bsn );

		if ( ! isset( $partijen['results'] ) ) {
			return [];
		}

		$klantcontacten = [];

		foreach ( $partijen['results'] as $partij ) {
			$betrokkenen_bij_klantcontact = $this->remote_get( 'api/kic/v1/betrokkenenbijklantcontact?partij=' . $partij['_self']['self'] );

			if ( ! $betrokkenen_bij_klantcontact ) {
				continue;
			}

			foreach ( $betrokkenen_bij_klantcontact['results'] as $betrokkene ) {
				$klantcontacten[] = $betrokkene;
			}
		}

		return $klantcontacten;
	}
}
