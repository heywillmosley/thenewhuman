<?php

if (!defined('ABSPATH')) {
    exit;
}

class OrderImpExpXMLBase_Exporter {

    public function do_export($post_type = 'shop_order', $order_IDS = array()) {
        global $wpdb;
        $export_limit = !empty($_POST['limit']) ? intval($_POST['limit']) : 999999999;
        $export_count = 0;
        $limit = 100;
        $export_offset = !empty($_POST['offset']) ? intval($_POST['offset']) : 0;

        if(!empty($_GET['method']))
            $_POST['order_export_type'] = $_GET['method'];

        $export_format = !empty($_POST['order_export_type']) ? $_POST['order_export_type']: 'general';

        $export_order_statuses = !empty($_POST['order_status']) ? $_POST['order_status'] : 'any';
        $end_date   = empty($_POST['end_date']) ? date('Y-m-d 23:59', current_time('timestamp')) : $_POST['end_date'] . ' 23:59:59.99';
        $start_date = empty($_POST['start_date']) ? date('Y-m-d 00:00', 0) : $_POST['start_date'];
        $delimiter  = !empty($_POST['delimiter']) ? $_POST['delimiter'] : ',';


        if ($limit > $export_limit)
            $limit = $export_limit;


        global $order_ids;
        if (empty($order_IDS)) {
            $query_args = array(
                'fields' => 'ids',
                'post_type' => 'shop_order',
                'post_status' => $export_order_statuses,
                'posts_per_page' => $export_limit,
                'offset' => $export_offset,
                'date_query' => array(
                    array(
                        'before' => $end_date,
                        'after' => $start_date,
                        'inclusive' => true,
                    ),
                ),
            );

            $query = new WP_Query($query_args);
           $order_ids = $query->posts;
        } else {
            $order_ids = $order_IDS;
        }

        $filename = 'order_';
        $xmlns = '';
        $xmlns = $this->export_formation($export_format, $order_ids);
        die();
    }
    
    public function export_formation($export_format,$order_ids){
        switch ($export_format) {
            case 'stamps':
                if ( ! class_exists( 'OrderImpExpXMLStamps_Exporter' ) )
                    include_once 'class-OrderImpExpXML-stamps-exporter.php' ;
                $general_exporter_obj = new OrderImpExpXMLStamps_Exporter();
                $general_exporter_obj->generate_xml_stamps($order_ids);
                break;
            case 'general' :
                if ( ! class_exists( 'OrderImpExpXML_GeneralCaseExporter' ) )
                    include_once 'class-OrderImpExpXML-general-case-exporter.php' ;
                $general_exporter_obj = new OrderImpExpXML_GeneralCaseExporter();
                $general_exporter_obj->generate_xml_general_case($order_ids);
                break;
            case 'fedex' :
               if ( ! class_exists( 'OrderImpExpXML_FedexExporter' ) )
                    include_once 'class-OrderImpExpXML-fedex-exporter.php' ;
                $fedex_exporter_obj = new OrderImpExpXML_FedexExporter();
                $fedex_exporter_obj->generate_xml_fedex($order_ids);
                break;
            case 'ups' :
                if ( ! class_exists( 'OrderImpExpXMl_UPSExporter' ) )
                    include_once 'class-OrderImpExpXML-ups-exporter.php' ;
                $ups_exporter_obj = new OrderImpExpXMl_UPSExporter();
                $ups_exporter_obj->generate_xml_ups($order_ids);
                break;
            case 'endicia' :
                if ( ! class_exists( 'OrderImpExpXML_EndiciaExporter' ) )
                    include_once 'class-OrderImpExpXML-endicia-exporter.php' ;
                $endicia_exporter_obj = new OrderImpExpXML_EndiciaExporter();
                $endicia_exporter_obj->generate_xml_endicia($order_ids);
                break;
        }
   }        
}


