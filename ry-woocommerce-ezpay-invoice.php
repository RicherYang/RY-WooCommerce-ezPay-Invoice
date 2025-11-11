<?php

/**
 * Plugin Name: RY ezPay Invoice for WooCommerce
 * Plugin URI: https://ry-plugin.com/ry-woocommerce-ezpay-invoice
 * Description: WooCommerce order invoice for ezPay
 * Version: 2.0.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Author: Richer Yang
 * Author URI: https://richer.tw/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Update URI: https://ry-plugin.com/ry-woocommerce-ezpay-invoice
 *
 * Text Domain: ry-woocommerce-ezpay-invoice
 * Domain Path: /languages
 *
 * WC requires at least: 8
 */

function_exists('plugin_dir_url') or exit('No direct script access allowed');

define('RY_WEZI_VERSION', '2.0.0');
define('RY_WEZI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RY_WEZI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RY_WEZI_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('RY_WEZI_PLUGIN_LANGUAGES_DIR', plugin_dir_path(__FILE__) . '/languages');

require_once RY_WEZI_PLUGIN_DIR . 'includes/main.php';

register_activation_hook(__FILE__, ['RY_WEZI', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['RY_WEZI', 'plugin_deactivation']);

function RY_WEZI(): RY_WEZI
{
    return RY_WEZI::instance();
}

RY_WEZI();
