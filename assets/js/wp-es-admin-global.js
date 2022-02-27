/**
 * Admin page javascript. Loaded on every admin page.
 * 
 * @author: 5um17
 */

jQuery(document).ready(function () {
    jQuery('#wpes-dismiss-recommendations .notice-dismiss').click(function () {
        jQuery.ajax({
            method: 'GET',
            url: ajaxurl,
            data: { action: 'wpes_dismiss_recommendations' },
            dataType: 'json'
        }).always(function (response) {
            if (!response.hasOwnProperty('data') || !response.data.notice_removed) {
                console.log(response);
                console.error('Can not remove notice. Please contact WPES support.');
            }
        });
    });
});