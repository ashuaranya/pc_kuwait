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

    const init = () => $('[data-component="wcpw"]').each(function () {
        const $element = $(this);

        return $element.wcProductsWizard($element.data('options') || {});
    });

    $(document)
        .ready(() => init())
        .on('init.wcProductsWizard', () => init())

        // ajax query is complete
        .on('ajaxCompleted.wcProductsWizard', (event, instance, response) => {
            if (response.hasOwnProperty('stateHash')) {
                instance.setQueryArg({wcpwStateHash: response.stateHash});
            }
        })

        // prevent thumbnail link redirect on click
        .on('click.thumbnail.product.wcpw', '[data-component~="wcpw-product-thumbnail-link"]', function (event) {
            return event.preventDefault();
        })

        // change the active form item
        .on('click.product.wcpw', '[data-component="wcpw-product"]', function () {
            return $(this).find('[data-component~="wcpw-product-choose"][type="radio"]')
                .prop('checked', true)
                .trigger('change');
        })

        // remove product from the cart
        .on('click.remove.product.wcpw', '[data-component~="wcpw-remove-cart-product"]', function (event) {
            event.preventDefault();

            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');

            if ($wcpw.data('wcpw')) {
                return $wcpw.data('wcpw').removeCartProduct({productCartKey: $element.val()});
            }
            
            return this;
        })

        // update product in the cart
        .on('click.update.product.wcpw', '[data-component~="wcpw-update-cart-product"]', function (event) {
            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');

            if ($element.hasClass('disabled')) {
                return event.preventDefault();
            }

            if ($wcpw.data('wcpw')) {
                const $product = $element.closest('[data-component~="wcpw-product"]');
                const $inputs = $wcpw.find(':input:not(disabled):not(button)');
                const $otherInputs = $inputs.filter(function () {
                    return $(this).closest($product).length === 0;
                });

                $otherInputs.attr('disabled', 'disabled');

                if (!$wcpw.data('wcpw').checkFromValidity($('#' + $element.attr('form')))) {
                    $otherInputs.removeAttr('disabled');

                    return this;
                }

                $otherInputs.removeAttr('disabled');

                event.preventDefault();

                return $wcpw.data('wcpw')
                    .updateCartProduct({productCartKey: $element.val()}, {behavior: $element.data('behavior')});
            }
            
            return this;
        })

        // add product to the cart
        .on('click.add.product.wcpw', '[data-component="wcpw-add-cart-product"]', function (event) {
            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');

            if ($element.hasClass('disabled')) {
                return event.preventDefault();
            }
            
            if ($wcpw.data('wcpw')) {
                const $product = $element.closest('[data-component~="wcpw-product"]');
                const $inputs = $wcpw.find(':input:not(disabled):not(button)');
                const $otherInputs = $inputs.filter(function () {
                    return $(this).closest($product).length === 0;
                });

                $otherInputs.attr('disabled', 'disabled');

                if (!$wcpw.data('wcpw').checkFromValidity($('#' + $element.attr('form')))) {
                    $otherInputs.removeAttr('disabled');

                    return this;
                }

                $otherInputs.removeAttr('disabled');

                event.preventDefault();

                return $wcpw.data('wcpw')
                    .addCartProduct({productToAddKey: $element.val()}, {behavior: $element.data('behavior')});
            }

            return this;
        })

        // nav item click
        .on('click.nav.wcpw', '[data-component~="wcpw-nav-item"]', function (event) {
            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');
            
            if ($wcpw.data('wcpw')) {
                const action = $element.data('nav-action');

                if (!$wcpw.data('wcpw').checkFromValidity($('#' + $element.attr('form')))
                    && $.inArray(action, ['submit', 'add-to-main-cart']) !== -1
                ) {
                    return this;
                }

                event.preventDefault();

                return $wcpw.data('wcpw').navRouter({
                    action: action,
                    stepId: $element.data('nav-id')
                });
            }

            return this;
        })

        // filter submit
        .on('submit.filter.wcpw', '[data-component~="wcpw-filter"]', function (event) {
            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');

            event.preventDefault();

            if ($wcpw.data('wcpw')) {
                const searchParams = new URLSearchParams(window.location.search);
                const filterData = $wcpw.data('wcpw').serializeObject($element).wcpwFilter;
                let pages = {};
                let filters = {};

                // change filter query
                if (searchParams.has('wcpwFilter') && searchParams.get('wcpwFilter')) {
                    filters = $wcpw.data('wcpw').queryStringToObject(searchParams.get('wcpwFilter'));
                }

                filters[$element.data('step-id')] = filterData[$element.data('step-id')];

                // reset page query
                if (searchParams.has('wcpwPage') && searchParams.get('wcpwPage')) {
                    pages = $wcpw.data('wcpw').queryStringToObject(searchParams.get('wcpwPage'));
                    pages[$element.data('step-id')] = 1;
                }

                return $wcpw.data('wcpw')
                    .setQueryArg({
                        wcpwFilter: $.param(filters),
                        wcpwPage: $.param(pages)
                    })
                    .getStep({stepId: $element.data('step-id')});
            }

            return this;
        })

        // filter reset
        .on('reset.filter.wcpw', '[data-component~="wcpw-filter"]', function (event) {
            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');

            event.preventDefault();

            if ($wcpw.data('wcpw')) {
                const searchParams = new URLSearchParams(window.location.search);
                let filters = {};

                if (searchParams.has('wcpwFilter')) {
                    filters = $wcpw.data('wcpw').queryStringToObject(searchParams.get('wcpwFilter'));
                }

                filters[$element.data('step-id')] = [];

                return $wcpw.data('wcpw')
                    .setQueryArg({wcpwFilter: $.param(filters)})
                    .getStep({stepId: $element.data('step-id')});
            }

            return this;
        })

        // pagination link click
        .on('click.pagination.form.wcpw', '[data-component="wcpw-form-pagination-link"]', function (event) {
            event.preventDefault();

            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');

            if ($wcpw.data('wcpw')) {
                const searchParams = new URLSearchParams(window.location.search);
                let pages = {};

                // change page query
                if (searchParams.has('wcpwPage') && searchParams.get('wcpwPage')) {
                    pages = $wcpw.data('wcpw').queryStringToObject(searchParams.get('wcpwPage'));
                }

                pages[$element.data('step-id')] = $element.data('page');

                return $wcpw.data('wcpw')
                    .setQueryArg({wcpwPage: $.param(pages)})
                    .getStep(
                        {
                            stepId: $element.data('step-id'),
                            page: $element.data('page')
                        },
                        {scrollingTopOnUpdate: false}
                    );
            }

            return this;
        })

        // products per page change
        .on('change', '[data-component~="wcpw-form-products-per-page"]', function () {
            $(this).submit();
        })

        // products per page submit
        .on('submit.productsPerPage.wcpw', '[data-component~="wcpw-form-products-per-page"]', function (event) {
            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');

            event.preventDefault();

            if ($wcpw.data('wcpw')) {
                const searchParams = new URLSearchParams(window.location.search);
                const value = $wcpw.data('wcpw').serializeObject($element);
                let pages = {};
                let productsPerPage = {};

                if (searchParams.has('wcpwProductsPerPage')) {
                    productsPerPage = $wcpw.data('wcpw').queryStringToObject(searchParams.get('wcpwProductsPerPage'));
                }

                // reset page query
                if (searchParams.has('wcpwPage') && searchParams.get('wcpwPage')) {
                    pages = $wcpw.data('wcpw').queryStringToObject(searchParams.get('wcpwPage'));
                    pages[$element.data('step-id')] = 1;
                }

                productsPerPage[$element.data('step-id')] = value.wcpwProductsPerPage[$element.data('step-id')];

                return $wcpw.data('wcpw')
                    .setQueryArg({
                        wcpwProductsPerPage: $.param(productsPerPage),
                        wcpwPage: $.param(pages)
                    })
                    .getStep({stepId: $element.data('step-id')});
            }

            return this;
        })

        // products per page change
        .on('change', '[data-component="wcpw-form-order-by"]', function () {
            $(this).submit();
        })

        // products per page submit
        .on('submit.orderBy.wcpw', '[data-component~="wcpw-form-order-by"]', function (event) {
            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');

            event.preventDefault();

            if ($wcpw.data('wcpw')) {
                const searchParams = new URLSearchParams(window.location.search);
                const value = $wcpw.data('wcpw').serializeObject($element);
                let orderBy = {};

                if (searchParams.has('wcpwOrderBy')) {
                    orderBy = $wcpw.data('wcpw').queryStringToObject(searchParams.get('wcpwOrderBy'));
                }

                orderBy[$element.data('step-id')] = value.wcpwOrderBy[$element.data('step-id')];

                return $wcpw.data('wcpw')
                    .setQueryArg({wcpwOrderBy: $.param(orderBy)})
                    .getStep({stepId: $element.data('step-id')});
            }

            return this;
        })

        // toggle element
        .on('click.toggle.wcpw', '[data-component~="wcpw-toggle"]', function (event) {
            event.preventDefault();

            const $element = $(this);
            const $wcpw = $element.closest('[data-component~="wcpw"]');
            const target = $element.attr('data-target') || $element.attr('href');
            const $target = $wcpw.find(target);
            const isClosed = $target.attr('aria-expanded') === 'false';

            $element.add($target).attr('aria-expanded', isClosed ? 'true' : 'false');

            document.cookie = `${target}-expanded=${String(isClosed)}; path=/`;
            $(document.body).trigger('sticky_kit:recalc');
        });
});
