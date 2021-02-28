(function (root, factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(['jquery', 'wNumb', 'noUiSlider'], factory);
    } else if (typeof exports === 'object'
        && typeof module !== 'undefined'
        && typeof require === 'function'
    ) {
        module.exports = factory(
            require('jquery'),
            require('wNumb'),
            require('noUiSlider')
        );
    } else {
        factory(root.jQuery, root.wNumb, root.noUiSlider);
    }
})(this, function ($, wNumb, noUiSlider) {
    'use strict';

    const defaultOptions = {
        cssPrefix: 'wcpw-noUi-',
        direction: $('html').attr('dir') || 'ltr',
        range: {
            max: 100,
            min: 0
        },
        start: 50
    };

    function initSlider(element) {
        const $element = $(element);
        const options = $.extend({}, defaultOptions, $element.data('options'));
        const bindItems = [];

        // return if there is no range
        if (options.hasOwnProperty('range') && options.range.min === options.range.max) {
            return this;
        }

        $element.attr('data-launched', 'true');

        // handle output format
        if (options.hasOwnProperty('format') && typeof wNumb !== 'undefined') {
            options.format = wNumb(options.format);
        }

        noUiSlider.create(element, options);

        // get binding items
        if (options.hasOwnProperty('binding')) {
            if (typeof options.binding === 'string') {
                bindItems.push(options.binding);
            } else if (options.binding instanceof Array) {
                bindItems.push(...options.binding);
            }
        }

        if (bindItems.length > 0) {
            // handle items change
            bindItems.map((bindItem, index) => {
                let selector = bindItem;

                if (Array.isArray(bindItem)) {
                    const selectors = [];

                    bindItem.map((bindItemPart) => selectors.push(bindItemPart));
                    selector = selectors.join(',');
                }

                bindItems[index] = $(selector);

                // bind inputs change
                bindItems[index].each(function () {
                    const $bindItemPart = $(this);

                    if ($bindItemPart.is(':input')) {
                        $bindItemPart.on('change', () => {
                            const values = [];
                            const value = $bindItemPart.val();

                            for (let i = 0; i < index; i++) {
                                values.push(null);
                            }

                            values.push(value);

                            // apply value to the slide
                            element.noUiSlider.set(values);
                        });
                    }
                });

                return null;
            });
        }

        // on update action
        element
            .noUiSlider
            .on('update', (values, handle) => {
                const value = values[handle];

                // update bind items
                if (bindItems.length > 0) {
                    bindItems[handle].each(function () {
                        const $bindItemPart = $(this);

                        if ($bindItemPart.is(':input')) {
                            return $bindItemPart.val(value).trigger('changed');
                        }

                        return $bindItemPart.text(value);
                    });
                }
            });

        return this;
    }

    function init() {
        return $('[data-component~="wcpw-no-ui-slider"]:not([data-launched="true"])').each(function () {
            return initSlider(this);
        });
    }

    $(document)
        .ready(() => init())
        .on('init.nouislider', () => init())
        .on('update.option.nouislider', () =>
            $('[data-component~="wcpw-no-ui-slider"][data-launched="true"]').each(function () {
                const $element = $(this);
                const options = $.extend({}, defaultOptions, $element.data('options'));

                // return if there is no range
                if (options.hasOwnProperty('range') && options.range.min === options.range.max) {
                    return this;
                }

                // handle output format
                if (options.hasOwnProperty('format') && typeof wNumb !== 'undefined') {
                    options.format = wNumb(options.format);
                }

                return $element[0].noUiSlider.updateOptions(options);
            })
        );
});
