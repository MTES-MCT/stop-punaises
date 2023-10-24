#!/bin/bash

echo "Executing migration..."
php bin/console doctrine:migrations:migrate --no-interaction

if [[ -z "${COMPOSER_DEV}" ]];
then
    echo "No data fixtures to load!!"
    echo "Optimize Composer Autoloader"
    composer dump-autoload --no-dev --classmap-authoritative
else
    echo "Load data fixtures..."
    composer dump-env dev && php bin/console -e dev doctrine:fixtures:load --no-interaction
    echo "Load entreprises publiques"
    php bin/console app:import-entreprise-publique --no-interaction
    php bin/console app:import-entreprise-publique-detection-canine --no-interaction
    echo "Optimize Composer Autoloader with dev dependencies"
    composer dump-autoload --classmap-authoritative
fi
