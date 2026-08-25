=== FSM FAQ ===

Contributors: fullspectrummarketing
Requires at least: 5.9
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.1.4
License: GPLv2 or later

Custom FAQ post type with page assignment and [fsm_display_faqs] shortcode. For use with FSM Foundation theme and ACF Pro.

== Description ==

* Registers the `faq` post type (admin only, no front-end archive).
* Requires Advanced Custom Fields Pro. Registers ACF field groups automatically:
  - **FAQs** (on FAQ post type): Answer (WYSIWYG), Display On (Post Object, multiple pages)
  - **Page FAQs** (on Page): Relationship to FAQs, bidirectional with Display On
* Maintains `_has_faqs` post meta on pages for Divi conditional logic (show/hide FAQ section).
* Shortcode: [fsm_display_faqs] – outputs FAQs for the current page with FAQPage schema.
* Admin list column: "Assigned to Pages" with links.

Use a parent section with class `faq-section` and Divi visibility based on custom field `_has_faqs` so the section is hidden when the page has no FAQs.

== Settings (FAQs -> Settings) ==

A no-code settings page (FAQs -> Settings, requires manage_options) controls display without touching the theme:

* **Brand colors** – Toggle background (closed), toggle background (hover), toggle background (open), question text, and toggle icon color. Applied to both the Divi toggle markup and the generic accordion. The question and its answer share one background so each toggle reads as a single connected unit.
* **Toggle icon** – Choose an icon library (ET Modules built-in Divi font, Font Awesome, or bundled SVG) and an icon style (Plus/Minus, Chevron, Caret, Angle, or No icon). Font Awesome is loaded from a CDN only when selected (filterable via `fsm_faq_fontawesome_url`); SVG uses lightweight icons bundled with the plugin.
* **Border & shape** – Border thickness, border color, and corner radius. The border wraps the entire toggle, enclosing the question and its answer together. A thickness of 0 means no plugin border and leaves any existing theme border untouched.
* **Layout & behavior** – Open the first FAQ by default (Divi + generic), allow an open FAQ to be closed by clicking it again (on by default), and spacing between items. Only one FAQ is open at a time either way.

Note: Divi's accordion always keeps one item open. Closing the currently open item is added by this plugin (a scoped port of Foundation's `divi-accordion-close.js`) unless the WCAG kit is already loaded, in which case the plugin skips its copy so both scripts do not fire on the same click. Unchecking the setting restores default Divi behavior on sites without the kit.
* **Structured data** – See below.

Changing settings automatically invalidates the FAQ output cache.

== FAQ ordering ==

* **Global order** – Drag-and-drop the rows on the All FAQs screen to set the default order (stored in menu_order). No extra plugin (e.g. Intuitive/Simple Custom Post Order) is required. Ordering is disabled while searching or filtering the list.
* **Per-page order** – When a page's ACF "Page FAQs" relationship is populated, its editor-defined order takes precedence for that page. Otherwise the global drag-and-drop order is used.

== Structured data / SEO plugin schema ==

The "FAQ schema output" setting controls FAQPage JSON-LD:

* **Output from this plugin (default)** – Inline FAQPage JSON-LD after the FAQ markup, matching exactly what is rendered.
* **Merge into active SEO plugin schema graph** – Injects FAQ entities into Yoast SEO, Rank Math, or All in One SEO instead of emitting inline JSON-LD. Falls back to plugin output if no supported SEO plugin is active.
* **Do not output FAQ schema** – For sites that manage FAQ schema elsewhere.

Output only one FAQ schema per page. If a page also uses an SEO plugin's own FAQ block, set this to "Do not output" (or remove the block) to avoid duplicate-schema warnings.

== Installation ==

1. Install and activate Advanced Custom Fields Pro.
2. Upload the plugin folder to wp-content/plugins/ and activate "FSM FAQ". Field groups are registered automatically.

== Migration from theme-based FAQ ==

