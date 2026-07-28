<?php

class ClientesModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listar(
        $busqueda = '',
        $tipo = 'all',
        $estado = 'all',
        $registro = 'active'
    ) {
        $sql = "SELECT
                    c.id_cliente,
                    c.tipo_cliente,
                    c.nombre_mostrar,
                    c.razon_social,
                    c.correo,
                    c.telefono,
                    c.sitio_web,
                    c.ruta_logotipo,
                    c.activo,
                    c.creado_en,
                    c.actualizado_en,
                    c.eliminado_en,
                    (
                        SELECT COUNT(*)
                        FROM proyectos p
                        WHERE p.id_cliente = c.id_cliente
                          AND p.eliminado_en IS NULL
                    ) AS total_proyectos,
                    (
                        SELECT COUNT(*)
                        FROM contactos_cliente cc
                        WHERE cc.id_cliente = c.id_cliente
                          AND cc.activo = 1
                    ) AS total_contactos
                FROM clientes c
                WHERE 1 = 1";

        $parametros = [];

        if ($registro === 'active') {
            $sql .= " AND c.eliminado_en IS NULL";
        } elseif ($registro === 'deleted') {
            $sql .= " AND c.eliminado_en IS NOT NULL";
        }

        if ($tipo !== 'all') {
            $sql .= " AND c.tipo_cliente = ?";
            $parametros[] = $tipo;
        }

        if ($estado === 'active') {
            $sql .= " AND c.activo = 1";
        } elseif ($estado === 'inactive') {
            $sql .= " AND c.activo = 0";
        }

        if ($busqueda !== '') {
            $valor = '%' . $busqueda . '%';
            $sql .= " AND (
                        c.nombre_mostrar LIKE ?
                        OR c.razon_social LIKE ?
                        OR c.correo LIKE ?
                        OR c.telefono LIKE ?
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
                    (c.eliminado_en IS NOT NULL) ASC,
                    c.activo DESC,
                    c.nombre_mostrar ASC";

        return $this->selectAll($sql, $parametros);
    }

    public function obtener($idCliente)
    {
        $sql = "SELECT
                    c.id_cliente,
                    c.tipo_cliente,
                    c.nombre_mostrar,
                    c.razon_social,
                    c.correo,
                    c.telefono,
                    c.sitio_web,
                    c.ruta_logotipo,
                    c.notas,
                    c.activo,
                    c.creado_en,
                    c.actualizado_en,
                    c.eliminado_en,
                    (
                        SELECT COUNT(*)
                        FROM proyectos p
                        WHERE p.id_cliente = c.id_cliente
                          AND p.eliminado_en IS NULL
                    ) AS total_proyectos,
                    (
                        SELECT COUNT(*)
                        FROM contactos_cliente cc
                        WHERE cc.id_cliente = c.id_cliente
                          AND cc.activo = 1
                    ) AS total_contactos
                FROM clientes c
                WHERE c.id_cliente = ?";

        return $this->select($sql, [$idCliente]);
    }

    public function registrar(array $datos)
    {
        $sql = "INSERT INTO clientes (
                    tipo_cliente,
                    nombre_mostrar,
                    razon_social,
                    correo,
                    telefono,
                    sitio_web,
                    ruta_logotipo,
                    notas,
                    activo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $datos['tipo_cliente'],
            $datos['nombre_mostrar'],
            $datos['razon_social'],
            $datos['correo'],
            $datos['telefono'],
            $datos['sitio_web'],
            $datos['ruta_logotipo'],
            $datos['notas'],
            $datos['activo'],
        ]);
    }

    public function modificar(array $datos, $idCliente)
    {
        $sql = "UPDATE clientes
                SET tipo_cliente = ?,
                    nombre_mostrar = ?,
                    razon_social = ?,
                    correo = ?,
                    telefono = ?,
                    sitio_web = ?,
                    ruta_logotipo = ?,
                    notas = ?,
                    activo = ?
                WHERE id_cliente = ?";

        return $this->save($sql, [
            $datos['tipo_cliente'],
            $datos['nombre_mostrar'],
            $datos['razon_social'],
            $datos['correo'],
            $datos['telefono'],
            $datos['sitio_web'],
            $datos['ruta_logotipo'],
            $datos['notas'],
            $datos['activo'],
            $idCliente,
        ]) === 1;
    }

    public function cambiarEstado($idCliente, $activo)
    {
        $sql = "UPDATE clientes
                SET activo = ?
                WHERE id_cliente = ?
                  AND eliminado_en IS NULL";

        return $this->save($sql, [
            $activo,
            $idCliente,
        ]) === 1;
    }

    public function darBaja($idCliente)
    {
        $sql = "UPDATE clientes
                SET activo = 0,
                    eliminado_en = CURRENT_TIMESTAMP
                WHERE id_cliente = ?
                  AND eliminado_en IS NULL";

        return $this->save($sql, [$idCliente]) === 1;
    }

    public function restaurar($idCliente)
    {
        $sql = "UPDATE clientes
                SET activo = 1,
                    eliminado_en = NULL
                WHERE id_cliente = ?
                  AND eliminado_en IS NOT NULL";

        return $this->save($sql, [$idCliente]) === 1;
    }

    public function estadisticas()
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    COALESCE(SUM(
                        eliminado_en IS NULL AND activo = 1
                    ), 0) AS activos,
                    COALESCE(SUM(
                        eliminado_en IS NULL AND activo = 0
                    ), 0) AS inactivos,
                    COALESCE(SUM(
                        eliminado_en IS NOT NULL
                    ), 0) AS bajas
                FROM clientes";

        return $this->select($sql);
    }
}
