jQuery(document).ready(function(a) {
    "use strict";
     a("#v_start_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_order_csv_import_params.calendar_icon,
        buttonImageOnly: !0
    }),a("#v_end_date").datepicker({
        dateFormat: "yy-mm-dd",
        numberOfMonths: 1,
        showButtonPanel: !0,
        showOn: "button",
        buttonImage: woocommerce_order_csv_import_params.calendar_icon,
        buttonImageOnly: !0
    }),
        a("#ord_enable_ftp_ie").click(function () {
        if (this.checked) {
            a("#ord_export_section_all").show();
        }else{
            a("#ord_export_section_all").hide();
        }
    });
    a("select[name=ord_auto_export]").change(function() {
        if("Disabled" === a(this).val()){
            a(".ord_export_section").hide();
        }else{
            a(".ord_export_section").show();
        }
    });
    
    if(woocommerce_order_csv_cron_params.ord_enable_ftp_ie != 1){
        a("#ord_export_section_all").hide();
    };
    if(woocommerce_order_csv_cron_params.ord_auto_export === 'Disabled'){
        a(".ord_export_section").hide();
    };
    a("select[name=ord_auto_import]").change(function() {
        if("Disabled" === a(this).val()){
            a(".ord_import_section").hide();
        }else{
            a(".ord_import_section").show();
        }
    })
    if(woocommerce_order_csv_cron_params.ord_auto_import === 'Disabled'){
        a(".ord_import_section").hide();
    }
        // Listen for click on toggle checkbox
        a('#selectall').click(function(event) {   
                // Iterate each checkbox
               a(':checkbox').each(function() {
                    this.checked = true;
                });
        });
        a('#unselectall').click(function(event) {   
                // Iterate each checkbox
               a(':checkbox').each(function() {
                    this.checked = false;
                });
        });
});