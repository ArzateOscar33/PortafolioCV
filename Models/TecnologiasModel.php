<?php

class TecnologiasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function listar($busqueda = '', $estado = null)
    {
        $sql = "SELECT
                    id_tecnologia,
                    nombre,
                    slug,
                    color_hexadecimal,
                    tipo_icono,
                    valor_icono,
                    nivel_o_area,
                    orden_visualizacion,
                    activa,
                    creada_en,
                    actualizada_en
                FROM tecnologias
                WHERE 1 = 1";

        $parametros = [];

        if ($estado !== null) {
            $sql .= " AND activa = ?";
            $parametros[] = $estado;
        }

        if ($busqueda !== '') {
            $valor = '%' . $busqueda . '%';
            $sql .= " AND (
                        nombre LIKE ?
                        OR slug LIKE ?
                        OR nivel_o_area LIKE ?
                    )";
            $parametros[] = $valor;
            $parametros[] = $valor;
            $parametros[] = $valor;
        }

        $sql .= " ORDER BY orden_visualizacion ASC, nombre ASC";

        return $this->selectAll($sql, $parametros);
    }

    public function obtener($idTecnologia)
    {
        $sql = "SELECT
                    id_tecnologia,
                    nombre,
                    slug,
                    color_hexadecimal,
                    tipo_icono,
                    valor_icono,
                    nivel_o_area,
                    orden_visualizacion,
                    activa,
                    creada_en,
                    actualizada_en
                FROM tecnologias
                WHERE id_tecnologia = ?";

        return $this->select($sql, [$idTecnologia]);
    }

    public function existe($campo, $valor, $idTecnologia = 0)
    {
        if (!in_array($campo, ['nombre', 'slug'], true)) {
            return false;
        }

        $sql = "SELECT id_tecnologia
                FROM tecnologias
                WHERE {$campo} = ?";

        $parametros = [$valor];

        if ($idTecnologia > 0) {
            $sql .= " AND id_tecnologia != ?";
            $parametros[] = $idTecnologia;
        }

        return $this->select($sql, $parametros);
    }

    public function registrar(array $datos)
    {
        $sql = "INSERT INTO tecnologias (
                    nombre,
                    slug,
                    color_hexadecimal,
                    tipo_icono,
                    valor_icono,
                    nivel_o_area,
                    orden_visualizacion,
                    activa
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $datos['nombre'],
            $datos['slug'],
            $datos['color_hexadecimal'],
            $datos['tipo_icono'],
            $datos['valor_icono'],
            $datos['nivel_o_area'],
            $datos['orden_visualizacion'],
            $datos['activa'],
        ]);
    }

    public function modificar(array $datos, $idTecnologia)
    {
        $sql = "UPDATE tecnologias
                SET nombre = ?,
                    slug = ?,
                    color_hexadecimal = ?,
                    tipo_icono = ?,
                    valor_icono = ?,
                    nivel_o_area = ?,
                    orden_visualizacion = ?,
                    activa = ?
                WHERE id_tecnologia = ?";

        return $this->save($sql, [
            $datos['nombre'],
            $datos['slug'],
            $datos['color_hexadecimal'],
            $datos['tipo_icono'],
            $datos['valor_icono'],
            $datos['nivel_o_area'],
            $datos['orden_visualizacion'],
            $datos['activa'],
            $idTecnologia,
        ]) === 1;
    }

    public function cambiarEstado($idTecnologia, $estado)
    {
        $sql = "UPDATE tecnologias
                SET activa = ?
                WHERE id_tecnologia = ?";

        return $this->save($sql, [$estado, $idTecnologia]) === 1;
    }

    public function estadisticas()
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    COALESCE(SUM(activa = 1), 0) AS activas,
                    COALESCE(SUM(activa = 0), 0) AS inactivas
                FROM tecnologias";

        return $this->select($sql);
    }

    public function siguienteOrden()
    {
        $sql = "SELECT
                    COALESCE(MAX(orden_visualizacion), 0) + 1
                        AS siguiente_orden
                FROM tecnologias";

        return $this->select($sql);
    }
}
