#!/usr/bin/env bash
set -euox pipefail

##
# This script copys the templated MySQL data into the 
# persistent volume mount and then handles forwarding
# the MySQL logs to stdout.
#
# This will tail the file logs configured in ./my.cnf
##

function handle_mysql_exit() {
  echo "MySQL exited."
  echo "MySQL content:"
  ls -la /var/lib/mysql/
  echo "Log files:"
  ls /var/log/
  echo "MySQL log file content:"
  tail -n 100 /var/log/mysql_*.log
}

trap handle_mysql_exit EXIT

if [ ! -e /var/lib/mysql ]; then
  echo "warning: MySQL test container is running without persistence!"  
  mkdir /var/lib/mysql
fi
if [ ! -e /var/lib/mysql/server-key.pem ]; then
  echo "Copying MySQL from template content..."  
  cp -r /var/lib/mysql-template/* /var/lib/mysql/
fi

echo "Starting MySQL log forwarding..."
LOG_PATHS=(
  # '/var/log/mysql_general.log'
  '/var/log/mysql_error.log'
  # '/var/log/mysql_slow_query.log'
)
for LOG_PATH in "${LOG_PATHS[@]}"; do
  # https://serverfault.com/a/599209
  ( umask 0 && truncate -s0 "$LOG_PATH" )
  tail --pid $$ -n0 -F "$LOG_PATH" &
done

echo "Starting MySQL..."
cd /var/lib/mysql
/usr/sbin/mysqld --daemonize=OFF --bind-address=0.0.0.0