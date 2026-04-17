# GitHub-based updates for FSM FAQ

This plugin can use [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) (PUC) so that **GitHub is the source of truth**: when you push a new release, all sites using the plugin see "Update available" in the WordPress Plugins screen and can update with one click.

## One-time setup

### 1. Put the plugin on GitHub

- Create a repository (e.g. `FullSpectrumMarketing/fsm-faq` or `YourOrg/fsm-faq`).
- Push this plugin code to the default branch (e.g. `main`).

### 2. Add the Plugin Update Checker library

- Download the latest release: https://github.com/YahnisElsts/plugin-update-checker/releases  
- Extract the zip. Inside you’ll see a folder **`plugin-update-checker`** (with `plugin-update-checker.php`, `Puc/`, etc.).
- Copy that folder into this plugin as **`vendor/plugin-update-checker`**:
  - Result: `fsm-faq/vendor/plugin-update-checker/plugin-update-checker.php` (and the rest of PUC).
- Commit the `vendor/` folder so your repo and release zips include the updater.

### 3. Tell the plugin where to check for updates

**Public repo (recommended):** keep the repository **public** on GitHub. The plugin already points at the default FSM URL (`https://github.com/FSM-agency/FSM-FAQ-Plugin/`). In that case you do **not** need any `wp-config.php` defines on client sites—no API keys on each install.

If the canonical repo URL is different, set it once per site (or in a shared `wp-config.php`):

```php
define( 'FSM_FAQ_GITHUB_REPO', 'https://github.com/YourOrg/fsm-faq/' );
```

Trailing slash is fine.

**Private repo (optional):** if you must keep the repo private, create a [GitHub Personal Access Token](https://github.com/settings/tokens) with `repo` scope (or a fine-grained token with read access to that repo) and add **on each site** that should receive updates:

```php
define( 'FSM_FAQ_GITHUB_TOKEN', 'ghp_xxxxxxxxxxxx' );
```

Prefer a public repo so you avoid rotating tokens across many hosts.

### 4. Optional: change update branch

By default the plugin checks the **`main`** branch (or GitHub Releases/tags). To use another branch:

```php
add_filter( 'fsm_faq_update_branch', function() { return 'stable'; } );
```

## Releasing an update

1. Bump the **Version** in `fsm-faq.php` (e.g. `1.0.1`) and the **Stable tag** in `readme.txt`.
2. Commit and push.
3. Do one of the following:
   - **GitHub Release:** Repo → Releases → Create a new release, choose or create a tag (e.g. `v1.0.1`).
   - **Tag only:** `git tag v1.0.1 && git push origin v1.0.1`
   - **Branch:** If you use a stable branch, PUC will use the Version header from that branch; just push to it.

Sites will see the update within about 12 hours, or immediately if someone clicks "Check for updates" on the Plugins screen.

## Flow summary

- **Source of truth:** Your GitHub repo.
- **Sites:** Install the plugin once (from a release zip or clone). For the default **public** repo, no defines are required. Custom or private repos need `FSM_FAQ_GITHUB_REPO` and, if private, `FSM_FAQ_GITHUB_TOKEN` per site.
- **Updates:** You release on GitHub → PUC on each site detects the new version → WordPress shows "Update available" → one-click update.

No WordPress.org listing or custom server required.
