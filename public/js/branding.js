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
        }).catch(function () {});
}());
