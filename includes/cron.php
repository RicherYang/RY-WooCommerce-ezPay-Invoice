<?php

final class RY_WEZI_Cron
{
    public static function add_action(): void
    {
        add_action(RY_WEZI::OPTION_PREFIX . 'check_expire', [__CLASS__, 'check_expire']);

        add_action(RY_WEZI::OPTION_PREFIX . 'auto_get_invoice', [__CLASS__, 'get_invoice']);
        add_action(RY_WEZI::OPTION_PREFIX . 'auto_invalid_invoice', [__CLASS__, 'invalid_invoice']);

        add_action('ry_wezi_auto_get_invoice', [__CLASS__, 'get_invoice']);
        add_action('ry_wezi_auto_invalid_invoice', [__CLASS__, 'invalid_invoice']);
    }

    public static function check_expire(): void
    {
        RY_WEZI_License::instance()->check_expire();
    }

    public static function get_invoice($order_ID): void
    {
        RY_WEZI_WC_Invoice_Api::instance()->get($order_ID);
    }

    public static function invalid_invoice($order_ID): void
    {
        RY_WEZI_WC_Invoice_Api::instance()->invalid($order_ID);
    }
}
