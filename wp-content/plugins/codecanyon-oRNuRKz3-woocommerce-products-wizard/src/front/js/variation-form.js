(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory);
    } else if (typeof exports === 'object'
        && typeof module !== 'undefined'
        && typeof require === 'function'
    ) {
        module.exports = factory(require('jquery'));
    } else {
        factory(root.jQuery);
    }
})(this, function ($) {
    'use strict';

    /**
     * Main variation plugin
     * @return {Object} jQuery element
     */
    $.fn.wcpwVariationForm = function () {
        // Unbind any existing events
        this.unbind('check_variations update_variation_values found_variation change');
        this.find('[data-component~="wcpw-product-variations-item-input"]').unbind('change');
        this.off('.wc-variation-form');

        // Bind events
        const $form = this
            // Upon changing an option
            .on('change', '[data-component~="wcpw-product-variations-item-input"]', function () {
                const $element = $(this);
                const $variationForm = $element.closest('[data-component~="wcpw-product-variations"]');

                $variationForm
                    .find('[data-component~="wcpw-product-variations-variation-id"]')
                    .val('')
                    .change();

                $variationForm
                    .trigger('woocommerce_variation_select_change')
                    .trigger('check_variations', [$element.attr('data-name'), true]);

                $element.blur();

                if ($().uniform && $.isFunction($.uniform.update)) {
                    $.uniform.update();
                }
            })

            // Check variations
            .on('check_variations', function (event, exclude, focus) {
                let allSet = true;
                const currentSettings = {};
                const $variationForm = $(this);
                const $product = $variationForm.closest('[data-component~="wcpw-product"]');
                const $productPrice = $product.find('[data-component~="wcpw-product-price"]');
                const $productDescription = $product.find('[data-component~="wcpw-product-description"]');
                const $productAvailability = $product.find('[data-component~="wcpw-product-availability"]');

                $variationForm
                    .find('[data-component~="wcpw-product-variations-item-input"]')
                    .each(function () {
                        const $element = $(this);

                        if ($element.prop('tagName') === 'SELECT' && (!$element.val() || $element.val().length === 0)) {
                            allSet = false;
                        }

                        if ($element.prop('tagName') === 'SELECT' || $element.prop('checked') === true) {
                            currentSettings[$element.attr('data-name')] = $element.val();
                        }
                    });

                const allVariations = $variationForm.data('product_variations');
                let matchingVariations = $.fn.wcpwVariationFormFindMatchingVariations(allVariations, currentSettings);

                if (allSet) {
                    let variation = null;

                    for (let key in matchingVariations) {
                        if (!matchingVariations.hasOwnProperty(key)) {
                            continue;
                        }

                        const currentCopy = $.extend({}, currentSettings);
                        const attributesCopy = $.extend({}, matchingVariations[key].attributes);

                        for (let attributeCopyItem in attributesCopy) {
                            if (!attributesCopy.hasOwnProperty(attributeCopyItem)) {
                                continue;
                            }

                            // change "any" value to compare
                            if (attributesCopy[attributeCopyItem] === '') {
                                attributesCopy[attributeCopyItem] = currentCopy[attributeCopyItem];
                            }
                        }

                        // find the same variation as for the current properties
                        if (JSON.stringify(attributesCopy) === JSON.stringify(currentCopy)) {
                            variation = matchingVariations[key];

                            break;
                        }
                    }

                    if (variation) {
                        // Found - set ID
                        $variationForm
                            .find('[data-component~="wcpw-product-variations-variation-id"]')
                            .val(variation.variation_id)
                            .change();

                        $variationForm.trigger('found_variation', [variation]);
                    } else if (!focus) {
                        // Nothing found - reset fields
                        $variationForm.trigger('reset_image');
                        $variationForm.trigger('hide_variation');
                    }
                } else {
                    if (!focus) {
                        $variationForm.trigger('reset_image');
                        $variationForm.trigger('hide_variation');
                    }

                    if (!exclude) {
                        // reset price
                        $productPrice.html($productPrice.data('default'));

                        // reset description
                        $productDescription.html($productDescription.data('default'));

                        // reset availability
                        $productAvailability.html($productAvailability.data('default'));
                    }
                }

                $variationForm.trigger(
                    'update_variation_values',
                    [$.extend({}, matchingVariations), $.extend({}, currentSettings)]
                );
            })

            // Reset product image
            .on('reset_image', function () {
                return $.fn.wcpwVariationFormUpdateImage($form, false);
            })

            // Disable option fields that are unavaiable for current set of attributes
            .on('update_variation_values', function (event, variations, currentSettings) {
                if (!variations || Object.keys(variations).length <= 0) {
                    return this;
                }

                const $variationForm = $(this);
                const $variationItem = $variationForm.find('[data-component~="wcpw-product-variations-item"]');

                // Loop through selects and disable/enable options based on selections
                $variationItem.each(function () {
                    const $element = $(this);
                    const currentAttrName = $element.attr('data-name');
                    const $values = $element.find('[data-component~="wcpw-product-variations-item-value"]');

                    $values.removeClass('active').removeAttr('disabled');

                    // Loop through variations
                    for (let variationKey in variations) {
                        if (!variations.hasOwnProperty(variationKey)) {
                            continue;
                        }

                        const attributes = $.extend({}, variations[variationKey].attributes);

                        for (let attrName in attributes) {
                            if (!attributes.hasOwnProperty(attrName) || attrName !== currentAttrName) {
                                continue;
                            }

                            let attrVal = attributes[attrName];

                            if (!attrVal) {
                                let currentCopy = $.extend({}, currentSettings);
                                let attributesCopy = $.extend({}, attributes);

                                delete attributesCopy[attrName];
                                delete currentCopy[attrName];

                                for (let attributeCopyItem in attributesCopy) {
                                    if (!attributesCopy.hasOwnProperty(attributeCopyItem)) {
                                        continue;
                                    }

                                    // remove "any" values too
                                    if (attributesCopy[attributeCopyItem] === '') {
                                        delete attributesCopy[attributeCopyItem];
                                        delete currentCopy[attributeCopyItem];
                                    }
                                }

                                if (JSON.stringify(attributesCopy) === JSON.stringify(currentCopy)) {
                                    $values.addClass('active');
                                }
                            }

                            // Decode entities
                            attrVal = $('<div/>').html(attrVal).text();
                            // Add slashes
                            attrVal = attrVal.replace(/'/g, "\\'");
                            attrVal = attrVal.replace(/"/g, '"');
                            // Compare the meerkat
                            $values.filter(attrVal !== '' ? `[value="${attrVal}"]` : '*').addClass('active');
                        }
                    }

                    // Detach inactive
                    $values.filter(':not(.active)').attr('disabled', true);

                    // choose a not-disabled value
                    if ($element.prop('tagName') === 'SELECT') {
                        const $activeValue = $element.find('option:selected');

                        if ($activeValue.prop('disabled')) {
                            const $otherValues = $element.find('option:not([disabled])');

                            if ($otherValues.length > 0) {
                                $element.val($otherValues.eq(0).attr('value'));
                            }
                        }
                    } else {
                        const $activeValue = $values.filter(':checked');

                        if ($activeValue.prop('disabled')) {
                            const $otherValues = $values.filter(':not([disabled])');

                            if ($otherValues.length > 0) {
                                $otherValues.eq(0).prop('checked', true);
                            }
                        }
                    }
                });

                // Custom event for when variations have been updated
                $variationForm.trigger('woocommerce_update_variation_values');

                return this;
            })

            // Show single variation details (price, stock, image)
            .on('found_variation', function (event, variation) {
                const $variationForm = $(this);
                const $product = $variationForm.closest('[data-component~="wcpw-product"]');
                const $productDescription = $product.find('[data-component~="wcpw-product-description"]');
                const $productAvailability = $product.find('[data-component~="wcpw-product-availability"]');
                const $productAddToCart = $product.find('[data-component~="wcpw-add-cart-product"]');
                const $productChoose = $product.find('[data-component~="wcpw-product-choose"]');
                const $productQuantity = $product
                    .find('[data-component~="wcpw-product-quantity"] :input:not([type="button"])');

                let purchasable = true;

                // change price
                if (variation.price_html) {
                    $product
                        .find('[data-component~="wcpw-product-price"]')
                        .html(variation.price_html);
                }

                // change min quantity
                if (variation.min_qty) {
                    $productQuantity.attr('min', variation.min_qty);
                }

                // change max quantity
                if (variation.max_qty) {
                    $productQuantity.attr('max', variation.max_qty);
                }

                // change description
                if (variation.description) {
                    // different versions of woocommerce
                    $productDescription.html(variation.description);
                } else if (variation.variation_description) {
                    // different versions of woocommerce
                    $productDescription.html(variation.variation_description);
                } else {
                    $productDescription.html($productDescription.data('default'));
                }

                // change availability
                if (variation.availability_html) {
                    $productAvailability.html(variation.availability_html);
                } else {
                    $productAvailability.html($productAvailability.data('default'));
                }

                // enable or disable the add to cart button and checkbox/radio
                if (!variation.is_purchasable || !variation.is_in_stock || !variation.variation_is_visible) {
                    purchasable = false;
                }

                $productAddToCart.add($productChoose).attr('disabled', !purchasable);

                return $.fn.wcpwVariationFormUpdateImage($form, variation);
            });

        $form.trigger('check_variations');
        $form.trigger('wc_variation_form');
        $form.trigger('launched.variationForm.wcProductsWizard');

        return $form;
    };

    /**
     * Reset a default attribute for an element so it can be reset later
     * @param {String} attr - attribute name
     * @return {Object} this
     */
    $.fn.wcpwVariationFormResetAttribute = function (attr) {
        if (typeof this.attr(`data-o_${attr}`) !== 'undefined') {
            this.attr(attr, this.attr(`data-o_${attr}`));
        }

        return this;
    };

    /**
     * Stores a default attribute for an element so it can be reset later
     * @param {String} attr - attribute name
     * @param {String} value - attribute value
     * @return {Object} this
     */
    $.fn.wcpwVariationFormSetAttribute = function (attr, value) {
        if (typeof this.attr(`data-o_${attr}`) === 'undefined') {
            this.attr(`data-o_${attr}`, !this.attr(attr) ? '' : this.attr(attr));
        }

        if (value === false) {
            this.removeAttr(attr);
        } else {
            this.attr(attr, value);
        }

        return this;
    };

    /**
     * Sets product images for the chosen variation
     * @param {Object} $variationForm - jQuery element
     * @param {Object} variation - variation data
     * @return {Object} this
     */
    $.fn.wcpwVariationFormUpdateImage = function ($variationForm, variation) {
        const $product = $variationForm.closest('[data-component~="wcpw-product"]');
        const $productImg = $product.find('[data-component~="wcpw-product-thumbnail-image"]');
        const $productLink = $product.find('[data-component~="wcpw-product-thumbnail-link"]');

        if (variation && variation.image && variation.image.src && variation.image.src.length > 1) {
            $productImg.wcpwVariationFormSetAttribute('src', variation.image_src || variation.image.src);
            $productImg.wcpwVariationFormSetAttribute('srcset', variation.image_srcset || variation.image.srcset);
            $productImg.wcpwVariationFormSetAttribute('sizes', variation.image_sizes || variation.image.sizes);
            $productImg.wcpwVariationFormSetAttribute('title', variation.image_title || variation.image.title);
            $productImg.wcpwVariationFormSetAttribute('alt', variation.image_alt || variation.image.alt);
            $productLink.wcpwVariationFormSetAttribute('href', variation.image_link || variation.image.full_src);
        } else {
            $productImg.wcpwVariationFormResetAttribute('src');
            $productImg.wcpwVariationFormResetAttribute('srcset');
            $productImg.wcpwVariationFormResetAttribute('sizes');
            $productImg.wcpwVariationFormResetAttribute('alt');
            $productLink.wcpwVariationFormResetAttribute('href');
        }

        return this;
    };

    /**
     * Get product matching variations
     * @param {Object} productVariations - jQuery element
     * @param {Object} current - current properties object
     * @return {Array} matching
     */
    $.fn.wcpwVariationFormFindMatchingVariations = function (productVariations, current) {
        const matching = [];
        const addedVariationsIds = {};

        for (let variationKey in productVariations) {
            if (!productVariations.hasOwnProperty(variationKey)) {
                continue;
            }

            const variation = productVariations[variationKey];

            for (let currentItem in current) {
                if (!current.hasOwnProperty(currentItem)) {
                    continue;
                }

                let attributesCopy = $.extend({}, variation.attributes);
                let currentCopy = $.extend({}, current);

                // remove the same property from compare
                delete attributesCopy[currentItem];
                delete currentCopy[currentItem];

                for (let attributeCopyItem in attributesCopy) {
                    if (!attributesCopy.hasOwnProperty(attributeCopyItem)) {
                        continue;
                    }

                    // remove "any" values too
                    if (attributesCopy[attributeCopyItem] === '') {
                        delete attributesCopy[attributeCopyItem];
                        delete currentCopy[attributeCopyItem];
                    }
                }

                // if the other variation properties are the same as the current then allow this variation
                if (JSON.stringify(attributesCopy) === JSON.stringify(currentCopy)
                    && !addedVariationsIds.hasOwnProperty(variation.variation_id)
                ) {
                    addedVariationsIds[variation.variation_id] = variation.variation_id;
                    matching.push(variation);
                }
            }
        }

        return matching;
    };

    const init = function () {
        $('[data-component~="wcpw-product-variations"]').each(function () {
            return $(this).wcpwVariationForm();
        });
    };

    $(document)
        .ready(() => init())
        .on('ajaxCompleted.wcProductsWizard init.variationForm.wcProductsWizard', () => init());
});
