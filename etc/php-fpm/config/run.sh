#!/usr/bin/env bash
set -eu

echo "+ Ensure storage Permission"
chmod -R 777 /var/www/storage

echo "+ Set Oauth Permission"
chmod 600 /var/www/storage/oaut*

echo "run supervisor worker"
service supervisor start

echo "run fpm"
php-fpm7.1