To move existing sites from the old fragmented implementation (FAQ code in theme + native ACF groups) to this plugin:

1. Deploy the FSM FAQ plugin and activate it (ACF Pro must be active).
2. Remove FAQ code from the theme: delete faq-post-type.php, remove the FAQ block from core.php (fsm_has_faqs_for_page, fsm_update_faq_status_on_save, admin columns), and remove the [fsm_display_faqs] shortcode from shortcodes.php. Or deploy the updated Foundation theme that already has that code removed.
3. On first load, the plugin runs a one-time migration: it removes any existing "FAQs" and "Page FAQs" field groups from the ACF database (by key) so the plugin's local field groups are the single source of truth. FAQ post content and post meta (faq_answer, display_on_pages) are not touched; only the field group definitions move from DB to plugin code.
4. Clear any object cache (Redis/Memcached) or wait for TTL so [fsm_display_faqs] output is fresh.
5. Verify: edit an FAQ, edit a page, view a page that uses the shortcode.

== GitHub updates (optional) ==

To push this plugin to GitHub and have all sites receive update notifications:

1. Create a GitHub repo (e.g. YourOrg/fsm-faq) and push this plugin code.
2. Add the Plugin Update Checker library:
   - Download the latest release from https://github.com/YahnisElsts/plugin-update-checker/releases
   - Extract it and copy the "plugin-update-checker" folder into wp-content/plugins/fsm-faq/vendor/
   - You should have: fsm-faq/vendor/plugin-update-checker/plugin-update-checker.php (and Puc/, etc.)
   - Commit vendor/ to your repo so release zips include the updater.
3. On each site (or in a shared wp-config), add:
   define( 'FSM_FAQ_GITHUB_REPO', 'https://github.com/YourOrg/fsm-faq/' );
4. For a private repo, also add a GitHub personal access token (repo scope):
   define( 'FSM_FAQ_GITHUB_TOKEN', 'ghp_...' );
5. To release an update: bump the Version header in fsm-faq.php and the "Stable tag" in readme.txt, then either:
   - Create a new GitHub Release (tag e.g. v1.0.1), or
   - Push a new tag (e.g. v1.0.1), or
   - Push to the branch set in FSM_FAQ_GITHUB_REPO (default: main).
   Sites will show "Update available" and can update with one click.

== Changelog ==

= 1.1.4 =
* Security: Harden FAQ capabilities and content escaping.
* Chore: Version bump so branch-based staging updates pick up the latest commits.

= 1.1.3 =
* Fix: Divi close-on-click now matches Foundation's WCAG kit script (direct bind + slideToggle) and skips loading when that kit is already present, so the two handlers cannot reverse each other.

= 1.1.2 =
* New: Settings page (FAQs -> Settings) for toggle background colors (closed/hover/open), question text, toggle icon library/style, border thickness/color, first-open behavior, corner radius, and item spacing.
* New: Toggle icon libraries – ET Modules (Divi font), Font Awesome, and bundled SVG – applied to both Divi and generic accordion markup.
* New: Open FAQs can be closed by clicking them again (setting, enabled by default), including on the Divi toggle markup where Divi normally prevents it.
* New: Native drag-and-drop ordering on the All FAQs screen (menu_order); ACF "Page FAQs" relationship order takes precedence per page.
* New: FAQ schema output modes – inline JSON-LD (default), merge into Yoast/Rank Math/All in One SEO graph, or off.
* Improvement: FAQ output cache now busts on content, order, and settings changes.

= 1.0.5 =
* Normalize typographic apostrophes in FAQ question titles (same as answers) so titles display correctly with Divi and other processors.

= 1.0.3 =
* Release 1.0.3.

= 1.0.0 =
* Initial release. CPT, ACF field groups (FAQs + Page FAQs), admin columns, save_post (_has_faqs + cache invalidation), [fsm_display_faqs] shortcode.
