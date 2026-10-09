# Plan de implementacion: app local offline-first con sincronizacion remota

Fecha: 2026-07-22

## Objetivo

Tener una version de la pagina que funcione localmente en una PC, pueda trabajar sin internet y sincronice con una base remota cuando haya conexion disponible.

La aplicacion local debe:

- Levantar en la PC sin depender de internet.
- Usar una base local para operar durante el dia.
- Recuperar novedades de la base remota al inicio del dia o al abrir la app.
- Registrar acciones locales mientras hay o no hay conexion.
- Subir acciones pendientes cuando vuelva la conectividad.
- Informar claramente el estado de sincronizacion.

## Terminos acordados

Para evitar ambiguedades, usaremos estos nombres durante el proyecto:

- **saikono_roi**: el proyecto web PHP actual.
- **app local**: instancia de `saikono_roi` corriendo en una PC de escritorio, por ejemplo con WinNMP. Trabaja contra una base local.
- **app remota**: instancia de `saikono_roi` corriendo en el hosting actual, en `easyshop.com.ar/saiko`. Trabaja directamente contra la base remota/central.
- **base local**: replica operativa de la base usada por la app local para poder vender y operar aun sin internet.
- **base remota**: base central usada directamente por la app remota.
- **concentrador API**: capa/API encargada de coordinar la sincronizacion entre la app local y la base remota. No reemplaza a la app remota; expone endpoints de sincronizacion y aplica reglas centrales.
- **modo sincronico**: la app remota opera directamente contra la base remota y guarda los cambios alli en el momento.
- **modo asincronico**: la app local opera contra la base local, registra acciones en una cola y sincroniza despues cuando haya conexion.

## Configuracion de modo de trabajo

El proyecto incorporara una configuracion para indicar en que modo trabaja cada instalacion.

Valores propuestos:

```text
APP_SYNC_MODE=sync
APP_SYNC_MODE=async
```

Interpretacion:

- `sync`: instalacion remota/sincronica. La aplicacion guarda directamente en la base remota. Es el modo esperado para `easyshop.com.ar/saiko`.
- `async`: instalacion local/offline-first. La aplicacion guarda en la base local, registra acciones en la cola de sincronizacion y usa el concentrador API para enviar/recibir cambios.

Esta configuracion debe ser local a cada entorno y no debe depender del usuario logueado. La misma base de codigo podra correr como app local o app remota segun este valor.

## Arquitectura objetivo

La misma aplicacion `saikono_roi` debe poder ejecutarse en dos contextos:

```text
PC escritorio
app local -> base local -> cola de acciones -> concentrador API -> base remota

Hosting actual
app remota -> base remota
```

El concentrador API se implementara como un proyecto PHP separado, ubicado como carpeta hermana de `saikono_roi`:

```text
C:\WinNMP\WWW\saikono_roi
C:\WinNMP\WWW\saiko_api
```

Esto permite evolucionar la API de sincronizacion sin mezclarla con las pantallas, sesiones y flujo web actual de `saikono_roi`.

En el entorno local inicial, el concentrador API usara una base propia llamada:

```text
saiko_api
```

El area de trabajo local queda representada por el archivo:

```text
C:\WinNMP\WWW\saikono_roi\saiko.code-workspace
```

Ese workspace abre juntas las carpetas `saikono_roi` y `saiko_api`.

La app remota no necesita una replica local ni cola propia para operar: usa directamente la base remota. El concentrador API se encarga de recibir acciones de la app local, validarlas, aplicarlas sobre la base remota y devolver novedades para que la app local mantenga su base actualizada.

La app local debe operar siempre contra la base local. La diferencia esta en como sincroniza:

- si hay conexion estable, guarda localmente, encola la accion y puede sincronizar inmediatamente mediante el concentrador API;
- si no hay conexion, guarda localmente, encola acciones y sincroniza despues.

## Idea central

No conviene hacer una sincronizacion directa base local <-> base remota comparando tablas completas.

El modelo recomendado es:

```text
App local -> base local -> cola de acciones -> API remota -> base central
Servidor remoto -> API de cambios -> app local -> base local
```

La aplicacion siempre trabaja contra la base local. La conexion remota se usa solo para sincronizar.

## Componentes

### PC local

- Aplicacion corriendo en WinNMP/local.
- Base local, idealmente MariaDB local si se quiere mantener compatibilidad con el proyecto actual.
- Tabla de estado de sincronizacion.
- Tabla de cola de acciones locales.
- Proceso de sincronizacion ejecutable al iniciar la app, al inicio del dia, cada X minutos y manualmente.

