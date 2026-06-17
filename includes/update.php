<?php

defined('ABSPATH') or exit;

final class RY_WEZI_update
{
    public static function update()
    {
        $now_version = RY_WEZI::get_option('version');

        if (false === $now_version) {
            $now_version = '0.0.0';
        }
        if (RY_WEZI_VERSION === $now_version) {
            return;
        }

        if (version_compare($now_version, '2.3.0', '<')) {
            if (RY_WEZI::get_option('ezpay_MerchantID') !== false) {
                RY_WEZI::update_option('apiinfo', [
                    'prefix' => RY_WEZI::get_option('order_prefix'),
                    'kiosk_print' => RY_WEZI::get_option('support_kiosk_print'),
                    'use_sku' => RY_WEZI::get_option('use_sku_as_name'),
                    'abnormal_mode' => RY_WEZI::get_option('amount_abnormal_mode'),
                    'abnormal_product' => RY_WEZI::get_option('amount_abnormal_product'),
                    'testmode' => RY_WEZI::get_option('ezpay_invoice_testmode'),
                    'MerchantID' => RY_WEZI::get_option('ezpay_MerchantID'),
                    'HashKey' => RY_WEZI::get_option('ezpay_HashKey'),
                    'HashIV' => RY_WEZI::get_option('ezpay_HashIV'),
                ], false);
                RY_WEZI::delete_option('order_prefix');
                RY_WEZI::delete_option('support_kiosk_print');
                RY_WEZI::delete_option('use_sku_as_name');
                RY_WEZI::delete_option('amount_abnormal_mode');
                RY_WEZI::delete_option('amount_abnormal_product');
                RY_WEZI::delete_option('used_track');
                RY_WEZI::delete_option('ezpay_invoice_testmode');
                RY_WEZI::delete_option('ezpay_MerchantID');
                RY_WEZI::delete_option('ezpay_HashKey');
                RY_WEZI::delete_option('ezpay_HashIV');
            }
            if (RY_WEZI::get_option('skip_foreign_order') !== false) {
                RY_WEZI::update_option('skip_foreign_order', RY_WEZI::get_option('skip_foreign_order'), true);
            }

            RY_WEZI::update_option('version', '2.3.0', true);
        }
    }
}
