#!/bin/bash

# =============================================================================
# WordPress Security Audit Helper Script
# Used by Claude Code's /security-audit slash command
# =============================================================================

set -e

PLUGIN_DIR="$(pwd)"
POCS_DIR="$PLUGIN_DIR/security-pocs"
WP_ENV_URL="http://localhost:8888"
WP_CLI="npx @wordpress/env run tests-cli wp"
RESULTS_FILE="$PLUGIN_DIR/security-poc-results.json"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log()    { echo -e "${BLUE}[INFO]${NC} $1"; }
success(){ echo -e "${GREEN}[OK]${NC} $1"; }
warn()   { echo -e "${YELLOW}[WARN]${NC} $1"; }
error()  { echo -e "${RED}[ERROR]${NC} $1"; }

# =============================================================================
# COMMAND: semgrep
# Runs Semgrep with WordPress security rulesets
# =============================================================================
run_semgrep() {
  log "Checking Semgrep installation..."

  if ! command -v semgrep &> /dev/null; then
    log "Semgrep not found. Installing via pip..."
    pip install semgrep --quiet || {
      error "Failed to install Semgrep. Please install it manually: pip install semgrep"
      exit 1
    }
  fi

  success "Semgrep is available."
  log "Running Semgrep with WordPress security rulesets..."

  # Run with WordPress-specific and generic PHP security rules
  semgrep scan \
    --config "p/wordpress" \
    --config "p/php" \
    --config "p/owasp-top-ten" \
    --json \
    --output semgrep-results.json \
    --quiet \
    "$PLUGIN_DIR" || true  # Don't exit on findings

  # Count findings
  FINDING_COUNT=$(python3 -c "
import json, sys
try:
  data = json.load(open('semgrep-results.json'))
  print(len(data.get('results', [])))
except:
  print(0)
" 2>/dev/null || echo "0")

  success "Semgrep complete. $FINDING_COUNT findings written to semgrep-results.json"
}

# =============================================================================
# COMMAND: run-pocs
# Starts wp-env, creates test users, runs all PoCs, captures results
# =============================================================================
run_pocs() {
  log "Checking wp-env and Docker..."

  # Check Docker
  if ! docker info &> /dev/null; then
    error "Docker is not running. Please start Docker and try again."
    exit 1
  fi

  # Check @wordpress/env
  if ! npx --yes @wordpress/env --version &> /dev/null; then
    error "Could not run @wordpress/env. Please ensure Node.js and npm are installed."
    exit 1
  fi

  # Check .wp-env.json exists
  if [ ! -f ".wp-env.json" ]; then
    error ".wp-env.json not found. Claude Code should have generated this in Step 4."
    exit 1
  fi

  # Check PoCs directory
  if [ ! -d "$POCS_DIR" ] || [ -z "$(ls -A $POCS_DIR 2>/dev/null)" ]; then
    warn "No PoC files found in security-pocs/. Nothing to run."
    echo '{"results": [], "message": "No PoCs were generated."}' > "$RESULTS_FILE"
    exit 0
  fi

  # Start wp-env
  log "Starting wp-env (this may take a few minutes on first run)..."
  npx @wordpress/env start 2>&1 | tail -5
  success "wp-env started."

  # Wait for WordPress to be ready
  log "Waiting for WordPress to be ready..."
  for i in {1..30}; do
    if curl -s "$WP_ENV_URL" | grep -q "WordPress" 2>/dev/null; then
      break
    fi
    sleep 2
  done

  # Create test users
  log "Creating test users..."

  $WP_CLI user create subscriber subscriber@test.local \
    --role=subscriber --user_pass=Subscriber123! 2>/dev/null || \
    $WP_CLI user update subscriber --user_pass=Subscriber123! 2>/dev/null || true

  $WP_CLI user create editor editor@test.local \
    --role=editor --user_pass=Editor123! 2>/dev/null || \
    $WP_CLI user update editor --user_pass=Editor123! 2>/dev/null || true

  $WP_CLI user update admin --user_pass=Admin123! 2>/dev/null || true

  success "Test users ready: admin/Admin123!, editor/Editor123!, subscriber/Subscriber123!"

  # Initialize results
  echo '{"results": []}' > "$RESULTS_FILE"

  # Run each PoC
  POC_COUNT=0
  SUCCESS_COUNT=0
  FAIL_COUNT=0

  for poc_file in "$POCS_DIR"/*.sh "$POCS_DIR"/*.php "$POCS_DIR"/*.html; do
    [ -f "$poc_file" ] || continue

    POC_NAME=$(basename "$poc_file")
    POC_COUNT=$((POC_COUNT + 1))

    log "Running PoC: $POC_NAME"

    # Execute PoC with timeout and capture output
    POC_OUTPUT=""
    POC_EXIT=0

    if [[ "$poc_file" == *.sh ]]; then
      chmod +x "$poc_file"
      POC_OUTPUT=$(timeout 30 bash "$poc_file" 2>&1) || POC_EXIT=$?
    elif [[ "$poc_file" == *.php ]]; then
      POC_OUTPUT=$(timeout 30 php "$poc_file" 2>&1) || POC_EXIT=$?
    elif [[ "$poc_file" == *.html ]]; then
      POC_OUTPUT="HTML-based PoC generated. Manual testing required — open the file in a browser while authenticated as the required role."
      POC_EXIT=0
    fi

    # Determine result
    CONFIRMED=false
    if echo "$POC_OUTPUT" | grep -qiE "alert\(1\)|UNION SELECT|ERROR|leaked|password|wp_users|exploited|success|vulnerable|bypassed"; then
      CONFIRMED=true
      SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
      success "  ✓ CONFIRMED: $POC_NAME"
    else
      FAIL_COUNT=$((FAIL_COUNT + 1))
      warn "  ✗ Not confirmed (may be false positive or env issue): $POC_NAME"
    fi

    # Append to results JSON
    POC_OUTPUT_ESCAPED=$(echo "$POC_OUTPUT" | python3 -c "import sys, json; print(json.dumps(sys.stdin.read()))" 2>/dev/null || echo "\"output unavailable\"")

    python3 - <<EOF >> /dev/null 2>&1
import json

with open('$RESULTS_FILE', 'r') as f:
    data = json.load(f)

data['results'].append({
    "poc_file": "$POC_NAME",
    "confirmed": $CONFIRMED,
    "exit_code": $POC_EXIT,
    "output": $POC_OUTPUT_ESCAPED
})

with open('$RESULTS_FILE', 'w') as f:
    json.dump(data, f, indent=2)
EOF

  done

  # Summary
  echo ""
  log "=============================="
  log "PoC Execution Summary"
  log "=============================="
  log "Total PoCs run:    $POC_COUNT"
  success "Confirmed:         $SUCCESS_COUNT"
  warn "Not confirmed:     $FAIL_COUNT"
  log "Results written to: security-poc-results.json"

  # Stop wp-env
  log "Stopping wp-env..."
  npx @wordpress/env stop 2>&1 | tail -2
  success "wp-env stopped."
}

# =============================================================================
# COMMAND: cleanup
# Removes temporary files, keeps report and PoCs
# =============================================================================
cleanup() {
  log "Cleaning up temporary files..."

  rm -f semgrep-results.json
  rm -f security-poc-results.json

  # Remove wp-env generated files but keep .wp-env.json
  rm -rf .wp-env/

  success "Cleanup complete. SECURITY_REPORT.md and security-pocs/ have been preserved."
}

# =============================================================================
# ENTRYPOINT
# =============================================================================
case "$1" in
  semgrep)
    run_semgrep
    ;;
  run-pocs)
    run_pocs
    ;;
  cleanup)
    cleanup
    ;;
  *)
    echo "Usage: bash security-audit.sh [semgrep|run-pocs|cleanup]"
    echo ""
    echo "  semgrep    Run Semgrep static analysis and output semgrep-results.json"
    echo "  run-pocs   Start wp-env, run all PoCs in security-pocs/, output results"
    echo "  cleanup    Remove temporary files (preserves SECURITY_REPORT.md)"
    exit 1
    ;;
esac