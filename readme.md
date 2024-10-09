# Klantinteractie Plugin

This plugin adds support for a Gravity Forms form to submit data to the klantinteractie API. It also adds a helper function for the frontend to retrieve KlantContactmomenten.

## Requirements

This plugin requires an DigiD plugin that implements the OWC IDP Userdata package to be installed and activated.

## Installation

### Composer installation

1. `composer source git@github.com:OpenWebconcept/plugin-klantinteractie.git`
2. `composer require acato/klantinteractie`
3. Activate the Klantinteractie plugin through the 'Plugins' menu in WordPress.

### Configuration

1. Go to the Klantinteractie settings page in the WordPress admin: Settings > Klantinteractie
2. Fill in the API domain, client ID and client secret.
3. In Gravity Forms, create a new form and activate the Klantinteractie under the form's settings.
4. Add fields to the form and map them to the Klantinteractie fields. You can do so under the field settings > Klantinteractie mapping.
5. The fields will now be prefilled with the data from the Klantinteractie API when the form is loaded. And the Klantinteractie API will be updated upon form submit.

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

### Functions

For frontend usage this plugin implements the following functions:
* `klantinteractie_get_klantcontacten( $bsn )`: Retrieve all contactmoments for a given BSN.
* `klantinteractie_get_berichten( $bsn )`: Retrieve all messages for a given BSN.
