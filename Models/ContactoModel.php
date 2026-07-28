<?php

class ContactoModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function servicioPorId($idServicio)
    {
        return $this->select(
            "SELECT
                id_servicio,
                nombre,
                slug
             FROM servicios
             WHERE id_servicio = ?
               AND activo = 1",
            [$idServicio]
        );
    }

    public function servicioPorSlug($slug)
    {
        return $this->select(
            "SELECT
                id_servicio,
                nombre,
                slug
             FROM servicios
             WHERE slug = ?
               AND activo = 1",
            [$slug]
        );
    }

    public function buscarContactoPorCorreo($correo)
    {
        return $this->select(
            "SELECT cc.id_contacto_cliente
             FROM contactos_cliente cc
             INNER JOIN clientes c
                ON c.id_cliente = cc.id_cliente
             WHERE cc.correo = ?
               AND cc.activo = 1
               AND c.activo = 1
               AND c.eliminado_en IS NULL
             ORDER BY cc.es_principal DESC
             LIMIT 1",
            [$correo]
        );
    }

    public function enviosRecientes(
        $direccionIp,
        $minutos
    ) {
        $minutos = max(1, (int) $minutos);

        $registro = $this->select(
            "SELECT COUNT(*) AS total
             FROM solicitudes_contacto
             WHERE direccion_ip = ?
               AND creado_en >= DATE_SUB(
                    CURRENT_TIMESTAMP,
                    INTERVAL {$minutos} MINUTE
               )",
            [$direccionIp]
        );

        return (int) ($registro['total'] ?? 0);
    }

    public function esDuplicadoReciente(
        $correo,
        $mensaje,
        $minutos
    ) {
        $minutos = max(1, (int) $minutos);

        return (bool) $this->select(
            "SELECT id_solicitud_contacto
             FROM solicitudes_contacto
             WHERE correo = ?
               AND mensaje = ?
               AND creado_en >= DATE_SUB(
                    CURRENT_TIMESTAMP,
                    INTERVAL {$minutos} MINUTE
               )
             LIMIT 1",
            [
                $correo,
                $mensaje,
            ]
        );
    }

    public function registrar(array $datos)
    {
        $sql = "INSERT INTO solicitudes_contacto (
                    id_servicio,
                    id_contacto_cliente,
                    nombre_completo,
                    correo,
                    telefono,
                    nombre_empresa,
                    asunto,
                    mensaje,
                    fecha_preferida,
                    modalidad_preferida,
                    estado,
                    origen,
                    direccion_ip
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $datos['id_servicio'],
            $datos['id_contacto_cliente'],
            $datos['nombre_completo'],
            $datos['correo'],
            $datos['telefono'],
            $datos['nombre_empresa'],
            $datos['asunto'],
            $datos['mensaje'],
            $datos['fecha_preferida'],
            $datos['modalidad_preferida'],
            $datos['estado'],
            $datos['origen'],
            $datos['direccion_ip'],
        ]);
    }
}
