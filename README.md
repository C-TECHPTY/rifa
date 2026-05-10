# RifaGrid / GIBEL Rifas

WebApp/PWA en PHP 8+, MySQL, HTML, CSS y JavaScript vanilla para crear rifas online, publicar una grilla interactiva y reservar números con pago manual por Yappy/WhatsApp.

## Estado

Sistema base implementado con:

- Configuración base sin credenciales reales.
- Login admin con `password_hash`.
- CRUD de rifas.
- Grilla configurable del 00 al 99, 00 al 100 o rango personalizado.
- Página pública responsive.
- Reserva transaccional de números.
- Pagos manuales y comprobantes.
- Confirmación/cancelación admin.
- Notificaciones internas.
- PWA instalable preparada.
- Ganadores por modo manual.
- Transparencia pública con iniciales.
- Puntos de fidelidad y reportes CSV.
- Preparación WhatsApp API / bot futuro.
- Diseño configurable por rifa con flyer completo y grilla interactiva.

El sistema sigue funcionando en modo manual con enlaces `wa.me` aunque no configures ninguna API externa.

## Accesos demo

- URL admin local: `http://localhost/rifa/admin/login.php`
- Usuario: `admin@rifagrid.local`
- Contraseña: `Admin123!`

## Carpetas

- `public/`: sitio público, PWA, assets y uploads.
- `admin/`: panel administrativo.
- `api/`: endpoints AJAX y futuras integraciones.
- `config/`: configuración y conexión PDO.
- `database/`: schema, seeds y migraciones.
- `storage/logs/`: espacio para logs operativos.

## Seguridad

No subas archivos con credenciales reales. Usa `config/config.example.php` o `config/config.hosting.createcpty.example.php` como plantilla y crea `config/config.php` solo en el servidor.
