<?php

require_once __DIR__ . '/SitioModel.php';

class HomeModel extends SitioModel
{
    /**
     * Obtiene las tecnologías visibles en el sitio público.
     */
    public function tecnologiasPublicas(): array
    {
        $sql = "SELECT
                    id_tecnologia,
                    nombre,
                    slug,
                    color_hexadecimal,
                    tipo_icono,
                    valor_icono,
                    nivel_o_area,
                    orden_visualizacion
                FROM tecnologias
                WHERE activa = 1
                ORDER BY
                    orden_visualizacion ASC,
                    nombre ASC";

        $tecnologias = $this->selectAll($sql);

        return is_array($tecnologias)
            ? $tecnologias
            : [];
    }

    public function contenidoProyectosPublicos(): array
    {
        $proyectos = $this->selectAll(
            "SELECT
            p.id_proyecto,
            p.slug,
            p.titulo,
            p.resumen_corto,
            p.descripcion,
            p.reto_tecnico,
            p.resultado,
            p.fecha_inicio,
            p.fecha_fin,
            p.destacado,
            p.orden_visualizacion,
            p.publicado_en,
            ep.nombre AS estado_nombre,
            ep.slug AS estado_slug,
            c.nombre_mostrar AS cliente_nombre
        FROM proyectos p
        INNER JOIN estados_proyecto ep
            ON ep.id_estado_proyecto = p.id_estado_proyecto
        LEFT JOIN clientes c
            ON c.id_cliente = p.id_cliente
        WHERE p.eliminado_en IS NULL
          AND ep.visible_publicamente = 1
          AND p.publicado_en IS NOT NULL
          AND p.publicado_en <= CURRENT_TIMESTAMP
        ORDER BY
            p.destacado DESC,
            p.orden_visualizacion ASC,
            p.publicado_en DESC"
        );

        if (!is_array($proyectos) || empty($proyectos)) {
            return [
                'proyectos' => [],
                'categorias' => [],
            ];
        }

        $ids = array_map(
            'intval',
            array_column($proyectos, 'id_proyecto')
        );

        $marcadores = implode(
            ',',
            array_fill(0, count($ids), '?')
        );

        $categorias = $this->selectAll(
            "SELECT
            pc.id_proyecto,
            ca.id_categoria,
            ca.nombre,
            ca.slug,
            ca.descripcion,
            ca.color_hexadecimal,
            ca.clase_icono,
            ca.orden_visualizacion
        FROM proyecto_categoria pc
        INNER JOIN categorias ca
            ON ca.id_categoria = pc.id_categoria
        WHERE pc.id_proyecto IN ({$marcadores})
          AND ca.activa = 1
        ORDER BY
            ca.orden_visualizacion ASC,
            ca.nombre ASC",
            $ids
        );

        $tecnologias = $this->selectAll(
            "SELECT
            pt.id_proyecto,
            t.id_tecnologia,
            t.nombre,
            t.slug,
            t.color_hexadecimal,
            t.tipo_icono,
            t.valor_icono,
            t.nivel_o_area,
            pt.orden_visualizacion
        FROM proyecto_tecnologia pt
        INNER JOIN tecnologias t
            ON t.id_tecnologia = pt.id_tecnologia
        WHERE pt.id_proyecto IN ({$marcadores})
          AND t.activa = 1
        ORDER BY
            pt.orden_visualizacion ASC,
            t.nombre ASC",
            $ids
        );

        $enlaces = $this->selectAll(
            "SELECT
            ep.id_proyecto,
            ep.id_enlace_proyecto,
            ep.etiqueta,
            ep.url,
            ep.es_privado,
            ep.orden_visualizacion,
            te.nombre AS tipo_nombre,
            te.slug AS tipo_slug
        FROM enlaces_proyecto ep
        INNER JOIN tipos_enlace te
            ON te.id_tipo_enlace = ep.id_tipo_enlace
        WHERE ep.id_proyecto IN ({$marcadores})
        ORDER BY
            ep.orden_visualizacion ASC,
            ep.id_enlace_proyecto ASC",
            $ids
        );

        $multimedia = $this->selectAll(
            "SELECT
            mp.id_proyecto,
            mp.id_multimedia,
            mp.tipo_almacenamiento,
            mp.ruta_archivo,
            mp.url_externa,
            mp.ruta_miniatura,
            mp.titulo,
            mp.texto_alternativo,
            mp.descripcion,
            mp.tipo_mime,
            mp.orden_visualizacion,
            mp.es_portada,
            tm.nombre AS tipo_nombre,
            tm.slug AS tipo_slug
        FROM multimedia_proyecto mp
        INNER JOIN tipos_multimedia tm
            ON tm.id_tipo_multimedia = mp.id_tipo_multimedia
        WHERE mp.id_proyecto IN ({$marcadores})
          AND mp.activo = 1
        ORDER BY
            mp.es_portada DESC,
            mp.orden_visualizacion ASC,
            mp.id_multimedia ASC",
            $ids
        );

        $mapa = [];

        foreach ($proyectos as $proyecto) {
            $idProyecto = (int) $proyecto['id_proyecto'];

            $proyecto['id_proyecto'] = $idProyecto;
            $proyecto['destacado'] = (int) $proyecto['destacado'];
            $proyecto['categorias'] = [];
            $proyecto['tecnologias'] = [];
            $proyecto['enlaces'] = [];
            $proyecto['imagenes'] = [];
            $proyecto['videos'] = [];
            $proyecto['portada'] = null;

            $mapa[$idProyecto] = $proyecto;
        }

        $categoriasPublicas = [];

        foreach (is_array($categorias) ? $categorias : [] as $categoria) {
            $idProyecto = (int) $categoria['id_proyecto'];
            $idCategoria = (int) $categoria['id_categoria'];

            unset($categoria['id_proyecto']);

            $categoria['id_categoria'] = $idCategoria;

            if (isset($mapa[$idProyecto])) {
                $mapa[$idProyecto]['categorias'][] = $categoria;
                $categoriasPublicas[$idCategoria] = $categoria;
            }
        }

        foreach (is_array($tecnologias) ? $tecnologias : [] as $tecnologia) {
            $idProyecto = (int) $tecnologia['id_proyecto'];

            unset($tecnologia['id_proyecto']);

            $tecnologia['id_tecnologia'] = (int) (
                $tecnologia['id_tecnologia'] ?? 0
            );

            if (isset($mapa[$idProyecto])) {
                $mapa[$idProyecto]['tecnologias'][] = $tecnologia;
            }
        }

        foreach (is_array($enlaces) ? $enlaces : [] as $enlace) {
            $idProyecto = (int) (
                $enlace['id_proyecto'] ?? 0
            );

            unset($enlace['id_proyecto']);

            /*
     * Normalizamos la privacidad como entero.
     */
            $enlace['es_privado'] = (int) (
                $enlace['es_privado'] ?? 0
            );

            /*
     * Nunca enviamos la URL de un enlace privado
     * hacia las vistas públicas.
     *
     * El registro se conserva para que la vista
     * pueda mostrar el candado.
     */
            if ($enlace['es_privado'] === 1) {
                $enlace['url'] = '';
            }

            if (isset($mapa[$idProyecto])) {
                $mapa[$idProyecto]['enlaces'][] = $enlace;
            }
        }

        foreach (is_array($multimedia) ? $multimedia : [] as $archivo) {
            $idProyecto = (int) $archivo['id_proyecto'];

            unset($archivo['id_proyecto']);

            $archivo['es_portada'] = (int) (
                $archivo['es_portada'] ?? 0
            );

            if (!isset($mapa[$idProyecto])) {
                continue;
            }

            if (($archivo['tipo_slug'] ?? '') === 'imagen') {
                $mapa[$idProyecto]['imagenes'][] = $archivo;

                if (
                    $mapa[$idProyecto]['portada'] === null
                    || $archivo['es_portada'] === 1
                ) {
                    $mapa[$idProyecto]['portada'] = $archivo;
                }
            }

            if (($archivo['tipo_slug'] ?? '') === 'video') {
                $mapa[$idProyecto]['videos'][] = $archivo;
            }
        }

        return [
            'proyectos' => array_values($mapa),
            'categorias' => array_values($categoriasPublicas),
        ];
    }
}
