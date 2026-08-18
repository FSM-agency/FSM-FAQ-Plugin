/**
 * FSM FAQ – Allow open Divi toggles to be closed.
 *
 * Divi's accordion script ignores clicks on a toggle that is already open, so an
 * expanded FAQ cannot be collapsed. This adds that behavior without replacing Divi's
 * script: the handler is delegated on document, so it runs after Divi's own handler
 * and only acts if the toggle is still open at that point. That check also makes it
 * a no-op on any Divi version that already closes the toggle itself.
 *
 * Opening and switching between FAQs remains Divi's responsibility. Only one FAQ is
 * open at a time either way.
 *
 * @since 1.1.0
 */
(function ($) {
	'use strict';

	var WRAPPER = '.fsm-faq-divi';
	var OPEN_CLASS = 'et_pb_toggle_open';
	var CLOSE_CLASS = 'et_pb_toggle_close';

	$(function () {
		$(document).on('click', WRAPPER + ' .et_pb_toggle_title', function () {
			var $toggle = $(this).closest('.et_pb_toggle');
			var $wrapper = $toggle.closest(WRAPPER);

			if (!$wrapper.length || $wrapper.attr('data-allow-close') === '0') {
				return;
			}

			// Divi handles opening and switching; only an still-open toggle needs closing.
			if (!$toggle.hasClass(OPEN_CLASS)) {
				return;
			}

			var $content = $toggle.find('.et_pb_toggle_content').first();

			if (!$content.length) {
				$toggle.removeClass(OPEN_CLASS).addClass(CLOSE_CLASS);
				return;
			}

			$content.stop(true, true).slideUp(400, function () {
				$toggle.removeClass(OPEN_CLASS).addClass(CLOSE_CLASS);
				// Clear the inline display left by slideUp so Divi's class-based CSS applies.
				$content.css('display', '');
			});
		});
	});
})(jQuery);
