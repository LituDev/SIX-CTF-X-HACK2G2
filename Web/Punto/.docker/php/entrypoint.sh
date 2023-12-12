#!/bin/bash
composer install --no-interaction --no-progress --no-suggest --prefer-dist

npm install --no-progress --no-audit --no-optional --no-save

npm run dev

php bin/console d:s:u --force --em=mysql
php bin/console d:s:u --force --em=sqlite
php bin/console d:m:s:u
php bin/console cache:warmup

FLAG_5=$(cat /var/www/symfony/flag5)
php bin/console app:warmup-flag $FLAG_5
rm /var/www/symfony/flag5


/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf || exit 1