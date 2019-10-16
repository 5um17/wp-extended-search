/* 
    Created on : Sep 20, 2015, 1:07:21 AM
    Author     : 5um17
    Admin page JavaScript
*/

jQuery(document).ready(function (){
    jQuery('select[name="wpessid"]').change(function (){
        var value = jQuery(this).val();
        if (value !== '') {
            window.location.href = "admin.php?page=wp-es&wpessid="+value;
        } else {
            window.location.href = "admin.php?page=wp-es";
        }
    });
});

