# Programa de Desarrollo Institucional (PDI)

<p align="center">
    <img src="resources/img/logo.png" alt="Logo PDI" width="200"/>
</p>

## Descripción

Programa de Desarrollo Institucional (PDI) es un sistema de seguimiento del proyecto de desarrollo institucional, asi como una herramienta para la medición de los resultados de los proyectos de desarrollo institucional, enfocado pero no limitado al ámbito publico.

## Requisitos

1. MySQL
1. Composer (PHP 8.0+)
1. npm (Node.js 12.0+)

## Instalación

1. Clonar este repositorio:

    ```
    $ git clone git@github.com:pl402/pdi.git
    ```

2. Entrar en el directorio del repositorio:

    ```
    $ cd pdi
    ```

3. Instalar dependencias:

    ```
    $ npm install
    $ composer install
    ```

## Configuración

1. Crear una base de datos en MySQL (se recomienda usar contraseña mas fuerte):

    ```
    $ mysql -u root -p
    mysql> create database pdi;
    mysql> grant all on pdi.* to usuario@localhost identified by 'password';
    mysql> quit
    ```

2. Configurar variables de entorno:

    1. Copiar el archivo `.env.example` a `.env`
        ```
        $ cp .env.example .env
        ```
    2. Modificar el archivo `.env` las variables de entorno de la base de datos:
        ```
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=pdi
        DB_USERNAME=usuario
        DB_PASSWORD=password
        ```
    3. Generar clave de encriptación de la aplicación:

        ```
        $ php artisan key:generate
        ```

    4. Realizar migraciones:
        ```
        $ php artisan migrate:refresh --seed
        ```

3. Correr la aplicación, esto genera un servidor local (por default en el puerto 8000), así como un watcher para generar automáticamente nuevos archivos públicos `.js` y `.css` al modificar los archivos `.vue`:

    ```
    $ npm run serve
    ```

4. Ir a la página de principal e iniciar sesión:

    ```
    http://localhost:8000/
    ```

    - _Usuario:_ `correo@admin.com`
    - _Contraseña:_ `C0n7r4s3ña!`

5. Para producción se recomienda usar un servidor como NGINX, Apache, etc.,

    ```
    $ npm run production
    ```
