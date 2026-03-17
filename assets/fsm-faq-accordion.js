/**
 * FSM FAQ – Generic accordion toggle (theme-agnostic fallback).
 * Uses W3Schools-style pattern: button + panel, toggle .active and maxHeight.
 *
 * @see https://www.w3schools.com/howto/howto_js_accordion.asp
 * @since 1.1.0
 */

(function () {
	'use strict';

	var CONTAINER_SELECTOR = '.fsm-faq-accordion';
	var BTN_SELECTOR = '.fsm-faq-accordion__btn';
	var PANEL_SELECTOR = '.fsm-faq-accordion__panel';
	var ACTIVE_CLASS = 'fsm-faq-accordion__btn--active';

	function init() {
		var containers = document.querySelectorAll(CONTAINER_SELECTOR);
		containers.forEach(function (container) {
			var buttons = container.querySelectorAll(BTN_SELECTOR);
			buttons.forEach(function (btn, index) {
				btn.setAttribute('aria-expanded', index === 0 ? 'true' : 'false');
				btn.addEventListener('click', function () {
					togglePanel(container, btn);
				});
			});
			// First panel open by default
			var firstPanel = container.querySelector(PANEL_SELECTOR);
			if (firstPanel && firstPanel.style) {
				firstPanel.style.maxHeight = firstPanel.scrollHeight + 'px';
			}
			var firstBtn = container.querySelector(BTN_SELECTOR);
			if (firstBtn) {
				firstBtn.classList.add(ACTIVE_CLASS);
			}
		});
	}

	function togglePanel(container, clickedBtn) {
		var isActive = clickedBtn.classList.contains(ACTIVE_CLASS);
		var panels = container.querySelectorAll(PANEL_SELECTOR);
		var buttons = container.querySelectorAll(BTN_SELECTOR);

		if (isActive) {
			clickedBtn.classList.remove(ACTIVE_CLASS);
			clickedBtn.setAttribute('aria-expanded', 'false');
			var idx = Array.prototype.indexOf.call(buttons, clickedBtn);
			if (panels[idx]) {
				panels[idx].style.maxHeight = null;
			}
			return;
		}

		// Close all in this accordion
		buttons.forEach(function (btn) {
			btn.classList.remove(ACTIVE_CLASS);
			btn.setAttribute('aria-expanded', 'false');
		});
		panels.forEach(function (panel) {
			panel.style.maxHeight = null;
		});

		// Open clicked
		clickedBtn.classList.add(ACTIVE_CLASS);
		clickedBtn.setAttribute('aria-expanded', 'true');
		var i = Array.prototype.indexOf.call(buttons, clickedBtn);
		if (panels[i]) {
			panels[i].style.maxHeight = panels[i].scrollHeight + 'px';
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
