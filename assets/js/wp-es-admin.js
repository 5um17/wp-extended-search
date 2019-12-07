/* 
    Created on : Sep 20, 2015, 1:07:21 AM
    Author     : 5um17
    Admin page JavaScript
*/

jQuery(document).ready(function (){
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
    
    jQuery('#es_exclude_date').datepicker({ 
        maxDate: new Date(),
        changeYear: true,
        dateFormat: "MM dd, yy" 
    });
});

