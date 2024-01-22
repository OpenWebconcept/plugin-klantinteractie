<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin and starts the plugin.
 *
 * @link              https://www.openwebconcept.nl
 * @since             1.0.0
 * @package           Klantinteractie_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Klantinteractie
 * Plugin URI:        https://www.openwebconcept.nl
 * Description:       This plugin adds support for a Gravity Forms form to submit data to the klantinteractie API.
 * Version:           1.0.0
 * Author:            Acato
 * Author URI:        https://www.acato.nl
 * Text Domain:       klantinteractie
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'KLANTINTERACTIE_VERSION', '1.0.0' );

require_once plugin_dir_path( __FILE__ ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'class-autoloader.php';
spl_autoload_register( array( '\Klantinteractie_Plugin\Autoloader', 'autoload' ) );

/**
 * Begins execution of the plugin.
 */
new \Klantinteractie_Plugin\Plugin();
