<?php

namespace App\Turnos\Infrastructure;

use App\Turnos\Application\TurnoReadRepository;

final class LegacyTurnoReadRepository implements TurnoReadRepository
{
    public function obtenerContextoActual($canalId)
    {
        $canalModel = new \Canal();
        $turnosModel = new \Turnos();
        $turnosParcialModel = new \TurnoParcial();
        $ventasModel = new \Ventas();
        $gastosModel = new \Gastos();
        $comprasModel = new \Compra();
        $cuentasModel = new \CuentasMovimientos();

        $canal = $canalModel->getById($canalId);
        $turno = $turnosModel->getActiveByCanal($canalId);
        $fechaCaja = $turno ? $turno['fecha'] : date('Y-m-d');

        $ventas = $ventasModel->getTotalByCanalAndDate($canalId, $fechaCaja);
        $gastos = $gastosModel->getTotalByCanalAndDate($canalId, $fechaCaja);
        $compras = $comprasModel->getTotalByDate($fechaCaja);
        $depositos = $cuentasModel->getTotalByCanalDateAndType($canalId, $fechaCaja, 'C');
        $extracciones = $cuentasModel->getTotalByCanalDateAndType($canalId, $fechaCaja, 'D');

        return array(
            'canal' => array(
                'id' => (int) $canalId,
                'nombre' => $canal && isset($canal['nombre']) ? $canal['nombre'] : '',
            ),
            'turno' => $this->mapearTurno($turno),
            'billetes' => $turno
                ? $this->obtenerBilletes($turnosModel->pdo, (int) $turno['id'])
                : array(),
            'montos' => array(
                'apertura' => $turno ? $turno['monto_apertura'] : 0,
                'ventas' => $this->extraerMonto($ventas),
                'depositos' => $this->extraerMonto($depositos),
                'compras' => $this->extraerMonto($compras),
                'gastos' => $this->extraerMonto($gastos),
                'extracciones' => $this->extraerMonto($extracciones),
            ),
            'movimientos' => $turno
                ? $this->prepararMovimientos(
                    $turnosModel->getDetailsActive($fechaCaja, $canalId),
                    $turno['monto_apertura']
                )
                : array(),
            'arqueos' => $turno
                ? $this->obtenerUltimosArqueos($turnosParcialModel->pdo, $canalId, $turno['fecha'])
                : array(),
            'advertencias' => array(
                'Las compras conservan temporalmente el cálculo legado sin filtro por canal.',
            ),
        );
    }

    private function mapearTurno($turno)
    {
        if (!$turno) {
            return null;
        }

        return array(
            'id' => (int) $turno['id'],
            'fecha' => $turno['fecha'],
            'estado' => $turno['estado'],
            'montoApertura' => round((float) $turno['monto_apertura'], 2),
            'montoCierre' => round((float) $turno['monto_cierre'], 2),
            'usuario' => isset($turno['username']) ? $turno['username'] : null,
            'actualizadoEn' => isset($turno['updateDate']) ? $turno['updateDate'] : null,
        );
    }

    private function extraerMonto($resultado)
    {
        return $resultado && isset($resultado['monto'])
            ? round((float) $resultado['monto'], 2)
            : 0.0;
    }

    private function normalizarLista($resultado)
    {
        return is_array($resultado) ? array_values($resultado) : array();
    }

    private function prepararMovimientos($resultado, $montoApertura)
    {
        $movimientos = $this->normalizarLista($resultado);
        $fechaActual = new \DateTimeImmutable(
            'now',
            new \DateTimeZone('America/Argentina/Buenos_Aires')
        );
        $hoy = $fechaActual->format('Y-m-d');
        $ayer = $fechaActual->modify('-1 day')->format('Y-m-d');

        usort($movimientos, function ($a, $b) {
            $comparacionFecha = strcmp($a['fecha'], $b['fecha']);
            if ($comparacionFecha !== 0) {
                return $comparacionFecha;
            }

            return strcmp($a['detalle'], $b['detalle']);
        });

        $saldo = round((float) $montoApertura, 2);
        $movimientosHoy = array();
        $saldoInicialDia = $saldo;

        foreach ($movimientos as &$movimiento) {
            $monto = round((float) $movimiento['monto'], 2);
            $saldo += $movimiento['tipo'] === 'I' ? $monto : -$monto;
            $movimiento['monto'] = $monto;
            $movimiento['saldo'] = round($saldo, 2);

            if (substr($movimiento['fecha'], 0, 10) === $hoy) {
                $movimientosHoy[] = $movimiento;
            } elseif (substr($movimiento['fecha'], 0, 10) < $hoy) {
                $saldoInicialDia = $saldo;
            }
        }
        unset($movimiento);

        $movimientosHoy = array_reverse($movimientosHoy);
        $movimientosHoy[] = array(
            'fecha' => $ayer,
            'detalle' => 'Saldo inicial del día',
            'tipo' => 'S',
            'monto' => round($saldoInicialDia, 2),
            'saldo' => round($saldoInicialDia, 2),
        );

        return $movimientosHoy;
    }

    private function obtenerBilletes($pdo, $turnoId)
    {
        $statement = $pdo->prepare(
            'SELECT denominacion, cantidad
             FROM turnos_billetes
             WHERE turno_id = ?
             ORDER BY denominacion ASC'
        );
        $statement->execute(array($turnoId));

        return array_map(function ($fila) {
            $denominacion = (int) $fila['denominacion'];
            $cantidad = (int) $fila['cantidad'];

            return array(
                'denominacion' => $denominacion,
                'cantidad' => $cantidad,
                'subtotal' => $denominacion * $cantidad,
            );
        }, $statement->fetchAll(\PDO::FETCH_ASSOC));
    }

    private function obtenerUltimosArqueos($pdo, $canalId, $fechaTurno)
    {
        $statement = $pdo->prepare(
            'SELECT *
             FROM turnos_parcial
             WHERE id_canal = ? AND fecha = ?
             ORDER BY updateDate DESC
             LIMIT 5'
        );
        $statement->execute(array($canalId, $fechaTurno));

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
