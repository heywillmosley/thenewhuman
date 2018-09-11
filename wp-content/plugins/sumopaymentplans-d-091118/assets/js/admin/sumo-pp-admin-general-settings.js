jQuery( function ( $ ) {

    $( '#_sumo_pp_selected_plans' ).select2() ;
    $( '#_sumo_pp_hide_payment_plans_only_for' ).select2() ;
    $( '#_sumo_pp_disabled_payment_gateways' ).select2() ;
    $( '#_sumo_pp_disabled_wc_order_emails' ).select2() ;

    $( '#_sumo_pp_min_deposit' ).closest( 'tr' ).hide() ;
    $( '#_sumo_pp_max_deposit' ).closest( 'tr' ).hide() ;
    $( '#_sumo_pp_fixed_deposit_percent' ).closest( 'tr' ).show() ;

    if ( 'user-defined' === $( '#_sumo_pp_deposit_type' ).val() ) {
        $( '#_sumo_pp_min_deposit' ).closest( 'tr' ).show() ;
        $( '#_sumo_pp_max_deposit' ).closest( 'tr' ).show() ;
        $( '#_sumo_pp_fixed_deposit_percent' ).closest( 'tr' ).hide() ;
    }

    $( '#_sumo_pp_deposit_type' ).on( 'change' , function () {
        $( '#_sumo_pp_min_deposit' ).closest( 'tr' ).hide() ;
        $( '#_sumo_pp_max_deposit' ).closest( 'tr' ).hide() ;
        $( '#_sumo_pp_fixed_deposit_percent' ).closest( 'tr' ).show() ;

        if ( 'user-defined' === this.value ) {
            $( '#_sumo_pp_min_deposit' ).closest( 'tr' ).show() ;
            $( '#_sumo_pp_max_deposit' ).closest( 'tr' ).show() ;
            $( '#_sumo_pp_fixed_deposit_percent' ).closest( 'tr' ).hide() ;
        }
    } ) ;
} ) ;