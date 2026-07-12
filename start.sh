#!/bin/bash
php artisan cache:clear
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 &
php artisan serve --host=0.0.0.0 --port=8000
