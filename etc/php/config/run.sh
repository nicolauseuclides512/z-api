#!/usr/bin/env bash
set -eu

echo "+ Ensure storage Permission"
chmod -R 777 /var/www/storage

#echo "+ Ensure Cache Permission"
#chmod -R 777 /var/www/bootstrap/cache

echo "+ Set Oauth Permission"
chmod 600 /var/www/storage/oaut*

echo "run supervisor worker"
service supervisor start

echo "run fpm"
php-fpm

