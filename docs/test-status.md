# Version 1.0.5 validation

Tested on cbsepath.com on 30 September 2026 with WordPress 7.1.1 and StudyPath
Lite 0.1.10. Replaced the installed 1.0.4 plugin with the verified 1.0.5 ZIP.

## Preservation check

Public REST-rendered content was compared before/after the replacement across
all 96 published gallery pages, containing 607 images. Image sources, alt text,
width/height, srcset, sizes and caption counts matched for every page. Published
content counts stayed at 112 posts and 348 pages. No existing content or media
was edited or deleted. FileBird remains responsible for media organization.

## Browser checks

- Class 8 Maths exercise 13.2: 4 images; Individual and Scroll modes displayed
  uncropped images. Page Down and End moved the focused scroll area; the final
  image loaded. Focus had a visible blue outline.
- Class 10 Maths exercise 5.3: 24 images; tested desktop and mobile scroll modes.
- Class 8 Maths exercise 5.4: 16 images; desktop scroll mode verified.
- Global override disabled: ordinary core Gallery wrapper returned unchanged.
- Homepage: no galleries, no plugin stylesheet and no plugin frontend script.
- Existing post editor: controls available in Gallery layout; no recovery warning.
  Mobile editor preview kept one uncropped column. The post was not saved/edited.

Live frontend responsive checks used WordPress Customizer device previews:

| Viewport | Gallery width | Inner scroll width | Scroll area height | Result |
| --- | ---: | ---: | ---: | --- |
| Mobile, 320 × 480 | 261 px | 261 px | 358 px | No horizontal overflow; proportions preserved |
| Tablet, 720 × 936 | 653 px | 653 px | 700 px | No horizontal overflow; proportions preserved |
| Desktop, 1063 × 936 | 688 px | 688 px | 700 px | No horizontal overflow; proportions preserved |
| Desktop, 1363 × 936 | 748 px | 748 px | 700 px | No horizontal overflow; keyboard scrolling passed |

The 24-image gallery also passed at 320 × 480. End reached scrollTop 8079 px,
matching the maximum, and the last image loaded. These checks use Chromium
viewport previews, not physical devices or Safari/Firefox touch testing.

The existing global setting (override enabled, Individual mode) was restored
following temporary Scroll and standard-layout tests. WP Rocket cache was cleared.

## Local checks

Production editor build succeeded. Frontend CSS: 1903 bytes; editor JS: 3538
bytes (uncompressed; no frontend JS). PHP syntax checks passed. 25 regression
assertions passed using the real WordPress 6.5 HTML Tag Processor. Tests isolate
options/hooks without a database; live installation supplies integration coverage.
The ZIP packager verified every required runtime file, including admin settings.
