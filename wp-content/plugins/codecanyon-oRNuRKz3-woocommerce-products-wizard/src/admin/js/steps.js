/* WooCommerce Products Wizard Steps
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

    const pluginName = 'wcpwSteps';
    const defaults = {};
    const $document = $(document);

    const Plugin = function (element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);

        return this.init();
    };

    Plugin.prototype.init = function () {
        this.$element = $(this.element);
        this.$select = this.$element.find('[data-component~="wcpw-steps-select"]');
        this.$list = this.$element.find('[data-component~="wcpw-steps-list"]');
        this.$modal = $('[data-component~="wcpw-step-modal"]');
        this.$modalBody = this.$modal.find('[data-component~="wcpw-step-modal-body"]');
        this.ajaxUrl = this.$element.data('ajax-url');

        if ($.fn.sortable) {
            // init sortable
            this.$list.sortable({items: '[data-component~="wcpw-steps-list-item"]'});
        }

        return this;
    };

    // add the new element
    Plugin.prototype.addItem = function () {
        const $listChildren = this.$list.children();
        let id = 0;

        if ($listChildren.length) {
            $listChildren.each(function () {
                id = Math.max(id, Number($(this).data('id')));
            });
        }

        id++;

        const $newItem = this.$element
            .find('[data-component~="wcpw-steps-list-item-template"]')
            .clone()
            .appendTo(this.$list)
            .attr('data-component', 'wcpw-steps-list-item')
            .attr('data-id', id);

        const $newItemSettings = $newItem.find('[data-component~="wcpw-steps-list-item-settings"]');
        const $newItemClone = $newItem.find('[data-component~="wcpw-steps-list-item-clone"]');

        $newItem
            .find('[data-component~="wcpw-steps-list-item-name"]')
            .text(`#${id}`)
            .end()
            .find('[data-component~="wcpw-steps-list-item-id"]')
            .attr('name', `_steps_ids[${id}]`)
            .attr('value', id);

        $newItemSettings.attr('data-settings', $newItemSettings.attr('data-settings').replace(/%STEP_ID%/g, id));
        $newItemClone.attr('data-settings', $newItemClone.attr('data-settings').replace(/%STEP_ID%/g, id));

        $document.trigger('added.item.steps.wcpw', [this, $newItemClone]);

        return $newItem;
    };

    // show modal
    Plugin.prototype.showModal = function () {
        this.$modal.addClass('is-opened');

        return this;
    };

    // hide modal
    Plugin.prototype.hideModal = function () {
        this.$modal.removeClass('is-opened');

        return this;
    };

    // get step settings form
    Plugin.prototype.getSettings = function (args) {
        const data = $.extend({}, {action: 'wcpwGetStepSettingsForm'}, args);

        return $.get(
            this.ajaxUrl,
            data,
            (response) => {
                // append data
                this.$modalBody.html(response);

                // show the modal
                this.showModal();

                $document.trigger('get.settings.item.steps.wcpw', [this, response]);
            }
        );
    };

    // save step settings
    Plugin.prototype.saveSettings = function ($form) {
        const data = {
            action: 'wcpwSaveStepSettings',
            post_id: $form.attr('data-post-id'),
            step_id: $form.attr('data-step-id'),
            values: $form.serialize()
        };

        return $.post(this.ajaxUrl, data, null, 'json');
    };

    // clone step with settings
    Plugin.prototype.cloneItem = function (id, sourceStep, targetStep) {
        const data = {
            action: 'wcpwCloneStepSettings',
            post_id: id,
            source_step: sourceStep,
            target_step: targetStep
        };

        return $.post(this.ajaxUrl, data, null, 'json');
    };

    // remove element
    Plugin.prototype.removeItem = function ($item) {
        this.$select
            .find(`[value="${$item.attr('data-id')}"]`)
            .removeClass('hidden');

        $item.remove();

        $document.trigger('removed.item.steps.wcpw', [this]);

        return this;
    };

    /**
     * Check HTML form validity
     * @param {Object} $form - jQuery object
     * @returns {Boolean} is valid
     */
    Plugin.prototype.checkFromValidity = function ($form) {
        let isValid = true;

        if ($form.length === 0) {
            return isValid;
        }

        $form.each(function () {
            if (this.checkValidity !== 'undefined') {
                isValid = this.checkValidity();
            }
        });

        return isValid;
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'wcpw-steps')) {
                $.data(this, 'wcpw-steps', new Plugin(this, options));
            }
        });
    };

    const init = () => $('[data-component~="wcpw-steps"]').each(function () {
        return $(this).wcpwSteps();
    });

    $document
        .ready(() => init())
        .on('ajaxComplete.wcpw init.steps.wcpw', () => init())

        // add form item
        .on('click', '[data-component~="wcpw-steps-add"]', function (event) {
            event.preventDefault();

            const $button = $(this);
            const $wcpwSteps = $button.closest('[data-component~="wcpw-steps"]');

            if ($wcpwSteps.data('wcpw-steps')) {
                return $wcpwSteps.data('wcpw-steps').addItem();
            }

            return this;
        })

        // remove form item
        .on('click', '[data-component~="wcpw-steps-list-item-remove"]', function (event) {
            event.preventDefault();

            const $button = $(this);
            const $wcpwSteps = $button.closest('[data-component~="wcpw-steps"]');

            if ($wcpwSteps.data('wcpw-steps')) {
                return $wcpwSteps.data('wcpw-steps')
                    .removeItem($button.closest('[data-component~="wcpw-steps-list-item"]'));
            }

            return this;
        })

        // open settings modal
        .on('click', '[data-component~="wcpw-steps-list-item-settings"]', function (event) {
            event.preventDefault();

            const $button = $(this);
            const $wcpwSteps = $button.closest('[data-component~="wcpw-steps"]');

            $button.addClass('is-loading');

            if ($wcpwSteps.data('wcpw-steps')) {
                return $wcpwSteps.data('wcpw-steps')
                    .getSettings($button.data('settings'))
                    .always(() => $button.removeClass('is-loading'));
            }

            return this;
        })

        // clone step
        .on('click', '[data-component~="wcpw-steps-list-item-clone"]', function (event) {
            event.preventDefault();

            const $button = $(this);
            const $wcpwSteps = $button.closest('[data-component~="wcpw-steps"]');

            $button.addClass('is-loading');

            if ($wcpwSteps.data('wcpw-steps')) {
                const $newItem = $wcpwSteps.data('wcpw-steps').addItem();

                return $wcpwSteps.data('wcpw-steps')
                    .cloneItem(
                        $button.data('settings').post_id,
                        $button.data('settings').step_id,
                        $newItem.data('id')
                    )
                    .always(() => $button.removeClass('is-loading'));
            }

            return this;
        })

        // save the item settings
        .on('submit', '[data-component~="wcpw-step-settings-form"]', function (event) {
            const $form = $(this);
            const $wcpwSteps = $('[data-component~="wcpw-steps"]');

            if ($wcpwSteps.data('wcpw-steps')) {
                if (!$wcpwSteps.data('wcpw-steps').checkFromValidity($form)) {
                    $form.find('[data-component~="wcpw-settings-group-content"]').addClass('is-visible');

                    return this;
                }

                event.preventDefault();

                $wcpwSteps.data('wcpw-steps').hideModal().saveSettings($form);
            }

            return this;
        })

        // close modal
        .on('click', '[data-component~="wcpw-step-modal-close"]', function (event) {
            event.preventDefault();

            const $wcpwSteps = $('[data-component~="wcpw-steps"]');

            if ($wcpwSteps.data('wcpw-steps')) {
                $wcpwSteps.data('wcpw-steps').hideModal();
            }

            return this;
        });
});
