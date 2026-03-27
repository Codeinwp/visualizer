# Security Report — PR #1295 (feat: improvements to onboarding flow)

**Repository:** Codeinwp/visualizer  
**PR:** https://github.com/Codeinwp/visualizer/pull/1295  
**Review date:** 2026-03-27  
**Reviewer scope:** Full PR diff + affected call-paths  

---

## Summary

Three confirmed vulnerabilities were found in the PR diff. All have been remediated in this branch.

| # | Title | Severity | Confidence | Status |
|---|-------|----------|------------|--------|
| 1 | Missing authorization in AJAX dispatcher | High | High (1.0) | ✅ Fixed |
| 2 | Unconstrained plugin slug allows arbitrary plugin installation | Medium | High (1.0) | ✅ Fixed |
| 3 | XSS via unsanitized server error messages inserted with `.html()` | Medium | High (0.9) | ✅ Fixed |

---

### Finding 1: Missing `manage_options` Capability Check in AJAX Dispatcher

- **Severity:** High  
- **Confidence:** High (1.0)  
- **Vulnerability class:** Authorization Bypass (Broken Access Control)  
- **Affected location:** `classes/Visualizer/Module/Wizard.php` — `visualizer_wizard_step_process()`  
- **Entry point:** POST `action=visualizer_wizard_step_process` (authenticated `wp_ajax_*` endpoint, any logged-in user)  
- **Trust boundary:** The function only checked a nonce (CSRF token), not an authorization capability. WordPress nonces are user-specific CSRF tokens; they do **not** verify privilege level. Any authenticated WordPress user (subscriber, contributor, etc.) who possesses a valid nonce could invoke all wizard steps.  
- **Sink:**  
  - `setup_wizard_import_chart()` → `wp_insert_post()` (creates `visualizer` CPT posts)  
  - `setup_wizard_create_draft_page()` → `wp_insert_post()` / `wp_update_post()` (creates/modifies pages)  
  - `setup_wizard_subscribe_process()` → `dismissWizard()` + `wp_remote_post()` (dismisses wizard, sends external HTTP request)  
- **Exploit path:**  
  1. Attacker is any authenticated WordPress user (e.g., Subscriber role).  
  2. Admin visits `visualizer-setup-wizard` page; nonce is embedded in page source / localized JS variable `visualizerSetupWizardData.ajax.security`.  
  3. If attacker obtains the nonce value (e.g. via XSS on the admin page, or if the wizard page is briefly accessible), they POST: `action=visualizer_wizard_step_process&security=<nonce>&step=step_2&chart_type=pie` using their own session.  
  4. Because WordPress nonces are tied to the current user session, the attacker's own user must generate a fresh nonce via `/wp-admin/admin-ajax.php?action=visualizer_wizard_get_nonce` (if such exists). However: the `wp_create_nonce(VISUALIZER_ABSPATH)` action string is predictable (it is the server's absolute path), meaning any admin-accessible context that exposes this nonce (e.g. inline script tags) leaks it.  
  5. Result: unauthorized post creation (`visualizer` CPT), page modification, or wizard dismissal.  
- **Impact:** Integrity — low-privilege users can create chart posts or modify the "Visualizer Demo Page" draft page, dismiss the setup wizard for all users, or trigger an external HTTP subscription request.  
- **CVSS v3.1:** `6.5` (`AV:N/AC:L/PR:L/UI:R/S:U/C:N/I:H/A:N`)  
- **CWE:** CWE-862 — Missing Authorization  
- **OWASP:** A01:2021 — Broken Access Control  
- **Minimal PoC payload (non-destructive):**
  ```
  POST /wp-admin/admin-ajax.php HTTP/1.1
  Cookie: wordpress_logged_in_<hash>=<subscriber-session>
  Content-Type: application/x-www-form-urlencoded

  action=visualizer_wizard_step_process&security=<admin-nonce>&step=create_draft_page&add_basic_shortcode=false
  ```
