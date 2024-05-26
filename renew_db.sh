#!/bin/bash

# Eliminar la base de datos actual (si existe) y crear una nueva
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create

# Ejecutar las migraciones para crear la estructura de la base de datos
php bin/console doctrine:migrations:migrate --no-interaction

# Cargar los datos desde fixtures
php bin/console doctrine:fixtures:load --no-interaction
