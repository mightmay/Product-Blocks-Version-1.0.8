<?php
/**
 * Plugin Name: Gutenberg WooCommerce Blocks
 * Description: Product blocks is a Gutenberg product blocks for WooCommerce, very useful for product listing, product grid and product slider.
 * Version:     1.0.8
 * Author:      wpxpo
 * Author URI:  https://wpxpo.com/
 * Text Domain: product-blocks
 * License:     GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
**/

defined( 'ABSPATH' ) || exit;

// Define
define('WOPB_VER', '1.0.8');
define('WOPB_URL', plugin_dir_url(__FILE__));
define('WOPB_PATH', plugin_dir_path(__FILE__));

// Language Load
add_action('init', 'wopb_language_load');
function wopb_language_load() {
    load_plugin_textdomain( 'product-blocks', false, basename(dirname(__FILE__))."/languages/" );
}

// Common Function
if(!function_exists('wopb_function')) {
    function wopb_function() {
        require_once WOPB_PATH . 'classes/Functions.php';
        return new \WOPB\Functions();
    }
}

// Plugin Initialization
if (!class_exists( 'wopb_Initialization' )) {
    require_once WOPB_PATH . 'classes/Initialization.php';
    new \WOPB\Initialization();
}

// Template
if (wopb_function()->is_wc_ready()) {
    require_once WOPB_PATH . 'classes/Templates.php';
    new \WOPB\Templates();
}