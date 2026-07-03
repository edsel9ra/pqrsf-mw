# Plataforma PQRSF Mister Wings

Aplicación Laravel para gestionar formularios PQRSF de Mister Wings: recepción pública de solicitudes, administración en Filament, validación, envío a destinatarios por sede, reportes y PDF de soporte.

El código de la aplicación vive en `./src`. El proyecto se ejecuta con Docker desde la raíz del repositorio.

## Stack

- Laravel 13 y PHP 8.4
- Filament v5 para el panel administrativo
- MySQL 8.4
- Tailwind CSS v4, Alpine.js y Vite/pnpm
- DomPDF para reportes y documentos PDF

## Requisitos

- Docker y Docker Compose
- Acceso a los puertos locales `8080`, `8081`, `5173` y `3307`

## Puesta En Marcha

Desde la raíz del repositorio:

```bash
docker compose up -d --build
```

URLs locales:

- Aplicación: `http://localhost:8080`
- Formulario público: `http://localhost:8080/pqrsf`
- Panel admin: `http://localhost:8080/admin`
- phpMyAdmin: `http://localhost:8081`
- Vite HMR: `http://localhost:5173`

Usuario admin seed:

```text
admin@pqrsf.com / admin123
```

## Comandos Frecuentes

Todos los comandos de Laravel se ejecutan dentro del contenedor `app`:

```bash
docker compose exec app php artisan test
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app ./vendor/bin/pint app tests
docker compose exec app php artisan config:clear
```

Frontend/Vite se ejecuta dentro del contenedor `vite`:

```bash
docker compose exec -w /var/www vite pnpm run build
```

Prueba enfocada del flujo admin PQRSF:

```bash
docker compose exec app php artisan test tests/Feature/AdminPqrsfSubmissionsTest.php
```

## Configuración

- La raíz `.env` solo define `UID` y `GID` para Docker.
- La configuración real de Laravel está en `src/.env`.
- Después de cambiar `src/.env`, ejecutar:

```bash
docker compose exec app php artisan config:clear
```

Dentro de Docker, MySQL se conecta como `db:3306`. Desde el host está expuesto en `localhost:3307`.

No publicar credenciales SMTP ni copiar valores sensibles de `src/.env` en documentación o commits.

## Estructura Principal

- `src/app/Http/Controllers/PqrsfController.php`: formulario público `/pqrsf`.
- `src/app/Http/Requests/StorePqrsfRequest.php`: validación y normalización del formulario público.
- `src/app/Services/FormFieldService.php`: campos dinámicos por defecto.
- `src/app/Filament/Resources/*`: recursos del panel administrativo.
- `src/app/Filament/Resources/PqrsfSubmissions/Tables/PqrsfSubmissionsTable.php`: tabla, filtros y acciones del flujo PQRSF.
- `src/app/Mail/PqrsfSubmissionMail.php`: correo enviado a destinatarios.
- `src/resources/views/emails/*`: cuerpo del correo y PDF asociado.
- `src/app/Http/Controllers/PqrsfSubmissionPdfController.php`: PDF firmado de una PQRSF.

## Modelo De Datos PQRSF

Los datos variables del formulario se guardan en JSON en `pqrsf_submissions.field_values`. No existen columnas separadas para cada respuesta.

Ejemplo de acceso:

```php
$record->field_values['nombre_completo']
$record->field_values['opcion_a_calificar']
```

Tipos de campo soportados en `form_fields.type`:

```text
text, email, tel, textarea, select, rating, boolean, checkbox_list
```

## Flujo Operativo

Estados:

```text
pending -> validated -> sent
```

- Toda solicitud pública inicia como `pending`.
- En admin, `Cambiar opción` solo está disponible mientras la solicitud está `pending`.
- `Validar` cambia el estado a `validated`.
- `Enviar a destinatarios` solo aparece para `validated`.
- El envío usa los `sede_recipients` activos de la sede asociada.
- Si todos los correos se envían correctamente, el estado cambia a `sent`.
- Si una sede no tiene destinatarios activos, no se enviará correo.

## Correos Y PDF

El correo a destinatarios no enlaza al panel administrativo. Incluye un botón `Abrir PDF` con URL firmada hacia:

```text
/pqrsf-submissions/{submission}/pdf
```

El correo y el PDF comparten el contenido visual desde:

```text
src/resources/views/emails/partials/pqrsf-summary.blade.php
```

El correo y el PDF no deben mostrar `autorizacion_datos`.

## Importación CSV

El comando de importación maneja exportaciones de Microsoft Forms con filas partidas, comas/punto y coma en texto libre y deduplicación por `field_values['csv_id']`.

Copiar un CSV del host al contenedor:

```bash
docker compose cp "C:\ruta\archivo.csv" app:/tmp/archivo.csv
```

Ejecutar primero en modo revisión:

```bash
docker compose exec app php artisan pqrsf:import-csv /tmp/archivo.csv --dry-run
```

Importar:

```bash
docker compose exec app php artisan pqrsf:import-csv /tmp/archivo.csv
```

## Pruebas Y Notas

- PHPUnit usa SQLite en memoria y `MAIL_MAILER=array`; las pruebas no validan SMTP real.
- Algunas pruebas de PDF de reportes se omiten en SQLite porque requieren funciones JSON de MySQL.
- Las vistas DomPDF deben usar estilos locales/inline y mantener `isRemoteEnabled=false` salvo necesidad explícita.
- `src/README.md` es el README estándar de Laravel; este README documenta el comportamiento real del proyecto.
