<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the public-facing side of the site and
 * the admin area.
 *
 * @link       https://www.openwebconcept.nl
 * @since      1.0.0
 *
 * @package    Klantinteractie_Plugin
 */

namespace Klantinteractie_Plugin;

use Klantinteractie_Plugin\Admin\Admin;
use Klantinteractie_Plugin\Frontend\Frontend;
use Klantinteractie_Plugin\Gravityforms\Gravityforms;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Klantinteractie_Plugin
 * @author     Richard Korthuis <richardkorthuis@acato.nl>
 */
class Plugin {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Define the locale, and set the hooks for the admin area and the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		/**
		 * Enable internationalization.
		 */
		new I18n();

		/**
		 * Register admin specific functionality.
		 */
		new Admin();

		/**
		 * Register Gravityforms hooks.
		 */
		new Gravityforms();
	}
}
