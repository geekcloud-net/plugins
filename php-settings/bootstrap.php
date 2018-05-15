<?php
/**
 * PHP Settings
 *
 * This plugin provides a simple user interface with a code editor to edit your local php.ini settings. 
 *
 * @package   php-settings
 * @author    Askupa Software <hello@askupasoftware.com>
 * @link      http://products.askupasoftware.com/php-settings
 * @copyright 2017 Askupa Software
 *
 * @wordpress-plugin
 * Plugin Name:     PHP Settings
 * Plugin URI:      http://products.askupasoftware.com/php-settings
 * Description:     This plugin provides a simple user interface with a code editor to edit your local php.ini settings. 
 * Version:         1.0.6
 * Author:          Askupa Software
 * Author URI:      http://www.askupasoftware.com
 * Text Domain:     php-settings
 * Domain Path:     /languages
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'PHP_SETTINGS_VERSION', '1.0.6' );
define( 'PHP_SETTINGS_JS_URL', plugins_url( '/assets/js/', __FILE__ ) );
define( 'PHP_SETTINGS_CSS_URL', plugins_url( '/assets/css/', __FILE__ ) );
define( 'PHP_SETTINGS_IMG_URL', plugins_url( '/assets/img/', __FILE__ ) );

if( include('includes/EnvironmentValidator.php') ) // Validate PHP version etc
{
    include('includes/PHPSettings.php');
    include('includes/INIFile.php');
    include('includes/PHPInfo.php');
}