/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

(function () {

    var documentAlias = document,
        windowAlias = window;

    // Private function copied from piwik.js
    function getReferrer()
    {
        var referrer = '';

        try {
            referrer = windowAlias.top.document.referrer;
        } catch (e) {
            if (windowAlias.parent) {
                try {
                    referrer = windowAlias.parent.document.referrer;
                } catch (e2) {
                    referrer = '';
                }
            }
        }

        if (referrer === '') {
            referrer = documentAlias.referrer;
        }

        return referrer;
    }

    // Private function copied from piwik.js
    function safeDecodeWrapper(url)
    {
        try {
            return windowAlias.decodeURIComponent(url);
        } catch (e) {
            return unescape(url);
        }
    }

    // Private function copied from piwik.js
    function urlFixup(hostName, href, referrer)
    {
        if (!hostName) {
            hostName = '';
        }

        if (!href) {
            href = '';
        }

        if (hostName === 'translate.googleusercontent.com') {       // Google
            if (referrer === '') {
                referrer = href;
            }

            href = getUrlParameter(href, 'u');
            hostName = getHostName(href);
        } else if (hostName === 'cc.bingj.com' ||                   // Bing
        hostName === 'webcache.googleusercontent.com' ||    // Google
        hostName.slice(0, 5) === '74.6.') {                 // Yahoo (via Inktomi 74.6.0.0/16)
            href = documentAlias.links[0].href;
            hostName = getHostName(href);
        }

        return {
            hostName: hostName,
            href: href,
            referrer: referrer,
        };
    }

    function processUrl(config, url)
    {
        if (!config) config = {};
        if (!url) return "";

        var decodedUrl = safeDecodeWrapper(url);
        var finalUrl = decodedUrl;
        switch (config.disableFor)
        {
            case "regexp":
                var urlMatch = new RegExp(config.regexp, 'i').exec(url);

                if (!urlMatch) {
                    finalUrl = decodedUrl;
                } else if (urlMatch.length > 1) {
                    for (var i = 1; i < urlMatch.length; ++i) {
                        finalUrl = finalUrl.replaceAll(safeDecodeWrapper(urlMatch[i]), urlMatch[i]);
                    }
                } else {
                    finalUrl = url;
                }
                break;
            case "params":
                var params = url.split('?')[1];
                if (params && 'object' === typeof config.params) {
                    params.split('&').forEach(function (param) {
                        var keyVal = param.split('=');
                        if (config.params.includes(keyVal[0])) {
                            finalUrl = finalUrl.replaceAll(safeDecodeWrapper(keyVal[1]), keyVal[1]);
                        }
                    });
                }
                break;
            case "all":
                finalUrl = url;
                break;
        }

        // Final check - if processing still causes decodeURIComponent to fail, fallback to normal behavior.
        try {
            decodeURIComponent(finalUrl);
        } catch (e) {
            finalUrl = decodedUrl;
        }

        return finalUrl;
    }

    function init()
    {
        if ('object' === typeof windowAlias && 'object' === typeof windowAlias.Matomo && 'object' === typeof windowAlias.Matomo.DisableUrlDecoding) {
            // do not initialize twice
            return;
        }

        if ('object' === typeof windowAlias && !windowAlias.Matomo) {
            // matomo is not defined yet
            return;
        }

        // Will be overwritten on SystemSettings update
        Matomo.DisableUrlDecoding = {};

        Matomo.on('TrackerSetup', function (tracker) {
            tracker.DisableUrlDecoding = {
                urls: urlFixup(documentAlias.domain, windowAlias.location.href, getReferrer()),
                processUrls: function (config) {
                    var me = this;

                    tracker.setCustomUrl(processUrl(config, me.urls.href));
                    tracker.setReferrerUrl(processUrl(config, me.urls.referrer));
                },
                doNotDecode: function (config) {
                    var me = this;
                    if (!config) config = {disableFor: "all"};

                    me.processUrls(config);
                },
                doDecode: function () {
                    var me = this;

                    tracker.setCustomUrl(safeDecodeWrapper(me.urls.href));
                    tracker.setReferrerUrl(safeDecodeWrapper(me.urls.referrer));
                }
            }

            tracker.DisableUrlDecoding.processUrls(Matomo.DisableUrlDecoding);
        });
    }

    if ('object' === typeof windowAlias.Matomo) {
        init();
    } else {
        // tracker is loaded separately for sure
        if ('object' !== typeof windowAlias.matomoPluginAsyncInit) {
            windowAlias.matomoPluginAsyncInit = [];
        }

        windowAlias.matomoPluginAsyncInit.push(init);
    }

})();
