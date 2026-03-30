# Run Unit Tests (Visualizer Free)

Run the PHPUnit test suite for the Visualizer free plugin.

## Commands

```bash
# Ensure MySQL is running and accessible (default: 127.0.0.1:3306).
# Example (macOS/Homebrew):
# brew services start mysql
# Example (Linux):
# sudo service mysql start

# Install PHP dependencies if not already done
composer install

# Install WordPress test suite (required for phpunit)
# Format: bash bin/install-wp-tests.sh <db_name> <db_user> <db_pass> <db_host> [wp_version]
bash bin/install-wp-tests.sh wordpress_test root root 127.0.0.1

# Run the full PHPUnit suite
./vendor/bin/phpunit

# Run a single test file (replace the path as needed)
# ./vendor/bin/phpunit tests/test-export.php
```

## Instructions

1. Check that `vendor/` exists. If not, run `composer install` first.
2. Ensure the WordPress test suite is installed using `bin/install-wp-tests.sh`.
3. Run the tests. Show output as it streams.
4. Report a summary: how many tests passed, failed, and any error messages.
5. If the user specified a particular test file or test name, run only that:
   - Single file: `./vendor/bin/phpunit tests/test-<name>.php`
   - Single test method: `./vendor/bin/phpunit --filter testMethodName`
