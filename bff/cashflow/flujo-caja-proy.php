<?php
require_once dirname(__DIR__, 2) . '/app/models/cashflow.php';

function obtenerFlujoCajaProyectadoViewModel()
{
    $cashflowPDO = new Cashflow();
    $horizonteMeses = 3;
    $zonaLocal = new DateTimeZone('America/Argentina/Buenos_Aires');
    $inicio = new DateTimeImmutable('first day of this month', $zonaLocal);
    $desde = new DateTimeImmutable('today', $zonaLocal);
    $hasta = $inicio->modify('+' . $horizonteMeses . ' months');
    $nombreMes = array(1=>"ENE",2=>"FEB",3=>"MAR",4=>"ABR",5=>"MAY",6=>"JUN",7=>"JUL",8=>"AGO",9=>"SEP",10=>"OCT",11=>"NOV",12=>"DIC");

    $periodosMensuales = array();
    for ($i = 0; $i < $horizonteMeses; $i++) {
        $fechaMes = $inicio->modify('+' . $i . ' months');
        $clave = $fechaMes->format('Y-m');
        $finMes = $fechaMes->modify('+1 month');
        $periodosMensuales[$clave] = array(
            'clave' => $clave,
            'label' => $nombreMes[(int) $fechaMes->format('n')] . ' ' . $fechaMes->format('Y'),
            'inicio' => $fechaMes,
            'fin' => $finMes,
        );
    }

    $mesActualClave = $inicio->format('Y-m');
    $domingosMesActual = array();
    $cursorDomingo = $inicio;
    $finMesActual = $inicio->modify('+1 month');
    while ($cursorDomingo < $finMesActual) {
        if ($cursorDomingo->format('w') === '0') {
            $domingosMesActual[] = $cursorDomingo;
        }
        $cursorDomingo = $cursorDomingo->modify('+1 day');
    }
    $ultimoDiaMesActual = $finMesActual->modify('-1 day');
    if ($ultimoDiaMesActual->format('w') !== '0') {
        $domingosMesActual[] = $ultimoDiaMesActual;
    }
    $cantidadSubcolumnasMesActual = max(1, count($domingosMesActual));

    $periodos = array();
    foreach ($domingosMesActual as $indiceDomingo => $domingo) {
        $clave = $mesActualClave . '-S' . ($indiceDomingo + 1);
        $periodos[$clave] = array(
            'clave' => $clave,
            'label' => $domingo->format('d/m'),
            'mes' => $mesActualClave,
            'semana' => $indiceDomingo + 1,
            'domingo' => $domingo->format('Y-m-d'),
        );
    }
    for ($i = 1; $i < $horizonteMeses; $i++) {
        $fechaMes = $inicio->modify('+' . $i . ' months');
        $clave = $fechaMes->format('Y-m');
        $periodos[$clave] = array(
            'clave' => $clave,
            'label' => $nombreMes[(int) $fechaMes->format('n')] . ' ' . $fechaMes->format('Y'),
            'mes' => $clave,
            'semana' => null,
        );
    }

    $crearSerieMensual = function () use ($periodosMensuales) {
        $serie = array();
        foreach ($periodosMensuales as $clave => $periodo) {
            $serie[$clave] = 0.0;
        }
        return $serie;
    };
    $crearSerie = function () use ($periodos) {
        $serie = array();
        foreach ($periodos as $clave => $periodo) {
            $serie[$clave] = 0.0;
        }
        return $serie;
    };
    $claveMensualPorFecha = function ($fecha) use ($periodosMensuales) {
        foreach ($periodosMensuales as $clave => $periodo) {
            if ($fecha >= $periodo['inicio'] && $fecha < $periodo['fin']) {
                return $clave;
            }
        }
        return null;
    };
    $expandirSerieSemanal = function ($serieMensual) use ($periodos, $mesActualClave, $cantidadSubcolumnasMesActual) {
        $serie = array();
        foreach ($periodos as $clave => $periodo) {
            $mes = $periodo['mes'];
            $valorMensual = isset($serieMensual[$mes]) ? (float) $serieMensual[$mes] : 0.0;
            $serie[$clave] = $mes === $mesActualClave ? $valorMensual / $cantidadSubcolumnasMesActual : $valorMensual;
        }
        return $serie;
    };

    $inicioMesActual = $inicio;
    $inicioPromedioVentas = $inicioMesActual->modify('-12 months');
    $ventasMensualesManga = $cashflowPDO->getVentasMensualesEntreFechas(
        $inicioPromedioVentas->format('Y-m-d'),
        $inicioMesActual->format('Y-m-d')
    );
    $sumaVentasManga = 0.0;
    $mesesMangaConOperatoria = 0;
    foreach ($ventasMensualesManga as $ventaMensualManga) {
        $totalMesManga = isset($ventaMensualManga['monto']) ? (float) $ventaMensualManga['monto'] : 0.0;
        $sumaVentasManga += $totalMesManga;
        if ($totalMesManga > 0) {
            $mesesMangaConOperatoria++;
        }
    }
    $ventaPromedioManga = $mesesMangaConOperatoria > 0 ? $sumaVentasManga / $mesesMangaConOperatoria : 0;
    $porcentajeCompraSobreVenta = 0.70;

    $ventasProyectadasMangaMensual = $crearSerieMensual();
    $comprasProyectadasMensual = $crearSerieMensual();
    foreach ($periodosMensuales as $clave => $periodo) {
        $ventasProyectadasMangaMensual[$clave] = $ventaPromedioManga;
        $comprasProyectadasMensual[$clave] = $ventaPromedioManga * $porcentajeCompraSobreVenta;
    }

    $gastosPresupuestadosMensual = $crearSerieMensual();
    $detalleGastosPresupuestados = array();
    foreach ($cashflowPDO->getClasesGastoPresupuestadas() as $row) {
        $monto = isset($row['presupuesto']) ? (float) $row['presupuesto'] : 0;
        $diaPago = isset($row['dia_pago']) ? max(1, min(31, (int) $row['dia_pago'])) : 30;
        foreach ($periodosMensuales as $clave => $periodo) {
            $ultimoDiaMes = (int) $periodo['inicio']->format('t');
            $fechaPago = $periodo['inicio']->setDate(
                (int) $periodo['inicio']->format('Y'),
                (int) $periodo['inicio']->format('m'),
                min($diaPago, $ultimoDiaMes)
            );

            if ($fechaPago < $desde) {
                continue;
            }

            $claveGasto = $claveMensualPorFecha($fechaPago);
            if ($claveGasto === null) {
                continue;
            }

            $gastosPresupuestadosMensual[$claveGasto] += $monto;
            $detalleGastosPresupuestados[] = array(
                'mes' => $claveGasto,
                'concepto' => isset($row['nombre']) ? $row['nombre'] : 'Gasto presupuestado',
                'fecha' => $fechaPago->format('Y-m-d'),
                'monto' => $monto,
            );
        }
    }

    $ordenesPendientesMensual = $crearSerieMensual();
    foreach ($cashflowPDO->getOrdenesCompraPendientesMensuales($desde->format('Y-m-d'), $hasta->format('Y-m-d')) as $row) {
        if (empty($row['fecha'])) {
            continue;
        }
        $claveOrden = $claveMensualPorFecha(new DateTimeImmutable($row['fecha'], $zonaLocal));
        if ($claveOrden !== null) {
            $ordenesPendientesMensual[$claveOrden] += (float) $row['monto'];
        }
    }

    $consignacionesPendientesMensual = $crearSerieMensual();
    $consignacionesPendientesMensual[$mesActualClave] = $cashflowPDO->getConsignacionesPendientesPagoTotal();

    $ventasProyectadasManga = $expandirSerieSemanal($ventasProyectadasMangaMensual);
    $comprasProyectadas = $expandirSerieSemanal($comprasProyectadasMensual);
    $gastosPresupuestados = $expandirSerieSemanal($gastosPresupuestadosMensual);
    $ordenesPendientes = $expandirSerieSemanal($ordenesPendientesMensual);
    $consignacionesPendientes = $expandirSerieSemanal($consignacionesPendientesMensual);

    $saldoInicialDisponible = $cashflowPDO->getSaldoDisponibleActual();
    $saldoInicial = $crearSerie();
    $totalVentasProyectadas = $crearSerie();
    $totalIngresos = $crearSerie();
    $totalEgresos = $crearSerie();
    $saldoFinal = $crearSerie();
    $saldo = $saldoInicialDisponible;

    foreach ($periodos as $clave => $periodo) {
        $saldoInicial[$clave] = $saldo;
        $totalVentasProyectadas[$clave] = $ventasProyectadasManga[$clave];
        $totalIngresos[$clave] = $totalVentasProyectadas[$clave];
        $totalEgresos[$clave] = $comprasProyectadas[$clave]
            + $gastosPresupuestados[$clave]
            + $ordenesPendientes[$clave]
            + $consignacionesPendientes[$clave];
        $saldoFinal[$clave] = $saldoInicial[$clave] + $totalIngresos[$clave] - $totalEgresos[$clave];
        $saldo = $saldoFinal[$clave];
    }

    $categoriasGrafico = array();
    $saldoFinalGrafico = array();
    foreach ($periodos as $clave => $periodo) {
        $categoriasGrafico[] = $periodo['label'];
        $saldoFinalGrafico[] = round($saldoFinal[$clave], 2);
    }

    return array(
        'periodos' => array_values(array_map(function ($periodo) {
            return array('clave' => $periodo['clave'], 'label' => $periodo['label']);
        }, $periodos)),
        'filas' => array(
            array('concepto' => 'SALDO INICIAL', 'valores' => $saldoInicial, 'clase' => 'table-info'),
            array('concepto' => 'Ventas Proyectadas Manga', 'valores' => $ventasProyectadasManga, 'clase' => ''),
            array('concepto' => 'TOTAL VENTAS PROYECTADAS', 'valores' => $totalVentasProyectadas, 'clase' => 'cashflow-total-ventas'),
            array('concepto' => 'TOTAL INGRESOS', 'valores' => $totalIngresos, 'clase' => 'table-warning'),
            array('concepto' => 'Compras proyectadas', 'valores' => $comprasProyectadas, 'clase' => ''),
            array('concepto' => 'Gastos presupuestados', 'valores' => $gastosPresupuestados, 'clase' => ''),
            array('concepto' => 'Ordenes de compra pendientes', 'valores' => $ordenesPendientes, 'clase' => ''),
            array('concepto' => 'Consignaciones pendientes', 'valores' => $consignacionesPendientes, 'clase' => ''),
            array('concepto' => 'TOTAL EGRESOS', 'valores' => $totalEgresos, 'clase' => 'table-warning'),
            array('concepto' => 'SALDO FINAL PROYECTADO', 'valores' => $saldoFinal, 'clase' => 'table-info'),
        ),
        'supuestos' => array(
            'saldoInicialDisponible' => $saldoInicialDisponible,
            'ventaPromedio' => $ventaPromedioManga,
            'ventasMangaMesesConOperatoria' => $mesesMangaConOperatoria,
            'ventasPeriodoDesde' => $inicioPromedioVentas->format('Y-m-d'),
            'ventasPeriodoHasta' => $inicioMesActual->modify('-1 day')->format('Y-m-d'),
            'porcentajeCompraSobreVenta' => $porcentajeCompraSobreVenta * 100,
            'horizonteMeses' => $horizonteMeses,
            'subcolumnasMesActual' => $cantidadSubcolumnasMesActual,
        ),
        'detalleGastosPresupuestados' => $detalleGastosPresupuestados,
        'grafico' => array(
            'categorias' => $categoriasGrafico,
            'saldoFinal' => $saldoFinalGrafico,
        ),
    );
}
?>
