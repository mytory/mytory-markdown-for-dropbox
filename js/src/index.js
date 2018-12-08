var Dropbox = require('dropbox').Dropbox;

// ==== admin ====
jQuery(function ($) {
    var dropbox, modal;

    function detectGutenbergEditor() {
        if (!document.querySelector('#title')) {
            document.querySelector('#mm4d .inside')
                .innerHTML = '<p><i>Mytory Markdown for Dropbox</i> is not yet support Gutenberg Editor. ' +
                'Please use <a href="https://wordpress.org/plugins/classic-editor/">Classic Editor</a> ' +
                'before support that.</p>';
        }
    }

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

    function drawList(path, dropbox, cursor) {
        var $container = $('.js-dropbox-list');
        var $content = $('.js-dropbox-list-content');
        var $title = $('.js-dropbox-list-title');
        var template = _.template($('#template-mm4d-li').html());
        var filesResultFolderList;

        $container.addClass('translucent');

        if (!cursor) {
            filesResultFolderList = dropbox.filesListFolder({path: path});
        } else {
            filesResultFolderList = dropbox.filesListFolderContinue({cursor: cursor});
        }

        filesResultFolderList
            .then(function (response) {
                $title.text(path);

                if (!cursor) {
                    $content.html('');
                }
                var $ul = $('<ul>');

                if (path && path.length > 1) {
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
                    if (entry['.tag'] === 'file' && $('#mm4d_extensions').val().split(',').indexOf(extension) === -1) {
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

                if (response.has_more) {
                    $('<button>', {
                        type: 'button',
                        text: 'more',
                        'data-cursor': response.cursor,
                        'class': 'js-mm4d-load-more'
                    }).appendTo($content);
                }

                $container.removeClass('translucent');
            })
            .catch(function (data) {
                alert(data.response.body.error_summary);
            });
    }

    function getParentPath(path) {
        var pathArray = _.filter(path.split('/'));
        pathArray.pop();
        if (pathArray.length === 0) {
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
        var $dropboxList = $('.js-dropbox-list');
        $dropboxList.on('click', '.js-mm4d-select-file', function (e) {
            var obj = {
                id: $(this).data('id'),
                path: $(this).data('path'),
                rev: $(this).data('rev')
            };
            $dropboxList.addClass('translucent');
            setConvertedContent(obj)
                .done(function() {
                    setFileMetadata(obj);
                    modal.close();
                    $dropboxList.removeClass('translucent');
                });

        });
    }

    function initChangeDirectory() {
        $('.js-dropbox-list').on('click', '.js-mm4d-change-directory', function (e) {
            drawList($(this).data('path'), dropbox);
        });
    }

    function initLoadMore() {
        $('.js-dropbox-list').on('click', '.js-mm4d-load-more', function (e) {
            $(this).remove();
            drawList(null, dropbox, $(this).data('cursor'));
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
                path: obj.path,
                include_media_info: false,
                include_deleted: false,
                include_has_explicit_shared_members: false
            }).then(function (file) {
                if (file.rev !== obj.rev) {
                    setFileMetadata({
                        id: file.id,
                        path: file.path_display,
                        rev: file.rev
                    });
                    $('.js-mm4d-loading').removeClass('hidden');
                    setConvertedContent(obj)
                        .done(function () {
                            $('.js-mm4d-loading').addClass('hidden');
                        });
                } else {
                    alert('The file has not modified.');
                }
            }).catch(function (data) {
                alert(data.response.body.error_summary);
            });

        });
    }

    function setConvertedContent(obj) {
        return $.post(ajaxurl, {
            action: 'mm4d_get_converted_content',
            path: obj.path
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

    detectGutenbergEditor();
    initRevoke();
    initDropbox();
    initModal();
    initDropboxListOpen();
    initSelectFile();
    initChangeDirectory();
    initLoadMore();
    initUpdate();
});


// ==== client ====
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