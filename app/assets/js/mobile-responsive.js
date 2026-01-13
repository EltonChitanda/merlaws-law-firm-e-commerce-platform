// app/assets/js/mobile-responsive.js
// Mobile enhancements and safe no-op fallbacks

(function () {
    'use strict';

    // Detect touch support and set a class on the document for CSS hooks
    try {
        var hasTouch = (('ontouchstart' in window) || (navigator.maxTouchPoints > 0));
        document.documentElement.classList.toggle('has-touch', !!hasTouch);
    } catch (e) {
        // no-op
    }

    // Enable any [data-toggle="collapse"] targets for simple mobile menus
    function bindCollapsibleToggles() {
        var toggles = document.querySelectorAll('[data-toggle="collapse"][data-target]');
        if (!toggles || toggles.length === 0) return;

        toggles.forEach(function (toggle) {
            var targetSelector = toggle.getAttribute('data-target');
            var target = targetSelector ? document.querySelector(targetSelector) : null;
            if (!target) return;

            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', String(!isExpanded));
                target.classList.toggle('show');
            }, { passive: true });
        });
    }

    // Make tables horizontally scrollable on small screens if not already
    function makeTablesResponsive() {
        var tables = document.querySelectorAll('table');
        tables.forEach(function (table) {
            if (table.closest('.table-responsive')) return;
            var wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });
    }

    // Defer to DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            bindCollapsibleToggles();
            makeTablesResponsive();
        });
    } else {
        bindCollapsibleToggles();
        makeTablesResponsive();
    }
})(); 