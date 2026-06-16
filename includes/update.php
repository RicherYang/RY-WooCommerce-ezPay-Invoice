<?php

defined('ABSPATH') or exit;

final class RY_WEZI_update
{
    public static function update()
    {
        $now_version = RY_WEZI::get_option('version', '0.0.0');

        if (RY_WEZI_VERSION === $now_version) {
            return;
        }

        if (version_compare($now_version, '2.1.5', '<')) {
            if (RY_WEZI::get_option('ezpay_MerchantID') !== false) {
                RY_WEZI::update_option('apikey', [
                    'MerchantID' => RY_WEZI::get_option('ezpay_MerchantID'),
                    'HashKey' => RY_WEZI::get_option('ezpay_HashKey'),
                    'HashIV' => RY_WEZI::get_option('ezpay_HashIV'),
                ], false);
                RY_WEZI::delete_option('ezpay_MerchantID');
                RY_WEZI::delete_option('ezpay_HashKey');
                RY_WEZI::delete_option('ezpay_HashIV');
            }
            if (RY_WEZI::get_option('ezpay_invoice_testmode') !== false) {
                RY_WEZI::update_option('testmode', RY_WEZI::get_option('ezpay_invoice_testmode'));
                RY_WEZI::delete_option('ezpay_invoice_testmode');
            }

            RY_WEZI::update_option('version', '2.1.5', true);
        }
    }
}
