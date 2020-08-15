/* 
    Setting CPT JS
    Author     : 5um17
*/

// jQuery ready function.
jQuery(document).ready(function (){
    
    // Select and copy the code to clipboard.
    jQuery('.wpes-display-input').click(function (){
        jQuery(this).select();
        document.execCommand('copy');
        jQuery(this).parent('td').append('<span class="wpes-copied">'+ wpes_admin_cpt_vars.str_copy +'</span>');
        jQuery('.wpes-copied').fadeOut(2000, function (){
            jQuery(this).remove();
        });
    });
    
});
