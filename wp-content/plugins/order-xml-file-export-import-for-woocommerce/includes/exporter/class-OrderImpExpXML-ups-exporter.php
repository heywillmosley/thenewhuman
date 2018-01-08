<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXMl_UPSExporter {

    public function generate_xml_ups($order_ids){
        
        include_once( 'class-OrderImpExpXML-order-exp-xml-general.php' );
        $export = new OrderImpExpXML_OrderExpXMLGeneral($order_ids);
        $order_details = $export->get_orders($order_ids);
        $data_array = array('Orders' => array('Order' => $order_details));
        $data_array = OrderImpExpXMl_UPSExporter::wf_order_xml_ups_export_format($data_array, $order_details);
        $filename.='ups_xml';
        $xmlns = 'x-schema:OpenShipments.xdr';
        $dt = new DateTime();
        $export->do_xml_export($filename, $export->get_order_details_xml($data_array, $xmlns));
        return $xmlns;
    }
    public function wf_order_xml_ups_export_format($formated_orders, $raw_orders) {
        $order_details = array();
        foreach ($raw_orders as $order) {
            $item = '';
            foreach($order['OrderLineItems'] as $order_items)
            {
                if(isset($order_items['Name']) && $order_items['Name'] !=''){$item .= ', '.$order_items['Name']; }
            }
            if ($order['StoreCountry'] == $order['ShippingCountry']) {
                $ShipmentOption = 'SC';
            }
            else
            {
                $ShipmentOption = '';
            }

            $order_data = array(
                'OpenShipments' => array(
                     '@attributes' => array(
                        'xmlns' => "x-schema:OpenShipments.xdr"
                    ),
                ),
                'OpenShipment' => array(
                    '@attributes' => array(
                     'ShipmentOption' => $ShipmentOption,
                     'ProcessStatus' => ''
                    ),
                ),
                'ShipTo' => array(
                    'CompanyOrName' => $order['ShippingCompany'],
                    'Attention' => 'Receiver',
                    'Address1' => $order['ShippingAddress1'],
                    'CityOrTown' => $order['ShippingCity'],
                    'StateProvinceCounty' => $order['ShippingState'],
                    'PostalCode' => $order['ShippingPostCode'],
                    'Telephone' => $order['BillingPhone'],
                    'EmailAddress1' => $order['BillingEmail']
                ),
                'ShipFrom' => array(
                    'CompanyOrName' => get_user_meta(1, 'billing_company', true),
                    'Attention' => 'Sender',
                    'Address1' => get_user_meta(1, 'billing_address_1', true),
                    'CityOrTown' => get_user_meta(1, 'billing_city', true),
                    'StateProvinceCounty' => get_user_meta(1, 'billing_state', true),
                    'PostalCode' => get_user_meta(1, 'billing_postcode', true),
                    'Telephone' => get_user_meta(1, 'billing_phone', true),
                    'EmailAddress1' => get_option('admin_email')
                ),
                'ShipmentInformation' => array(
                    'ServiceType' => $order['ShippingMethod'],
                    'DescriptionOfGoods' => $item,
                    'GoodsNotInFreeCirculation' => 0,
                    'BillTransportationTo' => 'Shipper'
                ),
                'Package' => array(
                    'PackageType' => 'CP', 
                    'Weight' => $order['OrderLineItems']['total_weight'],
                    'Reference1' => 'OrderId:'.$order['OrderId'].' CustId:'.$order['CustomerId'],
                    'Length' => $order['OrderLineItems']['total_length'],
                    'Width' => $order['OrderLineItems']['total_width'],
                    'Height' => $order['OrderLineItems']['total_height'],
                ),
            );
           $order_details[] = $order_data;
        }
        $formated_orders = array('Print' => array('Item' => $order_details));
        return apply_filters('hf_ups_order_export',$formated_orders);
    }
}


