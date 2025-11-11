<?php

function rywsi_invoice_type_to_name($invoice_type)
{
    static $type_name = [];
    if (empty($type_name)) {
        $type_name = [
            'personal' => _x('personal', 'invoice type', 'ry-woocommerce-ezpay-invoice'),
            'company' => _x('company', 'invoice type', 'ry-woocommerce-ezpay-invoice'),
            'donate' => _x('donate', 'invoice type', 'ry-woocommerce-ezpay-invoice'),
        ];
    }

    return $type_name[$invoice_type] ?? $invoice_type;
}

function rywsi_carruer_type_to_name($carruer_type)
{
    static $type_name = [];
    if (empty($type_name)) {
        $type_name = [
            'none' => _x('none', 'carruer type', 'ry-woocommerce-ezpay-invoice'),
            'ezpay_host' => _x('ezpay_host', 'carruer type', 'ry-woocommerce-ezpay-invoice'),
            'MOICA' => _x('MOICA', 'carruer type', 'ry-woocommerce-ezpay-invoice'),
            'phone_barcode' => _x('phone_barcode', 'carruer type', 'ry-woocommerce-ezpay-invoice'),
        ];
    }

    return $type_name[$carruer_type] ?? $carruer_type;
}
