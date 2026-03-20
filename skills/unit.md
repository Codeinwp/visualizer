# Run Unit Tests (Visualizer Free)

Run the PHPUnit test suite for the Visualizer free plugin.

## Commands

```bash
# Install PHP dependencies if not already done
composer install

# Run the full PHPUnit suite
./vendor/bin/phpunit

# Run a single test file (replace the path as needed)
# ./vendor/bin/phpunit tests/test-export.php
```

## Instructions

1. Check that `vendor/` exists. If not, run `composer install` first.
2. Run the tests. Show output as it streams.
3. Report a summary: how many tests passed, failed, and any error messages.
4. If the user specified a particular test file or test name, run only that:
   - Single file: `./vendor/bin/phpunit tests/test-<name>.php`
   - Single test method: `./vendor/bin/phpunit --filter testMethodName`
