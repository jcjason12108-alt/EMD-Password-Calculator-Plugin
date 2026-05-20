=== EMD Password Calculator ===
Contributors: jasoncox
Requires at least: 5.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.2.5
License: GPLv2 or later

Displays today’s and yesterday’s EMD password based on lodge rules (UTC).

== Description ==
Use the shortcode `[emd_password_calc]` to render the calculator (UTC). It computes:
- Digit sum of MM/DD/YY
- Last digit of the sum
- Last digit of the year
- Day reversed

Password format: `<last digit of digit sum> <last digit of year> <day reversed>`.

== Changelog ==
= 2.2.5 =
* Test release for GitHub update checks.

= 2.2.4 =
* Added GitHub automatic updates using Plugin Update Checker.
* Updated WordPress compatibility to 6.9.4.

= 2.2.3 =
* Use server UTC time for date/password calculation.

= 2.2.2 =
* Refactor to proper WP structure (enqueued CSS/JS).
* Accessibility: buttons with aria-expanded + hidden panels.
* Internationalization support.
* Restored classic light color palette (no dark background).
* Center-aligned layout, removed tip, shortcode shown on Plugins page.
* Added Settings page with shortcode + live preview and Copy button.
* Removed 'using the lodge convention' from plugin description.
* New calculation display modeled after example; password shows without spaces.
* Calc line now highlights 3rd, 4th, and 6th digits in red; sum shows ones digit in red.
* Replaced the word 'Example' with 'Calculation' in output.

= 1.0.0 =
* Initial single-file release.
