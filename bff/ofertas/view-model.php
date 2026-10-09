<?php
require_once dirname(__DIR__, 2) . '/app/models/oferta.php';

function obtenerOfertasViewModel()
{
    $ofertaPDO = new Oferta();
    $rows = $ofertaPDO->getPosiblesOfertas();
    $productos = array();
    $totCantidad = 0;
    $totValorizado = 0;
    $totMontoCompra = 0;
    $totMontoVenta = 0;
    $totOferta10 = 0;
    $totOferta20 = 0;
    $totOferta30 = 0;
    $totOferta40 = 0;
    $totOferta50 = 0;
    $porcentajeMaximoOferta = 50;

    foreach (is_array($rows) ? $rows : array() as $row) {
        $precioVenta = isset($row['precio']) ? (float) $row['precio'] : 0;
        $precioReposicion = isset($row['precio_costo']) ? (float) $row['precio_costo'] : 0;
        $precioCompra = isset($row['precio_compra']) ? (float) $row['precio_compra'] : 0;
        $mesesSinAccion = isset($row['meses_sin_accion']) ? (int) $row['meses_sin_accion'] : 0;
        $cantidad = isset($row['stock']) ? (int) $row['stock'] : 0;
        $esNuevo = isset($row['nuevo']) && (bool) $row['nuevo'];
        $titulo = isset($row['titulo']) ? $row['titulo'] : '';

        if ($mesesSinAccion > 15) {
            $porcentajeOferta = 50;
        } elseif ($mesesSinAccion > 12) {
            $porcentajeOferta = 40;
        } elseif ($mesesSinAccion > 9) {
            $porcentajeOferta = 30;
        } elseif ($mesesSinAccion > 6) {
            $porcentajeOferta = 20;
        } elseif ($mesesSinAccion > 3) {
            $porcentajeOferta = 10;
        } else {
            $porcentajeOferta = 0;
        }
        $porcentajeOferta = min($porcentajeOferta, $porcentajeMaximoOferta);

        $precioOfertaSugerido = $precioVenta * ((100 - $porcentajeOferta) / 100);
        $totCantidad += $cantidad;
        $totValorizado += $cantidad * $precioReposicion;
        $totMontoCompra += $cantidad * $precioCompra;
        $totMontoVenta += $cantidad * $precioVenta;
        if ($porcentajeOferta === 10) {
            $totOferta10 += $cantidad * $precioOfertaSugerido;
        } elseif ($porcentajeOferta === 20) {
            $totOferta20 += $cantidad * $precioOfertaSugerido;
        } elseif ($porcentajeOferta === 30) {
            $totOferta30 += $cantidad * $precioOfertaSugerido;
        } elseif ($porcentajeOferta === 40) {
            $totOferta40 += $cantidad * $precioOfertaSugerido;
        } elseif ($porcentajeOferta === 50) {
            $totOferta50 += $cantidad * $precioOfertaSugerido;
        }

        $productos[] = array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'titulo' => $titulo . ($esNuevo ? '' : ' (Usado)'),
            'fechaUltimaAccion' => isset($row['fecha_ultima_accion']) ? date('d/m/Y', strtotime($row['fecha_ultima_accion'])) : '',
            'tipoUltimaAccion' => isset($row['tipo_ultima_accion']) ? $row['tipo_ultima_accion'] : '',
            'mesesSinAccion' => number_format($mesesSinAccion, 0, '.', ''),
            'cantidad' => number_format($cantidad, 0, '.', ''),
            'precioCompra' => number_format($precioCompra, 2, '.', ''),
            'precioCosto' => number_format($precioReposicion, 2, '.', ''),
            'precioVenta' => number_format($precioVenta, 2, '.', ''),
            'precioOferta' => number_format(isset($row['precio_oferta']) ? (float) $row['precio_oferta'] : 0, 2, '.', ''),
            'porcentajeOferta' => number_format($porcentajeOferta, 0, '.', '') . '%',
            'criticidadOferta' => number_format($porcentajeOferta, 0, '.', ''),
            'precioOfertaSugerido' => number_format($precioOfertaSugerido, 2, '.', ''),
        );
    }

    return array(
        'productos' => $productos,
        'editorialesConsignacion' => $ofertaPDO->getEditorialesConsignacion(),
        'mediosPagoProveedor' => $ofertaPDO->getMediosPagoProveedor(),
        'porcentajeMaximoOferta' => $porcentajeMaximoOferta,
        'totales' => array(
            'registros' => count($productos),
            'cantidad' => $totCantidad,
            'montoCompra' => number_format($totMontoCompra, 2, '.', ''),
            'montoVenta' => number_format($totMontoVenta, 2, '.', ''),
            'oferta10' => number_format($totOferta10, 2, '.', ''),
            'oferta20' => number_format($totOferta20, 2, '.', ''),
            'oferta30' => number_format($totOferta30, 2, '.', ''),
            'oferta40' => number_format($totOferta40, 2, '.', ''),
            'oferta50' => number_format($totOferta50, 2, '.', ''),
            'valorizado' => number_format($totValorizado, 2, '.', ''),
        ),
    );
}
?>
