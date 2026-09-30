# Lighthouse regression check — 30 September 2026

Live site: cbsepath.com; StudyPath Lite 0.1.10; gallery plugin 1.0.5; WordPress 7.1.1.
Lighthouse CLI 12.8.1 / Headless Chrome 154.0.8037.92. Clean logged-out browser
profiles, standard simulated mobile/desktop Lighthouse settings, sequential runs.
Mobile: simulated slow 4G and 4× CPU slowdown. Cached page responses were verified
for the intended mode; WP Rocket cache was refreshed following state changes.

## Conclusion

No meaningful score/FCP/LCP/CLS regression was found attributable to the plugin.
Median gallery scores remain 90 mobile / 87 desktop with the plugin disabled or
active. CLS is zero in every valid audit. Mobile TBT has run-to-run variability;
the active Individual median differs from the disabled median by only a few ms.
The plugin added approximately 500 transferred HTML bytes, zero extra stylesheet
requests (CSS was inlined by WordPress), and zero extra script requests.

## Median results on the four-image gallery

FCP, LCP, Speed Index (SI) and response time are seconds. TBT is milliseconds.

| State | Device | Runs | Score | FCP | LCP | CLS | TBT | SI | Response |
| --- | --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| disabled | mobile | 3 | 90 | 1.42 | 1.43 | 0.000 | 15.0 | 13.21 | 1.27 |
| disabled | desktop | 3 | 87 | 0.90 | 1.15 | 0.000 | 0.0 | 6.18 | 1.04 |
| individual | mobile | 6 | 90 | 1.41 | 1.42 | 0.000 | 17.2 | 15.25 | 1.29 |
| individual | desktop | 4 | 87 | 0.88 | 1.14 | 0.000 | 0.0 | 6.26 | 1.27 |
| scroll | mobile | 1 | 90 | 1.30 | 1.33 | 0.000 | 60.5 | 15.50 | 1.26 |
| scroll | desktop | 1 | 87 | 0.85 | 1.09 | 0.000 | 0.0 | 6.31 | 1.27 |

Individual combines the first batch with a follow-up after the control runs.
Scroll was a one-run check per profile. The 24-image lesson scored 89 mobile
both active and disabled. The homepage scored 85 mobile with the plugin active;
its markup had no plugin assets. A total of 21 valid audits were collected.

## Speed Index and environment limitations

The active mobile Speed Index was slower than the control (roughly 15.25 vs
13.21 s), despite virtually identical FCP/LCP and scores. DevTools request timing
shows a substantial connection-establishment delay before the HTTP request is
sent. In representative mobile traces, active connectStart→connectEnd took 8.76 s,
control 7.50 s, and the follow-up active run 8.20 s. Actual request-send→headers
response times remained near 1.3 s in both states. This connection delay occurs
before WordPress can execute plugin code; it limits absolute Speed Index and
wall-clock conclusions from this proxy-routed runner. Do not treat the larger SI
number as a proven plugin regression or the stable scores as a guarantee for all
visitor networks. An external PageSpeed/field check remains useful.

The public PageSpeed API returned quota exhaustion. The local runner used the
execution environment's configured proxy and trusted CA, retaining TLS verification.
Failed setup attempts were excluded; there were no runtime errors in the 21
included audits. This is lab data, not a physical-device or field CWV study.

## Remaining site findings

Both active and disabled reports flag document response time and image delivery.
JPEG compression/modern formats, responsive image sizes and cache headers have
room for improvement. These findings also exist without this gallery plugin;
no unrelated theme/server configuration was changed during this test.

## Final site state

Plugin active, global override enabled, original Individual setting restored,
WP Rocket cache cleared. No posts, pages, attachments or FileBird folders edited
or deleted. This task required no additional plugin code changes.

## Files

Open a *.report.html file for the original Lighthouse report. Matching JSON files
contain the raw audit data; comparison.json contains extracted metrics and request
timing. All report URLs and screenshots are from logged-out public pages.
