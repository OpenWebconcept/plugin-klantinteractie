# Klantinteractie Plugin

This plugin adds support for a Gravity Forms form to submit data to the klantinteractie API. It also adds a helper function for the frontend to retrieve KlantContactmomenten.

## Requirements

This plugin requires the Yard | GravityForms DigiD plugin to be installed and activated.

## Installation

### Composer installation

1. `composer source git@github.com:OpenWebconcept/plugin-klantinteractie.git`
2. `composer require acato/klantinteractie`
3. Activate the Klantinteractie plugin through the 'Plugins' menu in WordPress.

## Development

### Coding Standards

Please remember, we use the WordPress PHP Coding Standards for this plugin! (https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/) To check if your changes are compatible with these standards:

*  `cd /wp-content/plugins/klantinteractie`
*  `composer install` (this step is only needed once after installing the plugin)
*  `./vendor/bin/phpcs --standard=phpcs.xml.dist .`
*  See the output if you have made any errors.
    *  Errors marked with `[x]` can be fixed automatically by phpcbf, to do so run: `./vendor/bin/phpcbf --standard=phpcs.xml.dist .`

N.B. the `composer install` command also install a git hook, preventing you from committing code that isn't compatible with the coding standards.

### Translations
```
wp i18n make-pot . languages/klantinteractie.pot --exclude="node_modules/,vendor/" --domain="klantinteractie"
```
