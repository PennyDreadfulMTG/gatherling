#!/bin/bash

set -e

mysqld &
MYSQLD=$!
echo "MysqlD running as PID $MYSQLD"
set +e
i=0
while [ $i -ne 10 ]; do
    echo '' | mysql -w
    if [ $? -eq 0 ]; then
        break
    fi
    i=$(($i+1))
    sleep 1
done
set -e
echo "Granting root@% access..."
echo "CREATE USER 'root'@'%' IDENTIFIED BY ''; GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' WITH GRANT OPTION;" | mysql -w
echo "Creating gatherling database..."
echo "CREATE DATABASE gatherling;" | mysql -w
echo "Shutting down MySQL..."
kill $MYSQLD
wait $MYSQLD
