<?php

class ProyectosModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listar($busqueda = '', $idEstado = 0, $registro = 'active')
    {
        $sql = "SELECT
                    p.id_proyecto,
                    p.slug,
                    p.titulo,
                    p.resumen_corto,
                    p.destacado,
                    p.orden_visualizacion,
                    p.publicado_en,
                    p.actualizado_en,
                    p.eliminado_en,
                    ep.id_estado_proyecto,
                    ep.nombre AS estado_nombre,
                    ep.slug AS estado_slug,
                    ep.color_hexadecimal AS estado_color,
                    c.nombre_mostrar AS cliente_nombre,
                    (
                        SELECT GROUP_CONCAT(
                            ca.nombre
                            ORDER BY ca.orden_visualizacion, ca.nombre
                            SEPARATOR '||'
                        )
                        FROM proyecto_categoria pc
                        INNER JOIN categorias ca
                            ON ca.id_categoria = pc.id_categoria
                        WHERE pc.id_proyecto = p.id_proyecto
                    ) AS categorias,
                    (
                        SELECT GROUP_CONCAT(
                            CONCAT(t.nombre, '::', t.color_hexadecimal)
                            ORDER BY pt.orden_visualizacion, t.nombre
                            SEPARATOR '||'
                        )
                        FROM proyecto_tecnologia pt
                        INNER JOIN tecnologias t
                            ON t.id_tecnologia = pt.id_tecnologia
                        WHERE pt.id_proyecto = p.id_proyecto
                    ) AS tecnologias
                FROM proyectos p
                INNER JOIN estados_proyecto ep
                    ON ep.id_estado_proyecto = p.id_estado_proyecto
                LEFT JOIN clientes c
                    ON c.id_cliente = p.id_cliente
                WHERE 1 = 1";

        $parametros = [];

        if ($registro === 'active') {
            $sql .= " AND p.eliminado_en IS NULL";
        } elseif ($registro === 'deleted') {
            $sql .= " AND p.eliminado_en IS NOT NULL";
        }

        if ($idEstado > 0) {
            $sql .= " AND p.id_estado_proyecto = ?";
            $parametros[] = $idEstado;
        }

        if ($busqueda !== '') {
            $valor = '%' . $busqueda . '%';
            $sql .= " AND (
                        p.titulo LIKE ?
                        OR p.slug LIKE ?
                        OR p.resumen_corto LIKE ?
                        OR c.nombre_mostrar LIKE ?
                    )";
            array_push($parametros, $valor, $valor, $valor, $valor);
        }

        $sql .= " ORDER BY
                    (p.eliminado_en IS NOT NULL) ASC,
                    p.destacado DESC,
                    p.orden_visualizacion ASC,
                    p.actualizado_en DESC";

        return $this->selectAll($sql, $parametros);
    }

    public function obtener($idProyecto)
    {
        $sql = "SELECT
                    id_proyecto,
                    id_estado_proyecto,
                    id_cliente,
                    slug,
                    titulo,
                    resumen_corto,
                    descripcion,
                    reto_tecnico,
                    resultado,
                    fecha_inicio,
                    fecha_fin,
                    destacado,
                    orden_visualizacion,
                    publicado_en,
                    creado_en,
                    actualizado_en,
                    eliminado_en
                FROM proyectos
                WHERE id_proyecto = ?";

        $proyecto = $this->select($sql, [$idProyecto]);

        if (empty($proyecto)) {
            return false;
        }

        $categorias = $this->selectAll(
            "SELECT id_categoria
             FROM proyecto_categoria
             WHERE id_proyecto = ?
             ORDER BY id_categoria",
            [$idProyecto]
        );

        $tecnologias = $this->selectAll(
            "SELECT id_tecnologia
             FROM proyecto_tecnologia
             WHERE id_proyecto = ?
             ORDER BY orden_visualizacion, id_tecnologia",
            [$idProyecto]
        );

        /*
         * Recupera todos los enlaces del proyecto:
         * GitHub, sitio, demostración, documentación,
         * descarga u otros tipos registrados en el catálogo.
         */
        $enlaces = $this->selectAll(
            "SELECT
                ep.id_enlace_proyecto,
                ep.id_tipo_enlace,
                ep.etiqueta,
                ep.url,
                ep.es_privado,
                ep.orden_visualizacion,
                te.nombre AS tipo_nombre,
                te.slug AS tipo_slug,
                te.clase_icono
             FROM enlaces_proyecto ep
             INNER JOIN tipos_enlace te
                ON te.id_tipo_enlace = ep.id_tipo_enlace
             WHERE ep.id_proyecto = ?
             ORDER BY
                ep.orden_visualizacion ASC,
                ep.id_enlace_proyecto ASC",
            [$idProyecto]
        );

        $proyecto['categorias'] = array_map(
            'intval',
            array_column(
                is_array($categorias) ? $categorias : [],
                'id_categoria'
            )
        );

        $proyecto['tecnologias'] = array_map(
            'intval',
            array_column(
                is_array($tecnologias) ? $tecnologias : [],
                'id_tecnologia'
            )
        );

        $proyecto['enlaces'] = is_array($enlaces)
            ? array_map(
                static function (array $enlace): array {
                    $enlace['id_enlace_proyecto'] = (int) (
                        $enlace['id_enlace_proyecto'] ?? 0
                    );
                    $enlace['id_tipo_enlace'] = (int) (
                        $enlace['id_tipo_enlace'] ?? 0
                    );
                    $enlace['es_privado'] = (int) (
                        $enlace['es_privado'] ?? 0
                    );
                    $enlace['orden_visualizacion'] = (int) (
                        $enlace['orden_visualizacion'] ?? 0
                    );

                    return $enlace;
                },
                $enlaces
            )
            : [];

        /*
         * Compatibilidad temporal con el formulario actual,
         * que todavía utiliza url_github y github_privado.
         */
        $enlaceGithub = null;

        foreach ($proyecto['enlaces'] as $enlace) {
            if (($enlace['tipo_slug'] ?? '') === 'github') {
                $enlaceGithub = $enlace;
                break;
            }
        }

        $proyecto['url_github'] = is_array($enlaceGithub)
            ? (string) ($enlaceGithub['url'] ?? '')
            : '';

        $proyecto['github_privado'] = is_array($enlaceGithub)
            ? (int) ($enlaceGithub['es_privado'] ?? 0)
            : 0;

        return $proyecto;
    }

    public function catalogos()
    {
        return [
            'estados' => $this->selectAll(
                "SELECT
                    id_estado_proyecto,
                    nombre,
                    slug,
                    color_hexadecimal,
                    visible_publicamente
                 FROM estados_proyecto
                 ORDER BY id_estado_proyecto"
            ),
            'categorias' => $this->selectAll(
                "SELECT
                    id_categoria,
                    nombre,
                    color_hexadecimal,
                    activa
                 FROM categorias
                 ORDER BY orden_visualizacion, nombre"
            ),
            'tecnologias' => $this->selectAll(
                "SELECT
                    id_tecnologia,
                    nombre,
                    color_hexadecimal,
                    activa
                 FROM tecnologias
                 ORDER BY orden_visualizacion, nombre"
            ),
            'clientes' => $this->selectAll(
                "SELECT id_cliente, nombre_mostrar
                 FROM clientes
                 WHERE activo = 1
                   AND eliminado_en IS NULL
                 ORDER BY nombre_mostrar"
            ),
            'tipos_enlace' => $this->selectAll(
                "SELECT
                    id_tipo_enlace,
                    nombre,
                    slug,
                    clase_icono
                FROM tipos_enlace
                ORDER BY id_tipo_enlace"
            ),
        ];
    }

    public function obtenerEstado($idEstado)
    {
        return $this->select(
            "SELECT
                id_estado_proyecto,
                nombre,
                slug,
                visible_publicamente
             FROM estados_proyecto
             WHERE id_estado_proyecto = ?",
            [$idEstado]
        );
    }

    public function existeSlug($slug, $idProyecto = 0)
    {
        $sql = "SELECT id_proyecto
                FROM proyectos
                WHERE slug = ?";

        $parametros = [$slug];

        if ($idProyecto > 0) {
            $sql .= " AND id_proyecto != ?";
            $parametros[] = $idProyecto;
        }

        return $this->select($sql, $parametros);
    }

    public function clienteValido($idCliente)
    {
        return $this->select(
            "SELECT id_cliente
             FROM clientes
             WHERE id_cliente = ?
               AND activo = 1
               AND eliminado_en IS NULL",
            [$idCliente]
        );
    }

    public function relacionesValidas($tipo, array $ids)
    {
        $configuracion = [
            'categorias' => ['categorias', 'id_categoria'],
            'tecnologias' => ['tecnologias', 'id_tecnologia'],
        ];

        if (!isset($configuracion[$tipo]) || empty($ids)) {
            return false;
        }

        [$tabla, $campo] = $configuracion[$tipo];
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT COUNT(*) AS total
                FROM {$tabla}
                WHERE {$campo} IN ({$marcadores})";

        $resultado = $this->select($sql, $ids);

        return (int) ($resultado['total'] ?? 0) === count($ids);
    }

    public function siguienteOrden()
    {
        return $this->select(
            "SELECT
                COALESCE(MAX(orden_visualizacion), 0) + 1
                    AS siguiente_orden
             FROM proyectos
             WHERE eliminado_en IS NULL"
        );
    }

    public function registrar(
        array $datos,
        array $categorias,
        array $tecnologias,
        ?array $enlaces = null
    ) {
        if (!$this->iniciarTransaccion()) {
            return 0;
        }

        try {
            $sql = "INSERT INTO proyectos (
                        id_estado_proyecto,
                        id_cliente,
                        creado_por,
                        slug,
                        titulo,
                        resumen_corto,
                        descripcion,
                        reto_tecnico,
                        resultado,
                        fecha_inicio,
                        fecha_fin,
                        destacado,
                        orden_visualizacion,
                        publicado_en
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $idProyecto = (int) $this->insertar($sql, [
                $datos['id_estado_proyecto'],
                $datos['id_cliente'],
                $datos['creado_por'],
                $datos['slug'],
                $datos['titulo'],
                $datos['resumen_corto'],
                $datos['descripcion'],
                $datos['reto_tecnico'],
                $datos['resultado'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $datos['destacado'],
                $datos['orden_visualizacion'],
                $datos['publicado_en'],
            ]);

            if (
                $idProyecto <= 0
                || !$this->guardarRelaciones(
                    $idProyecto,
                    $categorias,
                    $tecnologias
                )
                || !$this->guardarEnlacesProyecto(
                    $idProyecto,
                    $datos,
                    $enlaces
                )
            ) {
                $this->cancelarTransaccion();
                return 0;
            }

            $this->confirmarTransaccion();
            return $idProyecto;
        } catch (Throwable $e) {
            $this->cancelarTransaccion();
            error_log('Error al registrar proyecto: ' . $e->getMessage());
            return 0;
        }
    }

    public function modificar(
        array $datos,
        $idProyecto,
        array $categorias,
        array $tecnologias,
        ?array $enlaces = null
    ) {
        if (!$this->iniciarTransaccion()) {
            return false;
        }

        try {
            $sql = "UPDATE proyectos
                    SET id_estado_proyecto = ?,
                        id_cliente = ?,
                        slug = ?,
                        titulo = ?,
                        resumen_corto = ?,
                        descripcion = ?,
                        reto_tecnico = ?,
                        resultado = ?,
                        fecha_inicio = ?,
                        fecha_fin = ?,
                        destacado = ?,
                        orden_visualizacion = ?,
                        publicado_en = ?
                    WHERE id_proyecto = ?";

            $actualizado = $this->save($sql, [
                $datos['id_estado_proyecto'],
                $datos['id_cliente'],
                $datos['slug'],
                $datos['titulo'],
                $datos['resumen_corto'],
                $datos['descripcion'],
                $datos['reto_tecnico'],
                $datos['resultado'],
                $datos['fecha_inicio'],
                $datos['fecha_fin'],
                $datos['destacado'],
                $datos['orden_visualizacion'],
                $datos['publicado_en'],
                $idProyecto,
            ]);

            if (
                $actualizado !== 1
                || $this->save(
                    "DELETE FROM proyecto_categoria WHERE id_proyecto = ?",
                    [$idProyecto]
                ) !== 1
                || $this->save(
                    "DELETE FROM proyecto_tecnologia WHERE id_proyecto = ?",
                    [$idProyecto]
                ) !== 1
                || !$this->guardarRelaciones(
                    $idProyecto,
                    $categorias,
                    $tecnologias
                )
                || !$this->guardarEnlacesProyecto(
                    $idProyecto,
                    $datos,
                    $enlaces
                )
            ) {
                $this->cancelarTransaccion();
                return false;
            }

            $this->confirmarTransaccion();
            return true;
        } catch (Throwable $e) {
            $this->cancelarTransaccion();
            error_log('Error al modificar proyecto: ' . $e->getMessage());
            return false;
        }
    }

    public function cambiarBaja($idProyecto, $activo)
    {
        $sql = $activo
            ? "UPDATE proyectos
               SET eliminado_en = NULL
               WHERE id_proyecto = ?"
            : "UPDATE proyectos
               SET eliminado_en = CURRENT_TIMESTAMP
               WHERE id_proyecto = ?";

        return $this->save($sql, [$idProyecto]) === 1;
    }

    public function estadisticas()
    {
        return $this->select(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(p.eliminado_en IS NULL), 0) AS activos,
                COALESCE(SUM(
                    p.eliminado_en IS NULL
                    AND ep.slug = 'publicado'
                ), 0) AS publicados,
                COALESCE(SUM(p.eliminado_en IS NOT NULL), 0) AS bajas
             FROM proyectos p
             INNER JOIN estados_proyecto ep
                ON ep.id_estado_proyecto = p.id_estado_proyecto"
        );
    }

    private function guardarRelaciones(
        $idProyecto,
        array $categorias,
        array $tecnologias
    ) {
        foreach ($categorias as $idCategoria) {
            if ($this->save(
                "INSERT INTO proyecto_categoria (
                    id_proyecto,
                    id_categoria
                 ) VALUES (?, ?)",
                [$idProyecto, $idCategoria]
            ) !== 1) {
                return false;
            }
        }

        foreach ($tecnologias as $orden => $idTecnologia) {
            if ($this->save(
                "INSERT INTO proyecto_tecnologia (
                    id_proyecto,
                    id_tecnologia,
                    orden_visualizacion
                 ) VALUES (?, ?, ?)",
                [$idProyecto, $idTecnologia, $orden + 1]
            ) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Decide si debe utilizar el formulario antiguo de GitHub
     * o la colección nueva de enlaces.
     *
     * Mientras el controlador no envíe el cuarto/quinto argumento,
     * solamente se actualizará GitHub y se conservarán los demás
     * enlaces que pudieran existir.
     */
    private function guardarEnlacesProyecto(
        int $idProyecto,
        array $datos,
        ?array $enlaces
    ): bool {
        if ($enlaces === null) {
            return $this->guardarEnlaceGithub(
                $idProyecto,
                $datos['url_github'] ?? '',
                $datos['github_privado'] ?? 0
            );
        }

        return $this->guardarEnlaces($idProyecto, $enlaces);
    }

    /**
     * Sustituye todos los enlaces de un proyecto por la colección
     * recibida desde el formulario.
     */
    private function guardarEnlaces(
        int $idProyecto,
        array $enlaces
    ): bool {
        if ($idProyecto <= 0) {
            return false;
        }

        $tipos = $this->selectAll(
            "SELECT
                id_tipo_enlace,
                nombre
             FROM tipos_enlace"
        );

        if (!is_array($tipos)) {
            return false;
        }

        $tiposValidos = [];

        foreach ($tipos as $tipo) {
            $idTipo = (int) ($tipo['id_tipo_enlace'] ?? 0);

            if ($idTipo > 0) {
                $tiposValidos[$idTipo] = trim((string) (
                    $tipo['nombre'] ?? 'Enlace'
                ));
            }
        }

        /*
         * La colección recibida representa el estado completo
         * de los enlaces del proyecto.
         */
        if (
            $this->save(
                "DELETE FROM enlaces_proyecto
                 WHERE id_proyecto = ?",
                [$idProyecto]
            ) !== 1
        ) {
            return false;
        }

        foreach ($enlaces as $indice => $enlace) {
            if (!is_array($enlace)) {
                return false;
            }

            $idTipo = (int) (
                $enlace['id_tipo_enlace'] ?? 0
            );

            $url = trim((string) (
                $enlace['url'] ?? ''
            ));

            $esPrivado = filter_var(
                $enlace['es_privado'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ) ? 1 : 0;

            /*
             * Permite ignorar una fila completamente vacía
             * que haya quedado en el formulario dinámico.
             */
            if ($idTipo <= 0 && $url === '' && $esPrivado === 0) {
                continue;
            }

            if (!isset($tiposValidos[$idTipo])) {
                return false;
            }

            if (
                $url === ''
                && $esPrivado === 0
            ) {
                return false;
            }

            if ($url !== '') {
                if (
                    strlen($url) > 1000
                    || !filter_var($url, FILTER_VALIDATE_URL)
                ) {
                    return false;
                }

                $esquema = strtolower((string) parse_url(
                    $url,
                    PHP_URL_SCHEME
                ));

                if (!in_array($esquema, ['http', 'https'], true)) {
                    return false;
                }
            }

            $etiqueta = trim((string) (
                $enlace['etiqueta'] ?? ''
            ));

            if ($etiqueta === '') {
                $etiqueta = $tiposValidos[$idTipo];
            }

            if (strlen($etiqueta) > 100) {
                return false;
            }

            $orden = isset($enlace['orden_visualizacion'])
                ? max(0, (int) $enlace['orden_visualizacion'])
                : $indice + 1;

            if (
                $this->save(
                    "INSERT INTO enlaces_proyecto (
                        id_proyecto,
                        id_tipo_enlace,
                        etiqueta,
                        url,
                        es_privado,
                        orden_visualizacion
                     ) VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $idProyecto,
                        $idTipo,
                        $etiqueta,
                        $url !== '' ? $url : null,
                        $esPrivado,
                        $orden,
                    ]
                ) !== 1
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compatibilidad con el formulario actual, que administra
     * únicamente un enlace de GitHub.
     */
    private function guardarEnlaceGithub(
        int $idProyecto,
        $urlGithub,
        $esPrivado
    ): bool {
        if ($idProyecto <= 0) {
            return false;
        }

        $tipoGithub = $this->select(
            "SELECT id_tipo_enlace
             FROM tipos_enlace
             WHERE slug = 'github'
             LIMIT 1"
        );

        if (empty($tipoGithub['id_tipo_enlace'])) {
            return false;
        }

        $idTipoGithub = (int) $tipoGithub['id_tipo_enlace'];
        $urlGithub = trim((string) $urlGithub);

        $esPrivado = filter_var(
            $esPrivado,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        if (
            $this->save(
                "DELETE FROM enlaces_proyecto
                 WHERE id_proyecto = ?
                   AND id_tipo_enlace = ?",
                [
                    $idProyecto,
                    $idTipoGithub,
                ]
            ) !== 1
        ) {
            return false;
        }

        /*
         * Sin URL y sin privacidad significa que el proyecto
         * no tendrá enlace de GitHub.
         */
        if ($urlGithub === '' && $esPrivado === 0) {
            return true;
        }

        if ($urlGithub !== '') {
            if (
                strlen($urlGithub) > 1000
                || !filter_var($urlGithub, FILTER_VALIDATE_URL)
            ) {
                return false;
            }

            $esquema = strtolower((string) parse_url(
                $urlGithub,
                PHP_URL_SCHEME
            ));

            $host = strtolower((string) parse_url(
                $urlGithub,
                PHP_URL_HOST
            ));

            if (
                !in_array($esquema, ['http', 'https'], true)
                || !in_array(
                    $host,
                    ['github.com', 'www.github.com'],
                    true
                )
            ) {
                return false;
            }
        }

        return $this->save(
            "INSERT INTO enlaces_proyecto (
                id_proyecto,
                id_tipo_enlace,
                etiqueta,
                url,
                es_privado,
                orden_visualizacion
             ) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $idProyecto,
                $idTipoGithub,
                'GitHub',
                $urlGithub !== '' ? $urlGithub : null,
                $esPrivado,
                1,
            ]
        ) === 1;
    }

    public function fechaActualBaseDatos(): ?string
    {
        $resultado = $this->select(
            "SELECT CURRENT_TIMESTAMP AS fecha_actual"
        );

        if (!is_array($resultado)) {
            return null;
        }

        return $resultado['fecha_actual'] ?? null;
    }
}
