<?php

$order_statuses = wc_get_order_statuses();
$paid_status = [];
foreach (wc_get_is_paid_statuses() as $status) {
    $paid_status[] = $order_statuses['wc-' . $status];
}
$paid_status = implode(', ', $paid_status);

return [
    [
        'title' => __('Base options', 'ry-woocommerce-ezpay-invoice'),
        'id' => 'base_options',
        'type' => 'title',
    ],
    [
        'title' => __('Debug log', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'ezpay_invoice_log',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Enable logging', 'ry-woocommerce-ezpay-invoice') . '<br>'
            . sprintf(
                /* translators: %s: Path of log file */
                __('Log API / IPN information, inside %s', 'ry-woocommerce-ezpay-invoice'),
                '<code>' . WC_Log_Handler_File::get_log_file_path('ry_ezpay_invoice') . '</code>',
            )
            . '<p class="description" style="margin-bottom:2px">' . __('Note: this may log personal information.', 'ry-woocommerce-ezpay-invoice') . '</p>',
    ],
    [
        'title' => __('Order no prefix', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'order_prefix',
        'type' => 'text',
        'desc' => __('The prefix string of order no. Only letters and numbers allowed.', 'ry-woocommerce-ezpay-invoice'),
        'desc_tip' => true,
    ],
    [
        'title' => __('Show invoice number', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'show_invoice_number',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Show invoice number in Frontend order list', 'ry-woocommerce-ezpay-invoice'),
    ],
    [
        'title' => __('Move billing company', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'move_billing_company',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Move billing company to invoice area', 'ry-woocommerce-ezpay-invoice'),
    ],
    [
        'id' => 'base_options',
        'type' => 'sectionend',
    ],
    [
        'title' => __('Invoice options', 'ry-woocommerce-ezpay-invoice'),
        'id' => 'invoice_options',
        'type' => 'title',
    ],
    [
        'title' => __('Support paper type (B2C)', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'support_carruer_type_none',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('You need print invoice and seed to orderer.', 'ry-woocommerce-ezpay-invoice'),
    ],
    [
        'title' => __('Support kiosk print', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'support_kiosk_print',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Customer can print winning invoice at kiosk', 'ry-woocommerce-ezpay-invoice'),
    ],
    [
        'title' => __('User SKU as product name', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'use_sku_as_name',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('If product no SKU, back to use product name', 'ry-woocommerce-ezpay-invoice'),
    ],
    [
        'title' => __('Get mode', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'get_mode',
        'type' => 'select',
        'default' => 'manual',
        'options' => [
            'manual' => _x('manual', 'get mode', 'ry-woocommerce-ezpay-invoice'),
            'auto_paid' => _x('auto ( when order paid )', 'get mode', 'ry-woocommerce-ezpay-invoice'),
            'auto_completed' => _x('auto ( when order completed )', 'get mode', 'ry-woocommerce-ezpay-invoice'),
        ],
        'desc' => sprintf(
            /* translators: %s: paid status */
            __('Order paid status: %s', 'ry-woocommerce-ezpay-invoice'),
            $paid_status,
        ),
    ],
    [
        'title' => __('Skip foreign orders', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'skip_foreign_order',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Disable auto get invoice for order billing country and shipping country are not in Taiwan.', 'ry-woocommerce-ezpay-invoice'),
    ],
    [
        'title' => __('Invalid mode', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'invalid_mode',
        'type' => 'select',
        'default' => 'manual',
        'options' => [
            'manual' => _x('manual', 'invalid mode', 'ry-woocommerce-ezpay-invoice'),
            'auto_cancell' => _x('auto ( when order status cancelled OR refunded )', 'invalid mode', 'ry-woocommerce-ezpay-invoice'),
        ],
    ],
    [
        'title' => __('Amount abnormal mode', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'amount_abnormal_mode',
        'type' => 'select',
        'default' => '',
        'options' => [
            '' => _x('No action', 'amount abnormal mode', 'ry-woocommerce-ezpay-invoice'),
            'product' => _x('add one product to match order amount', 'amount abnormal mode', 'ry-woocommerce-ezpay-invoice'),
            'order' => _x('change order total amount', 'amount abnormal mode', 'ry-woocommerce-ezpay-invoice'),
        ],
    ],
    [
        'title' => __('Fix amount product name', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'amount_abnormal_product',
        'type' => 'text',
        'default' => __('Discount', 'ry-woocommerce-ezpay-invoice'),
    ],
    [
        'id' => 'invoice_options',
        'type' => 'sectionend',
    ],
    [
        'title' => __('API credentials', 'ry-woocommerce-ezpay-invoice'),
        'id' => 'api_options',
        'type' => 'title',
    ],
    [
        'title' => __('Sandbox', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'ezpay_invoice_testmode',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Enable ezPay invoice sandbox', 'ry-woocommerce-ezpay-invoice')
            . '<p class="description" style="margin-bottom:2px">' . __('Note: Recommend using this for development purposes only.', 'ry-woocommerce-ezpay-invoice') . '<p>',
    ],
    [
        'title' => __('MerchantID', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'ezpay_MerchantID',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => __('HashKey', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'ezpay_HashKey',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => __('HashIV', 'ry-woocommerce-ezpay-invoice'),
        'id' => RY_WEZI::OPTION_PREFIX . 'ezpay_HashIV',
        'type' => 'text',
        'default' => '',
    ],
    [
        'id' => 'api_options',
        'type' => 'sectionend',
    ],
];
