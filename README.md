# Proyecto HarmonyHub

## Manual de Instalación y Configuración

### 1. Requisitos Previos

Antes de comenzar con la instalación y configuración de la plataforma, asegúrese de tener los siguientes requisitos previos:

- PHP: Versión 8.1 o superior
- Base de Datos: MySQL
- Composer: Para la gestión de dependencias PHP
- Node.js y npm: Para la gestión de paquetes frontend (opcional, si se utiliza)
- Symfony CLI: Para gestionar el servidor embebido de Symfony

### 2. Instalación

**Paso 1: Clonar el Repositorio**

Primero, clone el repositorio del proyecto desde GitHub:

```bash
git clone https://github.com/LaPalidaPro/repositorio_Romero.git
cd repositorio_Romero
```
**Paso 2: Instalar Dependencias con Composer**

Una vez clonado el repositorio, instale las dependencias de PHP utilizando Composer:

```bash
composer install
```

**Paso 3: Configurar Variables de Entorno**

Edite el archivo .env para configurar las credenciales de su base de datos (DATABASE_URL). Asegúrese de reemplazar usuario y contraseña con sus credenciales correctas:

DATABASE_URL="mysql://root:@127.0.0.1:3306/harmonyhub?serverVersion=8.0.32&charset=utf8mb4"

**Paso 4: Configurar la Base de Datos**

Cree la base de datos y aplique las migraciones:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```
Si desea cargar datos de prueba (fixtures):

```bash
php bin/console doctrine:fixtures:load
```

### 3. Configuración del Servidor Embebido de Symfony
Inicie el servidor embebido de Symfony utilizando Symfony CLI. Este servidor es ideal para desarrollo y pruebas locales.
```bash
symfony server:start
```

El servidor iniciará y estará disponible en http://127.0.0.1:8000 por defecto. Si necesita cambiar el puerto, puede especificarlo:
```bash
symfony server:start --port=8080
```

### 4. Seguridad y Configuración Adicional
Tokens CSRF

La plataforma utiliza tokens CSRF para proteger los formularios de autenticación y otras acciones sensibles. Asegúrese de que esta configuración esté habilitada para proteger sus formularios:

En config/packages/framework.yaml:

```bash
framework:
    secret: '%env(APP_SECRET)%'
    csrf_protection: ~
    annotations: false
    http_method_override: false
    handle_all_throwables: true
```
Asegúrese también de que la configuración de seguridad en config/packages/security.yaml esté adecuada:

```bash
security:
    firewalls:
        main:
            form_login:
                csrf_token_generator: security.csrf.token_manager

```