### Servidor remoto

- Aplicacion/API remota.
- Base central.
- Endpoints de sincronizacion.
- Registro de cambios por entidad.
- Validacion central para operaciones sensibles como ventas, stock, pagos y caja.

## Tablas sugeridas

### sync_state

Guarda el estado general del dispositivo local.

```sql
sync_state
- id
- device_id
- last_pull_at
- last_push_at
- last_success_at
- last_error
- created_at
- updated_at
```

### sync_queue

Guarda acciones locales pendientes de enviar al servidor.

```sql
sync_queue
- id
- device_id
- entity_type
- entity_id
- action
- payload_json
- status
- attempt_count
- created_at
- sent_at
- error_message
```

Estados posibles:

```text
pending
sending
sent
error
conflict
```

### sync_log

Historial legible de sincronizacion.

```sql
sync_log
- id
- direction
- message
- details_json
- created_at
```

Direcciones posibles:

```text
push
pull
system
error
```

### sync_conflicts

Conflictos que requieren revision.

```sql
sync_conflicts
- id
- entity_type
- entity_id
- local_payload_json
- remote_payload_json
- reason
- status
- created_at
- resolved_at
```

## Flujo de sincronizacion

### Al abrir la app o al inicio del dia

```text
1. Verificar conexion con el servidor.
2. Si hay conexion, subir acciones locales pendientes.
3. Descargar cambios remotos desde last_pull_at.
4. Aplicar cambios en la base local.
5. Guardar last_pull_at usando la hora del servidor.
6. Mostrar resultado al usuario.
```

### Durante el uso normal

Cada accion importante debe guardarse localmente y registrar un evento en `sync_queue`.

Ejemplos:

```text
crear venta
editar cliente
cambiar stock
registrar pago
anular reserva
crear compra
recibir compra
```

Si hay conexion, el proceso de sync puede enviarla enseguida.

Si no hay conexion, queda pendiente.

### Cuando vuelve internet

```text
1. Detectar conexion disponible.
2. Subir cola pendiente en orden.
3. Recibir respuesta del servidor para cada accion.
4. Marcar acciones como sent, error o conflict.
5. Descargar cambios remotos nuevos.
6. Actualizar estado visual de la app.
```

## Endpoints remotos sugeridos

```text
POST /sync/push
GET /sync/pull?since=...
POST /sync/ack
GET /sync/status
```

## Primer incremento del concentrador API

El primer alcance del proyecto `saiko_api` es recibir acciones locales sin aplicarlas todavia a las tablas reales.

Endpoints iniciales:

```text
GET /health
GET /docs
GET /openapi/auth.json
GET /openapi/sync.json
GET /openapi/catalogo.json
GET /openapi/pedidos.json
POST /auth/token
GET /catalogo/productos
GET /catalogo/productos/{id}
POST /sync/push
POST /pedidos
GET /pedidos/{id}
```

### Swagger / OpenAPI

El concentrador API publicara documentacion Swagger/OpenAPI desde el propio proyecto.

La documentacion se organizara como catalogo de APIs para permitir mas de una API en el mismo proyecto.

APIs iniciales:

- `auth`: API transversal de seguridad. Emite JWT mediante API Key, Secret Key y firma HMAC.
- `sync`: API de sincronizacion offline-first. Usa el JWT emitido por `auth`.
- `catalogo`: API de consulta de productos. Solo expone operaciones `GET`, por listado y por ID puntual.
- `pedidos`: API para generar pedidos y consultar un pedido por ID sobre la tabla `pedidos`.

Rutas iniciales:

```text
GET /docs
GET /openapi/auth.json
GET /openapi/sync.json
GET /openapi/catalogo.json
GET /openapi/pedidos.json
```

Cuando se agregue otra API, por ejemplo `admin`, se sumara su especificacion al registro y quedara disponible como:

```text
GET /openapi/admin.json
```

### Seguridad del concentrador API

El concentrador API implementara tres conceptos de seguridad:

- **API Key**: identifica al cliente integrador/dispositivo autorizado.
- **Secret Key**: secreto compartido que no viaja en la llamada normal; se usa para firmar la obtencion del token mediante HMAC.
- **JWT**: token temporal que autoriza las llamadas posteriores a la API.

Flujo propuesto:

