cd ..
git pull origin dic
composer install
php bin/console cache:clear
php bin/console cache:warmup
sleep 100