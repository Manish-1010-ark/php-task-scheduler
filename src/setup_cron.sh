#!/bin/bash

# Get full path to this script's directory
DIR="$(cd "$(dirname "$0")" && pwd)"
PHP_PATH=$(which php)

# CRON job: Run cron.php every hour
CRON_JOB="0 * * * * $PHP_PATH $DIR/cron.php"

# Prevent duplicate cron entries for this file
(crontab -l 2>/dev/null | grep -F -v "$DIR/cron.php"; echo "$CRON_JOB") | crontab -

echo "CRON job installed to run cron.php every hour."
