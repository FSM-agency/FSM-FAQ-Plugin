=== FSM FAQ ===

Contributors: fullspectrummarketing
Requires at least: 5.9
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.0.0
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

= 1.0.0 =
* Initial release. CPT, ACF field groups (FAQs + Page FAQs), admin columns, save_post (_has_faqs + cache invalidation), [fsm_display_faqs] shortcode.
