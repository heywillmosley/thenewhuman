jQuery(document).ready(function(a) {
    "use strict";
     a("#v_start_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_order_xml_import_params.calendar_icon,
        buttonImageOnly: !0
    }),a("#v_end_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_order_xml_import_params.calendar_icon,
        buttonImageOnly: !0
    })
});