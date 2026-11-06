
/*
 * Hamrahang TV Debug v4
 * ES3-compatible diagnostics for old Samsung / Maple browsers.
 *
 * Load this file as the FIRST script in <head>,
 * before all application scripts.
 *
 * Public helpers:
 *   tvlog('message')
 *   tvdebugMark('MARK')
 *   tvdebugGetLog()
 */

(function () {
    var logs = [];
    var panel = null;
    var badge = null;
    var report = null;
    var started = (new Date()).getTime();
    var beaconUrl = window.TVDEBUG_BEACON || '';
    var oldOnError = window.onerror;
    var timer = null;
    var beat = 0;
    var maxLines = 12;

    function safeString(value) {
        try {
            return String(value);
        } catch (e) {
            return '[unprintable]';
        }
    }

    function escapeHtml(value) {
        return safeString(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function elapsed() {
        return '+' + (
            Math.round(((new Date()).getTime() - started) / 100) / 10
        ) + 's';
    }

    function pageRoot() {
        return document.body || null;
        //return document.body || document.documentElement || null;
    }

    function setStyle(el, css) {
        var key;

        for (key in css) {
            if (css.hasOwnProperty(key)) {
                try {
                    el.style[key] = css[key];
                } catch (e) {}
            }
        }
    }

    function sendBeacon(message) {
        if (!beaconUrl || !window.Image) {
            return;
        }

        try {
            var img = new Image();

            img.src = beaconUrl +
                (beaconUrl.indexOf('?') >= 0 ? '&' : '?') +
                'm=' + encodeURIComponent(message) +
                '&t=' + (new Date()).getTime();

        } catch (e) {}
    }

    function createPanel() {
        var root = pageRoot();

        if (!root || panel) {
            return;
        }

        try {
            panel = document.createElement('div');
            panel.id = 'tvdebug-panel';

            setStyle(panel, {
                position: 'fixed',
                right: '0',
                //bottom:0,
                top: '0',
                width: 'auto',
                //height: '100%',
                maxHeight: '70%',
                //maxHeight: '22%', 
                overflow: 'hidden',
                backgroundColor: '#a01818',
                color: '#ffffff',
                fontFamily: 'Arial,sans-serif',
                fontSize: '13px',
                lineHeight: '18px',
                //padding: '5px 1%',
                zIndex: '2147483647',
                textAlign: 'left'
            });

            root.appendChild(panel);

        } catch (e) {
            panel = null;
        }
    }

    function render() {
        var i;
        var start;
        var output = [];

        createPanel();

        if (!panel) {
            return;
        }

        start = logs.length > maxLines ?
            logs.length - maxLines : 0;

        for (i = start; i < logs.length; i++) {
            output.push(escapeHtml(logs[i]));
        }

        try {
            panel.innerHTML = output.join('<br />');
        } catch (e) {}
    }

    function log(message) {
        var line = elapsed() + ' ' + safeString(message);

        logs.push(line);

        if (logs.length > 100) {
            logs.shift();
        }

        render();
        sendBeacon(line);
    }

    window.tvlog = log;

    window.tvdebugMark = function (name) {
        log('MARK: ' + safeString(name));
    };

    window.tvdebugGetLog = function () {
        return logs.join('\n');
    };
    function checkBodyStyle() {
    var b = document.body;
    var h = document.documentElement;

    log('PAGE URL: ' + window.location.href);
    log('BODY TAG: ' + b.tagName);
    log('BODY STYLE ATTRIBUTE: ' + b.getAttribute('style'));
    log('BODY INLINE CSS: ' + b.style.cssText);
    log('BODY MARGIN: ' + b.style.margin);
    log('BODY POSITION: ' + b.style.position);
    log('HTML STYLE: ' + h.getAttribute('style'));

    var r = b.getBoundingClientRect();

    log('BODY RECT: ' +
        r.left + ',' + r.top + ' ' +
        r.width + 'x' + r.height);
}
    
    function inspectBodyCSS() {
    var b = document.body;
    var sheets = document.styleSheets;
    var i, j, sheet, rules, rule, selector, css;

    log('--- BODY CSS INSPECT ---');

    log('BODY INLINE: ' +
        (b.style.cssText || 'EMPTY'));

    log('BODY CLASS: ' + b.className);

    log('STYLESHEET COUNT: ' + sheets.length);

    for (i = 0; i < sheets.length; i++) {
        sheet = sheets[i];

        log('SHEET ' + i + ': ' +
            (sheet.href || 'INLINE'));

        try {
            rules = sheet.cssRules || sheet.rules;

            if (!rules) {
                log('  RULES: NOT ACCESSIBLE');
                continue;
            }

            for (j = 0; j < rules.length; j++) {
                rule = rules[j];

                if (!rule.selectorText) continue;

                selector = rule.selectorText.toLowerCase();

                if (selector.indexOf('body') >= 0 ||
                    selector.indexOf('html') >= 0) {

                    css = rule.style ? rule.style.cssText : '';

                    log('  ' + selector + ' {' + css + '}');
                }
            }
        } catch (e) {
            log('  CSS READ ERROR: ' + e.message);
        }
    }

    var r = b.getBoundingClientRect();

    log('BODY FINAL: ' +
        Math.round(r.left) + ',' +
        Math.round(r.top) + ' ' +
        Math.round(r.width) + 'x' +
        Math.round(r.height));

    log('--- END INSPECT ---');
}
    // =====================================================
    // TV DEBUG PANEL TOGGLE — 108 red / 112 / 403 TV / 120 F9 (Windows)
    // =====================================================
    
    var debugKeyPressed = false;
    
    function toggleDebugPanel() {
        createPanel();
    
        if (!panel) return;
    
        var hidden = (panel.style.display === 'none');
        panel.style.display = hidden ? 'block' : 'none';
    }
    
    function debugKeyDown(e) {
        e = e || window.event;
    
        var code = e.keyCode || e.which;
    
        /* 108 red, 112 F1/TV, 403 TV, 120 F9 Windows desktop */
        if (code !== 108 && code !== 112 && code !== 403 && code !== 120) return;
    
        if (!debugKeyPressed) {
            debugKeyPressed = true;
            toggleDebugPanel();
        }
    
        if (e.preventDefault) e.preventDefault();
        e.returnValue = false;
    
        if (e.stopPropagation) e.stopPropagation();
        e.cancelBubble = true;
    
        return false;
    }
    
    function debugKeyUp(e) {
        e = e || window.event;
        var code = e.keyCode || e.which;
        if (code === 108 || code === 112 || code === 403 || code === 120) {
            debugKeyPressed = false;
        }
    }
    
    if (window.addEventListener) {
        window.addEventListener('keydown', debugKeyDown, true);
        window.addEventListener('keyup', debugKeyUp, true);
    } else if (window.attachEvent) {
        window.attachEvent('onkeydown', debugKeyDown);
        window.attachEvent('onkeyup', debugKeyUp);
    }

    function errorHandler(message, url, line, column, errorObject) {
        var file = url ?
            safeString(url).split('/').pop() : '?';

        var text = 'JS ERROR: ' + safeString(message) +
            ' [' + file + ':' + safeString(line || '?');

        if (column) {
            text += ':' + safeString(column);
        }

        text += ']';

        if (errorObject && errorObject.stack) {
            text += ' STACK: ' +
                safeString(errorObject.stack).substring(0, 500);
        }

        log(text);

        if (typeof oldOnError === 'function' &&
            oldOnError !== errorHandler) {

            try {
                oldOnError(
                    message,
                    url,
                    line,
                    column,
                    errorObject
                );
            } catch (e) {}
        }

        return false;
    }

    window.onerror = errorHandler;

    function resourceError(event) {
        var target = event ?
            (event.target || event.srcElement) : null;

        var source = '';

        if (!target || target === window) {
            return;
        }

        try {
            source = target.src ||
                target.href ||
                target.data ||
                '';
        } catch (e) {}

        if (source) {
            log(
                'RESOURCE FAIL: ' +
                safeString(target.tagName || 'resource') +
                ' ' + safeString(source)
            );
        }
    }

    // Capture failed script, stylesheet and image loads.
    try {
        if (window.addEventListener) {
            window.addEventListener('error',resourceError,true);
        } else if (window.attachEvent) {
            log('NOTE: capture-phase resource error events unavailable');
        }
    } catch (e0) {
        log(
            'NOTE: resource error listener failed: ' +
            safeString(e0.message || e0)
        );
    }

    function readyState() {
        try {
            if (typeof document.readyState === 'undefined') {return 'undefined';}
            return safeString(document.readyState);
        } catch (e) {return 'unavailable';}
    }

    function elementCount() {
        try { return document.getElementsByTagName('*').length;
        } catch (e) { return '?'; }
    }

    function heartbeat() {
        var root = pageRoot();

        if (!root) {
            return;
        }

        try {
            if (!badge) {
                badge = document.createElement('div');
                badge.id = 'tvdebug-badge';

                setStyle(badge, {
                    position: 'fixed',
                    right: '0',
                    top: '0',
                    backgroundColor: '#0074c8',
                    color: '#ffffff',
                    fontFamily: 'Arial,sans-serif',
                    fontSize: '12px',
                    lineHeight: '17px',
                    padding: '2px 7px',
                    zIndex: '2147483647'
                });

                root.appendChild(badge);
            }

            badge.innerHTML =
                'TVDBG ' + readyState() + ' ' + elapsed();

        } catch (e) {}
    }

    function cssSupported(property, value) {
        var element;
        var result;

        try {
            element = document.createElement('div');
            element.style[property] = value;
            result = element.style[property];

            return result !== '' &&
                typeof result !== 'undefined';

        } catch (e) {
            return false;
        }
    }

    function featureReport() {
        var features = [];
        var html = document.documentElement;
        var canvas;
        var video;

        function addFeature(name, supported) {
            features.push(
                name + ':' + (supported ? 'Y' : 'N')
            );
        }

        try {
            canvas = document.createElement('canvas');
        } catch (e1) {
            canvas = {};
        }

        try {
            video = document.createElement('video');
        } catch (e2) {
            video = {};
        }

        addFeature(
            'XHR',
            typeof window.XMLHttpRequest !== 'undefined'
        );

        addFeature(
            'JSON',
            typeof window.JSON !== 'undefined'
        );

        addFeature(
            'localStorage',
            (function () {
                try {
                    return typeof window.localStorage !== 'undefined' &&
                        window.localStorage !== null;
                } catch (e) {
                    return false;
                }
            })()
        );

        addFeature(
            'querySelector',
            typeof document.querySelector === 'function'
        );

        addFeature(
            'addEventListener',
            typeof window.addEventListener === 'function'
        );

        addFeature(
            'attachEvent',
            typeof window.attachEvent === 'function'
        );

        addFeature(
            'classList',
            !!(html && html.classList)
        );

        addFeature(
            'className',
            !!(html && typeof html.className !== 'undefined')
        );

        addFeature(
            'bind',
            typeof Function.prototype.bind === 'function'
        );

        addFeature(
            'Array.isArray',
            typeof Array.isArray === 'function'
        );

        addFeature(
            'forEach',
            typeof Array.prototype.forEach === 'function'
        );

        addFeature(
            'Promise',
            typeof window.Promise !== 'undefined'
        );

        addFeature(
            'fetch',
            typeof window.fetch !== 'undefined'
        );

        addFeature(
            'canvas',
            typeof canvas.getContext === 'function'
        );

        addFeature(
            'video',
            typeof video.canPlayType === 'function'
        );

        addFeature(
            'flex',
            cssSupported('display', 'flex') ||
            cssSupported('display', '-webkit-box')
        );

        addFeature(
            'grid',
            cssSupported('display', 'grid')
        );

        addFeature(
            'transform',
            cssSupported(
                'webkitTransform',
                'translate(1px,1px)'
            ) ||
            cssSupported(
                'transform',
                'translate(1px,1px)'
            )
        );

        addFeature(
            'rgba',
            cssSupported(
                'backgroundColor',
                'rgba(0,0,0,0.5)'
            )
        );

        addFeature(
            'radius',
            cssSupported('borderRadius', '4px') ||
            cssSupported('webkitBorderRadius', '4px')
        );

        addFeature(
            'calc',
            cssSupported(
                'width',
                '-webkit-calc(10px + 1px)'
            ) ||
            cssSupported(
                'width',
                'calc(10px + 1px)'
            )
        );

        addFeature(
            'cssvars',
            cssSupported(
                'color',
                'var(--tvdebug-test)'
            )
        );

        return features.join('  ');
    }

    function showFeatureReport() {
        var root = pageRoot();
        var reportText;

        if (!root) {
            return;
        }

        reportText = featureReport();

        log('FEATURES: ' + reportText);

        try {
            if (report && report.parentNode) {
                report.parentNode.removeChild(report);
            }

            report = document.createElement('div');
            report.id = 'tvdebug-features';

            setStyle(report, {
                position: 'fixed',
                left: '0',
                top: '0',
                width: '98%',
                backgroundColor: '#003b60',
                color: '#bfffff',
                fontFamily: 'Arial,sans-serif',
                fontSize: '12px',
                lineHeight: '17px',
                padding: '4px 1%',
                zIndex: '2147483646',
                textAlign: 'left'
            });

            report.innerHTML = escapeHtml(reportText);
            root.appendChild(report);

            window.setTimeout(function () {
                try {
                    if (report && report.parentNode) {
                        report.parentNode.removeChild(report);
                    }
                } catch (e) {}
            }, 20000);

        } catch (e) {
            log(
                'FEATURE DISPLAY ERROR: ' +
                safeString(e.message || e)
            );
        }
    }

    function onDomReady() {
        log('DOMContentLoaded fired');
        //heartbeat();
    }

    function onWindowLoad() {
        log('window load fired');
        //setTimeout(inspectBodyCSS, 1500);
        //setTimeout(checkBodyStyle, 1500);
        //showFeatureReport();
    }

    log('tvdebug v4 started');

    try {
        log('URL: ' + window.location.href);
    } catch (e3) {}

    try {
        log('UA: ' + navigator.userAgent);
    } catch (e4) {}

    log(
        'initial readyState=' +
        readyState() +
        ', elements=' +
        elementCount()
    );

    if (window.addEventListener) {
        try {
            window.addEventListener(
                'load',
                onWindowLoad,
                false
            );
        } catch (e5) {}

        try {
            document.addEventListener(
                'DOMContentLoaded',
                onDomReady,
                false
            );
        } catch (e6) {}

    } else if (window.attachEvent) {
        try {
            window.attachEvent(
                'onload',
                onWindowLoad
            );
        } catch (e7) {}

    } else {
        var previousLoad = window.onload;

        window.onload = function () {
            if (typeof previousLoad === 'function') {
                try {
                    previousLoad();
                } catch (e8) {}
            }

            onWindowLoad();
        };
    }

    timer = window.setInterval(function () {
        beat++;
        //heartbeat();

        if (
            beat === 1 ||
            beat === 3 ||
            beat === 6 ||
            beat === 10 ||
            beat === 20 ||
            beat === 30
        ) {
            log(
                'HEARTBEAT: readyState=' +
                readyState() +
                ', elements=' +
                elementCount()
            );
        }

        if (beat >= 60) {
            window.clearInterval(timer);
            timer = null;

            if (badge && badge.parentNode) {
                try {
                    badge.parentNode.removeChild(badge);
                } catch (e9) {}

                badge = null;
            }
        }

    }, 1000);

    // Report unhandled Promise rejections when supported.
    try {
        if (window.addEventListener) {
            window.addEventListener(
                'unhandledrejection',
                function (event) {
                    var reason = event && event.reason ?
                        event.reason : 'unknown';

                    log(
                        'UNHANDLED REJECTION: ' +
                        safeString(reason)
                    );
                },
                false
            );
        }
    } catch (e10) {}
    
    // =====================================================
// TV INPUT & FOCUS DEBUG
// =====================================================

(function () {
    function getActiveElement() {
        var el = document.activeElement;

        if (!el) return 'NONE';

        return (
            el.tagName +
            (el.id ? '#' + el.id : '') +
            (el.className && typeof el.className === 'string'
                ? '.' + el.className.replace(/\s+/g, '.')
                : '') +
            (el.getAttribute('href')
                ? ' href=' + el.getAttribute('href')
                : '')
        );
    }

    function getKeyInfo(e) {
        e = e || window.event;

        return 'keyCode=' + (e.keyCode || 0) +
            ' which=' + (e.which || 0) +
            ' key=' + (e.key || '') +
            ' target=' + (
                e.target || e.srcElement
                    ? (e.target || e.srcElement).tagName
                    : 'UNKNOWN'
            );
    }

    function keyDown(e) {
        e = e || window.event;
        tvlog('REMOTE DOWN: ' + getKeyInfo(e));
        tvlog('ACTIVE: ' + getActiveElement());
    }

    function keyUp(e) {
        e = e || window.event;
        tvlog('REMOTE UP: ' + getKeyInfo(e));
        tvlog('ACTIVE: ' + getActiveElement());
    }

    function focusIn(e) {
        e = e || window.event;
        var el = e.target || e.srcElement;

        tvlog('FOCUS IN: ' +
            (el ? el.tagName + '#' + (el.id || '') +
                '.' + (typeof el.className === 'string'
                    ? el.className : '') : 'UNKNOWN'));
    }

    if (window.addEventListener) {
        window.addEventListener('keydown', keyDown, true);
        window.addEventListener('keyup', keyUp, true);
        window.addEventListener('focus', focusIn, true);
    } else if (window.attachEvent) {
        window.attachEvent('onkeydown', keyDown);
        window.attachEvent('onkeyup', keyUp);
        window.attachEvent('onfocus', focusIn);
    }

    tvlog('INPUT DEBUG: listeners installed');
})();

})();

// =====================================================
// SPATIAL NAVIGATION DEBUG
// =====================================================

tvlog('SPATIAL OBJECT: ' + typeof window.SpatialNavigation);

if (window.SpatialNavigation) {
    //tvlog('SPATIAL INIT: ' + typeof window.SpatialNavigation.init);
    //tvlog('SPATIAL ADD: ' + typeof window.SpatialNavigation.add);
    //tvlog('SPATIAL FOCUS: ' + typeof window.SpatialNavigation.focus);
    //tvlog('SPATIAL MAKEFOCUSABLE: ' + typeof window.SpatialNavigation.makeFocusable);
}
