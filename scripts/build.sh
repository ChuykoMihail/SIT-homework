cd ..
git fetch pull origin
composer install
php bin/console cache:clear
php bin/console cache:warmup