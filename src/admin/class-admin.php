<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.openwebconcept.nl
 * @since      1.0.0
 *
 * @package    Klantinteractie_Plugin
 * @subpackage Klantinteractie_Plugin/Admin
 */

namespace Klantinteractie_Plugin\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Klantinteractie_Plugin
 * @subpackage Klantinteractie_Plugin/Admin
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Admin {

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		// Add panel to the admin menu with title "Klantinteractie" that allows to configure the plugin under Settings.
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		// Add the option "CORS Origin" to the Settings menu.
		add_action( 'admin_init', [ $this, 'settings_init' ] );
		// Display settings errors.
		add_action( 'admin_notices', [ $this, 'display_settings_errors' ] );
	}

	/**
	 * Add a submenu item to the Settings menu.
	 */
	public function add_admin_menu() {
		add_options_page(
			'Klantinteractie',
			'Klantinteractie',
			'manage_options',
			'klantinteractie',
			[ $this, 'render_admin_page' ]
		);
	}

	/**
	 * Render the admin page.
	 */
	public function render_admin_page() {
		?>
		<form action='<?php print esc_attr( admin_url( 'options.php' ) ); ?>' method='post'>
			<?php
			settings_fields( 'klantinteractie_plugin' );
			do_settings_sections( 'klantinteractie_plugin' );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Register and add settings.
	 */
	public function settings_init() {
		register_setting(
			'klantinteractie_plugin',
			'klantinteractie_plugin_api_domain',
			[ $this, 'sanitize_api_domain' ]
		);

		register_setting(
			'klantinteractie_plugin',
			'klantinteractie_plugin_client_id',
			'sanitize_text_field'
		);

		register_setting(
			'klantinteractie_plugin',
			'klantinteractie_plugin_client_secret',
			'sanitize_text_field'
		);

		add_settings_section(
			'klantinteractie_plugin_section',
			__( 'Klantinteractie settings', 'klantinteractie' ),
			[ $this, 'settings_section_callback' ],
			'klantinteractie_plugin'
		);

		add_settings_field(
			'klantinteractie_plugin_api_domain',
			__( 'API Domain', 'klantinteractie' ),
			[ $this, 'api_domain_render' ],
			'klantinteractie_plugin',
			'klantinteractie_plugin_section'
		);

		add_settings_field(
			'klantinteractie_plugin_client_id',
			__( 'Client ID', 'klantinteractie' ),
			[ $this, 'client_id_render' ],
			'klantinteractie_plugin',
			'klantinteractie_plugin_section'
		);

		add_settings_field(
			'klantinteractie_plugin_client_secret',
			__( 'Client Secret', 'klantinteractie' ),
			[ $this, 'client_secret_render' ],
			'klantinteractie_plugin',
			'klantinteractie_plugin_section'
		);
	}

	/**
	 * Sanitize and validate the API Domain field as a URL.
	 *
	 * @param string $input The input to sanitize.
	 *
	 * @return string The sanitized input.
	 */
	public function sanitize_api_domain( $input ) {
		// Remove all illegal characters from a url.
		$input = filter_var( $input, FILTER_SANITIZE_URL );

		// Validate url.
		if ( ! filter_var( $input, FILTER_VALIDATE_URL ) ) {
			add_settings_error(
				'klantinteractie_plugin_api_domain',
				'klantinteractie_plugin_api_domain_error',
				__( 'The API Domain is not a valid URL.', 'klantinteractie' ),
				'error'
			);
			return '';
		}

		return $input;
	}

	/**
	 * Display settings errors.
	 */
	public function display_settings_errors() {
		settings_errors( 'klantinteractie_plugin' );
	}


	/**
	 * Render the Settings section.
	 */
	public function settings_section_callback() {
	}

	/**
	 * Render the API Domain field.
	 */
	public function api_domain_render() {
		$option = get_option( 'klantinteractie_plugin_api_domain' );
		?>
		<input type="text" name="klantinteractie_plugin_api_domain" value="<?php echo isset( $option ) ? esc_attr( $option ) : ''; ?>">
		<?php
	}

	/**
	 * Render the Client ID field.
	 */
	public function client_id_render() {
		$option = get_option( 'klantinteractie_plugin_client_id' );
		?>
		<input type="text" name="klantinteractie_plugin_client_id" value="<?php echo isset( $option ) ? esc_attr( $option ) : ''; ?>">
		<?php
	}

	/**
	 * Render the Client Secret field, do not show the actual value.
	 */
	public function client_secret_render() {
		$option = get_option( 'klantinteractie_plugin_client_secret' );
		?>
		<input type="password" name="klantinteractie_plugin_client_secret" value="<?php echo isset( $option ) ? esc_attr( $option ) : ''; ?>">
		<?php
	}
}
