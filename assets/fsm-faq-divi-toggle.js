/**
 * FSM FAQ – Allow open Divi toggles to be closed.
 *
 * Divi's accordion always keeps one item open. Closing the currently open item is not
 * native Divi behavior; Foundation 5.0 adds it in the child-theme WCAG kit
 * (accessibility/js/divi-accordion-close.js). This is a scoped port of that script so
 * sites without the kit still get the same close-on-second-click on [fsm_display_faqs].
 *
 * Load-bearing details (shared with the kit script):
 *
 * 1. Bind directly to `.et_pb_toggle_title`, not delegated on document. Divi 5 binds on
 *    `body` and calls stopPropagation(), so a document-level handler never runs.
 * 2. Use slideToggle(700) and swap open/close classes in the callback. Divi's expand
 *    path bails unless the item has `et_pb_toggle_close`, so leaving `et_pb_toggle_open`
 *    in place until the animation ends stops Divi from re-opening the item we are closing.
 * 3. `et_pb_accordion_toggling` plus a 750ms timeout keeps this from fighting Divi's
 *    own animation when switching items.
 *
 * Opening and switching between FAQs remain Divi's responsibility.
 *
 * @since 1.1.0
 */
jQuery(function ($) {
	$('.fsm-faq-divi .et_pb_toggle_title').on('click', function () {
		var $toggle = $(this).closest('.et_pb_toggle');
		if ($toggle.hasClass('et_pb_accordion_toggling')) {
			return;
		}

		var $accordion = $toggle.closest('.et_pb_accordion');
		if (!$accordion.length || $accordion.attr('data-allow-close') === '0') {
			return;
		}

		if ($toggle.hasClass('et_pb_toggle_open')) {
			$accordion.addClass('et_pb_accordion_toggling');
			$toggle.find('.et_pb_toggle_content').slideToggle(700, function () {
				$toggle.removeClass('et_pb_toggle_open').addClass('et_pb_toggle_close');
			});
		}

		setTimeout(function () {
			$accordion.removeClass('et_pb_accordion_toggling');
		}, 750);
	});
});
