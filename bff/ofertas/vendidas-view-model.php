<?php
require_once dirname(__DIR__, 2) . '/app/models/oferta.php';

function obtenerOfertasVendidasViewModel($nombre = '', $meses = 3)
{
    $ofertaPDO = new Oferta();
    $rows = $ofertaPDO->getOfertasVendidasUltimosMeses($meses, $nombre);
    $rowsMeses = $ofertaPDO->getOfertasVendidasPorMes($meses, $nombre);
    $rowsSemanas = $ofertaPDO->getOfertasVendidasPorSemana($meses, $nombre);
    $productos = array();
    $mesesGrafico = array('labels' => array(), 'data' => array());
    $semanasGrafico = array('labels' => array(), 'data' => array());

    foreach (is_array($rows) ? $rows : array() as $row) {
        $precioLista = isset($row['precio']) ? (float) $row['precio'] : 0;
        $precioOferta = isset($row['precio_oferta']) ? (float) $row['precio_oferta'] : 0;
        $precioCosto = isset($row['precio_costo']) ? (float) $row['precio_costo'] : 0;
        $unidadesVendidas = isset($row['unidades_vendidas']) ? (int) $row['unidades_vendidas'] : 0;
        $porcentajeDiferencia = $precioLista > 0 ? (($precioLista - $precioOferta) / $precioLista) * 100 : 0;
        $ultimaVenta = '';

        if (!empty($row['ultima_venta'])) {
            $timestamp = strtotime($row['ultima_venta']);
            $ultimaVenta = $timestamp !== false ? date('d/m/Y', $timestamp) : $row['ultima_venta'];
        }

        $productos[] = array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'titulo' => isset($row['titulo']) ? $row['titulo'] : '',
            'unidadesVendidas' => number_format($unidadesVendidas, 0, '.', ''),
            'precioCosto' => number_format($precioCosto, 2, '.', ''),
            'precioLista' => number_format($precioLista, 2, '.', ''),
            'precioOferta' => number_format($precioOferta, 2, '.', ''),
            'ultimaVenta' => $ultimaVenta,
            'porcentajeDiferencia' => number_format($porcentajeDiferencia, 2, '.', '') . '%',
            'precioOfertaInconsistente' => $precioOferta < $precioCosto || $precioOferta > $precioLista,
        );
    }

    foreach (is_array($rowsMeses) ? $rowsMeses : array() as $row) {
        $mesesGrafico['labels'][] = isset($row['etiqueta']) ? $row['etiqueta'] : '';
        $mesesGrafico['data'][] = isset($row['total_vendido']) ? round((float) $row['total_vendido'], 2) : 0;
    }

    foreach (is_array($rowsSemanas) ? $rowsSemanas : array() as $row) {
        $semanasGrafico['labels'][] = isset($row['etiqueta']) ? $row['etiqueta'] : '';
        $semanasGrafico['data'][] = isset($row['total_vendido']) ? round((float) $row['total_vendido'], 2) : 0;
    }

    return array(
        'productos' => $productos,
        'graficos' => array(
            'meses' => $mesesGrafico,
            'semanas' => $semanasGrafico,
        ),
        'filtros' => array(
            'nombre' => $nombre,
            'meses' => $meses,
        ),
    );
}
?>
