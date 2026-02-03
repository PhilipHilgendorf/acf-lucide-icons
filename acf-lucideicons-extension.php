<?php
/**
 * Plugin Name: ACF Custom Field Type - Lucide Icons
 * Plugin URI:  https://philip-hilgendorf.com/
 * Description: Adds a custom field type “Lucide Icons” to ACF.
 * Version:     1.0.0
 * Author:      Philip Hilgendorf
 * Author URI:  https://philip-hilgendorf.com/
 * Text Domain: acf-lucideicons
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if(!defined('ACF_LUCIDEICONS_PLUGIN_FILE')) {
    define('ACF_LUCIDEICONS_PLUGIN_FILE', __FILE__);
}

if(!defined('ACF_LUCIDEICONS_PLUGIN_DIR')) {
    define('ACF_LUCIDEICONS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if(!defined('ACF_LUCIDEICONS_PLUGIN_URL')) {
    define('ACF_LUCIDEICONS_PLUGIN_URL', plugin_dir_url(__FILE__));
}

add_action('init', function() {
    if ( ! class_exists( 'ACF' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . __( 'Das Plugin "ACF Custom Field Type - Lucide Icons" benötigt Advanced Custom Fields.', 'acf-deintyp' ) . '</p></div>';
        });
        return;
    }


    require_once __DIR__ . '/includes/fields/class-acf-field-lucideicons.php';

});
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_script('lucide-js', ACF_LUCIDEICONS_PLUGIN_URL . 'assets/js/lucide.min.js', [], '0.563.0', true);

    wp_add_inline_script('lucide-js', 'document.addEventListener("DOMContentLoaded", function() { lucide.createIcons(); });');
});