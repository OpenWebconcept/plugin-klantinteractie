<?php
/**
 * Add hooks and filter to alter Gravity Forms behaviour.
 *
 * @link       https://www.openwebconcept.nl
 * @since      1.0.0
 *
 * @package    Klantinteractie_Plugin
 * @subpackage Klantinteractie_Plugin/Gravityforms
 */

namespace Klantinteractie_Plugin\Gravityforms;

use Klantinteractie_Plugin\API\Klantinteractie_API;

/**
 * Add hooks and filter to alter Gravity Forms behaviour.
 *
 * @package    Klantinteractie_Plugin
 * @subpackage Klantinteractie_Plugin/Gravityforms
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Gravityforms {

	/**
	 * The mappings between the Klantinteractie API fields and the Gravity Forms fields.
	 *
	 * @var array
	 */
	private $mappings;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init' ] );

		add_filter( 'gform_form_settings_fields', [ $this, 'add_form_settings' ], 10, 1 );
		add_action( 'gform_field_standard_settings', [ $this, 'add_field_settings' ], 10, 2 );
		add_action( 'gform_editor_js', [ $this, 'add_editor_js' ] );
		add_filter( 'gform_pre_render', [ $this, 'validate_user' ], 10, 1 );
		add_filter( 'gform_form_post_get_meta', [ $this, 'prefill_fields' ], 10, 2 );
		add_action( 'gform_after_submission', [ $this, 'after_submission' ], 10, 2 );
	}

	/**
	 * Initialize the class properties.
	 */
	public function init() {
		$this->mappings = [
			'voorkeurskanaal' => [
				'label' => __( 'Contact preference', 'klantinteractie' ),
			],
			'emailadres'      => [
				'label' => __( 'Emailaddress', 'klantinteractie' ),
			],
			'telefoon'        => [
				'label' => __( 'Phone number', 'klantinteractie' ),
			],
		];
	}

	/**
	 * Add a new form setting to the form settings page. This setting is used to indicate it is a klantinteractie form.
	 *
	 * @param array $fields The Form Settings fields.
	 *
	 * @return array
	 */
	public function add_form_settings( $fields ) {
		$fields[] = [
			'title'  => esc_html__( 'Klantinteractie', 'klantinteractie' ),
			'fields' => [
				[
					'label'   => esc_html__( 'Klantinteractie', 'klantinteractie' ),
					'type'    => 'toggle',
					'name'    => 'owc-klantinteractie-enabled',
					'tooltip' => esc_html__( 'Enable Klantinteractie for this form.', 'klantinteractie' ),
					'choices' => [
						[
							'label' => esc_html__( 'Enable Klantinteractie', 'klantinteractie' ),
							'name'  => 'owc-klantinteractie-enabled',
						],
					],
				],
			],
		];

		return $fields;
	}

	/**
	 * Add a new field setting to the field settings page. This setting is used to indicate which klantinteractie field is mapped to the current field.
	 *
	 * @param int $position The position of the field.
	 * @param int $form_id The ID of the form.
	 */
	public function add_field_settings( $position, $form_id ) {
		if ( 0 !== $position ) {
			return;
		}

		if ( ! class_exists( '\GFAPI' ) ) {
			return;
		}

		$form = \GFAPI::get_form( $form_id );
		if ( ! isset( $form['owc-klantinteractie-enabled'] ) || '1' !== $form['owc-klantinteractie-enabled'] ) {
			return;
		}

		?>
		<li class="klantinteractie_setting field_setting">
			<label for="klantinteractie_mapping" class="section_label">
				<?php esc_html_e( 'Klantinteractie mapping', 'klantinteractie' ); ?>
			</label>

			<select id="klantinteractie_mapping" onchange="SetFieldProperty('klantinteractieMapping', this.value);">
				<option value=""><?php esc_attr_e( 'Choose klantinteractie fieldname', 'klantinteractie' ); ?></option>

				<?php foreach ( $this->mappings as $field => $settings ) : ?>
					<option value="<?php echo esc_attr( $field ); ?>">
						<?php echo esc_html( $settings['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</li>
		<?php
	}

	/**
	 * Add custom JS to the Gravity Forms editor for our custom fields.
	 *
	 * @return void
	 */
	public function add_editor_js() {
		?>
		<script type="text/javascript">
			// Add field setting to all field types.
			jQuery.each(fieldSettings, function (index, value) {
				fieldSettings[index] += ', .klantinteractie_setting';
			});
			// Binding to the load field settings event to initialize the dropdown.
			jQuery(document).bind('gform_load_field_settings', function (event, field, form) {
				jQuery('#klantinteractie_mapping').val(field['klantinteractieMapping']);
			});
		</script>
		<?php
	}

	/**
	 * Prefill the fields with the values from the Klantinteractie API.
	 *
	 * @param array $form The form.
	 *
	 * @return array The form.
	 */
	public function prefill_fields( $form ) {
		if ( ! class_exists( '\GFAPI' ) ) {
			return $form;
		}

		if ( ! isset( $form['owc-klantinteractie-enabled'] ) || '1' !== $form['owc-klantinteractie-enabled'] ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( empty( $field->klantinteractieMapping ) || ! array_key_exists( $field->klantinteractieMapping, $this->mappings ) ) {
				continue;
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$field->defaultValue = Klantinteractie_API::get_instance()->get_field( $field->klantinteractieMapping );
		}

		return $form;
	}

	/**
	 * Send the form data to the Klantinteractie API.
	 *
	 * @param array $entry The entry.
	 * @param array $form The form.
	 *
	 * @return void
	 */
	public function after_submission( $entry, $form ) {
		if ( ! class_exists( '\GFAPI' ) ) {
			return;
		}

		if ( ! isset( $form['owc-klantinteractie-enabled'] ) || '1' !== $form['owc-klantinteractie-enabled'] ) {
			return;
		}

		$errors = [];
		foreach ( $form['fields'] as &$field ) {
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( empty( $field->klantinteractieMapping ) || ! array_key_exists( $field->klantinteractieMapping, $this->mappings ) ) {
				continue;
			}

			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			$return = Klantinteractie_API::get_instance()->set_field( $field->klantinteractieMapping, $entry[ $field->id ] );
			if ( is_wp_error( $return ) ) {
				$errors = array_merge( $errors, $return->get_error_messages() );
			}
		}

		if ( count( $errors ) ) {
			include dirname( __DIR__, 2 ) . '/templates/form-submission-failed.php';
			exit;
		}
	}

	/**
	 * Validate if the user is known in the Klantinteractie API.
	 *
	 * @param array $form The form.
	 *
	 * @return array The form.
	 */
	public function validate_user( $form ) {
		if ( ! isset( $form['owc-klantinteractie-enabled'] ) || '1' !== $form['owc-klantinteractie-enabled'] ) {
			return $form;
		}

		$user_data = Klantinteractie_API::get_instance()->get_user_data();
		if ( ! empty( $user_data['results'] ) ) {
			return $form;
		}

		$form['fields'] = [
			new \GF_Field_HTML(
				[
					'id'      => 1,
					'type'    => 'html',
					'label'   => __( 'Error', 'klantinteractie' ),
					'content' => '<p>' . __( 'Your data could not be retrieved. Please try again later or contact you municipality.', 'klantinteractie' ) . '</p>',
				]
			),
		];

		return $form;
	}
}
