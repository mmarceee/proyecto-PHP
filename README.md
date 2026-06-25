# GendarApp

Aplicacion web desarrollada con Laravel 13, PHP 8.5, MySQL, Redis, MongoDB,
Laravel Reverb, Vite y Tailwind CSS. El entorno local se ejecuta con Docker
Desktop, WSL2 y Laravel Sail.

## Requisitos

- Windows 10/11 con WSL2 y una distribucion Linux instalada (Ubuntu recomendado).
- Docker Desktop con la integracion para WSL2 habilitada.
- Git, solo si el proyecto se obtiene desde un repositorio.
- Visual Studio Code y el comando `code` en WSL son opcionales. `inicio.sh`
  intenta abrir el proyecto con `code .`, pero esto no afecta a los contenedores.

No es necesario instalar PHP, Composer, MySQL, Redis, MongoDB ni Node.js en el
equipo anfitrion.

## Instalacion inicial

Ejecutar todos los comandos desde una terminal de WSL2, dentro de la carpeta del
proyecto.

Comprobar que la terminal utiliza un usuario normal de Ubuntu:

```bash
whoami
```

El resultado no debe ser `root`. Ejecutar la instalacion como `root` puede hacer
que `vendor/`, `node_modules/`, `storage/` y otros archivos queden con permisos
incorrectos.

### 1. Instalar las dependencias de PHP

Como Laravel Sail esta dentro de `vendor`, la primera instalacion se realiza con
la imagen oficial de Composer:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/app" \
    -w /app \
    composer:2 \
    install --ignore-platform-reqs
```

Explicacion del comando:

- `docker run --rm`: crea un contenedor temporal y lo elimina al terminar.
- `-u "$(id -u):$(id -g)"`: utiliza el usuario actual de WSL para evitar que
  los archivos generados pertenezcan a `root`.
- `-v "$(pwd):/app"`: monta la carpeta actual del proyecto dentro del
  contenedor, en `/app`.
- `-w /app`: establece `/app` como directorio de trabajo.
- `composer:2`: utiliza la imagen oficial de Composer 2.
- `install`: instala las versiones indicadas en `composer.lock` y genera
  `vendor/`, donde tambien queda disponible `vendor/bin/sail`.
- `--ignore-platform-reqs`: permite hacer esta instalacion inicial aunque la
  imagen temporal no tenga todas las extensiones PHP del proyecto. El
  contenedor definitivo de Sail incluye el entorno configurado para la
  aplicacion.

La imagen `composer:2` ya ejecuta Composer como comando principal; por eso al
final se escribe `install` y no `composer install`.

Este paso es necesario en una instalacion limpia porque `vendor/` no se incluye
en la entrega: contiene dependencias que Composer puede regenerar a partir de
`composer.lock`.

### 2. Crear y configurar el entorno

```bash
cp .env.example .env
sed -i 's/\r$//' .env
```

El comando `sed` convierte los finales de linea de Windows (`CRLF`) al formato
de Linux (`LF`) y evita errores como `$'\r': command not found`.

Editar `.env` y, como minimo, configurar estos valores para los servicios de
`compose.yaml`:

```dotenv
APP_NAME=GendarApp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=gendarapp
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null
QUEUE_CONNECTION=redis
CACHE_STORE=redis

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=gendarapp-local
REVERB_APP_KEY=gendarapp-local-key
REVERB_APP_SECRET=gendarapp-local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

MONGODB_HOST=mongodb
MONGODB_PORT=27017
MONGODB_DATABASE=gendarapp_logs
MONGODB_USERNAME=sail
MONGODB_PASSWORD=secret
```

Los valores de Google OAuth, PayPal y LiveKit son necesarios solamente para usar
esas integraciones. Deben solicitarse al responsable del proyecto y agregarse al
`.env`; nunca deben incluirse credenciales reales en el codigo entregado:

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback

PAYPAL_MODE=sandbox
PAYPAL_SANDBOX_CLIENT_ID=
PAYPAL_SANDBOX_CLIENT_SECRET=
PAYPAL_CURRENCY=USD
PAYPAL_UYU_TO_USD_RATE=40

LIVEKIT_URL=
LIVEKIT_API_KEY=
LIVEKIT_API_SECRET=
```

Si algun puerto ya esta ocupado, se puede publicar otro puerto desde `.env`, por
ejemplo `APP_PORT=8081`, `FORWARD_DB_PORT=3307` o
`FORWARD_MONGODB_PORT=27018`. En ese caso tambien hay que ajustar `APP_URL`.

### 3. Preparar la aplicacion

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail npm install
./vendor/bin/sail artisan migrate
```

Si `npm install` devuelve un error `EACCES` al crear `node_modules`, corregir los
permisos desde el contenedor y volver a intentarlo:

```bash
./vendor/bin/sail root-shell -c \
    "chown -R sail:sail /var/www/html"

./vendor/bin/sail npm install
```

Este problema suele ocurrir cuando el proyecto fue copiado, descomprimido o
preparado utilizando el usuario `root`.

## Arranque habitual

Dar permiso de ejecucion al script la primera vez:

```bash
chmod +x inicio.sh
```

Luego iniciar el entorno con:

```bash
./inicio.sh
```

El script comprueba Docker Desktop, levanta Laravel Sail y mantiene en ejecucion:

- Vite (`npm run dev`).
- El worker de colas con Redis.
- Laravel Reverb en el puerto `8080`.
- El scheduler de Laravel.

Mientras se desarrolla, la terminal que ejecuta `inicio.sh` debe permanecer
abierta.

## Direcciones locales

- Aplicacion: <http://localhost>
- Vite: <http://localhost:5173>
- Reverb: `ws://localhost:8080`
- Mailpit: <http://localhost:8025>

Si se definio `APP_PORT`, utilizar ese puerto para acceder a la aplicacion.

## Detener el entorno

Detener los procesos de `inicio.sh` con `Ctrl+C` y luego ejecutar:

```bash
./vendor/bin/sail down
```

Para borrar tambien las bases de datos locales y comenzar desde cero:

```bash
./vendor/bin/sail down -v
```

Este ultimo comando elimina los volumenes locales de MySQL, Redis y MongoDB.
