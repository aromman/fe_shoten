# Módulo Turnos

Primer corte de la migración progresiva de Gestión de Caja.

## Responsabilidades

- `Domain`: reglas puras de cálculo, sin acceso a sesión ni base de datos.
- `Application`: casos de uso y contratos de repositorio.
- `Infrastructure`: adaptación temporal a los modelos del monolito.
- `api/v1/turnos`: contrato de dominio para consumidores internos.
- `bff/turno`: respuesta preparada para el frontend de Gestión de Caja.
- `turno/turno.php`: frontend actual de Gestión de Caja.
- `turno/turno-old.php`: versión anterior conservada temporalmente.

La API y el BFF comparten por ahora el caso de uso dentro del mismo proceso PHP.
Esto mantiene un monolito modular; el contrato HTTP permite separarlos más
adelante sin trasladar reglas al frontend.

## Endpoints

- `GET /api/v1/turnos/actual.php`: representación del turno y su resumen.
- `GET /bff/turno/actual.php`: tarjetas, acciones disponibles y movimientos
  preparados para la pantalla.
- `POST /bff/turno/abrir.php`: abre un turno para el canal de la sesión.
  Requiere un usuario con rol administrador (`user.rol = 0`).
- `POST /bff/turno/arqueo.php`: registra un cierre parcial y reemplaza el
  inventario actual de billetes del turno abierto.
- `POST /bff/turno/cerrar.php`: registra el arqueo final, actualiza el
  inventario de billetes y cierra el turno. Requiere rol administrador.
- `POST /bff/turno/movimiento.php`: registra un ingreso o egreso manual en la
  cuenta de efectivo del canal y lo incorpora al turno abierto.

El conteo físico de apertura se persiste en `turnos_billetes`, vinculado al
turno creado. La migración está en
`db/migrations/20260624_turnos_billetes.sql`.

Los comandos de escritura requieren el encabezado `Idempotency-Key`. La clave,
el tipo de comando y su respuesta se guardan transaccionalmente en
`turnos_comandos`; una repetición devuelve la respuesta original. La migración
está en `db/migrations/20260624_turnos_comandos.sql`.

Ambos endpoints toman usuario, rol y canal de la sesión. El cliente no puede
elegir un canal arbitrario.

## Pruebas

```powershell
php tests\Turnos\SaldoTurnoTest.php
php tests\Turnos\ConteoBilletesTest.php
php tests\Turnos\AbrirTurnoTest.php
php tests\Turnos\RegistrarArqueoTest.php
php tests\Turnos\CerrarTurnoTest.php
php tests\Turnos\RegistrarMovimientoTest.php
php tests\Turnos\IdempotenciaIntegrationTest.php
php tests\Turnos\TurnoActualIntegrationTest.php 1
```

La prueba de integración requiere MySQL activo y un canal existente.

## Compatibilidad temporal

El cálculo de compras conserva la consulta actual sin filtro por canal para
que los totales del piloto sean comparables con la pantalla legada. Debe
corregirse mediante una decisión funcional y una prueba de datos antes de
retirar el flujo anterior.

## Próximo corte

1. Incorporar pruebas HTTP automatizadas para permisos, CSRF e idempotencia.
2. Ejecutar cada comando dentro de una transacción.
3. Incorporar token CSRF e idempotencia en el BFF.
4. Validar montos y fecha exclusivamente en el servidor.
5. Activar comandos por canal mediante una bandera de funcionalidad.
6. Retirar las escrituras de `abrirModal.php`, `cerrarModal.php` y
   `action-turno.php` cuando el piloto sea estable.
