<?php

final class RY_WEZI_WC_Admin_Setting_Invoice
{
    protected static $_instance = null;

    public static function instance(): RY_WEZI_WC_Admin_Setting_Invoice
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init()
    {
        add_filter('woocommerce_get_sections_rytools', [$this, 'add_sections'], 11);
        add_filter('woocommerce_get_settings_rytools', [$this, 'add_setting'], 10, 2);
        add_action('woocommerce_update_options_rytools_ezpay_invoice', [$this, 'check_option']);
    }

    public function add_sections($sections)
    {
        if (isset($sections['tools'])) {
            $add_idx = array_search('tools', array_keys($sections));
            $sections = array_slice($sections, 0, $add_idx) + [
                'ezpay_invoice' => __('ezPay invoice', 'ry-woocommerce-ezpay-invoice'),
            ] + array_slice($sections, $add_idx);
        } else {
            $sections['ezpay_invoice'] = __('ezPay invoice', 'ry-woocommerce-ezpay-invoice');
        }

        $enable_list = apply_filters('enable_ry_invoice', []);
        if (count($enable_list) > 1) {
            WC_Admin_Settings::add_error(__('Not recommended enable two invoice plugins at the same time!', 'ry-woocommerce-ezpay-invoice'));
        }

        return $sections;
    }

    public function add_setting($settings, $current_section)
    {
        if ('ezpay_invoice' == $current_section) {
            if (!function_exists('simplexml_load_string')) {
                echo '<div class="notice notice-error"><p><strong>RY ezPay Invoice for WooCommerce</strong> ' . esc_html__('Required PHP function `simplexml_load_string`.', 'ry-woocommerce-ezpay-invoice') . '</p></div>';
            }

            $settings = include RY_WEZI_PLUGIN_DIR . 'woocommerce/admin/settings/settings-invoice.php';
        }
        return $settings;
    }

    public function check_option()
    {
        if (!RY_WEZI_WC_Invoice::instance()->is_testmode()) {
            if (empty(RY_WEZI::get_option('ezpay_HashKey')) || empty(RY_WEZI::get_option('ezpay_HashIV'))) {
                WC_Admin_Settings::add_error(__('ezPay invoice method failed to enable!', 'ry-woocommerce-ezpay-invoice'));
            }
        }

        if (!preg_match('/^[a-z0-9]*$/i', RY_WEZI::get_option('order_prefix', ''))) {
            WC_Admin_Settings::add_error(__('Order no prefix only letters and numbers allowed', 'ry-woocommerce-ezpay-invoice'));
            RY_WEZI::update_option('order_prefix', '');
        }
    }
}
