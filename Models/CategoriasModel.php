<?php

class CategoriasModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtener todas las categorías.
     */
    public function getCategorias()
    {
        $sql = "SELECT
                    id_categoria,
                    nombre,
                    slug,
                    descripcion,
                    color_hexadecimal,
                    clase_icono,
                    orden_visualizacion,
                    activa,
                    creada_en,
                    actualizada_en
                FROM categorias
                ORDER BY orden_visualizacion ASC, nombre ASC";

        return $this->selectAll($sql);
    }

    /**
     * Obtener categorías según su estado.
     *
     * $estado:
     * 1 = activas
     * 0 = inactivas
     */
    public function getCategoriasEstado($estado)
    {
        $sql = "SELECT
                    id_categoria,
                    nombre,
                    slug,
                    descripcion,
                    color_hexadecimal,
                    clase_icono,
                    orden_visualizacion,
                    activa,
                    creada_en,
                    actualizada_en
                FROM categorias
                WHERE activa = ?
                ORDER BY orden_visualizacion ASC, nombre ASC";

        $array = [
            $estado
        ];

        return $this->selectAll(
            $sql,
            $array
        );
    }

    /**
     * Buscar categorías por nombre o slug.
     */
    public function buscarCategorias($busqueda)
    {
        $sql = "SELECT
                    id_categoria,
                    nombre,
                    slug,
                    descripcion,
                    color_hexadecimal,
                    clase_icono,
                    orden_visualizacion,
                    activa,
                    creada_en,
                    actualizada_en
                FROM categorias
                WHERE nombre LIKE ?
                   OR slug LIKE ?
                ORDER BY orden_visualizacion ASC, nombre ASC";

        $valorBusqueda = '%' . $busqueda . '%';

        $array = [
            $valorBusqueda,
            $valorBusqueda
        ];

        return $this->selectAll(
            $sql,
            $array
        );
    }

    /**
     * Buscar categorías por texto y estado.
     */
    public function buscarCategoriasEstado(
        $busqueda,
        $estado
    ) {
        $sql = "SELECT
                    id_categoria,
                    nombre,
                    slug,
                    descripcion,
                    color_hexadecimal,
                    clase_icono,
                    orden_visualizacion,
                    activa,
                    creada_en,
                    actualizada_en
                FROM categorias
                WHERE activa = ?
                  AND (
                      nombre LIKE ?
                      OR slug LIKE ?
                  )
                ORDER BY orden_visualizacion ASC, nombre ASC";

        $valorBusqueda = '%' . $busqueda . '%';

        $array = [
            $estado,
            $valorBusqueda,
            $valorBusqueda
        ];

        return $this->selectAll(
            $sql,
            $array
        );
    }

    /**
     * Obtener una categoría por ID.
     */
    public function getCategoria($idCategoria)
    {
        $sql = "SELECT
                    id_categoria,
                    nombre,
                    slug,
                    descripcion,
                    color_hexadecimal,
                    clase_icono,
                    orden_visualizacion,
                    activa,
                    creada_en,
                    actualizada_en
                FROM categorias
                WHERE id_categoria = ?";

        $array = [
            $idCategoria
        ];

        return $this->select(
            $sql,
            $array
        );
    }

    /**
     * Registrar una categoría.
     */
    public function registrar(
        $nombre,
        $slug,
        $descripcion,
        $colorHexadecimal,
        $claseIcono,
        $ordenVisualizacion,
        $activa
    ) {
        $sql = "INSERT INTO categorias (
                    nombre,
                    slug,
                    descripcion,
                    color_hexadecimal,
                    clase_icono,
                    orden_visualizacion,
                    activa
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $array = [
            $nombre,
            $slug,
            $descripcion,
            $colorHexadecimal,
            $claseIcono,
            $ordenVisualizacion,
            $activa
        ];

        return $this->insertar(
            $sql,
            $array
        );
    }

    /**
     * Modificar una categoría.
     */
    public function modificar(
        $nombre,
        $slug,
        $descripcion,
        $colorHexadecimal,
        $claseIcono,
        $ordenVisualizacion,
        $activa,
        $idCategoria
    ) {
        $sql = "UPDATE categorias
                SET nombre = ?,
                    slug = ?,
                    descripcion = ?,
                    color_hexadecimal = ?,
                    clase_icono = ?,
                    orden_visualizacion = ?,
                    activa = ?
                WHERE id_categoria = ?";

        $array = [
            $nombre,
            $slug,
            $descripcion,
            $colorHexadecimal,
            $claseIcono,
            $ordenVisualizacion,
            $activa,
            $idCategoria
        ];

        return $this->save(
            $sql,
            $array
        );
    }

    /**
     * Verificar si un nombre ya está registrado.
     */
    public function verificarNombre($nombre)
    {
        $sql = "SELECT
                    id_categoria,
                    nombre
                FROM categorias
                WHERE nombre = ?";

        $array = [
            $nombre
        ];

        return $this->select(
            $sql,
            $array
        );
    }

    /**
     * Verificar un nombre excluyendo la categoría editada.
     */
    public function verificarNombreEditar(
        $nombre,
        $idCategoria
    ) {
        $sql = "SELECT
                    id_categoria,
                    nombre
                FROM categorias
                WHERE nombre = ?
                  AND id_categoria != ?";

        $array = [
            $nombre,
            $idCategoria
        ];

        return $this->select(
            $sql,
            $array
        );
    }

    /**
     * Verificar si un slug ya está registrado.
     */
    public function verificarSlug($slug)
    {
        $sql = "SELECT
                    id_categoria,
                    slug
                FROM categorias
                WHERE slug = ?";

        $array = [
            $slug
        ];

        return $this->select(
            $sql,
            $array
        );
    }

    /**
     * Verificar un slug excluyendo la categoría editada.
     */
    public function verificarSlugEditar(
        $slug,
        $idCategoria
    ) {
        $sql = "SELECT
                    id_categoria,
                    slug
                FROM categorias
                WHERE slug = ?
                  AND id_categoria != ?";

        $array = [
            $slug,
            $idCategoria
        ];

        return $this->select(
            $sql,
            $array
        );
    }

    /**
     * Baja lógica de una categoría.
     */
    public function eliminar($idCategoria)
    {
        $sql = "UPDATE categorias
                SET activa = ?
                WHERE id_categoria = ?";

        $array = [
            0,
            $idCategoria
        ];

        return $this->save(
            $sql,
            $array
        );
    }

    /**
     * Reactivar una categoría.
     */
    public function activar($idCategoria)
    {
        $sql = "UPDATE categorias
                SET activa = ?
                WHERE id_categoria = ?";

        $array = [
            1,
            $idCategoria
        ];

        return $this->save(
            $sql,
            $array
        );
    }

    /**
     * Cambiar el estado directamente.
     */
    public function cambiarEstado(
        $estado,
        $idCategoria
    ) {
        $sql = "UPDATE categorias
                SET activa = ?
                WHERE id_categoria = ?";

        $array = [
            $estado,
            $idCategoria
        ];

        return $this->save(
            $sql,
            $array
        );
    }

    /**
     * Obtener las estadísticas de categorías.
     */
    public function getEstadisticas()
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(
                        CASE
                            WHEN activa = 1 THEN 1
                            ELSE 0
                        END
                    ) AS activas,
                    SUM(
                        CASE
                            WHEN activa = 0 THEN 1
                            ELSE 0
                        END
                    ) AS inactivas
                FROM categorias";

        return $this->select($sql);
    }

    /**
     * Obtener el siguiente orden de visualización.
     */
    public function getSiguienteOrden()
    {
        $sql = "SELECT
                    COALESCE(
                        MAX(orden_visualizacion),
                        0
                    ) + 1 AS siguiente_orden
                FROM categorias";

        return $this->select($sql);
    }

    /**
     * Contar proyectos vinculados a una categoría.
     */
    public function getTotalProyectos($idCategoria)
    {
        $sql = "SELECT
                    COUNT(*) AS total
                FROM proyecto_categoria
                WHERE id_categoria = ?";

        $array = [
            $idCategoria
        ];

        return $this->select(
            $sql,
            $array
        );
    }
}
