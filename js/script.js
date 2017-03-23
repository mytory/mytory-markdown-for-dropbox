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
            modal = $remodal.remodal({
                hashTracking: false
            });
        }
    }

    function drawList(path, dropbox) {
        var $container = $('.js-dropbox-list');
        var $content = $('.js-dropbox-list-content');
        var $title = $('.js-dropbox-list-title');
        var template = _.template($('#template-mm4d-li').html());

        $container.addClass('translucent');

        dropbox.filesListFolder({path: path})
            .then(function (response) {
                $title.text(path);
                $content.html('');
                var $ul = $('<ul>');

                if (path.length > 1) {
                    $ul.append(template({
                        name: '..',
                        tag: 'folder',
                        id: '',
                        path: getParentPath(path),
                        rev: ''
                    }));
                }

                _.forEach(response.entries, function (entry) {
                    var extension = entry.name.substr(entry.name.lastIndexOf('.') + 1);
                    if (entry['.tag'] == 'file' && $('#mm4d_extensions').val().split(',').indexOf(extension) === -1) {
                        return true;
                    }
                    $ul.append(template({
                        name: entry.name,
                        tag: entry['.tag'],
                        id: entry.id,
                        path: entry.path_display,
                        rev: entry.rev
                    }));
                });
                $ul.appendTo($content);
                $container.removeClass('translucent');
            })
            .catch(function (data) {
                console.log(data);
            });
    }

    function getParentPath(path) {
        var pathArray = _.filter(path.split('/'));
        pathArray.pop();
        if (pathArray.length == 0) {
            return '';
        } else {
            return '/' + pathArray.join('/');
        }
    }

    function initDropboxListOpen() {
        $('.js-open-dropbox-list').click(function () {
            var $content = $('.js-dropbox-list-content');
            if (!$.trim($content.text())) {
                drawList('', dropbox);
            }
            modal.open();
        });
    }

    function initSelectFile() {
        $('.js-dropbox-list').on('click', '.js-mm4d-select-file', function (e) {
            var obj = {
                id: $(this).data('id'),
                path: $(this).data('path'),
                rev: $(this).data('rev')
            };
            setConvertedContent(obj);
            setFileMetadata(obj);
            modal.close();
        });
    }

    function initChangeDirectory() {
        $('.js-dropbox-list').on('click', '.js-mm4d-change-directory', function (e) {
            drawList($(this).data('path'), dropbox);
        });
    }

    function initUpdate() {
        $('.js-mm4d-update').on('click', function (e) {
            var obj = {
                id: $('#mm4d-id').val(),
                path: $('#mm4d-path').val(),
                rev: $('#mm4d-rev').val()
            };

            dropbox.filesAlphaGetMetadata({
                path: obj.id || obj.path,
                include_media_info: false,
                include_deleted: false,
                include_has_explicit_shared_members: false
            }).then(function (file) {
                if (file.rev != obj.rev) {
                    setFileMetadata({
                        id: file.id,
                        path: file.path_display,
                        rev: file.rev
                    });
                    setConvertedContent(obj);
                } else {
                    alert('the file has not modified.');
                }
            }).catch(function (data) {
                alert(data.response.body.error_summary);
            });

        });
    }

    function setConvertedContent(obj) {
        $.post(ajaxurl, {
            action: 'mm4d_get_converted_content',
            id: obj.id
        }, function (response) {
            if (typeof response['is_error'] != 'undefined' && response['is_error'] == true) {
                alert(response.msg);
            } else {
                setContent(response);
            }
        }, 'json');
    }

    function setFileMetadata(obj) {
        $('#mm4d-path').val(obj.path);
        $('#mm4d-id').val(obj.id);
        $('#mm4d-rev').val(obj.rev);
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
    initSelectFile();
    initChangeDirectory();
    initUpdate();
});