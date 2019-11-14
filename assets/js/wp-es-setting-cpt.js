/* 
    Author     : 5um17
    Setting CPT JS
*/

jQuery(document).ready(function (){
    jQuery('.wpes-display-input').click(function (){
        jQuery(this).select();
        document.execCommand('copy');
        jQuery(this).parent('td').append('<span class="wpes-copied">Copied</span>');
        jQuery('.wpes-copied').fadeOut(2000);
    });
});
