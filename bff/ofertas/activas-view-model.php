<?php
require_once dirname(__DIR__, 2) . '/app/models/oferta.php';

function obtenerOfertasActivasViewModel($nombre = '')
{
    $ofertaPDO = new Oferta();
    $rows = $ofertaPDO->getOfertasActivas($nombre);
    $productos = array();
    $totCantidad = 0;
    $totPrecioLista = 0;
    $totPrecioOferta = 0;

    foreach (is_array($rows) ? $rows : array() as $row) {
        $precioLista = isset($row['precio']) ? (float) $row['precio'] : 0;
        $precioOferta = isset($row['precio_oferta']) ? (float) $row['precio_oferta'] : 0;
        $precioCosto = isset($row['precio_costo']) ? (float) $row['precio_costo'] : 0;
        $cantidad = isset($row['stock']) ? (int) $row['stock'] : 0;
        $porcentajeDiferencia = $precioLista > 0 ? (($precioLista - $precioOferta) / $precioLista) * 100 : 0;
        $fechaPrecio = '';

        if (!empty($row['updateDate'])) {
            $timestamp = strtotime($row['updateDate']);
            $fechaPrecio = $timestamp !== false ? date('d/m/Y H:i', $timestamp) : $row['updateDate'];
        }

        $totCantidad += $cantidad;
        $totPrecioLista += $cantidad * $precioLista;
        $totPrecioOferta += $cantidad * $precioOferta;

        $productos[] = array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'titulo' => isset($row['titulo']) ? $row['titulo'] : '',
            'cantidad' => number_format($cantidad, 0, '.', ''),
            'precioCosto' => number_format($precioCosto, 2, '.', ''),
            'precioLista' => number_format($precioLista, 2, '.', ''),
            'precioOferta' => number_format($precioOferta, 2, '.', ''),
            'fechaPrecio' => $fechaPrecio,
            'porcentajeDiferencia' => number_format($porcentajeDiferencia, 2, '.', '') . '%',
            'precioOfertaInconsistente' => $precioOferta < $precioCosto || $precioOferta > $precioLista,
        );
    }

    return array(
        'productos' => $productos,
        'filtros' => array(
            'nombre' => $nombre,
        ),
        'totales' => array(
            'registros' => count($productos),
            'cantidad' => $totCantidad,
            'precioLista' => number_format($totPrecioLista, 2, '.', ''),
            'precioOferta' => number_format($totPrecioOferta, 2, '.', ''),
            'porcentajeDiferencia' => number_format($totPrecioLista > 0 ? (($totPrecioLista - $totPrecioOferta) / $totPrecioLista) * 100 : 0, 2, '.', '') . '%',
        ),
    );
}
?>
