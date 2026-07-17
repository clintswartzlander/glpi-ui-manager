(function () {
    'use strict';
    document.documentElement.setAttribute('data-uimanager-branding', '');
    var script = document.currentScript || Array.prototype.find.call(document.scripts, function (item) {
        return /\/js\/branding(?:\.min)?\.js(?:\?|$)/.test(item.src);
    });
    if (!script || !script.src) return;
    var endpoint = new URL('../front/branding.config.php', script.src);
    fetch(endpoint, {credentials: 'same-origin', headers: {'Accept': 'application/json'}})
        .then(function (response) { return response.ok ? response.json() : {}; })
        .then(function (config) {
            if (config.css) {
                var style = document.getElementById('uimanager-branding-runtime');
                if (!style) {
                    style = document.createElement('style');
                    style.id = 'uimanager-branding-runtime';
                    document.head.appendChild(style);
                }
                style.textContent = config.css;
            }
            var logoUrl = function (value) {
                return value ? new URL(value, endpoint).href : '';
            };
            var applyLogo = function (selector, value, name) {
                var url = logoUrl(value);
                if (!url) return;
                var image = new Image();
                image.onload = function () {
                    document.querySelectorAll(selector).forEach(function (target) {
                        target.src = url;
                        if (name) target.alt = name;
                    });
                };
                image.src = url;
            };
            var name = typeof config.application_name === 'string' ? config.application_name.trim() : '';
            applyLogo('.navbar-brand-image:not(.navbar-brand-image-collapsed)', config.expanded_logo, name);
            applyLogo('.navbar-brand-image-collapsed', config.collapsed_logo, name);
            if (name) {
                document.querySelectorAll('.navbar-brand').forEach(function (brand) {
                    brand.title = name;
                    brand.setAttribute('aria-label', name);
                });
                document.querySelectorAll('.navbar-brand-text').forEach(function (label) {
                    label.textContent = name;
                    label.title = name;
                });
                document.title = document.title.replace(/\bGLPI\b/g, function () { return name; });
            }
        }).catch(function () {});
}());
