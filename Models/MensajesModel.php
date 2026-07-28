<?php

class MensajesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listar(
        $busqueda = '',
        $estado = 'all',
        $idServicio = 0,
        $origen = 'all',
        $fechaDesde = '',
        $fechaHasta = ''
    ) {
        $sql = "SELECT
                    sc.id_solicitud_contacto,
                    sc.id_servicio,
                    sc.id_contacto_cliente,
                    sc.nombre_completo,
                    sc.correo,
                    sc.telefono,
                    sc.nombre_empresa,
                    sc.asunto,
                    sc.mensaje,
                    sc.fecha_preferida,
                    sc.modalidad_preferida,
                    sc.estado,
                    sc.origen,
                    sc.direccion_ip,
                    sc.creado_en,
                    sc.actualizado_en,
                    s.nombre AS servicio_nombre,
                    s.slug AS servicio_slug,
                    cc.nombre_completo AS contacto_nombre,
                    cc.correo AS contacto_correo,
                    c.id_cliente,
                    c.nombre_mostrar AS cliente_nombre,
                    EXISTS (
                        SELECT 1
                        FROM citas ci
                        WHERE ci.id_solicitud_contacto =
                            sc.id_solicitud_contacto
                    ) AS tiene_cita
                FROM solicitudes_contacto sc
                LEFT JOIN servicios s
                    ON s.id_servicio = sc.id_servicio
                LEFT JOIN contactos_cliente cc
                    ON cc.id_contacto_cliente =
                        sc.id_contacto_cliente
                LEFT JOIN clientes c
                    ON c.id_cliente = cc.id_cliente
                WHERE 1 = 1";

        $parametros = [];

        if ($estado !== 'all') {
            $sql .= " AND sc.estado = ?";
            $parametros[] = $estado;
        }

        if ($idServicio > 0) {
            $sql .= " AND sc.id_servicio = ?";
            $parametros[] = $idServicio;
        }

        if ($origen !== 'all') {
            $sql .= " AND sc.origen = ?";
            $parametros[] = $origen;
        }

        if ($fechaDesde !== '') {
            $sql .= " AND DATE(sc.creado_en) >= ?";
            $parametros[] = $fechaDesde;
        }

        if ($fechaHasta !== '') {
            $sql .= " AND DATE(sc.creado_en) <= ?";
            $parametros[] = $fechaHasta;
        }

        if ($busqueda !== '') {
            $valor = '%' . $busqueda . '%';

            $sql .= " AND (
                        sc.nombre_completo LIKE ?
                        OR sc.correo LIKE ?
                        OR sc.telefono LIKE ?
                        OR sc.nombre_empresa LIKE ?
                        OR sc.asunto LIKE ?
                        OR sc.mensaje LIKE ?
                        OR s.nombre LIKE ?
                        OR c.nombre_mostrar LIKE ?
                    )";

            for ($i = 0; $i < 8; $i++) {
                $parametros[] = $valor;
            }
        }

        $sql .= " ORDER BY
                    CASE sc.estado
                        WHEN 'nueva' THEN 0
                        WHEN 'leida' THEN 1
                        WHEN 'en_seguimiento' THEN 2
                        WHEN 'respondida' THEN 3
                        WHEN 'cerrada' THEN 4
                        WHEN 'archivada' THEN 5
                        WHEN 'spam' THEN 6
                        ELSE 7
                    END ASC,
                    sc.creado_en DESC";

        return $this->selectAll($sql, $parametros);
    }

    public function obtener($idSolicitud)
    {
        $sql = "SELECT
                    sc.id_solicitud_contacto,
                    sc.id_servicio,
                    sc.id_contacto_cliente,
                    sc.nombre_completo,
                    sc.correo,
                    sc.telefono,
                    sc.nombre_empresa,
                    sc.asunto,
                    sc.mensaje,
                    sc.fecha_preferida,
                    sc.modalidad_preferida,
                    sc.estado,
                    sc.origen,
                    sc.direccion_ip,
                    sc.creado_en,
                    sc.actualizado_en,
                    s.nombre AS servicio_nombre,
                    s.slug AS servicio_slug,
                    s.clase_icono AS servicio_icono,
                    cc.nombre_completo AS contacto_nombre,
                    cc.puesto AS contacto_puesto,
                    cc.correo AS contacto_correo,
                    cc.telefono AS contacto_telefono,
                    c.id_cliente,
                    c.nombre_mostrar AS cliente_nombre,
                    (
                        SELECT ci.id_cita
                        FROM citas ci
                        WHERE ci.id_solicitud_contacto =
                            sc.id_solicitud_contacto
                        ORDER BY ci.creada_en DESC
                        LIMIT 1
                    ) AS id_cita,
                    (
                        SELECT ci.inicia_en
                        FROM citas ci
                        WHERE ci.id_solicitud_contacto =
                            sc.id_solicitud_contacto
                        ORDER BY ci.creada_en DESC
                        LIMIT 1
                    ) AS cita_inicia_en
                FROM solicitudes_contacto sc
                LEFT JOIN servicios s
                    ON s.id_servicio = sc.id_servicio
                LEFT JOIN contactos_cliente cc
                    ON cc.id_contacto_cliente =
                        sc.id_contacto_cliente
                LEFT JOIN clientes c
                    ON c.id_cliente = cc.id_cliente
                WHERE sc.id_solicitud_contacto = ?";

        return $this->select($sql, [$idSolicitud]);
    }

    public function catalogos()
    {
        return [
            'servicios' => $this->selectAll(
                "SELECT
                    id_servicio,
                    nombre,
                    slug
                 FROM servicios
                 WHERE activo = 1
                 ORDER BY orden_visualizacion ASC, nombre ASC"
            ),
            'contactos' => $this->selectAll(
                "SELECT
                    cc.id_contacto_cliente,
                    cc.nombre_completo,
                    cc.correo,
                    cc.puesto,
                    c.id_cliente,
                    c.nombre_mostrar AS cliente_nombre
                 FROM contactos_cliente cc
                 INNER JOIN clientes c
                    ON c.id_cliente = cc.id_cliente
                 WHERE cc.activo = 1
                   AND c.activo = 1
                   AND c.eliminado_en IS NULL
                 ORDER BY
                    c.nombre_mostrar ASC,
                    cc.es_principal DESC,
                    cc.nombre_completo ASC"
            ),
        ];
    }

    public function contactoExiste($idContacto)
    {
        return $this->select(
            "SELECT cc.id_contacto_cliente
             FROM contactos_cliente cc
             INNER JOIN clientes c
                ON c.id_cliente = cc.id_cliente
             WHERE cc.id_contacto_cliente = ?
               AND cc.activo = 1
               AND c.activo = 1
               AND c.eliminado_en IS NULL",
            [$idContacto]
        );
    }

    public function cambiarEstado(
        $idSolicitud,
        $estado
    ): array {
        $idSolicitud = (int) $idSolicitud;
        $estado = strtolower(trim((string) $estado));

        if ($idSolicitud <= 0 || $estado === '') {
            return [
                'status' => false,
                'error' => 'Identificador o estado incorrecto.',
            ];
        }

        $resultado = $this->ejecutar(
            "UPDATE solicitudes_contacto
         SET
            estado = ?,
            actualizado_en = CURRENT_TIMESTAMP
         WHERE id_solicitud_contacto = ?",
            [
                $estado,
                $idSolicitud,
            ]
        );

        if (!$resultado['status']) {
            return $resultado;
        }

        /*
     * rowCount() puede devolver cero cuando se guarda exactamente
     * el mismo estado. Por eso comprobamos directamente el registro.
     */
        $registro = $this->select(
            "SELECT estado
         FROM solicitudes_contacto
         WHERE id_solicitud_contacto = ?",
            [$idSolicitud]
        );

        if (!is_array($registro)) {
            return [
                'status' => false,
                'error' => 'No fue posible verificar el registro actualizado.',
            ];
        }

        if (($registro['estado'] ?? '') !== $estado) {
            return [
                'status' => false,
                'error' => sprintf(
                    'La base conservó el estado "%s" en lugar de "%s".',
                    $registro['estado'] ?? '',
                    $estado
                ),
            ];
        }

        return [
            'status' => true,
            'error' => null,
            'estado' => $estado,
        ];
    }

    public function vincularContacto(
        $idSolicitud,
        $idContacto
    ) {
        $sql = "UPDATE solicitudes_contacto
                SET id_contacto_cliente = ?
                WHERE id_solicitud_contacto = ?";

        return $this->save($sql, [
            $idContacto,
            $idSolicitud,
        ]) === 1;
    }

    public function estadisticas()
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    COALESCE(SUM(
                        estado = 'nueva'
                    ), 0) AS nuevas,
                    COALESCE(SUM(
                        estado = 'en_seguimiento'
                    ), 0) AS seguimiento,
                    COALESCE(SUM(
                        estado = 'respondida'
                    ), 0) AS respondidas,
                    COALESCE(SUM(
                        estado IN (
                            'nueva',
                            'leida',
                            'en_seguimiento'
                        )
                    ), 0) AS pendientes
                FROM solicitudes_contacto";

        return $this->select($sql);
    }
}
