function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

/**
 * Show one meaningful message for failed AJAX requests.
 *
 * Prefer the structured JSON error returned by the server. Only fall back to
 * raw response text or HTTP status when no structured error is available.
 * This avoids duplicated messages such as:
 * "Internal Server Error / Internal Server Error / {json...}".
 */
function showAjaxError(xhr, thrownError) {
	var response = null;
	var responseText = '';
	var message = '';

	if (xhr) {
		if (xhr.responseJSON && typeof xhr.responseJSON === 'object') {
			response = xhr.responseJSON;
		} else if (xhr.responseText) {
			responseText = $.trim(xhr.responseText);

			try {
				response = JSON.parse(responseText);
			} catch (e) {
				response = null;
			}
		}
	}

	if (response && response.error) {
		if (typeof response.error === 'string') {
			message = response.error;
		} else {
			try {
				message = JSON.stringify(response.error);
			} catch (e) {
				message = String(response.error);
			}
		}

		if (response.emergency_clear) {
			message += "\r\n\r\n" + response.emergency_clear;
		}
	} else if (responseText && responseText.charAt(0) !== '<') {
		message = responseText;
	}

	if (!message) {
		message = thrownError || (xhr && xhr.statusText) || (xhr && xhr.status ? 'HTTP ' + xhr.status : 'Request failed');
	}

	alert(message);
}

$(document).ready(function() {
	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();

		if (element.hasClass('form-group')) {
			element.addClass('has-error');
		}
	});

	// Currency
	$('#form-currency .currency-select').on('click', function(e) {
		e.preventDefault();

		$('#form-currency input[name=\'code\']').val($(this).attr('name'));

		$('#form-currency').submit();
	});

	// Language
	$('#form-language .language-select').on('click', function(e) {
		e.preventDefault();

		$('#form-language input[name=\'code\']').val($(this).attr('name'));

		$('#form-language').submit();
	});

	/* Search */
	$('#search input[name=\'search\']').parent().find('button').on('click', function() {
		var url = $('base').attr('href') + 'index.php?route=product/search';

		var value = $('header #search input[name=\'search\']').val();

		if (value) {
			url += '&search=' + encodeURIComponent(value);
		}

		location = url;
	});

	$('#search input[name=\'search\']').on('keydown', function(e) {
		if (e.keyCode == 13) {
			$('header #search input[name=\'search\']').parent().find('button').trigger('click');
		}
	});

	// Menu: horizontal correction is only needed for the desktop dropdown.
	// On mobile the dropdown is part of the collapsed flow and must not keep
	// an inline desktop offset.
	function updateMenuDropdownOffsets() {
		$('#menu .dropdown-menu').each(function() {
			var dropdownMenu = $(this);

			if (window.matchMedia('(max-width: 767px)').matches) {
				dropdownMenu.css('margin-left', '');
				return;
			}

			var menu = $('#menu').offset();
			var dropdown = dropdownMenu.parent().offset();
			var overflow = (dropdown.left + dropdownMenu.outerWidth()) - (menu.left + $('#menu').outerWidth());

			dropdownMenu.css('margin-left', overflow > 0 ? '-' + (overflow + 10) + 'px' : '');
		});
	}

	updateMenuDropdownOffsets();
	$(window).on('resize', updateMenuDropdownOffsets);

	// Product List
	$('#list-view').on('click', function() {
		$('#content .product-grid > .clearfix').remove();

		$('#content .row > .product-grid').attr('class', 'product-layout product-list col-xs-12');
		$('#grid-view').removeClass('active');
		$('#list-view').addClass('active');

		localStorage.setItem('display', 'list');
	});

	// Product Grid
	$('#grid-view').on('click', function() {
		// What a shame bootstrap does not take into account dynamically loaded columns
		var cols = $('#column-right, #column-left').length;

		if (cols == 2) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-6 col-md-6 col-sm-12 col-xs-12');
		} else if (cols == 1) {
			$('#content .product-list').attr('class', 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12');
		} else {
			var gridClass = $('#content .product-list').first().closest('#blog-latest, #blog-category').length
				? 'product-layout product-grid col-lg-4 col-md-4 col-sm-6 col-xs-12'
				: 'product-layout product-grid col-lg-3 col-md-3 col-sm-6 col-xs-12';

			$('#content .product-list').attr('class', gridClass);
		}

		$('#list-view').removeClass('active');
		$('#grid-view').addClass('active');

		localStorage.setItem('display', 'grid');
	});

	if (localStorage.getItem('display') == 'list') {
		$('#list-view').trigger('click');
		$('#list-view').addClass('active');
	} else {
		$('#grid-view').trigger('click');
		$('#grid-view').addClass('active');
	}

	// Checkout
	$(document).on('keydown', '#collapse-checkout-option input[name=\'email\'], #collapse-checkout-option input[name=\'password\']', function(e) {
		if (e.keyCode == 13) {
			$('#collapse-checkout-option #button-login').trigger('click');
		}
	});

	// tooltips on hover
	$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});

	// Makes tooltips work on ajax generated content
	$(document).ajaxStop(function() {
		$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
	});
});

