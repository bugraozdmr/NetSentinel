#!/bin/bash

# NetSentinel Cron Check Script
# Add to crontab: */5 * * * * /path/to/netsentinel/api/cron-check.sh

# Set the path to your NetSentinel installation
NETSENTINEL_PATH="/Applications/XAMPP/xamppfiles/htdocs/NetSentinel"

# Change to the NetSentinel directory
cd "$NETSENTINEL_PATH"

# Log file for cron execution
LOG_FILE="$NETSENTINEL_PATH/api/logs/cron-check-$(date +%Y-%m-%d).log"

# Create logs directory if it doesn't exist
mkdir -p "$NETSENTINEL_PATH/api/logs"

# Execute the check using the cron-specific runner
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting NetSentinel cron check..." >> "$LOG_FILE"

# Run the cron check runner
php "$NETSENTINEL_PATH/api/app/worker/cron-check-runner.php" >> "$LOG_FILE" 2>&1

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Cron check completed." >> "$LOG_FILE"
echo "----------------------------------------" >> "$LOG_FILE"

# Keep only last 7 days of logs
find "$NETSENTINEL_PATH/api/logs" -name "cron-check-*.log" -mtime +7 -delete 