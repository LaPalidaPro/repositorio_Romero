# Proyecto Symfony: Renovación de la Base de Datos y Carga de Fixtures

Este documento describe los pasos necesarios para renovar la base de datos y cargar datos desde fixtures en un proyecto Symfony, ejecutado con XAMPP.

## Prerrequisitos

Asegúrate de tener instalados los siguientes componentes:

- PHP 8.1.25
- Symfony 6.4
- XAMPP para ejecutar la base de datos

### Versiones del Software

- **PHP**: 8.1.25
- **Symfony**: 6.4

## Pasos para Renovar la Base de Datos

### 1. Crear la Base de Datos

Si la base de datos no existe, puedes crearla usando el siguiente comando:

```bash
php bin/console doctrine:database:create
```
### 2. Cargar los Datos desde Fixtures
Para cargar los datos definidos en tus fixtures, ejecuta el siguiente comando:
```bash
php bin/console doctrine:fixtures:load --no-interaction
```