# Instalación y operación

Hace falta PHP 8.3 o superior y Composer. La zona de la aplicación es `Europe/Madrid`. La base de datos de desarrollo es el archivo `database/database.sqlite`.

No copies contraseñas a estos documentos. Las cuentas de demostración y su clave se leen en la pantalla de entrada, en «Cuentas de prueba». Son personas ficticias.

## Instalar

En la carpeta del proyecto:

```bash
composer install
php artisan key:generate
```

Copia `.env.example` a `.env`. En PowerShell: `copy .env.example .env`. En bash: `cp .env.example .env`.

Crea el archivo de base de datos si no existe y aplica las migraciones y los datos de demostración:

```bash
php artisan migrate
php artisan db:seed
```

Arranque:

```bash
php artisan serve
```

Abre `http://127.0.0.1:8000/login`.

`php artisan db:seed` crea las personas de demostración. No lo vuelvas a lanzar sobre una base que ya tiene esas cuentas: el seeder inserta filas nuevas y chocará con el correo repetido.

## Actualizar

Con el código nuevo ya en la carpeta:

```bash
composer install
php artisan migrate
```

`migrate` solo aplica los cambios de base de datos que falten. No borra las jornadas ya guardadas.

## Copia

Para la aplicación y espera a que no quede ninguna petición a medias. Copia estos elementos:

- `database/database.sqlite`
- el archivo `.env` (tiene la clave de la aplicación; no lo subas al repositorio)

Una copia solo del código no recupera los fichajes.

## Restaurar

1. Para la aplicación.
2. Sustituye `database/database.sqlite` por la copia.
3. Comprueba que `.env` sigue apuntando a `DB_CONNECTION=sqlite`.
4. Vuelve a arrancar con `php artisan serve`.
5. Entra y abre un registro que sepas que estaba en la copia.

No hay una pantalla de copias. La restauración es sustituir el archivo. Conviene probar este recorrido en el entorno elegido antes de depender de él. Está anotado como pendiente en [pruebas.md](pruebas.md).

## Pruebas

```bash
php artisan test
```

Las pruebas usan una base en memoria. No escriben en `database/database.sqlite`. El detalle de casos y defectos abiertos está en [pruebas.md](pruebas.md).
