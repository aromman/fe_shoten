<?php

/**
 * Página inicial de cada rol después de ingresar al sistema.
 */
function navigation_default_pages()
{
    return array(
        0 => '/turno/turno.php',
        1 => '/turno/turno.php',
        2 => '/informes/ventas-canales-mensual.php',
        3 => '/dashboard.php',
    );
}

function navigation_default_path($role)
{
    $defaultPages = navigation_default_pages();
    $role = (int) $role;

    return isset($defaultPages[$role]) ? $defaultPages[$role] : '/bienvenido.php';
}

/**
 * Construye la navegación disponible para un usuario.
 *
 * Cada sección contiene grupos y cada grupo contiene sus enlaces. La
 * autorización de las páginas debe seguir validándose en cada endpoint.
 */
function build_navigation($role, $channelType)
{
    $sections = array(
        'operaciones' => array('label' => 'Operaciones', 'icon' => 'fa-cash-register', 'groups' => array()),
        'ventas' => array('label' => 'Ventas', 'icon' => 'fa-cart-shopping', 'groups' => array()),
        'productos' => array('label' => 'Productos', 'icon' => 'fa-boxes-stacked', 'groups' => array()),
        'compras' => array('label' => 'Compras', 'icon' => 'fa-truck', 'groups' => array()),
        'finanzas' => array('label' => 'Finanzas', 'icon' => 'fa-wallet', 'groups' => array()),
        'personal' => array('label' => 'Personal', 'icon' => 'fa-users', 'groups' => array()),
        'administracion' => array('label' => 'Administración', 'icon' => 'fa-gear', 'groups' => array()),
        'informes' => array('label' => 'Informes', 'icon' => 'fa-chart-column', 'groups' => array()),
    );

    $addGroup = function ($section, $label, $items) use (&$sections) {
        $sections[$section]['groups'][] = array('label' => $label, 'items' => $items);
    };

    if ($role == 0 || $role == 1) {
        $addGroup('operaciones', 'Caja', array(
            array('label' => 'Gestión de Caja', 'path' => '/turno/turno.php', 'icon' => 'fa-cash-register'),
        ));

        if ($channelType === 'CAFETERIA') {
            $addGroup('operaciones', 'Cafetería', array(
                array('label' => 'Ventas', 'path' => '/pos/ventas.php', 'icon' => 'fa-receipt'),
                array('label' => 'Nueva Venta', 'path' => '/pos/pos.php', 'icon' => 'fa-circle-plus'),
                array('label' => 'Monitor de Cocina', 'path' => '/cocina/monitor.php', 'icon' => 'fa-kitchen-set'),
            ));
            $addGroup('operaciones', 'Reservas', array(
                array('label' => 'Calendario', 'path' => '/reservas/calendario.php', 'icon' => 'fa-calendar-days'),
                array('label' => 'Reservas', 'path' => '/reservas/reserva.php', 'icon' => 'fa-bookmark'),
                array('label' => 'Histórico', 'path' => '/reservas/historico.php', 'icon' => 'fa-clock-rotate-left'),
            ));
            $addGroup('productos', 'Productos', array(
                array('label' => 'Productos', 'path' => '/productos/productos.php', 'icon' => 'fa-box'),
            ));
        } else {
            $addGroup('ventas', 'Ventas', array(
                array('label' => 'Ventas', 'path' => '/pos/ventas.php', 'icon' => 'fa-receipt'),
            ));
            $addGroup('operaciones', 'Librería', array(
                array('label' => 'Nueva Venta', 'path' => '/pos/pos.php', 'icon' => 'fa-circle-plus'),
            ));
            // Oculto en Shoten (librería): no aplica.
            // $addGroup('operaciones', 'Cafetería', array(
            //     array('label' => 'Cafetería', 'path' => '/cafeteria/cafeteria.php', 'icon' => 'fa-mug-hot'),
            //     array('label' => 'Billeteras Mozo', 'path' => '/cafeteria/billeteras-mozo.php', 'icon' => 'fa-wallet'),
            // ));
            $addGroup('operaciones', 'Pedidos', array(
                array('label' => 'Pedidos', 'path' => '/pedidos/pedidos.php', 'icon' => 'fa-clipboard-list'),
            ));
            $addGroup('productos', 'Productos', array(
                array('label' => 'Productos', 'path' => '/productos/productos.php', 'icon' => 'fa-box'),
                array('label' => 'Análisis de Precios', 'path' => '/precios/analisis-precios.php', 'icon' => 'fa-tags'),
            ));
            $addGroup('productos', 'Inventario', array(
                array('label' => 'Control', 'path' => '/inventario/control.php', 'icon' => 'fa-clipboard-check'),
                array('label' => 'Agotados', 'path' => '/inventario/agotados.php', 'icon' => 'fa-triangle-exclamation'),
            ));
            $addGroup('productos', 'Ofertas', array(
                array('label' => 'Ofertas Activas', 'path' => '/ofertas/activas.php', 'icon' => 'fa-tag'),
            ));
            // Oculto en Shoten (librería): no aplica.
            // $addGroup('operaciones', 'Torneos', array(
            //     array('label' => 'Inscripciones', 'path' => '/torneo/inscripcion.php', 'icon' => 'fa-trophy'),
            // ));
        }
    }

    if ($role == 0) {
        $addGroup('administracion', 'Paneles', array(
            array('label' => 'Panel de Control', 'path' => '/dashboard.php', 'icon' => 'fa-gauge-high'),
            array('label' => 'Mes Actual', 'path' => '/pos/dashboard.php', 'icon' => 'fa-chart-line'),
            array('label' => 'Actualizar Objetivos', 'path' => '/actualizar-objetivos.php', 'icon' => 'fa-bullseye'),
        ));
        $addGroup('finanzas', 'Rentabilidad', array(
            array('label' => 'Dashboard financiero', 'path' => '/rentabilidad/dashboard-financiero.php', 'icon' => 'fa-chart-pie'),
            array('label' => 'Rentabilidad', 'path' => '/rentabilidad/rentabilidad.php', 'icon' => 'fa-arrow-trend-up'),
        ));
        $addGroup('finanzas', 'Tableros', array(
            array('label' => 'Ticket promedio', 'path' => '/finanzas/ticket-promedio.php', 'icon' => 'fa-receipt'),
            array('label' => 'Estado de resultado', 'path' => '/finanzas/estado-de-resultado.php', 'icon' => 'fa-file-invoice-dollar'),
            array('label' => 'Sistema 4C', 'path' => '/finanzas/sistema-4c.php', 'icon' => 'fa-layer-group'),
        ));
        $addGroup('finanzas', 'Flujo de Caja', array(
            array('label' => 'Flujo de Caja 12 Meses', 'path' => '/cashflow/flujo-caja-12m.php', 'icon' => 'fa-chart-line'),
            array('label' => 'Flujo Proyectado', 'path' => '/cashflow/flujo-caja-proy.php', 'icon' => 'fa-chart-area'),
        ));
        $addGroup('finanzas', 'Presupuesto', array(
            array('label' => 'Tablero', 'path' => '/presupuesto/dashboard.php', 'icon' => 'fa-gauge'),
            array('label' => 'Presupuestos', 'path' => '/presupuesto/presupuestos.php', 'icon' => 'fa-file-invoice-dollar'),
        ));
        $addGroup('ventas', 'Ventas', array(
            array('label' => 'Detalle de Ventas', 'path' => '/ventas.php', 'icon' => 'fa-receipt'),
            array('label' => 'Ventas por Canal', 'path' => '/informes/ventas-canales.php', 'icon' => 'fa-store'),
            array('label' => 'Ventas a Facturar', 'path' => '/pos/ventas-a-facturar.php', 'icon' => 'fa-file-invoice'),
        ));
        $addGroup('finanzas', 'Gastos', array(
            array('label' => 'Gastos', 'path' => '/gastos/gastos.php', 'icon' => 'fa-money-bill-transfer'),
            array('label' => 'Histórico de Gastos', 'path' => '/gastos/historial.php', 'icon' => 'fa-clock-rotate-left'),
        ));
        $addGroup('ventas', 'Clientes', array(
            array('label' => 'Clientes', 'path' => '/clientes/clientes.php', 'icon' => 'fa-address-book'),
            array('label' => 'Sorteo', 'path' => '/sorteo/sorteo.php', 'icon' => 'fa-gift'),
        ));
        $existenciasItem = array('label' => 'Existencias', 'path' => '/stock/existencias.php', 'icon' => 'fa-warehouse');
        $costoMantenimientoItem = array('label' => 'Costo de Mantenimiento de Inventario', 'path' => '/stock/costo-mantenimiento.php', 'icon' => 'fa-money-bill-transfer');
        $inventarioUnificado = false;
        foreach ($sections['productos']['groups'] as &$grupoProductos) {
            if ($grupoProductos['label'] === 'Inventario') {
                $grupoProductos['items'][] = $existenciasItem;
                $grupoProductos['items'][] = $costoMantenimientoItem;
                $inventarioUnificado = true;
                break;
            }
        }
        unset($grupoProductos);

        if (!$inventarioUnificado) {
            $addGroup('productos', 'Inventario', array($existenciasItem, $costoMantenimientoItem));
        }
        $addGroup('compras', 'Compras', array(
            array('label' => 'Calendario', 'path' => '/proveedores/calendario.php', 'icon' => 'fa-calendar-days'),
            array('label' => 'Proveedores', 'path' => '/proveedores/proveedores.php', 'icon' => 'fa-truck-field'),
            array('label' => 'Compras', 'path' => '/compras/compras.php', 'icon' => 'fa-cart-flatbed'),
            array('label' => 'Nueva Compra', 'path' => '/compras/buy.php', 'icon' => 'fa-circle-plus'),
        ));
        $addGroup('finanzas', 'Liquidaciones', array(
            array('label' => 'Liquidación', 'path' => '/liquidaciones/liquidacion.php', 'icon' => 'fa-calculator'),
            array('label' => 'Historial', 'path' => '/liquidaciones/liquidaciones.php', 'icon' => 'fa-clock-rotate-left'),
        ));
        $addGroup('finanzas', 'Caja', array(
            array('label' => 'Movimientos de Caja', 'path' => '/turno/movimientos.php', 'icon' => 'fa-money-bill-transfer'),
        ));
        $addGroup('finanzas', 'Cuentas', array(
            array('label' => 'Cuentas Comerciales', 'path' => '/cuentas/cuentas.php', 'icon' => 'fa-building-columns'),
            array('label' => 'Cuentas Personales', 'path' => '/cuentas/cuentas-personales.php', 'icon' => 'fa-wallet'),
        ));
        $addGroup('finanzas', 'Consignaciones', array(
            array('label' => 'Consignaciones', 'path' => '/consignaciones/consignaciones.php', 'icon' => 'fa-handshake'),
        ));
        $addGroup('productos', 'Catálogo', array(
            array('label' => 'Editoriales', 'path' => '/editoriales/editoriales.php', 'icon' => 'fa-building'),
            array('label' => 'Más Vendidos', 'path' => '/productos-mas-vendidos.php', 'icon' => 'fa-ranking-star'),
            array('label' => 'Formatos', 'path' => '/maestros/productos-formato.php', 'icon' => 'fa-shapes'),
            array('label' => 'Series', 'path' => '/maestros/productos-serie.php', 'icon' => 'fa-layer-group'),
            array('label' => 'Carga Masiva', 'path' => '/productos/upload.php', 'icon' => 'fa-file-arrow-up'),
            array('label' => 'Tipos de Ubicación', 'path' => '/maestros/productos-ubicacion-tipo.php', 'icon' => 'fa-map-pin'),
            array('label' => 'Ubicaciones', 'path' => '/maestros/productos-ubicacion.php', 'icon' => 'fa-location-dot'),
            array('label' => 'Exportar Productos', 'path' => '/exportar-productos.php', 'icon' => 'fa-file-export'),
        ));
        $addGroup('productos', 'Ofertas', array(
            array('label' => 'Posibles Ofertas', 'path' => '/ofertas/ofertas.php', 'icon' => 'fa-percent'),
            array('label' => 'Ofertas Activas', 'path' => '/ofertas/activas.php', 'icon' => 'fa-tag'),
            array('label' => 'Ofertas Vendidas', 'path' => '/ofertas/vendidas.php', 'icon' => 'fa-tags'),
        ));
        $addGroup('administracion', 'Locaciones', array(
            array('label' => 'Canales de Venta', 'path' => '/canales.php', 'icon' => 'fa-store'),
        ));
        // Oculto en Shoten: el módulo Eventos usa la tabla `stock`, que ya no existe.
        // $addGroup('operaciones', 'Eventos', array(
        //     array('label' => 'Calendario', 'path' => '/eventos/eventos.php', 'icon' => 'fa-calendar-days'),
        //     array('label' => 'Preparar Evento', 'path' => '/eventos/preparar-evento.php', 'icon' => 'fa-list-check'),
        //     array('label' => 'Finalizar Evento', 'path' => '/eventos/finalizar-evento.php', 'icon' => 'fa-flag-checkered'),
        // ));
        $addGroup('finanzas', 'Costos y Deudas', array(
            array('label' => 'Costos', 'path' => '/costos.php', 'icon' => 'fa-coins'),
            array('label' => 'Inversores', 'path' => '/inversiones/inversiones.php', 'icon' => 'fa-hand-holding-dollar'),
        ));
        $addGroup('personal', 'Recursos Humanos', array(
            array('label' => 'Panel de RRHH', 'path' => '/empleados/dashboard.php', 'icon' => 'fa-gauge-high'),
            array('label' => 'Empleados', 'path' => '/empleados/empleados.php', 'icon' => 'fa-id-badge'),
            array('label' => 'Pagos', 'path' => '/empleados/pagos.php', 'icon' => 'fa-money-check-dollar'),
            array('label' => 'Salarios', 'path' => '/maestros/salarios.php', 'icon' => 'fa-sack-dollar'),
            // Equipos: ocultos en Shoten (no se usan).
            // array('label' => 'Equipos', 'path' => '/maestros/equipos.php', 'icon' => 'fa-people-group'),
            // array('label' => 'Integrantes de Equipo', 'path' => '/maestros/equipo-empleado.php', 'icon' => 'fa-user-group'),
        ));
        $addGroup('personal', 'Cuentas', array(
            array('label' => 'Cuentas', 'path' => '/cuentas/cuentas-empleados.php', 'icon' => 'fa-building-columns'),
        ));
        $addGroup('administracion', 'Tablas Maestras', array(
            array('label' => 'Clientes', 'path' => '/clientes.php', 'icon' => 'fa-address-book'),
            array('label' => 'Órdenes de Compra', 'path' => '/orden-compra.php', 'icon' => 'fa-file-signature'),
            array('label' => 'Compras', 'path' => '/compras.php', 'icon' => 'fa-cart-flatbed'),
            array('label' => 'Medios de Pago', 'path' => '/maestros/medios-pago.php', 'icon' => 'fa-credit-card'),
            array('label' => 'Descuentos', 'path' => '/maestros/descuentos.php', 'icon' => 'fa-percent'),
            array('label' => 'Clases de Gastos', 'path' => '/maestros/gastos-clase.php', 'icon' => 'fa-tags'),
        ));
        $addGroup('informes', 'Informes', navigation_report_items());
        $addGroup('administracion', 'Usuarios', array(
            array('label' => 'Usuarios', 'path' => '/usuarios/usuarios.php', 'icon' => 'fa-users-gear'),
            array('label' => 'Roles', 'path' => '/usuarios/roles.php', 'icon' => 'fa-user-shield'),
        ));
    }

    if ($role == 2) {
        $addGroup('informes', 'Informes', array_slice(navigation_report_items(), 3));
    }

    if ($role == 3) {
        $addGroup('ventas', 'Marketing', array(
            array('label' => 'Panel de Control', 'path' => '/dashboard.php', 'icon' => 'fa-gauge-high'),
            array('label' => 'Control de Stock', 'path' => '/inventario/control.php', 'icon' => 'fa-clipboard-check'),
            array('label' => 'Productos Agotados', 'path' => '/inventario/agotados.php', 'icon' => 'fa-triangle-exclamation'),
            array('label' => 'Exportar Productos', 'path' => '/exportar-productos.php', 'icon' => 'fa-file-export'),
        ));
    }

    return array_filter($sections, function ($section) {
        return !empty($section['groups']);
    });
}

function navigation_report_items()
{
    return array(
        array('label' => 'Ventas Inconsistentes', 'path' => '/informes/ventas-inconsistentes.php', 'icon' => 'fa-triangle-exclamation'),
        array('label' => 'Stock Inconsistente', 'path' => '/informes/stock-inconsistente.php', 'icon' => 'fa-box-open'),
        array('label' => 'Valoración ABC', 'path' => '/informes/valoracion-abc.php', 'icon' => 'fa-chart-column'),
        array('label' => 'Ventas por Canal Mensual', 'path' => '/informes/ventas-canales-mensual.php', 'icon' => 'fa-chart-line'),
        array('label' => 'Compras por Canal Mensual', 'path' => '/informes/compras-canales-mensual.php', 'icon' => 'fa-chart-area'),
        array('label' => 'Gastos por Canal Mensual', 'path' => '/informes/gastos-canales-mensual.php', 'icon' => 'fa-chart-pie'),
    );
}
