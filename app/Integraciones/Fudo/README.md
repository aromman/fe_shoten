# Integración con Fudo

La integración utiliza las credenciales oficiales de la API de Fudo y nunca
almacena secretos dentro del repositorio.

Las credenciales `FUDO_API_KEY` y `FUDO_API_SECRET` se usan solamente durante
la carga inicial desde consola y luego se recuperan cifradas desde MySQL.
La clave maestra se genera automáticamente en
`app/config/secrets.local.php`, archivo excluido de Git.

Variables opcionales:

- `FUDO_API_BASE_URL` (por defecto `https://api.fu.do/v1alpha1`)
- `FUDO_AUTH_URL` (por defecto `https://auth.fu.do/api`)
- `FUDO_TIMEOUT_SECONDS` (por defecto `15`)

El endpoint interno para verificar las ventas mensuales es:

`/api/v1/integraciones/fudo/ventas-mes.php?mes=YYYY-MM`

La consulta recupera ventas cerradas (`CLOSED`) creadas dentro del mes
solicitado y pagina automáticamente en bloques de 500 registros.

## Guardar credenciales en MySQL

En una PowerShell temporal:

```powershell
$env:FUDO_API_KEY="..."
$env:FUDO_API_SECRET="..."
php tools/configurar-fudo-db.php
```

Después se pueden borrar `FUDO_API_KEY` y `FUDO_API_SECRET` de la terminal.
No es necesario modificar Nginx ni PHP-FCGI. El archivo
`app/config/secrets.local.php` debe conservarse y respaldarse de forma segura:
si se pierde, las credenciales deberán cargarse otra vez.

## Prueba desde consola

Con las credenciales configuradas en la terminal:

```powershell
php probar-fudo.php
php probar-fudo.php 2026-06
```

El script no imprime las credenciales ni el token de acceso.

### Avast y el error cURL 60

Si Avast intercepta HTTPS y PHP informa `unable to get local issuer
certificate`, generar un bundle local con:

```powershell
.\configurar-ca-fudo.ps1
```

El bundle combina las autoridades configuradas en PHP con la raíz de Avast,
mantiene activa la validación TLS y queda excluido de Git.

## Dashboard financiero

El BFF del dashboard suma las ventas Fudo a la facturación local y conserva el
desglose de ambas fuentes. También consulta el mes anterior para calcular una
variación comparable. El costo de ventas continúa siendo el registrado en la
base local. Si Fudo no está disponible, el dashboard usa solo datos locales.

## Puesta en producción

Requisitos:

- PHP con `curl`, `openssl` y `pdo_mysql`.
- Acceso de salida HTTPS a `auth.fu.do` y `api.fu.do`.
- Permiso de escritura inicial sobre `app/config` para crear
  `secrets.local.php`.

Después de desplegar el código, ejecutar en el servidor:

```bash
export FUDO_API_KEY='...'
export FUDO_API_SECRET='...'
php tools/configurar-fudo-db.php
unset FUDO_API_KEY FUDO_API_SECRET
php tools/probar-fudo.php
```

El instalador crea la tabla si todavía no existe. El archivo
`app/config/secrets.local.php` no se despliega por Git: se genera una vez en
cada ambiente y debe incluirse en los respaldos seguros del servidor.
