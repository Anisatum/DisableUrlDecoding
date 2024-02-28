<script type="text/javascript">
    module('DisableUrlDecoding');

    test("DisableUrlDecoding", function() {
        expect(12);

        function getDisableUrlDecodingToken() {
            return "<?php $token = md5(uniqid(mt_rand(), true)); echo $token; ?>";
        }

        var tracker = Piwik.getTracker();
        tracker.disableBrowserFeatureDetection(); // avoid client hint queue
        tracker.setTrackerUrl("matomo.php");
        tracker.setSiteId(1);
        tracker.setCustomData({ "token" : getDisableUrlDecodingToken() });
        tracker.alwaysUseSendBeacon();

        tracker.DisableUrlDecoding.urls = {
            hostName: "example.com",
            href: "https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=1234",
            referrer: "https://referrer.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=%E4%F6%FC",
        }
        tracker.DisableUrlDecoding.doNotDecode();
        tracker.trackPageView('DND All: Bad Referrer');

        tracker.DisableUrlDecoding.urls = {
            hostName: "example.com",
            href: "https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=%E4%F6%FC",
            referrer: "https://referrer.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=1234",
        }
        tracker.DisableUrlDecoding.doNotDecode();
        tracker.trackPageView('DND All: Bad Location');

        tracker.DisableUrlDecoding.urls = {
            hostName: "example.com",
            href: "https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=%E4%F6%FC",
            referrer: "https://referrer.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=%E4%F6%FC",
        }
        tracker.DisableUrlDecoding.doNotDecode({disableFor: "params", params: ['q']});
        tracker.trackPageView('DND Param');

        tracker.DisableUrlDecoding.urls = {
            hostName: "example.com",
            href: "https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=%E4%F6%FC",
            referrer: "https://referrer.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=%E4%F6%FC",
        }
        tracker.DisableUrlDecoding.doNotDecode({disableFor: "regexp", regexp: "example\\.com\\/(.*)\\/\\?(q=.*)&"});
        tracker.trackPageView('DND Location Regexp');

        tracker.DisableUrlDecoding.urls = {
            hostName: "example.com",
            href: "https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=%E4%F6%FC",
            referrer: "https://referrer.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=%E4%F6%FC",
        }
        tracker.DisableUrlDecoding.doNotDecode({disableFor: "regexp", regexp: "referrer.com\\/(.*)\\/\\?(q=.*)&"});
        tracker.trackPageView('DND Referrer Regexp');

        tracker.DisableUrlDecoding.urls = {
            hostName: "example.com",
            href: "https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=%E4%F6%FC",
            referrer: "https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=1234",
        }
        tracker.DisableUrlDecoding.doNotDecode({disableFor: "regexp", regexp: "example.com\\/.*\\/\\?q=.*&"});
        tracker.trackPageView('DND Regexp No Groups');

        stop();
        setTimeout(function() {
            // wait till client hints were resolved
            var results = fetchTrackedRequests(getDisableUrlDecodingToken(), true);
            ok(results[0].includes(encodeURIComponent("https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=1234")),
	            "DND All: Bad Referrer - Request should include a URL-Encoded location");
            ok(results[0].includes(encodeURIComponent("https://referrer.com/Ampersand & Whitespace/?q=Heatmap+&+session+recording&post_type=äöü")),
                "DND All: Bad Referrer - Referrer contains invalid characters (escape('äöü')), request should include a URL-Encoded location after safeDecodeWrapper() was applied");

            ok(results[1].includes(encodeURIComponent("https://example.com/Ampersand & Whitespace/?q=Heatmap+&+session+recording&post_type=äöü")),
	            "DND All: Bad Location - Location contains invalid characters (escape('äöü')), request should include a URL-Encoded location after safeDecodeWrapper() was applied");
            ok(results[1].includes(encodeURIComponent("https://referrer.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=1234")),
                "DND All: Bad Location - Request should include a URL-Encoded referrer");

            ok(results[2].includes(encodeURIComponent("https://referrer.com/Ampersand & Whitespace/?q=Heatmap+%26+session+recording&post_type=äöü")),
                "DND Param: Location contains invalid characters (escape('äöü')), request should include a URL-Encoded value of the 'q' parameter after safeDecodeWrapper() was applied on the rest of the URL");
            ok(results[2].includes(encodeURIComponent("https://referrer.com/Ampersand & Whitespace/?q=Heatmap+%26+session+recording&post_type=äöü")),
                "DND Param: Referrer contains invalid characters (escape('äöü')), request should include a URL-Encoded value of the 'q' parameter after safeDecodeWrapper() was applied on the rest of the URL");

            ok(results[3].includes(encodeURIComponent("https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=äöü")),
                "DND Location Regexp: Location contains invalid characters (escape('äöü')) which should be hadnled by safeDecodeWrapper(), everything else was captured by the regexp, and was URL-encoded instead");
            ok(results[3].includes(encodeURIComponent("https://referrer.com/Ampersand & Whitespace/?q=Heatmap+&+session+recording&post_type=äöü")),
                "DND Location Regexp: Referrer contains invalid characters (escape('äöü')), and was not matched by the regexp. Request should include a URL-Encoded location after safeDecodeWrapper() was applied");

            ok(results[4].includes(encodeURIComponent("https://example.com/Ampersand & Whitespace/?q=Heatmap+&+session+recording&post_type=äöü")),
                "DND Referrer Regexp: Location contains invalid characters (escape('äöü')), and was not matched by the regexp. Request should include a URL-Encoded location after safeDecodeWrapper() was applied");
            ok(results[4].includes(encodeURIComponent("https://referrer.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=äöü")),
                "DND Referrer Regexp: Referrer contains invalid characters (escape('äöü')) which should be hadnled by safeDecodeWrapper(), everything else was captured by the regexp, and was URL-encoded instead");

            ok(results[5].includes(encodeURIComponent("https://example.com/Ampersand%20%26%20Whitespace/?q=Heatmap+%26+session+recording&post_type=1234")),
                "DND Regexp No Groups: Location contains no invalid characters, and was matched by the regexp with no groups. Request should include a URL-Encoded location with no safeDecodeWrapper()");
            ok(results[5].includes(encodeURIComponent("https://example.com/Ampersand & Whitespace/?q=Heatmap+&+session+recording&post_type=äöü")),
                "DND Regexp No Groups: Referrer was matched by the regexp with no groups, but contains invalid characters (escape('äöü')). Request should include a URL-Encoded referrer, after safeDecodeWrapper() was applied");

            start();
        }, 1500);
    });

</script>