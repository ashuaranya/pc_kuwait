/* WooCommerce Products Wizard Thumbnail
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

    const pluginName = 'wcpwThumbnail';
    const defaults = {};

    const Plugin = function (element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);

        return this.init();
    };

    // on plugin init
    Plugin.prototype.init = function () {
        this.$element = $(this.element);
        this.$id = this.$element.find('[data-component~="wcpw-thumbnail-id"]');
        this.$image = this.$element.find('[data-component~="wcpw-thumbnail-image"]');

        return this;
    };

    // open thumbnail modal
    Plugin.prototype.openModal = function () {
        // If the media frame already exists, reopen it.
        if (this.modalFrame) {
            this.modalFrame.open();

            return this;
        }

        // Create the media frame.
        this.modalFrame = wp.media.frames.downloadable_file = wp.media({
            title: 'Select image',
            button: {text: 'Select'},
            multiple: false
        });

        // When an image is selected, run a callback.
        this.modalFrame.on('select', () => {
            return this.modalFrame
                .state().get('selection')
                .map((attachment) => {
                    const attachmentJson = attachment.toJSON();

                    if (!attachmentJson.id) {
                        return null;
                    }

                    const src = {}.hasOwnProperty.call(attachmentJson, 'sizes')
                        && {}.hasOwnProperty.call(attachmentJson.sizes, 'thumbnail')
                        ? attachmentJson.sizes.thumbnail.url
                        : attachmentJson.url;

                    this.$image.html(`<img src="${src}">`);
                    this.$id.val(attachmentJson.id);

                    $(document).trigger('selected.thumbnail.wcpw', [this, attachment]);

                    return attachment;
                });
        });

        // Finally, open the modal
        return this.modalFrame.open();
    };

    // detach image is and remove image
    Plugin.prototype.removeImage = function () {
        this.$image.html('');
        this.$id.val('');

        $(document).trigger('removed.thumbnail.wcpw', [this]);

        return this;
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'wcpw-thumbnail')) {
                $.data(this, 'wcpw-thumbnail', new Plugin(this, options));
            }
        });
    };

    const init = () => $('[data-component~="wcpw-thumbnail"]').each(function () {
        return $(this).wcpwThumbnail();
    });

    $(document)
        .ready(() => init())
        .on('init.thumbnail.wcpw', () => init())
        // set thumbnail
        .on('click', '[data-component~="wcpw-thumbnail-set"]', function (event) {
            event.preventDefault();

            const $button = $(this);
            const $thumbnail = $button.closest('[data-component~="wcpw-thumbnail"]');

            if ($thumbnail.data('wcpw-thumbnail')) {
                return $thumbnail.data('wcpw-thumbnail').openModal();
            }

            return this;
        })

        // remove thumbnail
        .on('click', '[data-component~="wcpw-thumbnail-remove"]', function (event) {
            event.preventDefault();

            const $button = $(this);
            const $thumbnail = $button.closest('[data-component~="wcpw-thumbnail"]');

            if ($thumbnail.data('wcpw-thumbnail')) {
                return $thumbnail.data('wcpw-thumbnail').removeImage();
            }

            return this;
        });
});
