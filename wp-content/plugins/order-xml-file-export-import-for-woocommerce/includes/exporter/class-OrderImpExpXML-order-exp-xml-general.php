<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXML_OrderExpXMLGeneral extends XMLWriter {

    private $ids;

    public function __construct($ids) {

        $this->ids = $ids;
        $this->openMemory();
        $this->setIndent(TRUE);
        $xml_version = '1.0';
        $xml_encoding = 'UTF-8';
        $xml_standalone = 'no';
        $this->startDocument($xml_version, $xml_encoding, $xml_standalone);
    }

    public function do_xml_export($filename, $xml) {



        global $wpdb;

        $settings = get_option('woocommerce_' . WF_ORDER_IMP_EXP_XML_ID . '_settings', null);

        $wpdb->hide_errors();
        @set_time_limit(0);
        if (function_exists('apache_setenv'))
            @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 0);
        @ob_clean();


        $charset = get_option('blog_charset');
        header(apply_filters('hf_order_import_export_xml_content_type', "Content-Type: application/xml; charset={$charset}"));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $filename.".xml"));
        header('Pragma: no-cache');
        header('Expires: 0');
        if (version_compare(PHP_VERSION, '5.6', '<')) {
            iconv_set_encoding('output_encoding', $charset);
        } else {
            ini_set('default_charset', 'UTF-8');
        }

        echo $xml;
        exit;
    }

    public function get_order_details_xml($data_array, $xmlns = NULL) {
        $xmlnsurl = $xmlns;
        $keys = array_keys($data_array);
        $root_tag = reset($keys);
        OrderImpExpXML_OrderExpXMLGeneral::array_to_xml($this, $root_tag, $data_array[$root_tag], $xmlnsurl);
        return $this->output_xml();
    }

    public static function array_to_xml($xml_writer, $element_key, $element_value = array(), $xmlnsurl = NULL) {

        if (!empty($xmlnsurl)) {
            $my_root_tag = $element_key;
            $xml_writer->startElementNS(null, $element_key, $xmlnsurl);
        } else {
            $my_root_tag = '';
        }

        if (is_array($element_value)) {
            if ('@attributes' === $element_key) {
                foreach ($element_value as $attribute_key => $attribute_value) {

                    $xml_writer->startAttribute($attribute_key);
                    $xml_writer->text($attribute_value);
                    $xml_writer->endAttribute();
                }
                return;
            }

            if (is_numeric(key($element_value))) {

                foreach ($element_value as $child_element_key => $child_element_value) {

                    if ($element_key !== $my_root_tag)
                        $xml_writer->startElement($element_key);
                    foreach ($child_element_value as $sibling_element_key => $sibling_element_value) {
                        self::array_to_xml($xml_writer, $sibling_element_key, $sibling_element_value);
                    }

                    $xml_writer->endElement();
                }
            } else {

                if ($element_key !== $my_root_tag)
                    $xml_writer->startElement($element_key);

                foreach ($element_value as $child_element_key => $child_element_value) {
                    self::array_to_xml($xml_writer, $child_element_key, $child_element_value);
                }

                $xml_writer->endElement();
            }
        } else {

            if ('@value' == $element_key) {

                $xml_writer->text($element_value);
            } else {

                if (false !== strpos($element_value, '<') || false !== strpos($element_value, '>')) {

                    $xml_writer->startElement($element_key);
                    $xml_writer->writeCdata($element_value);
                    $xml_writer->endElement();
                } else {

                    $xml_writer->writeElement($element_key, $element_value);
                }
            }

            return;
        }
    }

    private function output_xml() {
        $this->endDocument();
        return $this->outputMemory();
    }

    public function get_orders($order_ids) {


        $order_data = array();
        $wc_countries = new WC_Countries();
        $base_country = $wc_countries->get_base_country();
        foreach ($order_ids as $order_id) {

            $order = wc_get_order($order_id);

            $shipping_methods = $shipping_methods_ids = array();

            foreach ($order->get_shipping_methods() as $method) {

                $shipping_methods[] = $method['name'];
                $shipping_methods_ids[] = $method['method_id'];
            }

            $fee_total = 0;
            foreach ($order->get_fees() as $fee_id => $fee) {
                $fee_total += $fee['line_total'];
            }
            $order_data[] = apply_filters('hf_order_import_export_xml_format', array(
                'OrderId' => ( WC_VERSION < '3.0' ) ? $order->id : $order->get_id(),
                'OrderNumber' => $order->get_order_number(),
                'OrderDate' => ( WC_VERSION < '3.0' ) ? $order->order_date : $order->get_date_created(),
                'OrderStatus' => $order->get_status(),
                'BillingFirstName' => ( WC_VERSION < '3.0' ) ? $order->billing_first_name : $order->get_billing_first_name(),
                'BillingLastName' => ( WC_VERSION < '3.0' ) ? $order->billing_last_name : $order->get_billing_last_name(),
                'BillingFullName' => ( ( WC_VERSION < '3.0' ) ? $order->billing_first_name : $order->get_billing_first_name() ) . ' ' . ( ( WC_VERSION < '3.0' ) ? $order->billing_last_name : $order->get_billing_last_name() ),
                'BillingCompany' => ( WC_VERSION < '3.0' ) ? $order->billing_company : $order->get_billing_company(),
                'BillingAddress1' => ( WC_VERSION < '3.0' ) ? $order->billing_address_1 : $order->get_billing_address_1(),
                'BillingAddress2' => ( WC_VERSION < '3.0' ) ? $order->billing_address_2 : $order->get_billing_address_2(),
                'BillingCity' => ( WC_VERSION < '3.0' ) ? $order->billing_city : $order->get_billing_city(),
                'BillingState' => ( WC_VERSION < '3.0' ) ? $order->billing_state : $order->get_billing_state(),
                'BillingPostCode' => ( WC_VERSION < '3.0' ) ? $order->billing_postcode : $order->get_billing_postcode(),
                'BillingCountry' => ( WC_VERSION < '3.0' ) ? $order->billing_country : $order->get_billing_country(),
                'BillingPhone' => ( WC_VERSION < '3.0' ) ? $order->billing_phone : $order->get_billing_phone(),
                'BillingEmail' => ( WC_VERSION < '3.0' ) ? $order->billing_email : $order->get_billing_email(),
                'ShippingFirstName' => ( WC_VERSION < '3.0' ) ? $order->shipping_first_name : $order->get_shipping_first_name(),
                'ShippingLastName' => ( WC_VERSION < '3.0' ) ? $order->shipping_last_name : $order->get_shipping_last_name(),
                'ShippingFullName' => ( ( WC_VERSION < '3.0' ) ? $order->shipping_first_name : $order->get_shipping_first_name() ) . ' ' . ( ( WC_VERSION < '3.0' ) ? $order->shipping_last_name :$order->get_shipping_last_name() ),
                'ShippingCompany' => ( WC_VERSION < '3.0' ) ? $order->shipping_company : $order->get_shipping_company(),
                'ShippingAddress1' => ( WC_VERSION < '3.0' ) ? $order->shipping_address_1 : $order->get_shipping_address_1(),
                'ShippingAddress2' => ( WC_VERSION < '3.0' ) ? $order->shipping_address_2 : $order->get_shipping_address_2(),
                'ShippingCity' => ( WC_VERSION < '3.0' ) ? $order->shipping_city : $order->get_shipping_city(),
                'ShippingState' => ( WC_VERSION < '3.0' ) ? $order->shipping_state : $order->get_shipping_state(),
                'ShippingPostCode' => ( WC_VERSION < '3.0' ) ? $order->shipping_postcode : $order->get_shipping_postcode(),
                'ShippingCountry' => ( WC_VERSION < '3.0' ) ? $order->shipping_country : $order->get_shipping_country(),
                'ShippingMethodId' => implode(',', $shipping_methods_ids),
                'ShippingMethod' => implode(', ', $shipping_methods),
                'PaymentMethodId' => ( WC_VERSION < '3.0' ) ? $order->payment_method : $order->get_payment_method(),
                'PaymentMethod' => ( WC_VERSION < '3.0' ) ? $order->payment_method_title : $order->get_payment_method_title(),
                'OrderDiscountTotal' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? $order->get_total_discount() : $order->get_order_discount(),
                'CartDiscountTotal' => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? $order->get_total_discount() : $order->get_cart_discount(),
                'DiscountTotal' => $order->get_total_discount(),
                'ShippingTotal' => $order->get_total_shipping(),
                'ShippingTaxTotal' => $order->get_shipping_tax(),
                'OrderTotal' => $order->get_total(),
                'FeeTotal' => $fee_total,
                'TaxTotal' => $order->get_total_tax(),
                'CompletedDate' => ( WC_VERSION < '3.0' ) ? $order->completed_date : $order->get_date_completed(),
                'CustomerNote' => ( WC_VERSION < '3.0' ) ? $order->customer_note : $order->get_customer_note(),
                'CustomerId' => $order->get_user_id(),
                'OrderLineItems' => $this->get_line_items($order),
                'StoreCountry' => $base_country
                    ), $order);
        }
        return $order_data;
    }

    private function get_line_items($order) {

        $items = array();

        $weight = 0;
        $length = 0;
        $width = 0;
        $height = 0; 
        $qty = 0;
        $weight_unit = get_option('woocommerce_weight_unit'); 

        foreach ($order->get_items() as $item_id => $item) {

            $item['id'] = $item_id;

            if (isset($item['type']) && 'line_item' !== $item['type']) {
                continue;
            }
            $product = $order->get_product_from_item($item);

            $item_meta = new WC_Order_Item_Meta((defined('WC_VERSION') && (WC_VERSION >= 2.4)) ? $item : $item['item_meta'] );
            $item_meta = $item_meta->display(true, true);

            $item_meta = preg_replace('/<[^>]*>/', ' ', $item_meta);
            $item_meta = str_replace(array("\r", "\n", "\t"), '', $item_meta);
            $item_meta = strip_tags($item_meta);


            if (!empty($product) && !$product->is_virtual()) {
                $weight += $product->get_weight() * $item['qty'];
            }

            if (!empty($product) && !$product->is_virtual()) {
                $length += ( ( WC_VERSION < '3.0' ) ? $product->length : $product->get_length() ) * $item['qty'];
            }

            if (!empty($product) && !$product->is_virtual()) {
                $height += ( ( WC_VERSION < '3.0' ) ? $product->height : $product->get_height() ) * $item['qty'];
            }

            if (!empty($product) && !$product->is_virtual()) {
                $width += ( ( WC_VERSION < '3.0' ) ? $product->width : $product->get_width ) * $item['qty'];
            }

            $qty+=$item['qty'];

            $item_format = array();
            $item_format['SKU'] = $product ? $product->get_sku() : '';
            $item_format['ExternalID'] = $product ? ( ( WC_VERSION < '3.0' ) ? $product->id : $product->get_id() ) : 0;
            $item_format['Name'] = html_entity_decode($product ? $product->get_title() : $item['name'], ENT_NOQUOTES, 'UTF-8');
            $item_format['Price'] = $order->get_item_total($item);
            $item_format['Quantity'] = $item['qty'];
            $item_format['Total'] = $item['line_total'];

            if ('yes' === get_option('woocommerce_calc_taxes') && 'yes' === get_option('woocommerce_prices_include_tax')) {
                $item_format['PriceInclTax'] = $order->get_item_total($item, true);
                $item_format['LineTotalInclTax'] = $item['line_total'] + $item['line_tax'];
            }

            $item_format['Meta'] = $item_meta;

            $items[] = apply_filters('hf_order_stamps_xml_export_line_item_format', $item_format, $order, $item);
        }
        $items['total_weight']  = $weight;
        $items['total_qty']     = $qty;
        $items['weight_unit']   = $weight_unit;
        $items['total_height']  = $height;
        $items['total_width']   = $width;
        $items['total_length']  = $length;
        return $items;
    }

    

}
