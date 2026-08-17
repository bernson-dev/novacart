$(document).ready(function() {
    function initializeSummernote(element) {
        var $element = $(element);
        var lang = $element.attr('data-lang') || 'en-gb'; // Устанавливаем язык, по умолчанию en-gb

        // Проверяем, нужно ли загружать локализационный файл
        if (lang !== 'en-gb') {
            var scriptUrl = 'view/javascript/summernote/lang/summernote-' + lang + '.min.js';

            $.getScript(scriptUrl)
                .done(function() {
                    setupSummernote($element, lang); // Инициализация после загрузки скрипта
                })
                .fail(function() {
                    console.error("Ошибка загрузки локализации Summernote:", scriptUrl);
                    setupSummernote($element, 'en-gb'); // Если не удалось загрузить, fallback на английский
                });
        } else {
            setupSummernote($element, lang); // Если язык en-gb, сразу инициализируем
        }
    }

    function setupSummernote($element, lang) {
        $element.summernote({
            lang: lang,
            disableDragAndDrop: true,
            height: 300,
            emptyPara: '',
            codemirror: {
                mode: 'text/html',
                htmlMode: true,
                lineNumbers: true,
                lineWrapping: true,
                theme: 'monokai'
            },
            fontSizes: ['8', '9', '10', '11', '12', '13', '14', '16', '18', '20', '24', '30', '36', '48', '64'],
            toolbar: [
                ['style', ['style']],
                ['history', ['undo', 'redo']],
                ['font', ['bold', 'italic', 'underline', 'clear', 'height', 'strikethrough', 'subscript', 'superscript']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'image', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            popover: {
                image: [
                    ['custom', ['imageAttributes']],
                    ['resize', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                    ['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['remove', ['removeMedia']]
                ],
                link: [['link', ['linkDialogShow', 'unlink']]],
                table: [
                    ['add', ['addRowDown', 'addRowUp', 'addColLeft', 'addColRight']],
                    ['delete', ['deleteRow', 'deleteCol', 'deleteTable']]
                ],
            },
            buttons: {
                image: function() {
                    var ui = $.summernote.ui;
                    var button = ui.button({
                        contents: '<i class="note-icon-picture" />',
                        tooltip: $.summernote.lang[lang].image.image,
                        click: function() {
                            $('#modal-image').remove();

                            $.ajax({
                                url: 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token'),
                                dataType: 'html',
                                beforeSend: function() {
                                    $('#button-image i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
                                    $('#button-image').prop('disabled', true);
                                },
                                complete: function() {
                                    $('#button-image i').replaceWith('<i class="fa fa-upload"></i>');
                                    $('#button-image').prop('disabled', false);
                                },
                                success: function(html) {
                                    if ($('#modal-image').length === 0) {
                                        $('body').append('<div id="modal-image" class="modal">' + html + '</div>');
                                    }
                                    $('#modal-image').modal('show');

                                    $('#modal-image').on('click', 'a.thumbnail', function(e) {
                                        e.preventDefault();
                                        $element.summernote('insertImage', $(this).attr('href'));
                                        $('#modal-image').modal('hide');
                                    });
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    alert("Ошибка загрузки изображения: " + textStatus);
                                    $('#button-image i').replaceWith('<i class="fa fa-upload"></i>');
                                    $('#button-image').prop('disabled', false);
                                }
                            });
                        }
                    });

                    return button.render();
                }
            }
        });
    }

    $('[data-toggle="summernote"]').each(function() {
        initializeSummernote(this);
    });

    $(document).on('submit', 'form', function() {
        $('[data-toggle="summernote"]').each(function() {
            if ($(this).summernote('codeview.isActivated')) {
                $(this).summernote('codeview.deactivate');
            }
        });
    });
});
