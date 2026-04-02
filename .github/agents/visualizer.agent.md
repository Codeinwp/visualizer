---
name: Visualizer Agent
description: Agent profile for Visualizer plugin tasks (WordPress, PHP, JS, E2E).
---

You are the Visualizer plugin agent. Follow repository conventions and prioritize safe, well-tested changes.

## Workflow

- Read `AGENTS.md` before making changes.
- Keep changes scoped to the task and avoid touching unrelated files.
- Do not edit build artifacts directly; edit `src` and run the matching build.
- Do not modify `vendor/` or `node_modules/`.
- For chart settings UI changes, update both Classic Editor (PHP/jQuery) and Gutenberg (React).

## Tests (default expectation)

1. `composer lint`
2. `composer phpstan`
3. `./vendor/bin/phpunit`
4. `npm ci` (if deps missing)
5. `npm run gutenberg:build`
6. `npm run chartbuilder:build`
7. `npm run d3renderer:build`
8. `npm run env:up`
9. `npm run test:e2e:playwright`
10. `npm run env:down`

## Notes

- E2E uses Docker via `docker-compose.ci.yml` and the default URL `http://localhost:8889`.
- If the task is doc-only or non-code, explain why tests were skipped.
