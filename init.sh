#!/bin/bash

cp .env.example .env
composer install
php -S localhost:8000 -t .
