<?php

namespace App\Turnos\Infrastructure;

use App\Turnos\Application\TurnoWriteRepository;

final class LegacyTurnoWriteRepository implements TurnoWriteRepository
{
    public function obtenerResultadoIdempotente($idempotencyKey, $tipo, $canalId, $username)
    {
        $turnosModel = new \Turnos();
        $statement = $turnosModel->pdo->prepare(
            'SELECT tipo, canal_id, username, estado, respuesta_json
             FROM turnos_comandos
             WHERE idempotency_key = ?'
        );
        $statement->execute(array($idempotencyKey));
        $comando = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$comando) {
            return null;
        }

        if ($comando['tipo'] !== $tipo
            || (int) $comando['canal_id'] !== (int) $canalId
            || $comando['username'] !== $username
        ) {
            throw new \DomainException('IDEMPOTENCY_KEY_REUTILIZADA');
        }

        if ($comando['estado'] !== 'COMPLETADO' || $comando['respuesta_json'] === null) {
            return null;
        }

        $resultado = json_decode($comando['respuesta_json'], true);
        if (!is_array($resultado)) {
            throw new \RuntimeException('RESPUESTA_IDEMPOTENTE_INVALIDA');
        }

        return $resultado;
    }

    public function abrir($canalId, $fecha, $montoApertura, $billetes, $username, $idempotencyKey)
    {
        $turnosModel = new \Turnos();
        $pdo = $turnosModel->pdo;

        try {
            $pdo->beginTransaction();

            $resultadoAnterior = $this->iniciarComando(
                $pdo,
                $idempotencyKey,
                'ABRIR_TURNO',
                $canalId,
                $username
            );
            if ($resultadoAnterior !== null) {
                $pdo->commit();
                return $resultadoAnterior;
            }

            // Serializa aperturas simultáneas para un mismo canal.
            $canalStatement = $pdo->prepare('SELECT id FROM canal WHERE id = ? FOR UPDATE');
            $canalStatement->execute(array($canalId));
            if (!$canalStatement->fetchColumn()) {
                throw new \RuntimeException('CANAL_NO_ENCONTRADO');
            }

            $activoStatement = $pdo->prepare(
                "SELECT id FROM turnos WHERE estado = 'A' AND id_canal = ? LIMIT 1"
            );
            $activoStatement->execute(array($canalId));
            if ($activoStatement->fetchColumn()) {
                throw new \DomainException('TURNO_YA_ABIERTO');
            }

            $insertStatement = $pdo->prepare(
                'INSERT INTO turnos
                    (fecha, monto_apertura, monto_cierre, estado, id_canal, username, updateDate)
                 VALUES (?, ?, 0, ?, ?, ?, ?)'
            );
            $insertStatement->execute(array(
                $fecha,
                $montoApertura,
                'A',
                $canalId,
                $username,
                date('Y-m-d H:i:s'),
            ));

            $turnoId = (int) $pdo->lastInsertId();

            $billeteStatement = $pdo->prepare(
                'INSERT INTO turnos_billetes (turno_id, denominacion, cantidad) VALUES (?, ?, ?)'
            );
            foreach ($billetes as $denominacion => $cantidad) {
                $billeteStatement->execute(array($turnoId, (int) $denominacion, (int) $cantidad));
            }

            $resultado = array(
                'id' => $turnoId,
                'fecha' => $fecha,
                'estado' => 'A',
                'montoApertura' => $montoApertura,
                'billetes' => $billetes,
            );
            $this->completarComando($pdo, $idempotencyKey, $resultado);
            $pdo->commit();

            return $resultado;
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $error;
        }
    }

    public function registrarArqueo(
        $turnoId,
        $canalId,
        $fecha,
        $montoEsperado,
        $montoContado,
        $billetes,
        $username,
        $idempotencyKey
    ) {
        $turnosModel = new \Turnos();
        $pdo = $turnosModel->pdo;

        try {
            $pdo->beginTransaction();

            $resultadoAnterior = $this->iniciarComando(
                $pdo,
                $idempotencyKey,
                'REGISTRAR_ARQUEO',
                $canalId,
                $username
            );
            if ($resultadoAnterior !== null) {
                $pdo->commit();
                return $resultadoAnterior;
            }

            $turnoStatement = $pdo->prepare(
                "SELECT id, fecha
                 FROM turnos
                 WHERE id = ? AND id_canal = ? AND estado = 'A'
                 FOR UPDATE"
            );
            $turnoStatement->execute(array($turnoId, $canalId));
            $turno = $turnoStatement->fetch(\PDO::FETCH_ASSOC);
            if (!$turno) {
                throw new \DomainException('TURNO_NO_ABIERTO');
            }

            $columnasBilletes = array();
            $cantidadesBilletes = array();
            foreach ($billetes as $denominacion => $cantidad) {
                $columnasBilletes[] = 'monto' . (int) $denominacion;
                $cantidadesBilletes[] = (int) $cantidad;
            }

            $columnas = array_merge(
                array('fecha', 'monto_apertura', 'monto_cierre', 'id_canal', 'username', 'updateDate'),
                $columnasBilletes
            );
            $valores = array_merge(
                array(
                    $turno['fecha'],
                    $montoEsperado,
                    $montoContado,
                    $canalId,
                    $username,
                    date('Y-m-d H:i:s'),
                ),
                $cantidadesBilletes
            );
            $marcadores = implode(',', array_fill(0, count($columnas), '?'));

            $arqueoStatement = $pdo->prepare(
                'INSERT INTO turnos_parcial (' . implode(',', $columnas) . ') VALUES (' . $marcadores . ')'
            );
            $arqueoStatement->execute($valores);
            $arqueoId = (int) $pdo->lastInsertId();

            $pdo->prepare('DELETE FROM turnos_billetes WHERE turno_id = ?')->execute(array($turnoId));
            $billeteStatement = $pdo->prepare(
                'INSERT INTO turnos_billetes (turno_id, denominacion, cantidad) VALUES (?, ?, ?)'
            );
            foreach ($billetes as $denominacion => $cantidad) {
                $billeteStatement->execute(array($turnoId, (int) $denominacion, (int) $cantidad));
            }

            $resultado = array(
                'id' => $arqueoId,
                'turnoId' => (int) $turnoId,
                'fecha' => $turno['fecha'],
                'montoEsperado' => round((float) $montoEsperado, 2),
                'montoContado' => round((float) $montoContado, 2),
                'diferencia' => round((float) $montoContado - (float) $montoEsperado, 2),
                'billetes' => $billetes,
            );
            $this->completarComando($pdo, $idempotencyKey, $resultado);
            $pdo->commit();

            return $resultado;
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $error;
        }
    }

    public function cerrar(
        $turnoId,
        $canalId,
        $fecha,
        $montoEsperado,
        $montoContado,
        $billetes,
        $username,
        $idempotencyKey
    ) {
        $turnosModel = new \Turnos();
        $pdo = $turnosModel->pdo;

        try {
            $pdo->beginTransaction();

            $resultadoAnterior = $this->iniciarComando(
                $pdo,
                $idempotencyKey,
                'CERRAR_TURNO',
                $canalId,
                $username
            );
            if ($resultadoAnterior !== null) {
                $pdo->commit();
                return $resultadoAnterior;
            }

            $turnoStatement = $pdo->prepare(
                "SELECT id, fecha
                 FROM turnos
                 WHERE id = ? AND id_canal = ? AND estado = 'A'
                 FOR UPDATE"
            );
            $turnoStatement->execute(array($turnoId, $canalId));
            $turno = $turnoStatement->fetch(\PDO::FETCH_ASSOC);
            if (!$turno) {
                throw new \DomainException('TURNO_NO_ABIERTO');
            }

            $columnasBilletes = array();
            $cantidadesBilletes = array();
            foreach ($billetes as $denominacion => $cantidad) {
                $columnasBilletes[] = 'monto' . (int) $denominacion;
                $cantidadesBilletes[] = (int) $cantidad;
            }

            $columnas = array_merge(
                array('fecha', 'monto_apertura', 'monto_cierre', 'id_canal', 'username', 'updateDate'),
                $columnasBilletes
            );
            $valores = array_merge(
                array(
                    $turno['fecha'],
                    $montoEsperado,
                    $montoContado,
                    $canalId,
                    $username,
                    date('Y-m-d H:i:s'),
                ),
                $cantidadesBilletes
            );
            $marcadores = implode(',', array_fill(0, count($columnas), '?'));

            $arqueoStatement = $pdo->prepare(
                'INSERT INTO turnos_parcial (' . implode(',', $columnas) . ') VALUES (' . $marcadores . ')'
            );
            $arqueoStatement->execute($valores);
            $arqueoId = (int) $pdo->lastInsertId();

            $pdo->prepare('DELETE FROM turnos_billetes WHERE turno_id = ?')->execute(array($turnoId));
            $billeteStatement = $pdo->prepare(
                'INSERT INTO turnos_billetes (turno_id, denominacion, cantidad) VALUES (?, ?, ?)'
            );
            foreach ($billetes as $denominacion => $cantidad) {
                $billeteStatement->execute(array($turnoId, (int) $denominacion, (int) $cantidad));
            }

            $cerrarStatement = $pdo->prepare(
                "UPDATE turnos
                 SET monto_cierre = ?, estado = 'C', username = ?, updateDate = ?
                 WHERE id = ? AND id_canal = ? AND estado = 'A'"
            );
            $cerrarStatement->execute(array(
                $montoContado,
                $username,
                date('Y-m-d H:i:s'),
                $turnoId,
                $canalId,
            ));
            if ($cerrarStatement->rowCount() !== 1) {
                throw new \DomainException('TURNO_NO_ABIERTO');
            }

            $resultado = array(
                'id' => (int) $turnoId,
                'arqueoId' => $arqueoId,
                'fecha' => $turno['fecha'],
                'estado' => 'C',
                'montoEsperado' => round((float) $montoEsperado, 2),
                'montoCierre' => round((float) $montoContado, 2),
                'diferencia' => round((float) $montoContado - (float) $montoEsperado, 2),
                'billetes' => $billetes,
            );
            $this->completarComando($pdo, $idempotencyKey, $resultado);
            $pdo->commit();

            return $resultado;
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $error;
        }
    }

    public function registrarMovimiento(
        $turnoId,
        $canalId,
        $fecha,
        $tipo,
        $detalle,
        $monto,
        $username,
        $idempotencyKey
    ) {
        $turnosModel = new \Turnos();
        $pdo = $turnosModel->pdo;

        try {
            $pdo->beginTransaction();

            $resultadoAnterior = $this->iniciarComando(
                $pdo,
                $idempotencyKey,
                'REGISTRAR_MOVIMIENTO',
                $canalId,
                $username
            );
            if ($resultadoAnterior !== null) {
                $pdo->commit();
                return $resultadoAnterior;
            }

            $turnoStatement = $pdo->prepare(
                "SELECT id, fecha
                 FROM turnos
                 WHERE id = ? AND id_canal = ? AND estado = 'A'
                 FOR UPDATE"
            );
            $turnoStatement->execute(array($turnoId, $canalId));
            if (!$turnoStatement->fetch(\PDO::FETCH_ASSOC)) {
                throw new \DomainException('TURNO_NO_ABIERTO');
            }

            $movimientoStatement = $pdo->prepare(
                'INSERT INTO cuentas_movimientos
                    (id_cuenta, id_canal, fecha, tipo_movimiento, descripcion, monto, consolidado, id_causal)
                 VALUES (1, ?, ?, ?, ?, ?, 0, 3)'
            );
            $movimientoStatement->execute(array($canalId, $fecha, $tipo, $detalle, $monto));
            $movimientoId = (int) $pdo->lastInsertId();

            $resultado = array(
                'id' => $movimientoId,
                'turnoId' => (int) $turnoId,
                'fecha' => $fecha,
                'tipo' => $tipo,
                'detalle' => $detalle,
                'monto' => $monto,
            );
            $this->completarComando($pdo, $idempotencyKey, $resultado);
            $pdo->commit();

            return $resultado;
        } catch (\Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $error;
        }
    }

    private function iniciarComando($pdo, $idempotencyKey, $tipo, $canalId, $username)
    {
        try {
            $statement = $pdo->prepare(
                "INSERT INTO turnos_comandos
                    (idempotency_key, tipo, canal_id, username, estado, created_at)
                 VALUES (?, ?, ?, ?, 'PENDIENTE', ?)"
            );
            $statement->execute(array(
                $idempotencyKey,
                $tipo,
                $canalId,
                $username,
                date('Y-m-d H:i:s'),
            ));

            return null;
        } catch (\PDOException $error) {
            if ($error->getCode() !== '23000') {
                throw $error;
            }
        }

        $statement = $pdo->prepare(
            'SELECT tipo, canal_id, username, estado, respuesta_json
             FROM turnos_comandos
             WHERE idempotency_key = ?
             FOR UPDATE'
        );
        $statement->execute(array($idempotencyKey));
        $comando = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$comando
            || $comando['tipo'] !== $tipo
            || (int) $comando['canal_id'] !== (int) $canalId
            || $comando['username'] !== $username
        ) {
            throw new \DomainException('IDEMPOTENCY_KEY_REUTILIZADA');
        }

        if ($comando['estado'] !== 'COMPLETADO' || $comando['respuesta_json'] === null) {
            throw new \RuntimeException('COMANDO_IDEMPOTENTE_PENDIENTE');
        }

        $resultado = json_decode($comando['respuesta_json'], true);
        if (!is_array($resultado)) {
            throw new \RuntimeException('RESPUESTA_IDEMPOTENTE_INVALIDA');
        }

        return $resultado;
    }

    private function completarComando($pdo, $idempotencyKey, $resultado)
    {
        $statement = $pdo->prepare(
            "UPDATE turnos_comandos
             SET estado = 'COMPLETADO', respuesta_json = ?, completed_at = ?
             WHERE idempotency_key = ?"
        );
        $statement->execute(array(
            json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            date('Y-m-d H:i:s'),
            $idempotencyKey,
        ));
    }
}
