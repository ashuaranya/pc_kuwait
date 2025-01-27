/* WooCommerce Products Wizard
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

    const pluginName = 'wcProductsWizard';
    const defaults = {
        documentNode: document,
        windowNode: window,
        bodySelector: 'html, body',
        ajaxActions: {
            submit: 'wcpwSubmit',
            addToMainCart: 'wcpwAddToMainCart',
            getStep: 'wcpwGetStep',
            skipStep: 'wcpwSkipStep',
            skipAll: 'wcpwSkipAll',
            reset: 'wcpwReset',
            addCartProduct: 'wcpwAddCartProduct',
            removeCartProduct: 'wcpwRemoveCartProduct',
            updateCartProduct: 'wcpwUpdateCartProduct'
        }
    };

    const Plugin = function (element, options) {
        this.element = element;
        this.options = $.extend({}, defaults, options);
        this.init();
    };

    // <editor-fold desc="Core">
    /**
     * Init the instance
     * @returns {Plugin} self instance
     */
    Plugin.prototype.init = function () {
        this.$body = $(this.options.bodySelector);
        this.$window = $(this.options.windowNode);
        this.$document = $(this.options.documentNode);
        this.$element = $(this.element);
        this.args = this.$element.data('arguments');
        this.hasError = false;
        this.productsWithError = [];

        this.$document.trigger('launched.wcProductsWizard', [this]);

        return this;
    };

    /**
     * Makes an ajax-request
     * @param {FormData | Object} data - object of arguments
     * @param {Object} options - object of options
     * @param {String} method - type of the request (get, post)
     * @returns {Promise} ajax request
     */
    Plugin.prototype.ajaxRequest = function (data, options = {}, method = 'post') {
        const formData = data instanceof FormData ? data : new FormData();
        const searchParams = new URLSearchParams(window.location.search);
        const defaultOptions = {
            processData: false,
            contentType: false,
            enctype: 'multipart/form-data',
            scrollingTopOnUpdate: Boolean(this.args.scrollingTopOnUpdate),
            scrollingTopGap: Number(this.args.scrollingUpGap),
            scrollingTopSpeed: 500,
            passCustomFields: true
        };

        options = $.extend({}, defaultOptions, options);

        // remove stepsData fields to pass them right from the form as binary
        delete data.stepsData;

        if (options.passCustomFields) {
            let $formElement = this.$element.find('[data-component="wcpw-form"]');

            $formElement = $formElement.length > 0 ? $formElement : this.$document.find('[data-component="wcpw-form"]');

            const formElementData = new FormData($formElement[0]);

            for (let pair of formElementData.entries()) {
                if (pair[0].includes('stepsData')
                    && (typeof pair[1] === 'object' && pair[1].name || typeof pair[1] === 'string')
                ) {
                    formData.append(pair[0], pair[1]);
                }
            }
        }

        if (!(data instanceof FormData)) {
            $.each(data, (key, value) =>
                formData.append(key, typeof value !== 'string' ? JSON.stringify(value) : value));
        }

        // save extra parameters
        $.each(this.args, (key, value) => formData.append(key, value));

        // delete 'add-to-cart' to not pass the attached product to the cart via AJAX
        formData.delete('add-to-cart');

        // add extra query from "get" parameter
        if (searchParams.has('wcpwFilter')) {
            formData.append('wcpwFilter', searchParams.get('wcpwFilter'));
        }

        if (searchParams.has('wcpwPage')) {
            formData.append('wcpwPage', searchParams.get('wcpwPage'));
        }

        if (searchParams.has('wcpwProductsPerPage')) {
            formData.append('wcpwProductsPerPage', searchParams.get('wcpwProductsPerPage'));
        }

        if (searchParams.has('wcpwOrderBy')) {
            formData.append('wcpwOrderBy', searchParams.get('wcpwOrderBy'));
        }

        this.$element
            .addClass('is-loading')
            .attr('aria-live', 'polite')
            .attr('aria-busy', 'true');

        this.$document.trigger('ajaxRequest.wcProductsWizard', [this, formData]);

        return $.ajax({
            url: this.args.ajaxUrl,
            method,
            data: formData,
            processData: options.processData,
            contentType: options.contentType,
            enctype: options.enctype,
            cache: false,
            success: (response) => {
                this.$element.removeClass('is-loading').removeAttr('aria-live').removeAttr('aria-busy');

                if (response.content) {
                    this.$element.html(response.content);
                }

                // scroll to top
                if (options.scrollingTopOnUpdate) {
                    let scrollTop = this.$element.offset().top;

                    // scroll to top
                    if (this.$window.scrollTop() > scrollTop) {
                        if (options.scrollingTopGap) {
                            scrollTop -= Number(options.scrollingTopGap);
                        }

                        this.$body.stop().animate({scrollTop}, options.scrollingTopSpeed);
                    }
                }

                this.$document.trigger('ajaxCompleted.wcProductsWizard', [this, response]);

                return response;
            },
            error: (xhr, status, error) => {
                this.$element.removeClass('is-loading');

                window.console.error(xhr, status, error);

                return alert(`Unexpected error occurred: ${xhr.status}, ${xhr.statusText}`);
            }
        });
    };
    // </editor-fold>

    // <editor-fold desc="Product actions">
    /**
     * Remove form item from the cart
     * @param {Object} data - object of arguments
     * @param {Object} options - object of method options
     * @returns {Promise} ajax request
     */
    Plugin.prototype.removeCartProduct = function (data = {}, options = {}) {
        const defaultOptions = {scrollingTopOnUpdate: false};
        const defaultData = {action: this.options.ajaxActions.removeCartProduct};

        data = $.extend({}, defaultData, data);
        options = $.extend({}, defaultOptions, options);

        // make custom request instead of the form submit
        return this.ajaxRequest(data, options);
    };

    /**
     * Update form item in the cart
     * @param {Object} data - object of arguments
     * @param {Object} options - object of method options
     * @returns {Promise} ajax request
     */
    Plugin.prototype.updateCartProduct = function (data = {}, options = {}) {
        const defaultOptions = {
            behavior: 'default',
            scrollingTopOnUpdate: false,
            passCustomFields: false
        };

        options = $.extend({}, defaultOptions, options);

        // change the action to submit
        switch (options.behavior) {
            default:
            case 'default':
                data = $.extend({}, {action: this.options.ajaxActions.updateCartProduct}, data);

                return this.submit(data, options);
            case 'submit':
                data = $.extend({}, {action: this.options.ajaxActions.submit}, data);

                return this.submit(data, options);
            case 'add-to-main-cart':
                return this.addToMainCart(data, options);
        }
    };

    /**
     * Add form item to the cart
     * @param {Object} data - object of arguments
     * @param {Object} options - object of method options
     * @returns {Promise} ajax request
     */
    Plugin.prototype.addCartProduct = function (data = {}, options = {}) {
        const defaultOptions = {
            behavior: 'default',
            scrollingTopOnUpdate: false,
            passCustomFields: false
        };

        options = $.extend({}, defaultOptions, options);

        // change the action to submit
        switch (options.behavior) {
            default:
            case 'default':
                data = $.extend({}, {action: this.options.ajaxActions.addCartProduct}, data);

                return this.submit(data, options);
            case 'submit':
                data = $.extend({}, {action: this.options.ajaxActions.submit}, data);

                return this.submit(data, options);
            case 'add-to-main-cart':
                return this.addToMainCart(data, options);
        }
    };
    // </editor-fold>

    // <editor-fold desc="Main actions">
    /**
     * Add selected products to the main cart
     * @param {Object} data - object of arguments
     * @param {Object} options - object of method options
     * @returns {Promise} ajax request
     */
    Plugin.prototype.addToMainCart = function (data = {}, options = {}) {
        const defaultData = {action: this.options.ajaxActions.addToMainCart};

        data = $.extend({}, defaultData, data);

        const result = this.submit(data, options);

        this.$document.trigger('addToMainCart.wcProductsWizard', [this, data, result]);

        if (!result) {
            return $.when();
        }

        return result.done((response) => {
            // has some product errors
            if (response.hasError || this.hasError) {
                this.$document.trigger('addToMainCart.error.wcProductsWizard', [this, data, response]);

                return response;
            }

            if (!response.preventRedirect && response.finalRedirectUrl) {
                this.$document.trigger('addToMainCartRedirect.wcProductsWizard', [this, data, response]);
                
                document.location = response.finalRedirectUrl;
            }

            return response;
        });
    };

    /**
     * Send custom products from the active step to the wizard cart
     * @param {Object} data - object of arguments
     * @param {Object} options - object of method options
     * @returns {Promise} ajax request or false
     */
    Plugin.prototype.submit = function (data = {}, options = {}) {
        // reset error state
        this.hasError = false;
        this.productsWithError = [];

        let $formElement = this.$element.find('[data-component="wcpw-form"]');

        $formElement = $formElement.length > 0 ? $formElement : this.$document.find('[data-component="wcpw-form"]');

        const formData = this.serializeObject($formElement);
        const defaultData = {
            action: this.options.ajaxActions.submit,
            productToAddKey: null,
            productsToAdd: [],
            productsToAddChecked: []
        };

        data = $.extend({}, defaultData, data, formData);

        if (data.productToAddKey) {
            // keep only one product by id
            $.each(data.productsToAdd, (key, product) => {
                if (`${product.step_id}-${product.product_id}` !== data.productToAddKey) {
                    delete data.productsToAdd[key];
                } else {
                    data.productsToAddChecked = {[product.step_id]: [product.product_id]};
                }
            });
        } else {
            delete data.productToAddKey;
        }

        this.$document.trigger('submit.wcProductsWizard', [this, data]);

        // has some errors
        if (this.hasError) {
            this.$document.trigger('submit.error.wcProductsWizard', [this, data]);

            return $.when();
        }

        // send ajax
        return this.ajaxRequest(data, options);
    };

    /**
     * Route to the required navigation event
     * @param {Object} args - object of arguments
     * @param {Object} options - object of method options
     * @returns {Object} nav function
     */
    Plugin.prototype.navRouter = function (args = {}, options = {}) {
        const action = args.action;

        // action will be added by a method
        delete args.action;

        switch (action) {
            case 'skip-step':
                return this.skipStep(args, options);

            case 'skip-all':
                return this.skipAll(args, options);

            case 'submit':
                return this.submit(args, options);

            case 'add-to-main-cart':
                return this.addToMainCart(args, options);

            case 'reset':
                return this.reset(args, options);

            case 'none':
                return null;

            case 'get-step':
            default:
                return this.getStep(args, options);
        }
    };

    /**
     * Skip form to the next step without adding products to the wizard cart
     * @param {Object} data - object of arguments
     * @param {Object} options - object of method options
     * @returns {Promise} ajax request
     */
    Plugin.prototype.skipStep = function (data = {}, options = {}) {
        const defaultData = {action: this.options.ajaxActions.skipStep};

        data = $.extend({}, defaultData, data);

        return this.ajaxRequest(data, options);
    };

    /**
     * Skip form to the next step without adding products to the wizard cart
     * @param {Object} data - object of arguments
     * @param {Object} options - object of method options
     * @returns {Promise} ajax request
     */
    Plugin.prototype.skipAll = function (data = {}, options = {}) {
        const defaultData = {action: this.options.ajaxActions.skipAll};

        data = $.extend({}, defaultData, data);

        return this.ajaxRequest(data, options);
    };

    /**
     * Get step content by the id
     * @param {Object} data - object of arguments
     * @param {Object} options - object of method options
     * @returns {Promise} ajax request
     */
    Plugin.prototype.getStep = function (data = {}, options = {}) {
        const defaultData = {action: this.options.ajaxActions.getStep};

        data = $.extend({}, defaultData, data);

        return this.ajaxRequest(data, options);
    };

    /**
     * Reset form to the initial state
     * @param {Object} data - object of arguments
     * @param {Object} options - object of method options
     * @returns {Promise} ajax request
     */
    Plugin.prototype.reset = function (data = {}, options = {}) {
        const defaultData = {action: this.options.ajaxActions.reset};

        data = $.extend({}, defaultData, data);

        return this.ajaxRequest(data, options);
    };
    // </editor-fold>

    // <editor-fold desc="Utils">
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
            } else {
                $form.find(':input').each(function () {
                    const $input = $(this);

                    if ($input.attr('required') && $input.val() === '') {
                        isValid = false;
                    }
                });
            }
        });

        return isValid;
    };

    /**
     * Parse any query data to an object
     * https://github.com/cobicarmel/jquery-serialize-object/
     * @param {Object} dataContainer - target
     * @param {Object} key - prop key
     * @param {Object} value - prop value
     * @returns {Object} recursive or null
     */
    Plugin.prototype.parseObject = function (dataContainer, key, value) {
        const isArrayKey = (/^[^\[\]]+\[]/).test(key);
        const isObjectKey = (/^[^\[\]]+\[[^\[\]]+]/).test(key);
        const keyName = key.replace(/\[.*/, '');

        if (isArrayKey) {
            if (!dataContainer[keyName]) {
                dataContainer[keyName] = [];
            }
        } else {
            if (!isObjectKey) {
                if (dataContainer.push) {
                    dataContainer.push(value);
                } else {
                    dataContainer[keyName] = value;
                }

                return null;
            }

            if (!dataContainer[keyName]) {
                dataContainer[keyName] = {};
            }
        }

        const nextKeys = key.match(/\[[^\[\]]*]/g);

        nextKeys[0] = nextKeys[0].replace(/\[|]/g, '');

        return this.parseObject(dataContainer[keyName], nextKeys.join(''), value);
    };

    /**
     * Get FormData as a recursive object
     * https://github.com/cobicarmel/jquery-serialize-object/
     * @param {Object} $form - jQuery object
     * @returns {Object} form data object
     */
    Plugin.prototype.serializeObject = function ($form) {
        const formData = new FormData($form.get(0));
        const data = {};

        for (let pair of formData.entries()) {
            this.parseObject(data, pair[0], pair[1]);
        }

        return data;
    };

    /**
     * Set URL request parameter value
     * @param {Object} args - key pair of params
     * @returns {Plugin} self instance
     */
    Plugin.prototype.setQueryArg = function (args) {
        if (!window.history || !window.history.pushState) {
            return this;
        }

        const params = new URLSearchParams(window.location.search);

        for (let key in args) {
            if (args.hasOwnProperty(key)) {
                params.set(key, args[key]);
            }
        }

        const newUrl = window.location.protocol + '//' + window.location.host + window.location.pathname
            + '?' + params.toString();

        window.history.pushState({path: newUrl}, '', newUrl);

        return this;
    };

    /**
     * Parse query string to an object
     * @param {String} string - string to parse
     * @returns {Object} parsed output
     */
    Plugin.prototype.queryStringToObject = function (string) {
        const output = {};

        if (!string) {
            return output;
        }

        const data = JSON.parse('{"' + decodeURI(string)
            .replace(/"/g, '\\"')
            .replace(/&/g, '","')
            .replace(/=/g,'":"') + '"}');

        $.each(data, (key, value) => {
            this.parseObject(output, key, value);
        });

        return output;
    };
    // </editor-fold>

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'wcpw')) {
                $.data(this, 'wcpw', new Plugin(this, options));
            }
        });
    };
});
