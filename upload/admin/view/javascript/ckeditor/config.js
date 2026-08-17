/**
* @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
* For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.editorConfig = function(config) {

    // --- Основные настройки ---
    // Устанавливает язык редактора на основе атрибута lang HTML-элемента <html>.
    config.language = document.documentElement.lang || 'en';
    // Устанавливает направление текста редактора на основе атрибута dir HTML-элемента <html>.
    config.contentsLangDirection = document.documentElement.dir || 'ltr';

    // Цвет интерфейса редактора.
    config.uiColor = '#F7F7F7';
    // Высота области редактирования.
    config.height = 400;
    // Отключает проверку версии CKEditor. Рекомендуется.
    config.versionCheck = false;

    // Стили CSS, которые будут применены к содержимому внутри редактора.
    // Если вам нужны специфические стили для содержимого (например, для Bootstrap), добавьте их сюда.
    config.contentsCss = [
        // 'path/to/your/bootstrap.css', // Пример: если хотите использовать стили Bootstrap
        // 'path/to/your/custom.css'     // Пример: ваши собственные стили
    ];

    // --- Настройки контента и безопасности ---
    // Разрешает все HTML-теги и атрибуты. Это необходимо для совместимости с OpenCart.
    config.allowedContent = true;
    // Разрешает дополнительные HTML-теги и атрибуты. '*' означает все.
    config.extraAllowedContent = '*{*}';

    // --- Настройки для загрузки файлов (Файловый менеджер) ---
    // Получение токена пользователя из URL, если функция getURLVar доступна.

    const token = typeof getURLVar === 'function' ? getURLVar('user_token') : '';
    // Базовый URL для файлового менеджера OpenCart.
    const fm    = 'index.php?route=common/filemanager&user_token=' + token;
    // URL для просмотра файлов в файловом менеджере.
    config.filebrowserBrowseUrl      = fm;
    // URL для просмотра изображений в файловом менеджере (с фильтром только для изображений).
    config.filebrowserImageBrowseUrl = fm + '&filter_image=1';
    // URL для загрузки файлов (если ваш файловый менеджер поддерживает загрузку напрямую через CKEditor)
    // config.filebrowserUploadUrl = fm + '&upload=1';
    // URL для загрузки изображений
    // config.filebrowserImageUploadUrl = fm + '&upload=1&type=image';

    // --- Плагины ---
    // Добавление дополнительных плагинов.
    //config.extraPlugins = 'image2,codemirror,clipboard,autogrow';

    // Настройки для Codemirror (если установлен плагин)
    //config.codemirror_theme = 'monokai';
    //config.codemirror_lineNumbers = true; // Отображать номера строк, активно по умоланию
    //config.codemirror_showAutoCompleteButton = true; // Кнопка автозавершения, активно по умоланию

    // Удаление ненужных плагинов. 'image' удален, так как используется 'image2'.
    config.removePlugins = 'autosave,image';

    // --- Настройки тулбара (панели инструментов) ---
    // Определяет группы кнопок на панели инструментов.
    config.toolbarGroups = [
        { name: 'document', groups: ['mode', 'document', 'doctools'] },
        { name: 'clipboard', groups: ['clipboard', 'undo'] },
        { name: 'editing', groups: ['find', 'selection', 'spellchecker'] },
        // { name: 'forms' }, // Раскомментируйте, если нужны кнопки для форм
        '/', // Разделитель
        { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
        { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'] },
        { name: 'links' },
        { name: 'insert' },
        { name: 'styles' },
        { name: 'colors' },
        { name: 'tools' },
        { name: 'others' },
        { name: 'about' }
    ];

    // Настройка кнопок на тулбаре для конкретных групп.
    // Это позволит более точно контролировать, какие кнопки отображаются.
    // Если не указано, CKEditor автоматически добавит стандартные кнопки для каждой группы.
    // Пример для 'insert':
    config.toolbar_Full = [
        { name: 'document', items: ['Source', '-', 'Save', 'NewPage', 'Preview', 'Print', '-', 'Templates'] },
        { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
        { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll', '-', 'Scayt'] },
        { name: 'forms', items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'] },
        '/',
        { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat'] },
        { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language'] },
        { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
        { name: 'insert', items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'] },
        '/',
        { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
        { name: 'colors', items: ['TextColor', 'BGColor'] },
        { name: 'tools', items: ['Maximize', 'ShowBlocks'] },
        { name: 'others', items: ['-'] },
        { name: 'about', items: ['About'] }
    ];
    // Упрощенные диалоговые окна
    config.removeDialogTabs = 'link:advanced;link:target';

    // --- Стили для OpenCart (Bootstrap-совместимые) ---
    // Определяет пользовательские стили, доступные через выпадающий список "Стили".
    config.stylesSet = [
        // Базовые стили для текста
        { name: 'Paragraph', element: 'p' },
        { name: 'Heading 1', element: 'h1' },
        { name: 'Heading 2', element: 'h2' },
        { name: 'Heading 3', element: 'h3' },
        { name: 'Heading 4', element: 'h4' },
        { name: 'Heading 5', element: 'h5' },
        { name: 'Heading 6', element: 'h6' },

        // Стили для изображений
        {
            name: 'Image Responsive',
            element: 'img',
            attributes: { 'class': 'img-responsive' }
        },
        {
            name: 'Image Left',
            element: 'img',
            attributes: { 'class': 'img-responsive img-left' } // Добавим img-responsive по умолчанию
        },
        {
            name: 'Image Right',
            element: 'img',
            attributes: { 'class': 'img-responsive img-right' } // Добавим img-responsive по умолчанию
        },

        // Стили для таблиц
        {
            name: 'Table',
            element: 'table',
            attributes: { 'class': 'table table-bordered' }
        },
        {
            name: 'Table Striped',
            element: 'table',
            attributes: { 'class': 'table table-striped' }
        },
        {
            name: 'Table Hover',
            element: 'table',
            attributes: { 'class': 'table table-hover' }
        },
        {
            name: 'Table Bordered',
            element: 'table',
            attributes: { 'class': 'table table-bordered' }
        },
        {
            name: 'Table Condensed',
            element: 'table',
            attributes: { 'class': 'table table-condensed' }
        },

        // Дополнительные стили текста (Bootstrap)
        { name: 'Highlight', element: 'span', attributes: { 'class': 'text-danger' } },
        { name: 'Small Text', element: 'small' },
        { name: 'Muted Text', element: 'span', attributes: { 'class': 'text-muted' } },
        { name: 'Primary Text', element: 'span', attributes: { 'class': 'text-primary' } },
        { name: 'Success Text', element: 'span', attributes: { 'class': 'text-success' } },
        { name: 'Info Text', element: 'span', attributes: { 'class': 'text-info' } },
        { name: 'Warning Text', element: 'span', attributes: { 'class': 'text-warning' } },
        // Стили для блоков
        { name: 'Well (Bootstrap)', element: 'div', attributes: { 'class': 'well' } },
        { name: 'Alert Info', element: 'div', attributes: { 'class': 'alert alert-info' } },
        { name: 'Alert Success', element: 'div', attributes: { 'class': 'alert alert-success' } },
        { name: 'Alert Warning', element: 'div', attributes: { 'class': 'alert alert-warning' } },
        { name: 'Alert Danger', element: 'div', attributes: { 'class': 'alert alert-danger' } }
    ];

    // --- Другие настройки ---
    // Автоматическое обновление связанного элемента формы при изменении содержимого редактора.
    //config.autoUpdateElement = true;

    // Настройки для вставки из Word (очистка стилей)
    config.pasteFromWordRemoveFontStyles = true;
    config.pasteFromWordRemoveStyles = true;
    config.pasteFilter = 'semantic-content'; // Сохраняет только семантически значимый контент.

    // Отключение автоформатирования пустых блоков и параграфов.
    config.autoParagraph = false;
    config.fillEmptyBlocks = false;

    // отключение встроенной проверки орфографии
    config.disableNativeSpellChecker = false; // Будет работать стандартная проверка в бразере. Активация контекстного меню браузера ПКМ при нажатой CTRL

    // Настройки для адаптивного режима (изменение размера редактора)
    config.resize_enabled = true;
    config.resize_dir = 'vertical'; // Разрешает изменение размера только по вертикали.
    config.resize_minWidth = 250; // Минимальная ширина
    config.resize_minHeight = 250; // Минимальная высота
};

// --- Обработчики событий CKEditor ---
CKEDITOR.on('instanceReady', function(evt) {
    var editor = evt.editor;

    editor.dataProcessor.htmlFilter.addRules({
        elements: {
            img: function(el) {
                if (!el.hasClass('img-responsive')) {
                    el.addClass('img-responsive');
                }
                el.attributes.alt = el.attributes.alt || '';
                return el;
            },
            table: function(el) {
                // добавляем класс, если нет
                ['table','table-striped','table-hover','table-bordered','table-condensed']
                .forEach(cls => el.hasClass(cls) || el.addClass(cls));

                // переписываем width
                let s = (el.attributes.style || '').replace(/width\s*:[^;]+;?/gi,'').trim();
                s = (s ? s + '; ' : '') + 'width: 100%;';
                el.attributes.style = s;
                return el;
            }
        }
    });

    editor.on('insertElement', function(evt) {
        var el = evt.data;
        if (el.is('img')) {
            el.hasClass('img-responsive') || el.addClass('img-responsive');
        }
        if (el.is('table')) {
            ['table','table-striped','table-hover','table-bordered','table-condensed']
            .forEach(cls => el.hasClass(cls) || el.addClass(cls));
            let s = (el.getAttribute('style') || '').replace(/width\s*:[^;]+;?/gi,'').trim();
            s = (s ? s + '; ' : '') + 'width: 100%;';
            el.setAttribute('style', s);
        }
    });
});