// Stock shortage popup
var stockShortagePopup = {
	'programmaticClose': false,
	'pending': null,
	'requestSeq': 0,

	'route': function() {
		return (typeof window.NovaCartRoute !== 'undefined' ? window.NovaCartRoute : (getURLVar('route') || 'common/home'));
	},

	'render': function(html, signature) {
		var self = this;
		var currentModal = $('#stock-shortage-modal');

		if (currentModal.length) {
			// Do not reopen or duplicate the same warning while it is already visible.
			if (currentModal.data('stock-signature') === signature) {
				return;
			}

			self.pending = {
				html: html,
				signature: signature
			};
			self.programmaticClose = true;
			currentModal.modal('hide');
			return;
		}

		$('body').append(html);

		$('#stock-shortage-modal')
			.data('stock-signature', signature)
			.on('hidden.bs.modal', function() {
				var modal = $(this);
				var next = self.pending;

				self.programmaticClose = false;
				self.pending = null;
				modal.remove();

				if (next) {
					self.render(next.html, next.signature);
				}
			})
			.modal('show');
	},

	'closeCurrent': function() {
		var modal = $('#stock-shortage-modal');

		this.pending = null;

		if (!modal.length) {
			return;
		}

		this.programmaticClose = true;
		modal.modal('hide');
	},

	'refresh': function() {
		var self = this;
		var requestId = ++self.requestSeq;

		if (typeof window.NovaCartStockPopupEnabled !== 'undefined' && !window.NovaCartStockPopupEnabled) {
			self.closeCurrent();
			return;
		}

		$.ajax({
			url: 'index.php?route=checkout/cart/stockPopup',
			type: 'post',
			data: {
				current_route: self.route()
			},
			dataType: 'json',
			global: false,
			success: function(json) {
				if (requestId !== self.requestSeq) {
					return;
				}

				if (!json['show'] || !json['html'] || !json['signature']) {
					self.closeCurrent();
					return;
				}

				$('.cart-success-alert').remove();
				self.render(json['html'], json['signature']);
			}
		});
	}
};

$(function() {
	stockShortagePopup.refresh();
});

var cartButtonState = {
	// One renderer for cards, product purchase, sticky purchase and compare.
	// Rebuild the two child elements together: using .text() on the button would
	// remove its icon after an AJAX cart removal.
	'render': function(button, inCart, label) {
		button = $(button);
		var icon = $('<i class="fa stock-cart-icon" aria-hidden="true"></i>')
			.addClass(inCart ? 'fa-check' : 'fa-shopping-cart');
		var text = $('<span class="stock-cart-label"></span>').text(label);

		// Product-card buttons keep their icon-only layout below Bootstrap lg.
		if (button.closest('.product-thumb').length) {
			text.addClass('hidden-xs hidden-sm hidden-md');
		}

		if (button.is('input')) {
			button.val(label);
		} else {
			button.empty().append(icon, text);
		}

		button.toggleClass('stock-cart-in-cart', inCart);
		button.attr('aria-label', label);
	},
	'refresh': function() {
		$.ajax({
			url: 'index.php?route=checkout/cart/buttonState',
			type: 'get',
			dataType: 'json',
			global: false,
			success: function(json) {
				if (!json || !json['products']) {
					return;
				}

				$('.stock-cart-button[data-product-id]').each(function() {
					var button = $(this);

					// Stock validation owns the disabled state and its explanation.
					if (button.prop('disabled') || button.hasClass('stock-purchase-disabled')) {
						return;
					}

					var productId = String(button.attr('data-product-id'));
					var inCart = Object.prototype.hasOwnProperty.call(json['products'], productId);
					var label = inCart ? json['button_in_cart'] : (button.attr('data-cart-label') || json['button_cart']);

					cartButtonState.render(button, inCart, label);
				});
			}
		});
	}
};
$(function() {
	cartButtonState.refresh();
});

