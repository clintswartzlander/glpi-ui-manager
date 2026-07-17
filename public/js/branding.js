(function () {
    'use strict';

    var script = document.currentScript || Array.prototype.find.call(document.scripts, function (item) {
        return /\/js\/branding(?:\.min)?\.js(?:\?|$)/.test(item.src);
    });
    if (!script || !script.src) return;

    var endpoint = new URL('../front/branding.config.php', script.src);
    var selectors = {
        // GLPI 11 page_header.html.twig renders a background-painted span here.
        shared: '[data-testid="sidebar"] .navbar-brand > .glpi-logo',
        applicationName: '[data-testid="sidebar"] .navbar-brand'
    };
    var loaded = {expanded: '', collapsed: ''};
    var attempts = {expanded: 0, collapsed: 0};
    var loadResults = {expanded: 'not-configured', collapsed: 'not-configured'};

    var whenReady = function (callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, {once: true});
        } else {
            callback();
        }
    };

    var discoverTargets = function () {
        return {
            expanded: Array.from(document.querySelectorAll(selectors.shared)),
            collapsed: Array.from(document.querySelectorAll(selectors.shared)),
            shared: Array.from(document.querySelectorAll(selectors.shared)),
            applicationName: document.querySelector(selectors.applicationName)
        };
    };

    var navigationState = function () {
        return document.body.classList.contains('navbar-collapsed') ? 'collapsed' : 'expanded';
    };

    var absoluteAssetUrl = function (value) {
        return value ? new URL(value, endpoint).href : '';
    };

    var markTargets = function (kind) {
        discoverTargets()[kind].forEach(function (target) {
            if (target.getAttribute('data-uimanager-branding-logo-' + kind) !== 'ready') {
                target.setAttribute('data-uimanager-branding-logo-' + kind, 'ready');
            }
        });
    };

    var loadLogo = function (kind, value) {
        var url = absoluteAssetUrl(value);
        if (!url) return;
        attempts[kind]++;
        loadResults[kind] = 'loading';
        var image = new Image();
        image.onload = function () {
            loaded[kind] = url;
            loadResults[kind] = 'success';
            document.documentElement.style.setProperty(
                '--uimanager-' + kind + '-logo-image',
                'url(' + JSON.stringify(url) + ')'
            );
            markTargets(kind);
        };
        image.onerror = function () {
            loadResults[kind] = 'error';
        };
        image.src = url;
    };

    var applyApplicationName = function (name) {
        if (typeof name !== 'string' || name.trim() === '') return;
        name = name.trim();
        var target = discoverTargets().applicationName;
        if (target) {
            target.title = name;
            target.setAttribute('aria-label', name);
        }
        document.title = document.title.replace(/\bGLPI\b/g, function () { return name; });
    };

    var enableDebugDiagnostics = function () {
        if (!document.body.classList.contains('debug-active')) return;
        var snapshot = function () {
            var targets = discoverTargets();
            return {
                expandedConfigured: loaded.expanded !== '',
                collapsedConfigured: loaded.collapsed !== '',
                expandedUrl: loaded.expanded,
                collapsedUrl: loaded.collapsed,
                candidateCount: targets.shared.length,
                candidates: targets.shared.map(function (target) {
                    return {
                        selector: selectors.shared,
                        elementType: target.tagName.toLowerCase(),
                        originalBackground: target.dataset.uimanagerOriginalBackground || '',
                        replacementBackground: getComputedStyle(target).backgroundImage
                    };
                }),
                replacementAttempts: Object.assign({}, attempts),
                activeNavigationState: navigationState(),
                imageLoadResults: Object.assign({}, loadResults),
                imageError: loadResults.expanded === 'error' || loadResults.collapsed === 'error',
                customLogoStillApplied: targets.shared.every(function (target) {
                    var desired = navigationState() === 'collapsed' ? loaded.collapsed : loaded.expanded;
                    return desired === '' || getComputedStyle(target).backgroundImage.indexOf(desired) !== -1;
                })
            };
        };
        discoverTargets().shared.forEach(function (target) {
            target.dataset.uimanagerOriginalBackground = getComputedStyle(target).backgroundImage;
        });
        window.UIManagerBrandingDiagnostics = {snapshot: snapshot};
        var observer = new MutationObserver(function () {
            window.UIManagerBrandingDiagnostics.lastNavigationState = navigationState();
        });
        observer.observe(document.body, {attributes: true, attributeFilter: ['class']});
    };

    fetch(endpoint, {credentials: 'same-origin', headers: {'Accept': 'application/json'}})
        .then(function (response) { return response.ok ? response.json() : {}; })
        .then(function (config) {
            whenReady(function () {
                document.documentElement.setAttribute('data-uimanager-branding', '');
                if (config.css) {
                    var style = document.getElementById('uimanager-branding-runtime');
                    if (!style) {
                        style = document.createElement('style');
                        style.id = 'uimanager-branding-runtime';
                        document.head.appendChild(style);
                    }
                    style.textContent = config.css;
                }
                loadLogo('expanded', config.expanded_logo);
                loadLogo('collapsed', config.collapsed_logo);
                applyApplicationName(config.application_name);
                enableDebugDiagnostics();
            });
        }).catch(function () {});
}());
