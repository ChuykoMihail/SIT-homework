cd ..
git fetch pull
composer install
php bin/console cache:clear
php bin/console cache:warmup