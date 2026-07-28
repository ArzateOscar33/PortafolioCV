<?php

class AdminModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function obtenerResumen()
    {
        $sql = "SELECT
                    (
                        SELECT COUNT(*)
                        FROM proyectos
                        WHERE eliminado_en IS NULL
                    ) AS proyectos_activos,

                    (
                        SELECT COUNT(*)
                        FROM proyectos p
                        INNER JOIN estados_proyecto ep
                            ON ep.id_estado_proyecto =
                               p.id_estado_proyecto
                        WHERE p.eliminado_en IS NULL
                          AND ep.slug = 'publicado'
                    ) AS proyectos_publicados,

                    (
                        SELECT COUNT(*)
                        FROM tecnologias
                        WHERE activa = 1
                    ) AS tecnologias_activas,

                    (
                        SELECT COUNT(*)
                        FROM categorias
                        WHERE activa = 1
                    ) AS categorias_activas,

                    (
                        SELECT COUNT(*)
                        FROM solicitudes_contacto
                        WHERE estado IN (
                            'nueva',
                            'leida',
                            'en_seguimiento'
                        )
                    ) AS mensajes_pendientes,

                    (
                        SELECT COUNT(*)
                        FROM solicitudes_contacto
                        WHERE estado = 'nueva'
                    ) AS mensajes_nuevos,

                    (
                        SELECT COUNT(*)
                        FROM clientes
                        WHERE activo = 1
                          AND eliminado_en IS NULL
                    ) AS clientes_activos,

                    (
                        SELECT COUNT(*)
                        FROM multimedia_proyecto
                        WHERE activo = 1
                    ) AS multimedia_activa";

        $resultado = $this->select($sql);

        return is_array($resultado)
            ? $resultado
            : [];
    }

    public function obtenerActividadReciente($limite = 8)
    {
        /*
         * El límite se convierte a entero antes de incorporarlo
         * a la consulta para evitar inyección SQL.
         */
        $limite = max(1, min((int) $limite, 20));

        $sql = "SELECT
                    ba.id_bitacora,
                    ba.accion,
                    ba.tipo_entidad,
                    ba.id_entidad,
                    ba.creado_en,
                    ba.valores_nuevos,
                    COALESCE(
                        u.nombre_mostrar,
                        u.nombre_usuario,
                        'Sistema'
                    ) AS usuario
                FROM bitacora_auditoria ba
                LEFT JOIN usuarios u
                    ON u.id_usuario = ba.id_usuario
                ORDER BY
                    ba.creado_en DESC,
                    ba.id_bitacora DESC
                LIMIT {$limite}";

        $resultado = $this->selectAll($sql);

        return is_array($resultado)
            ? $resultado
            : [];
    }
}