```text
1. El cliente pide POST /auth/token.
2. Envia API Key, timestamp y firma HMAC.
3. La API valida que la API Key exista y que la firma corresponda a la Secret Key.
4. La API emite un JWT de vida corta.
5. El cliente llama endpoints protegidos con Authorization: Bearer <jwt>.
```

Headers para obtener token:

```text
X-Api-Key: <api_key>
X-Timestamp: <unix_timestamp>
X-Signature: <hmac_sha256>
```

La firma HMAC se calculara inicialmente sobre:

```text
api_key + "." + timestamp
```

Responsabilidades iniciales de `POST /auth/token`:

- Validar que `X-Api-Key` exista.
- Validar ventana de tiempo del `X-Timestamp`.
- Validar `X-Signature` usando HMAC SHA256 con la Secret Key del cliente.
- Emitir JWT firmado por la API.

Para pruebas locales del concentrador API se definio inicialmente:

```text
API Key: test-api-key
Secret Key: test-secret-key
Device ID: pc-caja-01
```

Y un helper CLI para generar `X-Timestamp` y `X-Signature`:

```powershell
php tools/generate-auth-signature.php
```

Responsabilidades iniciales de `POST /sync/push`:

- Validar JWT con `Authorization: Bearer <jwt>`.
- Validar que el `device_id` enviado coincida con el cliente autorizado por el JWT.
- Recibir una lista de acciones.
- Registrar cada accion en `sync_received_actions`.
- Detectar reintentos por `local_action_id` y responder sin duplicar.

La aplicacion real de una accion, por ejemplo `venta/create`, se implementara despues como un procesador especifico por entidad.

### POST /sync/push

Recibe acciones locales pendientes.

Debe ser idempotente: si la PC reintenta una accion, el servidor no debe duplicarla.

Payload conceptual:

```json
{
  "device_id": "pc-caja-01",
  "actions": [
    {
      "local_action_id": "uuid",
      "entity_type": "venta",
      "entity_id": "uuid",
      "action": "create",
      "payload": {}
    }
  ]
}
```

### GET /sync/pull

Devuelve cambios remotos desde la ultima sincronizacion.

Respuesta conceptual:

```json
{
  "server_time": "2026-07-22T09:30:00",
  "changes": [
    {
      "entity_type": "producto",
      "entity_id": "15",
      "operation": "updated",
      "data": {}
    }
  ]
}
```

## Reglas de conflicto

Hay que definirlas por modulo.

Recomendacion inicial:

- Clientes/proveedores: puede ganar el cambio mas reciente o marcar conflicto.
- Productos/precios: servidor tiene prioridad.
- Stock: servidor valida y puede rechazar movimientos imposibles.
- Ventas: se aceptan con idempotencia; el servidor valida stock, caja y duplicados.
- Pagos/caja: servidor tiene la ultima palabra.
- Reservas: si hay choque de disponibilidad, marcar conflicto.

## UI necesaria

Agregar un indicador visible de estado:

```text
Online - sincronizado 08:31
Offline - 12 acciones pendientes
Sincronizando - enviando ventas...
Error - revisar 2 conflictos
```

Tambien conviene tener una pantalla o modal de sincronizacion con:

- Ultima sincronizacion exitosa.
- Acciones pendientes.
- Errores.
- Conflictos.
- Boton para sincronizar manualmente.

## Orden de implementacion recomendado

1. Relevar acciones criticas del sistema: ventas, stock, pagos, caja, reservas, compras.
2. Crear `device_id` local y tabla `sync_state`.
3. Crear `sync_queue`, `sync_log` y `sync_conflicts`.
4. Hacer que las acciones criticas registren eventos en `sync_queue`.
5. Crear API remota `POST /sync/push`.
6. Crear API remota `GET /sync/pull`.
7. Crear script/proceso local `sync.php`.
8. Ejecutar sync al abrir la app y permitir sync manual.
9. Agregar indicador visual de estado.
10. Agregar manejo de conflictos por modulo.
11. Probar escenarios offline, reconexion, doble envio y conflictos.

## Decision tecnica inicial

Para este proyecto, la opcion mas natural parece:

```text
Local: WinNMP + PHP + MariaDB local
Remoto: PHP/API + MariaDB central
Sync: endpoints HTTP + tablas de cola y estado
```

El punto mas importante a resolver antes de codificar es definir que entidades pueden modificarse offline y que reglas aplica el servidor cuando recibe acciones atrasadas.
