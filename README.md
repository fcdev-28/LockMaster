# LockMaster

Sistema de control de accesos: puntos de acceso, grupos de usuarios, reglas de
autorización por día de la semana y franja horaria, e histórico de accesos.
Proyecto de FCT de 2º DAW.

Symfony 5.4 · PHP 8.2 · MySQL 8 · Doctrine ORM · Twig · API Platform 2.7 ·
Lexik JWT · Webpack Encore · fixtures con Zenstruck Foundry.

## Requisitos

Solo Docker. En macOS sirve OrbStack o Docker Desktop; el resto (PHP, Node,
Composer, Yarn) va dentro de la imagen.

Si usas OrbStack, arráncalo antes o `docker compose` fallará con
`failed to connect to the docker API at unix:///Users/<usuario>/.orbstack/run/docker.sock`.

## Levantarlo

```sh
docker compose up --build
```

La primera vez tarda unos minutos: instala dependencias de Composer, de Yarn y
compila los assets con Encore. Cuando veas `resuming normal operations` en el
log, está lista.

| Qué | Dónde |
| --- | --- |
| Aplicación | http://localhost:8080 |
| Documentación de la API (Swagger) | http://localhost:8080/api |
| Documento OpenAPI en JSON | http://localhost:8080/api/docs.json |
| MySQL desde el host | `localhost:3307` |

### Credenciales

| Usuario | Contraseña | Rol |
| --- | --- | --- |
| `admin` | `admin` | Administrador |
| El resto de usuarios generados | `1234` | Usuario normal |

Los nueve usuarios no administradores salen de las fixtures con nombre y
username aleatorios: míralos en http://localhost:8080/users.

Base de datos: `lockmaster` / `lockmaster`, esquema `lockmaster`. La contraseña
de root es `root`. Son credenciales de desarrollo y están en `compose.yaml` a la
vista; no uses este montaje en producción.

## Qué hace al arrancar

`docker/entrypoint.sh`, cada vez que arranca el contenedor `app`:

1. Espera a que MySQL acepte conexiones.
2. Genera el par de claves JWT en `config/jwt/` si no existe (esos `.pem` están
   en `.gitignore`, así que nunca vienen en el repo).
3. Aplica las migraciones de Doctrine.
4. Carga las fixtures, **si `LOAD_FIXTURES=1`**.
5. Limpia la caché de Symfony.

⚠️ El paso 4 **vacía la base de datos antes de cargar**. `compose.yaml` trae
`LOAD_FIXTURES: "1"`, así que cualquier cosa que crees por la interfaz
desaparece en el siguiente `docker compose up`. Para conservar los datos, pon
`LOAD_FIXTURES: "0"` en `compose.yaml`.

## Comandos habituales

```sh
# Arrancar en segundo plano
docker compose up --build -d

# Ver el log de la aplicación
docker compose logs -f app

# Parar (los datos sobreviven)
docker compose down

# Parar y borrar la base de datos: la próxima vez los IDs vuelven a empezar en 1
docker compose down -v

# Una consola dentro del contenedor
docker compose exec app bash

# Cualquier comando de Symfony
docker compose exec app php bin/console debug:router

# Recargar las fixtures a mano
docker compose exec app php bin/console doctrine:fixtures:load --no-interaction

# Cliente de MySQL
docker compose exec db mysql -ulockmaster -plockmaster lockmaster
```

Los datos de las fixtures son aleatorios: cada recarga cambia nombres, horarios
y qué puntos de acceso están activos.

## La API

Dos operaciones, documentadas en http://localhost:8080/api:

- `POST /token` — devuelve un JWT a partir de `username` y `password`.
- `GET /api/access/check?access_point_id=N` — dice si el usuario del token
  tiene acceso a ese punto. Necesita la cabecera `Authorization`.

```sh
TOKEN=$(curl -s -X POST http://localhost:8080/token \
  -H 'Content-Type: application/json' \
  -d '{"username":"admin","password":"admin"}' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['token'])")

curl -s "http://localhost:8080/api/access/check?access_point_id=1" \
  -H "Authorization: Bearer $TOKEN"
# {"@context":"/api/contexts/AccessCheckOutput", ... ,"access":false}
```

Cada llamada a `/api/access/check` que termina bien deja una fila en el
histórico de accesos, tanto si concede como si deniega.

Códigos de respuesta: `200` con `access` true o false, `400` sin token o sin
`access_point_id`, `404` si el punto de acceso no existe.

Una regla sin franja horaria (`start_timestamp` a null) se considera disponible
todo el día, así que concede acceso a cualquier hora. Las que sí tienen horario
solo conceden dentro de él.

## Capturas de pantalla

`capturas/` tiene un script de Playwright que se loguea como `admin` y saca
cinco capturas a 1440x900 en `capturas/out/`: listado de puntos de acceso,
matriz de reglas por día, histórico de accesos y las dos operaciones del Swagger
desplegadas. Oculta la toolbar del profiler antes de disparar.

```sh
cd capturas
npm install          # solo la primera vez
npx playwright install chromium
node screenshots.js
```

Con la aplicación en otro puerto o máquina: `BASE_URL=http://otro:puerto node screenshots.js`.

## Notas del montaje con Docker

Cosas que no son evidentes y que conviene no deshacer sin querer:

- El `Dockerfile` corre `yarn install` **después** de `composer install`, porque
  `package.json` referencia `file:vendor/symfony/ux-dropzone/assets`.
- `docker/vhost.conf` redefine `Alias /icons/`. Debian trae
  `Alias /icons/ "/usr/share/apache2/icons/"` en `mods-enabled/alias.conf`, que
  tapa `public/icons/` y deja la fuente icomoon en 404: sin esto, todos los
  iconos de la interfaz salen en blanco.
- El bucle de espera del entrypoint usa `mysqladmin --skip-ssl`: el cliente
  MariaDB de Debian rechaza el certificado autofirmado de MySQL 8 y, sin esa
  opción, la espera no termina nunca.
- `docker/vhost.conf` trae `SetEnvIf Authorization`. Apache no expone esa
  cabecera en `$_SERVER` y Symfony solo la busca ahí, así que sin esa línea el
  token JWT se ignora y la API responde `Not authenticated`.
- El `DATABASE_URL` del `.env` apunta a PostgreSQL, que es lo que trae el
  esqueleto de Symfony sin tocar. `compose.yaml` lo pisa por variable de
  entorno con la URL de MySQL, que es la que corresponde a las migraciones.
