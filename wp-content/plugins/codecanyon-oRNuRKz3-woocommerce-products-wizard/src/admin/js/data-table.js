/* WooCommerce Products Wizard DataTable
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

    const pluginName = 'wcpwDataTable';
    const defaults = {};

    const Plugin = function (element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);

        return this.init();
    };

    // on plugin init
    Plugin.prototype.init = function () {
        this.$element = $(this.element);

        if ($.fn.sortable) {
            // init sortable
            this.$element.sortable({
                items: '[data-component~="wcpw-data-table-item"]',
                update: () => this.recalculation()
            });
        }

        return this;
    };

    // add the new element in the table
    Plugin.prototype.addItem = function ($insertAfterItem) {
        $insertAfterItem = $insertAfterItem.length !== 0
            ? $insertAfterItem
            : this.$element.find('[data-component~="wcpw-data-table-item"]:last');

        let $template = this.$element.find('[data-component~="wcpw-data-table-item-template"]');

        if ($template.length === 0) {
            $template = $insertAfterItem;
        }

        // clone the new element from default template
        const $clone = $template.clone();
        
        $clone
            .attr('data-component', 'wcpw-data-table-item')
            .find(':input')
            .each(function () {
                const $this = $(this);

                // make real attributes from the placeholders
                $.each(this.attributes, (i, attr) => {
                    const name = attr.name;
                    const value = attr.value;

                    if (name.indexOf('data-make') === -1) {
                        return this;
                    }

                    return $this.attr(name.replace('data-make-', ''), value);
                });
            });

        const $settingsModal = $clone.find('[data-component~="wcpw-data-table-modal"]');
        const $settingsModalOpen = $clone.find('[data-component~="wcpw-data-table-modal-open"]');
        const rand = ~~(Math.random() * 1000000);

        $settingsModal.attr('id', `wcpw-data-table-modal-${rand.toString()}`);
        $settingsModalOpen.attr('href', `#wcpw-data-table-modal-${rand.toString()}`);

        // insert the clone element
        $clone.insertAfter($insertAfterItem);

        this.recalculation();

        $(document).trigger('added.item.dataTable.wcpw', [this, $clone]);

        return this;
    };

    // remove element from the table
    Plugin.prototype.removeItem = function ($item) {
        if ($item.is(':only-child')) {
            // clear values of the last item
            $item.find(':input').val('');
        } else {
            // remove the non-last item
            $item.remove();
        }

        this.recalculation();

        $(document).trigger('removed.item.dataTable.wcpw', [this]);

        return this;
    };

    // reCalculate the input names indexes
    Plugin.prototype.recalculation = function () {
        return this.$element
            .find('[data-component~="wcpw-data-table-item"]')
            .each(function (index) {
                return $(this)
                    .find(':input')
                    .each(function () {
                        const $input = $(this);
                        const name = $input.attr('name');

                        if (!name) {
                            return this;
                        }

                        const number = index.toString().split('').reverse().join('');

                        // replace the first array key from the end
                        return $input.attr(
                            'name',
                            name
                                .split('')
                                .reverse()
                                .join('')
                                .replace(/]\d+\[/, `]${number}[`)
                                .split('')
                                .reverse()
                                .join('')
                        );
                    });
            });
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'wcpw-data-table')) {
                $.data(this, 'wcpw-data-table', new Plugin(this, options));
            }
        });
    };

    const init = () => $('[data-component~="wcpw-data-table"]').each(function () {
        return $(this).wcpwDataTable();
    });

    $(document)
        .ready(() => init())
        .on('ajaxComplete.wcpw init.dataTable.wcpw', () => init())

        // add the form item
        .on('click', '[data-component~="wcpw-data-table-item-add"]', function () {
            const $button = $(this);
            const $dataTable = $button.closest('[data-component~="wcpw-data-table"]');

            if ($dataTable.data('wcpw-data-table')) {
                return $dataTable
                    .data('wcpw-data-table')
                    .addItem($button.closest('[data-component~="wcpw-data-table-item"]'));
            }

            return this;
        })

        // remove the form item
        .on('click', '[data-component~="wcpw-data-table-item-remove"]', function () {
            const $button = $(this);
            const $dataTable = $button.closest('[data-component~="wcpw-data-table"]');

            if ($dataTable.data('wcpw-data-table')) {
                return $dataTable
                    .data('wcpw-data-table')
                    .removeItem($button.closest('[data-component~="wcpw-data-table-item"]'));
            }

            return this;
        });
});
