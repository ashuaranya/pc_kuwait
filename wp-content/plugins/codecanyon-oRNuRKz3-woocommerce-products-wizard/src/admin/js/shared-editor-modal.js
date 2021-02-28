/* WooCommerce Products Wizard Shared Editor Modal
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
    
    $(document)
        // open shared wp-editor modal
        .on('click', '[data-component~="wcpw-shared-editor-open"]', function (event) {
            event.preventDefault();

            const $element = $(this);
            const $target = $element.next('[data-component~="wcpw-shared-editor-target"]');
            const $sharedEditorModal = $('#wcpw-shared-editor-modal');

            // for a modal in a modal
            if (window.location.hash && window.location.hash !== 'close') {
                $sharedEditorModal.find('[href]').each(function () {
                    return $(this).attr('href', window.location.hash);
                });
            }

            $sharedEditorModal.addClass('is-opened').data('target', $target);

            // set editor content
            if ($('#wp-shared-editor-wrap').hasClass('tmce-active') && window.tinyMCE.get('shared-editor')) {
                window.tinyMCE.get('shared-editor').setContent($target.val());
            } else {
                $('#shared-editor').val($target.val());
            }
        })

        // modal save click
        .on('click', '#wcpw-shared-editor-save', function () {
            const $sharedEditorModal = $('#wcpw-shared-editor-modal');
            let content = $('#shared-editor').val();

            // get editor content
            if ($('#wp-shared-editor-wrap').hasClass('tmce-active') && window.tinyMCE.get('shared-editor')) {
                content = window.tinyMCE.get('shared-editor').getContent();
            }

            $sharedEditorModal.data('target').val(content);
        })

        // modal close click
        .on('click', '[data-component~="wcpw-modal-close"]', function () {
            return $(this).closest('[data-component~="wcpw-modal"]').removeClass('is-opened');
        });
});
