// trigger append event when append.
(function ($) {
    var origAppend = $.fn.append;

    $.fn.append = function () {
        return origAppend.apply(this, arguments).trigger("append");
    };
})(jQuery);

jQuery(function ($) {
    var dropbox;

    function initRemoveSettings() {
        $('.js-remove-settings').click(function () {
            $('.js-mm4d-input').each(function (i, el) {
                $(el).val('');
            });
            $('#mm4d-form').submit();
        });
    }

    function completeAuth() {
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
                    if (key === 'state') {
                        return true;
                    } // state is nonce
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
    }

    function initDropbox() {
        var accessToken = $('#mm4d-access-token').val();
        if (accessToken) {
            dropbox = new Dropbox({accessToken: 'qMVnmVlROZwAAAAAAADO6yhOibLJue8t8XawDB8tEr7cCY94xJO8R99rjsJ6Dgbe'});
        }
    }

    function drawList(path, dropbox) {
        var $content = $('#TB_ajaxContent');
        var $title = $('#TB_ajaxWindowTitle');
        var template = _.template($('#template-mm4d-li').html());
        dropbox.filesListFolder({path: path})
            .then(function (response) {
                console.log(response);
                if (path) {
                    $title.text(path);
                }
                $content.html('');
                var $ul = $('<ul>');
                _.forEach(response.entries, function (entry) {
                    var extension = entry.name.substr(entry.name.lastIndexOf('.') + 1);
                    if (entry['.tag'] == 'file' && ['txt', 'md', 'markdown', 'mdown'].indexOf(extension) === -1) {
                        return true;
                    }
                    $ul.append(template({
                        name: entry.name,
                        tag: entry['.tag'],
                        id: entry.id
                    }));
                });
                $ul.appendTo($content);
            })
            .catch(function (error) {
                console.log(error);
            });
    }

    function initFirstOpen() {
        $('body').one('append', '#TB_window', function (e) {
            drawList('', dropbox);
            initSelect();
        });
    }

    function initSelect() {
        $('#TB_window').on('click', '.js-mm4d-select', function (e) {
            selectFile(this);
        });
    }

    function selectFile(el) {
        var id = $(el).data('id');
        $('#mm4d-path').val(id);
        $.post(ajaxurl, {
            action: 'mm4d_get_converted_content',
            id: id
        }, function (response) {
            console.log(response);
        }, 'json');
    }

    completeAuth();
    initRemoveSettings();
    initDropbox();
    initFirstOpen();
});