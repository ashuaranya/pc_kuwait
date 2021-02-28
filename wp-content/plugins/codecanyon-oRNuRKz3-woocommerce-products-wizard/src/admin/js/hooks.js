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
    
    const $document = $(document);
    const $body = $(document.body);

    function toggleStyleSettingFields($element) {
        const value = $element.val();

        if (value === 'simple') {
            $element.closest('tr')
                .nextAll('tr').removeClass('hidden')
                .filter(':nth-last-child(2)').addClass('hidden');
        } else if (value === 'advanced') {
            $element.closest('tr')
                .nextAll('tr').addClass('hidden')
                .filter(':nth-last-child(2), :last-child').removeClass('hidden');
        }
    }
    
    function toggleAvailabilityRulesFields($element) {
        $element.find('[data-component~="wcpw-data-table-body-item"][data-key="source"] :input').each(function () {
            const $input = $(this);

            $input.closest('[data-component~="wcpw-data-table-body-item"]').attr('data-value', $input.val());
        });
    }

    $document
        .ready(() => {
            toggleAvailabilityRulesFields($document);
            toggleStyleSettingFields($('#woocommerce_products_wizard_custom_styles_mode'));
        })

        // get step setting
        .on('get.settings.item.steps.wcpw', (event, instance) => {
            $body.trigger('wc-enhanced-select-init');
            $document.trigger('init.thumbnail.wcpw');
            $document.trigger('init.multiSelect.wcpw');

            toggleAvailabilityRulesFields(instance.$modalBody);
        })

        // data table item added
        .on('added.item.dataTable.wcpw', (event, instance, $element) => {
            $body.trigger('wc-enhanced-select-init');
            $document.trigger('init.thumbnail.wcpw');
            $document.trigger('init.ajaxSelect.wcpw');

            toggleAvailabilityRulesFields($element);
        })

        // product variations are loaded
        .on('woocommerce_variations_loaded', '#woocommerce-product-data', function () {
            const $element = $(this);
            
            $body.trigger('wc-enhanced-select-init');
            $document.trigger('init.thumbnail.wcpw');

            toggleAvailabilityRulesFields($element);
        })

        // availability setting source change
        .on('change', '[data-component~="wcpw-data-table-body-item"][data-key="source"] :input', function () {
            const $input = $(this);

            $input.closest('[data-component~="wcpw-data-table-body-item"]').attr('data-value', $input.val());
        })

        // set default cart content
        .on('click', '[data-component~="wcpw-set-default-cart-content"]', function (event) {
            event.preventDefault();

            if (!confirm('Change default cart content?')) {
                return this;
            }

            const $element = $(this);
            const data = {
                action: 'wcpwSetDefaultCartContentAjax',
                id: $element.data('id')
            };

            if (typeof $element.data('value') !== 'undefined') {
                data.value = $element.data('value');
            }

            return $.post(
                $element.data('ajax-url'),
                data,
                (response) => {
                    if (response) {
                        alert(response.message);
                    }
                },
                'json'
            );
        })

        // toggle settings group
        .on('click', '[data-component~="wcpw-settings-group-toggle"]', function (event) {
            event.preventDefault();

            const $element = $(this);
            const $groups = $element.closest('[data-component~="wcpw-settings-groups"]');
            const $content = $groups.find('[data-component~="wcpw-settings-group-content"]');
            const $selectedContent = $content.filter(`[data-id="${$element.data('id')}"]`);

            if ($selectedContent.hasClass('is-visible')) {
                $selectedContent.removeClass('is-visible');

                return this;
            }

            $content.removeClass('is-visible');
            $selectedContent.toggleClass('is-visible');

            return this;
        })

        // bulk action handle
        .on('click', '#doaction, #doaction2', function () {
            const $element = $(this);
            const $select = $element.prev('select');

            if ($select.val() === 'edit') {
                setTimeout(() => {
                    $('#woocommerce-fields-bulk').append($('#wcpw-bulk-edit-fields-template').html());

                    // re-init libraries
                    $body.trigger('wc-enhanced-select-init');
                    $document.trigger('init.dataTable.wcpw');
                    toggleAvailabilityRulesFields($document);
                }, 0);
            }
        })

        // on edit cancel fix
        .on('mouseover focus', '#doaction, #doaction2', function () {
            if (!$('#bulk-edit').is(':visible')) {
                $('#wcpw-bulk-edit-fields').remove();
            }
        })

        // custom styles mode change
        .on('change', '#woocommerce_products_wizard_custom_styles_mode', function () {
            toggleStyleSettingFields($(this));
        })

        // settings reset
        .on('click', '[data-component~="wcpw-settings-reset"]', function (event) {
            if (!confirm('Reset settings?')) {
                event.preventDefault();
            }
        })

        // thumbnail generator area added event
        .on('added.area.thumbnailGenerator.wcpw cloned.area.thumbnailGenerator.wcpw', function () {
            $document.trigger('init.thumbnail.wcpw');
        })

        // thumbnail image selected
        .on('selected.thumbnail.wcpw', function (event, instance, attachment) {
            // append image into thumbnail generator
            if (instance.$element.data('component') === 'wcpw-thumbnail-generator-area-image wcpw-thumbnail') {
                const attachmentJson = attachment.toJSON();

                if (!attachmentJson.id) {
                    return null;
                }

                const $area = instance.$element.closest('[data-component~="wcpw-thumbnail-generator-area"]')
                    .find('[data-component~="wcpw-thumbnail-generator-area-inner"]');

                $area.children('img').remove();
                $area.append(`<img src="${attachmentJson.url}">`);
            }

            return this;
        })

        // thumbnail image removed
        .on('removed.thumbnail.wcpw', function (event, instance) {
            // remove image from thumbnail generator
            if (instance.$element.data('component') === 'wcpw-thumbnail-generator-area-image wcpw-thumbnail') {
                instance.$element.closest('[data-component~="wcpw-thumbnail-generator-area"]')
                    .find('[data-component~="wcpw-thumbnail-generator-area-inner"] > img').remove();
            }
        });

    // select2 clear value fix
    $('.wc-product-search[data-allow-clear="1"]').on('select2:unselect', function () {
        $(this).html('<option value=""></option>');
    });
});
