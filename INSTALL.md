# Instalación de RifaGrid

## XAMPP local

1. Copia el proyecto en `C:\xampp\htdocs\rifa`.
2. Crea una base de datos MySQL llamada `rifagrid`.
3. Importa `database/schema.sql`.
4. Importa `database/seed.sql`.
5. Copia `config/config.example.php` como `config/config.php`.
6. Ajusta:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
   - `APP_URL`
   - `ADMIN_WHATSAPP`
   - `YAPPY_NUMBER`
   - `PAYPAL_LINK`
   - `BANK_INFO`
7. Abre `http://localhost/rifa/public/index.php`.
8. Entra al admin en `http://localhost/rifa/admin/login.php`.

## Crear la primera rifa

1. Entra al admin.
2. Ve a `Rifas`.
3. Pulsa `Nueva rifa`.
4. Define premios, precio, fecha, rango `00-100`, Yappy y WhatsApp.
5. Sube el flyer.
6. Guarda con estado `active`.
7. Abre la URL pública desde `Ver`.

## Probar una reserva

1. Abre una rifa pública.
2. Toca uno o varios números disponibles.
3. Llena nombre y WhatsApp.
4. Pulsa `Reservar números`.
5. Usa el botón de WhatsApp generado para enviar el comprobante manualmente.
6. Opcionalmente sube el comprobante JPG, PNG o PDF desde el formulario que aparece después de reservar.

## Confirmar pago

1. Entra al admin.
2. Ve a `Comprobantes` para revisar archivos recibidos o a `Reservas` para ver pendientes.
3. Pulsa `Confirmar pago`.
4. El sistema cambia la reserva a `paid`, los números a `sold`, aprueba comprobantes pendientes y abre WhatsApp con el mensaje de confirmación al cliente.

## Cancelar reserva

1. Ve a `Reservas`.
2. En una reserva pendiente pulsa `Cancelar`.
3. El sistema marca la reserva como cancelada y libera los números para venta.

## Notificaciones internas

1. Mantén abierto el panel admin.
2. Cuando entra una reserva o comprobante, el contador se actualiza automáticamente.
3. El sistema muestra un popup y reproduce un sonido corto si el navegador lo permite.
4. Entra a `Notificaciones` para revisar el historial y marcar elementos como leídos.

## PWA instalable

1. Abre la web en Chrome, Edge o navegador compatible.
2. Pulsa `Instalar app` en la página pública o en el panel admin.
3. La app queda en modo standalone cuando el navegador lo permite.
4. Si no hay internet, el service worker muestra `offline.html` como pantalla de respaldo.

## Migración Fase 4

Si ya habías importado la base de datos antes de Fase 4, importa también:

`database/phase4_push_subscriptions.sql`

Esto crea la tabla `push_subscriptions` para futuras notificaciones Web Push.

## Web Push futuro

La estructura está lista, pero el envío real no se activa todavía. Para preparar claves:

1. Genera claves VAPID con la librería o servicio que decidas usar en la fase futura.
2. Configura en `config/config.php`:
   - `WEB_PUSH_PUBLIC_KEY`
   - `WEB_PUSH_PRIVATE_KEY`
   - `WEB_PUSH_SUBJECT`
3. Usa el botón `Alertas push` en admin para guardar la suscripción del dispositivo.

## Publicar ganadores

1. Confirma primero los pagos de los participantes.
2. Ve a `Ganadores`.
3. Selecciona la rifa.
4. Pulsa `Verificar en LNB` para abrir la página oficial.
5. Escribe los premios completos, por ejemplo `4924`, `1823`, `3400`.
6. Pulsa `Marcar ganadores`.
7. El sistema toma los últimos 2 dígitos, busca números vendidos y publica ganadores.
8. Usa el botón `WhatsApp` para enviar el mensaje con código secreto al ganador.

## Puntos y número gratis

1. Al confirmar un pago, el sistema suma puntos según la configuración de la rifa.
2. Ve a `Puntos`.
3. Selecciona cliente, rifa activa y número disponible.
4. Pulsa `Aplicar número gratis`.
5. El sistema descuenta los puntos requeridos, crea una reserva pagada en B/.0.00 y marca el número como vendido.

## Reportes y CSV

1. Ve a `Reportes`.
2. Filtra por rifa o revisa todas.
3. Revisa total confirmado, pendiente, costo, utilidad estimada, métodos de pago y números comprados.
4. Usa `Exportar reservas CSV` o `Exportar participantes CSV`.

## Migración Fase 7

Si tu base fue creada antes de Fase 7, importa:

`database/phase7_whatsapp.sql`

Esto crea:

- `whatsapp_configs`
- `whatsapp_messages`

## WhatsApp API / bot futuro

La primera versión sigue usando enlaces `wa.me`. No necesitas API paga.

Para preparar una integración futura:

1. Ve a `Settings`.
2. En `WhatsApp API / bot futuro`, elige proveedor.
3. Completa teléfono, Phone ID, token y verify token si ya los tienes.
4. Activa la configuración para verificación de webhook.
5. Usa la URL mostrada como webhook público.

El webhook actual:

- Verifica `hub.challenge` para WhatsApp Cloud API.
- Registra mensajes entrantes.
- Detecta intents básicos: reserva, comprobante, disponibilidad, ganador o mensaje general.
- Crea notificación interna para el admin.

Todavía no envía respuestas automáticas ni reserva números por bot; queda preparado para hacerlo sin afectar el flujo manual.

## Migración Diseño UI/UX

Si tu base fue creada antes del ajuste de diseño configurable, importa:

`database/phase8_design_settings.sql`

Esto agrega a `raffles`:

- `background_color`
- `grid_style`

Las rifas nuevas usan por defecto tema limpio celeste, tarjetas blancas y botones azul/celeste. En cada rifa puedes cambiar tema, colores, fondo, flyer y estilo de grilla desde el admin.

## Subir a cPanel

1. Sube las carpetas al hosting.
2. Crea base de datos y usuario desde cPanel.
3. Importa `schema.sql` y `seed.sql` con phpMyAdmin.
4. Crea `config/config.php` a partir del ejemplo.
5. Ajusta `APP_URL` al dominio público, por ejemplo `https://dominio.com/public`.
6. Asegura permisos de escritura en:
   - `public/assets/uploads/flyers`
   - `public/assets/uploads/comprobantes`
   - `storage/logs`

## Próxima fase

Proyecto base completo por fases. Siguientes mejoras recomendadas: hardening final para producción, pruebas E2E y personalización visual avanzada por marca.
