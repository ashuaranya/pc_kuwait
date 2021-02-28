/* WooCommerce Products Wizard Multi-select
 * Original author: troll_winner@mail.ru
 * Further changes, comments: troll_winner@mail.ru
 */

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

    const pluginName = 'wcpwMultiSelect';
    const defaults = {};

    const Plugin = function (element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);

        return this.init();
    };

    /**
     * Init the instance
     * @returns {Object} self instance
     */
    Plugin.prototype.init = function () {
        this.$element = $(this.element);
        this.$availableItems = this.$element.find('[data-component~="wcpw-multi-select-items-available"]');
        this.$selectedItems = this.$element.find('[data-component~="wcpw-multi-select-items-selected"]');
        this.$inputs = this.$element.find('[data-component~="wcpw-multi-select-inputs"]');

        return this;
    };

    /**
     * Add the new element in the table
     * @returns {Object} self instance
     */
    Plugin.prototype.addItem = function () {
        this.$availableItems
            .children(':selected')
            .each((i, selected) => {
                const $element = $(selected);

                $element.appendTo(this.$selectedItems);

                return this.$inputs
                    .children(`[value="${$element.val()}"]`)
                    .removeAttr('disabled');
            });

        $(document).trigger('added.item.multiSelect.wcpw', [this]);

        return this;
    };

    /**
     * Remove element from the table
     * @returns {Object} self instance
     */
    Plugin.prototype.removeItem = function () {
        this.$selectedItems
            .children(':selected')
            .each((i, selected) => {
                const $element = $(selected);

                $element.appendTo(this.$availableItems);

                return this.$inputs
                    .children(`[value="${$element.val()}"]`)
                    .attr('disabled', true);
            });

        $(document).trigger('removed.item.multiSelect.wcpw', [this]);

        return this;
    };

    /**
     * Move element upper in list
     * @returns {Object} self instance
     */
    Plugin.prototype.moveItemUp = function () {
        this.$selectedItems
            .children(':selected')
            .each((i, selected) => {
                const $element = $(selected);
                const $input = this.$inputs.children(`[value="${$element.val()}"]`);

                if ($element.prev().length <= 0) {
                    return this;
                }

                $element.insertBefore($element.prev());
                $input.insertBefore($input.prev());

                return this;
            });

        $(document).trigger('movedUp.item.multiSelect.wcpw', [this]);

        return this;
    };

    /**
     * Move element lower in list
     * @returns {Object} self instance
     */
    Plugin.prototype.moveItemDown = function () {
        $(this.$selectedItems
            .children(':selected')
            .get()
            .reverse())
            .each((i, selected) => {
                const $element = $(selected);
                const $input = this.$inputs.children(`[value="${$element.val()}"]`);

                if ($element.next().length <= 0) {
                    return this;
                }

                $element.insertAfter($element.next());
                $input.insertAfter($input.next());

                return this;
            });

        $(document).trigger('movedDown.item.multiSelect.wcpw', [this]);

        return this;
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'wcpw-multi-select')) {
                $.data(this, 'wcpw-multi-select', new Plugin(this, options));
            }
        });
    };

    const init = () => $('[data-component~="wcpw-multi-select"]').each(function () {
        return $(this).wcpwMultiSelect();
    });

    $(document)
        .ready(() => init())
        .on('init.multiSelect.wcpw', () => init())

        // add the form item
        .on('click', '[data-component~="wcpw-multi-select-add"]', function (event) {
            event.preventDefault();

            const $element = $(this);
            const $multiSelect = $element.closest('[data-component~="wcpw-multi-select"]');

            if ($multiSelect.data('wcpw-multi-select')) {
                return $multiSelect.data('wcpw-multi-select').addItem();
            }

            return this;
        })

        // remove the form item
        .on('click', '[data-component~="wcpw-multi-select-remove"]', function (event) {
            event.preventDefault();

            const $element = $(this);
            const $multiSelect = $element.closest('[data-component~="wcpw-multi-select"]');

            if ($multiSelect.data('wcpw-multi-select')) {
                return $multiSelect.data('wcpw-multi-select').removeItem();
            }

            return this;
        })

        // move item upper
        .on('click', '[data-component~="wcpw-multi-select-move-up"]', function (event) {
            event.preventDefault();

            const $element = $(this);
            const $multiSelect = $element.closest('[data-component~="wcpw-multi-select"]');

            if ($multiSelect.data('wcpw-multi-select')) {
                return $multiSelect.data('wcpw-multi-select').moveItemUp();
            }

            return this;
        })

        // move item lower
        .on('click', '[data-component~="wcpw-multi-select-move-down"]', function (event) {
            event.preventDefault();

            const $element = $(this);
            const $multiSelect = $element.closest('[data-component~="wcpw-multi-select"]');

            if ($multiSelect.data('wcpw-multi-select')) {
                return $multiSelect.data('wcpw-multi-select').moveItemDown();
            }

            return this;
        });
});
