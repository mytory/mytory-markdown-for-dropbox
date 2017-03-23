jQuery(function ($) {
    var dropbox, modal;

    function initRevoke() {
        $('.js-mm4d-revoke').click(function () {
            if (!confirm('Really?')) {
                return false;
            }
            dropbox.authTokenRevoke()
                .then(function (response) {

                })
                .catch(function (data) {
                    alert(data.response.body.error_summary);
                });

            $.post(ajaxurl, {
                action: 'mm4d_delete_options'
            }, function () {
                location.reload();
            });
        });
    }

    function initDropbox() {
        var accessToken = $('#mm4d_access_token').val();
        if (accessToken) {
            dropbox = new Dropbox({accessToken: accessToken});
        }
    }

    function initModal() {
        var $remodal = $('[data-remodal-id=modal]');
        if ($remodal.length > 0) {
            modal = $remodal.remodal();
        }
    }

    function drawList(path, dropbox) {
        var $content = $('.js-dropbox-list-content');
        var $title = $('.js-dropbox-list-title');
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
                        id: entry.id,
                        path: entry.path_display
                    }));
                });
                $ul.appendTo($content);
            })
            .catch(function (error) {
                console.log(error);
            });
    }

    function initDropboxListOpen() {
        $('.js-open-dropbox-list').click(function () {
            drawList('', dropbox);
            modal.open();
        });
    }

    function initSelect() {
        $('.js-dropbox-list').on('click', '.js-mm4d-select', function (e) {
            setConvertedContent(this);
            modal.close();
        });
    }

    function setConvertedContent(el) {
        var id = $(el).data('id'),
            path = $(el).data('path');
        $('#mm4d-path').val(path);
        $('#mm4d-id').val(id);
        $.post(ajaxurl, {
            action: 'mm4d_get_converted_content',
            id: id
        }, function (response) {
            if (typeof response['is_error'] != 'undefined' && response['is_error'] == true) {
                alert(response.msg);
            } else {
                setContent(response);
            }
        }, 'json');
    }

    function setContent(obj) {
        if (obj.post_title) {
            $('#title').val(obj.post_title);
            $('#title-prompt-text').addClass('screen-reader-text');
        }
        if ($('#content').is(':visible')) {

            // text mode
            $('#content').val(obj.post_content);
        } else {

            // wysiwyg mode
            if (tinymce.getInstanceById) {
                tinymce.getInstanceById('content').setContent(obj.post_content);
            } else {
                tinymce.get('content').setContent(obj.post_content);
            }
        }
    }

    initRevoke();
    initDropbox();
    initModal();
    initDropboxListOpen();
    initSelect();
});