# mbinv-hibrido (Symfony 7.3 + PHP 8.4 + Nginx, conectando a MySQL remoto)

Este stack levanta un entorno completo para desarrollar con Symfony (última 7.x), PHP 8.4 (FPM) y Nginx, **sin base de datos local**, conectando a un **MySQL remoto** provisto por Christian.

## Requisitos en tu máquina (host)
- Windows 10/11 con **Docker Desktop** (WSL2) o Linux/macOS con Docker Engine + plugin docker compose
- (Opcional) VS Code / PhpStorm
- (Opcional) Git

## Estructura
```
mbinv-hibrido/
  docker-compose.yml
  docker/
    php/
      Dockerfile
    nginx/
      default.conf
  .env.local        # será leído por Symfony (no commitear)
```

## 1) Levantar el stack
En la raíz del proyecto:
```bash
docker compose build
docker compose up -d
docker compose ps
```

## 2) Crear el proyecto Symfony dentro del contenedor
Entra al contenedor PHP:
```bash
docker exec -it mbinv_php bash
```

Dentro del contenedor:
```bash
composer create-project symfony/skeleton "^7.3" .
composer require symfony/webapp-pack
composer require symfony/orm-pack
composer require --dev symfony/maker-bundle
php -v
php bin/console about
```

> Si ya tienes un proyecto Symfony existente, simplemente **copia tu código** dentro de esta carpeta y omite `create-project`.

## 3) Configurar conexión a MySQL remoto
Crea/edita **.env.local** en la raíz del proyecto (host) con:
```
APP_ENV=dev
# Credenciales provistas por Christian (sensible: no subir a git)
# Nota: la contraseña tiene '@' y debe ir codificada como %40
DATABASE_URL="mysql://PruebasMB:MbPru3bas2025%40@132.226.40.48:3310/PruebasMB?serverVersion=8.0&charset=utf8mb4"
```

> Si el `serverVersion` exacto de tu MySQL remoto es 8.4, puedes usar `serverVersion=8.4`; si no estás seguro, 8.0 funciona en general con Doctrine.

## 4) Probar Symfony en el navegador
Abre http://localhost:8080

## 5) Probar la conexión a la base (opcional)
```bash
docker exec -it mbinv_php bash -lc "php bin/console doctrine:database:connect"
```

Si necesitas crear tablas con Doctrine:
```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Notas y tips
- **No instales XAMPP**: el PHP/MySQL/Nginx están en contenedores.
- Firewalls/seguridad: al ser una BD **remota y pública (3310)**, considera filtrar IPs permitidas en el servidor MySQL, o usar túnel/VPN en producción.
- Logs de Nginx/PHP: revisa con `docker logs mbinv_nginx` o `docker logs mbinv_php`.
- Reiniciar solo Nginx o PHP:
  ```bash
  docker compose restart nginx
  docker compose restart php
  ```
- Si cambias la versión de Symfony, ajusta los comandos de Composer.
- Si requieres Xdebug en dev, instálalo en `Dockerfile` con:
  ```
  pecl install xdebug && docker-php-ext-enable xdebug
  ```
  y configura `xdebug.mode=develop,debug` en un `.ini`.

¡Listo! Con esto tienes un contenedor **completo** con PHP 8.4 + Nginx y Symfony, conectado a **MySQL remoto**.