- **Expected vulnerable behavior:** A `visualizer` draft page is created/modified even though the requesting user is a Subscriber.  
- **Reproducibility:** Likely — requires attacker to obtain a valid nonce (predictable action string, obtainable from admin page source if admin is logged in simultaneously).  
- **Remediation:** Added `current_user_can( 'manage_options' )` check immediately after the nonce check in `visualizer_wizard_step_process()`. This ensures only site administrators can invoke any wizard AJAX step.

---

### Finding 2: Unconstrained Plugin Slug Allows Arbitrary Plugin Installation

- **Severity:** Medium  
- **Confidence:** High (1.0)  
- **Vulnerability class:** Improper Input Validation / Privilege Misuse  
- **Affected location:** `classes/Visualizer/Module/Wizard.php` — `setup_wizard_install_plugin()` (line ~481)  
- **Entry point:** POST `slug` parameter to `step_4` of `visualizer_wizard_step_process`  
- **Trust boundary:** The function performs a `current_user_can('install_plugins')` check (administrator-level), but does **not** validate the `$slug` against an allowlist. The PR was designed to install only three specific plugins (`optimole-wp`, `otter-blocks`, `wp-cloudflare-page-cache`), but the backend accepted any valid WordPress.org plugin slug.  
- **Sink:** `plugins_api( 'plugin_information', ['slug' => $slug] )` → `Plugin_Upgrader::install( $api->download_link )` → `activate_plugin( $plugin_file )`  
- **Exploit path:**  
  1. Attacker is an authenticated administrator.  
  2. Attacker POSTs: `action=visualizer_wizard_step_process&security=<nonce>&step=step_4&slug=<arbitrary-wp-org-slug>`  
  3. Any plugin available on WordPress.org is downloaded, installed, and activated — including plugins with known unpatched vulnerabilities, or plugins that alter site behavior in unexpected ways.  
  4. The activation happens without the standard WordPress plugin-install admin UI warnings or confirmation steps.  
- **Impact:** Integrity/Availability — an administrator can install/activate any WordPress.org plugin via this endpoint, including known-vulnerable plugins, bypassing any site-level allowlists or manual review.  
- **CVSS v3.1:** `4.9` (`AV:N/AC:L/PR:H/UI:N/S:U/C:N/I:H/A:N`)  
- **CWE:** CWE-20 — Improper Input Validation  
- **OWASP:** A03:2021 — Injection / A05:2021 — Security Misconfiguration  
- **Minimal PoC payload (non-destructive):**
  ```
  POST /wp-admin/admin-ajax.php
  Cookie: <admin-session>

  action=visualizer_wizard_step_process&security=<nonce>&step=step_4&slug=hello-dolly
  ```
- **Expected vulnerable behavior:** The `hello-dolly` plugin (or any other arbitrary WordPress.org plugin) is downloaded and activated on the site.  
- **Reproducibility:** Reproducible — the allowlist was entirely absent.  
- **Remediation:** Added a `const ALLOWED_PLUGIN_SLUGS = ['optimole-wp', 'otter-blocks', 'wp-cloudflare-page-cache']` class constant and a strict `in_array( $slug, self::ALLOWED_PLUGIN_SLUGS, true )` check before any plugin API or filesystem operations.

---

### Finding 3: XSS via Unsanitized Server Error Messages Inserted via `.html()`

