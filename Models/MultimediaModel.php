<?php

class MultimediaModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listar(
        $busqueda = '',
        $idProyecto = 0,
        $tipo = 'all',
        $estado = 'all'
    ) {
        $sql = "SELECT
                    mp.id_multimedia,
                    mp.id_proyecto,
                    mp.id_tipo_multimedia,
                    mp.tipo_almacenamiento,
                    mp.ruta_archivo,
                    mp.url_externa,
                    mp.ruta_miniatura,
                    mp.titulo,
                    mp.texto_alternativo,
                    mp.descripcion,
                    mp.tipo_mime,
                    mp.tamano_bytes,
                    mp.orden_visualizacion,
                    mp.es_portada,
                    mp.activo,
                    mp.creado_en,
                    mp.actualizado_en,
                    p.titulo AS proyecto_titulo,
                    p.slug AS proyecto_slug,
                    tm.nombre AS tipo_nombre,
                    tm.slug AS tipo_slug
                FROM multimedia_proyecto mp
                INNER JOIN proyectos p
                    ON p.id_proyecto = mp.id_proyecto
                INNER JOIN tipos_multimedia tm
                    ON tm.id_tipo_multimedia = mp.id_tipo_multimedia
                WHERE p.eliminado_en IS NULL";

        $parametros = [];

        if ($idProyecto > 0) {
            $sql .= " AND mp.id_proyecto = ?";
            $parametros[] = $idProyecto;
        }

        if (in_array($tipo, ['imagen', 'video'], true)) {
            $sql .= " AND tm.slug = ?";
            $parametros[] = $tipo;
        }

        if ($estado === 'active') {
            $sql .= " AND mp.activo = 1";
        } elseif ($estado === 'inactive') {
            $sql .= " AND mp.activo = 0";
        }

        if ($busqueda !== '') {
            $valor = '%' . $busqueda . '%';
            $sql .= " AND (
                        mp.titulo LIKE ?
                        OR mp.texto_alternativo LIKE ?
                        OR mp.descripcion LIKE ?
                        OR p.titulo LIKE ?
                    )";

            array_push(
                $parametros,
                $valor,
                $valor,
                $valor,
                $valor
            );
        }

        $sql .= " ORDER BY
                    mp.activo DESC,
                    mp.es_portada DESC,
                    p.titulo ASC,
                    mp.orden_visualizacion ASC,
                    mp.id_multimedia DESC";

        return $this->selectAll($sql, $parametros);
    }

    public function obtener($idMultimedia)
    {
        $sql = "SELECT
                    mp.id_multimedia,
                    mp.id_proyecto,
                    mp.id_tipo_multimedia,
                    mp.tipo_almacenamiento,
                    mp.ruta_archivo,
                    mp.url_externa,
                    mp.ruta_miniatura,
                    mp.titulo,
                    mp.texto_alternativo,
                    mp.descripcion,
                    mp.tipo_mime,
                    mp.tamano_bytes,
                    mp.orden_visualizacion,
                    mp.es_portada,
                    mp.activo,
                    mp.creado_en,
                    mp.actualizado_en,
                    p.titulo AS proyecto_titulo,
                    tm.nombre AS tipo_nombre,
                    tm.slug AS tipo_slug
                FROM multimedia_proyecto mp
                INNER JOIN proyectos p
                    ON p.id_proyecto = mp.id_proyecto
                INNER JOIN tipos_multimedia tm
                    ON tm.id_tipo_multimedia = mp.id_tipo_multimedia
                WHERE mp.id_multimedia = ?";

        return $this->select($sql, [$idMultimedia]);
    }

    public function catalogos()
    {
        return [
            'proyectos' => $this->selectAll(
                "SELECT
                    id_proyecto,
                    titulo
                 FROM proyectos
                 WHERE eliminado_en IS NULL
                 ORDER BY titulo ASC"
            ),
            'tipos' => $this->selectAll(
                "SELECT
                    id_tipo_multimedia,
                    nombre,
                    slug
                 FROM tipos_multimedia
                 ORDER BY id_tipo_multimedia ASC"
            ),
        ];
    }

    public function proyectoExiste($idProyecto)
    {
        return $this->select(
            "SELECT id_proyecto
             FROM proyectos
             WHERE id_proyecto = ?
               AND eliminado_en IS NULL",
            [$idProyecto]
        );
    }

    public function tipoExiste($idTipoMultimedia)
    {
        return $this->select(
            "SELECT
                id_tipo_multimedia,
                nombre,
                slug
             FROM tipos_multimedia
             WHERE id_tipo_multimedia = ?",
            [$idTipoMultimedia]
        );
    }

    public function tipoPorSlug($slug)
    {
        return $this->select(
            "SELECT
                id_tipo_multimedia,
                nombre,
                slug
             FROM tipos_multimedia
             WHERE slug = ?",
            [$slug]
        );
    }

    public function siguienteOrden($idProyecto)
    {
        return $this->select(
            "SELECT
                COALESCE(MAX(orden_visualizacion), 0) + 1
                    AS siguiente_orden
             FROM multimedia_proyecto
             WHERE id_proyecto = ?",
            [$idProyecto]
        );
    }

    public function registrar(array $datos)
    {
        if (!$this->iniciarTransaccion()) {
            return 0;
        }

        try {
            if (
                $datos['es_portada'] === 1
                && !$this->limpiarPortada($datos['id_proyecto'])
            ) {
                throw new RuntimeException(
                    'No fue posible actualizar la portada anterior.'
                );
            }

            $sql = "INSERT INTO multimedia_proyecto (
                        id_proyecto,
                        id_tipo_multimedia,
                        tipo_almacenamiento,
                        ruta_archivo,
                        url_externa,
                        ruta_miniatura,
                        titulo,
                        texto_alternativo,
                        descripcion,
                        tipo_mime,
                        tamano_bytes,
                        orden_visualizacion,
                        es_portada,
                        activo
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $idMultimedia = (int) $this->insertar($sql, [
                $datos['id_proyecto'],
                $datos['id_tipo_multimedia'],
                $datos['tipo_almacenamiento'],
                $datos['ruta_archivo'],
                $datos['url_externa'],
                $datos['ruta_miniatura'],
                $datos['titulo'],
                $datos['texto_alternativo'],
                $datos['descripcion'],
                $datos['tipo_mime'],
                $datos['tamano_bytes'],
                $datos['orden_visualizacion'],
                $datos['es_portada'],
                $datos['activo'],
            ]);

            if ($idMultimedia <= 0) {
                throw new RuntimeException(
                    'No fue posible registrar el archivo multimedia.'
                );
            }

            if (!$this->confirmarTransaccion()) {
                throw new RuntimeException(
                    'No fue posible confirmar el registro.'
                );
            }

            return $idMultimedia;
        } catch (Throwable $e) {
            $this->cancelarTransaccion();
            error_log('Error al registrar multimedia: ' . $e->getMessage());
            return 0;
        }
    }

    public function modificar(array $datos, $idMultimedia)
    {
        if (!$this->iniciarTransaccion()) {
            return false;
        }

        try {
            if (
                $datos['es_portada'] === 1
                && !$this->limpiarPortada(
                    $datos['id_proyecto'],
                    $idMultimedia
                )
            ) {
                throw new RuntimeException(
                    'No fue posible actualizar la portada anterior.'
                );
            }

            $sql = "UPDATE multimedia_proyecto
                    SET id_proyecto = ?,
                        id_tipo_multimedia = ?,
                        tipo_almacenamiento = ?,
                        ruta_archivo = ?,
                        url_externa = ?,
                        ruta_miniatura = ?,
                        titulo = ?,
                        texto_alternativo = ?,
                        descripcion = ?,
                        tipo_mime = ?,
                        tamano_bytes = ?,
                        orden_visualizacion = ?,
                        es_portada = ?,
                        activo = ?
                    WHERE id_multimedia = ?";

            if ($this->save($sql, [
                $datos['id_proyecto'],
                $datos['id_tipo_multimedia'],
                $datos['tipo_almacenamiento'],
                $datos['ruta_archivo'],
                $datos['url_externa'],
                $datos['ruta_miniatura'],
                $datos['titulo'],
                $datos['texto_alternativo'],
                $datos['descripcion'],
                $datos['tipo_mime'],
                $datos['tamano_bytes'],
                $datos['orden_visualizacion'],
                $datos['es_portada'],
                $datos['activo'],
                $idMultimedia,
            ]) !== 1) {
                throw new RuntimeException(
                    'No fue posible modificar el archivo multimedia.'
                );
            }

            if (!$this->confirmarTransaccion()) {
                throw new RuntimeException(
                    'No fue posible confirmar los cambios.'
                );
            }

            return true;
        } catch (Throwable $e) {
            $this->cancelarTransaccion();
            error_log('Error al modificar multimedia: ' . $e->getMessage());
            return false;
        }
    }

    public function cambiarEstado($idMultimedia, $activo)
    {
        $registro = $this->obtener($idMultimedia);

        if (empty($registro)) {
            return false;
        }

        if (!$this->iniciarTransaccion()) {
            return false;
        }

        try {
            if (
                $activo === 1
                && (int) $registro['es_portada'] === 1
                && !$this->limpiarPortada(
                    (int) $registro['id_proyecto'],
                    $idMultimedia
                )
            ) {
                throw new RuntimeException(
                    'No fue posible actualizar la portada del proyecto.'
                );
            }

            if (
                $this->save(
                    "UPDATE multimedia_proyecto
                     SET activo = ?
                     WHERE id_multimedia = ?",
                    [$activo, $idMultimedia]
                ) !== 1
            ) {
                throw new RuntimeException(
                    'No fue posible cambiar el estado.'
                );
            }

            if (!$this->confirmarTransaccion()) {
                throw new RuntimeException(
                    'No fue posible confirmar el cambio.'
                );
            }

            return true;
        } catch (Throwable $e) {
            $this->cancelarTransaccion();
            error_log('Error al cambiar estado multimedia: ' . $e->getMessage());
            return false;
        }
    }

    public function cambiarPortada($idMultimedia, $esPortada)
    {
        $registro = $this->obtener($idMultimedia);

        if (empty($registro)) {
            return false;
        }

        if (!$this->iniciarTransaccion()) {
            return false;
        }

        try {
            if ($esPortada === 1) {
                if (
                    $registro['tipo_slug'] !== 'imagen'
                    || (int) $registro['activo'] !== 1
                ) {
                    throw new RuntimeException(
                        'Solo una imagen activa puede establecerse como portada.'
                    );
                }

                if (!$this->limpiarPortada(
                    (int) $registro['id_proyecto'],
                    $idMultimedia
                )) {
                    throw new RuntimeException(
                        'No fue posible limpiar la portada anterior.'
                    );
                }
            }

            if (
                $this->save(
                    "UPDATE multimedia_proyecto
                     SET es_portada = ?
                     WHERE id_multimedia = ?",
                    [$esPortada, $idMultimedia]
                ) !== 1
            ) {
                throw new RuntimeException(
                    'No fue posible modificar la portada.'
                );
            }

            if (!$this->confirmarTransaccion()) {
                throw new RuntimeException(
                    'No fue posible confirmar la portada.'
                );
            }

            return true;
        } catch (Throwable $e) {
            $this->cancelarTransaccion();
            error_log('Error al cambiar portada multimedia: ' . $e->getMessage());
            return false;
        }
    }

    public function estadisticas()
    {
        return $this->select(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(
                    CASE
                        WHEN tm.slug = 'imagen' AND mp.activo = 1 THEN 1
                        ELSE 0
                    END
                ), 0) AS imagenes,
                COALESCE(SUM(
                    CASE
                        WHEN tm.slug = 'video' AND mp.activo = 1 THEN 1
                        ELSE 0
                    END
                ), 0) AS videos,
                COALESCE(SUM(
                    CASE
                        WHEN mp.activo = 0 THEN 1
                        ELSE 0
                    END
                ), 0) AS inactivos
             FROM multimedia_proyecto mp
             INNER JOIN tipos_multimedia tm
                ON tm.id_tipo_multimedia = mp.id_tipo_multimedia
             INNER JOIN proyectos p
                ON p.id_proyecto = mp.id_proyecto
             WHERE p.eliminado_en IS NULL"
        );
    }

    private function limpiarPortada(
        $idProyecto,
        $exceptoIdMultimedia = 0
    ) {
        $sql = "UPDATE multimedia_proyecto
                SET es_portada = 0
                WHERE id_proyecto = ?
                  AND es_portada = 1";

        $parametros = [$idProyecto];

        if ($exceptoIdMultimedia > 0) {
            $sql .= " AND id_multimedia != ?";
            $parametros[] = $exceptoIdMultimedia;
        }

        return $this->save($sql, $parametros) === 1;
    }
}
