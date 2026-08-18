/**
 * FSM FAQ – Allow open Divi toggles to be closed.
 *
 * Divi's accordion never collapses the item you click: for an accordion item it always
 * runs its "expand" path, so an open FAQ stays open. This adds that behavior.
 *
 * Two Divi implementation details make this work, and both are load-bearing:
 *
 * 1. Divi binds its click handler delegated on `body` and calls stopPropagation(), so a
 *    handler delegated on `document` would never run. This binds directly to the title
 *    elements instead, which fires at the target before the event reaches body.
 * 2. The open/close classes are swapped in the slide callback rather than immediately.
 *    Divi's expand routine bails unless the toggle has `et_pb_toggle_close`, so leaving
 *    `et_pb_toggle_open` in place until the animation ends stops Divi from re-opening
 *    the toggle we are closing.
 *
 * Opening and switching between FAQs remain Divi's responsibility. Only one FAQ is open
 * at a time either way.
 *
 * @since 1.1.0
 */
(function ($) {
	'use strict';

	var WRAPPER = '.fsm-faq-divi';
	var OPEN_CLASS = 'et_pb_toggle_open';
	var CLOSE_CLASS = 'et_pb_toggle_close';
	var TOGGLING_CLASS = 'et_pb_accordion_toggling';
	var DURATION = 700; // Matches Divi's own toggle animation.

	$(function () {
		$(WRAPPER + ' .et_pb_toggle_title').on('click', function () {
			var $toggle = $(this).closest('.et_pb_toggle');
			var $accordion = $toggle.closest(WRAPPER);

			if (!$accordion.length || $accordion.attr('data-allow-close') === '0') {
				return;
			}

			// Divi handles opening and switching; only a still-open toggle needs closing.
			if (!$toggle.hasClass(OPEN_CLASS) || $accordion.hasClass(TOGGLING_CLASS)) {
				return;
			}

			var $content = $toggle.children('.et_pb_toggle_content');

			if (!$content.length || $content.is(':animated')) {
				return;
			}

			$accordion.addClass(TOGGLING_CLASS);

			$content.slideUp(DURATION, function () {
				$toggle.removeClass(OPEN_CLASS).addClass(CLOSE_CLASS);
			});

			setTimeout(function () {
				$accordion.removeClass(TOGGLING_CLASS);
			}, DURATION + 50);
		});
	});
})(jQuery);
