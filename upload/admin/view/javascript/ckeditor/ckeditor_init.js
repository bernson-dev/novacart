(function($) {
  'use strict';

  // Конфигурация по умолчанию, настройки имееют больший приоритет чем config.js
  // при совпадении заменяют из config.js
  const defaultConfig = {
    height: 500,
    //filebrowserBrowseUrl: '',
    //filebrowserImageBrowseUrl: ''
  };

  // Инициализация одного textarea
  function initCkeditorInstance($textarea, user_token) {
    if ($textarea.data('cke-instance') || typeof CKEDITOR === 'undefined') {
      return;
    }

    let id = $textarea.attr('id');
    if (!id) {
      id = `cke-${Math.random().toString(36).substr(2, 9)}`;
      $textarea.attr('id', id);
    }

    const config = {
      ...defaultConfig,
      height: parseInt($textarea.data('height')) || defaultConfig.height,
      //filebrowserBrowseUrl: `index.php?route=common/filemanager&user_token=${user_token}`,
      //filebrowserImageBrowseUrl: `index.php?route=common/filemanager&user_token=${user_token}&filter_image=1`,
    };

    try {
      CKEDITOR.replace(id, config);

      CKEDITOR.on('dialogDefinition', function(ev) {
        const contents = ev.data.definition.contents;

        for (let i = 0; i < contents.length; i++) {
          const browseButton = contents[i].get('browse');
          if (browseButton) {
            browseButton.hidden = false;
            browseButton.onClick = function() {
              const target = this.filebrowser.target;
              $('#modal-image').remove();

              $.ajax({
                url: `index.php?route=common/filemanager&ckeditor=${target}&user_token=${user_token}`,
                dataType: 'html'
              })
              .done(function(html) {
                $('body').append(
                  `<div id="modal-image" class="modal" style="z-index:10020;">${html}</div>`
                );
                $('#modal-image').modal('show');
              })
              .fail(function(jqXHR, textStatus) {
                console.error('Filemanager request failed: ', textStatus);
              });
            };
          }
        }
      });

      $textarea.data('cke-instance', true);
    } catch (e) {
      console.error('CKEditor initialization error:', e);
    }
  }

  // Метод для всех
  $.fn.ocEditorCkeditor = function(user_token) {
    if (!user_token) {
      console.warn('User token is required for CKEditor initialization');
      return this;
    }

    return this.each(function() {
      initCkeditorInstance($(this), user_token);
    });
  };

  // Получаем токен
  function getToken() {
    try {
      // 1. Пробуем получить из URL (стандартный способ в OpenCart)
      if (typeof getURLVar === 'function') {
        return getURLVar('user_token');
      }

      // 2. Пробуем найти в форме
      const $form = $('form').first();
      if ($form.length && $form.find('input[name="user_token"]').length) {
        return $form.find('input[name="user_token"]').val();
      }

      // 3. Пробуем найти в любом инпуте на странице
      const $tokenInput = $('input[name="user_token"]').first();
      if ($tokenInput.length) {
        return $tokenInput.val();
      }

      console.warn('User token not found in URL or form inputs');
      return '';
    } catch (e) {
      console.error('Token retrieval error:', e);
      return '';
    }
  }

  // Функция для инициализации всех редакторов
  function initAllEditors() {
    const token = getToken();
    if (!token) {
      console.warn('Cannot initialize CKEditor without user token');
      return;
    }

    $('textarea[data-toggle="summernote"]').ocEditorCkeditor(token);
  }

  // Инициализация на DOMReady
  $(function() {
    // Небольшая задержка для совместимости с другими скриптами OpenCart
    setTimeout(initAllEditors, 100);
  });

  // И после AJAX
  $(document).on('ajaxComplete', function() {
    initAllEditors();
  });

})(jQuery);