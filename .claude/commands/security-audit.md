# Security Audit

You are about to perform a fully automated WordPress security audit on this repository. Follow every step below precisely and in order. Do not skip steps. Do not ask for confirmation between steps — execute autonomously from start to finish.

## Scope

**Always audit the entire repository.** Do not limit the audit to recent commits, the current branch diff, open PRs, or changed files. Every PHP and JS file in the codebase must be analyzed regardless of when it was last modified.

## Instructions

1. **Read `CLAUDE.md` in full** before doing anything else. This is your knowledge base for the entire audit.

2. **Detect product type** — determine if this repo is a plugin or a theme by checking the main PHP file header for `Plugin Name:` or `style.css` for `Theme Name:`.

3. **Read environment requirements** — check `readme.txt`, `README.md`, `composer.json`, and `package.json` for WordPress version, PHP version, and any other requirements.

4. **Generate `.wp-env.json`** — following the instructions in CLAUDE.md Step 1. If one already exists, validate and update it.

5. **Run Semgrep** — execute `bash security-audit.sh semgrep` and wait for it to complete. Read `semgrep-results.json` in full.

6. **Triage findings** — following CLAUDE.md Step 3, go through every Semgrep finding. Confirm or dismiss each one. Then perform the deep code analysis described in CLAUDE.md Step 4 to find issues Semgrep may have missed.

7. **Generate PoCs** — for every confirmed vulnerability, create a PoC file in `security-pocs/` following the templates in CLAUDE.md Step 5.

8. **Run PoCs** — execute `bash security-audit.sh run-pocs` and wait for it to complete. Read `security-poc-results.json` to confirm which vulnerabilities are real and exploitable.

9. **Write the report** — write `SECURITY_REPORT.md` following the structure in CLAUDE.md Step 7. Only include confirmed, exploitable vulnerabilities.

10. **Cleanup** — run `bash security-audit.sh cleanup`.

11. **Summarize** — once complete, give a brief summary in the terminal of how many confirmed vulnerabilities were found and their severity breakdown.