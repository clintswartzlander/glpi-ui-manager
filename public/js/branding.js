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
            var one = function (selector) { return document.querySelector(selector); };
            var setImage = function (selector, source) {
                var image = source && one(selector); if (image) image.src = source;
            };
            setImage('.navbar-brand-image:not(.navbar-brand-image-collapsed)', config.expanded_logo);
            setImage('.navbar-brand-image-collapsed', config.collapsed_logo);
            setImage('body.page-anonymous .navbar-brand-image,body.page-anonymous .logo img', config.login_logo);
            if (config.favicon) {
                var icon = one('link[rel~="icon"]') || document.head.appendChild(document.createElement('link'));
                icon.rel = 'icon'; icon.href = config.favicon;
            }
            if (config.application_name) {
                var title = one('body.page-anonymous h1,body.page-anonymous .card-title');
                if (title) title.textContent = config.application_name;
                document.title = config.application_name;
            }
        }).catch(function () {});
}());
