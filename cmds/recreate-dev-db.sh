#!/usr/bin/env bash
rm db.sqlite
touch db.sqlite
php artisan passport:keys --force
php artisan migrate --seed
