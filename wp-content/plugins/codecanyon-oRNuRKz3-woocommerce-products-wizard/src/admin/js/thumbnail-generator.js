/* WooCommerce Products Wizard Thumbnail Generator
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

    const pluginName = 'wcpwThumbnailGenerator';
    const defaults = {};

    const Plugin = function (element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);

        return this.init();
    };

    // on plugin init
    // on plugin init
    Plugin.prototype.init = function () {
        const _this = this;

        this.$element = $(this.element);
        this.$areasCount = this.$element.find('[data-component~="wcpw-thumbnail-generator-areas-count"]');
        this.$areaSelect = this.$element.find('[data-component~="wcpw-thumbnail-generator-area-select"]');
        this.$canvas = this.$element.find('[data-component~="wcpw-thumbnail-generator-canvas"]');
        this.ajaxUrl = this.$canvas.data('ajax-url') || '/wp-admin/admin-ajax.php';
        this.canvasSize = {
            width: this.$canvas.width(),
            height: this.$canvas.height()
        };

        this.resizableSettings = {
            containment: '[data-component~="wcpw-thumbnail-generator-canvas"]',
            snap: true,
            snapTolerance: 10,
            handles: 'all',
            resize: function (event, ui) {
                const $element = $(this).closest('[data-component~="wcpw-thumbnail-generator-area"]');

                $element.find('[data-component~="wcpw-thumbnail-generator-area-width"]').val(ui.size.width);
                $element.find('[data-component~="wcpw-thumbnail-generator-area-height"]').val(ui.size.height);

                event.preventDefault();
            }
        };

        this.draggableSettings = {
            containment: '[data-component~="wcpw-thumbnail-generator-canvas"]',
            snap: true,
            snapTolerance: 10,
            drag: function (event, ui) {
                const $element = $(this).closest('[data-component~="wcpw-thumbnail-generator-area"]');

                $element.find('[data-component~="wcpw-thumbnail-generator-area-y"]').val(ui.position.top);
                $element.find('[data-component~="wcpw-thumbnail-generator-area-x"]').val(ui.position.left);
            }
        };

        this.initUIHelpers();

        this.$element
            // change canvas width
            .on('change', '#_thumbnail_canvas_width', function () {
                return _this.setCanvasSize({width: $(this).val()});
            })

            // change canvas height
            .on('change', '#_thumbnail_canvas_height', function () {
                return _this.setCanvasSize({height: $(this).val()});
            })

            // clear canvas click
            .on('click', '[data-component~="wcpw-thumbnail-generator-clear"]', (event) => {
                event.preventDefault();

                return _this.clearCanvas();
            })

            // add an area
            .on('click', '[data-component~="wcpw-thumbnail-generator-area-add"]', function (event) {
                event.preventDefault();

                return _this.addArea();
            })

            // clone area click
            .on('click', '[data-component~="wcpw-thumbnail-generator-area-clone"]', function () {
                return _this.cloneArea($(this).closest('[data-component~="wcpw-thumbnail-generator-area"]'));
            })

            // remove the area
            .on('click', '[data-component~="wcpw-thumbnail-generator-area-remove"]', function () {
                return _this.removeArea($(this).closest('[data-component~="wcpw-thumbnail-generator-area"]'));
            })

            // change area x position
            .on('change', '[data-component~="wcpw-thumbnail-generator-area-x"]', function () {
                const $element = $(this);
                const $area = $element.closest('[data-component~="wcpw-thumbnail-generator-area"]');
                const $inner = $area.find('[data-component~="wcpw-thumbnail-generator-area-inner"]');
                let value = Number($element.val());

                if (value + $inner.width() > _this.canvasSize.width) {
                    value = _this.canvasSize.width - $inner.width();

                    setTimeout(() => $element.val(value), 0);
                }

                return _this.setAreaProperty($inner, {left: value});
            })

            // change area y position
            .on('change', '[data-component~="wcpw-thumbnail-generator-area-y"]', function () {
                const $element = $(this);
                const $area = $element.closest('[data-component~="wcpw-thumbnail-generator-area"]');
                const $inner = $area.find('[data-component~="wcpw-thumbnail-generator-area-inner"]');
                let value = Number($element.val());

                if (value + $inner.height() > _this.canvasSize.height) {
                    value = _this.canvasSize.height - $inner.height();

                    setTimeout(() => $element.val(value), 0);
                }

                return _this.setAreaProperty($inner, {top: value});
            })

            // change area width
            .on('change', '[data-component~="wcpw-thumbnail-generator-area-width"]', function () {
                const $element = $(this);
                const $area = $element.closest('[data-component~="wcpw-thumbnail-generator-area"]');
                const $inner = $area.find('[data-component~="wcpw-thumbnail-generator-area-inner"]');
                let value = Number($element.val());

                if (value + $inner.position().left > _this.canvasSize.width) {
                    value = _this.canvasSize.width - $inner.position().left;

                    setTimeout(() => $element.val(value), 0);
                }

                return _this.setAreaProperty($inner, {width: value});
            })

            // change area height
            .on('change', '[data-component~="wcpw-thumbnail-generator-area-height"]', function () {
                const $element = $(this);
                const $area = $element.closest('[data-component~="wcpw-thumbnail-generator-area"]');
                const $inner = $area.find('[data-component~="wcpw-thumbnail-generator-area-inner"]');
                let value = Number($element.val());

                if (value + $inner.position().top > _this.canvasSize.height) {
                    value = _this.canvasSize.height - $inner.position().top;

                    setTimeout(() => $element.val(value), 0);
                }

                return _this.setAreaProperty($inner, {height: value});
            })

            // change area order
            .on('click', '[data-component~="wcpw-thumbnail-generator-area-move"]', function (event) {
                event.preventDefault();

                const $element = $(this);
                const $area = $element.closest('[data-component~="wcpw-thumbnail-generator-area"]');

                return _this.changeAreaOrder($area, $element.data('direction'));
            });

        return this;
    };

    // initial ui helpers
    Plugin.prototype.initUIHelpers = function () {
        const $areas = this.$element.find('[data-component~="wcpw-thumbnail-generator-area-inner"]');

        if ($.fn.draggable) {
            $areas.draggable(this.draggableSettings);
        }

        if ($.fn.resizable) {
            $areas.resizable(this.resizableSettings);
        }

        return this;
    };

    // reCalculate the input names indexes
    Plugin.prototype.recalculateIds = function () {
        this.$canvas
            .find('[data-component~="wcpw-thumbnail-generator-area"]')
            .each(function (index) {
                const $element = $(this);

                $element.find('[data-component~="wcpw-thumbnail-generator-area-index"]').val(index);

                return $element
                    .find(':input')
                    .each(function () {
                        const name = $(this).attr('name');

                        if (!name) {
                            return this;
                        }

                        return $(this).attr('name', name.replace(/\[\d+]/g, `[${index}]`));
                    });
            });

        return this;
    };

    // change canvas size variables
    Plugin.prototype.setCanvasSize = function (value) {
        const css = {};

        if (typeof value.width !== 'undefined') {
            this.canvasSize.width = value.width;
            css.width = value.width + 'px';
        }

        if (typeof value.height !== 'undefined') {
            this.canvasSize.height = value.height;
            css.height = value.height + 'px';
        }

        this.$canvas.css(css);

        return this;
    };

    // clear canvas from areas
    Plugin.prototype.clearCanvas = function () {
        this.$canvas.html('');

        return this.recalculateAreasCount();
    };

    // add the new element to the canvas
    Plugin.prototype.addArea = function () {
        const data = {
            action: 'wcpwGetThumbnailGeneratorAreaView',
            id: this.$areaSelect.val(),
            type: this.$areaSelect.find(':selected').parent().data('type'),
            index: this.$canvas.find('[data-component~="wcpw-thumbnail-generator-area"]').length
        };

        return $.post(
            this.ajaxUrl,
            data,
            (response) => {
                this.$canvas.append(response.html);
                this.recalculateAreasCount().recalculateIds().initUIHelpers();

                $(document).trigger('added.area.thumbnailGenerator.wcpw', [this, response]);
            },
            'json'
        );
    };

    // clone area
    Plugin.prototype.cloneArea = function ($element) {
        const $clone = $element.clone();
        const $settingsModal = $clone.find('[data-component~="wcpw-thumbnail-generator-area-settings-modal"]');
        const $settingsModalOpen = $clone.find('[data-component~="wcpw-thumbnail-generator-area-settings-modal-open"]');
        const rand = ~~(Math.random() * 1000000);

        $settingsModal.attr('id', `wcpw-thumbnail-generator-area-settings-modal-${rand.toString()}`);
        $settingsModalOpen.attr('href', `#wcpw-thumbnail-generator-area-settings-modal-${rand.toString()}`);
        $clone.find('.ui-resizable-handle').remove();
        $clone.insertAfter($element);

        this.recalculateAreasCount().recalculateIds().initUIHelpers();

        $(document).trigger('cloned.area.thumbnailGenerator.wcpw', [this, $clone]);

        return this;
    };

    // remove element from the table
    Plugin.prototype.removeArea = function ($area) {
        $area.remove();

        this.recalculateAreasCount().recalculateIds();

        $(document).trigger('removed.area.thumbnailGenerator.wcpw', [this]);

        return this;
    };

    // apply css the the area element
    Plugin.prototype.setAreaProperty = function ($element, value, force = false) {
        const css = {};
        const elementHeight = $element.height();
        const elementWidth = $element.width();
        const elementPosition = $element.position();

        if (typeof value.top !== 'undefined'
            && (force || value.top + elementHeight <= this.canvasSize.height && !force)
        ) {
            css.top = value.top + 'px';
        }

        if (typeof value.left !== 'undefined'
            && (force || value.left + elementWidth <= this.canvasSize.width && !force)
        ) {
            css.left = value.left + 'px';
        }

        if (typeof value.width !== 'undefined'
            && (force || value.width + elementPosition.left <= this.canvasSize.width && !force)
        ) {
            css.width = value.width + 'px';
        }

        if (typeof value.height !== 'undefined'
            && (force || value.height + elementPosition.top <= this.canvasSize.height && !force)
        ) {
            css.height = value.height + 'px';
        }

        $element.css(css);

        return this;
    };

    // change areas order
    Plugin.prototype.changeAreaOrder = function ($element, direction) {
        switch (direction) {
            default:
            case 'up':
                if ($element.prev().length > 0) {
                    $element.prev().insertAfter($element);
                }

                break;
            case 'down':
                if ($element.next().length > 0) {
                    $element.next().insertBefore($element);
                }

                break;
        }

        return this.recalculateIds();
    };

    // reCalculate areas count
    Plugin.prototype.recalculateAreasCount = function () {
        this.$areasCount.text(this.$canvas.find('[data-component~="wcpw-thumbnail-generator-area"]').length);

        return this;
    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'wcpw-thumbnail-generator')) {
                $.data(this, 'wcpw-thumbnail-generator', new Plugin(this, options));
            }
        });
    };

    const init = function () {
        return $('[data-component~="wcpw-thumbnail-generator-canvas"]').each(function () {
            return $(this).closest('[data-component~="wcpw-settings-group-content"]').wcpwThumbnailGenerator();
        });
    };

    $(document)
        .ready(() => init())
        .on('init.thumbnailGenerator.wcpw', () => init());
});
