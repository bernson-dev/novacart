/**
* Извлекает значение переменной из строки запроса URL.
* @param {string} key Ключ переменной, которую нужно найти.
* @returns {string} Значение переменной или пустая строка.
*/
function getURLVar(key) {
	const params = Object.create(null);
	const query = String(document.location).split('?');

	if (query[1]) {
		const part = query[1].split('&');

		for (let i = 0; i < part.length; i++) {
			const data = part[i].split('=');

			if (data[0] && data[1]) {
				params[data[0]] = decodeURIComponent(data[1].replace(/\+/g, ' '));
			}
		}

		if (params[key]) {
			return params[key];
		}
	}

	return '';
}

function ocEscapeHtml(s) {
	return String(s)
	.replace(/&/g, '&amp;')
	.replace(/</g, '&lt;')
	.replace(/>/g, '&gt;')
	.replace(/"/g, '&quot;')
	.replace(/'/g, '&#039;');
}

/**
* SEO URL Generator для ocStore 3.x - Глобальная функция транслитерации
*/
window.translit = function(text) {
	if (!text) {
		return '';
	}

	const map = {
		'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
		'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
		'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
		'ф': 'f', 'х': 'kh', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'shch',
		'ы': 'y', 'э': 'e', 'ю': 'yu', 'я': 'ya', 'ъ': '', 'ь': '',
		'ґ': 'g', 'і': 'i', 'є': 'ye', 'ї': 'yi',
		'&': 'and'
	};

	return text.toString().toLowerCase()
	.replace(/\s+/g, '-')                // пробелы - дефис
	.replace(/\./g, '-')                 // точки - дефис
	.replace(/[а-яёґієї]/gu, char => map[char] || '')
	.replace(/[^a-z0-9\-_]/g, '')        // убрать лишние символы
	.replace(/-+/g, '-')                 // убрать повторные дефисы
	.replace(/_+/g, '_')                 // убрать повторные подчёркивания
	.replace(/-+$/g, '')                 // убрать дефисы в конце
	.replace(/^-+/g, '');                // убрать дефисы в начале
};

const buildSeoValue = (text, languageId) => {
	let seo = window.translit(text);

	if (!seo) {
		return '';
	}

	const def = window.defaultLanguageId ? Number(window.defaultLanguageId) : null;
	const current = Number(languageId);

	if (def && current && current !== def && window.languages && window.languages[languageId]) {
		const raw = String(window.languages[languageId]);
		const prefix = raw.split('-')[0];

		if (prefix) {
			seo = prefix + '_' + seo;
		}
	}

	return seo;
};

const flashSeoField = ($el) => {
	$el.addClass('seo-autofilled');

	window.setTimeout(() => {
		$el.removeClass('seo-autofilled');
	}, 1000);
};

const fillSeo = (languageId, sourceText, onlyEmpty) => {
	const seo = buildSeoValue(sourceText, languageId);

	if (!seo) {
		return;
	}

	const fieldNames = [
		'category_seo_url',
		'product_seo_url',
		'manufacturer_seo_url',
		'article_seo_url',
		'information_seo_url',
		'keyword'
	];

	fieldNames.forEach(name => {
		const selector = `input[name^="${name}[${languageId}]"], input[name^="${name}"][name$="[${languageId}]"]`;

		$(selector).each(function() {
			const $el = $(this);

			if (!onlyEmpty || !$el.val()) {
				$el.val(seo).trigger('change');
				flashSeoField($el);
			}
		});
	});
};

$(document).ready(function() {
	// 1. Form Submit for IE Browser
	$('button[type="submit"]').on('click', function() {
		const $form = $("form[id*='form-']");

		if ($form.length) {
			$form.submit();
		}
	});

	// 2. Highlight any found errors
	$('.text-danger').each(function() {
		const $element = $(this).parent().parent();

		if ($element.hasClass('form-group')) {
			$element.addClass('has-error');
		}

		$element.parents('.tab-pane').each(function() {
			const id = $(this).attr('id');

			if (id) {
				$('a[data-toggle="tab"][href="#' + id + '"]').addClass('has-error');
			}
		});
	});

	// 3. Tooltips initialization and handling
	(function initTooltipsBS3() {
		$('body').tooltip({
			selector: '[data-toggle="tooltip"]',
			container: 'body',
			html: true,
			trigger: 'hover focus'
		});

		$(document).on('click', '[data-toggle="tooltip"]', function() {
			$('body > .tooltip').remove();
		});

		$(document).on('click', 'a, button, input[type="submit"]', function() {
			$('body > .tooltip').remove();
		});
	})();

	// 4. Menu toggle
	const $buttonMenu = $('#button-menu');
	const $columnLeft = $('#column-left');
	const $menu = $('#menu');
	const $root = $(document.documentElement);

	function isDesktop() {
		return window.matchMedia('(min-width: 48rem)').matches;
	}

	function isMediumScreen() {
		return window.matchMedia('(min-width: 48rem) and (max-width: 100rem)').matches;
	}

	function applySidebarState(isExpanded) {
		$columnLeft.toggleClass('active', isExpanded);
		$root.toggleClass('sidebar-expanded', isExpanded);
		$root.toggleClass('sidebar-minimized', !isExpanded);
	}

	function applyResponsiveSidebarState() {
		if (!isDesktop()) {
			applySidebarState(false);
			return;
		}

		// На средних экранах стартуем компактно,
		// но не запрещаем пользователю развернуть меню кнопкой.
		if (isMediumScreen()) {
			const mediumState = sessionStorage.getItem('column-left-medium');

			applySidebarState(mediumState === 'expanded');
			return;
		}

		applySidebarState(localStorage.getItem('column-left') !== 'minimized');
	}

	$buttonMenu.on('click', function(e) {
		e.preventDefault();

		const isExpanded = !$columnLeft.hasClass('active');

		applySidebarState(isExpanded);

		if (isMediumScreen()) {
			// Для средних экранов состояние временное:
			// после закрытия вкладки снова будет компактное.
			sessionStorage.setItem('column-left-medium', isExpanded ? 'expanded' : 'minimized');
			return;
		}

		if (isDesktop()) {
			localStorage.setItem('column-left', isExpanded ? 'expanded' : 'minimized');
		}
	});

	applyResponsiveSidebarState();

	window.addEventListener('resize', function() {
		applyResponsiveSidebarState();
	});

	$menu.on('click', 'a[href]', function() {
		const href = $(this).attr('href') || '';

		if ($(this).hasClass('parent') || href === '#' || href.charAt(0) === '#') {
			return;
		}

		sessionStorage.setItem('menu', href);
	});

	const menuLink = sessionStorage.getItem('menu');

	$('#menu li').removeClass('active');
	$('#menu ul').removeClass('in');
	$('#menu a.parent').addClass('collapsed');

	if (menuLink) {
		const $activeLink = $('#menu a[href]').filter(function() {
			return $(this).attr('href') === menuLink;
		}).first();

		if ($activeLink.length) {
			$activeLink
			.closest('li')
			.addClass('active')
			.parents('ul')
			.addClass('in')
			.parents('li')
			.addClass('active')
			.children('a.parent')
			.removeClass('collapsed');
		} else {
			$('#menu #dashboard').addClass('active');
		}
	} else {
		$('#menu #dashboard').addClass('active');
	}

	// 5. Flyout подменю в компактном режиме
	(() => {
		const headerH = () => document.getElementById('header')?.getBoundingClientRect().height || 0;
		const columnEl = document.getElementById('column-left');
		const collapsedW = () => columnEl?.getBoundingClientRect().width || 50;
		const isDesktop = () => window.matchMedia('(min-width: 48rem)').matches;
		const isCollapsed = () => isDesktop() && $(document.documentElement).hasClass('sidebar-minimized');
		const $uls = () => $('#menu > li > ul');

		let flyoutCloseTimer = 0;
		let scrollRaf = 0;

		const clearFlyoutCloseTimer = () => {
			if (flyoutCloseTimer) {
				clearTimeout(flyoutCloseTimer);
				flyoutCloseTimer = 0;
			}
		};

		const destroyObserver = (ul) => {
			if (ul && ul._ocFlyoutObserver) {
				ul._ocFlyoutObserver.disconnect();
				ul._ocFlyoutObserver = null;
				ul._ocFlyoutOwner = null;
			}
		};

		const resetStyles = (ul) => {
			ul.style.left = '';
			ul.style.top = '';
			ul.style.maxHeight = '';
			ul.style.width = '';
		};

		const closeFlyouts = () => {
			clearFlyoutCloseTimer();

			$uls().each(function() {
				this.classList.remove('oc-flyout-open', 'oc-flyout-ready');
				resetStyles(this);
				destroyObserver(this);
			});
		};

		const scheduleCloseFlyouts = (delay = 300) => {
			clearFlyoutCloseTimer();

			flyoutCloseTimer = setTimeout(() => {
				flyoutCloseTimer = 0;
				closeFlyouts();
			}, delay);
		};

		const positionFlyout = (li, ul, options = {}) => {
			const freezeTopIfHover = !!options.freezeTopIfHover;

			if (!li || !ul) {
				return;
			}

			const rect = li.getBoundingClientRect();
			const vh = window.innerHeight;
			const topMin = headerH();
			const bottomGap = 10;
			const ulH = ul.scrollHeight;
			const currentTop = parseFloat(ul.style.top || 'NaN');
			const hasCurrentTop = Number.isFinite(currentTop);
			const desiredTop = Math.max(rect.top, topMin);
			const visibleH = Math.min(ulH, vh - topMin - bottomGap);
			const maxTop = vh - bottomGap - visibleH;

			let top;

			if (freezeTopIfHover && ul.matches(':hover') && hasCurrentTop) {
				top = Math.min(Math.max(currentTop, topMin), maxTop);
			} else {
				top = Math.min(desiredTop, maxTop);
			}

			ul.style.left = `${collapsedW()}px`;
			ul.style.top = `${top}px`;
			ul.style.maxHeight = `${vh - topMin - bottomGap}px`;
		};

		const openFlyout = (li) => {
			clearFlyoutCloseTimer();

			if (!isCollapsed() || !li) {
				return;
			}

			const $ul = $(li).children('ul');

			if (!$ul.length) {
				closeFlyouts();
				return;
			}

			closeFlyouts();

			$ul.addClass('oc-flyout-ready oc-flyout-open');

			const ul = $ul[0];
			ul._ocFlyoutOwner = li;
			ul.style.width = `${Math.max(ul.getBoundingClientRect().width || 0, 250)}px`;

			requestAnimationFrame(() => positionFlyout(li, ul));
			setTimeout(() => positionFlyout(li, ul), 0);

			if (!ul._ocFlyoutObserver && 'ResizeObserver' in window) {
				ul._ocFlyoutObserver = new ResizeObserver(() => {
					if ($ul.hasClass('oc-flyout-open') && ul._ocFlyoutOwner) {
						positionFlyout(ul._ocFlyoutOwner, ul, { freezeTopIfHover: true });
					}
				});

				ul._ocFlyoutObserver.observe(ul);
			}
		};

		$('#menu').children('li').on('mouseenter', function() {
			openFlyout(this);
		});

		$('#menu').children('li').on('mouseleave', function() {
			if (isCollapsed()) {
				scheduleCloseFlyouts(300);
			}
		});

		$('#menu').on('mouseenter', '> li > ul', function() {
			clearFlyoutCloseTimer();
		});

		$('#menu').on('mouseleave', '> li > ul', function() {
			if (isCollapsed()) {
				scheduleCloseFlyouts(300);
			}
		});

		$('#menu').on('click', '> li > a.parent', function(e) {
			if (!isCollapsed()) {
				return;
			}

			e.preventDefault();
			e.stopPropagation();

			openFlyout($(this).closest('li')[0]);
		});

		$('#button-menu').on('click', closeFlyouts);
		$(window).on('resize', closeFlyouts);

		window.addEventListener('scroll', () => {
			if (!isCollapsed() || scrollRaf) {
				return;
			}

			scrollRaf = requestAnimationFrame(() => {
				scrollRaf = 0;
				closeFlyouts();
			});
		}, { passive: true });

		document.addEventListener('mousedown', (e) => {
			if (!isCollapsed()) {
				return;
			}

			if ($(e.target).closest('#column-left, #menu > li > ul.oc-flyout-open').length) {
				return;
			}

			closeFlyouts();
		});
	})();

	// 6. Image Manager
	function ocGetPopoverOwner($btn) {
		const $popover = $btn.closest('.popover');
		const id = $popover.attr('id');

		return id ? $(`[aria-describedby="${id}"]`) : $();
	}

	const buttonsHtml =
	'<button type="button" class="btn btn-primary js-image-edit"><i class="fa fa-pencil"></i></button> ' +
	'<button type="button" class="btn btn-danger js-image-clear"><i class="fa fa-trash-o"></i></button>';

	$(document).on('click', 'a[data-toggle="image"]', function(e) {
		e.preventDefault();

		const $element = $(this);

		$('a[data-toggle="image"]').not($element).popover('destroy');

		if ($element.data('bs.popover')) {
			$element.popover('destroy');
			return;
		}

		$element.popover({
			html: true,
			placement: 'right',
			trigger: 'manual',
			container: 'body',
			content: buttonsHtml
		}).popover('show');

		const pop = $element.data('bs.popover');

		if (pop && typeof pop.tip === 'function') {
			const $tip = $(pop.tip());
			$tip.find('.popover-content').html(buttonsHtml);
		}
	});

	$(document).on('mousedown', function(e) {
		if (!$(e.target).closest('a[data-toggle="image"], .popover').length) {
			$('a[data-toggle="image"]').popover('destroy');
		}
	});

	$(document).on('click', '.popover .js-image-clear', function() {
		const $owner = ocGetPopoverOwner($(this));

		if (!$owner.length) {
			return;
		}

		const $img = $owner.find('img');
		$img.attr('src', $img.attr('data-placeholder'));
		$owner.parent().find('input').val('');
		$owner.popover('destroy');
	});

	$(document).on('click', '.popover .js-image-edit', function() {
		const $owner = ocGetPopoverOwner($(this));

		if (!$owner.length) {
			return;
		}

		const $button = $(this);
		const $icon = $button.find('> i');
		let directory = $owner.parent().find('input').val() || false;

		if (directory) {
			const parts = directory.split('/');
			parts.shift();
			parts.pop();
			directory = parts.join('/');
		}

		const $parentWithMulti = $owner.closest('[data-multi]');
		let allowMultiInsert = $parentWithMulti.length > 0 && ($parentWithMulti.data('multi') === true || $parentWithMulti.data('multi') === 'true');

		if (!allowMultiInsert) {
			const targetId = $owner.parent().find('input').attr('id');
			allowMultiInsert = /^input-image\d+$/.test(targetId);
		}

		$('#modal-image').remove();

		$.ajax({
			url: 'index.php?route=common/filemanager&user_token=' + encodeURIComponent(getURLVar('user_token')) +
			'&target=' + encodeURIComponent($owner.parent().find('input').attr('id')) +
			'&thumb=' + encodeURIComponent($owner.attr('id')) +
			(directory ? '&directory=' + encodeURIComponent(directory) : ''),
			dataType: 'html',
			beforeSend: function() {
				$button.prop('disabled', true);

				if ($icon.length) {
					$icon.attr('class', 'fa fa-circle-o-notch fa-spin');
				}
			},
			complete: function() {
				$button.prop('disabled', false);

				if ($icon.length) {
					$icon.attr('class', 'fa fa-pencil');
				}
			},
			success: function(html) {
				let $modal = $('#modal-image');

				if (!$modal.length) {
					$('body').append('<div id="modal-image" class="modal"></div>');
					$modal = $('#modal-image');
				}

				$modal.data('multiInsertEnabled', allowMultiInsert);
				$modal.html(html);
				$modal.modal('show');
			}
		});

		$owner.popover('destroy');
	});

	// 7. Индикатор длины текста
	$('.form-group input[type="text"][data-length], .form-group textarea[data-length]').each(function() {
		const $input = $(this);
		const len = parseInt($input.data('length'), 10);

		if (!Number.isFinite(len) || len < 1) {
			return;
		}

		const $group = $input.closest('.form-group');
		const counterType = $input.data('counter-type');
		const needLengthCounter = (counterType === 'length' || !counterType);
		const needProgress = (counterType === 'progress' || !counterType);

		const textCanvas = document.createElement('canvas');
		const textCtx = textCanvas.getContext('2d');
		const cs = window.getComputedStyle($input[0]);

		textCtx.font = cs.font && cs.font !== ''
		? cs.font
		: `${cs.fontStyle} ${cs.fontVariant} ${cs.fontWeight} ${cs.fontSize}/${cs.lineHeight} ${cs.fontFamily}`;

		const getTextWidth = (text) => Math.round(textCtx.measureText(String(text)).width);

		const rootStyles = getComputedStyle(document.documentElement);
		const progressFill1 = rootStyles.getPropertyValue('--progress-fill-1').trim() || '#28a745';
		const progressFill2 = rootStyles.getPropertyValue('--progress-fill-2').trim() || '#ffc107';
		const progressFill3 = rootStyles.getPropertyValue('--progress-fill-3').trim() || '#fd7e14';
		const progressFill4 = rootStyles.getPropertyValue('--progress-fill-4').trim() || '#dc3545';
		const progressEmpty = rootStyles.getPropertyValue('--progress-empty').trim() || '#e9ecef';
		// Счётчик длины (справа)
		let $counter1 = needLengthCounter ? $('<span class="input-group-addon counter"></span>') : $();
		let $progressContainer = $();
		let progressCanvas = null;
		let progressCtx = null;
		let $counter2 = $();

		// Прогресс-контейнер
		if (needProgress) {
			$progressContainer = $('<div class="progress-container"></div>');
			progressCanvas = document.createElement('canvas');
			progressCanvas.width = 200;
			progressCanvas.height = 20;
			progressCtx = progressCanvas.getContext('2d');
			$counter2 = $('<span class="progress-counter"></span>');
			$progressContainer.append(progressCanvas, $counter2);
		}

		if (counterType !== 'progress') {
			$input.wrap($('<div class="input-group"></div>'));
		}

		if (needLengthCounter) {
			$input.after($counter1);
		}

		if (needProgress) {
			const $insertionPoint = (counterType !== 'progress') ? $input.closest('.input-group') : $input;
			$insertionPoint.after($progressContainer);
		}

		function drawProgress(value) {
			if (!needProgress || !progressCtx || !progressCanvas) {
				return;
			}

			const v = Math.max(0, Math.min(value, len));
			const percentage = v / len;
			const blocksTotal = 5;
			const blockWidth = progressCanvas.width / blocksTotal;
			const filled = percentage * blocksTotal;
			const fullBlocks = Math.floor(filled);
			const partial = filled - fullBlocks;

			progressCtx.clearRect(0, 0, progressCanvas.width, progressCanvas.height);

			// Градиент слева направо: зеленый → желтый → оранжевый → красный
			const gradient = progressCtx.createLinearGradient(0, 0, progressCanvas.width, 0);
			gradient.addColorStop(0, progressFill1);
			gradient.addColorStop(0.5, progressFill2);
			gradient.addColorStop(0.75, progressFill3);
			gradient.addColorStop(1, progressFill4);

			// Полные блоки
			progressCtx.fillStyle = gradient;
			for (let i = 0; i < fullBlocks; i++) {
				progressCtx.fillRect(i * blockWidth, 0, blockWidth - 1, progressCanvas.height);
			}

			// Частично заполненный блок
			if (partial > 0 && fullBlocks < blocksTotal) {
				const x = fullBlocks * blockWidth;

				progressCtx.fillStyle = gradient;
				progressCtx.fillRect(x, 0, blockWidth * partial - 1, progressCanvas.height);

				progressCtx.fillStyle = progressEmpty;
				progressCtx.fillRect(
				x + blockWidth * partial - 1,
				0,
				blockWidth - blockWidth * partial,
				progressCanvas.height
				);
			}

			// Пустые блоки
			progressCtx.fillStyle = progressEmpty;
			for (let i = fullBlocks + (partial > 0 ? 1 : 0); i < blocksTotal; i++) {
				progressCtx.fillRect(i * blockWidth, 0, blockWidth - 1, progressCanvas.height);
			}
		}

		function update() {
			const value = $input.val();
			const valueLength = String(value).length;
			const textWidth = getTextWidth(value);

			if (needLengthCounter && $counter1.length) {
				$counter1.text(`${valueLength} / ${len}`);
			}

			if (needProgress && $counter2.length) {
				$counter2.text(`${valueLength} / ${len} (${textWidth} px)`);
				drawProgress(valueLength);
			}

			if (valueLength > len || (valueLength === 0 && $group.hasClass('required'))) {
				$group.addClass('has-error');
			} else {
				$group.removeClass('has-error');
			}
		}

		update();

		let updateRaf = 0;

		$input.on('input', function() {
			if (updateRaf) {
				cancelAnimationFrame(updateRaf);
			}

			updateRaf = requestAnimationFrame(() => {
				updateRaf = 0;
				update();
			});
		});
	});

	// 8. Open-close filter
	$('.open-close-filter').on('click', function() {
		const targetSelector = $(this).data('target');
		const $target = $(targetSelector);

		if ($target.length) {
			$target.toggleClass('show');
		}
	});

	// 9. SEO URL Generator Events
	$(document).on('blur', 'input[name$="][name]"], input[name$="][title]"], input[name="name"]', function() {
		const $el = $(this);
		const nameAttr = $el.attr('name');
		const val = $el.val();

		if (!val) {
			return;
		}

		if (nameAttr === 'name') {
			if (window.languages) {
				Object.keys(window.languages).forEach(id => fillSeo(id, val, true));
			}
		} else {
			const match = nameAttr.match(/\[(\d+)\]\[(name|title)\]/);

			if (match) {
				fillSeo(match[1], val, true);
			}
		}
	});

	$(document).on('click', '.regenerate-seo', function(e) {
		e.preventDefault();

		const langId = $(this).data('language-id');
		let $source = $(`input[name$="[${langId}][name]"], input[name$="[${langId}][title]"]`);

		if (!$source.length || !$source.val()) {
			$source = $('input[name="name"]');
		}

		if ($source.val()) {
			fillSeo(langId, $source.val(), false);
		}
	});
});

// Autocomplete Plugin
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			const $this = $(this);
			const $dropdown = $('<ul class="dropdown-menu" />');
			let timer = null;
			let items = Object.create(null);
			const element = this;

			$.extend(element, option);

			$this.attr('autocomplete', 'off');

			// Focus
			$this.on('focus', function() {
				element.request();
			});

			// Blur
			$this.on('blur', function() {
				setTimeout(() => {
					element.hide();
				}, 200);
			});

			// Keydown
			$this.on('keydown', function(event) {
				const key = event.keyCode;

				switch (key) {
					case 27: // escape
						element.hide();
						break;
					default:
						element.request();
						break;
				}
			});

			// Click
			this.click = function(event) {
				event.preventDefault();

				const $li = $(event.target).closest('li[data-value]');
				const value = $li.attr('data-value');

				if (value && items[value]) {
					this.select(items[value]);
				}
			};

			// Show
			this.show = function() {
				const pos = $this.position();

				$dropdown.css({
					top: pos.top + $this.outerHeight(),
					left: pos.left
				});

				$dropdown.show();
			};

			// Hide
			this.hide = function() {
				$dropdown.hide();
			};

			// Request
			this.request = function() {
				clearTimeout(timer);

				timer = setTimeout(() => {
					element.source($this.val(), (json) => element.response(json));
				}, 200);
			};

			// Response
			this.response = function(json) {
				let html = '';
				const category = Object.create(null);

				items = Object.create(null);

				if (json && json.length) {
					for (let i = 0; i < json.length; i++) {
						const item = json[i];
						const key = String(item.value);

						items[key] = item;

						if (!item.category) {
							html += `<li data-value="${ocEscapeHtml(key)}"><a href="#">${ocEscapeHtml(item.label)}</a></li>`;
						} else {
							if (!category[item.category]) {
								category[item.category] = [];
							}

							category[item.category].push(item);
						}
					}

					for (let name in category) {
						html += `<li class="dropdown-header">${ocEscapeHtml(name)}</li>`;

						for (let j = 0; j < category[name].length; j++) {
							const item = category[name][j];
							html += `<li data-value="${ocEscapeHtml(item.value)}"><a href="#">&nbsp;&nbsp;&nbsp;${ocEscapeHtml(item.label)}</a></li>`;
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$dropdown.html(html);
			};

			$dropdown.on('click', '> li > a', $.proxy(this.click, this));
			$this.after($dropdown);
		});
	};
})(window.jQuery);