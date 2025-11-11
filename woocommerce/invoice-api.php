<?php

class RY_WEZI_WC_Invoice_Api extends RY_WEZI_ezPay
{
    protected static $_instance = null;

    protected $api_test_url = [
        'get' => 'https://cinv.ezpay.com.tw/Api/invoice_issue',
        'invalid' => 'https://cinv.ezpay.com.tw/Api/invoice_invalid',
    ];

    protected $api_url = [
        'get' => 'https://inv.ezpay.com.tw/Api/invoice_issue',
        'invalid' => 'https://inv.ezpay.com.tw/Api/invoice_invalid',
    ];

    public static function instance(): RY_WEZI_WC_Invoice_Api
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get($order_ID)
    {
        $order = wc_get_order($order_ID);
        if (!$order) {
            return false;
        }

        if ($order->get_meta('_invoice_number')) {
            return false;
        }
        if (empty($order->get_meta('_invoice_type'))) {
            return false;
        }

        list($MerchantID, $HashKey, $HashIV) = RY_WEZI_WC_Invoice::instance()->get_api_info();

        $args = $this->make_get_data($order);
        if (0 == $args['TotalAmt']) {
            $order->update_meta_data('_invoice_number', 'zero');
            $order->save();
            $order->add_order_note(__('Zero total fee without invoice', 'ry-woocommerce-ezpay-invoice'));
            return;
        }
        if (0 > $args['TotalAmt']) {
            $order->update_meta_data('_invoice_number', 'negative');
            $order->save();
            $order->add_order_note(__('Negative total fee can\'t invoice', 'ry-woocommerce-ezpay-invoice'));
            return;
        }

        do_action('ry_wezi_get_invoice', $args, $order);
        $args['ItemName'] = implode('|', $args['ItemName']);
        $args['ItemCount'] = implode('|', $args['ItemCount']);
        $args['ItemPrice'] = implode('|', $args['ItemPrice']);
        $args['ItemUnit'] = implode('|', $args['ItemUnit']);
        $args['ItemAmt'] = implode('|', $args['ItemAmt']);

        RY_WEZI_WC_Invoice::instance()->log('Issue invoice for #' . $order->get_id(), WC_Log_Levels::INFO, ['data' => $args]);

        if (RY_WEZI_WC_Invoice::instance()->is_testmode()) {
            $post_url = $this->api_test_url['get'];
        } else {
            $post_url = $this->api_url['get'];
        }
        $result = $this->link_server($post_url, $args, $MerchantID, $HashKey, $HashIV);

        if (null === $result) {
            return;
        }

        if ($result->Status != 'SUCCESS') {
            $order->add_order_note(sprintf(
                /* translators: %1$s Error messade, %2$s Status code */
                __('Issue invoice error: %1$s (%2$s)', 'ry-woocommerce-ezpay-invoice'),
                $result->Message,
                $result->Status,
            ));
            return;
        }

        $result = @json_decode($result->Result);

        if (apply_filters('ry_wezi_add_api_success_notice', true)) {
            $order->add_order_note(
                __('Invoice number', 'ry-woocommerce-ezpay-invoice') . ': ' . $result->InvoiceNumber . "\n"
                . __('Invoice random number', 'ry-woocommerce-ezpay-invoice') . ': ' . $result->RandomNum . "\n"
                . __('Invoice create time', 'ry-woocommerce-ezpay-invoice') . ': ' . $result->CreateTime,
            );
        }

        $order->update_meta_data('_invoice_number', $result->InvoiceNumber);
        $order->update_meta_data('_invoice_random_number', $result->RandomNum);
        $order->update_meta_data('_invoice_date', $result->CreateTime);
        $order->save();

        do_action('ry_wezi_get_invoice_response', $result, $order);
    }

