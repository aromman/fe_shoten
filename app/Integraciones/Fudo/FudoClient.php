<?php

namespace App\Integraciones\Fudo;

final class FudoClient
{
    private const SESSION_TOKEN_KEY = 'integration.fudo.token';

    private $config;

    public function __construct(FudoConfig $config)
    {
        $this->config = $config;
    }

    public function estaConfigurado()
    {
        return $this->config->isConfigured();
    }

    public function obtenerVentas($desde, $hasta)
    {
        if (!$desde instanceof \DateTimeInterface || !$hasta instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('El período de ventas es inválido.');
        }
        if ($hasta <= $desde) {
            throw new \InvalidArgumentException('La fecha final debe ser posterior a la inicial.');
        }

        $pagina = 1;
        $tamanioPagina = 500;
        $ventas = array();

        do {
            $respuesta = $this->request('GET', '/sales', array(
                'filter' => array(
                    'createdAt' => sprintf(
                        'and(gte.%s,lte.%s)',
                        $desde->format('Y-m-d\TH:i:s\Z'),
                        $hasta->format('Y-m-d\TH:i:s\Z')
                    ),
                    'saleState' => 'in.(CLOSED)',
                ),
                'page' => array(
                    'number' => $pagina,
                    'size' => $tamanioPagina,
                ),
                'sort' => 'createdAt',
            ));

            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $ventas = array_merge($ventas, $datos);
            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return $ventas;
    }

    public function obtenerVentasConItems($desde, $hasta)
    {
        if (!$desde instanceof \DateTimeInterface || !$hasta instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('El periodo de ventas es invalido.');
        }
        if ($hasta <= $desde) {
            throw new \InvalidArgumentException('La fecha final debe ser posterior a la inicial.');
        }

        $pagina = 1;
        $tamanioPagina = 500;
        $ventas = array();
        $incluidos = array();

        do {
            $respuesta = $this->request('GET', '/sales', array(
                'filter' => array(
                    'createdAt' => sprintf(
                        'and(gte.%s,lte.%s)',
                        $desde->format('Y-m-d\TH:i:s\Z'),
                        $hasta->format('Y-m-d\TH:i:s\Z')
                    ),
                    'saleState' => 'in.(CLOSED)',
                ),
                'include' => 'items,items.subitems,tips',
                'page' => array(
                    'number' => $pagina,
                    'size' => $tamanioPagina,
                ),
                'sort' => 'createdAt',
            ));

            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $ventas = array_merge($ventas, $datos);

            if (isset($respuesta['included']) && is_array($respuesta['included'])) {
                $incluidos = array_merge($incluidos, $respuesta['included']);
            }

            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return array('ventas' => $ventas, 'incluidos' => $incluidos);
    }

    public function obtenerPagos($desde, $hasta)
    {
        if (!$desde instanceof \DateTimeInterface || !$hasta instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('El perÃ­odo de pagos es invÃ¡lido.');
        }
        if ($hasta <= $desde) {
            throw new \InvalidArgumentException('La fecha final debe ser posterior a la inicial.');
        }

        $pagina = 1;
        $tamanioPagina = 500;
        $pagos = array();
        $metodosPago = array();

        do {
            $respuesta = $this->request('GET', '/payments', array(
                'filter' => array(
                    'createdAt' => sprintf(
                        'and(gte.%s,lte.%s)',
                        $desde->format('Y-m-d\TH:i:s\Z'),
                        $hasta->format('Y-m-d\TH:i:s\Z')
                    ),
                    'canceled' => 'neq.true',
                ),
                'include' => 'paymentMethod',
                'page' => array(
                    'number' => $pagina,
                    'size' => $tamanioPagina,
                ),
            ));

            foreach (isset($respuesta['included']) && is_array($respuesta['included']) ? $respuesta['included'] : array() as $incluido) {
                if (!isset($incluido['type']) || $incluido['type'] !== 'PaymentMethod' || !isset($incluido['id'])) {
                    continue;
                }
                $metodosPago[(string) $incluido['id']] = isset($incluido['attributes']) && is_array($incluido['attributes'])
                    ? $incluido['attributes']
                    : array();
            }

            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $pagos = array_merge($pagos, $datos);
            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return array('pagos' => $pagos, 'metodosPago' => $metodosPago);
    }

    public function obtenerPagosPendientes()
    {
        $pagina = 1;
        $tamanioPagina = 500;
        $pagos = array();
        $metodosPago = array();

        do {
            $respuesta = $this->request('GET', '/payments', array(
                'filter' => array(
                    'receivedBy' => 'is.null',
                    'canceled' => 'neq.true',
                ),
                'fields' => array(
                    'payment' => 'amount,canceled,createdAt,expectedBy,paidAt,paymentMethod,receivedAmount,receivedBy',
                ),
                'include' => 'paymentMethod',
                'page' => array(
                    'number' => $pagina,
                    'size' => $tamanioPagina,
                ),
            ));

            foreach (isset($respuesta['included']) && is_array($respuesta['included']) ? $respuesta['included'] : array() as $incluido) {
                if (!isset($incluido['type']) || $incluido['type'] !== 'PaymentMethod' || !isset($incluido['id'])) {
                    continue;
                }
                $metodosPago[(string) $incluido['id']] = isset($incluido['attributes']) && is_array($incluido['attributes'])
                    ? $incluido['attributes']
                    : array();
            }

            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $pagos = array_merge($pagos, $datos);
            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return array('pagos' => $pagos, 'metodosPago' => $metodosPago);
    }

    public function obtenerPagosARecibir($desde)
    {
        if (!$desde instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('La fecha inicial de pagos a recibir es invÃ¡lida.');
        }

        $pagina = 1;
        $tamanioPagina = 500;
        $pagos = array();
        $metodosPago = array();

        do {
            $respuesta = $this->request('GET', '/payments', array(
                'filter' => array(
                    'receivedBy' => 'gte.' . $desde->format('Y-m-d\TH:i:s\Z'),
                    'canceled' => 'neq.true',
                ),
                'fields' => array(
                    'payment' => 'amount,canceled,createdAt,expectedBy,paidAt,paymentMethod,receivedAmount,receivedBy',
                ),
                'include' => 'paymentMethod',
                'page' => array(
                    'number' => $pagina,
                    'size' => $tamanioPagina,
                ),
            ));

            foreach (isset($respuesta['included']) && is_array($respuesta['included']) ? $respuesta['included'] : array() as $incluido) {
                if (!isset($incluido['type']) || $incluido['type'] !== 'PaymentMethod' || !isset($incluido['id'])) {
                    continue;
                }
                $metodosPago[(string) $incluido['id']] = isset($incluido['attributes']) && is_array($incluido['attributes'])
                    ? $incluido['attributes']
                    : array();
            }

            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $pagos = array_merge($pagos, $datos);
            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return array('pagos' => $pagos, 'metodosPago' => $metodosPago);
    }

    public function obtenerGastosAPagar($desde, $hasta)
    {
        if (!$desde instanceof \DateTimeInterface || !$hasta instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('El periodo de gastos a pagar es invalido.');
        }
        if ($hasta <= $desde) {
            throw new \InvalidArgumentException('La fecha final debe ser posterior a la inicial.');
        }

        $pagina = 1;
        $tamanioPagina = 500;
        $gastos = array();
        $categoriasGasto = array();

        do {
            $respuesta = $this->request('GET', '/expenses', array(
                'filter' => array(
                    'status' => 'eq.UNPAID',
                    'canceled' => 'neq.true',
                    'date' => sprintf(
                        'and(gte.%s,lte.%s)',
                        $desde->format('Y-m-d'),
                        $hasta->format('Y-m-d')
                    ),
                ),
                'fields' => array(
                    'expense' => 'amount,canceled,date,dueDate,paymentDate,status,description,expenseCategory',
                    'expenseCategory' => 'name',
                ),
                'include' => 'expenseCategory',
                'page' => array(
                    'number' => $pagina,
                    'size' => $tamanioPagina,
                ),
                'sort' => 'date',
            ));

            foreach (isset($respuesta['included']) && is_array($respuesta['included']) ? $respuesta['included'] : array() as $incluido) {
                if (!isset($incluido['type']) || $incluido['type'] !== 'ExpenseCategory' || !isset($incluido['id'])) {
                    continue;
                }
                $categoriasGasto[(string) $incluido['id']] = isset($incluido['attributes']) && is_array($incluido['attributes'])
                    ? $incluido['attributes']
                    : array();
            }

            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $gastos = array_merge($gastos, $datos);
            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return array('gastos' => $gastos, 'categoriasGasto' => $categoriasGasto);
    }

    public function obtenerGastosEntreFechas($desde, $hasta)
    {
        if (!$desde instanceof \DateTimeInterface || !$hasta instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('El periodo de gastos es invalido.');
        }
        if ($hasta <= $desde) {
            throw new \InvalidArgumentException('La fecha final debe ser posterior a la inicial.');
        }

        $pagina = 1;
        $tamanioPagina = 500;
        $gastos = array();
        $categoriasGasto = array();

        do {
            $respuesta = $this->request('GET', '/expenses', array(
                'filter' => array(
                    'canceled' => 'neq.true',
                    'date' => sprintf(
                        'and(gte.%s,lte.%s)',
                        $desde->format('Y-m-d'),
                        $hasta->format('Y-m-d')
                    ),
                ),
                'fields' => array(
                    'expense' => 'amount,canceled,date,dueDate,paymentDate,status,description,expenseCategory',
                    'expenseCategory' => 'name',
                ),
                'include' => 'expenseCategory',
                'page' => array(
                    'number' => $pagina,
                    'size' => $tamanioPagina,
                ),
                'sort' => 'date',
            ));

            foreach (isset($respuesta['included']) && is_array($respuesta['included']) ? $respuesta['included'] : array() as $incluido) {
                if (!isset($incluido['type']) || $incluido['type'] !== 'ExpenseCategory' || !isset($incluido['id'])) {
                    continue;
                }
                $categoriasGasto[(string) $incluido['id']] = isset($incluido['attributes']) && is_array($incluido['attributes'])
                    ? $incluido['attributes']
                    : array();
            }

            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $gastos = array_merge($gastos, $datos);
            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return array('gastos' => $gastos, 'categoriasGasto' => $categoriasGasto);
    }

    public function obtenerProveedores()
    {
        $pagina = 1;
        $tamanioPagina = 500;
        $proveedores = array();

        do {
            $respuesta = $this->request('GET', '/providers', array(
                'page' => array(
                    'number' => $pagina,
                    'size' => $tamanioPagina,
                ),
            ));

            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $proveedores = array_merge($proveedores, $datos);
            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return $proveedores;
    }

    public function obtenerProductos()
    {
        return $this->obtenerRecursosPaginados('/products', array(
            'include' => 'productCategory,unit',
            'page' => array(
                'size' => 500,
            ),
        ));
    }

    public function obtenerIngredientes()
    {
        return $this->obtenerRecursosPaginados('/ingredients', array(
            'include' => 'ingredientCategory,unit',
            'page' => array(
                'size' => 500,
            ),
        ));
    }

    public function obtenerCategoriasGasto()
    {
        return $this->obtenerRecursosPaginados('/expense-categories', array(
            'fields' => array(
                'expenseCategory' => 'name,financialCategory,active',
            ),
            'page' => array(
                'size' => 500,
            ),
        ));
    }

    public function obtenerGastosConItemsYProveedor()
    {
        $pagina = 1;
        $tamanioPagina = 500;
        $gastos = array();
        $incluidos = array();

        do {
            $respuesta = $this->request('GET', '/expenses', array(
                'filter' => array(
                    'canceled' => 'neq.true',
                ),
                'fields' => array(
                    'expense' => 'canceled,expenseItems,provider',
                    'expenseItem' => 'canceled,detail,price,product,ingredient,quantity',
                    'provider' => 'name',
                ),
                'include' => 'provider,expenseItems',
                'page' => array(
                    'number' => $pagina,
                    'size' => $tamanioPagina,
                ),
            ));

            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $gastos = array_merge($gastos, $datos);

            if (isset($respuesta['included']) && is_array($respuesta['included'])) {
                $incluidos = array_merge($incluidos, $respuesta['included']);
            }

            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return array('gastos' => $gastos, 'incluidos' => $incluidos);
    }

    public function obtenerProductosPorIds($ids)
    {
        return $this->obtenerRecursosPorIds(
            '/products',
            $ids,
            array(
                'include' => 'productCategory,unit',
            )
        );
    }

    public function obtenerIngredientesPorIds($ids)
    {
        return $this->obtenerRecursosPorIds(
            '/ingredients',
            $ids,
            array(
                'include' => 'ingredientCategory,unit',
            )
        );
    }

    private function obtenerRecursosPorIds($path, $ids, $queryBase)
    {
        $ids = array_values(array_unique(array_filter(array_map('strval', $ids), function ($id) {
            return $id !== '';
        })));

        $recursos = array();
        $incluidos = array();

        foreach (array_chunk($ids, 100) as $idsChunk) {
            $query = $queryBase;
            $query['filter'] = array('id' => 'in.(' . implode(',', $idsChunk) . ')');
            $query['page'] = array(
                'number' => 1,
                'size' => 500,
            );

            $respuesta = $this->request('GET', $path, $query);
            if (isset($respuesta['data']) && is_array($respuesta['data'])) {
                $recursos = array_merge($recursos, $respuesta['data']);
            }
            if (isset($respuesta['included']) && is_array($respuesta['included'])) {
                $incluidos = array_merge($incluidos, $respuesta['included']);
            }
        }

        return array('data' => $recursos, 'incluidos' => $incluidos);
    }

    private function obtenerRecursosPaginados($path, $queryBase)
    {
        $pagina = 1;
        $tamanioPagina = isset($queryBase['page']['size']) ? (int) $queryBase['page']['size'] : 500;
        $recursos = array();
        $incluidos = array();

        do {
            $query = $queryBase;
            $query['page']['number'] = $pagina;
            $query['page']['size'] = $tamanioPagina;

            $respuesta = $this->request('GET', $path, $query);
            $datos = isset($respuesta['data']) && is_array($respuesta['data'])
                ? $respuesta['data']
                : array();
            $recursos = array_merge($recursos, $datos);

            if (isset($respuesta['included']) && is_array($respuesta['included'])) {
                $incluidos = array_merge($incluidos, $respuesta['included']);
            }

            $pagina++;
        } while (count($datos) === $tamanioPagina);

        return array('data' => $recursos, 'incluidos' => $incluidos);
    }

    private function request($method, $path, $query = array())
    {
        $token = $this->obtenerToken();
        $url = $this->config->getApiBaseUrl() . '/' . ltrim($path, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        return $this->enviar($method, $url, null, array(
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
        ), 'API ' . $path);
    }

    private function obtenerToken()
    {
        if (!$this->config->isConfigured()) {
            throw new FudoException(
                'La integración con Fudo no tiene credenciales configuradas.',
                'FUDO_NO_CONFIGURADO'
            );
        }

        $tokenGuardado = isset($_SESSION[self::SESSION_TOKEN_KEY])
            ? $_SESSION[self::SESSION_TOKEN_KEY]
            : null;
        if (
            is_array($tokenGuardado)
            && !empty($tokenGuardado['token'])
            && isset($tokenGuardado['exp'])
            && isset($tokenGuardado['credentialsHash'])
            && hash_equals($this->credentialsHash(), (string) $tokenGuardado['credentialsHash'])
            && (int) $tokenGuardado['exp'] > time() + 300
        ) {
            return $tokenGuardado['token'];
        }

        $respuesta = $this->enviar(
            'POST',
            $this->config->getAuthUrl(),
            array(
                'apiKey' => $this->config->getApiKey(),
                'apiSecret' => $this->config->getApiSecret(),
            ),
            array('Accept: application/json', 'Content-Type: application/json'),
            'autenticación'
        );

        if (empty($respuesta['token']) || empty($respuesta['exp'])) {
            throw new FudoException(
                'Fudo no devolvió un token de acceso válido.',
                'FUDO_TOKEN_INVALIDO'
            );
        }

        $_SESSION[self::SESSION_TOKEN_KEY] = array(
            'token' => (string) $respuesta['token'],
            'exp' => (int) $respuesta['exp'],
            'credentialsHash' => $this->credentialsHash(),
        );

        return (string) $respuesta['token'];
    }

    private function enviar($method, $url, $body, $headers, $operacion)
    {
        if (!function_exists('curl_init')) {
            throw new FudoException(
                'La extensión cURL de PHP es necesaria para conectar con Fudo.',
                'CURL_NO_DISPONIBLE'
            );
        }

        $curl = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => $this->config->getTimeoutSeconds(),
            CURLOPT_TIMEOUT => $this->config->getTimeoutSeconds(),
        );

        if ($this->config->getCaBundlePath() !== '') {
            $options[CURLOPT_CAINFO] = $this->config->getCaBundlePath();
        }

        if ($body !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($curl, $options);
        $responseBody = curl_exec($curl);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErrorNumber = curl_errno($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($responseBody === false || $curlError !== '') {
            $detalle = $curlError !== ''
                ? sprintf(' cURL %d: %s', $curlErrorNumber, $curlError)
                : '';
            throw new FudoException(
                'No se pudo establecer conexión con Fudo.' . $detalle,
                'FUDO_CONEXION_ERROR'
            );
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new FudoException(
                'Fudo devolvió una respuesta no válida.',
                'FUDO_RESPUESTA_INVALIDA'
            );
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            if ($statusCode === 401) {
                $code = 'FUDO_NO_AUTORIZADO';
            } elseif ($statusCode === 403) {
                $code = 'FUDO_SIN_PERMISO';
            } else {
                $code = 'FUDO_HTTP_ERROR';
            }
            $detalle = $this->extraerDetalleError($decoded);
            throw new FudoException(
                sprintf(
                    'Fudo respondió con estado HTTP %d durante %s.%s',
                    $statusCode,
                    $operacion,
                    $detalle !== '' ? ' Detalle: ' . $detalle : ''
                ),
                $code
            );
        }

        return $decoded;
    }

    private function extraerDetalleError($respuesta)
    {
        $candidatos = array();

        foreach (array('message', 'detail', 'title', 'error', 'code') as $clave) {
            if (isset($respuesta[$clave]) && is_scalar($respuesta[$clave])) {
                $candidatos[] = (string) $respuesta[$clave];
            }
        }

        if (isset($respuesta['errors']) && is_array($respuesta['errors'])) {
            foreach ($respuesta['errors'] as $error) {
                if (!is_array($error)) {
                    continue;
                }
                foreach (array('detail', 'title', 'code') as $clave) {
                    if (isset($error[$clave]) && is_scalar($error[$clave])) {
                        $candidatos[] = (string) $error[$clave];
                    }
                }
            }
        }

        $detalle = implode(' | ', array_unique(array_filter(array_map('trim', $candidatos))));
        $detalle = preg_replace('/[\r\n\t]+/', ' ', $detalle);

        return substr((string) $detalle, 0, 500);
    }

    private function credentialsHash()
    {
        return hash('sha256', $this->config->getApiKey() . ':' . $this->config->getApiSecret());
    }
}
