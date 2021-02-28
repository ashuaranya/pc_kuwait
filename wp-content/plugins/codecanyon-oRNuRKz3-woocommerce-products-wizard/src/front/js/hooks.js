(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(['jquery', 'sticky-kit'], factory);
    } else if (typeof exports === 'object'
        && typeof module !== 'undefined'
        && typeof require === 'function'
    ) {
        module.exports = factory(require('jquery'), require('sticky-kit'));
    } else {
        factory(root.jQuery);
    }
})(this, function ($) {
    'use strict';

    const $document = $(document);

    document.wcpw = (function () {
        return {
            $rootElement: $('html, body'),

            isScrolledIntoView: function (element) {
                const docViewTop = $(window).scrollTop();
                const docViewBottom = docViewTop + $(window).height();
                const elemTop = $(element).offset().top;
                const elemBottom = elemTop + $(element).height();

                return elemBottom <= docViewBottom && elemTop >= docViewTop;
            },

            scrollToElement: function ($element, gap) {
                this.$rootElement.stop().animate({scrollTop: $element.offset().top + Number(gap)}, 500);
            },

            reInitExtraProductOptions: function () {
                if (typeof $.tcepo === 'undefined' || typeof $.tcepo.tm_init_epo === 'undefined') {
                    return this;
                }

                // clear fields cache
                if (typeof $.tc_api_set !== 'undefined') {
                    $.tc_api_set('get_element_from_field_cache', []);
                } else if (typeof $.tcAPISet !== 'undefined') {
                    $.tcAPISet('getElementFromFieldCache', []);
                }

                // remove old listeners
                $document
                    .off(
                        'click.cpfurl change.cpfurl tmredirect',
                        '.use_url_container .tmcp-radio, .use_url_container .tmcp-radio+label'
                    )
                    .off('change.cpfurl tmredirect', '.use_url_container .tmcp-select');

                // remove old elements
                $('.tmcp-upload-hidden').remove();

                // unique container where the options are embedded. this is usually the parent tag of the cart form
                return $('[data-component~="wcpw-product"]').each(function () {
                    const $product = $(this);
                    const $options = $product.find('.tc-extra-product-options');
                    const productId = $options.attr('data-product-id');
                    const epoId = $options.attr('data-epo-id');

                    if ($options.length <= 0 || !productId || !epoId) {
                        return null;
                    }

                    $.tcepo.tm_init_epo($product, true, productId, epoId);
                    $(window).trigger('tmlazy');

                    if ($.jMaskGlobals) {
                        $product.find($.jMaskGlobals.maskElements).each(function () {
                            const $element = $(this);

                            if ($element.attr('data-mask')) {
                                $element.mask($element.attr('data-mask'));
                            }
                        });
                    }

                    if ($product.data('type') === 'variable') {
                        $product.find('[data-component~="wcpw-product-variations"]').trigger('wc_variation_form.cpf');
                    }

                    return this;
                });
            },

            saveExtraProductOptions: function (productToAdd) {
                if (typeof $.tcepo === 'undefined') {
                    return null;
                }

                const productId = productToAdd.product_id;
                const $extraOptions = $(`.tc-extra-product-options.tm-product-id-${productId}`);

                if ($extraOptions.length !== 1) {
                    return true;
                }

                const $form = $(`.tc-totals-form.tm-product-id-${productId}`);
                const formPrefix = $form.find('.tc_form_prefix').val();
                const data = {
                    tcajax: 1,
                    tcaddtocart: productId,
                    cpf_product_price: $form.find('.cpf-product-price').val()
                };

                if (formPrefix) {
                    data.tc_form_prefix = formPrefix;
                }

                if ($form.tc_validate && !$form.closest('form').tc_validate().form()) {
                    return false;
                }

                // save collected data into product request arg
                const request = $extraOptions.tm_aserializeObject
                    ? $extraOptions.tm_aserializeObject()
                    : $extraOptions.tcSerializeObject();

                // bug with files upload
                $.each(request, (key, value) => {
                    if (Array.isArray(value)) {
                        value = value.filter((el) => el !== '');

                        if (value.length === 0) {
                            request[key] = '';
                        }
                    }
                });

                productToAdd.request = $.extend(request, data);

                return true;
            },

            saveExtraProductOptionsAttachments: function (data) {
                if (typeof $.tcepo === 'undefined') {
                    return;
                }

                $('.tc-extra-product-options input[type="file"]').each(function () {
                    if (!this.files[0] || !this.files[0].size) {
                        return this;
                    }

                    return data.append(this.name, this.files[0]);
                });
            }
        };
    })();

    $document
        .ready(() => {
            if (typeof wpcf7 !== 'undefined' && typeof wpcf7.initForm !== 'undefined') {
                $document.on('ajaxCompleted.wcProductsWizard', (event, instance) => {
                    // contact form 7 init on ajax complete
                    wpcf7.initForm(instance.$element.find('.wpcf7-form'));
                });
            }

            if (typeof $.tcepo !== 'undefined') {
                $document
                    .on('submit.wcProductsWizard', (event, instance, data) => {
                        if (instance && data) {
                            // pass EPO plugin data to the request
                            $.each(data.productsToAdd, (key, product) => {
                                if (typeof data.productsToAddChecked[product.step_id] !== 'undefined'
                                    && data.productsToAddChecked[product.step_id].indexOf(product.product_id) !== -1
                                    && !document.wcpw.saveExtraProductOptions(product)
                                ) {
                                    instance.hasError = true;
                                    instance.productsWithError.push(product);
                                }
                            });
                        }
                    })

                    .on('ajaxRequest.wcProductsWizard', (event, instance, data) => {
                        // save EPO plugin attachments
                        document.wcpw.saveExtraProductOptionsAttachments(data);
                    });
            }
        })

        .on('launched.wcProductsWizard ajaxCompleted.wcProductsWizard', (event, instance) => {
            // check product variations form for attached wizard
            if (instance.args.attachedMode) {
                $('#' + instance.args.formId).trigger('check_variations');
            }

            // prettyPhoto init
            if (typeof $.fn.prettyPhoto !== 'undefined'
                && typeof instance.$element !== 'undefined'
            ) {
                instance.$element
                    .find('a[data-rel^="prettyPhoto"]')
                    .prettyPhoto({
                        hook: 'data-rel',
                        social_tools: false,
                        theme: 'pp_woocommerce',
                        horizontal_padding: 20,
                        opacity: 0.8,
                        deeplinking: false
                    });
            }

            // avada lightbox init
            if (typeof window.avadaLightBox !== 'undefined'
                && typeof window.avadaLightBox.activate_lightbox !== 'undefined'
            ) {
                window.avadaLightBox.activate_lightbox(instance.$element);
            }

            // sticky elements init
            if (typeof $.fn.stick_in_parent !== 'undefined') {
                $('[data-component~="wcpw-sticky"]').each(function () {
                    const $element = $(this);

                    return $element.stick_in_parent({
                        parent: $element.data('sticky-parent'),
                        offset_top: Number($element.data('sticky-top-offset'))
                    });
                });
            }

            // EPO plugin init
            document.wcpw.reInitExtraProductOptions();

            // noUi slider init
            $document.trigger('init.nouislider');
        })

        .on('submit.error.wcProductsWizard', (event, instance) => {
            if (!instance || instance.productsWithError.length <= 0) {
                return this;
            }

            const $product = instance.$element
                .find(
                    `[data-component~="wcpw-product"][data-id="${instance.productsWithError[0].product_id}"]`
                    + `[data-step-id="${instance.productsWithError[0].step_id}"]`
                );

            if ($product.length <= 0) {
                return this;
            }

            // scroll window to the product
            if (!document.wcpw.isScrolledIntoView($product)) {
                document.wcpw.scrollToElement($product, instance.args.scrolling_up_gap);
            }

            if (typeof $.fn.modal !== 'undefined') {
                // open product modal
                const $modal = $product.find('[data-component="wcpw-product-modal"]');

                if ($modal.length > 0) {
                    $modal.modal('show');
                }
            }

            return this;
        })

        .on('ajaxRequest.wcProductsWizard', () => {
            if (typeof $.fn.modal !== 'undefined') {
                // close products modals
                $('[data-component="wcpw-product-modal"].show').modal('hide');
            }
        })

        // disable/enable add-to-cart button for attached wizards
        .on('hide_variation', '.variations_form', function () {
            const $form = $(this);
            const $product = $form.closest('.product');
            const $addToCart = $product.find(`[data-component="wcpw-add-to-cart"][form="${$form.attr('id')}"]`);

            if ($addToCart.length > 0) {
                $addToCart.addClass('disabled').attr('disabled', true);
            }
        })

        .on('show_variation', '.variations_form', function () {
            const $form = $(this);
            const $product = $form.closest('.product');
            const $addToCart = $product.find(`[data-component="wcpw-add-to-cart"][form="${$form.attr('id')}"]`);

            if ($addToCart.length > 0) {
                $addToCart.removeClass('disabled').removeAttr('disabled');
            }
        });
});