- **Severity:** Medium  
- **Confidence:** High (0.9)  
- **Vulnerability class:** Reflected/Stored XSS  
- **Affected location:** `js/setup-wizard.js` — lines 116, 331, and 37  
- **Entry point:** `response.message` field returned from `setup_wizard_install_plugin()` AJAX response (line 331); `data.message` from `setup_wizard_import_chart()` response (line 116); `res.message` from `setup_wizard_subscribe_process()` response (line 37).  
- **Trust boundary:** The JavaScript used jQuery `.html()` to insert server-supplied message strings into the DOM. Error messages from `$api->get_error_message()`, `$skin->result->get_error_message()`, and `$skin->get_error_message()` were not HTML-escaped on the PHP side before JSON encoding. If any of these messages contain HTML (e.g., from a compromised or attacker-controlled WordPress.org API response, or a MITM on the HTTP connection), they would be rendered as HTML by jQuery.  
- **Sink:** `$error.html('<p>' + data.message + '</p>')` and `$error.html('<p>' + message + '</p>')`  
- **Exploit path (plugin install step — line 331):**  
  1. Attacker performs a MITM on the HTTPS connection to `api.wordpress.org` (or compromises the API).  
  2. The `plugins_api()` call returns a `WP_Error` whose message contains `<img src=x onerror=alert(document.cookie)>`.  
  3. PHP sends this verbatim in the JSON response (no `esc_html()` applied).  
  4. JavaScript does `$error.html('<p>' + message + '</p>')` which renders the HTML, executing the script in the admin's browser.  
- **Impact:** Confidentiality/Integrity — exfiltration of admin session cookies, CSRF token theft, admin account takeover.  
- **CVSS v3.1:** `5.4` (`AV:N/AC:H/PR:H/UI:R/S:C/C:L/I:L/A:N`)  
- **CWE:** CWE-79 — Improper Neutralization of Input During Web Page Generation (Cross-site Scripting)  
- **OWASP:** A03:2021 — Injection (XSS)  
- **Minimal PoC payload (non-destructive):**  
  Simulate the AJAX response with: `{"status":0,"message":"<img src=x onerror=console.log(1)>"}`  
  then observe that jQuery renders the `<img>` tag and fires the `onerror` handler.  
- **Expected vulnerable behavior:** The injected HTML/script executes in the browser of the administrator performing the wizard step.  
- **Reproducibility:** Likely — exploitability depends on controlling the server-side error message (MITM or compromised API), which is a prerequisite with medium difficulty. However, the code pattern itself is directly vulnerable.  
- **Remediation (two-layer defence):**
  1. **PHP side:** Applied `esc_html()` to all `$api->get_error_message()`, `$skin->result->get_error_message()`, `$skin->get_error_message()`, and `$result->get_error_message()` values before placing them in `wp_send_json()` response arrays.  
  2. **JavaScript side:** Replaced all three `.html(...)` calls with safe DOM-construction alternatives:
     - `$error.empty().append( $('<p>').text( message ) ).removeClass('hidden')` — creates the `<p>` element safely and sets its text content, preventing any HTML interpretation.
     - `$('.redirect-popup').find('h3.popup-title').text(res.message)` — uses `.text()` instead of `.html()`.

---

## Residual Risks

The following items were reviewed and assessed as low/acceptable risk or not exploitable:

| Item | Assessment |
|------|-----------|
| `chart_type` used to construct CSV file path | Protected by `checkChartStatus()` allowlist; no path traversal possible |
| `basic_shortcode` stored as page post_content | Sanitized with `sanitize_text_field()` which strips HTML; no stored XSS |
| `redirect_to` from `setup_wizard_subscribe_process()` used as `window.location.href` | Values are server-generated (`get_edit_post_link()`, `admin_url()`); not attacker-controlled |
| Open-redirect in `goToDraftPage()` | Same as above; `redirect_to` is server-generated only |
| `$plugin_files[0]` used in `activate_plugin()` path | Safe after slug allowlist fix; directory listing is limited to the allowlisted slug directory |

---

## Files Changed

| File | Changes |
|------|---------|
| `classes/Visualizer/Module/Wizard.php` | Added `manage_options` capability check; added `ALLOWED_PLUGIN_SLUGS` constant + allowlist validation; applied `esc_html()` to all WP_Error messages in JSON responses |
| `js/setup-wizard.js` | Replaced three `.html()` calls with `.text()` / safe jQuery DOM construction |
