jQuery(function ($) {
    $('#wp-admin-bar-mm4d-update a').click(function (e) {
        var ajaxurl = $(this).attr('href'),
            that = this;
        e.preventDefault();
        $(this).data('text', $(this).text());
        $(this).text('Loading...');
        $.post(ajaxurl, {
            action: 'mm4d_update_in_client',
            post_id: $(this).attr('rel')
        }, function (response) {
            console.log(response);
            if (response.result === 'fail') {
                alert(response.message);
                $(that).text($(that).data('text'));
            }
            if (response.result === 'success') {
                location.reload();
            }
        }, 'json')
    });
});