#!/bin/bash

# Configuration
APP_PATH="/www/wwwroot/app.batistack.ovh"
PHP_BIN="php"

echo "--- 🚀 Starting Deployment: $(date) ---"

cd $APP_PATH || exit

# 1. Mode Maintenance
$PHP_BIN artisan down --refresh=15 --retry=60

# 2. Récupération du code
git pull origin master

# 3. Installation des dépendances PHP
composer install --no-interaction --prefer-dist --optimize-autoloader

# 4. Installation et Compilation des Assets (Spécifique Ubuntu 24.04)
if [ -f "package.json" ]; then
    npm install

    # On installe les dépendances système manquantes pour Chrome (nécessite sudo ou privilèges)
    # Si vous n'avez pas sudo dans le webhook, il faudra lancer cette ligne une fois manuellement en SSH
    # sudo npx puppeteer browsers install chrome --install-deps

    # Installation du binaire Chrome local au projet
    npx puppeteer browsers install chrome

    npm run build
fi

# 5. Migration de la base de données
$PHP_BIN artisan migrate --force

# 6. Optimisation des performances
$PHP_BIN artisan optimize
$PHP_BIN artisan view:cache
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache

/www/server/panel/pyenv/bin/supervisorctl restart frais_worker:frais_worker_00

# 7. Relancer l'application
$PHP_BIN artisan up

echo "--- ✅ Deployment Finished: $(date) ---"
echo ""
