/* 
    Admin page JavaScript
    Author     : 5um17
*/

// jQuery ready function.
jQuery(document).ready(function (){
    
    // Load the options on setting change.
    jQuery('#wpessid').change(function (){
        var value = jQuery(this).val();
        
        if ( value === 'new' ) {
            window.location.href = wpes_admin_vars.new_setting_url;
        } else if ( value !== '' ) {
            window.location.href = wpes_admin_vars.admin_setting_page + '&wpessid=' + parseInt(value);
        } else {
            window.location.href = wpes_admin_vars.admin_setting_page;
        }
    });
    
    // Save the setting when WC optimization clicked.
    jQuery('#es_wc_search').change(function (){
        var response = confirm( wpes_admin_vars.wc_setting_alert_txt );
        if (response) {
            jQuery('#submit').trigger('click');
        } else {
            jQuery(this).prop('checked', !jQuery(this).prop('checked'));
        }
    });
    
    // Load the jQuery UI datepicker.
    jQuery('#es_exclude_date').datepicker({ 
        maxDate: new Date(),
        changeYear: true,
        dateFormat: "MM dd, yy" 
    });
    
});