    protected function make_get_data($order)
    {
        $country = $order->get_billing_country();
        $countries = WC()->countries->get_countries();
        $full_country = ($country && isset($countries[$country])) ? $countries[$country] : $country;

        $state = $order->get_billing_state();
        $states = WC()->countries->get_states($country);
        $full_state = ($state && isset($states[$state])) ? $states[$state] : $state;

        $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));

        $data = [
            'RespondType' => 'JSON',
            'Version' => '1.5',
            'TimeStamp' => $now->getTimestamp(),
            'MerchantOrderNo' => $this->generate_trade_no($order->get_id(), RY_WEZI::get_option('order_prefix', '')),
            'Status' => '1',
            'Category' => 'B2C',
            'CarrierType' => '',
            'LoveCode' => '',
            'PrintFlag' => 'N',
            'Comment' => '#' . $order->get_order_number(),

            'TaxType' => '1',
            'TaxRate' => '5',
            'TotalAmt' => round($order->get_total() - $order->get_total_refunded(), 0),

            'ItemName' => [],
            'ItemCount' => [],
            'ItemPrice' => [],
            'ItemUnit' => [],
            'ItemAmt' => [],

            'BuyerName' => $order->get_billing_last_name() . $order->get_billing_first_name(),
            'BuyerAddress' => $full_country . $full_state . $order->get_billing_city() . $order->get_billing_address_1() . $order->get_billing_address_2(),
            'BuyerEmail' => $order->get_billing_email(),
        ];
        $data['Amt'] = round($data['TotalAmt'] / 1.05);
        $data['TaxAmt'] = $data['TotalAmt'] - $data['Amt'];

        switch ($order->get_meta('_invoice_type')) {
            case 'personal':
                switch ($order->get_meta('_invoice_carruer_type')) {
                    case 'none':
                        $data['PrintFlag'] = 'Y';
                        break;
                    case 'ezpay_host':
                        $data['CarrierType'] = '2';
                        $data['CarrierNum'] = wp_hash($order->get_billing_email());
                        $data['KioskPrintFlag'] = 'yes' === RY_WEZI::get_option('support_kiosk_print', 'no') ? '1' : '';
                        break;
                    case 'MOICA':
                        $data['CarrierType'] = '1';
                        $data['CarrierNum'] = rawurlencode($order->get_meta('_invoice_carruer_no'));
                        break;
                    case 'phone_barcode':
                        $data['CarrierType'] = '0';
                        $data['CarrierNum'] = rawurlencode($order->get_meta('_invoice_carruer_no'));
                        break;
                }
                break;
            case 'company':
                $data['Category'] = 'B2B';
                $data['PrintFlag'] = 'Y';
                $data['BuyerUBN'] = $order->get_meta('_invoice_no');
                $company = $order->get_billing_company();
                if ($company) {
                    $data['BuyerName'] = $company;
                } else {
                    $data['BuyerName'] = $data['BuyerUBN'];
                }
                break;
            case 'donate':
                $data['LoveCode'] = $order->get_meta('_invoice_donate_no');
                break;
        }

        $total_refunded = $order->get_total_refunded();
        $use_sku = 'yes' === RY_WEZI::get_option('use_sku_as_name', 'no');
        $order_items = $order->get_items(['line_item']);
        if (count($order_items)) {
            foreach ($order_items as $order_item) {
                $item_total = $order_item->get_total();
                $item_refunded = $order->get_total_refunded_for_item($order_item->get_id(), $order_item->get_type());
                $total_refunded -= $item_refunded;
                if ('yes' !== get_option('woocommerce_tax_round_at_subtotal')) {
                    $item_total = round($item_total, wc_get_price_decimals());
                    $item_refunded = round($item_refunded, wc_get_price_decimals());
                }

                $item_total = $item_total - $item_refunded;
                $item_qty = $order_item->get_quantity() + $order->get_qty_refunded_for_item($order_item->get_id(), $order_item->get_type());

                if (0 == $item_total && 0 == $item_qty) {
                    continue;
                }

                $item_name = '';
                if ($use_sku && method_exists($order_item, 'get_product')) {
                    $item_name = $order_item->get_product()->get_sku();
                }
                if (empty($item_name)) {
                    $item_name = $order_item->get_name();
                }

                $data['ItemName'][] = $item_name;
                $data['ItemCount'][] = $item_qty == 0 ? 1 : $item_qty;
                $data['ItemAmt'][] = $item_total;
            }
        }

        $fee_items = $order->get_items(['fee']);
        if (count($fee_items)) {
            foreach ($fee_items as $fee_item) {
                $item_total = $fee_item->get_total();
                $item_qty = $fee_item->get_quantity();
                $item_total = round($item_total, wc_get_price_decimals());
                if (0 == $item_total && 0 == $item_qty) {
                    continue;
                }

                $data['ItemName'][] = $fee_item->get_name();
                $data['ItemCount'][] = $item_qty == 0 ? 1 : $item_qty;
                $data['ItemAmt'][] = $item_total;
            }
        }

        $shipping_fee = $order->get_shipping_total() - $order->get_total_shipping_refunded();
        $total_refunded -= $order->get_total_shipping_refunded();
        if ($shipping_fee != 0) {
            $data['ItemName'][] = __('shipping fee', 'ry-woocommerce-ezpay-invoice');
            $data['ItemCount'][] = 1;
            $data['ItemAmt'][] = round($shipping_fee, wc_get_price_decimals());
        }

        if ($total_refunded != 0) {
            $data['ItemName'][] = __('return fee', 'ry-woocommerce-ezpay-invoice');
            $data['ItemCount'][] = 1;
            $data['ItemAmt'][] = round(-$total_refunded, wc_get_price_decimals());
        }

        $total_amount = array_sum($data['ItemAmt']);
        if ($total_amount != $data['TotalAmt']) {
            switch (RY_WEZI::get_option('amount_abnormal_mode', '')) {
                case 'product':
                    $data['ItemName'][] = RY_WEZI::get_option('amount_abnormal_product', __('Discount', 'ry-woocommerce-ezpay-invoice'));
                    $data['ItemCount'][] = 1;
                    $data['ItemAmt'][] = round($data['TotalAmt'] - $total_amount, wc_get_price_decimals());
                    break;
                case 'order':
                    $data['TotalAmt'] = round($total_amount, 0);
                    break;
                default:
                    break;
            }
        }

        foreach ($data['ItemName'] as $key => $item) {
            $item = str_replace('|', '', $item);
            $data['ItemName'][$key] = mb_substr($item, 0, 30);
            $data['ItemAmt'][$key] = round((isset($data['BuyerUBN']) ? ($data['ItemAmt'][$key] / 1.05) : $data['ItemAmt'][$key]), 0);
            $data['ItemCount'][$key] = round($data['ItemCount'][$key], 3);
            $data['ItemPrice'][$key] = round($data['ItemAmt'][$key] / $data['ItemCount'][$key], 2);
            $data['ItemUnit'][$key] = __('parcel', 'ry-woocommerce-ezpay-invoice');
        }

        $data['Comment'] = apply_filters('ry_wezi_invoice_remark', $data['Comment'], $data, $order);
        $data['Comment'] = mb_substr($data['Comment'], 0, 100);

        return $data;
    }

    public function invalid($order_ID)
    {
        $order = wc_get_order($order_ID);
        if (!$order) {
            return false;
        }

        $invoice_number = $order->get_meta('_invoice_number');

        if ('zero' == $invoice_number || 'negative' == $invoice_number) {
            $order->delete_meta_data('_invoice_number');
            $order->save();
            return;
        }

        if (!$invoice_number) {
            return false;
        }

        list($MerchantID, $HashKey, $HashIV) = RY_WEZI_WC_Invoice::instance()->get_api_info();

        $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));

        $args = [
            'RespondType' => 'JSON',
            'Version' => '1.0',
            'TimeStamp' => $now->getTimestamp(),
            'InvoiceNumber' => $invoice_number,
            'InvalidReason' => __('Order cancel', 'ry-woocommerce-ezpay-invoice'),
        ];

        do_action('ry_wezi_invalid_invoice', $args, $order);

        RY_WEZI_WC_Invoice::instance()->log('Invalid invoice for #' . $order->get_id(), WC_Log_Levels::INFO, ['data' => $args]);

        if (RY_WEZI_WC_Invoice::instance()->is_testmode()) {
            $post_url = $this->api_test_url['invalid'];
        } else {
            $post_url = $this->api_url['invalid'];
        }
        $result = $this->link_server($post_url, $args, $MerchantID, $HashKey, $HashIV);

        if (null === $result) {
            return;
        }

        if ($result->Status != 'SUCCESS') {
            $order->add_order_note(sprintf(
                /* translators: %1$s Error messade, %2$s Status code */
                __('Issue invoice error: %1$s (%2$s)', 'ry-woocommerce-ezpay-invoice'),
                $result->Message,
                $result->Status,
            ));
            return;
        }

        $result = @json_decode($result->Result);

        if (apply_filters('ry_wezi_add_api_success_notice', true)) {
            $order->add_order_note(
                __('Invalid invoice', 'ry-woocommerce-ezpay-invoice') . ': ' . $result->InvoiceNumber,
            );
        }

        $order->delete_meta_data('_invoice_number');
        $order->delete_meta_data('_invoice_random_number');
        $order->delete_meta_data('_invoice_date');
        $order->save();

        do_action('ry_wezi_invalid_invoice_response', $result, $order);
    }
}
