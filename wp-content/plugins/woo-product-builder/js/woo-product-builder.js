jQuery(document).ready(function ($) {
	'use strict';
	var woo_product_builder = {
		init: function () {
			this.sort_by();
			this.review_popup();
			this.review_total_price();
			this.events();
			this.mobileControlBar();
			this.getShortShareLink();
		},
		sort_by: function () {
			jQuery('.woopb-sort-by-button').on('change', function () {
				var href = jQuery(this).val();
				window.location.href = href
			})
		},
		review_popup: function () {
			jQuery('#vi_wpb_sendtofriend').on('click', function () {
				woo_product_builder.review_popup_show();
			});
			jQuery('#vi_wpb_popup_email .vi-wpb_overlay, #vi_wpb_popup_email .woopb-close').on('click', function () {
				woo_product_builder.review_popup_hide();
			});
		},
		review_popup_show: function () {
			jQuery('html').css({'overflow': 'hidden'});
			jQuery('#vi_wpb_popup_email').fadeIn(500);
		},
		review_popup_hide: function () {
			jQuery('#vi_wpb_popup_email').fadeOut(300);
			jQuery('html').css({'overflow': 'inherit'});
		},
		review_total_price: function () {
			jQuery('.woopb-qty-input').on('change', function () {
				var quantity = parseInt(jQuery(this).val());
				var price = parseFloat(jQuery(this).closest('td').attr('data-price'));
				var total_html = jQuery(this).closest('tr').find('.woopb-total .woocommerce-Price-amount').contents();

				if (price > 0) {
					var total = quantity * price;
					total_html.filter(function (index) {
						return this.nodeType == 3;
					}).each(function () {
						this.textContent = total;
					})
				} else {
					return;
				}
			})
		},
		events: function () {
			jQuery('.woopb-share-link').on('click', function () {
				jQuery(this).select();
				document.execCommand("copy");
			})
		},

		mobileControlBar() {
			let overlay = jQuery('.woopb-overlay'),
				steps = jQuery('.vi-wpb-wrapper .woopb-steps'),
				sidebar = jQuery('.woocommerce-product-builder-sidebar'),
				viewStepsBtn = jQuery('.woopb-steps-detail-btn'),
				viewFilterBtn = jQuery('.woopb-mobile-filters-control'),
				close = jQuery('.woopb-close-modal');

			viewStepsBtn.on('click', function () {
				steps.toggle('slow');
				sidebar.hide();
			});

			viewStepsBtn.on('mouseup', function () {
				steps.css('display') === 'none' ? overlay.show('slow') : overlay.hide();
				steps.css('display') === 'none' ? close.show() : close.hide();
			});

			viewFilterBtn.on('click', function () {
				sidebar.toggle('slow');
				steps.hide();
			});

			viewFilterBtn.on('mouseup', function () {
				sidebar.css('display') === 'none' ? overlay.show('slow') : overlay.hide();
				sidebar.css('display') === 'none' ? close.show() : close.hide();
			});

			function hideAll() {
				sidebar.hide('slow');
				steps.hide('show');
				overlay.hide();
				close.hide();
			}

			overlay.on('click', function () {
				hideAll();
			});

			close.on('click', function () {
				hideAll();
			});
		},
		getShortShareLink() {
			$('#vi-wpb-get-short-share-link').on('click', function () {
				let _thisBtn = $(this);
				$.ajax({
					url: _woo_product_builder_params.ajax_url,
					type: 'post',
					dataType: 'json',
					data: {action: 'woopb_get_short_share_link', nonce: $('#_nonce').val(), woopb_id: $('[name=woopb_id]').val()},
					beforeSend: function () {
						_thisBtn.addClass('woopb-loading');
					},
					success: function (res) {
						if (res.success && res.data) {
							let copy = $('.woopb-short-share-link').html(`<div class="woopb-short-share-link-inner">
                                <span class="woopb-short-share-link-text">${res.data}</span>
                                <i class="dashicons dashicons-admin-page woopb-copy-short-link"></i></div>`);

							copy.on('click', function () {
								let node = $(this).find('.woopb-short-share-link-text').get(0);
								if (window.getSelection) {
									let selection = window.getSelection();
									let range = document.createRange();
									range.selectNode(node);
									selection.removeAllRanges();
									selection.addRange(range);
									document.execCommand("copy");
								} else if (document.selection) {
									let range = document.body.createTextRange();
									range.moveToElementText(node);
									range.select().createTextRange();
									document.execCommand("copy");
								}
							});
						}
					},
					complete() {
						_thisBtn.removeClass('woopb-loading');
					}
				});
			});
		}

	};

	woo_product_builder.init();

});
