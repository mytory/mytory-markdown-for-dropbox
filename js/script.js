jQuery(function ($) {
    if (window.location.hash) {
        var pairs = window.location.hash.substr(1).split('&');
        var result = {};
        _.forEach(pairs, function (pair) {
            pair = pair.split('=');
            result[pair[0]] = decodeURIComponent(pair[1] || '');
        });

        var requiredKeys = [
            'access_token',
            'account_id',
            'state',
            'token_type',
            'uid'
        ];

        var validationResult = _.every(requiredKeys, function (key) {
            return _.has(result, key);
        });

        var $form = $('#mm4d-form');

        if (!validationResult) {
            alert('Error code 1');
        } else {
            _.each(result, function (value, key) {
                if (key === 'state') { return true; } // state is nonce
                var selector = '[name="{{key}}"]'.replace('{{key}}', key);
                $(selector).val(value);
            });

            $form.hide();

            $.post(ajaxurl, {
                'action': 'mm4d_verify_state_nonce',
                'state': result.state
            }, function (response) {
                $form.stop().fadeIn();
                if (response == 'fail') {
                    alert('Error code 2');
                    $form[0].reset();
                } else if (response == 'pass') {
                    alert('Success! It will save form.');
                    $form.submit();
                } else {
                    alert('Error code 3');
                    $form[0].reset();
                }

            });
        }
    }
});