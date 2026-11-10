/**
 * Toggle collapse of the current wp-admin section on parent re-click.
 * Uses WordPress #adminmenu markup only (no SPA).
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	function syncAria(li, anchor) {
		var collapsed = li.classList.contains('wl-submenu-collapsed');
		anchor.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
	}

	ready(function () {
		var menu = document.getElementById('adminmenu');
		if (!menu) {
			return;
		}

		menu.querySelectorAll('li.wp-has-current-submenu > a.menu-top').forEach(function (anchor) {
			var li = anchor.closest('li.menu-top');
			if (li) {
				syncAria(li, anchor);
			}
		});

		menu.addEventListener('click', function (event) {
			var anchor = event.target.closest('a.menu-top');
			if (!anchor || !menu.contains(anchor)) {
				return;
			}
			var li = anchor.closest('li.menu-top');
			if (!li || !li.classList.contains('wp-has-current-submenu')) {
				return;
			}
			// Current section only: re-click toggles collapse instead of navigating.
			event.preventDefault();
			li.classList.toggle('wl-submenu-collapsed');
			syncAria(li, anchor);
		});
	});
})();
