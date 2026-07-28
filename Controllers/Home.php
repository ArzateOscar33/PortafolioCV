<?php

class Home extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $configuracion = $this->model->configuracionPublica();
        $redes = $this->model->redesPublicas();
        $tecnologias = $this->model->tecnologiasPublicas();
        $contenidoProyectos = $this->model->contenidoProyectosPublicos();

        $configuracion = is_array($configuracion)
            ? $configuracion
            : [];

        $redes = is_array($redes)
            ? $redes
            : [];

        $tecnologias = is_array($tecnologias)
            ? $tecnologias
            : [];

        $proyectos = is_array(
            $contenidoProyectos['proyectos'] ?? null
        )
            ? $contenidoProyectos['proyectos']
            : [];

        $categoriasProyectos = is_array(
            $contenidoProyectos['categorias'] ?? null
        )
            ? $contenidoProyectos['categorias']
            : [];

        $data = [
            'title' => $configuracion['seo_titulo']
                ?? $configuracion['nombre_sitio']
                ?? 'Cyberpunk | Portafolio de Desarrollo',

            'description' => $configuracion['seo_descripcion']
                ?? $configuracion['descripcion_sitio']
                ?? 'Portafolio profesional de desarrollo e infraestructura.',

            'themeColor' => $configuracion['color_primario']
                ?? '#070711',

            'configuracion' => $configuracion,
            'redes' => $redes,
            'tecnologias' => $tecnologias,
            'proyectos' => $proyectos,
            'categoriasProyectos' => $categoriasProyectos,

            'styles' => [
                'Assets/css/Principal/style.css',
            ],

            'scripts' => [
                'Assets/js/Principal/index.js',
            ],
        ];

        $this->views->getView(
            'Principal/Home',
            'index',
            $data
        );
    }
}