// Cart add remove functions
var cart = {
	'add': function(product_id, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				$('.alert-dismissible, .text-danger').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['error'] && json['error']['stock']) {
					$('#content').parent().before('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + json['error']['stock'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					$('html, body').animate({ scrollTop: 0 }, 'slow');
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible cart-success-alert"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					// Need to set timeout otherwise it wont update the total
					setTimeout(function () {
						$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
					}, 100);

					$('html, body').animate({ scrollTop: 0 }, 'slow');

					$('#cart > ul').load('index.php?route=common/cart/info ul li');
					cartButtonState.refresh();
					stockShortagePopup.refresh();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				showAjaxError(xhr, thrownError);
			}
		});
	},
	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
					cartButtonState.refresh();

					if (typeof updateStockPurchaseState === 'function') {
						updateStockPurchaseState();
					}

					stockShortagePopup.refresh();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				showAjaxError(xhr, thrownError);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
					cartButtonState.refresh();

					if (typeof updateStockPurchaseState === 'function') {
						updateStockPurchaseState();
					}

					stockShortagePopup.refresh();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				showAjaxError(xhr, thrownError);
			}
		});
	}
}

var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cart > button').button('loading');
			},
			complete: function() {
				$('#cart > button').button('reset');
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					location = 'index.php?route=checkout/cart';
				} else {
					$('#cart > ul').load('index.php?route=common/cart/info ul li');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				showAjaxError(xhr, thrownError);
			}
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}

				$('#wishlist-total span').html(json['total']);
				$('#wishlist-total').attr('title', json['total']);

				$('html, body').animate({ scrollTop: 0 }, 'slow');
			},
			error: function(xhr, ajaxOptions, thrownError) {
				showAjaxError(xhr, thrownError);
			}
		});
	},
	'remove': function() {

	}
}

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['success']) {
					$('#content').parent().before('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('#compare-total').html(json['total']);
					$('#compare-total-top')
						.attr('title', json['total'])
						.find('.top-link-label')
						.text(json['total']);

					$('html, body').animate({ scrollTop: 0 }, 'slow');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				showAjaxError(xhr, thrownError);
			}
		});
	},
	'remove': function() {

	}
}

/* Agree to Terms */
$(document).on('click', '.agree', function(e) {
	e.preventDefault();

	$('#modal-agree').remove();

	var element = this;

	$.ajax({
		url: $(element).attr('href'),
		type: 'get',
		dataType: 'html',
		success: function(data) {
			html  = '<div id="modal-agree" class="modal">';
			html += '  <div class="modal-dialog">';
			html += '    <div class="modal-content">';
			html += '      <div class="modal-header">';
			html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
			html += '        <h4 class="modal-title">' + $(element).text() + '</h4>';
			html += '      </div>';
			html += '      <div class="modal-body">' + data + '</div>';
			html += '    </div>';
			html += '  </div>';
			html += '</div>';

			$('body').append(html);

			$('#modal-agree').modal('show');
		}
	});
});

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			this.timer = null;
			this.items = new Array();

			$.extend(this, option);

			$(this).attr('autocomplete', 'off');

			// Focus
			$(this).on('focus', function() {
				this.request();
			});

			// Blur
			$(this).on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			// Keydown
			$(this).on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Click
			this.click = function(event) {
				event.preventDefault();

				value = $(event.target).parent().attr('data-value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			// Show
			this.show = function() {
				var pos = $(this).position();

				$(this).siblings('ul.dropdown-menu').css({
					top: pos.top + $(this).outerHeight(),
					left: pos.left
				});

				$(this).siblings('ul.dropdown-menu').show();
			}

			// Hide
			this.hide = function() {
				$(this).siblings('ul.dropdown-menu').hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			// Response
			this.response = function(json) {
				html = '';

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						this.items[json[i]['value']] = json[i];
					}

					for (i = 0; i < json.length; i++) {
						if (!json[i]['category']) {
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						}
					}

					// Get all the ones with a categories
					var category = new Array();

					for (i = 0; i < json.length; i++) {
						if (json[i]['category']) {
							if (!category[json[i]['category']]) {
								category[json[i]['category']] = new Array();
								category[json[i]['category']]['name'] = json[i]['category'];
								category[json[i]['category']]['item'] = new Array();
							}

							category[json[i]['category']]['item'].push(json[i]);
						}
					}

					for (i in category) {
						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

						for (j = 0; j < category[i]['item'].length; j++) {
							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$(this).siblings('ul.dropdown-menu').html(html);
			}

			$(this).after('<ul class="dropdown-menu"></ul>');
			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

		});
	}
})(window.jQuery);
