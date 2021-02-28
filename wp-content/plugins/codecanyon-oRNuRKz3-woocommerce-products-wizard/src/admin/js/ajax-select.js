/* WooCommerce Products Wizard Ajax Select
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

    const pluginName = 'wcpwAjaxSelect';
    const defaults = {};
    const ajaxRequests = {};
    const ajaxRequestsCache = {};

    const Plugin = function (element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);

        return this.init();
    };

    // on plugin init
    Plugin.prototype.init = function () {
        this.$element = $(this.element);
        this.$target = false;
        this.ajaxUrl = this.$element.data('ajax-url');

        if (this.$element.data('target-parent')) {
            this.$target = this.$element.parents(this.$element.data('target-parent'));
        }

        if (this.$element.data('target-selector')) {
            if (this.$target) {
                this.$target = this.$target.find(this.$element.data('target-selector'));
            } else {
                this.$target = $(this.$element.data('target-selector'));
            }
        }

        // set items by default
        this.getItems().done((response) => {
            this.updateItems(response);

            if (this.$element.data('value')) {
                this.$element.val(this.$element.data('value'));
            }
        });

        this.$target
            .off('change.ajaxSelect.wcpw')
            .on('change.ajaxSelect.wcpw', () => this.getItems().done((response) => this.updateItems(response)));

        return this;
    };

    // get items list via ajax
    Plugin.prototype.getItems = function () {
        const action = this.$element.data('action');
        const value = this.$target.val();

        if (value === '' || typeof value === 'undefined') {
            return $.when('');
        }

        const data = {
            action,
            value
        };

        if (ajaxRequestsCache[action] && ajaxRequestsCache[action][value]) {
            return $.when(ajaxRequestsCache[action][value]);
        }

        if (ajaxRequests[action] && ajaxRequests[action][value]) {
            return ajaxRequests[action][value];
        }

        const request = $.get(
            this.ajaxUrl,
            data,
            (response) => {
                if (!ajaxRequestsCache[action]) {
                    ajaxRequestsCache[action] = {};
                }

                ajaxRequestsCache[action][value] = response;

                $(document).trigger('itemsUpdated.ajaxSelect.wcpw', [this, response]);
            }
        );

        if (!ajaxRequests[action]) {
            ajaxRequests[action] = {};
        }

        ajaxRequests[action][value] = request;

        return request;
    };

    // update items list
    Plugin.prototype.updateItems = function (html) {
        this.$element.html(html);

        return this;
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'wcpw-ajax-select')) {
                $.data(this, 'wcpw-ajax-select', new Plugin(this, options));
            }
        });
    };

    const init = () => $('[data-component~="wcpw-ajax-select"]').each(function () {
        return $(this).wcpwAjaxSelect();
    });

    $(document)
        .ready(() => init())
        .on('ajaxComplete.wcpw init.ajaxSelect.wcpw', () => init());
});
